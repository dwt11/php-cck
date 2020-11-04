//遍历radio的点击 事件
$("input[name^='sydateint_']").on('ifChecked', function (event) {//必须选一个
    ////如果当前RADIO被选中,则取值
    var dayint = $(this).val();
    //setChecked();
    //console.log(benefitInfo_CheckedNumb);

});
$("input[name^='sydateint_']").on('ifUnchecked', function (event) {
    //setChecked();
});

var selectEDindex_arr1 = new Array();//选中的index

//将两个中间的check默认选中
//如果第一个和第三个选中了，则中间的第二个也选择
//170522更新不自动选中中间的值了,如果有中断 就提示用户


function carSelectSubimt(date3day) {


    selectEDindex_arr1.length = 0;    //获取 当前页面已经选择了的INDEX索引
    $("#error_str").text("");
    $('input[name^="sydateint_"]:checked').each(function () {
        var e_name_index = $(this).attr("id").replace("sydateint_", "");//表单名称
        selectEDindex_arr1.push(parseInt(e_name_index));
        console.log(e_name_index);
    });
    console.log(selectEDindex_arr1);
    //如果已经选择的元素大于2个，则循环每两个之前的元素为选中状态
    if (selectEDindex_arr1.length > 1) {
         for (var cciiikey = 1; cciiikey < selectEDindex_arr1.length; cciiikey++) {


             var now_index_value=selectEDindex_arr1[cciiikey];//当前索引值
             var before_index_value=now_index_value-1;  //当前索引的前一个值
             if(before_index_value!=selectEDindex_arr1[cciiikey-1]) {
                 //如果当前索引值的前一个没有选择,则提示
                 $("#error_str").text("不能有中断的日期");
                  return false;

             }
        }


        /* for (var iiii = 0; iiii <= selectEDindex_arr1[selectEDindex_arr1.length-1]; iiii++) {
         //if (selectEDindex_arr1[iiii + 1]) {
         //如果当前元素的下一个元素存在
         //则循环两个元素之间的checkbox为选中状态
         // var start_iiii = selectEDindex_arr1[iiii];
         //var end_iiii = selectEDindex_arr1[iiii + 1];
         //for (var cciii = start_iiii + 1; cciii < end_iiii; cciii++) {
         //console.log(cciii);
         $("input[name='sydateint_" + iiii + "']").iCheck('toggle');
         //这里有BUG,如果自动选择后,用户手动取消的话,不会再自动选择上
         //}
         //}
         }*/
    }



    console.log(selectEDindex_arr1.length);
    var startdateint = 0;
    var enddateint = 0;
    var jbnum = 0;
    var jfnum = 0;
    var benefitInfo_text = "";


    if (selectEDindex_arr1.length == 0) {
        $("#error_str").text("请选择使用日期");
        return false;
    }

    //开始日期必须在今日之后的三天内
    var startdate_str_all = $("#datestrall_" + selectEDindex_arr1[0]).html();


    var is3day = 0;
    var date3day_arry = date3day.split(",")
    for (var ddddd44iiii = 0; ddddd44iiii < date3day_arry.length; ddddd44iiii++) {

        if (date3day_arry[ddddd44iiii] == startdate_str_all) is3day = 1;
    }

    if (is3day == 0) {
        $("#error_str").text("开始日期必须是未来三天内");
        return false;
    }

    console.log(startdate_str_all);


    if (selectEDindex_arr1.length == 1) {
        startdateint = $("input[name='sydateint_" + selectEDindex_arr1[0] + "']").val();
        enddateint = startdateint;
        var money = $("#money_" + selectEDindex_arr1[0]).html();
        money = money.trim();
        var money_array = money.split(" ");
        for (var dddddiiii = 0; dddddiiii < money_array.length; dddddiiii++) {
            if (money_array[dddddiiii].indexOf("金币") >= 0) jbnum += parseInt(money_array[dddddiiii].replace("金币", "").trim());
            if (money_array[dddddiiii].indexOf("积分") >= 0) jfnum += parseInt(money_array[dddddiiii].replace("积分", "").trim());
        }
        benefitInfo_text = "金币" + jbnum + " 积分" + jfnum;
    } else if (selectEDindex_arr1.length > 1) {


        startdateint = $("input[name='sydateint_" + selectEDindex_arr1[0] + "']").val();//开始的日期
        enddateint = $("input[name='sydateint_" + selectEDindex_arr1[selectEDindex_arr1.length - 1] + "']").val();//结束 的日期  最后一个日期

        for (var iiii = 0; iiii < selectEDindex_arr1.length; iiii++) {

            var money = $("#money_" + selectEDindex_arr1[iiii]).html();
            money = money.trim();
            var money_array = money.split(" ");
            var jbtmp = "";
            var jftmp = "";
            for (var dddddiiii = 0; dddddiiii < money_array.length; dddddiiii++) {


                var date_str = $("#datestr_" + selectEDindex_arr1[iiii]).html();


                if (money_array[dddddiiii].indexOf("金币") >= 0) {
                    jbtmp = parseInt(money_array[dddddiiii].replace("金币", "").trim());
                    jbnum += jbtmp;
                }
                if (money_array[dddddiiii].indexOf("积分") >= 0) {
                    jftmp = parseInt(money_array[dddddiiii].replace("积分", "").trim());
                    jfnum += jftmp;
                }

            }
            console.log(money_array);
            benefitInfo_text += "  [" + date_str + "]金币" + jbtmp + " 积分" + jftmp;

        }


    }
    console.log("开始" + startdateint);
    console.log("结束" + enddateint);
    console.log("JB" + jbnum);
    console.log("JF" + jfnum);
    //console.log(cciii);
    console.log(benefitInfo_text);


    if (startdateint && enddateint && jbnum !== "" && jfnum !== "") {
        var goodsid = $("#goodsid").val();
        var url = "car_add.php?benefitInfo_text=" + benefitInfo_text + "&goodsid=" + goodsid + "&startdateint=" + startdateint + "&enddateint=" + enddateint + "&jbnum=" + jbnum + "&jfnum=" + jfnum;
        //console.log(url);
        window.location.href = url;

    } else {
        $("#error_str").text("请正确选择");
        return false;
    }


}


$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})

});

