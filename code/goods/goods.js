/**
 * Created by Administrator on 2017-03-13.
 */
$(document).ready(function () {
    $(".i-checks").iCheck({
        checkboxClass: "icheckbox_square-green",
        radioClass: "iradio_square-green",
    })
    $("#form1").validate({
        rules: {
            goodsname: {required: !0},
            goodscode: {required: !0,
                remote: {//校验
                    type: "get",
                    url: "goods.do.php?dopost=validateGoodscode",
                    data: {
                        goodscode: function () {
                            return $("#goodscode").val();
                        },
                        goodsoldcode: function () {
                            var goodsoldcode= $("#goodsoldcode").val();
                            if(!goodsoldcode)goodsoldcode="";
                            return goodsoldcode;
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
            typeid: {
                required: !0,
                remote: {//校验旧密码是否正确
                    type: "get",
                    url: "goods.do.php?dopost=validateCheckIsPart",
                    data: {
                        typeid: function () {
                            return $("#typeid").val();
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
            }
        },
        messages: {
            goodsname: {required: "请填写名称"},
            goodscode: {required: "请填写编号", remote: "编号重复"},
            typeid: {required: "请选择分类", remote: "你所选择的分类不可以添加内容，请选择白色的选项"}
        }
    })
});
