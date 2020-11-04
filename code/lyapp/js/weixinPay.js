/**
 * Created by dell on 2016-06-15.
 */



/*调用微信支付 两个地方使用 订单付款和金币充值*/
/*
* jsApiParameters,url_href
* */
function start_wx_pay(jsApiParameters,url_href) {
    //console.log(jsApiParameters);
    //调用微信JS api 支付
    function jsApiCall() {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest',
            jsApiParameters, function (res) {
                WeixinJSBridge.log(res.err_msg);
                //alert('code:'+res.err_code);
                //alert('desc:'+res.err_desc);
                //alert('msg:'+res.err_msg);
                parent.location.href = url_href;

                //无论成功与否都跳转到支付结果页面
                //return "complete";
            }
        );
    }

    function callpay() {
        if (typeof WeixinJSBridge == "undefined") {
            if (document.addEventListener) {
                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
            } else if (document.attachEvent) {
                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
            }
        } else {
            jsApiCall();
        }
    }

    callpay();
}