//引入JQUERY,cookie用于判断是否显示COOKIE
document.write(" <script language=\"javascript\" src=\"/ui/js/jquery.cookie.js\" > <\/script>");

$(document).ready(function () {
    var gofeedback = '<div class="quickButton gofeedback"><a class="animated bounceInUp" href="/lyapp/member/feedback_add.php" title="反馈"><i class="fa fa-envelope-o" ></i></a></div>';
    $("body").append(gofeedback);
    SETServicePhoneIcon();
    //SETGWCicon();
    var DWTis_coupon_view = 1;
    DWTis_coupon_view = $.cookie('DWTis_coupon_view');//获取COOK中是否显示红包

    //alert(DWTis_coupon_view);
    if (DWTis_coupon_view) {
        //如果没有显示过红包,则显示
        var index = layer.open({
            type: 2,
            title: false,
            closeBtn: 0, //不显示关闭按钮
            anim: -1,
            shadeClose: 0, //开启遮罩
            shade: [0.8, '#000000'],
            scrollbar: false,//浏览器滚动禁用 手机不起作用,待查
            content: '/lyapp/hongbao.php'
        });
    }
});

function SETServicePhoneIcon() {
    var servicePhone = "12345678";
    /*$.ajax({
        type: "get",
        url: "/lyapp/getServicePhone.ajax.php",
        async: false,//这个执行完才执行下面的
        dataType: 'html',
        success: function (result) {
            servicePhone=result;
        }
    });*/
    if (servicePhone != "") {
        var gophone = '<div class="quickButton gophone"><a class="animated bounceInUp" href="tel:' + servicePhone + '" title="客服电话"><i class="fa fa-phone" > </i></a></div>';
        $("body").append(gophone);
    }
}

function SETGWCicon() {
    var GWCnumber = 0;
    $.ajax({
        type: "get",
        url: "/lyapp/order/GWCgetNumber.php",
        async: false,//这个执行完才执行下面的
        dataType: 'html',
        success: function (result) {
            GWCnumber = result;
        }
    });
    if (GWCnumber > 0) {
        var goGWC = '<div class="quickButton goGWC"><a class="animated bounceInUp" href="/lyapp/order/GWC.php" title="购物车"><i class="fa fa-shopping-cart" ></i></a></div>';
        $("body").append(goGWC);
    }
}

//返回顶部
$.fn.manhuatoTop = function (options) {
    var defaults = {
        showHeight: 150,
        speed: 1000
    };
    var options = $.extend(defaults, options);
    $("body").append("<div class='quickButton gotop' id='totop' style='display: none' ><a class='animated bounceInUp' href='#' title='返回顶部' ><i class='fa fa-angle-double-up'></i></a></div>");
    var $toTop = $(this);
    var $top = $("#totop");
    var $ta = $("#totop a");
    $toTop.scroll(function () {
        var scrolltop = $(this).scrollTop();
        if (scrolltop >= options.showHeight) {
            $top.show();
        }
        else {
            $top.hide();
        }
    });
    $ta.hover(function () {
        $(this).addClass("cur");
    }, function () {
        $(this).removeClass("cur");
    });
    $top.click(function () {
        $("html,body").animate({scrollTop: 0}, options.speed);
    });
}
$(window).manhuatoTop({
    showHeight: 400,//设置滚动高度时显示
    speed: 500 //返回顶部的速度以毫秒为单位
});
