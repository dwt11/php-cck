<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if (empty($dopost)) $dopost = '';
if (empty($paynumb_all)) $paynumb_all = 0;
if (empty($jbnum_all)) $jbnum_all = 0;
if (empty($jfnum_all)) $jfnum_all = 0;
if (empty($total_all)) $total_all = 0;

$operatorid = $CUSERLOGIN->userID;

$whereSql = " WHERE 1=1   AND  #@__order.ordertype='orderLycp'   ";


//是否交易过
$sta = isset($sta) ? $sta : "";

if ($sta != "") {
    if ($sta == "0") $whereSql .= "AND #@__order.sta=0 ";//未交易
    if ($sta == "1") $whereSql .= "AND #@__order.sta=1 ";
    if ($sta == "2") $whereSql .= "AND (#@__order.sta!=0 AND #@__order.sta!=1) ";
    if($sta=="bftk"){
        //如果是查看部分退款的
        $whereSql .= "  AND #@__order.isdel=4 AND  #@__order.sta=1   ";
    }else{
        //全部或其他 的状态查询
        $whereSql .= "  AND (#@__order.isdel=4  OR  #@__order.isdel=0) ";
    }
}else{
    //全部或其他 的状态查询
    $whereSql .= "  AND (#@__order.isdel=4  OR  #@__order.isdel=0) ";
}




$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    $whereSql .= " AND
    (
        ( 
            c1.`realname` LIKE '%$keyword%' 
            OR c1.`mobilephone` LIKE '%$keyword%' 
            OR #@__order.`ordernum` LIKE '%$keyword%'
        )
     )";
}
//不能加此名 加上太慢                     or #@__order.id in(SELECT orderid from #@__order_addon_lycp aa  where  aa.`realname` LIKE '%$keyword%' or  aa.`tel` LIKE '%$keyword%' or  aa.`idcard` LIKE '%$keyword%' )

if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $whereSql .= " AND #@__order.`createtime` >= UNIX_TIMESTAMP('$startdate1') ";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    $whereSql .= " AND #@__order.`createtime` <= UNIX_TIMESTAMP('$enddate1') ";
}

//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
    $whereSql .= " AND #@__order.paytype='$paytype' ";
}

//$whereSql .= " OR #@__order.operatorid='$operatorid' ";

$query = "SELECT #@__order.createtime,#@__order.ordernum,#@__order.operatorid,
          #@__order.id,#@__order.desc,#@__order.sta,#@__order.isdel,
          #@__order.paytype,#@__order.paynum,#@__order.benefitInfo,
          #@__order.jbnum,#@__order.jfnum,#@__order.total,
          c1.realname,c1.mobilephone  FROM #@__order
          INNER JOIN #@__client AS c1 ON c1.id=#@__order.clientid
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
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('paytype', $paytype);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderLine.htm';
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
 * @return int 加工厂个数
 * @internal param $id
 */
function getOrderList($id, $rowIndex = 0, $keyword = "", $isnumb = 0)
{
    $return = "";
    global $dsql;

    $total = 0;


    $nquery = " SELECT count(id) as dd FROM `#@__order_addon_lycp`  WHERE orderid='$id' ";
    $arcRow11 = $dsql->GetOne($nquery);
    if (is_array($arcRow11)) {
        $total = $arcRow11["dd"];
    }




    if ($isnumb > 0) return $total;//如果是要数量,则直接返回
    if ($total > 0) {
        if ($rowIndex == 0) {
            $nquery = " SELECT appttime FROM `#@__order_addon_lycp`  WHERE orderid='$id' ";
            $arcRow11 = $dsql->GetOne($nquery);
            if (is_array($arcRow11)) {
                $return .= "<td rowspan=\"$total\" style=\"text-align: left;white-space:nowrap;width:200px\"> ";
                $return .= "预约日期:" . MyDate('Y-m-d H:i', $arcRow11['appttime']);

                $return .= "</td>";
            }
        }
        require_once("../include/role.class.php");
        $roleCheck = new roleClass();

        $limit = "   limit 0,1";
        if ($rowIndex > 0) $limit = "  limit 1,$total";
        $nquery = " SELECT o.* ,order1.benefitInfo FROM #@__order_addon_lycp o  INNER JOIN #@__order   order1 on order1.id=o.orderid WHERE o.orderid='{$id}'  $limit";


        $dsql->Execute('f', $nquery);
        while ($frow = $dsql->GetArray('f')) {




            if ($rowIndex > 0) $return .= "<tr>";



            $name = $frow["realname"];
            $tel = $frow["tel"];
            $idcard = $frow["idcard"];


            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\">";
            $del_tip = "";
            if ($frow['isdel'] == "1") $del_tip = "<b>已被业务删除</b>";
            $return .= "$del_tip";

            $ztcCarcType="";

            if ($frow['orderlistztcid'] > 0) {
                $ztcCarcType=GetZTCOrderGoodsTYPE($frow['orderlistztcid']);
                // $price = GetBenefitInfoToOrderAddin($frow['benefitInfo'], "直通车");
                // $return .= "<br>[直通车卡] {$price}";
                $ztcCarcType = "[$ztcCarcType]";
            } else {
                //$price = GetBenefitInfoToOrderAddin($frow['benefitInfo'], "非会员");
                $ztcCarcType = "[其他人]";
            }
            $return .= "出行人:{$name}   {$tel}<br>{$ztcCarcType} {$idcard}";
            $return .= " </td > ";


            if ($rowIndex > 0) $return .= "</tr>";
        }
    } elseif ($rowIndex == 0) {
        $return = "<td colspan=\"9\">未找到记录</td>";
    }
    return $return;
}


function GetBenefitInfoToOrderAddin($benefitInfo, $orderAddontype)
{

    if($benefitInfo=="")return "";
    $benefitInfo_array = explode($orderAddontype, $benefitInfo);
    $benefitInfo_array_PLUS = explode("[", $benefitInfo_array[1]);
   // dump($benefitInfo_array_PLUS);
    $benefitInfo_array_PLUS_PRICE = explode(":", $benefitInfo_array_PLUS[0]);

    $benefitInfo_info=str_replace("非会员单价","",$benefitInfo_array_PLUS_PRICE[1]);
    $benefitInfo_info=str_replace("会员单价","",$benefitInfo_info);
    $return_str = "<span class=\"text-danger\">$benefitInfo_info</span>";


    return $return_str;
}