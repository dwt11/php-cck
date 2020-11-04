<?php
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
if (empty($dopost)) $dopost = '';

//如果是多个ID,跳到多个编辑页面
$lineids = $id;
$lineid_array = explode(",", $lineids);
if (count($lineid_array) > 1) {
    header("location:line.edit.more.php?id=$id");
}


if ($dopost == 'save') {

    $gotime = isset($gotime) ? trim($gotime) : $gotime = "";
    $backtime = isset($backtime) ? trim($backtime) : $backtime = "";
    $seats = isset($seats) ? trim($seats) : $seats = 0;

    if ($goodsid == 0 || $gotime == "" || $backtime == "") {
        ShowMsg("线路名称 出发时间  返回时间 不能为空 请检查！", "-1");
        exit;
    }

    $gotime = GetMkTime($gotime);
    $backtime = "2016-10-29 " . $backtime;
    $backtime = GetMkTime($backtime);//这个只用时间，日期模拟的
    $diaodudianhua_str = "";
    if (is_array($diaodudianhua)) {
        $diaodudianhua_str = join(',', $diaodudianhua);
    }
    // `carinfo_desc`='$carinfo_desc' ,
    $inQuery = "UPDATE `#@__line` SET 
                `goodsid`='$goodsid',
                `gotime`='$gotime',
                `backtime`='$backtime',
                `seats`='$seats',
                `tmp`='临时',
               `carinfo_desc`='$carinfo_desc', 
                `islock`='$islock',
                `diaodudianhua`='$diaodudianhua_str',
                `beforHours`='$beforHours',
                `linedaynumb`='$linedaynumb'
                where id=$id
                ";

    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("更新信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {

    //读取归档信息
    $arcQuery = "SELECT *  FROM #@__line  WHERE id='$id' ";

    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
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
    <title>更改线路信息</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/chosen/chosen.css" rel="stylesheet">
</head>

<body>
<div class="ibox-title">
    <h5>更改线路信息</h5>
</div>
<div class="ibox-content">
    <form id="zhishuadd" name="form1" action="" method="post" class="form-horizontal">

        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>

        <div class="form-group">
            <label class="col-sm-2 control-label">选择商品:</label>

            <div class="col-sm-2">
                <button type="button" class="btn btn-primary" onclick="selectGoods()">选择商品</button>
                <input name="goodsid" id="goodsid" type="hidden" value="<?php echo $arcRow['goodsid'] ?>">
            </div>
            <label class="col-sm-2 control-label">商品信息:</label>
            <div class="col-sm-4 form-control-static">
                <span id="goodsinfo"></span>
            </div>


        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">调度电话：</label>
            <div class="col-sm-10">
                <?php echo GetEnumsForm('diaodudianhua', $evalue = $arcRow['diaodudianhua'], $formid = 'diaodudianhua', $seltitle = '', $display = 'checkbox'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">出发时间:</label>
            <div class="col-sm-2 ">
                <?php $nowtime = date('Y-m-d H:i:s', $arcRow['gotime']); ?>
                <input type="text" name="gotime" id='gotime' class="form-control  Wdate" size="14" placeholder="" onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd H:mm:00',minDate:'%y-%M-#{%d+1} 00:00:00'})" value="<?php echo $nowtime; ?>"/>
            </div>


        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">行程天数:</label>
            <div class="col-sm-2 ">
                <div class="input-group">
                    <input type="number" name="linedaynumb" id='linedaynumb' class="form-control " value="<?php echo $arcRow['linedaynumb']; ?>" min="1"/>
                    <span class="input-group-addon"> 天 </span>
                </div>

            </div>
            <label class="col-sm-2 control-label">返回时间:</label>


            <div class="col-sm-2 ">
                <?php $nowtime = date('H:i:s', $arcRow['backtime']); ?>
                <input type="text" name="backtime" id='backtime' class="form-control  Wdate" placeholder="" onfocus="WdatePicker({skin:'whyGreen',dateFmt:'H:mm:ss'})" value="<?php echo $nowtime; ?>"/>
            </div>
            <div class="col-sm-4 form-control-static">
                系统根据出发日期和行程天数自动计算返回日期
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">座位数:</label>
            <div class=" col-sm-2">
                <div class="input-group">
                    <input type="number" class="form-control" name="seats" id="seats" value="<?php echo $arcRow['seats'] ?>">
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
                <input type="text" class="form-control" name="carinfo_desc" id="carinfo_desc" value="<?php echo $arcRow['carinfo_desc'] ?>">
            </div>
            <div class="col-sm-6 form-control-static">
                车牌号码或车辆的特殊说明；填写后，会在前台(出行日期选择处)显示；不填写不显示
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">预约截止时间(发车前):</label>
            <div class=" col-sm-2">
                <div class="input-group">
                    <input type="number" class="form-control" name="beforHours" id="beforHours" value="<?php echo $arcRow['beforHours'] ?>">
                    <span class="input-group-addon"> 小时 </span>
                </div>

            </div>
            <div class="col-sm-6 form-control-static">
                与线路团期的出发时间进行计算
            </div>
        </div>
        <!--   <div class="form-group">
                            <label class="col-sm-2 control-label">路线类型:</label>
                            <div class="input-group col-sm-2">
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" name="tmp" id="tmp" value="临时" <?php /*if ($arcRow['tmp'] == '临时') echo 'checked="checked"'; */ ?> > 临时
                                </label>
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" name="tmp" id="tmp" value="每日" <?php /*if ($arcRow['tmp'] == '每日') echo 'checked="checked"'; */ ?>> 每日
                                </label>
                            </div>
                        </div>-->

        <div class="form-group">
            <label class="col-sm-2 control-label">状态:</label>
            <div class="input-group col-sm-2">
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="islock" id="islock" value="1" <?php if (1 == $arcRow['islock']) echo 'checked="checked"'; ?> > 启用
                </label>
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="islock" id="islock" value="0" <?php if (0 == $arcRow['islock']) echo 'checked="checked"'; ?> > 停用
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
<script>

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
