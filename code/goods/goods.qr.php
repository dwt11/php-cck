<?php
require_once("../config.php");
//只 使用
?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg" >
     <div class="row" align="center">


            <?php
            echo "<h2>$goodsname</h2>";
            //生成二维码
            require_once('../include/qrcode.class.php');
            $qrpng_name = '..' . getUploadFileAdd(17) . 'goodscode/'.$id.'.png';
            if (!file_exists($qrpng_name)) {
                $params = array();
                //$params['data'] = 'http://' . $_SERVER['SERVER_NAME'] . '/lyapp/ly_carcard_show.php?id=1&u=' . $fields['clientid'] . "&did=17";//生成的连接
                $params['data'] = "http://" . $_SERVER['SERVER_NAME'] . "/lyapp/goods/goods_view.php?id={$id}";//生成的连接
                $params['size'] = 6;
                $params['savename'] = $qrpng_name;//二维码存储地址
                $qrcode = new DwtQrcode;
                $eeeee = $qrcode->generate($params);
            }
            ?>
            <br><img src="<?php echo $qrpng_name ?>" style="width: 200px;height: 200px" > </span>


 </div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>

<!--表格-->


<SCRIPT src="../ui/js/jquery.lazyload.js" type=text/javascript></SCRIPT>
<SCRIPT src="../ui/js/jquery.lazyload.plus.js" type=text/javascript></SCRIPT>
<script type="text/javascript" charset="utf-8">


    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>

</body>
</html>