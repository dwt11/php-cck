<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) $dopost = '';
if ($dopost == "") setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");//为空设定返回地址,其他有动作的不设定


if($dopost=="getOrderCode"){
$ordercode111=GetOrderOneInfo($orderCarid,"ordernum");
if($ordercode111!=""){
    echo $ordercode111;
}else{
    echo "无";
}
exit;
}


//新的保存车辆租赁订单，然后车辆部门那里排司机
if ($dopost == 'save') {

    $operatorid = $CUSERLOGIN->userID;
    /*    if (empty($orderCarId)) $orderCarId = '';
        $orderCarId_old = $orderCarId;
        //如果传递过来的CAR的orderID》0代表用户重新选择了车辆类型，则退款原订单后，再重新建立
        if ($orderCarId_old != "") {
            $return_str = ReturnOrder($orderCarId_old, 0, $orerid = $operatorid);
            //dump($return_str);
        }*/

    //------------------------------返回值初始化
    //"info" => "",   提示信息
    //"jsApiParameters" => "",支付字符串
    //"orderid" => ""  订单ID
    $aa = array(
        "info" => "",
        "jsApiParameters" => "",
        "orderid" => ""
    );


    //---------------------------------------创建通用主订单
    //获取当前操作的员工对应的前台会员的ID，用于订单保存
    $clientid = 3;//默认会员名称是3曹越的
    $questr11 = "SELECT clientid  FROM `#@__emp_client` 
                LEFT JOIN  `#@__sys_admin` ON #@__sys_admin.empid=#@__emp_client.emp_id 
                WHERE #@__sys_admin.id='$operatorid'
                ";
    $rowarc11 = $dsql->GetOne($questr11);
    if (isset($rowarc11) && $rowarc11["clientid"] > 0) {
        $clientid = $rowarc11["clientid"];
    }

    $tel = $realname = "";
    $questr1 = "SELECT realname,mobilephone FROM `#@__client`  WHERE  id='$clientid' ";
    $row2 = $dsql->GetOne($questr1);
    if (isset($row2)) {
        $tel = $row2['mobilephone'];
        $realname = $row2['realname'];
    }


    $orderReturnStr = CreateOrder(
        $clientid,
        $ordertype = "orderCar",
        $desc = "直通车线路",
        $jfnum100 = 0,
        $jbnum100 = 0,
        $operatorid,
        $total100 = 0,
        $paynum100 = 0,
        $benefitInfo = "",
        $fh_ejjb100 = 0,
        $fh_ejjf100 = 0,
        $fh_sjjb100 = 0,
        $fh_sjjf100 = 0,
        $buynumb = 1
    );
    $orderReturnStr_array = explode(",", $orderReturnStr);
    $orderInfo = "";      //订单操作成功与否信息
    $orderCode = "";//订单编号
    $orderCarId_new = "";//订单Id
    if (count($orderReturnStr_array) > 0) {
        $orderInfo = $orderReturnStr_array[0];      //订单操作成功与否信息
        $orderCode = $orderReturnStr_array[1];//订单编号
        $orderCarId_new = $orderReturnStr_array[2];//订单Id
    }
//dump($orderReturnStr);
    if ($orderInfo != "订单创建成功") {
        $aa = array(
            "info" => $orderInfo,
            "jsApiParameters" => "",
            "orderid" => ""
        );
        echo json_encode($aa);
        exit();
    }
//---------------------------------------创建通用主订单


//---------------------------------------创建订单附加
    $start_date = $end_date = GetMkTime($appttime); //开始时间和结束 时间都等于 线路出行日期


    $sql = "INSERT INTO `#@__order_addon_car` ( `orderid`, `goodsid`, `carNumb`, `start_date`, `end_date` , `realname`, `tel`, `state`)
           VALUES ('$orderCarId_new', '$goodsid', '1', '$start_date', '$end_date' , '$realname', '$tel', '0');";
    $dsql->ExecuteNoneQuery($sql);


//---------------------------------------创建订单附加


//---------------------------------------订单支付过程
    //模拟支付过程

    $json = "{
            \"appid\":\"\",
            \"attach\":[],
            \"bank_type\":\"\",
            \"cash_fee\":\"\",
            \"fee_type\":\"\",
            \"is_subscribe\":\"\",
            \"mch_id\":\"\",
            \"nonce_str\":\"\",
            \"openid\":\"\",
            \"out_trade_no\":\"$orderCode-144850\",
            \"result_code\":\"SUCCESS\",
            \"return_code\":\"SUCCESS\",
            \"return_msg\":\"OK\",
            \"sign\":\"EAE6E5AC280E341BD9D2E7C202D0F96A\",
            \"time_end\":\"\",
            \"total_fee\":\"0\",
            \"trade_state\":\"SUCCESS\",
            \"trade_type\":\"JSAPI\",
            \"transaction_id\":\"\"
            }";
    $result = json_decode($json, true);//dump($result);
    saveTruePayOrder($result, "0元");
    $aa = array(
        "info" => "添加成功",
        "jsApiParameters" => "",
        "orderid" => $orderCarId_new
    );


