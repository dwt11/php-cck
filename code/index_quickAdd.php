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


    /*-----------------------
   添加新项
   function _AddNew() {   }
   -------------------------*/
    if (empty($link) || empty($title)) {
        ShowMsg("链接网址或标题不能为空！", "-1");
        exit();
    }

    $oldct = "";
    $fp = fopen($myQuickMenu, 'w');
    if (filesize($myQuickMenu) > 0) $oldct = trim(fread($fp, filesize($myQuickMenu)));
    fclose($fp);

    $link = preg_replace("#['\"]#", '`', $link);
    $title = preg_replace("#['\"]#", '`', $title);

    //得到id
    $id = 1;
    $dtp = new DwtTagparse();
    $dtp->SetNameSpace('menu', '<', '>');
    $dtp->LoadTemplet($myQuickMenu);
    if (is_array($dtp->CTags)) {
        $id = count($dtp->CTags) + 1;
    }
    $oldct .= "\r\n<menu:item link=\"{$link}\" title=\"{$title}\"  id=\"{$id}\" />";


    $fp = fopen($myQuickMenu, 'w');
    fwrite($fp, $oldct);
    fclose($fp);

    dump($oldct);
    /*$nowLinkStr = "<a href='{$link}' id='link{$id}' target=\"_blank\" class='btn btn-info   btn-outline' >
                <i class='fa fa-bookmark-o'> </i>
                {$title}
                <button name='closeQuick'  data-dismiss='alert' class='close'  data-toggle='tooltip' data-placement='top' title='删除网址' style=\"margin-left: 10px;display: none\" type='button' onclick=\"delQuick('{$id}')\">×</button>
            </a>";
    echo $nowLinkStr;//用于AJAX在界面立即显示 增加的连接*/

}//End dopost==save

$id = 0;
$dtp = new DwtTagparse();
$dtp->SetNameSpace('menu', '<', '>');
$dtp->LoadTemplet($myQuickMenu);
if (is_array($dtp->CTags)) {
    $id = count($dtp->CTags) + 1;
}
if (empty($dopost)) {
    if ($id > 10) {
        echo "最多添加10个连接";
        exit;
    }
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
    <div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
        <form class="form-horizontal m-t" id="form1">
            <input type="hidden" name="dopost" value="save"/>

            <div class="form-group">
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="title" name="title" placeholder="名称" autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="link" name="link" placeholder="网址,例:http://www.163.com" autocomplete="off">
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
                    title: {required: !0},
                    link: {required: !0, url: !0}
                },
                messages: {
                    title: {required: "请填写标题"},
                    link: {required: "请填写网址", url: "网址格式不正确,应为:http://www.163.com"}
                },
                submitHandler: function (form) {
                    $.ajax({
                        type: "post",
                        url: "index_quickAdd.php",
                        data: "dopost=save&title=" + $("#title").val() + "&link=" + $("#link").val(),
                        dataType: 'html',
                        success: function (result) {
                            parent.display_tips("操作成功");
                            parent.$('#quick').append(result);
                            parent.layer.closeAll('iframe');
                        }
                    });
                }
            })
        });
    </script>
    </body>
    </html>

<?php } ?>
