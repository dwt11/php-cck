<?php
/**
 *
 */

require_once(dirname(__FILE__) . '/include/common.inc.php');
$t1 = ExecTime();
//echo DWTINC;
require_once(DWTINC . '/userlogin.class.php');
if (empty($dopost)) $dopost = '';
if (empty($gotopage)) $gotopage = '';
require_once('dwtkey.php');

$msg = "";
//登录检测
if ($dopost == 'login') {
    //PutCookie("DWTLoginTime", time(), $this->M_KeepTime);

    $GLOBALS['CUSERLOGIN'] = new userLogin();
    if (!empty($userName) && !empty($pwd)) {
        $res = $GLOBALS['CUSERLOGIN']->checkUser($userName, $pwd);
        $GLOBALS['CUSERLOGIN']->keepUser();
        //dump($res) ;
        //success
        if ($res != 1 && $cfg_dwt_log == 'Y') {
            $s_userip_log = GetIP();
            //记录登录失败
            $inquery = "INSERT INTO `#@__sys_log`(adminid,filename,method,query,cip,dtime)
				 VALUES ('','login.php','POST','{$userName}-{$pwd}','{$s_userip_log}','" . time() . "');";
           // dump($inquery);
            $dsql->ExecuteNoneQuery($inquery);

        }
        if ($res == 1) {//登录成功
            if ($saveusername == "true") {
                PutCookie("DWTLoginUserName", $userName, 3600 * 24 * 30);
            } else {
                DropCookie('DWTLoginUserName');
            }
            $return_array = array('tag' => $res, 'gotopage' => $gotopage);
            echo json_encode($return_array);
            exit;
        } //error
        else if ($res == -1) {
            //输入的用户名不存在
            $return_array = array('tag' => $res, 'gotopage' => "");
            echo json_encode($return_array);
            exit;
        } else {
            //密码错误
            $return_array = array('tag' => -2, 'gotopage' => "");
            echo json_encode($return_array);
            exit;
        }
    } //password empty
    else {
        //未输入用户名和密码
        echo "";
        exit;
    }
}


//如果用户未登录 则提示
if ($msg == "nologin") {
    $redmsg = '<div class=\'tips\'>请填写用户名和密码登录</div>';
} else {
    $redmsg = '';
}


$userName = GetCookie("DWTLoginUserName");
$checked = "";
if ($userName != "") $checked = " checked ";
$userNameFrom = "<input type=\"text\" class=\"form-control uname\" placeholder=\"用户名或手机号\" value='$userName'  name='userName' id='userName'/>";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <title><?php echo $cfg_softname . " " . $cfg_version; ?></title>
    <link href="ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="ui/css/animate.min.css" rel="stylesheet">
    <link href="ui/css/style.min.css" rel="stylesheet">
    <link href="ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="ui/css/login.min.css" rel="stylesheet">
</head>

<body class="signin">
<div class="signinpanel">

    <div class="row">
        <div class="col-sm-7">
            <div class="signin-info">
                <div class="logopanel m-b">
                    <h1> 旅游管理平台</h1>
                </div>
                <div class="m-b"></div>
                <h4>欢迎使用 </strong></h4>
                <ul class="m-b">
                    <!--  <li><i class="fa fa-arrow-circle-o-right m-r-xs"></i> 优势一</li>
                      <li><i class="fa fa-arrow-circle-o-right m-r-xs"></i> 优势二</li>
                      <li><i class="fa fa-arrow-circle-o-right m-r-xs"></i> 优势三</li>
                      <li><i class="fa fa-arrow-circle-o-right m-r-xs"></i> 优势四</li>
                      <li><i class="fa fa-arrow-circle-o-right m-r-xs"></i> 优势五</li>-->
                </ul>

            </div>
        </div>
        <div class="col-sm-5">
            <form name="form1" method="post" id="form1">
                <h4 class="no-margins">登录</h4>

                <p class="m-t-md"></p>

                <?php echo $userNameFrom; ?>

                <input type="password" class="form-control pword m-b" placeholder="密码" onfocus="this.type='password'" value="qcx101703" required="" name="pwd" id="pwd"/>
                <label class="no-padding checkbox i-checks"><input name="saveusername" id="saveusername" type="checkbox" <?php echo $checked ?>>记住用户名</label>

                <input type="hidden" name="gotopage" value="<?php if (!empty($gotopage)) echo $gotopage; ?>"/>
                <input type="hidden" name="dopost" value="login"/>
                <a href=""></a>
                <button class="btn btn-success btn-block">登录</button>


            </form>
        </div>
    </div>
    <div class="signup-footer">
        <div class="pull-left">
            &copy; <?php echo $cfg_softname . " " . $cfg_version; ?>
        </div>
    </div>
</div>
<script src="ui/js/jquery.min.js"></script>
<script src="ui/js/bootstrap.min.js"></script>
<script src="ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="ui/js/plugins/layer/layer.min.js"></script>
<script src="ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });

    if (window.top !== window.self) {
        window.top.location = window.location;
    }

    $().ready(function () {
        $("#form1").validate({
            rules: {
                userName: {required: true},
                pwd: {required: true}
            },
            messages: {
                userName: {required: "请输入用户名或手机号"},
                pwd: {required: "请输入密码"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "login.php",
                    data: {
                        dopost: "login",
                        userName: $("#userName").val(),
                        pwd: $("#pwd").val(),
                        gotopage: $("#gotopage").val(),
                        saveusername: $('#saveusername').is(':checked')
                    },
                    dataType: 'html',
                    success: function (result) {
                        var obj = JSON.parse(result); //由JSON字符串转换为JSON对象
                        var tag = obj.tag;
                        var gotopage = obj.gotopage;
                        if (tag == 1) {
                            if (gotopage != "") {
                                layer.msg('成功登录，正在跳转', {
                                    shade: 0.5, //开启遮罩
                                    time: 2000 //20s后自动关闭
                                }, function () {
                                    window.parent.location.href = gotopage;
                                });


                            } else {
                                layer.msg('成功登录，正在跳转到系统首页', {
                                    shade: 0.5, //开启遮罩
                                    time: 2000 //20s后自动关闭
                                }, function () {
                                    window.parent.location.href = 'main.php';
                                });

                            }
                        } else if (tag == -1) {
                            layer.msg('输入的用户名不存在', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            });
                        } else if (tag == -2) {
                            layer.msg('输入的密码错误', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            });

                        }
                        if (tag == "") {
                            layer.msg('用户和密码没填写完整', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            });
                        }

                    }
                });
            }
        });

    });


</script>
</body>

</html>


<?php
$t2 = ExecTime();
//echo $t2-$t1;?>
