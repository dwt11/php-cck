<?php
require_once("../config.php");
require_once(DWTINC.'/datalistcp.class.php');
require_once DWTINC.'/enums.func.php';  //获取数据字典对应的值

// requir
ExecTime();

if (empty($keyword)) $keyword = '';

$whereSql = "   ";

$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "AND `goodsname` LIKE '%$keyword%' ";
}

//获得数据表名
$query = "SELECT litpic,flag,typeid,goodscode,goodsname,price,#@__goods_addon_lycp.jfnum,DATE_FORMAT(FROM_UNIXTIME(gotime),'%Y-%m-%d'),
          #@__line.* FROM #@__line 
          LEFT JOIN `#@__goods` ON `#@__line`.goodsid=`#@__goods`.id
          LEFT JOIN `#@__goods_addon_lycp` ON `#@__goods_addon_lycp`.goodsid=`#@__goods`.id
          WHERE     islock=1";




$usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
//dump($usertypename);
$isskd=strpos($usertypename, "售卡点子部门");//判断 是否售卡点
// dump($isskd);
if ( $isskd===false) {
        //后台发车时间之前都可以预约,不和截止时间比较
    $query .= " AND  
                    (
                            tmp='临时'
                            AND UNIX_TIMESTAMP(now())< (gotime)
                    )";   //临时线路，只能获取当前时间小于发车时间的*/

}else{
    //售卡点,在截止时间时就不能预约了
    $query .= " AND
                    (
                            tmp='临时'
                            AND UNIX_TIMESTAMP(now())< (gotime-beforHours*3600)
                    )";   //临时线路，只能获取当前时间小于发车时间的*/

}
$query .= " $whereSql ";
$query .= " ORDER BY gotime asc ,convert(goodsname USING gbk) ,tmp DESC ";
//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
$dlist->SetParameter('keyword', $keyword);

//模板
$s_tmplets = 'line.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
