<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if(empty($dopost)) $dopost = '';
if(empty($paynumb_all)) $paynumb_all = 0;
if(empty($jbnum_all)) $jbnum_all = 0;
if(empty($jfnum_all)) $jfnum_all = 0;
if(empty($total_all)) $total_all = 0;
if (empty($goodsid)) $goodsid = '';




$client_dep = isset($client_dep) ? $client_dep : "0";
$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
$whereSql = " WHERE  #@__order.sta=1 and #@__order.isdel=0  AND #@__client_depinfos.isdel=0 ";

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
	OR #@__order_addon_ztc.`idcard` LIKE '%$keyword%'
    ) ";
}

if ($client_dep>0) {
    $client_deps = GetDepChilds($client_dep);
    //dump($emp_depids);
    $whereSql .= " and   #@__client_depinfos.depid in ($client_deps) ";    //资料编号
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


//不显示过期的
$whereSql .= "
    AND (
			unix_timestamp(
				DATE_ADD(from_unixtime(
                                        CASE  WHEN createtime<1483199999 THEN 1483199999 ELSE createtime END  /*1483199999-2016-12-31如果此日期前的订单则修改为此日期才过期*/
                                    ),INTERVAL rankLenth MONTH )/*到期日期*/
			)
		)
		>unix_timestamp(NOW())
    ";

//获得数据表名
$query= "
      SELECT #@__order_addon_ztc.id,#@__order_addon_ztc.goodsid,#@__order_addon_ztc.cardcode,#@__order_addon_ztc.name,#@__order_addon_ztc.tel
     ,#@__order_addon_ztc.idcard,#@__order_addon_ztc.idpic,#@__order_addon_ztc.idpic_desc,
     #@__order.ordernum,#@__order.paytype,#@__order.createtime,#@__order.operatorid,#@__client.`from`  
     ,#@__goods.goodsname,#@__goods.goodscode,#@__goods.litpic
     FROM #@__order_addon_ztc  
     LEFT JOIN #@__goods  ON #@__goods.id=#@__order_addon_ztc.goodsid
          LEFT JOIN #@__goods_addon_ztc  ON #@__goods.id=#@__goods_addon_ztc.goodsid
    LEFT JOIN #@__order  ON #@__order.id=#@__order_addon_ztc.orderid
    LEFT JOIN #@__client  ON #@__order.clientid=#@__client.id
    LEFT JOIN #@__client_depinfos  ON #@__client_depinfos.clientid=#@__client.id
     $whereSql
       ORDER BY   #@__order_addon_ztc.id DESC ";


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
if (empty($s_tmplets)) $s_tmplets = 'ztcList.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


