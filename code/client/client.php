<?php
/**
 * 客户列表
 *
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');


$t1 = ExecTime();


if (!isset($keyword)) $keyword = '';
if (!isset($orderby)) $orderby = '';
if (!isset($dopost)) $dopost = '';

$depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
//dump($depid);
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");









$whereSql = " where #@__client_depinfos.isdel=0 ";
$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   cl.realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.mobilephone LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.address LIKE '%$keyword%'"; //qq
    $whereSql .= " or clw.nickname LIKE '%$keyword%'";  //
    $whereSql .= " or cl.tag LIKE '%$keyword%'";  //
    $whereSql .= " or cl.description LIKE '%$keyword%' )";//备注

}
$sponsoriid = isset($sponsoriid) ? $sponsoriid : "";
if($sponsoriid!="")    $whereSql .= "   AND  cladd.sponsorid='$sponsoriid'  "; //qq

//金币区间查询
$jbnum_t = isset($jbnum_t) ? $jbnum_t : "";
if($jbnum_t==499)    $whereSql .= "   AND  cladd.jbnum between 100 and 49900   "; //qq
if($jbnum_t==500)    $whereSql .= "   AND  cladd.jbnum='50000'   "; //qq
if($jbnum_t==2000)    $whereSql .= "   AND cladd.jbnum between 50100 and 200000  " ; //qq
if($jbnum_t==2001)    $whereSql .= "   AND cladd.jbnum>200000 " ; //qq



//是否交易过
$isorder = isset($isorder) ? $isorder : "";
$leftjoin = isset($leftjoin) ? $leftjoin : "";
if ($isorder != "") {
    $leftjoin = " INNER JOIN #@__order order1 on order1.clientid=cl.id";
    if ($isorder == 1) {
        $whereSql .= "And (order1.id is null)";
    }//未交易

    if ($isorder == 2) {
        //已交易
        $whereSql .= "And (order1.id is not null and  order1.sta=1  and order1.isdel=0) ";
    }
}
//按会员类型搜索
$rank = isset($rank) ? $rank : "";
if ($rank != "") {
    $whereSql .= "And rankinfostr like '%$rank%'";
    $leftjoin="             /*获取会员类型*/
            INNER JOIN (
                        SELECT clientid,GROUP_CONCAT(x_clientdata_ranklog.rank,'|',x_clientdata_ranklog.rankcutofftime) AS rankinfostr 
                        FROM `x_clientdata_ranklog`
                        
                         GROUP BY `x_clientdata_ranklog`.clientid
                    ) ranklogtable ON cl.id=ranklogtable.clientid
";
}
$rankoption="";
$query3 = "SELECT rank  FROM `#@__clientdata_ranklog`  group by rank";
$dsql->SetQuery($query3);
$dsql->Execute("17013122");
while ($row1 = $dsql->GetArray("17013122")) {
    $rankrow=$row1["rank"];
    $selected="";
    if($rankrow==$rank)$selected=" selected ";
    $rankoption.="<option value='$rankrow' $selected>$rankrow</option>";
}






$from = isset($from) ? $from : "";
if ($from != "") {
    $whereSql .= "And cl.from = '$from'";
}

$orderby_str = " cl.senddate DESC ";
if ($orderby != "") {
    if ($orderby == "jf") $orderby_str = " jfnum DESC ";
    if ($orderby == "jb") $orderby_str = " jbnum DESC ";
}


$query = "SELECT  cl.id,cl.realname,cl.mobilephone,cl.mobilephone_check,cl.mobilephone_checkDate,cl.senddate,cl.description,cl.`from`,
          #@__client_depinfos.depid,  #@__client_depinfos.openid,#@__client_depinfos.clientid,
          cladd.jfnum,cladd.jbnum,cladd.scoresnum,cladd.scorescutofftime,cladd.sponsorid,
          clw.nickname,clw.photo
          FROM #@__client_depinfos
          INNER JOIN #@__client cl ON cl.id=#@__client_depinfos.clientid
          INNER JOIN #@__client_addon cladd ON cl.id=cladd.clientid
          INNER JOIN #@__client_weixin clw ON cl.id=clw.clientid
            $leftjoin
          $whereSql 
            ORDER BY   $orderby_str";

//如果查询金币区间,则显示合计
if($jbnum_t>0){
    $query1111 = "SELECT  SUM(cladd.jbnum) AS JBtotal
          FROM #@__client_depinfos
          INNER JOIN #@__client cl ON cl.id=#@__client_depinfos.clientid
          INNER JOIN #@__client_addon cladd ON cl.id=cladd.clientid
          INNER JOIN #@__client_weixin clw ON cl.id=clw.clientid
            $leftjoin
          $whereSql  ";
    $row11 = $dsql->GetOne($query1111);
    if (is_array($row11)) {
        $jbtotal=($row11["JBtotal"])/100;
    }
}
//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('orderby', $orderby);
$dlist->SetParameter('rank', $rank);
$dlist->SetParameter('from', $from);
$dlist->SetParameter('isorder', $isorder);
$dlist->SetParameter('jbnum_t', $jbnum_t);
$dlist->SetParameter('sponsoriid', $sponsoriid);

//模板
if (empty($s_tmplets)) $s_tmplets = 'client.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;






/**
 * 160516手机是否验证
 *
 * @param $ischeck
 *
 * @return string
 */
function getPhoneIsCheck($ischeck, $mobilephone_checkDate)
{
    $str = "";
    if ($ischeck == 1) $str = "  已验证";
    //$datetime=GetDateMk($mobilephone_checkDate);
    //if($datetime!="")$str.=" <br>验证时间：$datetime";
    return $str;
}







