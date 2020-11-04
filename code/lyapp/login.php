<?php
/**
 *   ID: login.php
 * User: dell
 * Date: 2016-05-18 15:41
 */

require_once("include/config.php");

if (empty($dopost)) $dopost = '';


//用户注册过程
if ($dopost == 'regsave') {


    //检测验证码是否正确
    if(!isset($_SESSION))session_start();
    if (empty($_SESSION[$mobilephone])) {
        //showMsg("未获取手机验证码,请重新获取", -1);
        $request_array = array("stat" => "-1");
        echo json_encode($request_array);
        exit();


    }

    //同级下部门名称是否重复
    //$questr = "SELECT mobilephone FROM `#@__client` WHERE isdel='0' AND FIND_IN_SET('17', depids) and  mobilephone ='$mobilephone'";
    $questr = "SELECT cl.mobilephone FROM `#@__client` cl
                                   LEFT JOIN #@__client_depinfos depinfos on cl.id=depinfos.clientid
                where depinfos.isdel='0'  and  cl.mobilephone ='$mobilephone'";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        $request_array = array("stat" => "-2");
        echo json_encode($request_array);
        exit();

        //ShowMsg("手机号已经被注册,如果忘记密码,请使用[取回密码]功能,修改密码", "-1");
        exit();
    }


    $phoneMsgId = $_SESSION[$mobilephone];// 在短信类中生成
    $query = "SELECT body FROM `#@__interface_phonemsg_log`    WHERE id='$phoneMsgId'";
    $row = $dsql->GetOne($query);
    if ($row["body"] != $checkCode) {
        //showMsg("手机验证码填写错误,请核对", -1);
        $request_array = array("stat" => "-3");
        echo json_encode($request_array);
        exit();

    } else {

        $sponsorid = 0;
        if (GetCookie("DWTsponsorid") != "") $sponsorid = GetCookie("DWTsponsorid");

        $nomd5pwd = $pwd;
        $pwd = preg_replace("/[^0-9a-zA-Z_@!\.-]/", '', $pwd);
        $pwd = substr(md5($pwd), 5, 20);

        $clientId = RegClient(
            $realname = "", $mobilephone, $mobilephone_check = 1, $address = "", $tag = "", $description = "", $from = "手机验证",
            $idcard = "", $operatorid = "", $sponsorid,
            $pwd,
            $depid = $DEPID, $openid = "", $AppId = "",
            $nickname = "", $sex = "", $city = "", $province = "", $country = "", $headimgurl = ""
        );


        if ($clientId > 0) {
            //----------------------------------------------
            //模拟登录
            //---------------------------
            $cfg_ml = new MemberLogin();
            $rs = $cfg_ml->CheckUser($mobilephone, $nomd5pwd);
            $gourl = "";
            if (GetCookie("gourl") != "") $gourl = GetCookie("gourl");
            if ($gourl == "") {
                $gourl = "index.php";
                //ShowMsg("成功登录，5秒钟后转向系统主页...", "index.php", 0, 2000);
            } else {
                //ShowMsg("成功登录...", $gourl, 0, 2000);
            }
            // 清除会员缓存
            $request_array = array("stat" => "1", "gourl" => "$gourl",);
            echo json_encode($request_array);
        } else {
            ShowMsg("未知错误", "index.php", 0, 2000);
        }
        exit;
    }
}

