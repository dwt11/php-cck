$(function () {
    //用于控制图片自适应宽度和高度 151106添加
    var autocss = {"max-width": "100%", "height": "auto"};
    $("img").css(autocss);

    $('#s-header').onePageNav();



    //屏幕下拉显示TAB
    $(window).scroll(function () {
        var targetTop = $(this).scrollTop();//当前屏幕顶部位置
        if (targetTop >= 300) {
            //下划离开顶部就显示TAB
            $("#s-header").addClass("fixedheader");
            $(".tab").show();
        } else {
            //回到顶部则不显示TAB
            $("#s-header").removeClass("fixedheader");
            $(".tab").hide();
        }
    });


});
