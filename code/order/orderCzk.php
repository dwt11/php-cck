<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if (empty($paynumb_all)) $paynumb_all = 0;
if (empty($total_all)) $total_all = 0;


$whereSql = " WHERE 1=1 and #@__order.isdel=0  AND  #@__order.ordertype='orderCzk'";


$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    $whereSql .= "AND
    (
        ( 
            c1.`realname` LIKE '%$keyword%' 
            OR c1.`mobilephone` LIKE '%$keyword%' 
            OR #@__order.`ordernum` LIKE '%$keyword%'
        )
     )";
}

if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $whereSql .= " AND #@__order.`createtime` >= UNIX_TIMESTAMP('$startdate1') ";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    $whereSql .= " AND #@__order.`createtime` <= UNIX_TIMESTAMP('$enddate1') ";
}




$query = "SELECT #@__order.*,c1.realname,c1.mobilephone  FROM #@__order
          LEFT JOIN #@__client AS c1 ON c1.id=#@__order.clientid
          $whereSql
            ORDER BY   #@__order.createtime desc";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderCzk.htm';
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
 * 得到订单的所有子订单
 *
 * @param              $id       订单ID
 * @param int|默认取第一条数据 $rowIndex 默认取第一条数据(与煤矿行并列),大于0取其他的行(与煤矿行分开)
 * @param string       $keyword  主表单搜索的关键词 加红色显示
 * @param int          $isnumb   0返回数量 1返回数据
 *
 * @internal param $id
 */
function getOrderList($id, $rowIndex = 0, $keyword = "", $isnumb = 0)
{
    $return = "";
    global $dsql;

    $total = 0;


    $nquery = " SELECT count(id) as dd FROM `#@__order_addon_czk`  where orderid='$id' ";
    $arcRow11 = $dsql->GetOne($nquery);
    if (is_array($arcRow11)) {
        $total = $arcRow11["dd"];
    }
    if ($isnumb > 0) return $total;//如果是要数量,则直接返回
    if ($total > 0) {

        $limit = "   limit 0,1";
        if ($rowIndex > 0) $limit = "  limit 1,$total";
        $nquery = " SELECT o.* FROM #@__order_addon_czk o  WHERE o.orderid='{$id}'  $limit";
        $dsql->Execute('f', $nquery);
        $czk_i=0;
        while ($frow = $dsql->GetArray('f')) {


            $czk_i++;

            if ($rowIndex > 0) $return .= "<tr>";
            $je = $frow["je"]/100;

            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\"> ";
            $return .="卡{$czk_i} 面额:{$je}元";
            $return .= "</td>";





            $usedate = $frow["usedate"];
            if($usedate>0){
                $czk_password = $frow["czk_password"];

                $clientid=$operatorid="";
                $query = "SELECT clientid,operatorid FROM `#@__clientdata_jblog` WHERE isdel=0 AND `desc` LIKE '%{$czk_password}' ";
                //dump($query);
                $row = $dsql->GetOne($query);
                if (isset($row["clientid"])&&$row["clientid"]!="") {
                    $clientid = getOneCLientRealName($row["clientid"]);
                    $operatorid = GetEmpNameByUserId($row["operatorid"]);
                }

                $usedate="使用时间:".GetDateTimeMk($usedate);
                $usedate.="<br>使用人:[$clientid]  操作人:[$operatorid]";
            }else{
                $id=$frow["id"];
                $usedate="密码:<a onclick=\"layer.open({type: 2,title: '查看充值卡密码', content: 'orderCzk.showPassword.php?orderAddonId=$id'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='查看充值卡密码' > 查看充值卡密码 </a> ";
            }

            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\">";
            $return .= $usedate;
            $return .= " </td > ";


            if ($rowIndex > 0) $return .= "</tr>";
        }
    } elseif ($rowIndex == 0) {
        $return = "<td colspan=\"9\">未找到记录</td>";
    }
    return $return;
}