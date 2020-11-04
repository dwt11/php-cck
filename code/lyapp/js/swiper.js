$(document).ready(function () {
    var mySwiper = new Swiper('.index-banner', {
        initialSlide: 1,//默认第一个
        pagination: '.index-pagination',
        autoplay: 3000,
        loop: true,
        onSlideChangeEnd: function () {
            //图片切换后，切换标题
            var bannerTitle = $(".index-banner ul li.swiper-slide-active img").attr("title");
            $(".banner-title p").text(bannerTitle);
        }
    })
    var bannerFstTitle = $(".index-banner ul li.swiper-slide-active img").attr("title");
    $(".banner-title p").text(bannerFstTitle);
    fjcHeight();
    fontSizeHack();
    $(".index-banner ul li img").show();
})
