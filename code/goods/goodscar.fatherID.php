<?php
/**
 * 预约截止时间调整
 *
 * @version        $Id: .php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
if (empty($dopost)) $dopost = '';



if ($goodsid == '') {
    ShowMsg("参数无效");
    exit();
}

if ($dopost == 'save') {

    $inQuery = "UPDATE `#@__goods_addon_car` SET 
                `fatherNumberID`='$fatherNumberID'
                WHERE goodsid ='$goodsid'";

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
    $fatherNumberID=0;
    $arcQuery = "SELECT fatherNumberID  FROM #@__goods_addon_car  WHERE goodsid='$goodsid' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if (is_array($arcRow)) {
        $fatherNumberID=$arcRow["fatherNumberID"];
    }


    //获取上级商品ID为空,不是当前商品的列表
    $goodsOptions = "";
    $query3 = "
        SELECT #@__goods.id,#@__goods.goodsname FROM  #@__goods  
        INNER JOIN #@__goods_addon_car  ON #@__goods.id=#@__goods_addon_car.goodsid
        WHERE fatherNumberID='0' AND id!='$goodsid'
        ORDER BY convert(goodsname USING gbk) ASC  ";
    $dsql->SetQuery($query3);
    $dsql->Execute("999");
    while ($row1 = $dsql->GetArray("999")) {
        $goodsid_1 = $row1["id"];
        $name =  $row1["goodsname"];
        $selected = "";
        if ($fatherNumberID == $goodsid_1) $selected = " selected";
        $goodsOptions .= "<option value='$goodsid_1' $selected>$name</option>";
    }



}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">

    <form id="zhishuadd" name="form1" action="" method="post" class="form-horizontal" target="_parent">

        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="goodsid" value="<?php echo $goodsid; ?>"/>


        <div class="form-group">
            <label class="col-sm-2 control-label">上级车辆:</label>
            <div class=" col-sm-1">
                <?php

                echo "<select  class='form-control' name='fatherNumberID' id='fatherNumberID'  >\r\n";
                echo "<option value='0'>请选择商品...</option>\r\n";
                echo $goodsOptions;
                echo "</select>";
                ?>
            </div>

        </div>


        <div class="hr-line-dashed"></div>

        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>

    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/validate/messages_zh.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {


    });
</script>
</body>
</html>
