<?php
/**
 * 订单快速添加 第一步,选择商品和客户
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
if (empty($dopost)) $dopost = '';


?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?> </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form id="form" action="orderZtc.addquick.php" class="form-horizontal">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择商品:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectGoods()">选择商品</button>
                                <input type="hidden" name="goodsid" id="goodsid" value=""/>
                                <span id="goodsid_str"><span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">商品名称:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="goodstitle"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="price"></span>
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">下一步</button>
                            </div>
                        </div>


                    </form>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>

<script src="../ui/js/validate.js"></script>

<script>
    $(function () {
        var goodsid = "";
        intervalName = setInterval(handle, 1000);//定时器句柄
        function handle() {
            //如果值不一样,则代表了改变
            if ($("#goodsid").val() != goodsid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                goodsid = $("#goodsid").val();//保存改变后的值
                $("#goodsid_str").html("编号" + goodsid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../goods/goods.do.php",
                    data: {
                        goodsid: goodsid,
                        dopost: "GetOneGoodsInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        $("#price").html(result.price);
                        $("#goodstitle").html(result.goodsname);
                    }
                });
            }
        }
    });
    function selectGoods() {
        layer.open({type: 2, title: '选择商品', content: '../goods/goods.select.php?typeid=1'});
    }




    $().ready(function () {
        $("#form").validate({
            rules: {
                goodsid: {required: true}
            },
            messages: {
                goodsid: {required: "请选择商品"}
            }
        });

    });
</script>

</body>
</html>