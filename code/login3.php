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

$msg = "";

//登录检测
if ($dopost == 'login') {
    $GLOBALS['CUSERLOGIN'] = new userLogin();
    if (!empty($userName) && !empty($pwd)) {
        $res = $GLOBALS['CUSERLOGIN']->checkUser($userName, $pwd);
        $GLOBALS['CUSERLOGIN']->keepUser();
        //dump($res) ;
        //success
        if ($res == 1) {//登录成功
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


/*//获取系统中用户的个数,如果个数大于5则输出input供用户填写,小于5的话,直接下拉select供用户选择
$dsql = $GLOBALS['dsql'];
$usernumb = 0;
$sql = " SELECT count(id) as dd   FROM #@__sys_admin";
$userrow = $dsql->GetOne($sql);
if (is_array($userrow)) {
    $usernumb = $userrow['dd'];
}
if ($usernumb < 1) {
    $optionarr = "";
    $dsql->SetQuery("SELECT userName  FROM `#@__sys_admin` ORDER BY logintime desc");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $optionarr .= "<option value='" . $row['userName'] . "'>" . $row['userName'] . "</option>\r\n";
    }
    $userNameFrom = "<select name='userName' style='width:135px'>\r\n" . $optionarr . "</select>\r\n";
}*/
$userNameFrom = "<input type=\"text\" class=\"form-control uname\" placeholder=\"用户名或手机号\"   name='userName' id='userName'/>";

?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <title><?php echo $cfg_softname . " " . $cfg_version; ?></title>
    <link href="ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="ui/css/animate.min.css" rel="stylesheet">
    <link href="ui/css/style.min.css" rel="stylesheet">
    <link href="ui/css/login.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="middle-box text-center loginscreen  animated fadeInDown">
    <div>

        <h1 class="logo-name"></h1> <!--<img src="images/bwlogo.png" width="300" height="300"/>-->

        <h3>欢迎使用 <?php echo $cfg_softname; ?></h3>

        <form name="form1" id="form1">
            <input type="hidden" name="gotopage" id="gotopage" value="<?php if (!empty($gotopage)) echo $gotopage; ?>"/>

            <div class="form-group">
                <?php echo $userNameFrom; ?>
            </div>
            <div class="form-group">
                <input type="password" class="form-control pword m-b" placeholder="密码" name="pwd" id="pwd"  />
            </div>
            <button class="btn btn-success btn-block">登录</button>
            <br>

            <p class="text-muted text-center"></b></p>
        </form>
    </div>
</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    if (window.top !== window.self) {
        window.top.location = window.location;
    }
</script>
<script>
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
                        gotopage: $("#gotopage").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        var obj = JSON.parse(result); //由JSON字符串转换为JSON对象
                        var tag = obj.tag;
                        var gotopage = obj.gotopage;
                        if (tag == 1) {
                            if (gotopage != "") {
                                layer.msg('成功登录，正在跳转', {
                                    time: 1000 //20s后自动关闭
                                });
                                window.parent.location.href = gotopage;
                            } else {
                                layer.msg('成功登录，正在跳转到系统首页', {
                                    time: 5000 //20s后自动关闭
                                });
                                window.parent.location.href = 'main.php';
                            }
                        } else if (tag == -1) {
                            layer.msg('输入的用户名不存在', {
                                time: 1000 //20s后自动关闭
                            });
                        } else if (tag == -2) {
                            layer.msg('输入的密码错误', {
                                time: 1000 //20s后自动关闭
                            });

                        }
                        if (tag == "") {
                            layer.msg('用户和密码没填写完整', {
                                time: 1000 //20s后自动关闭
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
