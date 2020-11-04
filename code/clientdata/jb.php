<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


if (empty($keyword)) $keyword = '';
if(empty($desc)) $desc = '';//操作类型
if(empty($dopost)) $dopost = '';


$whereSql = " WHERE 1=1 and #@__clientdata_jblog.isdel=0 ";


$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    //$whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%' OR c2.`idcard` LIKE '%$keyword%' or jb.desc LIKE '%$keyword%' ) ";
    $whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%'  or #@__clientdata_jblog.desc LIKE '%$keyword%'  or #@__clientdata_jblog.jbordercode LIKE '%$keyword%' or #@__clientdata_jblog.info LIKE '%$keyword%' ) ";
}
if ($startdate != "") {
    $stardate_t=$startdate." 00:00:00";
    $whereSql .= "AND #@__clientdata_jblog.`createtime` >= UNIX_TIMESTAMP('$stardate_t') ";
}

if ($enddate != "") {
    $enddate_t=$enddate." 23:59:59";
    $whereSql .= "AND #@__clientdata_jblog.`createtime` <= UNIX_TIMESTAMP('$enddate_t') ";
}
if ($desc != "") {
    if($desc=="充值")$whereSql .= "AND (#@__clientdata_jblog.desc LIKE '金币充值%' )";
    if($desc=="提现")$whereSql .= "AND (#@__clientdata_jblog.desc LIKE '会员提现申请%'
                                            or  #@__clientdata_jblog.desc LIKE '提现审核不通过恢复金币%'
                                            or  #@__clientdata_jblog.desc LIKE '删除提现明细，恢复金币%'
                                            )";
    if($desc=="返利")$whereSql .= "AND (
                                        #@__clientdata_jblog.desc LIKE '下级会员购买赠送%'
                                         or #@__clientdata_jblog.desc LIKE '下下级会员购买赠送%' 
                                         or #@__clientdata_jblog.desc LIKE '购买多件赠送%' 
                                         )";
    if($desc=="消费")$whereSql .= "AND (#@__clientdata_jblog.desc LIKE '转为合伙人金币减少'
                                         or #@__clientdata_jblog.desc LIKE '订单删除%' 
                                         or #@__clientdata_jblog.desc LIKE '消费%' 
                                         )";
    if($desc=="手工")$whereSql .= "AND (#@__clientdata_jblog.desc LIKE '管理员手工%' 
                                            or  #@__clientdata_jblog.desc LIKE '操作错误金币撤消%' 
                                        )";
    if($desc=="转账")$whereSql .= "AND (#@__clientdata_jblog.desc LIKE '转账%' 
                                            OR #@__clientdata_jblog.desc LIKE '收到%')";
}





$query = "SELECT #@__clientdata_jblog.*,c1.realname,c1.mobilephone ,cw.nickname FROM #@__clientdata_jblog 
 INNER JOIN #@__client c1 ON #@__clientdata_jblog.clientid=c1.id
 INNER JOIN `#@__client_weixin` cw ON cw.clientid=c1.id
$whereSql
  ORDER BY   #@__clientdata_jblog.id desc
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
if (empty($s_tmplets)) $s_tmplets = 'jb.htm';
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
function getJBall(){
    global $dsql;
    $str = "";
    $mx_jb=$client_jb=0.00;
    //明细记录中的
    $query = "SELECT SUM(jbnum) as dd FROM `#@__clientdata_jblog`
              INNER JOIN  `#@__client_depinfos` AS AAAAA ON AAAAA.clientid=`#@__clientdata_jblog`.clientid
              WHERE #@__clientdata_jblog.isdel=0 AND AAAAA.isdel=0";
    $row = $dsql->GetOne($query);
    if (isset($row["dd"])&&$row["dd"]!="") {
        $mx_jbnum100 = $row["dd"];
        $mx_jbnum = $mx_jbnum100/100;
    }
    $mx_jbnum=number_format($mx_jbnum,2);



    //会员余额的总额
    $query = "SELECT SUM(jbnum) as dd FROM `#@__client_addon`
               INNER JOIN  `#@__client_depinfos` AS  AAAAA ON AAAAA.clientid=`#@__client_addon`.clientid
              WHERE   AAAAA.isdel=0";

    $row = $dsql->GetOne($query);
    $str = "";
    if (isset($row["dd"])&&$row["dd"]!="") {
        $client_jbnum100 = $row["dd"];
        $client_jbnum = $client_jbnum100/100;
    }
    $client_jbnum=number_format($client_jbnum,2);

    if($mx_jbnum==$client_jbnum){
        $str=$mx_jbnum;
    }else
    {
        $str="金币不对应，明细积分$mx_jbnum,会员余额：$client_jbnum";
    }



    return $str;

}
