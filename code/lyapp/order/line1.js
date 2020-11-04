//----------------日期选择页面提交过程
$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"});
    $('#lineid').iCheck('check');//默认选中第一个

});
var lineid = 0;
//遍历radio的点击 事件
$("input[name='lineid']").on('ifChecked', function (event) {//必须选一个
    ////如果当前RADIO被选中,则取值
     lineid = $(this).val();
    //var line_value = $(this).next("span").text() ;
   // console.log(lineid);
    //console.log(line_value);
    //console.log(benefitInfo_CheckedNumb);

});

function lineSelectSubimt() {
    $("#error_str").text("");
    //$("#error").hide();
    console.clear();
    var goodsid = $("#goodsid").val();

    //var tmpType = $('input[name="tmpType"]:checked').val();
    var tmpType = "";//170414已经没用了,不用线路日期类型了


    var xz_time = $("#xz_time").val();




    // var lineid_mr = $("#lineid_mr").val();
    //如果没有线路类型的选择
    /*if (!tmpType) {
     if (lineid_mr) {
     lineid = lineid_mr;
     tmpType = "每日";
     var timebool = compareDate(xz_time);
     if (!timebool) {
     $("#error_str").text("出发日期需在30天以内,并不能小于今天");
     $("#error").show();
     return false;
     }
     }*/

    xz_time = 0;
    tmpType = "临时";

    /*}
     } else {
     //如果有选择线路类型,则另一个类型初始为0
     if (tmpType == "临时") {
     lineid = lineid_ls;
     xz_time = 0;
     //tmpType = "临时";
     }
     if (tmpType == "每日") {
     lineid = lineid_mr;
     var timebool = compareDate(xz_time);
     if (!timebool) {
     $("#error_str").text("出发日期需在30天以内,并不能小于今天");
     $("#error").show();
     return false;
     }
     //tmpType = "每日";
     }
     }*/

    //var cardType = $('input[name="cardType"]:checked').val();

    var tjsite = $('input[name="tjsite"]:checked').val();
    if (!tjsite) tjsite = "";
    /*console.log("商品ID" + goodsid);

     console.log("日期类型" + tmpType);
     console.log("线路ID" + lineid);
     console.log("选择的日期" + xz_time);
     console.log("乘车人类型" + cardType);
     console.log("途经站点" + tjsite);*/
    if(!lineid>0){
        $("#error_str").html("<h2>请选择出发日期</h2>");
        return false;
    }

    //var url = "line_add.php?goodsid=" + goodsid + "&tmpType=" + tmpType + "&lineid=" + lineid + "&xz_time=" + xz_time + "&cardType=" + cardType + "&tjsite=" + tjsite;
    var url = "line_add.php?goodsid=" + goodsid + "&tmpType=" + tmpType + "&lineid=" + lineid + "&xz_time=" + xz_time + "&tjsite=" + tjsite;
    //console.log(url);
    window.location.href = url;

}
//----------------日期选择页面提交过程


