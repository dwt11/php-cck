<?php
/**
 * 系统权限组
 *
 * @version        $Id: sysGroup.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

$keyword = isset($keyword) ? $keyword : "";

if (empty($dopost)) $dopost = "";
if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "0";
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
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?></h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <div class="alert alert-warning alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        修改用户组信息后,用户组包含的系统用户所使用的权限会立即生效！
                    </div>


                    <!--工具框   开始-->

                    <div class="btn-group" id="Toolbar">
                        <?php
                        echo $roleCheck->RoleCheckToLink("sys/sysGroup_add.php", "", "btn btn-white", "", "glyphicon glyphicon-plus");
                        ?>


                    </div>
                    <div class="btn-group" id="Toolbar2" style="margin-left: 5px">
                        <form name="form2" method="get" action="">
                            <div class="input-group">
                                <?php

                                if ($GLOBAMOREDEP && $GLOBALS['NOWLOGINUSERTOPDEPID'] == 0) {

                                    ?>
                                    <div class="pull-left">
                                        <?php
                                        //150610添加
                                        //判断dwt_emp_dep_plus是否存在,如果存在,则按顶级部门来查询显示不同的权限组
                                        //dump($GLOBALS['CUSERLOGIN']->getUserType());
                                        if (!$GLOBAMOREDEP) {
                                            //???????????????这里要取 部门管理员登录后的顶级部门ID
                                            //$depid=;
                                            echo "<input type=\"hidden\" name=\"depid\" value=\"$depid\" />";
                                        } else {
                                            $depOptions = GetDepOnlyTopOptionList($depid);
                                            //dump($emp_dep);
                                            echo "<select  class='form-control' name='depid' id='depid'  >\r\n";
                                            if ($GLOBALS['NOWLOGINUSERTOPDEPID'] == 0) echo "<option value='0'>超级管理员菜单</option>\r\n";//如果超级管理员登录  显示这个
                                            echo $depOptions;
                                            echo "</select>";
                                        } ?>


                                    </div>
                                <?php } ?>
                                <div class="pull-left ">
                                    <input name="keyword" type="text" placeholder="组名称" class="form-control" value="<?php echo $keyword ?>">
                                </div>

                                <div class="pull-left ">
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-white">
                                            搜索
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--工具框   结束-->


                    <!--表格数据区------------开始-->
                    <div class="table-responsive">
                        <table id="datalist" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-sort-order="desc" data-mobile-responsive="true">
                            <thead>
                            <tr>
                                <th data-halign="center" data-align="center">权限值
                                </td>
                                <th data-halign="center" data-align="left">组名称
                                </td>
                                <th data-halign="center" data-align="left">备注
                                </td>
                                <th data-halign="center" data-align="center">管理
                                </td>
                            </tr>
                            </thead>
                            <?php

                            //????做到这里 按部门查询权限组   SQL语句看一下怎么写,下面的获取部门名称就可以省略了
                            $wheresql = " where depid='$depid'";
                            if ($keyword != "") {
                                $wheresql .= "And   typename LIKE '%$keyword%'  ";//备注

                            }
                            $sql = "SELECT t.rank,t.typename,t.remark FROM #@__sys_admintype t $wheresql ORDER BY rank";
                            //dump($sql);
                            $dsql->SetQuery($sql);
                            $dsql->Execute();
                            while ($row = $dsql->GetObject()) {
                                ?>
                                <tr>
                                    <td><?php echo $row->rank ?></td>
                                    <td align="left"><?php echo $row->typename; ?></td>
                                    <td align="left"><?php echo $row->remark ?></td>
                                    <td><?php

                                        $usernumb = 0;
                                        $userinfo = $dsql->getone("SELECT COUNT(*) AS dd FROM #@__sys_admin
                                                                    LEFT JOIN #@__emp ON #@__emp.emp_id=#@__sys_admin.empid
                                                                    WHERE find_in_set({$row->rank},usertype)  AND #@__emp.emp_isdel=0");
                                        if ($userinfo != "") {
                                            $usernumb = $userinfo['dd'];
                                        }


                                        if ($row->rank == 10) {
                                            echo "超级管理员权限不可以修改";
                                        } else if ($row->rank == 9) {
                                            echo "公司管理员权限不可以修改";
                                        } else {
                                            echo $roleCheck->RoleCheckToLink("sys/sysGroup_view.php?rank=" . $row->rank, "详细信息(人数:" . $usernumb . ")");
                                            echo $roleCheck->RoleCheckToLink("sys/sysGroup_edit.php?rank=" . $row->rank);
                                            echo $roleCheck->RoleCheckToLink("sys/sysGroup_del.php?rank=" . $row->rank);
                                        }
                                        ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <div class="fixed-table-pagination">
                            <div class="pull-left pagination-detail"><span class="pagination-info">
                                    共<?php echo $dsql->GetTotalRow(); ?>条记录
                                    </span>
                            </div>
                        </div>
                    </div>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>
    </div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script src="../ui/js/plugins/toastr/toastr.min.js"></script>
<link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
<script src="../ui/js/plugins/layer/layer.min.js"></script>
</body>
</html>




