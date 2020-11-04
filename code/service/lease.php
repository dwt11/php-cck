<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


if (empty($keyword)) $keyword = '';
if (empty($goodsid)) $goodsid = '';
$whereSql = " WHERE #@__order.isdel=0 AND  #@__order.sta=1 ";

$startdate = isset($startdate) ? $startdate : "";
if ($startdate != "") {
    $startdate1=$startdate." 00:00:00";
    $whereSql .= " AND (
            UNIX_TIMESTAMP(from_unixtime(#@__order_addon_car.`start_date`,'%Y-%m-%d  00:00:00'))  <= UNIX_TIMESTAMP('$startdate1')  
            AND  UNIX_TIMESTAMP(from_unixtime(#@__order_addon_car.`end_date`,'%Y-%m-%d  00:00:00')) >= UNIX_TIMESTAMP('$startdate1')
    )";
}



if ($keyword != "") {
    $whereSql .= "AND ( ";
    $whereSql .= "goods.goodsname LIKE '%$keyword%' ";
    $whereSql .= " OR #@__order_addon_car.realname LIKE '%$keyword%' ";
    $whereSql .= "  OR  #@__order_addon_car.tel LIKE '%$keyword%' ";
    $whereSql .= "  OR  ordernum LIKE '%$keyword%' ";
    $whereSql .= "  OR  #@__order_addon_car.get_info LIKE '%$keyword%' OR  #@__order_addon_car.return_info LIKE '%$keyword%' ";//上车站点
    $whereSql .= ") ";

}

$sta = isset($sta) ? $sta : "-1";//默认显示未取车的


//未取车
if ($sta == '0') {
    $whereSql .= " AND state=0";

}
//已取车
if ($sta == '1') {
    $whereSql .= " AND state=1";
}

//已还车
if ($sta == '2') {
    $whereSql .= " AND state=2";
}

/*
if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $startdate1 = $startdate;
    $whereSql .= " and (
                        (tmp=1 and #@__line.gotime>=UNIX_TIMESTAMP('$startdate1'))
                         or
                         (tmp=0 and #@__order_addon_lycp.appttime>=UNIX_TIMESTAMP('$startdate1'))
                       )";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    //$enddate1=$enddate;
    $whereSql .= " and (
                        (tmp=1 and #@__line.gotime<=UNIX_TIMESTAMP('$enddate1'))
                         or
                         (tmp=0 and #@__order_addon_lycp.appttime<=UNIX_TIMESTAMP('$enddate1'))
                       )";
}*/



//获取有效名称-------商品下拉框
$goodsOptions = "";
$query3 = "
            SELECT goods.id,goods.goodsname  FROM #@__order_addon_car
            LEFT JOIN #@__goods goods ON goods.id=#@__order_addon_car.goodsid
            LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_car.orderid
            $whereSql
            group by goods.id
            order by convert(goodsname USING gbk)   ";

//dump($query3);
$dsql->SetQuery($query3);
$dsql->Execute("999");
while ($row1 = $dsql->GetArray("999")) {
    $goodsid_1 = $row1["id"];

    $name =  $row1["goodsname"];
    $selected = "";
    if ($goodsid == $goodsid_1) $selected = " selected";
    $goodsOptions .= "<option value='$goodsid_1' $selected>$name</option>";
}



if ($goodsid != "") {
    $whereSql .= " AND goods.id='$goodsid' ";
}


//订单来源
$orderLY = isset($orderLY) ? $orderLY : "";
if($orderLY!="") {
    if ($orderLY == "直通车线路") {
        $whereSql .= "  AND #@__order.desc='直通车线路' ";
    }
    if ($orderLY != "直通车线路") {
        $whereSql .= "  AND #@__order.desc!='直通车线路' ";
    }
}

$query = "
SELECT #@__order_addon_car.*,goods.goodsname,goods.goodscode,goods.litpic, 
#@__order.desc,#@__order.createtime,#@__order.ordernum
  FROM #@__order_addon_car
LEFT JOIN #@__goods goods ON goods.id=#@__order_addon_car.goodsid
LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_car.orderid
$whereSql
   ORDER BY   #@__order.createtime DESC ";




//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('goodsid', $goodsid);
/*$dlist->SetParameter('enddate', $enddate);
$dlist->SetParameter('startdate', $startdate);*/
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('orderLY', $orderLY);

//模板
if (empty($s_tmplets)) $s_tmplets = 'lease.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

