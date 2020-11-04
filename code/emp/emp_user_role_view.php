<?php
/**
 * 系统权限组编辑
 *
 * @version        $Id: sysGroup_edit.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("../sys/sysGroup.class.php");

$deptopid = 0;
if ($GLOBAMOREDEP) $deptopid = GetEmpDepTopIdByUserId($id);
if (empty($dopost)) $dopost = "";


$sql = "SELECT * FROM `#@__sys_admin` WHERE id='$id'";
$row = $dsql->GetOne($sql);

$usertypes = explode(',', $row['usertype']);    //用户有属于多个权限组的话,则分别存入数组中

$depRoleArrary = array();
$webRoleArrary = array();
foreach ($usertypes as $usertype) {
    //直接从数据 库获取 权限内容
    $sql = "SELECT web_role,department_role FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='" . $usertype . "'";
    $groupSet = $dsql->GetOne($sql);
    if (is_array($groupSet)) {
        $groupWebRanks = explode('|', $groupSet['web_role']);
        $groupDepRanks = explode('|', $groupSet['department_role']);
        //将用户的多个权限组的值 合并成一个  存入数组 供权限检查使用
        foreach ($groupWebRanks as $web_role) {
            array_push($webRoleArrary, $web_role);
        }
        foreach ($groupDepRanks as $dep_role) {
            array_push($depRoleArrary, $dep_role);
        }
    }


}

//  140922此处不能删除重复数据 删除的话 容易引起部门中有用的数据删除

//dump($webRoleArrary);
//dump($depRoleArrary);


?>


<!DOCTYPE html>
<html>
<head>

    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>

                </div>
                <div class="ibox-content">

                    <form name='form2' class="form-horizontal">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title">
                                    用户组信息
                                </h5>
                            </div>
                            <div class="panel-collapse in"><!--这里与标准的面板样式class="panel-collapse collapse in"有区别 删除了collapse  因为后面要把所有checkbox面板都默认收缩了 -->
                                <div class="panel-body">


                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">用户登录名:</label>

                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="groupname" value="<?php echo $row['userName'] ?>" disabled>
                                        </div>

                                    </div>


                                    <?php

                                    if (file_exists('../emp')) {//如果有员工管理功能,才输出下面的

                                        echo "<div class='form-group'>
                                                            <label class='col-sm-2 control-label'>用户组:</label>
                                                            <div class='col-sm-2'>
                                                                <input type='text' class='form-control'  value='" . GetUserTypeNames($row['usertype']) . "'   disabled>
                                                            </div>
                                                        </div>";

                                        echo "<div class='form-group'>
                                                            <label class='col-sm-2 control-label'>员工姓名:</label>
                                                            <div class='col-sm-2'>
                                                                <input type='text' class='form-control'  value='" . GetEmpNameById($row['empid']) . "'   disabled>
                                                            </div>
                                                        </div>";


                                    } ?>


                                </div>
                            </div>
                        </div>


                        <?php
                        if (in_array("admin_AllowAll", $depRoleArrary) && in_array("admin_AllowAll", $webRoleArrary)) {

                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        特别权限
                                    </h5>
                                </div>
                                <div class="panel-collapse in"><!--这里与标准的面板样式class="panel-collapse collapse in"有区别 删除了collapse  因为后面要把所有checkbox面板都默认收缩了 -->
                                    <div class="panel-body">

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">超级管理员:</label>

                                            <div class="col-sm-10">
                                                <label class="checkbox-inline i-checks">

                                                    超级管理员权限,可以进行任意操作
                                                </label>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                        <?php } else {

                            if (in_array("dep_DepAll", $depRoleArrary) && in_array("dep_DepAll", $webRoleArrary)) {


                                ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">
                                            特别权限
                                        </h5>
                                    </div>
                                    <div class="panel-collapse in"><!--这里与标准的面板样式class="panel-collapse collapse in"有区别 删除了collapse  因为后面要把所有checkbox面板都默认收缩了 -->
                                        <div class="panel-body">

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">部门管理员:</label>

                                                <div class="col-sm-10">
                                                    <label class="checkbox-inline i-checks">

                                                        部门管理员权限,可以对以下功能进行任意操作
                                                    </label>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>

                            <?php }

                            if (GetUserTypeNames($row['usertype']) !== "无任何权限") {
                                $group = new sysGroup($deptopid);
                                $group->getRoleTable($webRoleArrary, $depRoleArrary, true);   //直接输出
                            }
                        } ?>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/toastr/toastr.min.js"></script>
<link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->


<?php
echo $group->json;   //直接输出
?>
<script src="../sys/sysGroup.js"></script>
<script src="../ui/js/jquery.cookie.js"></script>

<SCRIPT LANGUAGE="JavaScript">
    <?php
    echo $group->panelJScode;?>
</script>
</body>
</html>


