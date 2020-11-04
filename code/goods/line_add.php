<?php
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单

if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {

    $startdate = isset($startdate) ? trim($startdate) : $startdate = "";
    $starttime = isset($starttime) ? trim($starttime) : $starttime = "";
    $backtime = isset($backtime) ? trim($backtime) : $backtime = "";
    $diaodudianhua = isset($diaodudianhua) ? trim($diaodudianhua) : $diaodudianhua = "";
    $seats = isset($seats) ? trim($seats) : $seats = 0;


    if ($goodsid == 0 || $startdate == "" || $starttime == "" || $backtime == "") {
        //ShowMsg("线路名称 出发时间  返回时间 不能为空 请检查！", "-1");
        exit;
    }

    $createtime = time();

    $backtime = "2016-10-29 " . $backtime;
    // dump($backtime);
    $backtime = GetMkTime($backtime);//这个只用时间，日期模拟的
    $diaodudianhua_str = "";
    if (is_array($diaodudianhua)) {
        $diaodudianhua_str = join(',', $diaodudianhua);
    }
    //dump($startdate);


    //分隔多选日期
    $startdate_array = explode(",", $startdate);
    if (is_array($startdate_array)) {
        foreach ($startdate_array as $datevalue) {

            $gotime = $datevalue . " " . $starttime;
            //组和日期和时间
            //dump($gotime);
            $gotime = GetMkTime($gotime);

            //大于当前日期的,才可以添加
            if ($gotime > time()) {
                $query = "INSERT INTO `#@__line` (`goodsid`,`gotime`,`backtime`, `seats`,`createtime`,`carinfo_desc`,`beforHours`,`islock`,`tmp`,`diaodudianhua`,`linedaynumb`)
                          VALUES ('$goodsid','$gotime','$backtime', '$seats','$createtime','$carinfo_desc','$beforHours','$islock','临时','$diaodudianhua_str','$linedaynumb')";
                //dump($query);
                $dsql->ExecuteNoneQuery($query);
                /*if (!$dsql->ExecuteNoneQuery($query)) {
                    ShowMsg("添加数据时出错，请检查原因！", "-1");
                    exit();
                }*/
            }
        }

    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功添加线路信息！", $$ENV_GOBACK_URL);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>添加线路</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../ui/css/plugins/kitjsdate/datepicker.css">


</head>

<body>
<div class="ibox-title">
    <h5>添加线路 </h5>
    <button type="button" class="btn btn-primary btn-xs" onclick="refresh()">刷新当前页面</button>
</div>
<div class="ibox-content">
    <div class="alert alert-warning alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        1、出发日期支持[鼠标划动]多选,[Ctrl+鼠标]单个多选,[Shift+鼠标]起始多选
        <br>2、如果出现单日无法选择,请刷新当前页面

    </div>

    <form id="zhishuadd" name="form1" action="" method="post" class="form-horizontal">

        <input type="hidden" name="dopost" value="save"/>


        <div class="form-group">
            <label class="col-sm-2 control-label">选择商品:</label>

            <div class="col-sm-2">
                <button type="button" class="btn btn-primary" onclick="selectGoods()">选择商品</button>
                <input name="goodsid" id="goodsid" type="hidden" value="">
            </div>
            <label class="col-sm-2 control-label">商品信息:</label>
            <div class="col-sm-4 form-control-static">
                <span id="goodsinfo"></span>
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">调度电话：</label>
            <div class="col-sm-10">
                <?php echo GetEnumsForm('diaodudianhua', $evalue = '', $formid = 'diaodudianhua', $seltitle = '', $display = 'checkbox'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">出发日期:</label>
            <div class="col-sm-2 ">
                <?php $nowdate = date('Y-m-d'); ?>
                <input type="text" id="startdate" name="startdate" class="form-control" value="<?php echo $nowdate ?>" style="*zoom:1;">

            </div>

            <label class="col-sm-2 control-label">出发时间:</label>
            <div class="col-sm-2 ">
                <?php $nowtime = '08:00:00'; ?>
                <input type="text" name="starttime" id='starttime' class="form-control  Wdate" size="6" placeholder="" onfocus="WdatePicker({skin:'whyGreen',dateFmt:'H:mm:ss'})" value="<?php echo $nowtime; ?>"/>
            </div>

        </div>
        <div class="form-group">


        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">行程天数:</label>
            <div class="col-sm-2 ">
                <div class="input-group">
                    <input type="number" name="linedaynumb" id='linedaynumb' class="form-control " value="1" min="1"/>
                    <span class="input-group-addon"> 天 </span>
                </div>

            </div>
            <label class="col-sm-2 control-label">返回时间:</label>
            <div class="col-sm-2 ">
                <?php $nowtime = '17:00:00'; ?>
                <input type="text" name="backtime" id='backtime' class="form-control  Wdate" size="6" placeholder="" onfocus="WdatePicker({skin:'whyGreen',dateFmt:'H:mm:ss'})" value="<?php echo $nowtime; ?>"/>
            </div>
            <div class="col-sm-4 form-control-static">
                系统根据出发日期和行程天数自动计算返回日期
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">座位数:</label>
            <div class=" col-sm-2">
                <div class="input-group">
                    <input type="number" class="form-control" name="seats" id="seats" value="0" min="0">
                    <span class="input-group-addon"> 个 </span>
                </div>
            </div>
            <div class="col-sm-8 form-control-static">
                不限人数请填0;限制人数请按实际数量填写
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">车型信息:</label>
            <div class=" col-sm-2">
                <input type="text" class="form-control" name="carinfo_desc" id="carinfo_desc" value="">
            </div>
            <div class="col-sm-6 form-control-static">
                车牌号码或车辆的特殊说明；填写后，会在前台(出行日期选择处)显示；不填写不显示
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">预约截止时间(出发前):</label>
            <div class=" col-sm-2">
                <div class="input-group">
                    <input type="number" class="form-control" name="beforHours" id="beforHours" value="0" min="0">
                    <span class="input-group-addon"> 小时 </span>
                </div>

            </div>
            <div class="col-sm-6 form-control-static">
                与线路团期的出发时间进行计算
            </div>
        </div>
        <!--<div class="form-group">
            <label class="col-sm-2 control-label">路线类型:</label>
            <div class="input-group col-sm-2">
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="tmp" id="tmp" value="临时" checked="checked"> 临时
                </label>
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="tmp" id="tmp" value="每日"> 每日
                </label>
            </div>
        </div>-->

        <div class="form-group">
            <label class="col-sm-2 control-label">状态:</label>
            <div class="input-group col-sm-2">
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="islock" id="islock" value="1" checked="checked"> 启用
                </label>
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="islock" id="islock" value="0"> 停用
                </label>

            </div>
        </div>


        <div class="clearfix" style="margin-bottom: 50px"></div>
        <div class="bodyButtomTab">
            <div class="col-sm-4 col-sm-offset-2 ">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>

    </form>

</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/validate/messages_zh.min.js"></script>
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/kitjsdate/kit.js"></script>
<script src="../ui/js/plugins/kitjsdate/array.js"></script>
<script src="../ui/js/plugins/kitjsdate/date.js"></script>
<script src="../ui/js/plugins/kitjsdate/dom.js"></script>
<script src="../ui/js/plugins/kitjsdate/selector.js"></script>
<!--widget-->
<script src="../ui/js/plugins/kitjsdate/datepicker.js"></script>
<script src="../ui/js/plugins/kitjsdate/datepicker-n-months.js"></script>

<script>


    //多选日期
    $kit.$(function () {
        //输入框点击后下拉显示
        $kit.ev({
            el: '#startdate',
            ev: 'focus',
            fn: function (e) {
                var d, ipt = e.target;
                d = e.target[$kit.ui.DatePicker.defaultConfig.kitWidgetName];
                if (d) {
                    d.show();
                } else {
                    d = new $kit.ui.DatePicker.NMonths({
                        // date: $kit.date.dateNow(),//初始日期
                        dateFormat: 'yyyy-mm-dd', //接受和输出的日期格式
                        weekViewFormat: 'daysMin',//daysMin是几,  daysShort周几 days星期几
                        nMonths: 2, //配置显示多少个月的日历
                        canMultipleChoose: true,//能否多选
                        dateStringSeparator: ',',//多选时候输出分隔符

                        //setStartDate:$kit.date.dateNow(),这个设定了不能用
                    });
                    d.init();
                    d.adhere($kit.el('#startdate'));
                    d.show();
                }
            }
        });
        //点击页面其他 地方隐藏
        $kit.ev({
            el: document,
            ev: 'click',
            fn: function (e) {
                var input = $kit.el('#startdate');
                d = input[$kit.ui.DatePicker.defaultConfig.kitWidgetName];
                if (d && !$kit.contains(d.picker, e.target) && input != e.target) {
                    d.hide();
                }
            }
        });
    })


    $(function () {
        var goodsid = "";
        intervalName = setInterval(handle, 1000);//定时器句柄
        function handle() {
            // IE浏览器此处判断没什么意义，但为了统一，且提取公共代码而这样处理。
            //如果值不一样,则代表了改变
            if ($("#goodsid").val() != goodsid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                goodsid = $("#goodsid").val();//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "goods.do.php",
                    data: {
                        goodsid: goodsid,
                        dopost: "GetOneGoodsInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        $("#goodsinfo").html(result.goodsname + " ￥" + result.price);
                    }
                });
            }
        }


    });

    function refresh() {
        location.reload();
    }

    function selectGoods() {
        layer.open({type: 2, title: '选择商品', content: '../goods/goods.select.php?typeid=2'});
    }

    $().ready(function () {
        $("#zhishuadd").validate({
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
