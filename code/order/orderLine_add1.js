$(function () {

    //---------------------线路选择回调
    var lineid = "";
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        //如果值不一样,则代表了改变
        if ($("#lineid").val() != lineid) {
            //console.log($("#lineid").val()+"----"+lineid);
            lineid = $("#lineid").val();//保存改变后的值
            $("#lineid_str").html("编号" + lineid);//保存改变后的值
            $.ajax({
                type: "get",
                url: "../goods/goods.do.php",
                data: {
                    lineid: lineid,
                    dopost: "GetOneLineInfo"
                },
                dataType: 'json',
                success: function (result) {
                    var price=parseInt(result.price);
                    var jfnum=parseInt(result.jfnum);
                    var jbnum=price-jfnum;
                    //$("#price").html("金币"+jbnum+" 积分"+jfnum);
                    $("#goodstitle").html(result.goodsname);
                    $("#goodsid").val(result.goodsid);
                    $("#tmpType").html(result.tmpType);
                    if (result.tmpType == "每日") {
                        $("#gdtime").show();
                        $("#lstime").hide();
                    }
                    if (result.tmpType == "临时") {
                        $("#gdtime").hide();
                        $("#lstime").show();
                        $("#apptime_str").html(result.gotime);
                    }
                    if (result.tjsite != "") {
                        $("#scd").show();
                        str = result.tjsite; //这是一字符串
                        var str_array = new Array(); //定义一数组
                        str_array = str.split(","); //字符分割
                        tjsiteHTML = "";
                        for (var stri = 0; stri < str_array.length; stri++) {
                            tjsiteHTML += "<label class='checkbox-inline   i-checks' style='min-width: 80px;line-height: 20px'>";
                            tjsiteHTML += "<input name='tjsite' id='tjsite' type='radio'  value='" + str_array[stri] + "'  /> " + str_array[stri] + "";
                            tjsiteHTML += "</label>";
                        }
                        $("#tjsite").html(tjsiteHTML);
                        $(".i-checks").iCheck({
                            checkboxClass: "icheckbox_square-green",
                            radioClass: "iradio_square-green"
                        })
                    }
                }
            });


        }
    }
});
function selectLine() {
    layer.open({type: 2, title: '选择商品', content: '../goods/line.select.php'});
}


//==============================选择客户
function selectClient() {
    layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
}


$(function () {
    //---------------------会员 选择回调
    var clientid = "";
    intervalName11 = setInterval(handle11, 1000);//定时器句柄
    function handle11() {
        //如果值不一样,则代表了改变
        if ($("#clientid").val() != clientid) {
            //console.log($("#goodsid").val()+"----"+goodsid);
            clientid = $("#clientid").val();//保存改变后的值
            $("#clientid_str").html("编号" + clientid);//保存改变后的值
            $.ajax({
                type: "get",
                url: "../client/client.do.php",
                data: {
                    clientid: clientid,
                    dopost: "GetOneClientInfo"
                },
                dataType: 'json',
                success: function (result) {
                    console.log(result);
                    $("#realname").html(result.realname + " " + result.mobilephone);
                }
            });

            /*获取乘车卡数量 */
            $.ajax({
                type: "get",
                url: "orderLine_add.php",
                data: {
                    clientid: clientid,
                    dopost: "getztccardnumb"
                },
                dataType: 'json',
                success: function (result) {
                    console.log(result);
                    cardTypeHtml="无乘车卡,可以添加其他人乘车";
                    if (result > 0) {
                        cardTypeHtml ="直通车卡" + result + "张";
                    }
                    $("#cardTypeHtml").html(cardTypeHtml);


                }
            });
        }
    }
});

//----------------选择页面提交过程
function lineSelectSubimt() {
    console.clear();
    $("#error").text("");
    var lineid = $("#lineid").val();
    if(!lineid){
        $("#error").text("请选择线路");
        return false;
    }
    var clientid = $("#clientid").val();
    if(!clientid){
        $("#error").text("请选择会员");
        return false;
    }
    var goodsid = $("#goodsid").val();
    if(!goodsid){
        $("#error").text("请选择线路");
        return false;
    }

    var tmpType = $("#tmpType").html();
    if(!tmpType){
        $("#error").text("线路类型获取失败,请刷新页面,重新获取");
        return false;
    }

    var xz_time ="";
    if(tmpType=="每日") {
         xz_time = $("#xz_time").val();
    }else {
        xz_time=0;
    }


    var cardType = $('input[name="cardType"]:checked').val();

    var tjsite = $('input[name="tjsite"]:checked').val();
    if (!tjsite) tjsite = "";
    /*console.log("商品ID" + goodsid);

     console.log("日期类型" + tmpType);
     console.log("线路ID" + lineid);
     console.log("选择的日期" + xz_time);
     console.log("乘车人类型" + cardType);
     console.log("途经站点" + tjsite);*/

    var url = "orderLine.add.php?goodsid=" + goodsid + "&clientid=" + clientid + "&tmpType=" + tmpType + "&lineid=" + lineid + "&xz_time=" + xz_time + "&cardType=" + cardType + "&tjsite=" + tjsite;
    //console.log(url);
    window.location.href = url;

}
//----------------日期选择页面提交过程
