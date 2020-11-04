/**
 * Created by dell on 2016-06-15.
 */
//------------屏蔽微信 的分享按钮
function onBridgeReady() {
    //WeixinJSBridge.call('showOptionMenu');
    WeixinJSBridge.call('hideOptionMenu');
}
if (typeof WeixinJSBridge == "undefined") {
    if (document.addEventListener) {
        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    } else if (document.attachEvent) {
        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    }
} else {
    onBridgeReady();
}
//------------屏蔽微信 的分享按钮
