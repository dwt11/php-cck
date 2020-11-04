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


//获取原值

$groupWebRanks = Array();
$groupSet = $dsql->GetOne("SELECT * FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='{$rank}' ");
if (!is_array($groupSet)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}
$groupWebRanks = explode('|', $groupSet['web_role']);
$groupDepRanks = explode('|', $groupSet['department_role']);
//dump($groupWebRanks);
//dump($groupDepRanks);

$depid=$groupSet['depid'];
/*
if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "0";
}*/


//dump($rank);

$groupName_temp= $groupSet['typename'] ;
$group = new sysGroup($depid,$groupName_temp);
if (empty($adminRole)) $adminRole = "";
if (empty($dopost)) $dopost = "";
if ($dopost == 'save') {
    if ($rank == 10) {
        ShowMsg('超级管理员的权限不允许更改!', 'sysGroup.php');
        exit();
    }


    //dump($adminWebRole);exit;
    $All_webRole = $All_depRole = "";      //页面权限
    if ($adminRole != "") {
        $All_webRole = $All_depRole = "admin_AllowAll";      //页面权限
    } else {
        $checkBoxArrary = array();

        //引入功能类
        require_once("sysFunction.class.php");
        $fun = new sys_function();
        $fucArray = $fun->getSysFunArray($depid);
        //有部门数据的CHECKBOX
        //1将页面选中的checkbox组合为一个数组
        //按部门的行为数组,循环获取选中的checkbox
        foreach ($fucArray as $key => $menu) {
            for ($i2 = 0; $i2 <= $allDepNumb - 1; $i2++) {
                $checkBoxName = "dep" . $i2 . $key;   //通过页面input hidden传递过来的部门个数,来定义CHECKBOx的名字,然后用PHP来获取选中的CHECKBOX的值
                if (empty($$checkBoxName)) $$checkBoxName = "";
                //dump($$checkBoxName);
                if (is_array($$checkBoxName)) {
                    foreach ($$checkBoxName as $checkBoxValues) {
                        $value = explode(',', $checkBoxValues);
                        //dump($value);
                        if ($checkBoxValues != "" && count($value) > 1) array_push($checkBoxArrary, array("webRole" => $value[1], "depRole" => $value[0]));   //获取到值后 存入新的数组   151015修复BUG原没有count计数判断
                    }
                }
            }
        }

        //无部门数据的 文件名称压入数组
        foreach ($fucArray as $key => $menu) {
            $checkBoxName = "dep" . $key;   //通过页面input hidden传递过来的部门个数,来定义CHECKBOx的名字,然后用PHP来获取选中的CHECKBOX的值
            if (empty($$checkBoxName)) $$checkBoxName = "";
            //dump($$checkBoxName);
            if (is_array($$checkBoxName)) {

                foreach ($$checkBoxName as $checkBoxValues) {
                    $value = explode(',', $checkBoxValues);
                    if ($checkBoxValues != "") array_push($checkBoxArrary, array("webRole" => $checkBoxValues, "depRole" => "0"));   //获取到值后 存入新的数组
                }
            }

        }
//dump($checkBoxArrary);
        $group->getSaveValue($checkBoxArrary);   //获取字符串
        $All_webRole = $group->save_webRole;
        $All_depRole = $group->save_depRole;
    }
    $sql = "UPDATE `#@__sys_admintype` SET typename='$groupname',web_role='$All_webRole',department_role='$All_depRole',Remark='$Remark' WHERE CONCAT(`rank`)='$rank'";
    $dsql->ExecuteNoneQuery($sql);
    //dump($sql);
    ShowMsg('成功更改用户组的权限!', 'sysGroup.php');
    exit();
}


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
                    <div class="alert alert-warning alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        1、修改此页内容有风险，请小心操作！<br>
                        2、<strong>第一行</strong> 为用户可以访问的页面功能，每项的<strong>管理</strong>选中后，其后的<strong>添加、删除、编辑</strong>等扩展功能，才可以根据设定显示<br>
                        3、 如果是售卡点权限组,请在名称中添加"售卡点子部门"字样<br>
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp带有"售卡点子部门"名称的权限组,登录系统后:<br>
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp会员信息编辑时,无法修改会员的推荐人信息<br>
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp预约旅游线路,选择线路时,在截止时间时就不可以选择(其他操作员可以任意选择)<br>
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp直通卡不可以办理70岁以上的人员<br>
                        <?php if (file_exists(DWTPATH . '/emp')) { ?>
                            4、<strong>第一列</strong> 为用户可以访问的部门权限<br>
                            5、   <strong>―</strong> 代表功能无部门数据,请直接选择功能下的选择框
                        <?php } ?>
                    </div>


                    <form name='form2' id='form2' action='sysGroup_edit.php' method='post' class="form-horizontal" >
                        <input type='hidden' name='dopost' value='save'>
                        <input name="rank" type="hidden" id="rank" value="<?php echo $groupSet['rank'] ?>">

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
                                            <input type="text" class="form-control" name="groupname" value="<?php echo $groupSet['typename'] ?>">
                                         </div>

                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>

                                        <div class="col-sm-10">
                                              如果是售卡点的权限组,组名称请以"售卡点子部门-"开头,其他的与部门名称一致.
                                        </div>

                                    </div>

                                    <?php

                                    if ($GLOBAMOREDEP && $depid>0) {
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
                                            <input type="text" class="form-control" name="Remark" value="<?php echo $groupSet['Remark'] ?>">
                                        </div>
                                    </div>
                                    <div class="hr-line-dashed"></div>
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <button class="btn btn-primary" type="submit">保存内容</button>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <?php
                        if ($depid == 0) {
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
                                                <input type="checkbox" value="admin_AllowAll" id="adminRole" <?php echo $group->CRank("admin_AllowAll", "admin_AllowAll", $groupWebRanks, $groupDepRanks) ?> name="adminRole">
                                                可以进行任意操作(选择此项后,用户组将具有超级管理员的权限)
                                            </label>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>




                        <?php
			                        }

                        $group->getRoleTable($groupWebRanks, $groupDepRanks);   //直接输出
                        ?>


                        <input type='hidden' name='allDepNumb' value='<?php echo $group->allDepNumb; ?>'>


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

<script>
    $(document).ready(function () {
        <?php if($group->CRank("admin_AllowAll", "admin_AllowAll", $groupWebRanks, $groupDepRanks)==" checked") echo "$('#group').hide();"  //如果权限选择了管理,则在页面加载完成后再隐藏checkbox ?>
    });
</script>

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