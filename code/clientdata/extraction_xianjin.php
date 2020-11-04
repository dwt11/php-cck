<?php
require_once('../config.php');
if (empty($id)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = trim(preg_replace("#[^0-9]#", '', $id));
if (empty($dopost)) $dopost = '';


//订单信息
$query = "SELECT e.*,o2.realname,o2.mobilephone from
          #@__clientdata_extractionlog e
          LEFT JOIN #@__client o2 ON e.clientid=o2.id
        where e.id='$id' ";
$rowOrder = $dsql->GetOne($query);
//dump($query);

$realname = $rowOrder['realname'];//提现金额
$paydesc_value = $rowOrder['paydesc'];//
$mobilephone = $rowOrder['mobilephone'];//提现金额
$paynum = $rowOrder['jbnum']/100;//提现金额


if ($dopost == 'save') {
    $payment_no = "现金线下付款";//订单号==商户订单号+微信订单号
    $userid = preg_replace("#[^0-9]#", '', $GLOBALS['CUSERLOGIN']->getUserId());
    $pwd = substr(md5($pwd), 5, 20);
    $chRow = $dsql->GetOne("SELECT id FROM `#@__sys_admin`  WHERE  id='$userid' and pwd='$pwd' ");
    if (!is_array($chRow)) {
        $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
        ShowMsg("密码错误,操作失败！", $$ENV_GOBACK_URL);
        exit();
    }
    $payment_time = time();//付款时间
    $payoperatorid = $CUSERLOGIN->userID;//操作人
    //付款成功
    $query = "UPDATE `#@__clientdata_extractionlog` SET status='5' ,payment_no='$payment_no',payment_time='$payment_time',payoperatorid='$payoperatorid' WHERE id='$id'";
    $dsql->ExecuteNoneQuery($query);
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("操作成功！", $$ENV_GOBACK_URL);
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>

        <div class="form-group">
            <div class="col-sm-2">
                会员信息：<?php echo $realname . " " . $mobilephone; ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                提现金额：<?php echo $paynum; ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                    <input type="text"  class="form-control pword m-b" placeholder="登录密码" onfocus="this.type='password'" autocomplete="off"  name="pwd" id="pwd"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    <textarea  name="paydesc" id="paydesc" class="form-control" placeholder="备注"  rows="5"><?php echo $paydesc_value; ?></textarea>
                </div>


        </div>

        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">确认支付</button>
            </div>
        </div>

    </form>
</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
    <script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
    <!--验证用-->
    <script>
        $().ready(function () {
            $("#form1").validate({
                rules: {
                    pwd: {required: !0, minlength: 6}
                },
                messages: {
                    pwd: {required: "请填写密码", minlength: "密码必须6个字符以上"}
                }
            })
        });
</script>
</body>
</html>


