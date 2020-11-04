//表单验证
function from_validate() {
    $("#error").text("");

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
            $("#error").text("第" + xx + "个必须输入姓名");
            return false;
        }

        //手机号验证
        var m_value_str = $('#mobilephone_' + xx).val();
        var mobile_bool = validate_mobilePhone(m_value_str, mobilephoneUseONclient);
        if (mobile_bool != "正确") {
            $("#error").text("请检查第" + xx + "个手机号(" + mobile_bool + ")");
            return false;
        }


        //身份证验证
        var idcard_value_str = $('#idcard_' + xx).val();
        var idcard_bool = validate_idcard(idcard_value_str, minNL, maxNL, 1);
        if (idcard_bool != "正确" && !isxf) {
            $("#error").text("请检查第" + xx + "个身份证号(" + idcard_bool + ")");
            return false;
        }


        //实体卡号验证
        var cardcode_value_str = $('#cardcode_' + xx).val();
        var cardcode_bool = validate_cardbode(cardcode_value_str);
        if (cardcode_bool != "正确" && !isxf) {
            $("#error").text("请检查第" + xx + "个实体卡号(" + cardcode_bool + ")");
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
        $("#error").html("<br>输入的多个姓名中存在重复内容,请检查");
        return false;
    }
    if (arrRepeat(mobilephone_arr)) {
        $("#error").html("<br>输入的多个电话中存在重复内容,请检查");
        return false;
    }
    if (arrRepeat(idcard_arr)) {
        $("#error").html("<br>输入的多个身份证号中存在重复内容,请检查");
        return false;
    }
    if (arrRepeat(cardcode_arr)) {
        $("#error").html("<br>输入的多个实体卡号中存在重复内容,请检查");
        return false;
    }
    return from_str;
}