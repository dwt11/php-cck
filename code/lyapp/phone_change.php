<?php
require_once("include/config.php");

if (empty($dopost)) $dopost = '';
if (empty($gourl)) $gourl = '';
CheckRank();
//dump($CLIENTID);
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'next') {


    $checkCode_str = ValidatePhoneCode($mobilephone, $checkCode);
    echo $checkCode_str;
    exit;
}

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
    echo "更换成功";
    exit;
}

//获取客户信息
$questr = "SELECT mobilephone FROM `#@__client`  where  id='$CLIENTID'";
$row = $dsql->GetOne($questr);


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>手机号码更换</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/animate.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("index_heard.php");


    $titleTip = '验证旧号码';
    if ($dopost != "")$titleTip = '验证新号码'
    ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-8 text-left lefttext">
                更换手机
            </div>
            <div class="col-xs-4 text-right">
                <i class="fa fa-info-circle   "></i><?php echo $titleTip; ?>
            </div>
        </div>
    </div>


    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">

            <?php
            if ($dopost == '') {
                ?>

                <form id="formold" class="m-t" role="form" action="">
                    <div class="form-group">
                        <input name="mobilephone" id="mobilephone" type="number" class="form-control" value="<?php echo $row["mobilephone"] ?>" placeholder="手机号码" disabled>
                    </div>
                    <div class="form-group">
                        <button class="btn  btn-primary" style="float: right" type="button" onclick="settime(this,<?php echo $CLIENTID; ?>,'<?php echo urlencode("业务验证码") ?>')">获取验证码</button>
                        <input type="number" class="form-control" style="width: 45%" id="checkCode" name="checkCode" placeholder="点击右侧">
                    </div>
                    <button type="submit" class="btn btn-primary block full-width">下一步</button>
                </form>

            <?php }
            if ($dopost == 'new') {
                ?>
                <form id="formnew" class="m-t" role="form" action="">
                    <div class="form-group">
                        <input name="mobilephone" id="mobilephone" type="number" class="form-control" value="" placeholder="新的手机号码">
                    </div>
                    <input name="mobilephone_old" id="mobilephone_old" value="<?php echo $row["mobilephone"] ?>" type="hidden">
                    <input name="clientid" id="clientid" value="<?php echo $CLIENTID ?>" type="hidden">
                    <div class="form-group">
                        <button class="btn  btn-primary" style="float: right" type="button" onclick="settime(this,<?php echo $CLIENTID; ?>,'<?php echo urlencode("业务验证码") ?>')">获取验证码</button>
                        <input type="number" class="form-control" style="width: 45%" id="checkCode" name="checkCode" placeholder="点击右侧">
                    </div>
                    <button type="submit" class="btn btn-primary block full-width">完成验证</button>
                </form>
            <?php }
            ?>
        </div>
    </div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="js/sendPhoneMSG.js"></script>
<script src="phone_change.js"></script>
</body>
</html>
