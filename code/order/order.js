/*后台订单公用文件*/
/*170616随后要和ORDERNEW.JS
 更换*/

$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    $('#benefitInfo').iCheck('check');
    getTotalMoney();
});
//遍历radio的点击 事件
$("input[name='benefitInfo']").on('ifChecked', function (event) {//必须选一个
    ////如果当前RADIO被选中,则取值
    var benefitInfo_CheckedNumb = $(this).val();
    //console.log( $(this));
    //console.log(benefitInfo_CheckedNumb);
    setInputValue(benefitInfo_CheckedNumb);//获取值写到input中
    getTotalMoney();
});

/*
 可以全部不选择

 //$("input[name='benefitInfo']").on('ifClicked', function (event) {ifClicked,可以全部不选择
 $(this).iCheck('uncheck');//如果当前radio被选中,再次点击则取消选中
 setInputValue(0);//初始为空
 $(this).on('ifChecked', function (event) {
 ////如果当前RADIO被选中,则取值
 var benefitInfo_CheckedNumb = $(this).val();
 //console.log( $(this));
 //console.log(benefitInfo_CheckedNumb);
 setInputValue(benefitInfo_CheckedNumb);//获取值写到input中
 getTotalMoney();
 });
 $(this).on('ifUnchecked', function (event) {
 setInputValue(0);//初始为空
 getTotalMoney();
 });
 });
 */

//设定用户选中的优惠值
function setInputValue(benefitInfo_CheckedNumb) {
    if (benefitInfo_CheckedNumb > 0) {
        var gmyh = $("#gmyh_" + benefitInfo_CheckedNumb).html();
        if (!gmyh) gmyh = "";

        var ejfhjb = $("#ejfhjb_" + benefitInfo_CheckedNumb).html();
        if (!ejfhjb) ejfhjb = "";

        var ejfhjf = $("#ejfhjf_" + benefitInfo_CheckedNumb).html();
        if (!ejfhjf) ejfhjf = "";

        var sjfhjb = $("#sjfhjb_" + benefitInfo_CheckedNumb).html();
        if (!sjfhjb) sjfhjb = "";

        var sjfhjf = $("#sjfhjf_" + benefitInfo_CheckedNumb).html();
        if (!sjfhjf) sjfhjf = "";


        var zdsyjb = $("#zdsyjb_" + benefitInfo_CheckedNumb).html();
        if (!zdsyjb) zdsyjb = "";


        var zdsyjf = $("#zdsyjf_" + benefitInfo_CheckedNumb).html();
        if (!zdsyjf) zdsyjf = "";


        $("#dk_jg").val(gmyh);
        $("#fh_ejjb").val(ejfhjb);
        $("#fh_ejjf").val(ejfhjf);
        $("#fh_sjjb").val(sjfhjb);
        $("#fh_sjjf").val(sjfhjf);
        $("#dk_jb").val(zdsyjb);
        $("#dk_jf").val(zdsyjf);
    } else {
        $("#dk_jg").val("");
        $("#fh_ejjb").val("");
        $("#fh_ejjf").val("");
        $("#fh_sjjb").val("");
        $("#fh_sjjf").val("");
        $("#dk_jb").val("");
        $("#dk_jf").val("");
    }
}

var totalMoney = 0;//订单总价
var payMoney = 0;//实际付款 显示
var buynumb = 0;//购买的数量
var price = 0;//单价原始
var dk_jb = 0;//金币抵扣
var dk_jf = 0;//积分抵扣
var dk_jg = "";//折扣单价
var fh_ejjb = 0;
var fh_ejjf = 0;
var fh_sjjb = 0;
var fh_sjjf = 0;

var ye_jb = 0;//余额金币
var ye_jf = 0;//余额积分

