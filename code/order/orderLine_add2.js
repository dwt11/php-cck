$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    //getTotalMoney();
});


//---------------------以下是乘车卡输入框
$("input[name^='cck_']").on('ifChecked', function (event) {//必须选一个
    ////选后加数量
    var enname = $(this).attr("id");//表单名称
    enname = enname.replace("cck_", "");
    console.log(enname);
    //console.log(benefitInfo_CheckedNumb);
    var ccknumber = $("#buynumb_" + enname).html();
    if (!ccknumber) ccknumber = 0;
    ccknumber++;
    $("#buynumb_" + enname).html(ccknumber);
    getTotalMoney();
});

$("input[name^='cck_']").on('ifUnchecked', function (event) {
    //不选后减数量
    var enname = $(this).attr("id");//表单名称
    enname = enname.replace("cck_", "");
    var ccknumber = $("#buynumb_" + enname).html();
    if (!ccknumber) ccknumber = 0;
    if (ccknumber > 0) {
        ccknumber--;
        $("#buynumb_" + enname).html(ccknumber);
        getTotalMoney();
    }
    if (ccknumber == 0) $("#buynumb_" + enname).html("0");
});
//--------------------- 乘车卡输入框


var totalMoney = 0;//订单总价  所有积分加金币
var payMoney = 0;//实际付款 显示
var buynumb_all = 0;//直通车+其他 人的总数
var buynumb_basic = 0;//其他 人数量
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
    payMoney=0;//初始化  一下170401,每次调用都重新计算这个值
    var benefitInfo = "";//优惠信息

    /*------------------------默认其他人的 数量 金币 积分取值*/
    buynumb_basic = parseFloat($("#buyNumb").html());//其他人的数量
    if (!buynumb_basic) buynumb_basic = 0;

    jbnum_basic = parseFloat($("#jbnum_basic").html());//基础价格
    if (!jbnum_basic) jbnum_basic = 0;

    jfnum_basic = parseFloat($("#jfnum_basic").html());//基础价格
    if (!jfnum_basic) jfnum_basic = 0;
    if (buynumb_basic > 0) {
        benefitInfo += " [非会员]金币:" + jbnum_basic + " 积分:" + jfnum_basic;
    }
    /*------------------------默认其他人的 数量 金币 积分取值*/


    /*-----------------------乘车卡的 数量 金币 积分取值*/


    var jbnum_all_ztc = 0;//用户选择卡后jb总数

    var jfnum_all_ztc = 0;//用户选择卡后jf总数
    var buynumb_all_ztc = 0;//用户选择卡后商品总数


    $('input[name^="cck_"]:checked').each(function () {

        var e_name = $(this).attr("id");//表单名称
        var e_name_hz = e_name.replace("cck_", "");//表单后辍名称
        var buynumb_t = $("#buynumb_" + e_name_hz).html();//数量
        var ztcclienttype_t = $("#ztcclienttype_" + e_name_hz).html();//中文卡名称

            var jbnum_t = parseInt($("#zdsyjb_" + e_name_hz).html());//单价
            // 当前类型有数量  优惠金币大于0,  并且优惠信息中不包含此类型
            if (buynumb_t > 0 && jbnum_t > 0 && !(benefitInfo.indexOf("[" + ztcclienttype_t + "]金币") >= 0)) {
                benefitInfo += " [" + ztcclienttype_t + "]金币:" + jbnum_t;
            }
            if (!jbnum_t && jbnum_t != 0) jbnum_t = jbnum_basic;//如果没有优惠规则，则金币等于基础价

            var jfnum_t = parseInt($("#zdsyjf_" + e_name_hz).html());//单价
            if (buynumb_t > 0 && jfnum_t > 0&& !(benefitInfo.indexOf("[" + ztcclienttype_t + "]积分") >= 0)) {
                benefitInfo += " [" + ztcclienttype_t + "]积分:" + jfnum_t;
            }
            if (!jfnum_t && jfnum_t != 0) jfnum_t = jfnum_basic;//如果没有优惠规则，则积分等于基础价


        jbnum_all_ztc += parseInt(accMul(jbnum_t, 1));
        jfnum_all_ztc += parseInt(accMul(jfnum_t, 1));
        buynumb_all_ztc +=1;
    });
    $("#benefitInfo_text").val(benefitInfo);//优惠信息
    /*-----------------------乘车卡的 数量 金币 积分取值*/


    jbnum_all = parseInt(jbnum_all_ztc) + parseInt(accMul(jbnum_basic, buynumb_basic));
    jfnum_all = parseInt(jfnum_all_ztc) + parseInt(accMul(jfnum_basic, buynumb_basic));
    buynumb_all = buynumb_basic + buynumb_all_ztc;

    totalMoney = jbnum_all + jfnum_all;

    ye_jb = parseFloat($("#ye_jb").val());
    if (!ye_jb) ye_jb = 0;

    ye_jf = parseFloat($("#ye_jf").val());
    if (!ye_jf) ye_jf = 0;

