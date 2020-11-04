//增加商品的选择行
//i 默认的商品个数
//chargeunit_str   单位的选择表单
function AddGoodsTr() {
    i++;
    if (i > 15) {
        alert("最大只允许15个选项！");
        return;
    }

    var htmldata = '<tr id="tr' + i + '">';
    htmldata += '<td class="text-center col-xs-1"><a class="btn btn-white" onClick="removeGoodsTr(' + i + ');" name="remove' + i + '">删除</a></td>';
    htmldata += '<td class="text-center col-xs-1">' + i + '</td>';
    htmldata += '<td class="col-xs-2"><button type="button" class="btn"  onClick="select(' + i + ')">选择</button>        </td>';
    htmldata += '<td><input id="urladd' + i + '" name="urladd' + i + '"  type="hidden" > <input id="unit' + i + '" name="unit' + i + '" value="" type="hidden">       <span id="_goodsInfo' + i + '" ></span></td>';
    htmldata += '<td class="col-xs-1"><input type="text" name="nowPrice' + i + '" id="nowPrice' + i + '" value="0" class="form-control"></td>';
    htmldata += '<td class="col-xs-1"><input type="text" name="goodsNumb' + i + '" id="goodsNumb' + i + '"value="0" class="form-control"></td>';
    htmldata += '<td class="text-center col-xs-1"><span id="_singlegoodstotal' + i + '">0</span></td>';
    htmldata += '<td class="text-center col-xs-1"><span id="_chargeunit' + i + '"></span></td>';
    htmldata += '</tr>';
    jQuery("#goodslist").append(htmldata);

    $("#nowPrice" + i).change(function () {
        getTotalMoney()	//监控第1+N个商品价格的变化(成品 )
    });

    $("#goodsNumb" + i).change(function () {
        getTotalMoney()	//监控第1+N个商品价格的变化(成品 )
    });
}


function removeGoodsTr(enamei) {

    jQuery("#tr" + enamei).remove();   //移除行
    getTotalMoney()	//监控第1+N个商品价格的变化(成品 )
}


//当商品价格改变时  计算总价
function getTotalMoney() {
    m = 1;
    totalMoney = 0;
    price = 0;
    numb = 0;//数量(单位平方)
    for (m = 1; m < 16; m++) {
        if (parseFloat($("#nowPrice" + m).val()) > 0 && parseFloat($("#goodsNumb" + m).val()) > 0) {
            money = 0;
            price = parseFloat($("#nowPrice" + m).val());
            numb = parseFloat($("#goodsNumb" + m).val());  //单位平方
            money = accMul(price, numb);//单个商品的总价
            $("#_singlegoodstotal" + m).html(money.toFixed(2));	//改变单个商品的合计总价
            totalMoney = accAdd(totalMoney, money);//合并总价用于输出
        }
    }
    $("#totalMoney").val(totalMoney.toFixed(2));
}


////成品页面的商品选择功能end


//初始化页面,引入要用的功能
function InitPage() {
    for (m = 1; m < i + 1; m++) {
        $("#nowPrice" + m).change(function () {
            getTotalMoney()	//监控第1+N个商品价格的变化(成品 )
        });
        $("#goodsNumb" + m).change(function () {
            getTotalMoney()	//监控第1+N个商品价格的变化(成品 )
        });
    }
}


//除法函数，用来得到精确的除法结果
//说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
//调用：accDiv(arg1,arg2)
//返回值：arg1除以arg2的精确结果
function accDiv(arg1, arg2) {
    var t1 = 0, t2 = 0, r1, r2;
    try {
        t1 = arg1.toString().split(".")[1].length
    } catch (e) {
    }
    try {
        t2 = arg2.toString().split(".")[1].length
    } catch (e) {
    }
    with (Math) {
        r1 = Number(arg1.toString().replace(".", ""))
        r2 = Number(arg2.toString().replace(".", ""))
        return (r1 / r2) * pow(10, t2 - t1);
    }
}

//乘法函数，用来得到精确的乘法结果
//说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
//调用：accMul(arg1,arg2)
//返回值：arg1乘以arg2的精确结果
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


//加法函数，用来得到精确的加法结果
//说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
//调用：accAdd(arg1,arg2)
//返回值：arg1加上arg2的精确结果
function accAdd(arg1, arg2) {
    var r1, r2, m;
    try {
        r1 = arg1.toString().split(".")[1].length
    } catch (e) {
        r1 = 0
    }
    try {
        r2 = arg2.toString().split(".")[1].length
    } catch (e) {
        r2 = 0
    }
    m = Math.pow(10, Math.max(r1, r2))
    return (arg1 * m + arg2 * m) / m
}

