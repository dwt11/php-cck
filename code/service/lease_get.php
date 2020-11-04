<?php
require_once("../config.php");
if (empty($dopost)) $dopost = '';
/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {

    $oper_id = $CUSERLOGIN->userID;

    //更新
    $completetime = time();
    $sql = "UPDATE `#@__order_addon_car` SET `state`=1,`get_infodate`='$completetime', `get_info`='$get_info',get_infooperatorid='$oper_id' WHERE id='$id' ";
//dump($sql);
    if (!$dsql->ExecuteNoneQuery($sql)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }


    //获取当前线路 当前时间 在预约表中,已经有的车辆,
    //然后在用车记录中,将不在上述记录中的车辆使用情况删除
    $questr77 = "SELECT start_date,carNumb FROM `#@__order_addon_car` WHERE  id='$id' ";
    $rowarc77 = $dsql->GetOne($questr77);
    $deviceid_weixin = "";//微信 使用的车辆ID
    $driverid_weixin = "";//微信 使用的司机ID
    if ($rowarc77['start_date'] != "") {
        $start_date = $rowarc77['start_date'];
        $carNumb = $rowarc77['carNumb'];

        //先将用车记录里原有的此线路 此发车时间 此车的记录 变为删除
        $sql = "DELETE FROM  `#@__device_automobile_uselog`  WHERE lineid=0 AND orderAddonId='$id' ";
        $dsql->ExecuteNoneQuery($sql);

        for ($car_i = 1; $car_i <= $carNumb; $car_i++) {

            $deviceid_str = "deviceid_" . $car_i;

            $driverid_str = "driverid_" . $car_i;
            $guideid_str = "guideid_" . $car_i;
            $deviceid_weixin = $$deviceid_str;
            $driverid_weixin = $$driverid_str;
            //再保存新值
            $sql = "INSERT INTO `#@__device_automobile_uselog` ( `deviceid`, `start_date`, `end_date`, `clientid`, `operatorid`, `orderAddonId`, `lineid`, `driverid`, `guideid`, `isdel`)
                                              VALUES ( '{$$deviceid_str}', '$start_date', '', '', '$oper_id', '$id', '0', '{$$driverid_str}', '{$$guideid_str}', '0');";
            $dsql->ExecuteNoneQuery($sql);
        }
    }

//读取 信息
    $query = "SELECT #@__order_addon_car.*,goods.goodsname,goods.goodscode,goods.litpic, order1.clientid,order1.ordernum  FROM #@__order_addon_car
                LEFT JOIN #@__goods goods ON goods.id=#@__order_addon_car.goodsid
                LEFT JOIN #@__order order1 ON order1.id = #@__order_addon_car.orderid  WHERE #@__order_addon_car.id='$id' ";
    $row = $dsql->GetOne($query);
    if (is_array($row)) {
        $goodsname = $row["goodsname"];
        $carNumb = $row["carNumb"];
        $first = "您好,您租赁的{$carNumb}台车辆已经安排[$goodsname]";

        $ordercode = $row["ordernum"];
        $start_date = MyDate('Y年m月d日',$row["start_date"]);


        $devicename = "";
        //如果车不同，才获取新的值
        if ($deviceid_weixin != "") {

            //获取车牌号
            $arcQuery55 = "SELECT devicename  FROM #@__device  WHERE  id='$deviceid_weixin' ";
            $arcRow55 = $dsql->GetOne($arcQuery55);
            if ($arcRow55) {
                $devicename = $arcRow55["devicename"];
            }
        }

        $driverName = " ";
        $driverPhone = " ";
        if ($driverid_weixin != "") {
            $driverName = GetEmpNameById($driverid_weixin);
            $driverPhone = GetEmpPhoneById($driverid_weixin);
        }

        $clientid = $row["clientid"];
        $weixinMsgDataArray = array();
        $weixinMsgDataArray["first"] = $first;//订单号
        $weixinMsgDataArray["keyword1"] = "订单号:" . $ordercode;//订单号
        $weixinMsgDataArray["keyword2"] = $devicename;//车辆牌号
        $weixinMsgDataArray["keyword3"] = $start_date;//出发时间
        $weixinMsgDataArray["keyword4"] = $driverName;//驾车人
        $weixinMsgDataArray["keyword5"] = $driverPhone;// 电话
       // dump($weixinMsgDataArray);
       // dump($clientid);
        SendTemplateMessage("车辆安排提醒", $clientid, "17", $weixinMsgDataArray);

    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    ShowMsg("操作成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == '') {


    //读取 信息
    $query = "SELECT #@__order_addon_car.*,goods.goodsname,goods.goodscode,goods.litpic, order1.desc,order1.createtime,order1.ordernum  FROM #@__order_addon_car
                LEFT JOIN #@__goods goods ON goods.id=#@__order_addon_car.goodsid
                LEFT JOIN #@__order order1 ON order1.id = #@__order_addon_car.orderid  WHERE #@__order_addon_car.id='$id' ";
    $row = $dsql->GetOne($query);
    if (!is_array($row)) {
        ShowMsg("读取信息出错!", "-1");
        exit();
    }


}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">

    <!--表格数据区------------开始-->
    <div class="table-responsive">
        <table data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-sort-order="desc" data-mobile-responsive="true">
            <thead>
            <tr>
                <th data-halign="center" data-align="left">商品信息</th>
                <th data-halign="center" data-align="left">联系方式</th>
                <th data-halign="center" data-align="left">日期</th>
                <th data-halign="center" data-align="left">备注</th>
                <th data-halign="center" data-align="left">车辆使用</th>
                <th data-halign="center" data-align="center">创建时间</th>
            </tr>
            </thead>

            <tr>
                <td>
                    <?php
                    $photo = $row["litpic"];
                    if ($photo == "") $photo = "/images/arcNoPic.jpg";


                    echo " <img data-original=\"$photo\" width=\"60\" height=\"60\" style='float:left;margin-right: 5px'/>";
                    $goodscode = $row["goodscode"];
                    echo "[$goodscode] <b>" . $row["goodsname"] . "</b>";
                    echo "<br>订单编号:CAR" . $row["ordernum"];
                    ?>
                </td>
                <?php

                $name = $row["realname"];
                $tel = $row["tel"];
                ?>

                <td>
                    <?php echo $name . "<br>" . $tel ?>
                </td>


                <td>
                    <?php
                    $start_date = GetDateNoYearMk($row["start_date"]);

                    $end_date = GetDateNoYearMk($row["end_date"]);
                    $dayNumb = SubDay($row["end_date"], $row["start_date"]) + 1;
                    $carNumb = $row["carNumb"];
                    $buyNumb = $dayNumb * $carNumb;


                    echo "取车日期:$start_date";
                    echo "<br>还车日期:$end_date";
                    echo "<br>天数:$dayNumb";
                    echo "<br>台数:$carNumb";
                    echo "<br>合计: $buyNumb 件";

                    ?>
                    <span id="appttime"><?php echo $row["start_date"];?></span>
                </td> <td>
                    <?php echo $row["desc"]; ?>
                </td>

                <td>
                    <?php
                    echo GetOrderUseDeviceLog($row['id']);
                    ?>
                </td>
                <td>
                    <?php
                    echo GetDateTimeMk($row['createtime']);
                    ?>
                </td>

            </tr>
        </table>
    </div>
    <br><br><br>
    <form id="form1" name="form1" action="" method="post" class="form-horizontal">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="dopost" value="save">


        <div class="form-group">
            <label class="col-sm-2 control-label">车辆:</label>

            <div class="col-sm-6 form-control-static">
                <?php echo $carNumb . "台" ?>

            </div>
        </div>

        <?php
        for ($car_i = 1; $car_i <= $carNumb; $car_i++) {
            echo "<div class=\"form-group\" id=\"tr_1\">
                                <label for=\"\" class=\"col-sm-2 control-label\">车辆信息{$car_i}:</label>
                                <div class=\"col-sm-10 form-inline\">
                                    <div class=\"form-group\">
                                       <a href=\"javascript:carInfo_select($car_i);\" class=\"btn btn-primary\" data-toggle='tooltip' data-placement='top' title='选择车辆'>
                                            选择车辆
                                        </a>
                                        <span id='devicename_{$car_i}'></span>
                        
                                        <input id='deviceid_{$car_i}' name='deviceid_{$car_i}' value='' type='hidden'> 
                                    </div>
                                    <div class=\"form-group\">
                                        <a href=\"javascript:driverid_select({$car_i});\" class=\"btn  btn-primary\" data-toggle='tooltip' data-placement='top' title='选择司机'>
                                            选择司机
                                        </a>
                                        <span id='drivername_{$car_i}'></span>
                        
                                        <input id='driverid_{$car_i}' name='driverid_{$car_i}' value='' type='hidden'>                                    
                                    </div>
                                    <div class=\"form-group\">
                                            <a href=\"javascript:guideid_select({$car_i});\" class=\"btn  btn-primary\" data-toggle='tooltip' data-placement='top' title='选择乘务'>
                                                选择乘务
                                            </a>
                                            <span id='guidename_{$car_i}'></span>
                            
                                            <input id='guideid_{$car_i}' name='guideid_{$car_i}' value='' type='hidden'>
                                    </div>
                                </div>
                            </div>
                ";
        }

        ?>


        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea name="get_info" id="get_info" class="form-control" placeholder="请填写内容" rows="5"></textarea>
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label"></label>

            <div class="col-sm-2">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--日期控件-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $().ready(function () {
        $("#form1").validate({
            rules: {
                <?php
                for ($car_i = 1; $car_i <= $carNumb; $car_i++) {

                    echo "deviceid_{$car_i}: {required: !0},
                           driverid_{$car_i}: {required: !0},";
                }?>

                get_info: {required: !0}
            },
            messages: {
                <?php
                for ($car_i = 1; $car_i <= $carNumb; $car_i++) {

                    echo "deviceid_{$car_i}: {required: \"请选择车辆\"},
                           driverid_{$car_i}: {required: \"请选择司机\"},";
                }?>
                get_info: {required: "请填写内容"}
            }
        })
    });


    //选择车辆
    function carInfo_select(car_group_i) {
        var appttime = $('#appttime').html();
        if (!appttime) appttime = "";

        layer.open({
            type: 2,
            title: '选择车辆',
            content: '../device/device.select.php?appttime=' + appttime + '&targetname=deviceid_' + car_group_i
        });
    }
    //选择司机
    function driverid_select(car_group_i) {
        layer.open({
            type: 2,
            title: '选择司机',
            content: '../emp/emp.select.radio.php?targetname=driverid_' + car_group_i+'&emp_dep=30'
        });
    }
    //选择乘务
    function guideid_select(car_group_i) {
        layer.open({
            type: 2,
            title: '选择乘务',
            content: '../emp/emp.select.radio.php?targetname=guideid_' + car_group_i+'&emp_dep=30'
        });
    }


    $(function () {
        var vars_value = {};
        intervalName = setInterval(handle, 1000);//定时器句柄
        function handle() {
            // IE浏览器此处判断没什么意义，但为了统一，且提取公共代码而这样处理。
            //如果值不一样,则代表了改变

            //车辆值改变
            $("input[name^='deviceid_']").each(
                function () {
                    var e_name = $(this).attr("id");//表单名称
                    var tmpvalue = vars_value[e_name];//临时的值
                    if (!tmpvalue) tmpvalue = "";
                    //console.log("tmpvalue " + tmpvalue);
                    var nowvalue = $(this).val();
                    if (nowvalue != tmpvalue) {
                        vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                        //console.log(vars_value.e_name);
                        var e_name_array = e_name.split("_");
                        var car_group_i = e_name_array[1];

                        $.ajax({
                            type: "get",
                            url: "/device/device.do.php",
                            data: {
                                deviceid: nowvalue,
                                dopost: "GetOneDeviceInfo"
                            },
                            dataType: 'json',
                            success: function (result) {
                                $("#devicename_" + car_group_i).html(result.devicename);
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                $("#devicename_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                            }
                        });
                    }
                }
            )


            //司机值改变
            $("input[name^='driverid_']").each(
                function () {
                    var e_name = $(this).attr("id");//表单名称
                    var tmpvalue = vars_value[e_name];//临时的值
                    if (!tmpvalue) tmpvalue = "";
                    //console.log("tmpvalue " + tmpvalue);
                    var nowvalue = $(this).val();
                    if (nowvalue != tmpvalue) {
                        vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                        //console.log(vars_value.e_name);
                        var e_name_array = e_name.split("_");
                        var car_group_i = e_name_array[1];

                        $.ajax({
                            type: "get",
                            url: "/emp/emp.inc.do.php",
                            data: {
                                emp_id: nowvalue,
                                dopost: "GetOneEmpInfo"
                            },
                            dataType: 'json',
                            success: function (result) {
                                $("#drivername_" + car_group_i).html(result.emp_realname);
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                $("#drivername_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                            }
                        });
                    }
                }
            )

            //乘务值改变
            $("input[name^='guideid_']").each(
                function () {
                    var e_name = $(this).attr("id");//表单名称
                    var tmpvalue = vars_value[e_name];//临时的值
                    if (!tmpvalue) tmpvalue = "";
                    //console.log("tmpvalue " + tmpvalue);
                    var nowvalue = $(this).val();
                    if (nowvalue != tmpvalue) {
                        vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                        //console.log(vars_value.e_name);
                        var e_name_array = e_name.split("_");
                        var car_group_i = e_name_array[1];

                        $.ajax({
                            type: "get",
                            url: "/emp/emp.inc.do.php",
                            data: {
                                emp_id: nowvalue,
                                dopost: "GetOneEmpInfo"
                            },
                            dataType: 'json',
                            success: function (result) {
                                $("#guidename_" + car_group_i).html(result.emp_realname);
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                $("#guidename_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                            }
                        });
                    }
                }
            )


        }
    });

</script>

</body>
</html>



