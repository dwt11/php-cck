<?php
/*单独的打印报表 未启用
这里如果启用,这个PHP查询页面 要和appquery_list.php合成一个页面,只调用不同的HTMl就可以了

*/
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) $dopost = '';


$t1 = ExecTime();
//--------------------------------获取线路名称 发车时间
$lineid = isset($lineid) ? $lineid : "";
$gotime = isset($gotime) ? $gotime : "";
if ($lineid == "" || $gotime == "") {
    ShowMsg("参数出错!", "-1");
}
$xl_dest = $xl_tmp = $xl_gotime = "";
$arcQuery = "SELECT #@__line.gotime,#@__line.tmp,goods.goodsname  FROM #@__line
              LEFT JOIN #@__goods as goods ON goods.id=#@__line.goodsid
              WHERE  #@__line.id='$lineid' ";
$arcRow = $dsql->GetOne($arcQuery);
if ($arcRow) {
    $xl_dest = $arcRow["goodsname"];
    $xl_tmp = $arcRow["tmp"];
    $xl_gotime = $arcRow["gotime"];
}

$godate = "";
if ($xl_tmp == '每日') {
    //固定线路 取用户输入的发车日期
    $godate = $gotime . " " . date(' H时i分', $xl_gotime);
} elseif ($xl_tmp == '临时') {
    //临时线路取线路的发车日期
    $godate = date('Y-m-d H时i分', $xl_gotime);
} else {
    $godate = $gotime;
}
$title_info = "";
$title_info = "" . $godate . " ";
if ($xl_dest != "") $title_info .= $xl_dest;

//dump($title_info);
//--------------------------------获取线路名称 发车时间


//---------------------------获取预约人信息
//if (empty($keyword)) $keyword = '';
$whereSql = " WHERE (#@__order.isdel=0 OR #@__order.isdel=4 )  AND  #@__order_addon_lycp.isdel=0  AND #@__order.sta=1";//显示 未被删除和已经支付的主订单  并且子订单未被业务删除

$whereSql .= " AND #@__order_addon_lycp.lineid=$lineid  ";
$whereSql .= " AND  FROM_UNIXTIME(#@__order_addon_lycp.appttime,'%Y-%m-%d')='$gotime' ";


$query = "
        SELECT 
        #@__order_addon_lycp.id,#@__order_addon_lycp.appttime,#@__order_addon_lycp.tjsite,#@__order_addon_lycp.seatNumber,#@__order_addon_lycp.orderlistztcid,
        #@__order_addon_lycp.deviceid,
        #@__order_addon_lycp.realname,#@__order_addon_lycp.tel,#@__order_addon_lycp.idcard,
        #@__order_addon_lycp.info,#@__order_addon_lycp.infodate,#@__order_addon_lycp.infooperatorid,#@__order_addon_lycp.iscc,
        #@__order.desc,
        #@__order.clientid
        FROM #@__order_addon_lycp #@__order_addon_lycp
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
        $whereSql
        ORDER BY   #@__order_addon_lycp.deviceid ASC, seatNumber ASC";

//dump($query);
$dsql->SetQuery($query);
$dsql->Execute();
//dump($dsql->GetTotalRow());
$info = array();
while ($row = $dsql->GetArray()) {
    $info[$row["deviceid"]][] = array(
        "id" => $row["id"],
        "appttime" => $row["appttime"],
        "seatNumber" => $row["seatNumber"],
        "tjsite" => $row["tjsite"],
        "realname" => $row["realname"],
        "tel" => $row["tel"],
        "idcard" => $row["idcard"],
        "desc" => $row["desc"],
        "info" => $row["info"],
        "clientid" => $row["clientid"],
        "iscc" => $row["iscc"],
        "orderlistztcid" => $row["orderlistztcid"],
        "infodate" => GetDateNoYearMk($row["infodate"]),
        "infooperatorid" => GetEmpNameByUserId($row["infooperatorid"]),
    );

    $appttime = $row["appttime"];
}

//$print_array = $info;
///dump($info);

$gobackurl = ($dwtNowUrl);
//dump($gobackurl);
$s_tmplets = 'service/apptQuery.list.toexcel.htm';

include DwtInclude($s_tmplets);


