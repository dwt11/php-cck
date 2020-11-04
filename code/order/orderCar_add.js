$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    $('#benefitInfo').iCheck('check');
    $('#carNumb').spinner(
        {
            value: 1,
            min: 1,
            max: 5,
            step: 1
        });


    //初始值
    var carNumb = 1;
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    pickedFunc();
    getTotalMoney();


    //有改变后的
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        //如果值不一样,则代表了改变
        //车辆数量改变
        if ($("#carNumb").val() != carNumb) {
            //console.log($("#lineid").val()+"----"+lineid);
            carNumb = $("#carNumb").val();//保存改变后的值
            getTotalMoney();
            console.log("车辆:" + carNumb);
        }


        //日期改变
        if ($("#start_date").val() != start_date) {
            //console.log($("#lineid").val()+"----"+lineid);
            start_date = $("#start_date").val();//保存改变后的值
            var timebool = compareDate(start_date);
            if (!timebool) {
                $("#error").text("取车日期需在30天以内,并不能小于今天");
                return false;
            }
            pickedFunc();
            getTotalMoney();
            console.log("开始日期" + start_date);
        }
        if ($("#end_date").val() != end_date) {
            //console.log($("#lineid").val()+"----"+lineid);
            end_date = $("#end_date").val();//保存改变后的值
            var timebool = compareDate(end_date);
            if (!timebool) {
                $("#error").text("还车日期需在30天以内,并不能小于今天");
                return false;
            }
            pickedFunc();
            getTotalMoney();
            console.log("结束日期" + end_date);
        }
    }
});


//遍历radio的点击 事件
$("input[name='benefitInfo']").on('ifChecked', function (event) {
    //必须选一个
    ////如果当前RADIO被选中,则取值
    var benefitInfo_CheckedNumb = $(this).val();
    if (benefitInfo_CheckedNumb > 0) {


        //直通车使用金币数量
        var jbnum_0 = $("#zdsyjb_" + benefitInfo_CheckedNumb).html();
        if (!jbnum_0) jbnum_0 = "";

        //直通车使用积分数量
        var jfnum_0 = $("#zdsyjf_" + benefitInfo_CheckedNumb).html();
        if (!jfnum_0) jfnum_0 = "";

        var hyknmae = $("#hykname_" + benefitInfo_CheckedNumb).html();
        var benefitInfo = hyknmae + ":金币" + jbnum_0 + " 积分" + jfnum_0;
        $("#benefitInfo_text").val(benefitInfo);//优惠信息

        $("#jbnum").val(jbnum_0);
        $("#jfnum").val(jfnum_0);
    } else {

        $("#jbnum").val("");
        $("#jfnum").val("");
    }
    getTotalMoney();
});

var totalMoney = 0;//订单总价  所有积分加金币
var payMoney = 0;//实际付款 显示
var buynumb_all = 0;//直通车+其他 人的总数
var jbnum = 0;//直通车金币单价
var jfnum = 0;//直通车积分单价
var jbnum_basic = 0;//其他 会员金币单价
var jfnum_basic = 0;//其他 会员积分单价
var ye_jb = 0;//余额金币
var ye_jf = 0;//余额积分

var jbnum_all = 0;//要使用的金币总合
var jfnum_all = 0;//要使用的积分总合


