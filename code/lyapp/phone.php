<?php
require_once("include/config.php");

if (empty($dopost)) $dopost = '';
if (empty($gourl)) $gourl = '';
//CheckRank();
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'save') {


    $checkCode_str = ValidatePhoneCode($mobilephone, $checkCode);
    //检测验证码是否正确
    if ($checkCode_str != "验证成功") {
        echo $checkCode_str;
        exit;
    }


    $phoenISuse = ValidatePhoneISon($mobilephone, $CLIENTID);//新的手机号 是否已经使用
    //dump($phoenISuse);
    if ($phoenISuse != "手机号可用") {
        echo $phoenISuse;
        exit;
    }


    //更新验证时间和客户ID
    $senddate = time();
    $sql = "UPDATE  `#@__client` SET   mobilephone='$mobilephone',mobilephone_check='1',mobilephone_checkDate='$senddate'    WHERE (`id`='$CLIENTID');";
    $dsql->ExecuteNoneQuery($sql);
    echo "验证成功";
    exit;
}


//获取客户信息
$questr = "SELECT mobilephone,mobilephone_check  FROM `#@__client`  where  id='$CLIENTID'";
$row = $dsql->GetOne($questr);
if ($row["mobilephone_check"] == 1) {
    showMsg("手机已经验证", $gourl);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>手机验证</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/animate.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                手机验证
            </div>
            <div class="col-xs-6 text-right">
                <i class="fa fa-info-circle   " style=" "></i>请先验证手机号码
            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form id="formphone" class="m-t" role="form" action="">
                <input type="hidden" name="dopost" value="save"/>
                <div class="form-group">
                    <input name="mobilephone" id="mobilephone" type="number" class="form-control" value="<?php echo $row["mobilephone"] ?>" placeholder="手机号码">
                </div>
                <div class="form-group">
                    <button class="btn  btn-primary" style="float: right" type="button" onclick="settime(this,<?php echo $CLIENTID; ?>,'<?php echo urlencode("业务验证码") ?>')">获取验证码</button>
                    <input type="number" class="form-control" style="width: 45%" id="checkCode" name="checkCode" placeholder="点击右侧">
                </div>
                <input name="clientid" id="clientid" value="<?php echo $CLIENTID ?>" type="hidden">
                <input type="hidden" class="form-control" name="pwd" value="<?php echo $pwd; ?>">
                <button type="submit" class="btn btn-primary block full-width">完成验证</button>
            </form>
        </div>
    </div>
    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <!--验证用-->
    <script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <script src="js/sendPhoneMSG.js"></script>
    <script>
        $().ready(function () {
            $("#formphone").validate({
                rules: {
                    mobilephone: {
                        required: !0, minlength: 11, isMobile: !0,
                        remote: {//校验
                            type: "get",
                            url: "phoneCodeCheck.ajax.php",
                            data: {
                                mobilephone: function () {
                                    return $("#mobilephone").val();
                                },
                                clientid: function () {
                                    return $("#clientid").val();
                                }
                            },
                            dataType: "html",
                            dataFilter: function (data, type) {
                                if (data == "true")
                                    return true;
                                else
                                    return false;
                            }
                        }
                    },
                    checkCode: {required: !0, minlength: 4, maxlength: 4}
                },
                messages: {
                    mobilephone: {
                        required: "请填写手机号",
                        minlength: "手机号应为11个数字",
                        isMobile: "手机号应以13/14/15/17/18开头",
                        remote: "手机号码已经在系统中存在,请更换"
                    },
                    checkCode: {required: "请填写短信中的四位数字", minlength: "验证码应为4个数字", maxlength: "验证码应为4个数字"}
                },
                submitHandler: function (form) {
                    $.ajax({
                        type: "post",
                        url: "phone.php",
                        data: {dopost: "save", mobilephone: $("#mobilephone").val(), checkCode: $("#checkCode").val()},
                        dataType: 'html',
                        success: function (result) {

                            if (result == "验证成功") {
                                layer.msg('验证成功', {
                                    shade: 0.5, //开启遮罩
                                    time: 1000, //20s后自动关闭
                                }, function () {
                                    <?
                                    if (GetCookie("gourl") != "" && $gourl == "") $gourl = GetCookie("gourl");
                                    if (GetCookie("gourl") == "" && $gourl == "") $gourl = "index.php";
                                    //如果是从别的页面跳转到此页验证的，则验证后，再跳转回去.
                                    ?>
                                    window.location.href = "<?php echo $gourl;?>";
                                });
                            } else {
                                layer.msg(result, {
                                    shade: 0.5, //开启遮罩
                                    time: 1000, //20s后自动关闭
                                }, function () {
                                });
                            }
                        }
                    });
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg("系统错误,请重试", {
                        shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        window.location.href = 'phone.php';
                    });
                }
            })
        });
    </script>
</body>
</html>
