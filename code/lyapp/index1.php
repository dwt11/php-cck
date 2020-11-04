<?php
/**
 * @version        $Id: index.php 1 8:24 2010年7月9日
 * @package        DWTCMS.Member

 * @license        http://help.DWTcms.com/usersguide/license.html
 * @link           http://www.DWTcms.com
 */
require_once("include/config.php");

//dump($cfg_ml);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>免费旅游</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" media="screen">
    <link href="css/index.css" rel="stylesheet" media="screen">
</head>

<body>

<div class="main">
    <?php include("index_heard1.php"); ?>

    <div class="index-banner proportion" F="0.40625">

        <ul class="swiper-wrapper">
            <?php
            $query3 = "SELECT goodsname,senddate,litpic,id  FROM #@__goods where  FIND_IN_SET('h', flag) ORDER BY pubdate desc limit 0,4  ";
            $dsql->SetQuery($query3);
            $dsql->Execute("161217");
            $gotime = "";
            while ($row1 = $dsql->GetArray("161217")) {
                $id = $row1["id"];
                $goodsname = $row1["goodsname"];
                $senddate = $row1["senddate"];

                $photo =  $row1["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";
                echo "<li class=\"swiper-slide\">
                        <a href=\"goods/goods_view.php?id=$id\" ><img data-original=\"$photo\" title=\"$goodsname\"></a>
                    </li>";
            }
            ?>


        </ul>

        <div class="index-pagination"></div>

        <div class="banner-title"><p></p></div>

    </div>


    <div class="tm_div" style="margin-top: 10px">
        <div class="tm_pro">


            <?php
            $query3 = "SELECT goodsname,senddate,litpic,id,`desc` FROM #@__goods goods LEFT JOIN #@__goods_addon_ztc ztc on ztc.goodsid=goods.id where  id=1  ";
            $row2 = $dsql->GetOne($query3);

            $id = $row2["id"];
            $goodsname = $row2["goodsname"];
            $desc = strip_tags($row2["desc"]);
            if (strlen($desc) > 200) $desc = cn_substr_utf8($desc, 199) . "...";
            $senddate = $row2["senddate"];

            $photo = $row2["litpic"];
            if ($photo == "") $photo = "/images/arcNoPic.jpg";
            echo "<a href=\"goods/goods_view.php?id=$id\"><img data-original=\"$photo\" width=\"100%\"></a>
                <div class=\"tm_type\"><a href=\"onsale_0.html\"></a></div>
                <div class=\"tm_title\">
                    <div class=\"tm_title_con\">
                        <div class=\"tm_title_con_1\">
                            <a href=\"goods/goods_view.php?id=$id\">$goodsname     </a>
                        </div>
                        <div class=\"tm_title_con_2\">
                            <a href=\"goods/goods_view.php?id=$id\">$desc     </a>
                        </div>
                    </div>
                </div>";

            ?>


        </div>
    </div>


    <div class="box">

        <div class="hd">

            <ins></ins>

            <div class="over">

                <a href="android/danji.html" class="more">更多</a>


                <h3>旅游线路</h3>

            </div>

        </div>

        <div id="con">
            <?php
            $query3 = "SELECT goodsname,senddate,litpic,id,price,`desc`  FROM #@__goods goods
                LEFT JOIN #@__goods_addon_lycp lycp on lycp.goodsid=goods.id
                where  typeid in (3,4) ORDER BY senddate desc limit 0,4  ";
            $dsql->SetQuery($query3);
            $dsql->Execute("16121701");
            while ($row3 = $dsql->GetArray("16121701")) {
                $id = $row3["id"];
                $goodsname = $row3["goodsname"];
                $desc = strip_tags($row3["desc"]);
                if (strlen($desc) > 200) $desc = cn_substr_utf8($desc, 199) . "...";
                $price = $row3["price"];
                $senddate = GetDateNoYearMk($row3["senddate"]);

                $photo =  $row3["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";

                echo "<div class=\"tm_div\">
                            <div class=\"tm_pro\">
                                <a href=\"/lyapp/goods/goods_view.php?id=$id\"><img data-original=\"$photo\" width=\"100%\"></a>
                                <div class=\"tm_type\"><a href=\"onsale_0.html\"></a></div>
                                <div class=\"tm_title\">
                                    <div class=\"tm_title_con\">
                                        <div class=\"tm_title_con_1\">
                                            <a href=\"/lyapp/goods/goods_view.php?id=$id\">$goodsname     </a>
                                        </div>
                                        <div class=\"tm_title_con_2\">
                                            <a href=\"/lyapp/goods/goods_view.php?id = $id\">$desc</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class=\"tm_price\">

                                <div class=\"tm_price_1\">&nbsp;￥<font style=\"font-size:20px;\">$price</font></div>

                                <div class=\"tm_price_2\"><span class=\"servertime\">$senddate</span></div>

                            </div>

                        </div>";
            }
            ?>
        </div>


    </div>



<?php include("index_foot.php"); ?>

</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/lyapp/js/swiper-2.0.min.js"></script>
<script src="/lyapp/js/swiper.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>
<script src="/lyapp/js/quickButton.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>


</body>
</html>