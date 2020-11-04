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


$whereSql = " WHERE 1=1 AND #@__order.isdel=0 AND  #@__order.ordertype='orderZtc' ";


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
        OR #@__order.`desc` LIKE '%$keyword%' 
         )
     )";
}
//不要这个查询 太慢OR #@__order.id in(SELECT orderid from x_order_addon_ztc aa  where  aa.`name` LIKE '%$keyword%' or  aa.`tel` LIKE '%$keyword%' or  aa.`idcard` LIKE '%$keyword%'  or  aa.`cardcode` LIKE '%$keyword%' )

if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $whereSql .= " AND #@__order.`paytime` >= UNIX_TIMESTAMP('$startdate1') ";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    $whereSql .= " AND #@__order.`paytime` <= UNIX_TIMESTAMP('$enddate1') ";
}

//是否交易过
$sta = isset($sta) ? $sta : "";
if ($sta != "") {
    if ($sta == 0) $whereSql .= "And #@__order.sta=0 ";//未交易
    if ($sta == 1) $whereSql .= "And #@__order.sta=1 ";
    if ($sta == 2) $whereSql .= "And (#@__order.sta!=0 and #@__order.sta!=1) ";
}
//是否续费订单
$isxufei = isset($isxufei) ? $isxufei : "";
if ($isxufei != "") {
    if ($isxufei == "正常订单") $whereSql .= "AND #@__order.`desc` NOT LIKE '续费%'";
    if ($isxufei == "续费订单") $whereSql .= "AND #@__order.`desc`  LIKE '续费%'";
 }
//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
    $whereSql .= "And #@__order.paytype='$paytype' ";
}


$query = "SELECT #@__order.*,c1.realname,c1.mobilephone FROM #@__order
            LEFT JOIN #@__client AS c1 ON c1.id=#@__order.clientid

            $whereSql
              ORDER BY   #@__order.createtime desc,#@__order.id desc";


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
$dlist->SetParameter('isxufei', $isxufei);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderZtc.htm';
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
 */
function getOrderList($id, $rowIndex = 0, $keyword = "", $isnumb = 0)
{
    $return = "";
    global $dsql;

    $total = 0;


    $nquery = " SELECT count(id) as dd FROM `#@__order_addon_ztc`  where orderid='$id' ";
    $arcRow11 = $dsql->GetOne($nquery);
    if (is_array($arcRow11)) {
        $total = $arcRow11["dd"];
    }
    if ($isnumb > 0) return $total;//如果是要数量,则直接返回
    if ($total > 0) {
        require_once("../include/role.class.php");
        $roleCheck = new roleClass();

        $limit = "   limit 0,1";
        if ($rowIndex > 0) $limit = "  limit 1,$total";
        $nquery = " SELECT o.*,order1.desc,order1.ordernum,order1.sta,order1.createtime,order1.operatorid FROM #@__order_addon_ztc o  LEFT JOIN #@__order   order1 on order1.id=o.orderid WHERE o.orderid='{$id}'  $limit";


        $dsql->Execute('f', $nquery);
        while ($frow = $dsql->GetArray('f')) {

            $photo = $frow["idpic"];
            if ($photo != "") $photo = "<a href='$photo' target='_blank'> <img data-original='$photo' width='80' height='80'/></a>";

            if ($rowIndex > 0) $return .= "<tr>";
            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\"> ";
            if ($frow["sta"] == 1) {
                $return .= GetZtcCardCode($frow['id']);
                //$return .= "<br>购买时间:" . GetDateMk($frow['createtime']);

                $return .= "<br>到期时间:" . GetZtcCardTimeIsBool($frow['createtime'], $frow['goodsid']);
            }

            $isjihuo = "";
            /*
            //171102修改为不用激活判断
             //如果是微信自己买的卡,并支付成功,则判断是否激活了
             if ($frow['operatorid'] == 0 && $frow["sta"] == 1) {
                 $isjihuo = "<b>未激活</b>";
                 $query = "SELECT orderListId,createtime FROM #@__ztc_jihuo   WHERE orderListId='{$frow['id']}' ";
                 $row = $dsql->GetOne($query);
                 if (isset($row["orderListId"]) && $row["orderListId"] > 0) {
                     $isjihuo = "已激活" . GetDateMk($row["createtime"]);
                 }
             }*/
            $return .= "</td>
                                 <td style=\"text-align: left;white-space:nowrap;width:200px\">" . GetRedKeyWord($frow['name'], $keyword) . ' ' . GetRedKeyWord($frow['tel'], $keyword) . "<br>" . GetRedKeyWord($frow["idcard"], $keyword) . "</td>
                               <td >
                               <div style=\"text-align: left; max-width:300px\">
                               {$isjihuo}<br>
                               订单备注:{$frow["desc"]}
                              
                               </div></td>
                                <td style=\"text-align: center;width:200px\">" . $photo . "<br>";

            if ($frow["sta"] == 1) {
                $ztc_list_id=$frow['id'];
                $return .= $roleCheck->RoleCheckToLink("order/orderZtc_idpicSH.php?id={$id}&ztc_list_id={$ztc_list_id}", "照片审核", "", true);

                $return .= '<a onclick="layer.open({type: 2,title: \'照片编辑\', content: \'orderZtc.list.idpic.edit.php?id=' . $frow['id'] . '\'});"  href=\'javascript:\' data-toggle=\'tooltip\' data-placement=\'top\' title=\'照片编辑\' > 照片编辑 </a>';
                if ($frow["idpic_desc"] != "") $return .= " <br><div style='max-width: 200px' align='left'>审核说明:{$frow["idpic_desc"]}</div>";
            }

            $return .= "</td>";
            if ($rowIndex > 0) $return .= "</tr>";
        }
    } elseif
    ($rowIndex == 0
    ) {
        $return = "<td colspan=\"9\">未找到记录</td>";
    }
    return $return;
}