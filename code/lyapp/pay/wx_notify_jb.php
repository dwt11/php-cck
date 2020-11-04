<?php
ini_set('date.timezone', 'Asia/Shanghai');
//保存到logs0919tzy文件，并保存到数据库
//error_reporting(E_ERROR);
require_once(dirname(__FILE__) . "/../include/config.php");

require_once DWTINC . "/weixin/pay/lib/WxPay.Api.php";
require_once DWTINC . '/weixin/pay/lib/WxPay.Notify.php';
require_once DWTINC . '/weixin/pay/log.php';

//初始化日志
$logHandler = new CLogFileHandler(DWTINC . "/weixin/pay/logs0919tzy/jb_" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);


 /////

class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        //Log::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
        ) {
            //Log::DEBUG("query:" . $result);
            saveTruePayOrder_jb($result);
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {

        Log::DEBUG("call back:" . json_encode($data));//这里用来判断   微信是不是在一直回调访问这个页面，用于验证  支付后是不是通知了微信
        $notfiyOutput = array();

        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);//回复给微信 结果


function saveTruePayOrder_jb($result)
{

    //Log::DEBUG("result:" . $result);
    global $DEP_TOP_ID;


    //这里支付成功，将信息写入数据库,要判断订单是否重复
    //$orderid_retsult = $result["out_trade_no"];//返回的商户订单，有时间码
    global $dsql;
    // dump($result["out_trade_no"]);
    $jbordercode = $result["out_trade_no"];//返回的商户订单
    $transaction_id = $result["transaction_id"];//返回的微信订单号

    $paytime = time();//记录日期

    //Log::DEBUG("jbordercode:" . $jbordercode);
   // Log::DEBUG("transaction_id:" . $transaction_id);

    //查询未支付的订单（刚创建的订单）sta=0  或  支付失败的订单 sta==12 sta==2
    $questr = "SELECT id,code,jbnum,clientid  FROM `#@__clientdata_jb_pay_t`  where  code='$jbordercode' and (stat=0) ";
    //Log::DEBUG("questr:" . $questr);
    //dump($questr);
    $row = $dsql->GetOne($questr);
    if (isset($row["id"]) && $row["id"] != "") {

        $orderid = $row["id"];
        $jbnum100 = $row["jbnum"];
        $jbnum_str = $jbnum100/100;
        $clientid = $row["clientid"];
        //Log::DEBUG("jbnum:" . $jbnum);
        //Log::DEBUG("total_fee:" . $result['total_fee']);

        if ($jbnum100 == $result['total_fee']) {
            //更新临时表信息
            $sql = "UPDATE `#@__clientdata_jb_pay_t`    SET stat=stat+1,paytime='$paytime',pay_transaction_id='$transaction_id' WHERE id='$orderid';    ";
            $dsql->ExecuteNoneQuery($sql);


            //先查询金币表是否有重复的充值订单号,没有了,才增加
            $questr = "SELECT id   FROM `#@__clientdata_jblog`  where  jbordercode='$jbordercode' ";
            $row = $dsql->GetOne($questr);
            if(!$row) {
                $clientjbnum100 = GetClientJBJFnumb("jb", $clientid) * 100;//获取 用户当前的金额数量
                $clientjbnum100 = $clientjbnum100 + $jbnum100;//充值后的金额数量

                //获取用户最近的余额
                $yenum100_t = 0;
                $query11 = "SELECT  yenum  FROM x_clientdata_jblog WHERE clientid='$clientid'  ORDER BY  id DESC limit 1";
                //dump($query11);
                $row11 = $dsql->GetOne($query11);
                if (isset($row11["yenum"])) {
                    //如果有上一次的余额,则新的余额等于=上一次余额+当前操作金额
                    $yenum100_t = $row11["yenum"] + $jbnum100;//上一次的余额  与当前的金额变动  加减
                } else {
                    //如果没有上一次的操作  则余额=当前操作的数量
                    $yenum100_t = $jbnum100;
                }

                //容错校验  如果余额小于0,则等于0
                if ($yenum100_t < 0) $yenum100_t = 0;


                //插入金币表 这里没有使用金币的操作过程，因为要插入微信 的订单号
                $sqladdorder = "INSERT INTO `#@__clientdata_jblog` ( `clientid`, `jbnum`, `yenum`, `createtime`, `desc`,  `pay_transaction_id`,`jbordercode`)
                            VALUES ('$clientid', '$jbnum100', '$yenum100_t', '$paytime', '金币充值', '$transaction_id', '$jbordercode');";
                $dsql->ExecuteNoneQuery($sqladdorder);

                //更新会员 金币数量
                $dsql->ExecuteNoneQuery("Update `#@__client_addon` set `jbnum`='$clientjbnum100' where clientid='$clientid' ");


                //如果用户表的余额与金币表的余额不一样,则保存日志
                if ($clientjbnum100 != $yenum100_t) {
                    require_once DWTINC . '/weixin/pay/log.php';
                    //初始化日志
                    $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_jb_jf_ye_error.log');
                    $log = Log::Init($logHandler, 15);
                    Log::DEBUG("wx_notify_jb.php-clientid:$clientid,金币表yenum100:$yenum100_t,用户表clientjbnum100:$clientjbnum100,操作数字jbnum100:$jbnum100");
                }




                $clientopenid = GetClientOpenID($clientid);//会员 的OPENid用于判断 是否发送微信通知


                if($clientopenid!=""){
                    //$realname = getOneCLientRealName($clientid);   //订单用户姓名
                    $weixinMsgDataArray = array();
                    //微信发送----充值成功通知
                    $weixinMsgDataArray["frist"] = "金币充值成功通知";
                    //$weixinMsgDataArray["accountType"] = $realname;
                    $weixinMsgDataArray["amount"] = $jbnum_str;
                    SendTemplateMessage("会员充值通知", $clientid, $DEP_TOP_ID, $weixinMsgDataArray);

                }



                //如果是有效的乘车卡会员 ，则充值多少金币，送多少积分
                $rankInfo = GetClientType("rank", $clientid);
                //dump(($rankInfo));
                $rankInfo_array = explode(",", $rankInfo);
                if (
                    /*(
                        in_array("直通车", $rankInfo_array) ||
                        in_array("合伙人", $rankInfo_array) ||
                        in_array("爱心卡", $rankInfo_array) ||
                        in_array("学生卡", $rankInfo_array)
                    )*/
                    $rankInfo!=""/*除了刚注册的会员,只要有会员身份的所有的会员卡都返积分171103修改*/
                ) {
                    $desc = "金币充值赠送 订单号:" . $jbordercode;

                    $jbnum_100_temp_bai = intval($jbnum100/10000);//取出百位数
                    $jfnum_100_temp = $jbnum_100_temp_bai*10000;//取出百位数
                    $mynumjf100 = intval($jfnum_100_temp/2);
                    //赠送积分数量为金币的100位取整后的一半 1XX 送50  2xx送100,3xx送150以此类推
                    Update_jf($clientid, $mynumjf100, $desc);//

                    if($mynumjf100>0&&$clientopenid!="") {

                        $mynumjf_str=$mynumjf100/100;
                        //sleep(5);5秒后再发
                        $weixinMsgDataArray = array();
                        //微信发送----返积分成功通知
                        $weixinMsgDataArray["frist"] = "赠送积分到账通知(会员金币充值赠送)。";
                        $weixinMsgDataArray["keyword1"] = "{$mynumjf_str}积分";
                        $weixinMsgDataArray["keyword2"] = "点击详情查看";
                        $weixinMsgDataArray["remark"] = "订单号:" . $jbnum100;
                        SendTemplateMessage("积分到帐提醒", $clientid, $DEP_TOP_ID, $weixinMsgDataArray);
                    }


                }
            }
        }
    }
}
