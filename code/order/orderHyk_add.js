
var iiiii = 1;//设定默认的商品个数
//增加输入框

function AddGoodsTr() {
    $("#error").text("");
    iiiii++;
    if (iiiii > 10) {
        alert("一次最多购买10个！");
        return;
    }
    $("#buyNumb").html(iiiii);//页面显示数量
    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
}


//移除输入框
function removeGoodsTr(enamei) {
    $("#error").text("");
    jQuery("#tr_" + enamei).remove();   //移除行
    if (iiiii ==1) {
     alert("最少一件！");
     return;
     }
    iiiii--;
    $("#buyNumb").html(iiiii);//页面显示数量
    getTotalMoney();	//监控第1+N个商品价格的变化(成品 )
}



//表单验证
function from_validate() {
    $("#error").text("");

//这里没有用,只是为了格式 返回一下
    var buyNumb = $("#buyNumb").html();
    return "&type=1&buyNumb=" + buyNumb;
}

