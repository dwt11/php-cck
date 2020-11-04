<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if (empty($dopost)) $dopost = '';


$whereSql = " WHERE   (#@__order.isdel=2 OR #@__order.isdel=3) AND  #@__order.ordertype='orderZtc' ";


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
        OR #@__order.id in(SELECT orderid from x_order_addon_ztc aa  where  aa.`name` LIKE '%$keyword%' or  aa.`tel` LIKE '%$keyword%' or  aa.`idcard` LIKE '%$keyword%'  or  aa.`cardcode` LIKE '%$keyword%' )
      )
     )";
}




$query = "SELECT #@__order.*,c1.realname,c1.mobilephone FROM #@__order
            LEFT JOIN #@__client AS c1 ON c1.id=#@__order.clientid

            $whereSql
              ORDER BY   #@__order.createtime desc";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderZtc.guashiSelect.htm';
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
        $nquery = " SELECT o.*,order1.desc,order1.ordernum,order1.sta,order1.createtime FROM #@__order_addon_ztc o  LEFT JOIN #@__order   order1 on order1.id=o.orderid WHERE o.orderid='{$id}'  $limit";


        $dsql->Execute('f', $nquery);
        while ($frow = $dsql->GetArray('f')) {

            $photo = $frow["idpic"];
            if ($photo != "") $photo = "<a href='$photo' target='_blank'> <img data-original='$photo' width='80' height='80'/></a>";

            if ($rowIndex > 0) $return .= "<tr>";
            $return .= "<td style=\"text-align: left;white-space:nowrap;width:200px\"> ";
            if ($frow["sta"] == 1) {
                $return .= GetZtcCardCode( $frow['id']);
                //$return .= "<br>购买时间:" . GetDateMk($frow['createtime']);

                $return .= "<br>到期时间:" . GetZtcCardTimeIsBool($frow['createtime'],$frow['goodsid']);
            }
            $return .= "</td>
                               <td style=\"text-align: left;white-space:nowrap;width:200px\">" . GetRedKeyWord($frow['name'], $keyword) ."<br>" . GetRedKeyWord($frow['tel'], $keyword) .
                                "<br>".GetRedKeyWord($frow["idcard"], $keyword) . "
                                </td>
                               ";

            $idpic=$frow["idpic"];
            if($idpic==""){
                $idpic="无照片";
            }else{
                $idpic="<a href='$idpic' target='_blank'> <img data-original='$idpic'  src='$idpic' width='80' height='80'/></a>";
            }
            $return .= "
                               <td >
                $idpic
                                </td>
                               ";

            if ($rowIndex > 0) $return .= "</tr>";
        }
    } elseif ($rowIndex == 0) {
        $return = "<td colspan=\"9\">未找到记录</td>";
    }
    return $return;
}