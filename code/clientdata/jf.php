<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


if (empty($keyword)) $keyword = '';
if(empty($desc)) $desc = '';//操作类型
if(empty($dopost)) $dopost = '';


$whereSql = " WHERE 1=1 and #@__clientdata_jflog.isdel=0 ";


$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    //$whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%' OR c2.`idcard` LIKE '%$keyword%' or jf.desc LIKE '%$keyword%'  ) ";
    $whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%' OR  #@__clientdata_jflog.desc LIKE '%$keyword%'  or #@__clientdata_jflog.info LIKE '%$keyword%' ) ";
}
if ($startdate != "") {
    $stardate_t=$startdate." 00:00:00";
    $whereSql .= "AND #@__clientdata_jflog.`createtime` >= UNIX_TIMESTAMP('$stardate_t') ";
}

if ($enddate != "") {
    $enddate_t=$enddate." 23:59:59";
    $whereSql .= "AND #@__clientdata_jflog.`createtime` <= UNIX_TIMESTAMP('$enddate_t') ";
}
if ($desc != "") {
    if($desc=="充值赠送")$whereSql .= "AND (#@__clientdata_jflog.desc LIKE '金币充值赠送%')";
    if($desc=="返利")$whereSql .= "AND (#@__clientdata_jflog.desc LIKE '下级会员购买赠送%' 
                                        OR #@__clientdata_jflog.desc LIKE '下下级会员购买赠送%' 
                                      OR  #@__clientdata_jflog.desc LIKE '购买赠送%' 
                                         OR  #@__clientdata_jflog.desc LIKE '转为合伙人赠送%'
                                          )";
    if($desc=="消费")$whereSql .= "AND (#@__clientdata_jflog.desc LIKE '订单删除恢复积分%' 
                                         OR  #@__clientdata_jflog.desc LIKE '消费%')";
      if($desc=="手工")$whereSql .= "AND (#@__clientdata_jflog.desc LIKE '管理员手工添加%' 
                                            OR #@__clientdata_jflog.desc LIKE '操作错误积分撤消%')";
      if($desc=="转账")$whereSql .= "AND (#@__clientdata_jflog.desc LIKE '转账%' 
                                            OR #@__clientdata_jflog.desc LIKE '收到%')";
}

$query = "SELECT #@__clientdata_jflog.*,c1.realname,c1.mobilephone,cw.nickname FROM #@__clientdata_jflog
 INNER JOIN #@__client c1 ON #@__clientdata_jflog.clientid=c1.id
 INNER JOIN `#@__client_weixin` cw on cw.clientid=c1.id
$whereSql
  ORDER BY   #@__clientdata_jflog.id desc
";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 20;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('desc', $desc);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);

//模板
if (empty($s_tmplets)) $s_tmplets = 'jf.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


//获取 系统的积分额度
function getJFall(){
    global $dsql;
    //积分表中的
    $str = "";
    $mx_jf=$client_jf=0.00;
    $query = "SELECT SUM(jfnum) as dd FROM `#@__clientdata_jflog` 
              INNER JOIN  `#@__client_depinfos` AS AAAAA ON AAAAA.clientid=`#@__clientdata_jflog`.clientid
              WHERE `#@__clientdata_jflog`.isdel=0 AND AAAAA.isdel=0";
    $row = $dsql->GetOne($query);
    if (isset($row["dd"])&&$row["dd"]!="") {
         $mx_jfnum100 = $row["dd"];
        $mx_jfnum = $mx_jfnum100/100;
    }

    $mx_jfnum=number_format($mx_jfnum,2);



    $query = "SELECT SUM(jfnum) as dd FROM `#@__client_addon` 
                INNER JOIN  `#@__client_depinfos` AS  AAAAA ON AAAAA.clientid=`#@__client_addon`.clientid
              WHERE   AAAAA.isdel=0";
   $row = $dsql->GetOne($query);
    $str = "";
    if (isset($row["dd"])&&$row["dd"]!="") {
        $client_jfnum100 = $row["dd"];
        $client_jfnum = $client_jfnum100/100;
    }

    $client_jfnum=number_format($client_jfnum,2);

    if($mx_jfnum==$client_jfnum){
        $str=$mx_jfnum;
    }else
    {
        $str="积分不对应，明细积分$mx_jfnum,会员余额：$client_jfnum";
    }



    return $str;

}
