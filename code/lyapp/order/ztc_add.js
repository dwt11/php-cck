var iiiii = 1;//设定默认的商品个数
//增加输入框
function AddGoodsTr() {
    $("#error_str").text("");
    $("#error").hide();
    iiiii++;
    if (iiiii > 5) {
        alert("一次最多购买5个！");
        return;
    }
    var tr = '<li class="list-group-item1" id="tr_' + iiiii + '" >' +
        '           <div class=" pull-left "><button  type="button" class="close  pull-left "  onClick="removeGoodsTr(' + iiiii + ');">×</button>  ' +
        '           乘车人信息' + iiiii + '' +
        '           <span class="small text-muted "><br> 此卡不会给您返利<br>会给您的上级返利</span> ' +
        '           </div>' +
        '           <div class="pull-right  ">' +
        '               <div style="max-width: 250px">' +
        '                   <div>' +
        '                       <div class="col-xs-5">' +
        '                           <input type="text" class="form-control" name="realname_' + iiiii + '"   id="realname_' + iiiii + '" placeholder="姓名必填">' +
        '                       </div>' +
        '                       <div class="col-xs-7">' +
        '                            <input type="number" class="form-control" name="mobilephone_' + iiiii + '" id="mobilephone_' + iiiii + '" placeholder="手机号">' +
        '                       </div>' +
        '                   </div>' +
        '                   <div class="clearfix"></div>' +
        '                   <div style="margin-top: 5px">' +
        '                       <div class="col-xs-12">' +
        '                           <input type="text" name="idcard_' + iiiii + '" id="idcard_' + iiiii + '" class="form-control"  value="" placeholder="身份证号">' +
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
    $("#error_str").text("");
    $("#error").hide();
    jQuery("#tr_" + enamei).remove();   //移除行
    iiiii--;
    $("#buyNumb").html(iiiii);//页面显示数量

    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
}


//表单验证
function from_validate() {
    $("#error_str").text("");
    $("#error").hide();

    var from_str = "";
    var realname_arr = [];//用于判断 多个表单是否重复
    var mobilephone_arr = [];//用于判断 多个表单是否重复
    var idcard_arr = [];//用于判断 多个表单是否重复
    var cardcode_arr = [];//用于判断 多个表单是否重复

    var minNL = 0;
    var maxNL = 0;
    var goodsid_temp = $("#goodsid").val();
    if (goodsid_temp == 1) maxNL = 70;//商品1直通车卡最大年龄70
    if (goodsid_temp == 142) minNL = 70;//商品爱心卡最小年龄70
    for (var xx = 1; xx <= 5; xx++) {



        //姓名校验
        var realname_value_str = $('#realname_' + xx).val();
        if ($('#realname_' + xx).length > 0 && $('#realname_' + xx).val() == '') {
            $("#error_str").text("第" + xx + "个必须输入姓名");
            $("#error").show();
            return false;
        }

        //手机号验证
        var m_value_str = $('#mobilephone_' + xx).val();
        var mobile_bool = validate_mobilePhone(m_value_str, 0);
        if (mobile_bool != "正确") {
            $("#error_str").text("请检查第" + xx + "个手机号(" + mobile_bool + ")");
            $("#error").show();
            return false;
        }


        //身份证验证
        var idcard_value_str = $('#idcard_' + xx).val();
        var idcard_bool = validate_idcard(idcard_value_str, minNL, maxNL, 1);
        if (idcard_bool != "正确" && !isxf) {
            $("#error_str").text("请检查第" + xx + "个身份证号(" + idcard_bool + ")");
            $("#error").show();
            return false;
        }


        //实体卡号验证
        var cardcode_value_str = $('#cardcode_' + xx).val();
        var cardcode_bool = validate_cardbode(cardcode_value_str);
        if (cardcode_bool != "正确" && !isxf) {
            $("#error_str").text("请检查第" + xx + "个实体卡号(" + cardcode_bool + ")");
            $("#error").show();
            return false;
        }


        if (realname_value_str) realname_arr.push(realname_value_str);
        if (m_value_str) mobilephone_arr.push(m_value_str);
        if (idcard_value_str) idcard_arr.push(idcard_value_str);
        if (cardcode_value_str) cardcode_arr.push(cardcode_value_str);

        if (realname_value_str) {
            from_str += '&realname_' + xx + '=' + encodeURIComponent(realname_value_str) +
                '&mobilephone_' + xx + '=' + encodeURIComponent(m_value_str) +
                '&idcard_' + xx + '=' + encodeURIComponent(idcard_value_str) +
                '&cardcode_' + xx + '=' + encodeURIComponent(cardcode_value_str);
        }
    }

    console.log(cardcode_arr);
    if (arrRepeat(realname_arr)) {
        $("#error_str").html("<br>输入的多个姓名中存在重复内容,请检查");
        $("#error").show();
        return false;
    }
    if (arrRepeat(mobilephone_arr)) {
        $("#error_str").html("<br>输入的多个电话中存在重复内容,请检查");
        $("#error").show();
        return false;
    }
    if (arrRepeat(idcard_arr)) {
        $("#error_str").html("<br>输入的多个身份证号中存在重复内容,请检查");
        $("#error").show();
        return false;
    }
    if (arrRepeat(cardcode_arr)) {
        $("#error_str").html("<br>输入的多个实体卡号中存在重复内容,请检查");
        $("#error").show();
        return false;
    }
    return from_str;
}


//遍历radio的点击 事件
$("input[name='benefitInfo']").on('ifChecked', function (event) {//必须选一个
    ////如果当前RADIO被选中,则取值
    var benefitInfo_CheckedNumb = $(this).val();

    //如果优惠价格为0,则只可以购买一个卡
    var gmyh = $("#gmyh_" + benefitInfo_CheckedNumb).html();
    if (!gmyh) gmyh = "";

    if (parseInt(gmyh) == 0) {
        $("#ztcaddnumb").hide();

    } else {
        $("#ztcaddnumb").show();
    }

});
