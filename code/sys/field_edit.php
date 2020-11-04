<?php
/**
 * 自定义模型,字段编辑
 *
 * @version        $Id: field_edit.php 1 15:22 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");
require_once("fieldsSqlCode.func.php");
if (empty($dopost)) $dopost = '';


$id = isset($id) && is_numeric($id) ? $id : 0;
/*$mysql_version = $dsql->GetVersion();*/


//模型信息,获取附加表的名称和一些参数
$row = $dsql->GetOne("SELECT fieldset,addtable FROM `#@__sys_channeltype` WHERE id='$id'");
$fieldset = $row['fieldset'];
$trueTable = $row['addtable'];
$dtp = new DwtTagParse();
$dtp->SetNameSpace("field", "<", ">");
$dtp->LoadSource($fieldset);
if (is_array($dtp->CTags)) {
    foreach ($dtp->CTags as $ctag) {
        if (strtolower($ctag->GetName()) == strtolower($fieldname)) break;
    }
} else {
    ShowMsg("数据获取出错", "-1");
    exit();
}
$itemname = $ctag->GetAtt('itemname');
$type = $ctag->GetAtt('type');
$default = $ctag->GetAtt('default');
$issearch = $ctag->GetAtt('issearch');

//保存更改
/*----------------------
function Save()
---------------------*/
if ($dopost == 'save') {
    if ($itemname_1 == "" || $fieldname_1 == "") {
        ShowMsg("字段名称或字段提示文字不能为空！", "-1");
        exit();
    }

//如果提示文字 修改了 ，判断字段名称是否重复
    if ($itemname_1 != $itemname) {
        foreach ($dtp->CTags as $ctag) {
            $itemname_t = $ctag->GetAtt('itemname');
            if ($itemname_1 == $itemname_t) {
                ShowMsg("字段的提示文字已经存在,请修改！", "-1");
                exit();
            }
        }
    }

//如果字段名称修改了 ，判断字段名称是否重复
    if ($fieldname_1 != $fieldname) {
        foreach ($dtp->CTags as $ctag) {
            $fieldname_t = $ctag->GetTagName();
            if ($fieldname_1 == $fieldname_t) {
                ShowMsg("字段名称已经存在,请修改！", "-1");
                exit();
            }
        }
    }


    $default_1 = trim($default_1);
    if (preg_match("#^(select|radio|checkbox)$#i", $dtype)) {
        if (!preg_match("#,#", $default_1)) {
            ShowMsg("你设定了字段为 {$dtype} 类型，必须在默认值中指定元素列表，如：'a,b,c' ", "-1");
            exit();
        }
    }

    if (preg_match("#^(stepselect|stepradio|stepcheckbox)$#i", $dtype)) {
        //查询传过来的 数据字典名称是否存在
        $arr = $dsql->GetOne("SELECT * FROM `#@__sys_stepselect` WHERE egroup='$fieldname_1' ");
        if (!is_array($arr)) {
            //不存在 则提示
            ShowMsg("你设定了字段为数据字典类型，但系统中没找到与你定义的字段名相同的组名!", "-1");
            exit();
        }
    }


//如果字段名称 或  字段类型有改变
    if ($fieldname_1 != $fieldname || $dtype != $type||$default_1!=$default) {
        //获取 新的字段的 SQL语句 和类型
        $fieldinfos = GetFieldMake($dtype, $fieldname_1, $default_1);
        $ntabsql = $fieldinfos[0];//创建用的SQL语言
        $buideType = $fieldinfos[1];//新的字段类型


        //如果旧的字段类型和新的字段类型不一样 在附加表中修改字段类型
        $tabsql = "ALTER TABLE `$trueTable` CHANGE `$fieldname` " . $ntabsql;
        //dump($tabsql);
        $rs = $dsql->ExecuteNoneQuery($tabsql);
        if (!$rs) {
            $gerr = $dsql->GetError();
            ShowMsg("修改字段失败，错误提示为：" . $gerr, "javascript:;");
            exit();
        }
    }


//int和float用户输入的是数字 才起作用，否则为0。
    if (preg_match("#^(int|float)$#i", $dtype)) {
        isset($default_1) && is_numeric($default_1) ? $default_1 : 0;
    }
    //数据字典的模认值为空
    if (preg_match("#^(stepselect|stepradio|stepcheckbox)$#i", $dtype)) {
        $default_1 = "";
    }
    //新的字段数据，用于保存到模型数据表中
    $fieldstring = "<field:$fieldname_1 itemname=\"$itemname_1\"  type=\"$dtype\"  issearch=\"$issearch_1\" default=\"$default_1\"> </field:$fieldname_1>\r\n";

    //检测模型的字段旧配置信息，并替换为新配置
    $ok = FALSE;
    $fieldname_1 = strtolower($fieldname_1);
    foreach ($dtp->CTags as $tagid => $ctag) {
        if ($fieldname == strtolower($ctag->GetName())) {
            //如果有同名的 就替换
            $dtp->Assign($tagid, stripslashes($fieldstring), FALSE);
            $ok = true;
            break;
        }
    }
    $oksetting = $dtp->GetResultNP();
    $oksetting = addslashes($oksetting);


    $sql = "UPDATE `#@__sys_channeltype` SET fieldset='$oksetting' WHERE id='$id' ";
    $rs = $dsql->ExecuteNoneQuery($sql);
    if (!$rs) {
        $grr = $dsql->GetError();
        ShowMsg("保存模型的字段信息配置出错！" . $grr, "javascript:;");
        exit();
    }


    ShowMsg("成功更改一个字段！", "field.php?id={$id}&dopost=edit");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">

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
                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>

                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                        <input type='hidden' name='id' value='<?php echo $id ?>'>
                        <input type="hidden" name="dopost" value="save"/>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">表单提示文字:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="itemname_1" name="itemname_1" value="<?php echo $itemname ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">（发布内容时显示的提示文字）</div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">字段名称:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="fieldname_1" name="fieldname_1" value="<?php echo $fieldname ?>"/>
                            </div>
                            <div class="col-sm-6 form-control-static">只能用英文字母或数字，数据表的真实字段名，如果数据类型是数据字典类型，该项应该填写数据字典的<a href='<?php echo $GLOBALS['cfg_install_path'] ?>/sys/sys_stepselect.php' target='_blank'><u>[英文组名称]</u></a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">数据类型:</label>
                            <div class="col-sm-8">
                                <?php

                                //字段类型信息
                                require_once( "fieldsType.inc.php");
                                $ds = $fieldsTypeArray;
                                //dump($ds);
                                $dtype_1_i = 0;
                                foreach ($ds as $dtype_1 => $dtypename) {
                                    $dtype_1_i++;
                                    $checked = "";

                                    if ($type == "" && $dtype_1_i == 1) {
                                        $checked = "  checked ";
                                    } elseif ($dtype_1 == $type) {
                                        $checked = "  checked ";
                                    }

                                    echo "<label class=\"checkbox-inline i-checks col-sm-2 \">
                                                <input type='radio'   name='dtype' id='dtype$dtype_1_i' value=\"$dtype_1\"  $checked>
                                                $dtypename
                                            </label>";
                                }
                                ?>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">搜索表单:</label>
                            <div class="col-sm-8">
                                <label class="checkbox-inline i-checks col-sm-2 ">
                                    <input type='radio'   name='issearch_1' id='issearch_1' value="0" <?php if($issearch==0) echo "checked"?>    >不可搜索
                                </label>

                                <label class="checkbox-inline i-checks col-sm-2 ">
                                    <input type='radio'   name='issearch_1' id='issearch_1' value="1"  <?php if($issearch==1) echo "checked"?>   >在列表可搜索此字段内容
                                </label>

                            </div>

                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">默认值:</label>
                            <div class="col-sm-2">
                                <textarea name="default_1" type="text" id="default_1" class="form-control"><?php echo $default; ?></textarea>
                            </div>
                            <div class="col-sm-6 form-control-static">如果定义数据类型为select、radio、checkbox时，此处填写被选择的项目(用“,”分开，如“男,女,人妖”)。</div>
                        </div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">保存内容</button>
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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/toastr/toastr.min.js"></script>
<link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        $(".i-checks").iCheck({
            checkboxClass: "icheckbox_square-green",
            radioClass: "iradio_square-green",
        })
        $("#form1").validate({
            rules: {
                itemname_1: {required: !0},
                fieldname_1: {required: !0}
            },
            messages: {
                itemname_1: {required: "请填写提示文字"},
                fieldname_1: {required: "请填写表单名称"}
            }
        })
    });


</script>
</body>
</html>