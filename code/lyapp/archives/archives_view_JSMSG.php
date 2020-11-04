<?php

require_once(dirname(__FILE__) . "/../include/config.php");


/*---------------------------------------------------
 *  乘车卡信息,只做为预约时的联系人信息使用,每个卡算做一个商品
 *
 * 优惠信息 根据会员类型来计算,每个商品都按优惠规则来
 *
 *
 *
 *
 *
 *
 *
 * */
CheckRank();
if (empty($id)) $id = '';
if (empty($id) ) {
    showMsg("非法参数");
    exit;
}




$query = "SELECT body FROM #@__archives_addonarticle WHERE   archivesid=$id";

$arcRow = $dsql->GetOne($query);
if (!is_array($arcRow)) {
    ShowMsg("读取档案基本信息出错!");
    exit();
}
$body = $arcRow["body"];


//dump($appttime);
//if (empty($appttime)) $appttime = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <title>旅游合同</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
    <style type="text/css">
        div {
        }

        #test {
            min-height: 250px;
            overflow-y: auto;
            max-height: 300px;
            border-radius: 5px;
            border: 1px solidred;
            padding: 10px;
        }
    </style>
</head>
<body>
<div id="test">
    <?php echo $body; ?>
</div>
<div class="clearfix" style="margin-bottom: 30px"></div>
<div class="bodyButtomTab" style="padding: 5px">
    <button class='btn btn-white' id='cancel' type='button'>关闭</button>

</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $('#cancel').click(function () {
        parent.islyht = false;
        parent.$("body").unbind("touchmove");
        parent.layer.closeAll('iframe');
    })


</script>
</body>
</html>

