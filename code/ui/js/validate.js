/*//校验身份证是否大于等于70
function eighteen_70(val) {
    if (typeof(val) == "string" && 18 == val.length) { //18位身份证号码
        bv = val.charAt(6) + val.charAt(7) + val.charAt(8) + val.charAt(9);//1980
        var d = new Date();

        if (d.getFullYear() - bv >= 70) {
            return true;
        }
        else {
            return false;
        }
    }
}*/
//手机验证
//isuse true去CLIENT表验证手机是否使用
function validate_mobilePhone(mobilePhone,isuse) {
    var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
    var mobile_bool = "正确";
    if (mobilePhone=="" && !(mobilePhone.length > 0)) {
        mobile_bool = "请输入手机号";
    }
    if (mobilePhone && mobilePhone.length > 0) {
        if (mobile.test(mobilePhone)) {
            if (isuse == 1) {
                $.ajax({
                    type: "post",
                    url: "/lyapp/phoneCodeCheck.ajax.php?isyz=false&mobilephone=" + mobile,
                    async: false,//这个执行完才执行下面的
                    dataType: 'html',
                    success: function (result) {
                        if (result == "false") {
                            mobile_bool = "已被使用";
                        }
                    }
                });
            }

        } else {
            mobile_bool = "格式不对";
        }
    }
    return mobile_bool;
}

//实体卡验证
function validate_cardbode(cardbode) {
    var return_bool = "正确";

    if (cardbode && cardbode.length > 0) {
        if (isNaN(cardbode)) {
            return "卡号码必须是数字";
        }
        $.ajax({
            type: "post",
            url: "orderZtcCardCodeCheck.ajax.php?cardcode=" + cardbode,
            async: false,//这个执行完才执行下面的
            dataType: 'html',
            success: function (result) {
                if (result == "false") {
                    return_bool = "已被使用";
                }
            }
        });
    }
     return return_bool;
}

/*
 idcard,
 minNL, 最小年龄  为0不检验
 maxNL,  最大年龄, 为0不检验
 isuse   1检查是否使用 0不检查   是否在CLIENT表中使用
 * */
function validate_idcard(idcard, minNL, maxNL, isuse) {
    var return_bool = "正确";
    if (idcard=="" && !(idcard.length > 0)) {
        return_bool = "请输入身份证号";
    }
    if (idcard && idcard.length > 0) {
        if (isIdCardNo(idcard)) {
            return_bool = "正确";
            if (minNL > 0) {
                bv = idcard.charAt(6) + idcard.charAt(7) + idcard.charAt(8) + idcard.charAt(9);//1980
                var d = new Date();
                if (d.getFullYear() - bv < minNL) {
                    return "年龄不能小于" + minNL + "岁";
                }
            }
            if (maxNL > 0) {
                bv = idcard.charAt(6) + idcard.charAt(7) + idcard.charAt(8) + idcard.charAt(9);//1980
                var d = new Date();
                if (d.getFullYear() - bv >= maxNL) {
                    return "年龄不能大于" + maxNL + "岁";
                }
            }
            if (isuse == 1) {

                $.ajax({
                    type: "post",
                    url: "/lyapp/order/ztc_list_idcard_search.php?idcard=" + idcard,
                    async: false,//这个执行完才执行下面的
                    dataType: 'html',
                    success: function (result) {
                        if (result == 0) {
                            return_bool = "已被使用";
                        }
                    }
                });
            }
        } else {
            return_bool = "格式不对";
        }
    }
    return return_bool;
}


/*检验JS数组是否有重复*/
function arrRepeat(ary) {
    var rdata = {};
    for (var i in ary) {
        var c = ary[i];
        rdata[c] ? (rdata[c]++) : (rdata[c] = 1)
    }

    var strResult = "";
    for (var k in rdata) {
        if (rdata[k] > 1)
        //strResult += k + "出现了" + rdata[k] + "次,";
            return true;
    }
    //strResult && (strResult.replace(/,$/,""));
    //return strResult ? strResult : "字串(\"" + input + "\")中没有重复项!"
    return false;
}