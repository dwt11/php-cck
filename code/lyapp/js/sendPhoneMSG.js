var countdown = 60;
var isOne = true;
function settime(obj, clientid, name) {
    if (isOne) {
        var phone = $("#mobilephone").val();
        $.ajax({
            type: "POST",
            url: "/lyapp/sendPhoneMSG.php",
            data: {
                clientid: clientid,
                mobilephone: phone,
                name: name
            },
            dataType: 'text',
            success: function (result) {
                if (isNaN(result)) {
                    //如果果返回的信息为非数字的,则提示出错
                    layer.msg(result, {
                        shade: 0.5, //开启遮罩
                        time: 1000 //20s后自动关闭
                    });
                    countdown = 0;
                }
            }
        });
        isOne = false;
    }
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.innerText = "获取验证码";
        countdown = 60;
        isOne = true;
        return;
    } else {
        obj.setAttribute("disabled", true);
        obj.innerText = "重新发送" + countdown + "";
        countdown--;
    }
    setTimeout(function () {
            settime(obj)
        }
        , 1000)
}

