<?php
/**
 * 内容列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once('goods.functions.php');
require_once(DWTINC.'/datalistcp.class.php');
require_once (DWTINC.'/enums.func.php');  //获取数据字典对应的值
// var_dump($roleCheck);exit;


$totalMoney_all=$payMoney_all=0;   //每页最后一行合计清零

$t1 = ExecTime();




setcookie('ENV_GOBACK_URL', $dwtNowUrl, time()+3600, '/');


$whereSql=" WHERE 1=1 ";

$title="订单管理";


// var_dump($status);
//2状态
$status = isset($status) ? $status : "";   
$depid = isset($depid) ? $depid : "0";
//$optionsalesstatus=GetEnumsForm("salesstatus",$status,"status","订单状态","select");


//dump($optionsalesstatus);
if($status!="")
{
  $title.=$status;
  $whereSql .= " and goodsOrder.status='$status'";
}

if($depid!="0")
{
   $whereSql .= " and goodsOrder.depid='$depid'";
}


//3关键词搜索 表单类型 search_type 关键词keyword
$search_type = isset($search_type) ? $search_type : "";   
$keyword = isset($keyword) ? trim($keyword) : "";   
if($keyword!="")
{
	  //$whereSql  .= " And ( goods.goodscode  LIKE '%$keyword%' ";    //商品编号
	  $whereSql  .= "And ( goodsOrder.goodsOrderCode  LIKE '%$keyword%'";  //订单编号
	  $whereSql  .= " or cl.realname LIKE '%$keyword%'";
	  $whereSql  .= " or cl.mobilephone LIKE '%$keyword%'";
	  $whereSql  .= " or dep.dep_name LIKE '%$keyword%'";
	//  $whereSql  .= " or goods.goodsname  LIKE '%$keyword%'";
	  $whereSql  .= " or goodsOrder.description LIKE '%$keyword%' )";
      
}
//4日期    
$startdate = isset($startdate) ? $startdate : "";  
$enddate = isset($enddate) ? $enddate : "";   
if ($startdate!="" && $enddate!="")
{
  $title.="日期:从".$startdate."到".$enddate;
  $startdate1=GetMkTime($startdate);      //(时间戳)获得选定开始日期的开始时间 格式  2014-11-04 00:00:00
  $enddate1=GetMkTime($enddate)+86399;    //(时间戳)获得选定结束日期的结束时间格式2014-11-04 23:59:59   86399代表23小时59分59秒
  //dump(GetDateTimeMk($startdate1).GetDateTimeMk($enddate1));
  $whereSql  .= " And (goodsOrder.senddate>= '$startdate1' and goodsOrder.senddate<= '$enddate1')"; //qq
}















//$query = "SELECT  goods.*,gtype.typename,addtable.* ,count(mom.buyid) as cbuyid 
// FROM `$maintable` goods 
// LEFT JOIN $addtable addtable on goods.id=addtable.gid
// LEFT JOIN  `#@__crm` mom on mom.buyid=arc.id
// $whereSql

$query = "SELECT  goodsOrder.* ,cl.realname,cl.mobilephone,dep.dep_name
 FROM #@__sys_goods_order goodsOrder
 LEFT JOIN #@__client cl on goodsOrder.clientid=cl.id
 LEFT JOIN #@__emp_dep dep on goodsOrder.depid=dep.dep_id
 $whereSql
   ORDER BY   goodsOrder.id desc";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 25;

//GET参数
$dlist->SetParameter('depid', $depid);

$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('status', $status);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);

//模板
$s_tmplets = 'goodsOrder.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();

// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

