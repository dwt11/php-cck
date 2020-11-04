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
require_once("../config.php");

if (empty($dopost)) $dopost = '';

/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {


    $s_userip = GetIP();
    $userid=$CUSERLOGIN->getUserId();
    $dtime=time();


    $in_query = "INSERT INTO `#@__sys_feedback` (`userid`, `filename`, `body`, `cip`, `dtime`, `completeTime`, `completeBody`)
                  VALUES ('$userid', '', '$body', '$s_userip', '$dtime', '0', '')";
    //dump($in_query);
    $dsql->ExecuteNoneQuery($in_query);

   exit;

}//End dopost==save


if (empty($dopost)) {
    ?>


    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="<?php echo $cfg_soft_lang; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../ui/css/style.min.css" rel="stylesheet">
        <style>html {
                height: auto; /*160109 style.min.css将html设置为 {  height: 100%;} ,引起layer自动适应高度时错误 ,再这里将100%屏蔽掉*/
            }</style>
    </head>
    <body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
            <form id="form1"   class="form-horizontal m-t">
                <div class="form-group">
                    <div class="col-sm-2">
                        <textarea  name="body" id="body" class="form-control" placeholder="请填写您在使用中遇到的问题,我们将为您不断改进"  rows="5"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="text-center">
                        <button class="btn btn-primary" type="submit" >保存内容</button>
                    </div>
                </div>
            </form>
    </div>
    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../ui/js/content.min.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <!--右下角自动隐藏提示框 显示提示-->
    <script src="../ui/js/plugins/toastr/toastr.min.js"></script>
    <link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">


    <script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>


    <script>
        //让这个弹出层iframe自适应高度150109
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
        $().ready(function () {
            $("#form1").validate({
                rules:{body: {required: !0}
                },
                messages: {body: {required: "请填写建议内容"}
                },
                submitHandler: function (form) {
                    $.ajax({
                        type: "post",
                        url: "sysFeedback_add.php",
                        data: "dopost=save&body="+$("#body").val(),
                        dataType: 'html',
                        success: function(result)
                        {
                            parent.display_tips("操作成功,谢谢您的建议.");
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
