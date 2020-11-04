var iiiii = $("#buyNumb").html();
;//设定默认的商品个数
if (!iiiii) iiiii = 1;
//增加输入框后台
function AddGoodsTr(nostk) {
    $("#error").text("");
    iiiii++;
    if (iiiii > 5) {
        alert("一次最多购买5个！");
        return;
    }
    var htmldata = '<div class="form-group" id="tr_' + iiiii + '">' +
        '<label   class="col-sm-2 control-label"><button  type="button" class="close"  onClick="removeGoodsTr(' + iiiii + ');">×</button>  乘车人信息' + iiiii + ':</label>' +
        '    <div class="col-sm-10 form-inline">' +
        '            <div class="form-group"  >' +
        '                <input type="text" autoComplete="off" placeholder="姓名" id="realname_' + iiiii + '" name="realname_' + iiiii + '" class="form-control" style="max-width: 80px">' +
        '            </div>' +
        '           <div class="form-group"  >' +
        '                <input type="text" autoComplete="off"  placeholder="手机号码" id="mobilephone_' + iiiii + '" name="mobilephone_' + iiiii + '" class="form-control" style="max-width:120px">' +
        '            </div>' +
        '            <div class="form-group">' +
        '                <input type="text" autoComplete="off"  placeholder="身份证号码" id="idcard_' + iiiii + '" name="idcard_' + iiiii + '" class="form-control">' +
        '            </div>';
    if (!nostk) htmldata += '   <div class="form-group">' +
        '               <input type="text" autoComplete="off"  placeholder="实体卡号" id="cardcode_' + iiiii + '" name="cardcode_' + iiiii + '" class="form-control">' +
        '            </div>';
    htmldata += '        </div>' +
        ' </div>';
    jQuery("#goodslist").append(htmldata);
    $("#buyNumb").html(iiiii);//页面显示数量
    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
}


function AddGoodsTrQT() {
    $("#error_str").text("");
    $("#error").hide();
    iiiii++;
    if (iiiii > 5) {
        alert("一次最多购买5个！");
        return;
    }
    var tr = '<li class="list-group-item1" id="tr_' + iiiii + '" >' +
        '           <button  type="button" class="close pull-left "  onClick="removeGoodsTr(' + iiiii + ');">×</button> 乘车人信息' + iiiii + '' +
        '           <div class="pull-right  ">' +
        '               <div style="max-width: 220px">' +
        '                   <div>' +
        '                       <div class="col-xs-5">' +
        '                           <input type="text"  autoComplete="off" class="form-control" name="realname_' + iiiii + '"   id="realname_' + iiiii + '" placeholder="姓名必填">' +
        '                       </div>' +
        '                       <div class="col-xs-7">' +
        '                            <input type="number" autoComplete="off"  class="form-control" name="mobilephone_' + iiiii + '" id="mobilephone_' + iiiii + '" placeholder="手机号">' +
        '                       </div>' +
        '                   </div>' +
        '                   <div class="clearfix"></div>' +
        '                   <div style="margin-top: 5px">' +
        '                       <div class="col-xs-12">' +
        '                           <input type="text"  autoComplete="off" name="idcard_' + iiiii + '" id="idcard_' + iiiii + '" class="form-control"  value="" placeholder="身份证号">' +
        '                       </div>' +
        '                   </div>' +
        '               </div>' +
        '           </div>' +
        '           <div class="clearfix"></div>' +
        '    </li>';
    jQuery("#goodslist").append(tr);
    $("#buyNumb").html(iiiii);//页面显示数量
    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
}

//移除输入框
function removeGoodsTr(enamei) {
    $("#error").text("");
    jQuery("#tr_" + enamei).remove();   //移除行
    iiiii--;
    $("#buyNumb").html(iiiii);//页面显示数量

    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
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


/*金币 积分  需要支付的现金
 * 计算过程
 * */
function jb_jf_xj(jbnum_all, jfnum_all, ye_jb, ye_jf) {

    console.log(jbnum_all);
    console.log(jfnum_all);
    console.log(ye_jb);
    console.log(ye_jf);
    var payMoney = 0;//需要支付的现金
    var t_dk_jb = 0; //订单可以使用的金币
    var t_dk_jf = 0; //订单可以使用的金币
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
    } else if(!(ye_jf>0)){
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
    var return_array = new Array();
    return_array["payMoney"] = payMoney;
    return_array["t_dk_jb"] = t_dk_jb;
    return_array["t_dk_jf"] = t_dk_jf;
    return return_array;
}