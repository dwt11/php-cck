<?php
/**
 * 清除缓存
 *
 * @version        $Id: sys_cache_up.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

if (empty($dopost)) $dopost = '';
if (empty($step)) $step = 1;

if ($dopost == "ok") {
    if (empty($uparc)) $uparc = 0;
    if ($step == -1) {
        if ($uparc == 0) sleep(1);
        ShowMsg("成功更新所有缓存！", "javascript:;");
        exit();
    } //清空缓存目录 tplcache
    else if ($step == 1) {
        if (ClearCache()) ShowMsg("成功清空tplcache目录", "sysCacheUp.php?dopost=ok&step=-1"); else ShowMsg("tplcache目录无文件", "sysCacheUp.php?dopost=ok&step=-1");
        exit();
    }

}


// 清空缓存tplcache目录
function ClearCache()
{
    $tplCache = DEDEDATA . '/tplcache/';
    $fileArray = glob($tplCache . "*.*");
    if (count($fileArray) > 1) {
        foreach ($fileArray as $key => $value) {
// dump($value);
            if (file_exists($value)) unlink($value);
            else continue;
        }
        return TRUE;
    }
    return FALSE;
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
                    <!--搜索框   开始-->

                    <!--搜索框   结束-->


                    <!--表格数据区------------开始-->
                    <div class="table-responsive">


                        <form name="form1" action="sysCacheUp.php" method="get" target='stafrm' onSubmit="checkSubmit()">

                            <input type="hidden" name="dopost" value="ok">

                            <!--选项-->

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        本程序会执行下面的操作,用于修复页面显示不同步的问题：
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        1、清空'data/tplcache/'
                                        <br>
                                        <input type="submit" name="Submit" value="执行" class="btn btn-sm btn-primary"/>
                                    </div>
                                </div>
                            </div>
                            <!--选项-->
                            <br>

                            <!--进行状态-->
                            <div class="panel panel-default" id="status" style="display:none">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        进行状态
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%" style="min-height: 200px"></iframe>
                                </div>
                            </div>
                            <!--进行状态-->

                        </form>


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
<script type="text/javascript">
    function click_scroll() {
        //进度框 默认是隐藏的，当提交后，显示进度框，并将焦点移至进度框
        document.getElementById("status").style.display = "";
        var scroll_offset = $("#status").offset(); //得到pos这个div层的offset，包含两个值，top和left
        $("body,html").animate({
            scrollTop: scroll_offset.top //让body的scrollTop等于pos的top，就实现了滚动
        }, 0);
    }

    function checkSubmit() {
        click_scroll();
        return true;
    }


</script>


</body>
</html>