var t_dk_jb = 0;
var t_dk_jf = 0;
//当商品价格改变时  计算总价
function getTotalMoney() {
    //170217因为car_add.js日期比较后显示错误,但日期比较后,还要计算总额
    // $("#error").text("");

    buynumb = parseFloat($("#buyNumb").html());
    if (!buynumb) buynumb = 0;


    price = parseFloat($("#price").html());
    if (!price) price = 0;

    dk_jg = parseFloat($("#dk_jg").val());
    if (dk_jg===false) dk_jg = "";

    fh_ejjb = parseFloat($("#fh_ejjb").val());
    if (!fh_ejjb) fh_ejjb = 0;

    fh_ejjf = parseFloat($("#fh_ejjf").val());
    if (!fh_ejjf) fh_ejjf = 0;

    fh_sjjb = parseFloat($("#fh_sjjb").val());
    if (!fh_sjjb) fh_sjjb = 0;

    fh_sjjf = parseFloat($("#fh_sjjf").val());
    if (!fh_sjjf) fh_sjjf = 0;

    dk_jb = parseFloat($("#dk_jb").val());
    if (!dk_jb) dk_jb = 0;

    dk_jf = parseFloat($("#dk_jf").val());
    if (!dk_jf) dk_jf = 0;

    ye_jb = parseFloat($("#ye_jb").val());
    if (!ye_jb) ye_jb = 0;

    ye_jf = parseFloat($("#ye_jf").val());
    if (!ye_jf) ye_jf = 0;

    var gmyh_dkjb = 0;//购买以后的折扣金币


    //-----------------------------计算总价
    totalMoney = parseFloat(accMul(price, buynumb));//商品实际价格
    if (totalMoney > 0) {
        if (parseFloat(dk_jg) == 0) totalMoney = 0;
        if (dk_jg > 0) totalMoney = parseFloat(accMul(dk_jg, buynumb));//如有优惠后价格 ,则总价变为优惠后价格

        if (dk_jb > 0) t_dk_jb = parseFloat(accMul(dk_jb, buynumb));
        if (dk_jf > 0) t_dk_jf = parseFloat(accMul(dk_jf, buynumb));

        //if (t_dk_jb > dk_jb) t_dk_jb = dk_jb;
        // if (t_dk_jf > dk_jf) t_dk_jf = dk_jf;


        //抵扣和用户余额判断
        if (dk_jb > 0 && ye_jb > 0) {
            if (ye_jb < t_dk_jb) {
                //如果用户余额小于可以使用金币 则可以使用金币等于余额
                t_dk_jb = ye_jb;
            }
            /*else {
             t_dk_jb = dk_jb;
             }*/
        } else {
            t_dk_jb = 0;
        }
        if (dk_jf > 0 && ye_jf > 0) {
            if (ye_jf < t_dk_jf) {
                //如果用户余额小于可以使用金币 则可以使用金币等于余额
                t_dk_jf = ye_jf;
            }
            /* else {
             t_dk_jf = dk_jf;
             }*/
        } else {
            t_dk_jf = 0;
        }


        //金币使用量与优惠后价格比较
        if (totalMoney > 0) {
            //如果最多使用金币大于0
            if (t_dk_jb > 0) {
                //如果折扣后价格 大于 金币抵扣价格 则折扣后价格减去 金币折扣价格 剩下的供积分判断
                if (totalMoney > t_dk_jb) {
                    gmyh_dkjb = totalMoney - t_dk_jb;
                } else {
                    //如果折扣后价格 小于等金币价格  则金币抵扣价格等于折扣后价格
                    t_dk_jb = totalMoney;
                }
            }
            //如果最多可用积分大于0
            if (t_dk_jf > 0) {
                //如果有最多可用金币
                if (t_dk_jb > 0) {
                    //小于等于可以积分
                    //if (gmyh_dkjb > t_dk_jf) t_dk_jb = dk_jb;
                    if (parseFloat(gmyh_dkjb) <= t_dk_jf) {
                        t_dk_jf = gmyh_dkjb;
                    }
                } else {
                    //如果最多可用金币没有
                    if (parseFloat(totalMoney) <= t_dk_jf) {
                        //如果折扣后价格 小于等金币价格  则金币抵扣价格等于折扣后价格
                        t_dk_jf = totalMoney;
                    }
                }
            }
            //结束 --------------------根据用户余额和折扣后价格 计算最终付款使用的金币 积分
        }


    } else {
        t_dk_jf = t_dk_jb = 0;
    }


    //用户实际支付的价格
    payMoney = parseFloat(totalMoney - t_dk_jf - t_dk_jb);//计算实际 支付
    if (parseFloat(payMoney) == 0 || parseFloat(payMoney) < 0) payMoney = 0;//如果 抵扣完小于0,则需要支付0
    $("#payMoney").html(fmoney(payMoney));
    $("#totalMoney").val(fmoney(totalMoney));

    $("#t_dk_jb").html(parseInt(t_dk_jb));//最终金币
    $("#t_dk_jf").html(parseInt(t_dk_jf));//最终积分
    $("#t_pay").html(fmoney(payMoney));//最终支付
    $("#t_total").html(fmoney(totalMoney));//最终总价
    $("#ye_jf_d").html(parseInt(ye_jf));//余额
    $("#ye_jb_d").html(parseInt(ye_jb));//余额


}