//---------------------------------------订单支付过程
//订单创建完成  改动当前表里的座位


    /*座号算法 ，先获取原车里的ID编号 ，生成数组 ，
    然后获取表单来的数组 ，
    两个比较，以原车里的为准，将表单里的去重后，加到原车数组后，
    重新生成所有的座位号
    */


    //表单过来的乘车人
    $ids_array = array();
    if ($ids != "") {
        $ids_array = explode('`', $ids);
    }


    //重新排位置
    $seatNumber = 0;
    foreach ($ids_array as $idvalue) {
        //把所有的人  从1开始重排
        $seatNumber++;
        $sql_array[] = "UPDATE `#@__order_addon_lycp` SET  `orderCarId`='$orderCarId_new', `seatNumber`='$seatNumber' WHERE id='$idvalue' ";
    }
    //dump($sql_array);
    $dsql->ExecuteNoneCommit($sql_array);//批量处理事务


    echo json_encode($aa);
    exit();


}
//保存乘务的信息
if ($dopost == 'guideInfo_save') {
    $sql = "UPDATE `x_device_automobile_uselog` SET `guideid`='$guideid' WHERE (`id`='$device_automobile_uselog_id')";
    $dsql->ExecuteNoneQuery($sql);
    echo " 成功";
    exit();
}
//清空
if ($dopost == 'clear') {
    /*座号算法 ，先获取原车里的ID编号 ，生成数组 ，
    然后获取表单来的数组 ，
    两个比较，以原车里的为准，将表单里的去重后，加到原车数组后，
    重新生成所有的座位号
    */

    //将车辆ID和座位,保存入订单子表
    $sql = "";
    $dquery = "";
    $oldids = "";//原车里的人
    $questr11 = "SELECT GROUP_CONCAT(#@__order_addon_lycp.id) AS oldids  FROM `#@__order_addon_lycp` 
                LEFT JOIN  `#@__order` ON #@__order_addon_lycp.orderid=#@__order.id 
                WHERE lineid='$lineid' AND `orderCarId`='' AND (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order.sta=1
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
    //dump("新的：" . $ids);

    $newids_array = array_merge($oldids_array, $ids_array);//合并两个
    $newids_array = array_unique($newids_array);//删除重复
    // dump($newids_array);

    //重新排位置
    $seatNumber = 0;
    foreach ($newids_array as $idvalue) {
        //把所有的人  从1开始重排
        $seatNumber++;
        $sql_array[] = "UPDATE `#@__order_addon_lycp` SET  `orderCarId`='', `seatNumber`='$seatNumber', `iscc`='0' WHERE id='$idvalue' ";
    }
    $dsql->ExecuteNoneCommit($sql_array);//批量处理事务
    echo " 成功";
    exit();


}


//变更 车辆
if ($dopost == 'carOrder_select_save') {
    /*座号算法 ，先获取原车里的ID编号 ，生成数组 ，
    然后获取表单来的数组 ，
    两个比较，以原车里的为准，将表单里的去重后，加到原车数组后，
    重新生成所有的座位号
    */

    //将车辆ID和座位,保存入订单子表
    $sql = "";
    $dquery = "";
    $oldids = "";//原车里的人
    $questr11 = "SELECT GROUP_CONCAT(#@__order_addon_lycp.id) AS oldids  FROM `#@__order_addon_lycp` 
                LEFT JOIN  `#@__order` ON #@__order_addon_lycp.orderid=#@__order.id 
                WHERE lineid='$lineid' AND `orderCarId`='$CarOrderid' AND (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order.sta=1
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
    //dump("新的：" . $ids);

    $newids_array = array_merge($oldids_array, $ids_array);//合并两个
    $newids_array = array_unique($newids_array);//删除重复
    // dump($newids_array);

    //重新排位置
    $seatNumber = 0;
    foreach ($newids_array as $idvalue) {
        //把所有的人  从1开始重排
        $seatNumber++;
        $sql_array[] = "UPDATE `#@__order_addon_lycp` SET  `orderCarId`='$CarOrderid', `seatNumber`='$seatNumber', `iscc`='0' WHERE id='$idvalue' ";
    }
    //dump($sql_array);
    $dsql->ExecuteNoneCommit($sql_array);//批量处理事务
    echo " 成功";
    exit();


}


//保存座位顺序
if ($dopost == 'moveSave') {
    //dump($moveData) ;
    $sql_array = array();
    $order_addon_lycp_id_array = explode(",", $moveData);
    $seatNumber = 0;
    //重新排位置
    $seatNumber = 0;
    foreach ($order_addon_lycp_id_array as $id) {
        $seatNumber++;
        //按顺序更新座位
        $sql_array[] = "UPDATE `#@__order_addon_lycp` SET  `seatNumber`='$seatNumber' WHERE id='$id' ";
    }
    $dsql->ExecuteNoneCommit($sql_array);//批量处理事务
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();

}
$t1 = ExecTime();
//--------------------------------获取线路名称 发车时间
$lineid = isset($lineid) ? $lineid : "";
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


$whereSql = " WHERE (#@__order.isdel=0 OR #@__order.isdel=4 )  AND  #@__order_addon_lycp.isdel=0  AND #@__order.sta=1";//显示 未被删除和已经支付的主订单  并且子订单未被业务删除

$whereSql .= " AND #@__order_addon_lycp.lineid=$lineid  ";
$whereSql .= " AND  FROM_UNIXTIME(#@__order_addon_lycp.appttime,'%Y-%m-%d')='$gotime' ";

$query = "
        SELECT 
        #@__order_addon_lycp.id,#@__order_addon_lycp.appttime,#@__order_addon_lycp.tjsite,#@__order_addon_lycp.seatNumber,#@__order_addon_lycp.orderlistztcid,
        #@__order_addon_lycp.orderCarId,
        #@__order_addon_lycp.realname,#@__order_addon_lycp.tel,#@__order_addon_lycp.idcard,
        #@__order_addon_lycp.info,#@__order_addon_lycp.infodate,#@__order_addon_lycp.infooperatorid,#@__order_addon_lycp.iscc,
        #@__order.desc,
        #@__order.clientid
        FROM #@__order_addon_lycp #@__order_addon_lycp
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
        $whereSql
        ORDER BY   #@__order_addon_lycp.orderCarId ASC, seatNumber ASC";
//dump($query);
$dsql->SetQuery($query);
$dsql->Execute();
//dump($dsql->GetTotalRow());
$info_lycp = array();
while ($row = $dsql->GetArray()) {
    $info_lycp[$row["orderCarId"]][] = array(
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
$s_tmplets = 'service/apptQuery_list.htm';//默认的
if ($dopost == "move") $s_tmplets = 'service/apptQuery_list_move.htm';//座位调整的

include DwtInclude($s_tmplets);


