<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: channel_edit.php 1 14:49 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");

if (empty($dopost)) $dopost = "";
$id = isset($id) && is_numeric($id) ? $id : 0;


/*------------
function __SaveEdit()
------------*/
if ($dopost == "save") {
    $fieldset = preg_replace("#[\r\n]{1,}#", "\r\n", $fieldset);
    $query = "Update `#@__sys_channeltype` set    fieldset = '$fieldset'    where id='$id' ";
    if (trim($fieldset) != '') {
        $dtp = new DwtTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource(stripslashes($fieldset));
        if (!is_array($dtp->CTags)) {
            ShowMsg("文本配置参数无效，无法进行解析！", "-1");
            exit();
        }
    }
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功更改一个模型的字段！", "field.php?id=$id ");
    exit();
}


$row = $dsql->GetOne("SELECT typename,fieldset,addtable FROM `#@__sys_channeltype` WHERE id='$id' ");
$fieldset = $row['fieldset'];
$addtable = $row['addtable'];

$dtp = new DwtTagParse();
$dtp->SetNameSpace("field", "<", ">");
$dtp->LoadSource($fieldset);
$datanumb=0;
if(is_array($dtp->CTags))$datanumb   = count($dtp->CTags);//数据中保存的字段信息 个数


$iserror = false;
$qsql = "select count(COLUMN_NAME) as dd from information_schema.COLUMNS where table_name = '$addtable' and  TABLE_SCHEMA='$cfg_dbname';";
$row1 = $dsql->GetOne($qsql);
//dump($qsql);
$table_col_num = $row1['dd'] - 1;//减去gid aid这些主键
if ($table_col_num != $datanumb) $iserror = true;

//dump($table_col_num);
//dump($datanumb);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
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
                    <h5><?php echo $sysFunTitle ?></h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <div class="btn-group" id="Toolbar">
                        <a href="field_add.php?id=<?php echo $id; ?>" class="btn btn-outline btn-default" data-toggle='tooltip' data-placement='top' title='添加字段'><i class='glyphicon glyphicon-plus' aria-hidden='true'></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="datalist" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-sort-order="desc" data-mobile-responsive="true">
                            <thead>
                            <tr>
                                <th class="text-center">提示文字</th>
                                <th class="text-center">字段名</th>
                                <th class="text-center">数据类型</th>
                                <th class="text-center">搜索表单</th>
                                <th class="text-center">默认值</th>
                                <th class="text-center"></th>
                            </tr>
                            </thead>
                            <tbody>


                            <?php

                            $fieldset = $row['fieldset'];
                            $dtp = new DwtTagParse();
                            $dtp->SetNameSpace("field", "<", ">");
                            $dtp->LoadSource($fieldset);
                            if (is_array($dtp->CTags)) {
                                foreach ($dtp->CTags as $ctag) {
                                    ?>
                                    <tr>
                                        <td><?php
                                            $itname = $ctag->GetAtt('itemname');
                                            if ($itname == '') echo "没指定";
                                            else echo $itname;
                                            ?></td>
                                        <td><?php echo $ctag->GetTagName(); ?></td>
                                        <td><?php
                                            $ft = $ctag->GetAtt('type');


                                            //字段类型信息
                                            require_once("fieldsType.inc.php");
                                            $ds = $fieldsTypeArray;
                                            // dump($ds[$ft]);
                                            $fieldtypename = $ds[$ft];
                                            if ($fieldtypename == "") $fieldtypename = "未知";
                                            echo $fieldtypename;


                                            ?></td>
                                        <td><?php
                                            $ft = $ctag->GetAtt('issearch');
                                            if ($ft == '' || $ft == 0) {
                                                echo "否";
                                            } else {
                                                echo "是";
                                            }
                                            ?></td>
                                        <td><?php echo $ctag->GetAtt('default'); ?></td>
                                        <td>
                                            <a href='field_edit.php?id=<?php echo $id; ?>&fieldname=<?php echo $ctag->GetTagName(); ?>'>编辑</a>
                                            <a href="field_del.php?fname=<?php echo $ctag->GetTagName(); ?>&id=<?php echo $id; ?>">删除</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>


                    <br>
                    <div class="table-responsive">
                        <form name="form1" action="field.php" method="post" role="form">
                            <input type='hidden' name='id' value='<?php echo $id ?>'>
                            <input type='hidden' name='dopost' value='save'>
                            <!--选项-->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        <?php
                                        if ($iserror) echo "<div class='text-warning'>字段信息与数据表实体中的字段个数不对应，请核对。</div>" ?>
                                        字段配置(文本模式)， 修改配置文本可调整字段顺序，但不会更改字段属性！
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <textarea name="fieldset" rows="10" id="fieldset" class="form-control"><?php echo $fieldset; ?></textarea>
                                        <br>
                                        <button type="submit" class="btn btn-primary">保存</button>
                                    </div>
                                </div>
                            </div>
                            <!--选项-->
                        </form>
                    </div>
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

<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
</body>
</html>