/*
 * orderUrl   不同订单的保存页面
 *
 * orderUrl,订单创建 地址
 * backUrl 创建后 返回地址
 * */
function gopay(orderUrl, backUrl) {
    var isdebug = false;
//加载层
    //loading层


    var goodsid = $("#goodsid").val();
    if (goodsid == '') {
        $("#error").text("商品信息获取错误,请刷新页面");
        return false;
    }

    var clientid = $("#clientid").val();
    if (clientid == '') {
        $("#error").text("会员信息获取错误,请刷新页面");
        return false;
    }

    if (!buynumb > 0) {
        $("#error").text("请选择商品");
        return false;
    }


    var from_str = from_validate();
    if (!from_str) {
        //$("#error").text("订单提交失败,请刷新页面1");
        return false;
    }//表单验证未通过

    $("#error").text("");

    var paytype = $('input[name="paytype"]:checked').val();


    //--------------优惠信息获取
    var benefitCreatetime = $("#benefitCreatetime").val();
    if (!benefitCreatetime) benefitCreatetime = 0;


    var desc = $("#desc").val();
    //var desc = encodeURIComponent($("#desc").val());
    if (!desc) {
        desc = "";
    } else {
        desc = encodeURIComponent(desc);
    }


    //进度条
    var index = layer.load(2, {
        shade: [0.1, '#fff'] //0.1透明度的白色背景
    });
    $.ajax({
        type: "post",
        url: orderUrl + from_str,
        data: {
            goodsid: goodsid,
            clientid: clientid,
            paytype: paytype,
            desc: desc,
            payMoney: payMoney,
            dk_jb: t_dk_jb,
            dk_jf: t_dk_jf,
            totalMoney: totalMoney,
            benefitCreatetime: benefitCreatetime,
            fh_ejjb: fh_ejjb,
            fh_ejjf: fh_ejjf,
            fh_sjjb: fh_sjjb,
            fh_sjjf: fh_sjjf,
            buynumb: buynumb
        },
        dataType: 'json',
        success: function (data) {
            layer.closeAll('loading'); //关闭加载层
            if (data.info == "添加成功") {
                layer.msg(data.info, {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    if (!isdebug)window.location.href = backUrl;
                });
            } else {
                layer.msg("订单创建失败 请在订单管理中核对,原因:" + data.info, {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                   //这里出错后,不要跳转,还在当前页面 让用户选择操作if (!isdebug) window.location.href = backUrl;
                });

            }
        }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.closeAll('loading'); //关闭加载层
            layer.msg("订单未正常创建 请在订单管理中核对 ", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                //这里要考虑一下,是否自动刷新 当前页面
            });
        }

    });


    //return true;
}



//用户选择日期与当前日期比较
//isnow  是否当前合适日期 1是, 0与当前日期+30天比较(用户选择日期必须在三十天内) 并且不能早于当前时间
function compareDate(xz_date) {
    var now = new Date;
    var now30 = new Date(now);
    now30.setDate(now.getDate() + 30);//30天后的日期
    var xzdate = new Date(xz_date);//选择的日期
    xzdate = new Date(new Date(xzdate.toLocaleDateString()).getTime() + 24 * 60 * 60 * 1000 - 1);//选择的日期 默认加上当天的 23点,与服务器时间比较 [这个比较通过后,再在save.php页面也提前预约时间比较]

    if (now > xzdate) {
        //alert("之前的日期");
        return false;
    } else if (now30 < xzdate) {
        //alert("大于30天日期");
        return false;
    } else {
        return true; //alert("一样的日期或30天以内的日期");
    }
}



//计算
function accMul(arg1, arg2) {
    var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
    try {
        m += s1.split(".")[1].length
    } catch (e) {
    }
    try {
        m += s2.split(".")[1].length
    } catch (e) {
    }
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}


//格式化金额为两位小数S金额 N小数位数
function fmoney(s, n) {
    n = n > 0 && n <= 20 ? n : 2;
    s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
    var l = s.split(".")[0].split("").reverse(),
        r = s.split(".")[1];
    t = "";
    for (iq = 0; iq < l.length; iq++) {
        t += l[iq] + ((iq + 1) % 3 == 0 && (iq + 1) != l.length ? "," : "");
    }
    return t.split("").reverse().join("") + "." + r;
}