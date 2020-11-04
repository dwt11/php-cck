var countdown = 60;
var isOne = true;
function settime(obj, clientid, name, depid) {
    if (isOne) {
        var phone = $("#mobilephone").val();
        $.ajax({
            type: "POST",
            url: "phoneMSG_test_send.do.php",
            data: {
                clientid: clientid,
                mobilephone: phone,
                depid: depid,
                name: name
            },
            dataType: 'text',
            success: function (result) {
                if (isNaN(result)) {
                    //如果果返回的信息为非数字的,则提示出错
                    layer.alert(result, {icon: 6});
                }else{
                    layer.alert('发送成功', {icon: 6});
                }
                countdown = 0;
            }
        });
        isOne = false;
    }
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.innerText = "发送";
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