var t_dk_jb = 0; //订单可以使用的金币
var t_dk_jf = 0; //订单可以使用的金币
//当商品价格改变时  计算总价
function getTotalMoney() {


    var dayNumb = $("#dayNumb").html();
    var carNumb = $("#carNumb").val();

    buynumb_all = dayNumb * carNumb;
    console.log("件数:" + buynumb_all);
    $("#buyNumb").html(buynumb_all);//总件数


    jbnum_basic = parseFloat($("#jbnum_basic").html());
    if (!jbnum_basic) jbnum_basic = 0;

    jfnum_basic = parseFloat($("#jfnum_basic").html());
    if (!jfnum_basic) jfnum_basic = 0;


    jbnum = parseFloat($("#jbnum").val());
    if (!jbnum && jbnum != 0) jbnum = jbnum_basic;//如果没有优惠规则，则金币等于基础价

    jfnum = parseFloat($("#jfnum").val());
    if (!jfnum && jbnum != 0) jfnum = jfnum_basic;//如果没有优惠规则，则积分等于基础价


    jbnum_all = parseInt(accMul(jbnum, buynumb_all));
    jfnum_all = parseInt(accMul(jfnum, buynumb_all));

    totalMoney = jbnum_all + jfnum_all;

    ye_jb = parseFloat($("#ye_jb").val());
    if (!ye_jb) ye_jb = 0;

    ye_jf = parseFloat($("#ye_jf").val());
    if (!ye_jf) ye_jf = 0;


    var gmyh_ye_jb = 0;//余额减去使用的金币问题后的剩余


    if (jbnum_all == 0) t_dk_jb = 0;//初始化一下，如果不初始化 当点击其他 人增加了使用金币后，再删除其他 后，删除到最后一个时 这个t_dk_jb不回0
    if (jfnum_all == 0) t_dk_jf = 0;//初始化一下，如果不初始化 当点击其他 人增加了使用金币后，再删除其他 后，删除到最后一个时 这个t_dk_jb不回0

    //1先计算金币
    if (ye_jb > 0 && jbnum_all > 0) {
        //余额大于0
        if (ye_jb > jbnum_all) {
            //余额大于金币总额
            gmyh_ye_jb = ye_jb - jbnum_all;//计算出使用后余额，供积分使用
            t_dk_jb = jbnum_all;
        } else {
            //余额小于金币总额，则现金=金币总额-余额
            payMoney = jbnum_all - ye_jb;
            t_dk_jb = ye_jb;
        }
    } else {
        //没有余额，则金币总额 就是支付的现金
        payMoney = jbnum_all;
    }


    var jfnum_all_sy = 0;//剩余未抵扣的积分总额
    //2计算积分使用，先使用积分余额
    if (ye_jf > 0 && jfnum_all > 0) {
        //积分余额大于0
        //1、先扣积分余额
        if (ye_jf > jfnum_all) {
            //积分余额大于积分总额
            t_dk_jf = jfnum_all;//可以使用的积分为积分总额
        } else {
            t_dk_jf = ye_jf;//可以使用的积分为余额积分
            jfnum_all_sy = jfnum_all - ye_jf;//余额积分不够使用，则将积分总额改为 =积分总额 -余额积分，供下面金币再判断
        }
    } else {
        payMoney += jfnum_all;//现金=现金+积分未抵扣的//这里有问题 170708 如果JF余额为0的话,应该去扣金币,而不是加现金,修改方法参见 public.js中的jb_jf_xj
    }


    // 3积分余额不够  则用金币扣 金币使用后余额大于0
    if (gmyh_ye_jb > 0 && jfnum_all_sy > 0) {
        if (gmyh_ye_jb > jfnum_all_sy) {
            t_dk_jb += jfnum_all_sy;//本订单使用了金币后余额大于积分剩下的作用金额，则再累加使用金币
        } else {
            t_dk_jb += gmyh_ye_jb;//如果本订单使用了金币后余额 小于 积分剩下的作用金额 则  金币用量加上 使用后的金币余额

            jfnum_all_sy = jfnum_all_sy - gmyh_ye_jb;//计算剩下未抵扣的积分
            payMoney += jfnum_all_sy;//现金=现金+积分未抵扣的
        }
    } else {
        payMoney += jfnum_all_sy;//现金=现金+积分未抵扣的
    }


    if (parseFloat(payMoney) == 0 || parseFloat(payMoney) < 0) payMoney = 0;//检验支付的现金额


    if (t_dk_jb > 0 || t_dk_jf > 0) {
        $("#paydiv").show();//显示支付密码输入框
    } else {
        $("#paydiv").hide()
    }

    $("#payMoney").html(parseInt(payMoney));
    $("#totalMoney").val(parseInt(totalMoney));


    $("#t_dk_jb").html(parseInt(t_dk_jb));//最终使用金币
    $("#t_dk_jf").html(parseInt(t_dk_jf));//最终使用积分
    $("#t_pay").html(parseInt(payMoney));//最终支付现金
    $("#buynumb_all").html(parseInt(buynumb_all));//商品总数
    $("#t_total_jb").html(parseInt(jbnum_all));//商品金币总价
    $("#t_total_jf").html(parseInt(jfnum_all));//商品积分总价
    $("#ye_jf_d").html(parseInt(ye_jf));//余额

//ye_jb=ye_jb-t_dk_jb;
    $("#ye_jb_d").html(parseInt(ye_jb));
//余额


}

