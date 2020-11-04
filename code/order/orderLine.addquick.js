//移除输入框
function removeGoodsListTr_quick_forZTCCARD(enamei, iiiii) {
    $("#error").text("");
    jQuery("#tr_" + enamei + iiiii).remove();   //移除行
    var iiiii_t = $("#buyNumb_" + enamei).html();//页面显示数量
    iiiii_t--;
    $("#buyNumb_" + enamei).html(iiiii_t);//页面显示数量
}


//增加乘车卡选择框   用于乘车卡 验证
function AddGoodsListTr_quick_forZTCCARD(enname_global, clientTypeName, appttime) {
    $("#error").text("");
    var iiiii = $("#buyNumb_" + enname_global).html();//页面显示数量
    iiiii++;
    if (iiiii > 5) {
        alert("一次最多添加5个！");
        return;
    }
    var htmldata = '<div class="form-group" id="tr_' + enname_global + iiiii + '">' +
        '<label   class="col-sm-2 control-label"><button  type="button" class="close"  onClick="removeGoodsListTr_quick_forZTCCARD(\'' + enname_global + '\',\'' + iiiii + '\');">×</button> 订单信息' + iiiii + ':</label>' +
        '    <div class="col-sm-10 form-inline">' +
        '            <div class="form-group"  >' +
        '                <button type="button" class="btn btn-primary" onclick="selectOrderZtcListId(\'' + enname_global + iiiii + '\',\'' + clientTypeName + '\',\'' + appttime + '\')">选择乘车卡</button>' +
        '            </div>' +
        '           <div class="form-group"  style="min-width:120px" >' +
        '                会员账户ID:[<span id="clientidspan_' + enname_global + iiiii + '" name="clientidspan_' + enname_global + iiiii + '" ></span>]' +
        '                <input type="hidden"     id="clientid_' + enname_global + iiiii + '" name="clientid_' + enname_global + iiiii + '" >' +
        '                <input type="hidden"     id="orderlistztcid_' + enname_global + iiiii + '" name="orderlistztcid_' + enname_global + iiiii + '" >' +
        '            </div>' +
        '            <div class="form-group">' +
        '                <span id="orderinfo_' + enname_global + iiiii + '"></span>' +
        '            </div>';
    htmldata += '        </div>' +
        ' </div>';
    jQuery("#goodslist_" + enname_global).append(htmldata);
    $("#buyNumb_" + enname_global).html(iiiii);//页面显示数量
}

//选择卡后值改变
$(function () {
    var vars_value = {};
    //$("input[name^='ordercarInfo_save_']").hide();
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        // IE浏览器此处判断没什么意义，但为了统一，且提取公共代码而这样处理。
        //如果值不一样,则代表了改变

        $("input[name^='orderlistztcid_']").each(
            function () {
                var e_name = $(this).attr("id");//表单名称
                var tmpvalue = vars_value[e_name];//临时的值
                if (!tmpvalue) tmpvalue = "";
                //console.log("tmpvalue " + tmpvalue);

                var nowvalue = $(this).val();
                if (nowvalue != tmpvalue) {
                    vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                    //console.log(vars_value.e_name);
                    var e_name_array = e_name.split("_");
                    var enname_global_temp = e_name_array[1];
                    var orderZtcListId = $("#orderlistztcid_" + enname_global_temp).val();

                    $.ajax({
                        type: "get",
                        url: "../order/order.do.php",
                        data: {
                            orderZtcListId: orderZtcListId,
                            dopost: "GetOrderZtcListInfo"
                        },
                        dataType: 'json',
                        success: function (result) {
                            var ztcListInfo = "";
                            for (var i = 0; i < result.length; i++) {
                                if (ztcListInfo != "") ztcListInfo += "<br>";
                                ztcListInfo += " 姓名:" + result[i].name + " 手机:" + result[i].tel + " 身份证:" + result[i].idcard

                            }
                            $("#orderinfo_" + enname_global_temp).html(ztcListInfo);

                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#orderinfo_" + enname_global_temp).html("未找到乘车卡详细信息");//如果清空了车辆或出错,则把车牌号清空
                        }
                    });
                }
            }
        )
    }
});
function selectOrderZtcListId(enname_global, clientTypeName, appttime) {
    layer.open({
        type: 2,
        title: '选择乘车卡',
        content: '../order/orderZtc.select.php?target=' + enname_global + '&clientTypeName=' + clientTypeName + '&appttime=' + appttime
    });
}


