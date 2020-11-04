<?php
require_once("include/config.php");
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
    <link href="css/style.css" rel="stylesheet" media="screen">
    <link href="css/index.css" rel="stylesheet" media="screen">
</head>
<body>


<div class="main">
    <?php include("index_heard.php"); ?>
    <div class="index-banner proportion" F="0.40625">
        <ul class="swiper-wrapper">
            <?php
            $query3 = "SELECT goodsname,senddate,litpic,id  FROM #@__goods WHERE  FIND_IN_SET('h', flag)  ORDER BY pubdate desc limit 0,4  ";
            $dsql->SetQuery($query3);
            $dsql->Execute("161217");
            $gotime = "";
            while ($row1 = $dsql->GetArray("161217")) {
                $id = $row1["id"];
                $goodsname = $row1["goodsname"];
                $senddate = $row1["senddate"];

                $photo = $row1["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";
                echo "<li class=\"swiper-slide\">
                        <a href=\"goods/goods_view.php?id=$id\" ><img src=\"$photo\" title=\"$goodsname\"></a>
                    </li>";
            }
            ?>
        </ul>
        <div class="index-pagination"></div>
        <div class="banner-title"><p></p></div>
    </div>

    <div class="list-group-item-border navbar">
        <ul class="navbar-list">
            <li>
                <a href="goods/goods_list.php?typeid=1">
                    <div class="menulistbox box1">
                        <div class=" iclolr fa fa-credit-card fa-2x ">
                        </div>
                    </div>
                    会员卡
                </a>
            </li>
            <li>
                <a href="goods/goods_view.php?id=2">
                    <div class="menulistbox box2">
                        <div class=" iclolr fa fa-gg-circle fa-2x ">
                        </div>
                    </div>
                    合伙人
                </a>
            </li>
            <li>
                <a href="goods/goods_list.php?typeid=5">
                    <div class="menulistbox box4">
                        <div class=" iclolr fa fa-bus fa-2x ">
                        </div>
                    </div>
                    车辆租赁
                </a>
            </li>
            <li>
                <a href="archives/archives_list.php">
                    <div class="menulistbox box5">
                        <div class=" iclolr fa fa-volume-up fa-2x ">
                        </div>
                    </div>
                    新闻公告
                </a>
            </li>
            <li>
                <a href="goods/goods_list.php?typeid=3">
                    <div class="menulistbox box3">
                        <div class=" iclolr fa fa-star fa-2x ">
                        </div>
                    </div>
                    直通车线路
                </a>
            </li>
            <li>
                <a href="goods/goods_list.php?typeid=9">
                    <div class="menulistbox box6">
                        <div class=" iclolr fa fa-street-view fa-2x ">
                        </div>
                    </div>
                    周边旅游
                </a>
            </li>
            <li>
                <a href="goods/goods_list.php?typeid=10">
                    <div class="menulistbox box7">
                        <div class=" iclolr fa fa-map-marker fa-2x ">
                        </div>
                    </div>
                    境内旅游
                </a>
            </li>
            <li>
                <a href="goods/goods_list.php?typeid=11">
                    <div class="menulistbox box8">
                        <div class=" iclolr fa fa-plane fa-2x ">
                        </div>
                    </div>
                    境外旅游
                </a>
            </li>
        </ul>
    </div>


    <ul class="list-group list-group-plus-nomargintop ">
        <li class="list-group-item1 ">
            <span class="pull-left text-warning " style="margin-right:  10px"><i class="fa fa-random"></i> 新闻头条</span>
            <div id="scrollDiv" class="scrollDiv">
                <ul>
                    <?php
                    $query3 = "SELECT title,id,pubdate  FROM #@__archives where  FIND_IN_SET('h', flag) ORDER BY pubdate desc limit 0,4  ";
                    $dsql->SetQuery($query3);
                    $dsql->Execute("1612178899");
                    $gotime = "";
                    while ($row1 = $dsql->GetArray("1612178899")) {
                        $id = $row1["id"];
                        $title = $row1["title"];
                        $pubdate = date('m月d日', $row1["pubdate"]);
                        echo " <li>
                                <a href=\"archives/archives_view.php?aid=$id\" >$title [$pubdate]</a>
                         </li>
                            ";
                    }
                    ?>
                </ul>
            </div>
            <div class="clearfix  "></div>
        </li>
    </ul>

    <div class="list-group list-group-index">
        <li class="list-group-item-index text-center list-group-item-border text-danger">
            <i class="fa fa-gg"></i> 热门线路
            <span class="pull-right" style="margin-right: 10px"> <a href="goods/goods_list_day.php"> <b>每日发车表</b> </a></span>
        </li>
        <ul class="rowlist">
            <?php
            $query3 = "SELECT #@__goods.id as goodsid,litpic,goodscode,goodsname,price,#@__line.gotime,#@__line.tmp FROM `#@__line` 
              INNER JOIN #@__goods ON  #@__goods.id=#@__line.goodsid
              INNER JOIN #@__goods_addon_lycp lycp on lycp.goodsid=#@__goods.id
               WHERE #@__line.islock=1 AND #@__goods.`status`='0'  AND `isOnlyAdminDisplay`='0'     
              AND (
                        (
                                UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
                        )   
                        
                  )
                GROUP BY goodsid
                ORDER BY x_goods.flag DESC,#@__goods.weight ASC,  gotime ASC
                LIMIT 0,6";
            $dsql->SetQuery($query3);
            //dump($query3);
            $dsql->Execute("170127");
            while ($row1 = $dsql->GetArray("170127")) {
                $goodsid = $row1["goodsid"];

                $goodsname = $row1["goodsname"];
                if (strlen($goodsname) > 30) $goodsname = cn_substr_utf8($goodsname, 18) . "...";

                $photo = $row1["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";

                $gotime = $row1["gotime"];
                if ($row1["tmp"] == "临时") {
                    //临时路线
                    $gotime = date('m月d日', $gotime);
                } else {
                    $gotime = '每日 ' . date('H:i', $gotime);
                }

                echo "<li>
                                    <a href=\"goods/goods_view.php?id=$goodsid\">
                                    <img src=\"$photo\" data-original=\"$photo\" alt=\"$goodsname\" width='33.33%'>
                                    </a>
                    
                                    <h3><a href=\"goods/goods_view.php?id=$goodsid\">$goodsname</a></h3>
                    
                                    <p>$gotime 发车</p>
                                </li>
                    ";
            }
            ?>
        </ul>

    </div>


    <div class="list-group list-group-index">
        <li class="list-group-item-index text-center list-group-item-border  text-danger">
            <i class="fa fa-newspaper-o"></i> <a href="/lyapp/archives/archives_list.php">新闻公告</a>
        </li>
        <ul class="newslist">
            <dt>
                <ul>
                    <?php
                    $query = "SELECT  arc.id,arc.typeid,arc.title,arc.senddate,arc.litpic
                        FROM `#@__archives` arc
                         WHERE arc.issend > -2
                          ORDER BY   id DESC limit 0,5";
                    $dsql->SetQuery($query);
                    $dsql->Execute("170127145");
                    while ($row1 = $dsql->GetArray("170127145")) {
                        $id = $row1["id"];
                        $title = $row1["title"];
                        //if (strlen($title) > 50) $title = cn_substr_utf8($title, 50) . "...";
                        $senddate = $row1["senddate"];

                        $photo = $row1["litpic"];
                        if ($photo == "") $photo = "/images/arcNoPic.jpg";

                        echo "
                            <li>
                                <a href=\"archives/archives_view.php?aid=$id\"><img src=\"$photo\"  data-original=\"$photo\" alt=\"$title\"></a>
                                <h3><a href=\"archives/archives_view.php?aid=$id\">$title</a></h3>
                                <p></p>
                            </li>
                    ";
                    }
                    ?>
                </ul>
            </dt>
        </ul>

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
<script src="/lyapp/js/quickButton.js?171008"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script>
    function AutoScroll(obj) {
        $(obj).find("ul:first").animate({
            marginTop: "-25px"
        }, 500, function () {
            $(this).css({marginTop: "0px"}).find("li:first").appendTo(this);
        });
    }

    $(document).ready(function () {
        setInterval('AutoScroll("#scrollDiv")', 2000);
    });
</script>
</body>
</html>