<?php
/**
 * 订单添加 第一步,选择商品和客户
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {
//------------------------------返回值初始化
//"info" => "",   提示信息
//"jsApiParameters" => "",支付字符串
//"orderid" => ""  订单ID
    $aa = array(
        "info" => "",
        "jsApiParameters" => "",
        "orderid" => ""
    );


//------------变量预处理
    $clientid = $clientid;
    $ordertype = "orderZtc";
    $jfnum100 = 0;
    $jbnum100 = 0;
    $total100 = 0;
    $paynum100 = 0;
    $fh_ejjb100 = 0;
    $fh_ejjf100 = 0;
    $fh_sjjb100 = 0;
    $fh_sjjf100 = 0;
    $benefitCreatetime = 0;
    $operatorid = $CUSERLOGIN->userID;
    $buynumb = 1;
//---------------------------------------创建通用主订单

    $orderReturnStr = CreateOrder(
        $clientid,
        $ordertype,
        $desc,
        $jfnum100,
        $jbnum100,
        $operatorid,
        $total100,
        $paynum100,
        $benefitCreatetime,
        $fh_ejjb100,
        $fh_ejjf100,
        $fh_sjjb100,
        $fh_sjjf100,
        $buynumb
    );
    $orderReturnStr_array = explode(",", $orderReturnStr);
    $orderInfo = "";      //订单操作成功与否信息
    $orderCode = "";//订单编号
    $orderId = "";//订单Id
    if (is_array($orderReturnStr_array)) {
        $orderInfo = $orderReturnStr_array[0];      //订单操作成功与否信息
        $orderCode = $orderReturnStr_array[1];//订单编号
        $orderid = $orderReturnStr_array[2];//订单Id
    }
//dump($orderReturnStr);
    if ($orderInfo != "订单创建成功") {
        $aa = array(
            "info" => $orderInfo,
            "jsApiParameters" => "",
            "orderid" => ""
        );
        echo json_encode($aa);
        exit();
    }
//---------------------------------------创建通用主订单


//---------------------------------------创建订单附加

//同时添加到销售记录表
    if (!empty($realname) || !empty($mobilephone) || !empty($idcard)) {
         $litpic_filename="";//默认照片为空
        if($litpicsrc!=""){
            //如果没有上传照片，原有照片地址，则用原来 的
            $litpic_filename=$litpicsrc;
        }

        //获取商品ID
        $goodsid=1;//默认是直通车
        $questr = "SELECT goodsid  FROM `#@__order_addon_ztc`  where  orderid='$orderid_old'";
        $row = $dsql->GetOne($questr);
        if (isset($row["goodsid"])&&$row["goodsid"]>0) {
            $goodsid=$row["goodsid"];
        }


        $sqladdordergoods = "
                    INSERT INTO `#@__order_addon_ztc` ( `orderid`,`goodsid`,`name`, `tel`, `idcard`, `idpic`)
                    VALUES ( '$orderid','{$goodsid}','{$realname}', '{$mobilephone}', '{$idcard}', '{$litpic_filename}');";
        $dsql->ExecuteNoneQuery($sqladdordergoods);
    }

//---------------------------------------创建订单附加


//---------------------------------------订单支付过程
    $paytime = time();
    $createtime=GetMkTime($createtime);//修改订单的时间
    $sql = "UPDATE `#@__order`    SET `paytype`='0元',sta=1,paytime='$paytime',pay_transaction_id='',createtime='$createtime' WHERE id='$orderid';    ";

    $dsql->ExecuteNoneQuery($sql);
    $aa = array(
        "info" => "订单创建成功",
        "jsApiParameters" => "",
        "orderid" => $orderid
    );
    createRankLog($orderid, $clientid);//更新会员的类型  //这里的时间是当前操作时间
    //这里将此订单的会员类型时间修改为正确的
    $addRowAddtable = $dsql->GetOne("SELECT rankLenth FROM `#@__goods_addon_ztc` WHERE goodsid='$goodsid'");
    $rankLenth = $addRowAddtable["rankLenth"];
    $ranktime = $createtime;
    $rankcutofftime = strtotime("+{$rankLenth} month", $ranktime);
    $sql = "UPDATE `#@__clientdata_ranklog`    SET ranktime='$ranktime',rankcutofftime='$rankcutofftime'  WHERE   orderid='$orderid';    ";
    $dsql->ExecuteNoneQuery($sql);


    if ($orderid_old > 0) {
        //更新旧订单的备注
        $time = GetDateMk(time());
        $sql = "UPDATE `#@__order`    SET isdel=3,`desc`=concat(`desc`,'<br>{$time}补办新订单ID:{$orderid} 新订单号:{$orderCode}') WHERE id='$orderid_old';    ";
        $dsql->ExecuteNoneQuery($sql);
    }
//---------------------------------------订单支付过程


    echo json_encode($aa);
    exit();


}
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
                    <form id="form" action="orderZtc.add.php" class="form-horizontal">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择已挂失的订单:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectOrder()">选择已挂失的订单</button>
                                <input type="hidden" name="orderid" id="orderid" value=""/>
                                <input type="hidden" name="ordernum" id="ordernum" value=""/>
                                <span id="orderid_str"><span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">补办新订单的信息:</label>
                            <div class="col-sm-10">
                                <span id="orderinfo"></span>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">照片:</label>

                            <div class=" col-sm-2">
                                <img src="/images/arcNoPic.jpg" width="50" height="50" id="imgsrc"/>
                                <span id="litpicsrc" style="display: none"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择会员:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                                <input type="hidden" name="clientid" id="clientid" value=""/>
                                <span id="clientid_str"><span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">会员姓名:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="clientrealname"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">原支付日期:</label>
                            <div class="col-sm-2">
                                <?php

                                //$time=GetDateMk(time());


                                ?>
                                <input type="text" name="createtime" id='createtime' class="form-control Wdate"
                                       size="12" placeholder="原支付日期" value=""
                                       onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc" id="desc"></textarea>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class='btn btn-primary' id='add_test' type='button' onclick='gopay()'>提交</button>
                                <span id="error" class="text-danger"></span>
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
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>


<script>
    $(function () {
        var orderid = "";
        intervalName = setInterval(handle, 1000);//定时器句柄
        function handle() {
            //如果值不一样,则代表了改变
            if ($("#orderid").val() != orderid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                orderid = $("#orderid").val();//保存改变后的值
                $("#orderid_str").html("订单ID" + orderid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../order/order.do.php",
                    data: {
                        orderid: orderid,
                        dopost: "GetOneOrderInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        var orderinfo = "";
                        var createtime = "";

                        for (var i = 0; i < result.length; i++) {
                            var orderinfo = '<div class="col-sm-10 form-inline">' +
                                '            <div class="form-group"  >' +
                                '                <input type="text" placeholder="姓名" id="realname" name="realname" class="form-control" value="' + result[i].name + '">' +
                                '            </div>' +
                                '           <div class="form-group"  >' +
                                '                <input type="text" placeholder="手机号码" id="mobilephone" name="mobilephone" class="form-control" value="' + result[i].tel + '">' +
                                '            </div>' +
                                '            <div class="form-group">' +
                                '                <input type="text" placeholder="身份证号码" id="idcard" name="idcard" class="form-control" value="' + result[i].idcard + '">' +
                                '            </div>' +
                                '        </div>';
                            //' 订单号:'+result[i].ordernum;

                            createtime = result[i].createtime;
                            ordernum = result[i].ordernum;
                            litpicsrc = result[i].idpic;

                            //if(i>0)orderinfo +="<br>";
                            //orderinfo += "订单号:"+result[i].ordernum  +"姓名:"+result[i].name  + " 电话:"+result[i].tel+ " 身份证:"+result[i].idcard;
                        }

                        $("#ordernum").val(ordernum);
                        $("#createtime").val(createtime);
                        $("#orderinfo").html(orderinfo);
                        //console.log(litpicsrc);
                        $("#litpicsrc").html(litpicsrc);
                        $("#imgsrc").attr('src', src = litpicsrc + "?t=" + Math.random());

                    }
                });
            }
        }
    });
    function selectOrder() {
        layer.open({type: 2, title: '选择订单', content: '../order/orderZtc.guashiSelect.php'});
    }


    //==============================选择客户
    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
    }


    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                $("#clientid_str").html("编号" + clientid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../client/client.do.php",
                    data: {
                        clientid: clientid,
                        dopost: "GetOneClientInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        console.log(result);
                        $("#clientrealname").html(result.realname + " " + result.mobilephone);
                    }
                });
            }
        }
    });

    $().ready(function () {
        $("#form").validate({
            rules: {
                orderid: {required: true},
                clientid: {required: true}
            },
            messages: {
                orderid: {required: "请选择挂失的订单"},
                clientid: {required: "请选择新用户"}
            }
        });

    });


    function gopay(orderUrl, backUrl) {
        var isdebug = false;
        //加载层
        //loading层


        var clientid = $("#clientid").val();
        if (clientid == '') {
            $("#error").text("会员信息获取错误,请刷新页面");
            return false;
        }


        var from_str = from_validate();
        if (!from_str) {
            //$("#error").text("订单提交失败,请刷新页面1");
            return false;
        }//表单验证未通过

        $("#error").text("");


        var desc = $("#desc").val();
        //var desc = encodeURIComponent($("#desc").val());
        if (!desc) {
            desc = "";
        }
        desc += "[补办卡]原订单ID：" + $("#orderid").val() + " 原订单编号:" + $("#ordernum").val();
        desc = encodeURIComponent(desc);

        var createtime = $("#createtime").val();

        //进度条
        var index = layer.load(2, {
            shade: [0.1, '#fff'] //0.1透明度的白色背景
        });
        $.ajax({
            type: "post",
            url: "orderZtc_buka.php?dopost=save" + from_str,
            data: {
                orderid_old: $("#orderid").val(),
                clientid: clientid,
                createtime: createtime,
                desc: desc
            },
            dataType: 'json',
            success: function (data) {
                layer.closeAll('loading'); //关闭加载层
                if (data.info == "订单创建成功") {
                    layer.msg(data.info, {
                        shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                      window.location.href = "orderZtc.php";
                    });
                } else {
                    layer.msg("订单创建失败 请在订单管理中核对 原因:" + data.info, {
                        shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        //这里出错后,不要跳转,还在当前页面 让用户选择操作if (!isdebug) window.location.href = backUrl;
                    });

                }
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.closeAll('loading'); //关闭加载层
                layer.msg("订单未正常创建 请在订单管理中核对", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                    //这里要考虑一下,是否自动刷新 当前页面
                });
            }

        });


        //return true;
    }


    //表单验证
    function from_validate() {
        $("#error").text("");

        var from_str = "";

        var m_length = 0;
        var m_value_str = "";
        var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
        var mobile_bool = true;
        if ($('#mobilephone').length > 0) {
            m_length = $('#mobilephone').val().length;
            m_value_str = $('#mobilephone').val();
            mobile_bool = mobile.test(m_value_str);
        }


        var idcard_length = 0;
        var idcard_value_str = "";
        var idcard_bool = true;
        if ($('#idcard').length > 0) {
            idcard_length = $('#idcard').val().length;
            idcard_value_str = $('#idcard').val();
            idcard_bool = isIdCardNo(idcard_value_str);
        }


        if ($('#realname').length > 0 && $('#realname').val() == '') {
            $("#error").text("必须输入姓名");
            return false;
        } else if (m_length > 0 && !mobile_bool) {
            $("#error").text("手机号码格式不对");
            return false;
        } else if ($('#idcard').val() == '') {
            $("#error").text("第身份证号必须填写");
            return false;
        } else if (idcard_length > 0 && !idcard_bool) {
            $("#error").text("身份证号码格式不对");
            return false;
        } else if (idcard_length > 0 && idcard_bool) {
            var result_bool = true;
            $.ajax({
                type: "post",
                url: "/lyapp/order/ztc_list_idcard_search.php?idcard=" + idcard_value_str,
                async: false,//这个执行完才执行下面的
                dataType: 'html',
                success: function (result) {
                    if (result == 0) {
                        result_bool = false;
                    }
                }
            });
            if (!result_bool) {
                $("#error").html("身份证号码已经购买过乘车卡");
                return false;
            }
        }

        if ($('#realname').length > 0){
            from_str += '&realname=' + encodeURIComponent($('#realname').val()) + '&mobilephone=' + encodeURIComponent($('#mobilephone').val()) + '&idcard=' + encodeURIComponent($('#idcard').val())+ '&litpicsrc=' + $('#litpicsrc').html();
        }
        return from_str;
    }


</script>

</body>
</html>