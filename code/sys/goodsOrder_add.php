<?php
/**
 * 订单添加
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once("goods.functions.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


if (empty($dopost)) $dopost = '';


/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {
    /*
        if ($mobilephone == "" || $installaddress == "" || $realname == "" || $tag == "") {
            ShowMsg("客户的姓名、联系电话、小区名称和安装地址不能为空！", -1);
            exit();
        }
    */

    if (empty($desc)) $desc = '';
    if (empty($clientid)) $clientid = '';

    //获取编号
    $goodsOrderCode = date("Ym") . "001";
    $questr = "SELECT MAX(goodsOrderCode)  as goodsOrderCode FROM `#@__sys_goods_order` where  FROM_UNIXTIME(senddate,'%Y-%m') ='" . date("Y-m") . "'";
    $rowarc = $dsql->GetOne($questr);
    if ($rowarc['goodsOrderCode'] != "") {
        $goodsOrderCode = $rowarc['goodsOrderCode'];
        $goodsOrderCode++;
    }
    $goodsOrderCode = $goodsOrderCode;
    $senddate = $payDate = GetMkTime($senddate);
    $desc = trim($desc);
    $userid = $CUSERLOGIN->getUserId();
    $payment = trim($payment);    //订单记录里用
    $paycode = trim($paycode);

    $status="支付完成";

    //插入订单

    $sqladdorder = "INSERT INTO `#@__sys_goods_order` ( `goodsOrderCode`, `depid`, `clientid`, `totalMoney`, `payMoney`, `senddate`, `payDate`, `status`, `desc`, `payment`, `paycode`, `userid`)
                VALUES ( '$goodsOrderCode', '$depid', '$clientid', '$totalMoney', '$payMoney', '$senddate', '$payDate', '$status', '$desc', '$payment', '$paycode', '$userid');";
    $dsql->ExecuteNoneQuery($sqladdorder);
    $orderid = $dsql->GetLastID();


    //循环15个商品,如果有商品编号,则添加到销售商品表这里的
    //同时添加到销售记录表
    for ($goodsi = 1; $goodsi < 16; $goodsi++) {
        $urladd = $price = $unit = $numb = $desc = "";
        $ename_urladd = "urladd" . $goodsi;
        //dump($$ename_urladd."---");
        if (!empty($$ename_urladd)) {

            $urladd = $$ename_urladd;


            $ename_price = "nowPrice" . $goodsi;
            $ename_unit = "unit" . $goodsi;
            $ename_numb = "goodsNumb" . $goodsi;
            $ename_desc = "desc" . $goodsi;
            $price = $$ename_price;
            $unit = $$ename_unit;
            $numb = $$ename_numb;
            $desc = $$ename_desc;

            $startDate=$senddate;//开始时间 即是订单时间
            $endDate=0;
              if($unit=="日")$endDate=strtotime('+'.$numb.' day', $startDate) ;//按日算
             if($unit=="周")$endDate=strtotime('+'.$numb.' week', $startDate) ;//按周算
            if($unit=="月")$endDate=strtotime('+'.$numb.' month', $startDate) ;//按月算

            $sqladdordergoods = "
                    INSERT INTO `#@__sys_goods_orderdetails` ( `orderId`,`depid`, `urladd`, `nowPrice`, `unit`, `numb`,  `desc`, `startDate`, `endDate`)
                    VALUES ( '$orderid','$depid', '$urladd', '$price', '$unit', '$numb',  '$desc', '$startDate', '$endDate');";
            $dsql->ExecuteNoneQuery($sqladdordergoods);


        }
    }

    ShowMsg("订单创建成功！", "goodsOrder.php");
    exit;
} elseif ($dopost == '') {

    //获取编号
    $goodsOrderCode = date("Ym") . "001";
    $questr = "SELECT MAX(goodsOrderCode)  as goodsOrderCode FROM `#@__sys_goods_order` where  FROM_UNIXTIME(senddate,'%Y-%m') ='" . date("Y-m") . "'";
    $rowarc = $dsql->GetOne($questr);
    if ($rowarc['goodsOrderCode'] != "") {
        $goodsOrderCode = $rowarc['goodsOrderCode'];
        $goodsOrderCode++;
    }
    $goodsOrderCode = "DD" . $goodsOrderCode;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单添加 订单号:<?php echo $goodsOrderCode; ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg" onload="InitPage()">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5>订单添加 </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form name="form1" id="form1" action="goodsOrder_add.php" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">订单号</label>

                            <div class="col-sm-2">
                                <label class="control-label"><?php echo $goodsOrderCode; ?></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">订单建立日期:</label>

                            <div class="col-sm-2" style="width: 130px">
                                <input type="text" name="senddate" class="form-control Wdate" size="14" value="<?php echo GetDateMk(time()); ?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">公司:</label>

                            <div class="col-sm-2">
                                <select class="form-control m-b" name='depid'>
                                    <option value='0'>请选择公司...</option>
                                    <?php
                                    $depOptions = GetDepOnlyTopOptionList();
                                    echo $depOptions;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">缴费人员:</label>

                            <div class="col-sm-2">这里随后要选择所选公司包含的员工
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">支付方式</label>

                            <div class="col-sm-4">
                                <?php echo GetEnumsForm("payment", '1', '', '', 'radio'); ?>
                            </div>
                            <div class="col-sm-6"></div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">流水号</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="paycode">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" name="bbb" onclick="AddGoodsTr();">增加商品选项</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="goodslist">
                                <thead>
                                <tr>
                                    <th class="text-center"></th>
                                    <th class="text-center">序号</th>
                                    <th class="text-center">选择</th>
                                    <th class="text-center">商品信息</th>
                                    <th class="text-center">销售单价</th>
                                    <th class="text-center">销售数量</th>
                                    <th class="text-center">总价(元)</th>
                                    <th class="text-center">销售单位</th>
                                    <!-- <th></th> -->
                                </tr>
                                </thead>
                                <tbody>
                                <tr id="tr1">
                                    <td class="text-center col-xs-1"></td>
                                    <td class="text-center col-xs-1">1</td>
                                    <td class="col-xs-2">
                                        <button type="button" class="btn" onclick="select(1)">选择</button>
                                    </td>
                                    <td>
                                        <input id="urladd1" name="urladd1" value="" type="hidden">
                                        <input id="unit1" name="unit1" value="" type="hidden">
                                        <span id="_goodsInfo1"></span>
                                    </td>
                                    <td class="col-xs-1">
                                        <input type="text" name="nowPrice1" id="nowPrice1" value="0" class="form-control">
                                    </td>
                                    <td class="col-xs-1">
                                        <input type="text" name="goodsNumb1" id="goodsNumb1" value="0" class="form-control">
                                    </td>
                                    <td class="text-center col-xs-1"><span id="_singlegoodstotal1">0</span></td>
                                    <td class="text-center col-xs-1"><span id="_chargeunit1"></span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单总价</label>

                            <div class="col-sm-2 input-group">
                                <input type="text" class="form-control" id="totalMoney" name="totalMoney">
                                <span class="input-group-addon">元</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">支付金额</label>

                            <div class="col-sm-2 input-group">
                                <input type="text" class="form-control" id="payMoney" name="payMoney">
                                <span class="input-group-addon">元</span>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-primary">保存</button>
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
<!--日期控件-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="goodsOrder.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
    var i = 1;//设定默认的商品个数 在goodsOrder.js中调用
    function select(inputid) {
        layer.open({type: 2, title: '商品信息', content: 'sysgoods.select.php?inputid=' + inputid});
    }


    $().ready(function () {
        $("#form1").validate({
            rules: {
                depid: {isIntGtZero: !0},
                goodsNumb1: {isIntGtZero: !0}
            },
            messages: {
                depid: {isIntGtZero: "请选择公司"},
                goodsNumb1: {isIntGtZero: "请填写数量"}
            }
        })
    });

</script>
</body>
</html>