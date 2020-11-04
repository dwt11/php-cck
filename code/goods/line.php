<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once('catalog.class.php');








$t1 = ExecTime();







if (empty($goodsid)) $goodsid = '';
if (empty($typeid)) $typeid = '2';


//获取当前点开的分类信息
$tl = new GoodsTypeUnit($typeid);
$optionarr = $tl->GetGoodsTypeOptionS(2);  //搜索表单的分类值//GetOptionArray








if (empty($keyword)) $keyword = '';
$sta = isset($sta) ? $sta : "1";//默认显示正常发车的

$whereSql = " WHERE 1=1 ";



$startdate = isset($startdate) ? $startdate : "";
if ($startdate != "") {
    $startdate1=$startdate." 00:00:00";
    $startdate2=$startdate." 23:59:59";
    $whereSql .= " AND #@__line.`gotime` >= UNIX_TIMESTAMP('$startdate1')  AND #@__line.`gotime` <= UNIX_TIMESTAMP('$startdate2')  ";
}



$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "AND (
    goods.`goodsname` LIKE '%$keyword%'
     ) ";
}


//未出行
if($sta=='1') {
    $whereSql .= " 
     AND   #@__line.tmp='临时' 
               AND #@__line.gotime>".time()."
               AND islock=1
      
     ";
}

//已出行
if($sta=='2') {
    $whereSql .= "AND   #@__line.tmp='临时' AND #@__line.gotime<=".time()."            AND islock=1";
}
//停用
if($sta=='3') {
    $whereSql .= "AND  (#@__line.islock=0)";
}
//按商品筛选
if($goodsid!="") {
    $whereSql .= "AND  (goods.id='$goodsid')";
}
//按商品筛选
if($typeid>0) {
    $whereSql .= " AND goods.`typeid` IN (" . $tl->GetGoodsSonIds() . ")";    //搜索用的
}







//获取有效的产品名称
$goodsOptions = "";
$query3 = "
        SELECT goods.id,goods.goodsname FROM  #@__line  
        INNER JOIN #@__goods goods ON goods.id=#@__line.goodsid
        $whereSql
        GROUP BY #@__line.goodsid
        ORDER BY  CONVERT(goodsname USING gbk) ASC  ";

$dsql->SetQuery($query3);
$dsql->Execute("999");
while ($row1 = $dsql->GetArray("999")) {
    $goodsid_1 = $row1["id"];
    $name =  $row1["goodsname"];
    $selected = "";
    if ($goodsid == $goodsid_1) $selected = " selected";
    $goodsOptions .= "<option value='$goodsid_1' $selected>$name</option>";
}

//dump($goodsOptions);






$query = "
SELECT  
        GROUP_CONCAT(x_line.gotime) AS gotime,
        GROUP_CONCAT(DATE_FORMAT(FROM_UNIXTIME(x_line.gotime),'%Y年%m月%d日  %h时'))  AS gotime_str,
        GROUP_CONCAT(x_line.id) AS  lineids,
        x_line.seats,x_line.linedaynumb,x_line.backtime,x_line.carinfo_desc ,x_line.beforHours ,x_line.diaodudianhua ,x_line.islock ,
        lycp.gosite,lycp.downsite,lycp.tjsite,
        goods.id AS goodsid,goods.goodsname,goods.goodscode,goods.litpic
FROM x_line
INNER JOIN x_goods goods ON goods.id=x_line.goodsid
INNER JOIN x_goods_addon_lycp lycp ON lycp.goodsid=goods.id
$whereSql
GROUP BY  goods.id,lycp.gosite,lycp.downsite,lycp.tjsite,x_line.seats ,x_line.carinfo_desc,x_line.linedaynumb ,x_line.beforHours 
ORDER BY convert(goods.goodsname USING gbk)  ,gotime ASC
   ";





//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('goodsid', $goodsid);

//模板
if (empty($s_tmplets)) $s_tmplets = 'line.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

