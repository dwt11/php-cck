$().ready(function () {
    $("#formold").validate({
        rules: {
            mobilephone: {required: !0, minlength: 11, isMobile: !0},
            checkCode: {required: !0, minlength: 4, maxlength: 4}
        },
        messages: {
            mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "手机号应以13/14/15/17/18开头"},
            checkCode: {required: "请填写短信中的四位数字", minlength: "验证码应为4个数字", maxlength: "验证码应为4个数字"}
        },
        submitHandler: function (form) {
            $.ajax({
                type: "post",
                url: "phone_change.php",
                data: {dopost: "next", mobilephone: $("#mobilephone").val(), checkCode: $("#checkCode").val()},
                dataType: 'html',
                success: function (result) {
                    if (result == '验证成功') {
                        window.location.href = "phone_change.php?dopost=new";
                    } else {
                        layer.msg("<span style='font-size:30px'>" + result + " </span>", {
                            shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                            time: 4000 //2秒关闭（如果不配置，默认是3秒）
                        }, function () {
                            //这里出错后,不要跳转,if (!isdebug) window.location.href = 'order.php';
                            //这里要考虑一下,是否自动刷新 当前页面
                        });

                    }
                }
            });
        }
    });


    $("#formnew").validate({
        rules: {
            mobilephone: {
                required: !0, minlength: 11, isMobile: !0, notEqual: "#mobilephone_old",
                remote: {//校验
                    type: "get",
                    url: "phoneCodeCheck.ajax.php",
                    data: {
                        mobilephone: function () {
                            return $("#mobilephone").val();
                        },
                        clientid: function () {
                            return $("#clientid").val();
                        }
                    },
                    dataType: "html",
                    dataFilter: function (data, type) {
                        if (data == "true")
                            return true;
                        else
                            return false;
                    }
                }
            },
            checkCode: {required: !0, minlength: 4, maxlength: 4}
        },
        messages: {
            mobilephone: {
                required: "请填写手机号",
                minlength: "手机号应为11个数字",
                isMobile: "手机号应以13/14/15/17/18开头",
                notEqual: "不能与旧手机号相同",
                remote: "新手机号码已经在系统中存在,请更换"
            },
            checkCode: {required: "请填写短信中的四位数字", minlength: "验证码应为4个数字", maxlength: "验证码应为4个数字"}
        },
        submitHandler: function (form) {
            $.ajax({
                type: "post",
                url: "phone_change.php",
                data: {dopost: "save", mobilephone: $("#mobilephone").val(), checkCode: $("#checkCode").val()},
                dataType: 'html',
                success: function (result) {

                    if (result == "更换成功") {
                        layer.msg('更换成功', {
                            shade: 0.5, //开启遮罩
                            time: 1000, //20s后自动关闭
                        }, function () {
                            window.location.href = "member/myinfo.php";
                        });
                    } else  {
                        layer.msg(result, {
                            shade: 0.5, //开启遮罩
                            time: 1000, //20s后自动关闭
                        }, function () {
                         });
                    }
                }
            });
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg("系统错误,请重试", {
                        shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        window.location.href = 'phone.php';
                    });
                }
            })
        });
