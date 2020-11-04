<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if(empty($dopost)) $dopost = '';
if (empty($goodsid)) $goodsid = '';




$client_dep = isset($client_dep) ? $client_dep : "0";
$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
$whereSql = " WHERE 1=1 and #@__order.sta=1 and #@__order.isdel=0 ";

if ($startdate != "") {
    $startdate1=$startdate." 00:00:00";
    $whereSql .= " AND #@__order.`createtime` >= UNIX_TIMESTAMP('$startdate1') ";
}

if ($enddate != "") {
    $enddate1=$enddate." 23:59:59";
    $whereSql .= " AND #@__order.`createtime` <= UNIX_TIMESTAMP('$enddate1') ";
}


//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
    $whereSql .= "And #@__order.paytype='$paytype' ";
}




if ($keyword != "") {
    $whereSql .= "AND (
    #@__order_addon_ztc.`name` LIKE '%$keyword%' 
    OR  #@__order_addon_ztc.`tel` LIKE '%$keyword%' 
    OR #@__order.`ordernum` LIKE '%$keyword%'  
    OR #@__order_addon_ztc.`cardcode` LIKE '%$keyword%'
    ) ";
}

if ($client_dep>0) {
    $client_deps = GetDepChilds($client_dep);
    //dump($emp_depids);
    $whereSql .= " and   #@__ztc_jihuo.dep_id in ($client_deps) ";
}


//获取有效名称-------商品下拉框
$goodsOptions = "";
$query3 = "
            SELECT #@__goods.id,#@__goods.goodsname  FROM  #@__goods  WHERE typeid=1 AND status='0'
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
    $whereSql .= " AND #@__goods.id='$goodsid' ";
}


//获得数据表名
$query= "
 SELECT  #@__order_addon_ztc.*,#@__order.ordernum,#@__order.paytype,
 #@__order.createtime as ordertime,#@__ztc_jihuo.dep_id,#@__ztc_jihuo.createtime as jihuotime,#@__order.operatorid,#@__client.`from`  
 ,#@__goods.goodsname,#@__goods.goodscode,#@__goods.litpic
 FROM #@__ztc_jihuo LEFT JOIN #@__order_addon_ztc  ON #@__order_addon_ztc.id=#@__ztc_jihuo.orderListId
 LEFT JOIN #@__goods  ON #@__goods.id=#@__order_addon_ztc.goodsid
LEFT JOIN #@__order  ON #@__order.id=#@__order_addon_ztc.orderid
LEFT JOIN #@__client  ON #@__order.clientid=#@__client.id
LEFT JOIN #@__client_depinfos  ON #@__client_depinfos.clientid=#@__client.id
 $whereSql
ORDER BY   id DESC ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);
$dlist->SetParameter('paytype', $paytype);
$dlist->SetParameter('client_dep', $client_dep);
$dlist->SetParameter('goodsid', $goodsid);

//模板
if (empty($s_tmplets)) $s_tmplets = 'ztcJihuoList.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


