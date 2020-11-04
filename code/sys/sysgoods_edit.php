<?php
/**
 * 微信参数编辑
 *
 * @version        $Id: sysGroup_edit.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */

require_once("../config.php");
require_once("goods.class.php");

if (empty($dopost)) $dopost = '';

/*--------------------------------
 function __save(){  }
 -------------------------------*/


if ($dopost == 'save') {

    if ($name == "") {
        ShowMsg("请输入标题", "-1");
        exit();
    }
    if ($urladd == "") {
        ShowMsg("请选择功能", "-1");
        exit();
    }
    if (empty($body)) $body = '';

    $urladd_arry=explode("/",$urladd);
    $dir=$urladd_arry[0];

    $updateDate = time();

    $inQuery = "
        UPDATE `#@__sys_goods` SET
        `dir`='$dir',
         `urladd`='$urladd',
          `flag`='$flag',
          `salePrice`='$salePrice',
          `nowPrice`='$nowPrice',
          `unit`='$unit',
          `isbasic`='$isbasic',
          `body`='$body',
          `name`='$name',
            `updateDate`='$updateDate'
            WHERE (`id`='$id')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {
    //读取归档信息
    $arcQuery = "SELECT *  FROM #@__sys_goods  WHERE id='$id' ";
    //dump($arcQuery);
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
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>
                </div>
                <div class="ibox-content">
                    <form id="form2" name="form2" action='' method='post' class="form-horizontal">
                        <input type='hidden' name='dopost' value='save'>
                        <input name="id" type="hidden" id="id" value="<?php echo $arcRow['id'] ?>">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">名称:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="name" id="name"  value="<?php echo $arcRow['name'] ?>">
                            </div>
                        </div>


                        <?php

                        $goods = new goods();
                        $optionarr = $goods->getDirFileOption($arcRow['urladd']);  //供栏目选择

                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择功能:</label>

                            <div class="col-sm-2">
                                <?php
                                //echo "<select name='urladd' class='form-control m-b'  data-toggle='tooltip' data-placement='top' title='添加顶级功能' >\r\n";
                                //提示功能暂时不用，后期要考虑，下面的大段文字 ，怎么整合到提示弹框内 160416
                                echo "<select name='urladd' id='urladd' class='form-control m-b'    >\r\n";
                                echo "<option value='' selected>请选择功能...</option>\r\n";
                                echo $optionarr;
                                echo "</select>";
                                ?>
                            </div>
                            <div class="col-sm-6 form-control-static">
                                <strong>灰色背景</strong>代表功能父分类或已经添加到系统中的功能，不可选择！
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">功能标志:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="flag" id="flag" autocomplete="off"   value="<?php echo $arcRow['flag'] ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">
                                多个标志，请使用逗号分隔
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">销售价格:</label>

                            <div class="col-sm-2">
                                <input type="number" class="form-control" name="salePrice"  id="salePrice"  value="<?php echo $arcRow['salePrice'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">优惠价格:</label>

                            <div class="col-sm-2 ">
                                <input type="number" class="form-control" name="nowPrice"   value="<?php echo $arcRow['nowPrice'] ?>" id="nowPrice">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">计费单位：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='unit' value='日' <?php if($arcRow["unit"]=='日')echo " checked='1'";?>/>日
                                </label>
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='unit' value='月' <?php if($arcRow["unit"]=='月')echo " checked='1'";?>/>月
                                </label>
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='unit' value='年' <?php if($arcRow["unit"]=='年')echo " checked='1'";?>/>年
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">是否系统基础功能：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='isbasic' value='是' <?php if($arcRow["isbasic"]=='是')echo " checked='1'";?>/>是
                                </label>
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='isbasic' value='否' <?php if($arcRow["isbasic"]=='否')echo " checked='1'";?>/>否
                                </label>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">介绍内容：</label>

                            <div class="col-sm-10">
                                <?php echo GetEditor("body", $arcRow["body"]); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>


<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->

<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    });
    $().ready(function () {
        $("#form2").validate({
            rules: {
                name: {required: !0},
                urladd: {required: !0, isIntNotZero: !0},
                salePrice: {required: !0, number: !0},
                nowPrice: {required: !0, number: !0}
            },
            messages: {
                name: {required: "请填写标题"},
                urladd: {required: "请选择功能", isIntNotZero: "请选择白色条目(不要选择灰色)"},
                salePrice: {required: "请填写数字", number: "必须为数字"},
                nowPrice: {required: "请填写数字", number: "必须为数字"}
            }
        });
        $("#urladd").change(function () {
            if ($("#urladd").val() != "" && $("#urladd").val() != 0)$("#name").val($.trim($("#urladd option:selected").text()));
        });

    });
</script>

</body>
</html>