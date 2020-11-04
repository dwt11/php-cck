$(document).ready(function () {
    $('#buyNumb_t').spinner(
        {
            value: 1,
            min: 1,
            max: 10,
            step: 1
        });


    //初始值
    var buyNumb_t = 1;
    //有改变后的
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        //如果值不一样,则代表了改变
        if ($("#buyNumb_t").val() != buyNumb_t) {
            //console.log($("#lineid").val()+"----"+lineid);
            buyNumb_t = $("#buyNumb_t").val();//保存改变后的值
            $("#buyNumb").html(buyNumb_t);
            console.log("车辆:" + buyNumb);
            getTotalMoney();
        }


    }
});
//表单验证
function from_validate() {
    $("#error_str").text("");
    $("#error").hide();

//这里没有用,只是为了格式 返回一下
    var buyNumb = $("#buyNumb").html();
    return "&type=1&buyNumb=" + buyNumb;
}

