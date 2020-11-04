<?php
/**
 * 部门添加
 *
 * @version        $Id: 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 * 151218 注销掉判断部门相关的功能,改为添加功能时不选择部门,添加后再编辑所属的部门
 */
require_once("config.php");
require(DWTINC . '/dwttag.class.php');

if (empty($dopost)) $dopost = '';
$myQuickMenu = DEDEDATA . '/indexBody/quick/quick-' . $CUSERLOGIN->getUserId() . '.txt';

/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {
    $myMenu = DEDEDATA . '/indexBody/menu/menu-' . $CUSERLOGIN->getUserId() . '.txt';
    $fp = fopen($myMenu, 'r');//如果不存在就创建
    $oldct = trim(fread($fp, filesize($myMenu)));
    fclose($fp);

    $del_menu_array = explode(",", $oldct); //用户禁用的功能数组
    $newct = "";
    foreach ($del_menu_array as $oldid) {
        if ($id != $oldid) {
            $newct .= ",{$oldid}";
        }
    }
    $fp = fopen($myMenu, 'w');
    fwrite($fp, $newct);
    fclose($fp);


    //得到功能的相关信息
    $sysFunInfo = $dsql->getone("SELECT id,title,urladd,iconName FROM #@__sys_function where id='$id'");
    if ($sysFunInfo) {
        $childid = $sysFunInfo['id'];
        $childtitle = $sysFunInfo['title'];
        $iconName = $sysFunInfo['iconName'];
        $urladd = $sysFunInfo['urladd'];
        $nowLinkStr = "<a  data-index='$urladd' href='$urladd' name='menu' id='menu{$childid}'  class='J_menuItem btn btn-info   btn-outline' >
                                <i class='fa fa-$iconName'> </i>
                                {$childtitle}
                                <button name='closeMenu'  data-dismiss='alert' class='close'  data-toggle='tooltip' data-placement='top' title='删除此功能' style=\"margin-left: 10px;display: none\" type='button' onclick=\"delMenu('{$childid}')\">×</button>
                            </a>\r\n ";
        echo $nowLinkStr;//用于AJAX在界面立即显示 增加的连接
    }

}//End dopost==save


if (empty($dopost)) {
    //打开用户的文件,获取已经禁用的功能信息
    $oldct = "";
    $myMenu = DEDEDATA . '/indexBody/menu/menu-' . $CUSERLOGIN->getUserId() . '.txt';
    $fp = fopen($myMenu, 'r');//如果不存在就创建
    if (filesize($myMenu) > 0) $oldct = trim(fread($fp, filesize($myMenu)));
    fclose($fp);

    $del_menu_array = explode(",", $oldct); //用户禁用的功能数组
    //dump($del_menu_array);
    $option = "<option value='0'>请选择功能</option>/r/n";
    $option1 = "";
    foreach ($del_menu_array as $id) {
        if ($id != "") {
            $sysFunInfo = $dsql->getone("SELECT id,title FROM #@__sys_function where id='$id'");
            $id = $sysFunInfo['id'];
            $title = $sysFunInfo['title'];
            $option1 .= "<option value='{$id}'>{$title}</option>/r/n";
        }
    }
    if ($option1 != "") {
        $option .= $option1;
    } else {
        echo "没有可以添加的功能";
        exit;
    }//判断是否为空(这里需要循环之后再判断,因为$del_menu_array中存在只逗号的情况 )

    ?>


    <!DOCTYPE html>
    <html>
    <head>

        <meta charset="<?php echo $cfg_soft_lang; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href="ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="ui/css/style.min.css" rel="stylesheet">
        <style>html {
                height: auto; /*160109 style.min.css将html设置为 {  height: 100%;} ,引起layer自动适应高度时错误 ,再这里将100%屏蔽掉*/
            }</style>
    </head>

    <body class="gray-bg">


    <div class="wrapper wrapper-content animated fadeInRight"  style="background-color: #ffffff">

        <form class="form-horizontal m-t" id="form1">
            <input type="hidden" name="dopost" value="save"/>

            <div class="form-group">

                <div class="col-sm-2">
                    <select class='form-control' id='functionid' name='functionid'>
                        <?php echo $option; ?>
                    </select>
                </div>
            </div>


            <div class="form-group">
                <div class="text-center">
                    <button class="btn btn-primary" type="submit">保存内容</button>
                </div>
            </div>
        </form>
    </div>

    <script src="ui/js/jquery.min.js"></script>
    <script src="ui/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="ui/js/content.min.js"></script>
    <script src="ui/js/plugins/layer/layer.min.js"></script>
    <!--右下角自动隐藏提示框 显示提示-->
    <script src="ui/js/plugins/toastr/toastr.min.js"></script>
    <link href="ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <!--验证用-->
    <script src="ui/js/plugins/validate/jquery.validate.min.js"></script>
    <!--验证用-->
    <script>
        //让这个弹出层iframe自适应高度150109
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
        $().ready(function () {
            $("#form1").validate({
                rules: {
                    functionid: {isIntGtZero: !0}
                },
                messages: {
                    functionid: {isIntGtZero: "请选择功能"}
                },
                submitHandler: function (form) {
                    $.ajax({
                        type: "post",
                        url: "index_menuAdd.php",
                        data: "dopost=save&id=" + $("#functionid").val(),
                        dataType: 'html',
                        success: function (result) {
                            parent.display_tips("操作成功");
                            //parent.$('#menu').append(result);
                            parent.location.reload();//添加后刷新 页面,尝试了动态添加元素,但添加后 不能绑定功能的ON打开新的TAB的事件,所有刷新 了父页面
                            //parent.layer.closeAll('iframe');
                        }
                    });
                }
            })
        });
    </script>


    </body>
    </html>

<?php } ?>