if ($dopost == 'next') {
    //判断用户是否注册过
    //$questr = "SELECT id FROM `#@__client` WHERE isdel='0' and FIND_IN_SET('17', depids) and  mobilephone ='$mobilephone'";
    $questr = "SELECT cl.id FROM `#@__client`  cl
               LEFT JOIN #@__client_depinfos depinfos on cl.id=depinfos.clientid
               where depinfos.isdel='0'  and  cl.mobilephone ='$mobilephone'";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {

        //----------------------------------------------
        //模拟登录
        //---------------------------

        if ($pwd == '') {
            $request_array = array("stat" => "密码不能为空");
            echo json_encode($request_array);
            //ShowMsg("密码不能为空！", "-1", 0, 2000);
            exit();
        }
        $cfg_ml = new MemberLogin();
        $rs = $cfg_ml->CheckUser($mobilephone, $pwd);
        if ($rs == 0) {
            $request_array = array("stat" => "请检查输入的用户名");
            echo json_encode($request_array);
            exit();
        } else if ($rs == -1) {
            $request_array = array("stat" => "密码错误");
            echo json_encode($request_array);
            //ShowMsg("密码错误！", "index.php", 0, 2000);
            exit();
        } else if ($rs == -2) {
            $request_array = array("stat" => "管理员帐号不允许从前台登录");
            echo json_encode($request_array);
            //ShowMsg("管理员帐号不允许从前台登录！", "index.php", 0, 2000);
            exit();
        } else if ($rs == 1) {

            //$cfg_ml->DelCache($cfg_ml->M_ID);
            //echo ($gourl);
            if (empty($gourl) || preg_match("#action|_do#i", $gourl)) {
                $gourl = "index.php";
                //ShowMsg("成功登录，5秒钟后转向系统主页...", "index.php", 0, 2000);
            } else {
                $gourl = str_replace('^', '&', $gourl);
                //ShowMsg("成功登录，现在转向指定页面...", $gourl, 0, 2000);
            }
            // 清除会员缓存
            $request_array = array("stat" => "成功登录", "gourl" => "$gourl",);
            echo json_encode($request_array);

            exit();
        }

    } else {

        $request_array = array("stat" => "1000", "mobilephone" => $mobilephone, "pwd" => $pwd);
        echo json_encode($request_array);
        //$dopost = 'reg';//如果此电话在此公司未注册过,则注册
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>会员登录</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body >
<?php if ($dopost == "") {
    ?>
    <div class="lock-word animated fadeInDown">
    </div>
    <div class="middle-box text-center lockscreen animated fadeInDown">
        <div>
            <div class=" text-center text-warning">
                <b> 登录</b>
            </div>
            <form id="formphone" class="m-t" role="form" action="">
                <input type="hidden" name="dopost" value="next"/>
                <input type="hidden" name="gourl" id="gourl" value="<?php echo $gourl ?>"/>

                <div class="form-group">
                    <!--<div class="input-group m-b"><span class="input-group-addon">@</span>
                        <input type="text" placeholder="用户名" class="form-control">
                    </div>-->

                    <input type="number" class="form-control" name="mobilephone" id="mobilephone" placeholder="手机号"
                           value="">
                </div>
                <div class="form-group" style="margin-top: 20px">
                    <input type="password" class="form-control" name="pwd" id="pwd" placeholder="密码" value="">
                </div>
                <button type="submit" class="btn btn-primary block full-width">登录</button>
                无需注册,快速登录
            </form>
            <div class="text-right">
                <a href="#">取回密码</a>
            </div>
            <!-- <div class="m-b-md">
                 <img alt="image" class="img-circle circle-border" src="img/a1.jpg">
             </div>-->
        </div>
    </div>
<?php }
if ($dopost == "reg") {
    $_SESSION[$mobilephone] = "";//清空
    ?>
    <div class="lock-word animated fadeInDown">
    </div>
    <div class="middle-box text-center lockscreen animated fadeInDown">
        <div>
            <div class=" text-center text-warning">
                <b>验证手机</b>
            </div>
            <form id="formreg" class="m-t" role="form" action="">
                <input type="hidden" name="dopost" value="regsave"/>
                <input type="hidden" name="gourl" id="gourl" value="<?php echo $gourl ?>"/>

                <div class="form-group">
                    <input type="text" class="form-control" disabled="" value="<?php echo $mobilephone ?>">
                    <input type="hidden" name="mobilephone" id="mobilephone" value="<?php echo $mobilephone ?>">
                </div>

                <div class="form-group">
                    <button class="btn  btn-primary" style="float: right" type="button" onclick="settime(this,0,'<?php echo urlencode("注册验证码")?>')">
                        获取验证码
                    </button>
                    <input type="number" class="form-control" style="width: 45%" name="checkCode" id="checkCode"
                           placeholder="点击右侧">
                </div>
                <input type="hidden" class="form-control" name="pwd" id="pwd" value="<?php echo $pwd; ?>">
                <button type="submit" class="btn btn-primary block full-width">完成验证</button>
            </form>
        </div>
    </div>
    <div class=" text-center"> 由于您是第一次登录 请验证手机号</div>

<?php } ?>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="js/sendPhoneMSG.js"></script>
<script>
    $().ready(function () {
        $("#formphone").validate({
            rules: {
                mobilephone: {required: !0, minlength: 11, isMobile: !0},
                pwd: {required: !0, minlength: 6}
            },
            messages: {
                mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "手机号应以13/14/15/17/18开头"},
                pwd: {required: "请填写密码", minlength: "密码必须5个字符以上"}
            }, submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "login.php",
                    data: {
                        dopost: "next",
                        mobilephone: $("#mobilephone").val(),
                        pwd: $("#pwd").val(),
                        gourl: "<?php echo $gourl;?>"
                    },
                    dataType: 'json',
                    async: false,//这个执行完才执行下面的
                    success: function (result) {
                        if (result.stat == "成功登录") {
                            layer.msg('成功登录', {
                                time: 10000 //20s后自动关闭
                            });
                            window.location.href = result.gourl;
                        } else if (result.stat == 1000) {
                            var mobilephone = result.mobilephone;
                            var pwd = result.pwd;
                            window.location.href = "login.php?dopost=reg&mobilephone=" + mobilephone + "&pwd=" + pwd;
                        } else {
                            layer.msg(result.stat, {
                                time: 10000 //20s后自动关闭
                            });
                        }
                    }
                });
            }

        })
        $("#formreg").validate({
            rules: {
                checkCode: {required: !0, minlength: 4, maxlength: 4}
            },
            messages: {
                checkCode: {required: "请填写短信中的四位数字", minlength: "验证码应为4个数字", maxlength: "验证码应为4个数字"}
            }, submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "login.php",
                    data: {
                        dopost: "regsave",
                        mobilephone: $("#mobilephone").val(),
                        pwd: $("#pwd").val(),
                        checkCode: $("#checkCode").val()
                    },
                    dataType: 'json',
                    async: false,//这个执行完才执行下面的
                    success: function (result) {
                        if (result.stat == -1) {
                            layer.msg('未获取手机验证码', {
                                time: 10000 //20s后自动关闭
                            });
                        } else if (result.stat == -2) {
                            layer.msg('手机号已经被注册', {
                                time: 10000 //20s后自动关闭
                            });
                        } else if (result.stat == -3) {
                            layer.msg('手机验证码填写错误', {
                                time: 10000 //20s后自动关闭
                            });
                        } else {
                            layer.msg('成功登录', {
                                time: 10000 //20s后自动关闭
                            });
                            window.location.href = result.gourl;
                        }
                    }
                });
            }
        })

    });
</script>


</body>
</html>