/*金币积分计算过程 在public.js中做了通用模块*/
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
    } else if (!(ye_jf > 0)) {
        jfnum_all_sy = jfnum_all;//如果积分余额不大于0 ,则jf使用等于订单积分总额
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

/*
 * orderUrl   不同订单的保存页面
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

    if (!buynumb_all > 0) {
        $("#error").text("请选择乘车卡或输入其他乘车人信息");
        return false;
    }


    if (payMoney > 0) {
        $("#error").text("金币不够，无法预约");
        return false;
    }

    //--------------优惠信息获取
    var benefitInfo = $("#benefitInfo_text").val();
    if (!benefitInfo) benefitInfo = "";
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
            benefitInfo: benefitInfo,
            fh_ejjb: 0,
            fh_ejjf: 0,
            fh_sjjb: 0,
            fh_sjjf: 0,
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

    // var cckIDS = $('input[name="cck"]:checked').val();
    var cckIDS = "";
    $('input[name^="cck_"]:checked').each(function () {
        cckIDS += this.value + '|';    //遍历被选中CheckBox元素的集合 得到Value值
    });
    console.log("cckids" + cckIDS);
    var lineid = $("#lineid").val();
    var appttime = $("#appttime").val();
    var tjsite = $("#tjsite").val();
    var tmpType = $("#tmpType").val();
    var cardType = $("#cardType").val();

    var from_str = "&lineid=" + lineid + "&appttime=" + appttime + "&tjsite=" + tjsite + "&tmpType=" + tmpType;
    //乘车卡取值
    from_str += "&cckids=" + cckIDS;


    //其他人取值
    var realname_arr = [];//用于判断 多个表单是否重复
    var mobilephone_arr = [];//用于判断 多个表单是否重复
    var idcard_arr = [];//用于判断 多个表单是否重复
    var buynumb_basic_QTR = $("#buyNumb").html();//其他人的数量
    if (!buynumb_basic_QTR) buynumb_basic_QTR = 0;
    for (var xx = 1; xx <= buynumb_basic_QTR; xx++) {


        //姓名校验
        var realname_value_str = $('#realname_' + xx).val();
        if ($('#realname_' + xx).length > 0 && $('#realname_' + xx).val() == '') {
            $("#error").text("第" + xx + "个必须输入姓名");
            return false;
        }


        //手机号验证
        var m_value_str = $('#mobilephone_' + xx).val();
        var mobile_bool = validate_mobilePhone(m_value_str, false);
        if (mobile_bool != "正确") {
            $("#error").text("请检查其他人第" + xx + "个手机号(" + mobile_bool + ")");
            return false;
        }


        //身份证验证
        var idcard_value_str = $('#idcard_' + xx).val();
        var idcard_bool = validate_idcard(idcard_value_str, 0, 0, 0);
        if (idcard_bool != "正确") {
            $("#error").text("请检查其他人第" + xx + "个身份证号(" + idcard_bool + ")");
            return false;
        } else {
            var result_bool = true;
            $.ajax({
                type: "get",
                url: "/lyapp/order/line_lycpapp_search.php?appttime=" + appttime + "&idcard=" + idcard_value_str,
                async: false,//这个执行完才执行下面的
                dataType: 'html',
                success: function (result) {
                    if (result == 0) {
                        result_bool = false;
                    }
                }
            });
            if (!result_bool) {
                $("#error").text("请检查其他人第" + xx + "个身份证号码所选日期,已经预约过线路");
                return false;
            }
        }

        if (realname_value_str) realname_arr.push(realname_value_str);
        if (m_value_str) mobilephone_arr.push(m_value_str);
        if (idcard_value_str) idcard_arr.push(idcard_value_str);

        if (realname_value_str) {
            from_str += '&realname_' + xx + '=' + encodeURIComponent(realname_value_str) +
                '&mobilephone_' + xx + '=' + encodeURIComponent(m_value_str) +
                '&idcard_' + xx + '=' + encodeURIComponent(idcard_value_str);
        }
    }
    if (arrRepeat(realname_arr)) {
        $("#error").text("[其他人]多个姓名中存在重复内容,请检查");
        return false;
    }
    if (arrRepeat(mobilephone_arr)) {
        $("#error").text("[其他人]多个电话中存在重复内容,请检查");
        return false;
    }
    if (arrRepeat(idcard_arr)) {
        $("#error").text("[其他人]多个身份证号中存在重复内容,请检查");
        return false;
    }
    return from_str;
}











