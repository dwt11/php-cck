<?php
require_once("../config.php");


//表单过来的乘车人
if ($ids == "") {
    echo "未选择人员";
    exit();
}
$ids = str_replace('`', ',', $ids);

$truesendnum = 0;
$falsesendnum = 0;
//dump($info);

$query = "
        SELECT 
        #@__order_addon_lycp_old.id AS addonlycpid,
        #@__order_addon_lycp_old.appttime,#@__order_addon_lycp_old.seatNumber,
        #@__order_addon_lycp_old.deviceid,
        #@__order_addon_lycp_old.realname,
        #@__order_addon_lycp_old.tel,
        #@__order_addon_lycp_old.lineid,
        #@__order.clientid,
        #@__order.ordernum
        FROM #@__order_addon_lycp_old  
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp_old.orderid
        WHERE #@__order_addon_lycp_old.id IN($ids)
        ORDER BY     seatNumber ASC";

 //dump($query);
$dsql->SetQuery($query);
$dsql->Execute();
//dump($dsql->GetTotalRow());
$info = array();
$lineid = "";
$deviceid = "";
$devicename = "";
$driverName=$guideidName="";
while ($row_weixin170417 = $dsql->GetArray()) {

    //如果线路不同，才获取新的值
    if ($lineid != $row_weixin170417["lineid"]) {
        $appttime = $row_weixin170417["appttime"];
        $lineid = $row_weixin170417["lineid"];
        //获取线路名称和发车日期
        $xl_dest = $xl_tmp = $xl_gotime = "";
        $arcQuery = "SELECT #@__line.gotime,#@__line.tmp,goods.goodsname  FROM #@__line
              LEFT JOIN #@__goods as goods ON goods.id=#@__line.goodsid
              WHERE  #@__line.id='$lineid' ";
        $arcRow = $dsql->GetOne($arcQuery);
        if ($arcRow) {
            $goodsname = $arcRow["goodsname"];
            //if(strlen($goodsname)>25)$goodsname=cn_substr_utf8($goodsname,25)."...";
            $xl_tmp = $arcRow["tmp"];
            $xl_gotime = $arcRow["gotime"];
        }
        $godate = "";
        $appttime_str = MyDate("Y年m月d日", $appttime);
        if ($xl_tmp == '每日') {
            //固定线路 取用户输入的发车日期
            $godate = $appttime_str . " " . date(' H时i分', $xl_gotime);
        } elseif ($xl_tmp == '临时') {
            //临时线路取线路的发车日期
            $godate = date('Y年m月d日 H时i分', $xl_gotime);
        } else {
            $godate = $appttime_str;
        }

    }


    //如果车不同，才获取新的值
    if ($deviceid != $row_weixin170417["deviceid"] && $row_weixin170417["deviceid"] != "") {
        $deviceid = $row_weixin170417["deviceid"];
        //获取车牌号
        $arcQuery55 = "SELECT devicename  FROM #@__device  WHERE  id='$deviceid' ";
        $arcRow55 = $dsql->GetOne($arcQuery55);
        if ($arcRow55) {
            $devicename = $arcRow55["devicename"];
        }


        $questr11 = "SELECT driverid,guideid  FROM `#@__device_automobile_uselog`       WHERE lineid='{$lineid}' AND `deviceid`='$deviceid'                 ";
        $rowarc11 = $dsql->GetOne($questr11);
        if (isset($rowarc11)) {
            $driverid = $rowarc11["driverid"];
            $guideid = $rowarc11["guideid"];
            if ($driverid > 0) {
                $driverName = GetEmpNameById($driverid) . " " . GetEmpPhoneById($driverid);
            }
            if ($guideid > 0) {
                $guideidName = GetEmpNameById($guideid) . " " . GetEmpPhoneById($guideid);
            }
        }


    }
    //$clientid="1090";测试使用
    $clientid = $row_weixin170417["clientid"];
    $info[] = array(
        "seatNumber" => $row_weixin170417["seatNumber"],
        "realname" => $row_weixin170417["realname"],
        "ordernum" => $row_weixin170417["ordernum"],
        "clientid" => $clientid,
        "devicename" => $devicename,
        "driverName" => $driverName,
        "guideidName" => $guideidName,
        "goodsname" => $goodsname,
        "godate" => $godate
    );


    $name = "旅游订单预订成功通知";
    $weixinMsgDataArray = array();
    foreach ($info as $row_info) {
        $weixinMsgDataArray["frist"] = "您的线路预约已经确认,请按时乘车出行。";
        $weixinMsgDataArray["ordernum"] = "LYCP" . $row_info["ordernum"];
        $weixinMsgDataArray["goodsname"] = $row_info["goodsname"];
        $weixinMsgDataArray["godate"] = $row_info["godate"];
        $remark = "出行人姓名:" . $row_info["realname"];
        if ($row_info["devicename"] != "") $remark .= " \\n车牌号:" . $row_info["devicename"];
        if ($row_info["seatNumber"] != "") $remark .= " 座位号:" . GetIntAddZero($row_info["seatNumber"], 2);
        if ($row_info["driverName"] != "") $remark .= " \\n司机:" .$row_info["driverName"];
        if ($row_info["guideidName"] != "") $remark .= " \\n乘务:" . $row_info["guideidName"];





        $weixinMsgDataArray["remark"] = $remark;
    }
    //dump($clientid);
    if ($clientid > 0) {
        $return_info = SendTemplateMessage($name, $clientid, "17", $weixinMsgDataArray);
        //dump($return_info);
        $addonlycpid=$row_weixin170417["addonlycpid"];
        if ($return_info == "发送成功") {

            //如果有发送记录就加1  没有就添加
            $sql11111 = "INSERT INTO x_order_addon_lycp_weixin (addonlycpid,weixinSendNumb) VALUES ($addonlycpid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1; ";
            //dump($sql11111);
            $dsql->ExecuteNoneQuery($sql11111);


            $truesendnum++;
        } elseif ($return_info == "未获取到会员OPENID") {

            $phoneMsgDataArray = array();
            foreach ($info as $row_info) {
                $phoneMsgDataArray["ordernum"] = "LYCP" . $row_info["ordernum"];
                //$weixinMsgDataArray["goodsname"] = $row_info["goodsname"];
                $phoneMsgDataArray["godate"] = $row_info["godate"];

                if ($row_info["devicename"] != "") {
                    $phoneMsgDataArray["devicename"]=   $row_info["devicename"];
                }else{
                    $phoneMsgDataArray["devicename"]="  暂无 ";
                }
                if ($row_info["seatNumber"] != "") {
                    $phoneMsgDataArray["seatNumber"]=  GetIntAddZero($row_info["seatNumber"], 2);
                }else{
                    $phoneMsgDataArray["seatNumber"]="  暂无 ";
                }
                $phoneMsgDataArray["remark"] = $remark;
            }

            $mobilephone=$row_weixin170417["tel"];
            $return = SendPhoneMSG($mobilephone, $name="旅游订单预订成功通知", $clientid, $DEPID=17,$data=$phoneMsgDataArray);
            //这里没有判断$return是否成功
            //如果有发送记录就加1  没有就添加
            $sql11111 = "INSERT INTO x_order_addon_lycp_weixin (addonlycpid,weixinSendNumb) VALUES ($addonlycpid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1; ";
            //dump($sql11111);
            $dsql->ExecuteNoneQuery($sql11111);


            $truesendnum++;

        }else {
            $falsesendnum++;
        }
    }
    // sleep(5);//170403删除不用延时了
}

$return_info = "";
if ($truesendnum > 0) $return_info = "发送成功{$truesendnum}条";
if ($falsesendnum > 0) $return_info .= "发送失败{$falsesendnum}条";
echo $return_info;
exit();


