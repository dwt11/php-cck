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
//CheckRank();
if (empty($goodsid)) $goodsid = '';
if (empty($lyhtid)) $lyhtid = '';
if (empty($goodsid)&&empty($lyhtid)) {
    showMsg("非法参数", "index.php");
    exit;
}


//------------------------商品信息，
//线路
if($goodsid!="")$query = "SELECT title,body FROM #@__goods_addon_lycp
          LEFT JOIN #@__lyht ON  #@__goods_addon_lycp.lyhtid=#@__lyht.id
          WHERE   #@__goods_addon_lycp.goodsid='$goodsid'";
//直通车
if($lyhtid!="")$query = "SELECT title,body FROM  #@__lyht  WHERE    id='$lyhtid'";

//dump($query);

$arcRow = $dsql->GetOne($query);
if (!is_array($arcRow)) {
    ShowMsg("读取档案基本信息出错!" );
    exit();
}
$title = $arcRow["title"];
$body = $arcRow["body"];


//dump($appttime);
//if (empty($appttime)) $appttime = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <title>告知书</title>
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
<div id="test" >

    <div class="text-center"><?php echo $title; ?></div>
    <?php echo $body; ?>
</div>
<div class="clearfix" style="margin-bottom: 30px"></div>
<div class="bodyButtomTab" style="padding: 5px">
    <button class='btn btn-primary' id='agree' type='button' >关闭</button>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $('#agree').click(function () {
        parent.$("body").unbind("touchmove");//恢复父页面滚动
        parent.layer.closeAll('iframe');
    })

</script>
</body>
</html>

