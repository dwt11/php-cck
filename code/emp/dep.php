<?php
/**
 * 部门管理
 *
 * @version        $Id: dep.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("depunit.class.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
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
                    <div class="ibox-tools">
                            <button type="button" data-action="expand-all" id="expand" class="btn btn-white btn-xs">展开所有</button>
                        <button type="button" data-action="collapse-all"  id="collapse" class="btn btn-white btn-xs">收起所有</button>


                        <?php

                        //超级管理员才显示添加顶级公司的功能
                        if($CUSERLOGIN->getUserType()==10)echo $roleCheck->RoleCheckToLink("emp/dep_add.php","","btn btn-primary btn-xs",true);
                        ?>


                    </div>
                </div>
                <!--标题栏和 添加按钮   结束-->
                <div class="ibox-content">
                    <!--搜索框   开始-->
                    <!--搜索框   结束-->
                    <!--表格数据区------------开始-->
                    <div class="dd" id="nestable2">
                        <?php
                        $tu = new DepUnit();
                        $tu->ListAllDep();
                        ?>
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
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/nestable/jquery.nestable.js"></script>
<!--表格-->
<script>
    $(document).ready(function () {

        $("#nestable2").nestable();  //初始化
        $(".dd").nestable("collapseAll");//收缩全部.这里有BUG,原来旧界面的ajax动态获取值实现不了,因为无法得到+号-号的当前状态. 现在是直接加载所有的数据,后期再改为AJAX的
        $("#expand").on("click", function (e) {
            var target = $(e.target), action = target.data("action");
            if (action === "expand-all") {
                $(".dd").nestable("expandAll")
            }
        })
        $("#collapse").on("click", function (e) {
            var target = $(e.target), action = target.data("action");
            if (action === "collapse-all") {
                $(".dd").nestable("collapseAll")
            }
        })
        $(".dd").nestable("expandAll")

    });
</script>

</body>
</html>

