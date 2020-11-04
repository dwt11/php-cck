/**
 * Created by dell on 2016-06-15.
 */
/*删除确认提示框*/
function isdel(actionUrl) {
    layer.confirm('您确定要删除此内容吗？', {icon: 3, title: '提示'}, function (index) {
        location.href = actionUrl;
        layer.close(index);
    });
}

$(document).ready(function(){
   /* $(".main").append("<div id='overlay'></div>");*/
    $("#menu").click(function(){
        $("#guide").slideToggle();
        //$("#overlay").toggleClass("show");
    })

    //search
    /*$(".text").keypress(function(){
     $(this).next(".btn").show();
     $(".btn").click(function(){
     $(this).siblings(".text").val("");
     })
     });*/
    $(".text").focus(function(){
        var txt_value =  $(this).val();
        if(txt_value==this.defaultValue){
            $(this).val("").css("color","#313131");
        }
    });
})

//图表切换 swiper使用
function fjcHeight(){
    $(".proportion").each(function(i,n){
        var FjcHeight=$(this).attr("F");//从HTML中取的高度比例
        var nowWidth=$(this).width();
        var thisHeight=nowWidth*parseFloat(FjcHeight);
        $(this).css("height",thisHeight+"px");
    });
}
function fontSizeHack(){
    var viewportW=$(".main").width();
    var fz=(viewportW/320)*0.625;
    $(".index-banner").css("font-size",(fz*100)+"%");
}

$(window).resize(function(){
    fjcHeight();
    fontSizeHack();
})





function msg_from_arc(id,title) {
    layer.open({
        type: 2,
        title: title,
        closeBtn: 0, //不显示关闭按钮
        anim: 2,
        shadeClose: 0, //开启遮罩
        scrollbar: false,//浏览器滚动禁用 手机不起作用,待查
        content: '/lyapp/archives/archives_view_JSMSG.php?id=' + id
    });
    //禁止主页面滚动
    $("body").bind("touchmove", function (event) {
        event.preventDefault();//code
    });
}