// 计算两个日期的间隔天数
function pickedFunc() {
    console.clear();
    Date.prototype.dateDiff = function (interval, objDate) {
        //若参数不足或 objDate 不是日期物件则回传 undefined
        if (arguments.length < 2 || objDate.constructor != Date) return undefined;
        switch (interval) {
            //计算秒差
            // case "s":return parseInt((objDate-this)/1000);
            //计算分差
            case "n":
                return parseInt(Math.round(((objDate - this) / 60000) * 100) / 100);
            //计算时差
            case "h":
                return Math.round(((objDate - this) / 3600000) * 100) / 100;
            //计算日差
            case "d":
                return parseInt((objDate - this) / 86400000) + 1;
            //计算月差
            // case "m":return (objDate.getMonth()+1)+((objDate.getFullYear()-this.getFullYear())*12)-(this.getMonth()+1);
            //计算年差
            // case "y":return objDate.getFullYear()-this.getFullYear();

            //输入有误
            default:
                $("#error").text("日期有误");
                return undefined;
        }
    }
    //document.all.dateChangDu.value = document.all.jx_date.value;
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    var sDT = new Date(start_date);
    var eDT = new Date(end_date);
    var daynumb_temp = sDT.dateDiff("d", eDT);
    if (daynumb_temp < 0) {
        daynumb_temp = 1;
        $("#error").text("取车日期要早于还车日期");
        return false;
    } else {
        $("#error").text("");
    }
    console.log("天数:" + daynumb_temp);
    $("#dayNumb").html(daynumb_temp);
    return true;

}

/*
 * orderUrl   不同订单的保存页面
 * orderUrl  支付地址
 * ,lyhtbool=false  是否判断旅游合同
 * */
function gopay() {
    var isdebug = false;
    //加载层
    //loading层
    var goodsid = $("#goodsid").val();
    if (goodsid == '') {
        $("#error_str").text("商品信息获取错误,请刷新页面1");
        $("#error").show();
        return false;
    }

    var clientid = $("#clientid").val();
    if (clientid == '') {
        $("#error").text("会员信息获取错误,请刷新页面");
        return false;
    }

    if (!buynumb_all > 0) {
        $("#error").text("请选择日期和车辆数量");
        return false;
    }


    if (payMoney > 0) {
        $("#error").text("金币不够，无法预约");
        return false;
    }

    //--------------优惠信息获取
    //只有正规商品才获取优惠信息
    var benefitInfo = $("#benefitInfo_text").val();
    if (!benefitInfo) benefitInfo = "非会员单价:金币" + jbnum + " 积分" + jfnum;//如果没有优惠信息，则使用默认的非会员价格
    benefitInfo = encodeURIComponent(benefitInfo);

    var from_str = from_validate();
    if (!from_str) {
        //$("#error").text("订单提交失败,请刷新页面1");
        return false;
    }//表单验证未通过

    $("#error").text("");

    var paytype = $('input[name="paytype"]:checked').val();


    var desc = $("#desc").val();
    if (!desc) {
        desc = "";
    } else {
        desc = encodeURIComponent(desc);
    }

    console.log("天数:" + benefitInfo);


    var backUrl = "orderCar.php";
    //进度条
    var index = layer.load(2, {
        shade: [0.1, '#fff'] //0.1透明度的白色背景
    });
    $.ajax({
        type: "post",
        url: "orderCar_add.php?dopost=save" + from_str,
        data: {
            goodsid: goodsid,
            clientid: clientid,
            paytype: paytype,
            desc: desc,
            payMoney: payMoney,
            dk_jb: t_dk_jb,
            dk_jf: t_dk_jf,
            totalMoney: totalMoney,
            benefitInfo: benefitInfo,
            buynumb: buynumb_all
        },
        dataType: 'json',
        success: function (data) {
            layer.closeAll('loading'); //关闭加载层
            if (data.info == "添加成功") {
                layer.msg(data.info, {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    if (!isdebug) window.location.href = backUrl;
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
            layer.msg("订单未正常创建 请在订单管理中核对", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                //这里要考虑一下,是否自动刷新 当前页面
            });
        }

    });
}
function from_validate() {
    $("#error").text("");
    if (!pickedFunc())return false;

    var goodsid = $("#goodsid").val();
    var carNumb = $("#carNumb").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();


    var from_str = "&goodsid=" + goodsid + "&carNumb=" + carNumb + "&start_date=" + start_date + "&end_date=" + end_date;

    var m_length = 0;
    var m_value_str = "";
    var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
    var mobile_bool = true;
    if ($('#mobilephone').length > 0) {
        m_length = $('#mobilephone').val().length;
        m_value_str = $('#mobilephone').val();
        mobile_bool = mobile.test(m_value_str);
    }


    if ($('#realname').length > 0 && $('#realname').val() == '') {
        $("#error").text("必须输入姓名");
        return false;
    } else if (m_length > 0 && !mobile_bool) {
        $("#error").text("手机号码格式不对");
        return false;
    }

    if ($('#realname').length > 0) from_str += '&realname=' + encodeURIComponent($('#realname').val()) + '&mobilephone=' + encodeURIComponent($('#mobilephone').val());

    return from_str;
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
