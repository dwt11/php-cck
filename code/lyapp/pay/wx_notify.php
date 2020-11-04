<?php
ini_set('date.timezone', 'Asia/Shanghai');
//保存到logs0919tzy文件，并保存到数据库
//error_reporting(E_ERROR);
require_once(dirname(__FILE__) . "/../include/config.php");

require_once DWTINC . "/weixin/pay/lib/WxPay.Api.php";
require_once DWTINC . '/weixin/pay/lib/WxPay.Notify.php';
require_once DWTINC . '/weixin/pay/log.php';

//初始化日志
//这里已经在数据库配置未引用????171111
$logHandler = new CLogFileHandler(DWTINC . "/weixin/pay/logs0919tzy/" . date('Y-m-d') . '.log');
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
            //成功支付后,与入订单
            saveTruePayOrder($result,"微信");
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


