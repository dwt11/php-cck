<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) $dopost = '';
if($dopost=="")setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");//为空设定返回地址,其他有动作的不设定

//新的保存车辆租赁订单，然后车辆部门那里排司机
if ($dopost == 'save') {
    //参数  $ids        旅游产品子订单 ID
    //参数  $lineid     线路ID
    //参数  $appttime   发车时间
    if (empty($driverid)) $driverid = '';
    if (empty($guideid)) $guideid = '';


    //dump($ids);
    //dump($id);
    $oper_id = $CUSERLOGIN->userID;
    //更新


    /*座号算法 ，先获取原车里的ID编号 ，生成数组 ，
    然后获取表单来的数组 ，
    两个比较，以原车里的为准，将表单里的去重后，加到原车数组后，
    重新生成所有的座位号
    */

    //将车辆ID和座位,保存入订单子表
    $sql = "";
    $dquery = "";
    $oldids = "";//原车里的人
    $questr11 = "SELECT GROUP_CONCAT(#@__order_addon_lycp_old.id) AS oldids  FROM `#@__order_addon_lycp_old` 
                LEFT JOIN  `#@__order` ON #@__order_addon_lycp_old.orderid=#@__order.id 
                WHERE lineid='$lineid' AND `deviceid`='$deviceid' AND (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order.sta=1
                ";
    //dump($questr11);
    $rowarc11 = $dsql->GetOne($questr11);
    if (isset($rowarc11) && $rowarc11["oldids"] != "") {
        $oldids = $rowarc11["oldids"];
    }
    $oldids_array = array();
    if ($oldids != "") {
        $oldids_array = explode(',', $oldids);
    }

    //表单过来的乘车人
    $ids_array = array();
    if ($ids != "") {
        $ids_array = explode('`', $ids);
    }

    //dump("旧的：" . $oldids);
    // dump("新的：" . $ids);

    $newids_array = array_merge($oldids_array, $ids_array);//合并两个
    $newids_array = array_unique($newids_array);//删除重复
    // dump($newids_array);
    //重新排位置
    $seatNumber = 0;
    foreach ($newids_array as $idvalue) {
//把所有的人  从1开始重排
        $seatNumber++;
        $sql = "UPDATE `#@__order_addon_lycp_old` SET `deviceid`='$deviceid', `seatNumber`='$seatNumber' WHERE id='$idvalue' ";
        //dump($sql);
        $dsql->ExecuteNoneQuery($sql);
    }


    //获取当前线路 当前时间 在预约表中,已经有的车辆,
    //然后在用车记录中,将不在上述记录中的车辆使用情况删除
    $questr77 = "SELECT group_concat(deviceid) AS deviceid_str  FROM `#@__order_addon_lycp_old` WHERE  lineid='$lineid' AND appttime='$appttime' ";
    $rowarc77 = $dsql->GetOne($questr77);
    if ($rowarc77['deviceid_str'] != "") {
        $use_deviceid_str = $rowarc77['deviceid_str'];
        //先将用车记录里原有的此线路 此发车时间 此车的记录 变为删除
        $sql = "DELETE FROM  `#@__device_automobile_uselog`  WHERE `deviceid` NOT IN ($use_deviceid_str) AND lineid='$lineid' AND start_date='$appttime' ";
        $dsql->ExecuteNoneQuery($sql);
    }


    //先将用车记录里原有的此线路 此发车时间 此车的记录 变为删除
    $sql = "DELETE FROM  `#@__device_automobile_uselog`  WHERE `deviceid`='$deviceid' AND lineid='$lineid' AND start_date='$appttime' ";
    $dsql->ExecuteNoneQuery($sql);

    //再保存新值
    $sql = "INSERT INTO `#@__device_automobile_uselog` ( `deviceid`, `start_date`, `end_date`, `clientid`, `operatorid`, `orderAddonId`, `lineid`, `driverid`, `guideid`, `isdel`)
                                              VALUES ( '$deviceid', '$appttime', '', '', '$oper_id', '', '$lineid', '$driverid', '$guideid', '0');";
    $dsql->ExecuteNoneQuery($sql);


    echo "操作成功";
    exit();


}





//保存座位顺序
if ($dopost == 'moveSave') {
    //dump($moveData) ;
    $sql_array=array();
    $order_addon_lycp_id_array=explode(",",$moveData);
    $seatNumber=0;
    foreach ($order_addon_lycp_id_array as $id){
        $seatNumber++;
        //按顺序更新座位
        $sql_array[] = "UPDATE `#@__order_addon_lycp_old` SET  `seatNumber`='$seatNumber' WHERE id='$id' ";
    }
    $dsql->ExecuteNoneCommit($sql_array);//批量处理事务
    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
    ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();

}
$t1 = ExecTime();
//--------------------------------获取线路名称 发车时间
$lineid = isset($lineid) ? $lineid : "";
//$deviceid = isset($deviceid) ? $deviceid : "-1";//这个用与调整座位时,只显示当前车的所有人,如果没有分车 则deviceid=''
$gotime = isset($gotime) ? $gotime : "";
if ($lineid == "" || $gotime == "") {
    ShowMsg("参数出错!", "-1");
}



//----获取标题使用的信息
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
//----获取标题使用的信息
//dump($title_info);
//--------------------------------获取线路名称 发车时间


//---------------------------获取预约人信息
//if (empty($keyword)) $keyword = '';


$whereSql = " WHERE (#@__order.isdel=0 OR #@__order.isdel=4 )  AND  #@__order_addon_lycp_old.isdel=0  AND #@__order.sta=1";//显示 未被删除和已经支付的主订单  并且子订单未被业务删除

$whereSql .= " AND #@__order_addon_lycp_old.lineid=$lineid  ";
$whereSql .= " AND  FROM_UNIXTIME(#@__order_addon_lycp_old.appttime,'%Y-%m-%d')='$gotime' ";
//if($deviceid!="-1") $whereSql .= " AND deviceid='$deviceid'";

$query = "
        SELECT 
        #@__order_addon_lycp_old.id,#@__order_addon_lycp_old.appttime,#@__order_addon_lycp_old.tjsite,#@__order_addon_lycp_old.seatNumber,#@__order_addon_lycp_old.orderlistztcid,
        #@__order_addon_lycp_old.deviceid,
        #@__order_addon_lycp_old.realname,#@__order_addon_lycp_old.tel,#@__order_addon_lycp_old.idcard,
        #@__order_addon_lycp_old.info,#@__order_addon_lycp_old.infodate,#@__order_addon_lycp_old.infooperatorid,#@__order_addon_lycp_old.iscc,
        #@__order.desc,
        #@__order.clientid
        FROM #@__order_addon_lycp_old #@__order_addon_lycp_old
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp_old.orderid
        $whereSql
        ORDER BY   #@__order_addon_lycp_old.deviceid ASC, seatNumber ASC";

//dump($query);
$dsql->SetQuery($query);
$dsql->Execute();
//dump($dsql->GetTotalRow());
$info_lycp = array();
while ($row = $dsql->GetArray()) {
    $info_lycp[$row["deviceid"]][] = array(
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
//dump($info);

$gobackurl = ($dwtNowUrl);
//dump($gobackurl);
$s_tmplets = 'service/old.apptQuery.list.htm';//默认的
if($dopost=="move")$s_tmplets = 'service/old.apptQuery.list_move.htm';//座位调整的

include DwtInclude($s_tmplets);


