var pageno = 0;
function iscrollloaded(A) {
    var C = '<div class="quickButton gotop"  id="gotop_iscroll" style="display: none" ><a class="animated bounceInUp" href="#" title="返回顶部" ><i class="fa fa-angle-double-up"></i></a></div>';
    $("body").append(C);
    var B, D = $("#up-icon"), E = $("#down-icon"), B = new IScroll("#wrapper", {
        probeType: 3,
        scrollbars: true,
        mouseWheel: true,
        fadeScrollbars: true,
        bounce: true,
        interactiveScrollbars: true,
        click: true,
        keyBindings: true,
        momentum: true
    });
    console.warn("maxScrollY" + B.maxScrollY);
    if (B.maxScrollY < 0) {
        $("#scroller-pullUp").show();
        $("#scroller-pullDown").show()
    }
    $("#gotop_iscroll").click(function () {
        B.scrollTo(0, 0, 1500)
    });
    B.on("scroll", function () {
        var H = this.y, F = this.maxScrollY - H, I = E.hasClass("reverse_icon"), G = D.hasClass("reverse_icon");
        if (H >= 40) {
            !I && E.addClass("reverse_icon");
            return ""
        } else {
            if (H < 40 && H > 0) {
                I && E.removeClass("reverse_icon");
                return ""
            }
        }
        if (F >= 40) {
            !G && D.addClass("reverse_icon");
            return ""
        } else {
            if (F < 40 && F >= 0) {
                G && D.removeClass("reverse_icon");
                return ""
            }
        }
        if (H < -500) {
            $("#gotop_iscroll").fadeIn(1500)
        } else {
            $("#gotop_iscroll").fadeOut(1500)
        }
    });
    B.on("slideDown", function () {
        if (this.y > 40) {
            var F = A + "&pageno=1";
            $.getJSON(F, function (H) {
                var G, I;
                G = document.getElementById("scroller-content");
                $(G).empty();
                $.each(H, function (J, K) {
                    I = document.createElement("li");
                    I.innerHTML = K;
                    G.appendChild(I, G.childNodes[0])
                });
                D.removeClass("reverse_icon");
                B.refresh()
            })
        }
    });
    B.on("slideUp", function () {
        if (this.maxScrollY - this.y > 1) {
            if (pageno == 0) {
                pageno = 2
            } else {
                pageno++
            }
            var F = A + "&pageno=" + pageno;
            try {
                $.ajaxSetup({
                    error: function (I, H) {
                        layer.msg("亲,没有更多的数据了", {time: 1000,});
                        return false
                    }
                });
                if (A != "") {
                    $.getJSON(F, function (I) {
                        if (I != "") {
                            var H, J;
                            H = document.getElementById("scroller-content");
                            $.each(I, function (K, L) {
                                J = document.createElement("li");
                                J.innerHTML = L;
                                H.appendChild(J, H.childNodes[0])
                            });
                            D.removeClass("reverse_icon");
                            B.refresh();
                            B.scrollBy(0, -50, 5000)
                        }
                    })
                } else {
                    layer.msg("亲,没有更多的数据了", {time: 1000,})
                }
            } catch (G) {
                layer.msg("亲,没有更多的数据了", {time: 1000,})
            }
        }
    })
};