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
require_once("sysGroup.class.php");
$t1 = ExecTime();


//获得当前权限组的信息
$groupWebRanks = Array();
$groupSet = $dsql->GetOne("SELECT * FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='{$rank}' ");
$groupWebRanks = explode('|', $groupSet['web_role']);
$groupDepRanks = explode('|', $groupSet['department_role']);
//dump($groupWebRanks);
//dump($groupDepRanks);
$depid = $groupSet['depid'];


$group = new sysGroup($depid);

if (empty($dopost)) $dopost = "";

$empUserNames = "";
//获得当前权限组所包含的登录名和员工姓名   全字匹配 不是模糊搜索
$query = "SELECT userName,empid FROM `#@__sys_admin` WHERE FIND_IN_SET('$rank',usertype)>0";        //echo $query."<br>";//exit;
$db->SetQuery($query);
$db->Execute(0);
//echo $query."<br>";//exit;
while ($row1 = $db->GetObject(0)) {

    $empUserNames .= "<div class='col-md-2'>" . GetEmpNameById($row1->empid) . "(" . $row1->userName . ") </div>";
}

if ($empUserNames == "") $empUserNames = "无";

?>

<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
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
                                        <label class="col-sm-2 control-label">组名称:</label>

                                        <div class="col-sm-2">
                                            <p class='form-control-static'><?php echo $groupSet['typename'] ?></p>
                                        </div>

                                    </div>


                                    <?php

                                    if ($GLOBAMOREDEP && $depid > 0) {
                                        //150611如果是多级部门则获取部门的ID和NAME
                                        $sql1 = "SELECT dep_name FROM  `#@__emp_dep` d  WHERE  dep_id='$depid'";
                                        $dsql->SetQuery($sql1);
                                        $dsql->Execute(1);
                                        $row1 = $dsql->GetObject(1);
                                        if ($row1 != "") {
                                            $depname = $row1->dep_name;
                                        }
                                        echo "<div class='form-group'>
                                                            <label class='col-sm-2 control-label'>所属顶级部门:</label>
                                                            <div class='col-sm-2'>
                                                                                <p  class='form-control-static'>$depname</p>
                                                            </div>
                                                        </div>";

                                    } ?>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">备注:</label>

                                        <div class="col-sm-2">
                                            <p class='form-control-static'><?php echo $groupSet['Remark'] ?></p>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">包含人员:</label>

                                        <div class="col-sm-6">
                                            <div class="row show-grid" style="padding: 0"> <?php echo $empUserNames ?></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php
                        if ($groupSet['web_role'] == "admin_AllowAll" && $groupSet['department_role'] == "admin_AllowAll") {

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

                            $group->getRoleTable($groupWebRanks, $groupDepRanks, true);   //直接输出
                            ?>


                        <?php } ?>


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
<script src="sysGroup.js"></script>
<script src="../ui/js/jquery.cookie.js"></script>

<SCRIPT LANGUAGE="JavaScript">
    <?php
    echo $group->panelJScode;?>
</script>


</body>
</html>