/*
 * orderUrl   不同订单的保存页面
 * orderUrl,订单创建 地址
 * backUrl 创建后 返回地址
 * */
function gopay_quick(orderUrl, backUrl, isdebug) {
    $("#error").text("");
    //加载层
    //loading层
    var goodsid = $("#goodsid").val();
    if (goodsid == '') {
        $("#error").text("商品信息获取错误,请刷新页面");
        return false;
    }

    //--------------------------根据页面的表单,将每个会员的订单信息存入数组中,待筛选(未去重复)
    var orderinfo_array = new Array();
    $('input[name^="clientid_"]').each(function () {
        var e_name = $(this).attr("id");//表单名称
        var e_name_hz = e_name.replace("clientid_", "");//表单后辍名称
        var benefitInfo = "";//优惠信息
        var ztcclienttype_en = e_name_hz.substring(0, e_name_hz.length - 1);//去除最后的数字,得到会员卡类型(英文)
        var ztcclienttype_cn = $("#ztcclienttype_" + ztcclienttype_en).html();//中文卡名称;/得到会员卡类型
        var clientid = $(this).val();//会员ID
        var cckids = $("#orderlistztcid_" + e_name_hz).val();//乘车卡ADDON的id
        var buyNumb_array = cckids.split(",");
        var buyNumb = buyNumb_array.length;
        var jbnum = $("#jbnum_" + ztcclienttype_en).html();//当前类型会员卡的 优惠金额
        var jfnum = $("#jfnum_" + ztcclienttype_en).html();//当前类型会员卡的 优惠金额
        if (jbnum > 0) {
            benefitInfo += " [" + ztcclienttype_cn + "]金币:" + jbnum;
        }
        if (jfnum > 0) {
            benefitInfo += " [" + ztcclienttype_cn + "]积分:" + jfnum;
        }

        //ztcclienttype_en_clientid  这个参数 用于判断同一个类型的乘车卡下,会员ID是否重复
        orderinfo_array.push(eval("({'clientid':'" + clientid + "','ztcclienttype_en_clientid':'" + ztcclienttype_en + clientid + "','buyNumb':'" + buyNumb + "','cckids':'" + cckids + "','jbnum':'" + jbnum + "','jfnum':'" + jfnum + "','benefitInfo':'" + benefitInfo + "'})"));
    });
    //--------------------------根据页面的表单,将每个会员的订单信息存入数组中,待筛选(未去重复)

    if (!(orderinfo_array.length > 0)) {
        $("#error").text("请选择乘车卡");
    } else {
        //------------------判断同一乘车卡类型下,会员ID是否重复
        //console.log(orderinfo_array);
        var ztcclienttype_en_clientid = getXTkeyValue(1, orderinfo_array).ztcclienttype_en_clientid;//得 到ztcclienttype_en_clientid,所有的值,判断是否重复
        //console.log(ztcclienttype_en_clientid);
        var ztcclienttype_en_clientid_array = ztcclienttype_en_clientid.split(",");
        if (arrRepeat(ztcclienttype_en_clientid_array)) {
            $("#error").text("同一乘车卡类型下,会员ID重复,请重新选择");
            return false;
        }
        //------------------判断同一乘车卡类型下,会员ID是否重复
    }


    //---------------------------合并同一会员,不同优惠类型的订单,生成最终的订单参数
    var new_orderinfo_array = new Array();
    for (var iuft = 0; iuft < orderinfo_array.length; iuft++) {
        var totalMoney = 0;//订单总价  所有积分加金币
        var buynumb_all = 0;//人的总数
        var jbnum_all = 0;//要使用的金币总合
        var jfnum_all = 0;//要使用的积分总合

        var clientid = orderinfo_array[iuft]["clientid"];
        var jbnum = parseInt(orderinfo_array[iuft]["jbnum"]);
        var jfnum = parseInt(orderinfo_array[iuft]["jfnum"]);
        var buyNumb = parseInt(orderinfo_array[iuft]["buyNumb"]);
        var cckids = orderinfo_array[iuft]["cckids"];
        var benefitInfo = orderinfo_array[iuft]["benefitInfo"];

        jbnum_all = parseInt(accMul(jbnum, buyNumb));
        jfnum_all = parseInt(accMul(jfnum, buyNumb));
        totalMoney = jbnum_all + jfnum_all;
        //合并相同clientid的内容

        var iscf = false;//当前clientdid是否重复
        for (var i_ttt = 0; i_ttt < new_orderinfo_array.length; i_ttt++) {
            if (new_orderinfo_array[i_ttt]["clientid"] == clientid) {
                //如果当前的clientid与数组中的相同则合并
                var buynumb_all_t = parseInt(new_orderinfo_array[i_ttt]["buynumb_all"]) + parseInt(buyNumb);
                var jbnum_all_t = parseInt(new_orderinfo_array[i_ttt]["jbnum_all"]) + parseInt(jbnum_all);
                var jfnum_all_t = parseInt(new_orderinfo_array[i_ttt]["jfnum_all"]) + parseInt(jfnum_all);
                var cckids_t = new_orderinfo_array[i_ttt]["cckids"] + "," + cckids;
                var benefitInfo_t = new_orderinfo_array[i_ttt]["benefitInfo"] + " " + benefitInfo;
                var totalMoney_t = parseInt(new_orderinfo_array[i_ttt]["totalMoney"]) + parseInt(totalMoney);
                new_orderinfo_array[i_ttt] = {
                    clientid: clientid,
                    buynumb_all: buynumb_all_t,
                    cckids: cckids_t,
                    jbnum_all: jbnum_all_t,
                    jfnum_all: jfnum_all_t,
                    benefitInfo: benefitInfo_t,
                    totalMoney: totalMoney_t
                }
                iscf = true;
            }
        }


        //如果不重复,直接追加
        if (!iscf) {
            //如果不同 则追加到数组中
            new_orderinfo_array.push(
                {
                    clientid: clientid,
                    buynumb_all: buyNumb,
                    cckids: cckids,
                    jbnum_all: jbnum_all,
                    jfnum_all: jfnum_all,
                    benefitInfo: benefitInfo,
                    totalMoney: totalMoney
                }
            );
        }

    }
    //console.log(new_orderinfo_array);
    //---------------------------合并同一会员,不同优惠类型的订单,生成最终的订单参数

    //---------------------------验算每个会员的余额是否够用
    var jb_jf_nouse = new Array();//金币 不够用的会员ID
    var orderinfo_send_save_array = new Array()//最终生成订单要用的参数
    for (var i_hhyfttt = 0; i_hhyfttt < new_orderinfo_array.length; i_hhyfttt++) {
        //AJAX获取会员的余额 进行比对
        var ye_jb = 0;//余额金币
        var ye_jf = 0;//余额积分
        var clientid_t = new_orderinfo_array[i_hhyfttt]["clientid"];
        $.ajax({
            type: "post",
            url: "../client/client.do.php",
            data: {
                dopost: "GetOneClientJBJF",
                clientid: clientid_t
            },
            async: false,//这个执行完才执行下面的
            dataType: 'json',
            success: function (data) {
                ye_jb = data.jbnum;
                ye_jf = data.jfnum;
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            }
        });
        //console.log(clientid_t);
        //console.log(ye_jb);
        //console.log(ye_jf);
        var jbnum_all_t = parseInt(new_orderinfo_array[i_hhyfttt]["jbnum_all"]);
        var jfnum_all_t = parseInt(new_orderinfo_array[i_hhyfttt]["jfnum_all"]);

        var jb_jf_xj_array = new Array();
        jb_jf_xj_array = jb_jf_xj(jbnum_all_t, jfnum_all_t, ye_jb, ye_jf);
        var payMoney_t = jb_jf_xj_array["payMoney"];//需要支付的现金
        var t_dk_jb = jb_jf_xj_array["t_dk_jb"];//可抵扣的金币
        var t_dk_jf = jb_jf_xj_array["t_dk_jf"];//可抵扣 的积分
        //console.log(payMoney_t);
        //console.log(t_dk_jb);
        //console.log(t_dk_jf);
        if (payMoney_t > 0) {
            //如果需要 支付现金 代表金币 不够用
            jb_jf_nouse.push(clientid_t);
        }

        var buynumb_all_t = parseInt(new_orderinfo_array[i_hhyfttt]["buynumb_all"]);
        var cckids_t = new_orderinfo_array[i_hhyfttt]["cckids"];
        var benefitInfo_t = new_orderinfo_array[i_hhyfttt]["benefitInfo"];
        var totalMoney_t = parseInt(new_orderinfo_array[i_hhyfttt]["totalMoney"]);

        //如果不同 则追加到数组中
        orderinfo_send_save_array.push(
            {
                clientid: clientid_t,
                buynumb_all: buynumb_all_t,
                cckids: cckids_t,
                t_dk_jb: t_dk_jb,
                t_dk_jf: t_dk_jf,
                benefitInfo: benefitInfo_t,
                totalMoney: totalMoney_t
            }
        );

    }

    if (jb_jf_nouse.length > 0) {
        $("#error").text("会员ID[" + jb_jf_nouse.toString() + "] 金币不够，无法预约");
        return false;
    }
    //---------------------------验算每个会员的余额是否够用


    //console.log(orderinfo_send_save_array);
    //return false;
    //----------------------支付过程
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
    var true_order = 0;//成功的订单
    var false_order = 0;//失败的订单
    var false_order_str = "";//失败原因
    var lineid = $("#lineid").val();
    var appttime = $("#appttime").val();
    var tjsite = $("#tjsite").val();
    var tmpType = $("#tmpType").val();
    var from_str = "&lineid=" + lineid + "&appttime=" + appttime + "&tjsite=" + tjsite + "&tmpType=" + tmpType;
    for (var iiiii_xxx = 0; iiiii_xxx < orderinfo_send_save_array.length; iiiii_xxx++) {
        var buynumb_all_t = parseInt(orderinfo_send_save_array[iiiii_xxx]["buynumb_all"]);
        var t_dk_jb = parseInt(orderinfo_send_save_array[iiiii_xxx]["t_dk_jb"]);
        var t_dk_jf = parseInt(orderinfo_send_save_array[iiiii_xxx]["t_dk_jf"]);
        var cckids_t = orderinfo_send_save_array[iiiii_xxx]["cckids"];
        var benefitInfo_t = encodeURIComponent(orderinfo_send_save_array[iiiii_xxx]["benefitInfo"]);
        var totalMoney_t = parseInt(orderinfo_send_save_array[iiiii_xxx]["totalMoney"]);
        var clientid_t = orderinfo_send_save_array[iiiii_xxx]["clientid"];
        //乘车卡取值
        from_str += "&cckids=" + cckids_t;
        $.ajax({
            type: "post",
            url: orderUrl + from_str,
            data: {
                goodsid: goodsid,
                clientid: clientid_t,
                paytype: "现金",
                desc: desc,
                payMoney: 0,
                dk_jb: t_dk_jb,
                dk_jf: t_dk_jf,
                totalMoney: totalMoney_t,
                benefitInfo: benefitInfo_t,
                fh_ejjb: 0,
                fh_ejjf: 0,
                fh_sjjb: 0,
                fh_sjjf: 0,
                buynumb: buynumb_all_t
            },
            async: false,//这个执行完才执行下面的
            dataType: 'json',
            success: function (data) {
                if (data.info == "添加成功") {
                    true_order++;
                } else {
                    false_order++;
                    false_order_str += "<br>会员ID[" + clientid_t + "]失败原因:" + data.info;
                }
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                false_order++;
                false_order_str += "<br>会员ID[" + clientid_t + "]失败原因:请重新添加" + data.info;
            }

        });

    }

    layer.closeAll('loading'); //关闭加载层
    var return_str = "";
    if (true_order > 0) return_str += "成功创建" + true_order + "个订单 ";
    if (false_order > 0) return_str += " " + false_order + "个订单创建失败,请检查"+false_order_str;
    layer.msg(return_str, {
        shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
        time: 3000 //2秒关闭（如果不配置，默认是3秒）
    }, function () {
        if (!isdebug) window.location.href = backUrl;
    });


}


//获得多维数组中,相同键的值
//key_index得到第几个键名 的值
function getXTkeyValue(key_index, array) {
    var obj = {};
    var narr = new Array();
    for (var i = 0; i < array.length; i++) {
        for (var j in array[i]) {
            if (obj[j] != undefined)
                obj[j] += "," + array[i][j];
            else
                obj[j] = array[i][j];
        }
    }
    for (var i in obj) {
        narr.push(eval("({'" + i + "':'" + obj[i] + "'})"));
    }

    return narr[key_index];
}
