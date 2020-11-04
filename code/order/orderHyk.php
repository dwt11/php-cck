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


$whereSql = " WHERE 1=1 and #@__order.isdel=0  AND  #@__order.ordertype='orderHyk'";


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

//是否交易过
$sta = isset($sta) ? $sta : "";
if ($sta != "") {
    if ($sta == 0) $whereSql .= "And #@__order.sta=0 ";//未交易
    if ($sta == 1) $whereSql .= "And #@__order.sta=1 ";
    if ($sta == 2) $whereSql .= "And (#@__order.sta!=0 and #@__order.sta!=1) ";
}
//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
    $whereSql .= "And #@__order.paytype='$paytype' ";
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
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('paytype', $paytype);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderHyk.htm';
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


    $nquery = " SELECT count(id) as dd FROM `#@__order_addon_hyk`  where orderid='$id' ";
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
        $nquery = " SELECT o.*,order1.desc,order1.ordernum,order1.sta,order1.createtime FROM #@__order_addon_hyk o  LEFT JOIN #@__order   order1 on order1.id=o.orderid WHERE o.orderid='{$id}'  $limit";

//dump($nquery);
        $dsql->Execute('f', $nquery);
        while ($frow = $dsql->GetArray('f')) {

            $goodsid = $frow["goodsid"];
            $nquery = " SELECT goods.goodsname,goods.goodscode,goods.litpic,goods.price FROM `#@__goods` goods where id='$goodsid' ";
            $arcRow11 = $dsql->GetOne($nquery);
            if (is_array($arcRow11)) {
                $photo =  $arcRow11["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";
            }
            $goodsname = $arcRow11["goodsname"];
            $goodscode = $arcRow11["goodscode"];
            $price = $arcRow11["price"]/100;

            if ($rowIndex > 0) $return .= "<tr>";
            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\"> ";
            $return .= "<img src=\"$photo\" width=\"60\" height=\"60\" style='float:left; margin-right: 5px'>";
            $return .= "[" . $goodscode . "] " . $goodsname;
            //$return .= "<br><span class=\"text-danger\"> 单价:￥<span id=\"price\">$price</span></span>";//171109 不显示单价
            $return .= "<br><span  > 数量: {$frow["buynumb"]} </span>";
            $return .= "</td>";



            /*//$return .= "     <td >
                               <div style=\"text-align: left; max-width:300px\">
                               订单备注:{$frow["desc"]}
                              
                               </div></td>";*/




            //$return .= " <td style = \"text-align: left; width:200px\">用户备注:$desc</td>";
            if ($rowIndex > 0) $return .= "</tr>";
        }
    } elseif ($rowIndex == 0) {
        $return = "<td colspan=\"9\">未找到记录</td>";
    }
    return $return;
}