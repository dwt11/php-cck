<?php
/**
 * 添加
 *
 * @version
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("sysFunction.class.php");

if (empty($dopost)) $dopost = '';
$topid = isset($topid) ? intval($topid) : 0;
$wheresql = " 1=1 ";
if ($topid == 0) {
    $wheresql .= " and f.topid=0";
} else {
    $wheresql .= " and f.topid={$topid}";
}
//$depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];

//dump($dopost);
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {
    $wheresql .= " AND depid='$depid'";
    if (empty($icon)) $icon = '';

    if ($urladd == "0") {
        ShowMsg("所选功能不可用,请选择非灰色背景选项！", "-1");
        exit();
    }

//是否同名

    $query = " SELECT f.title,f.disorder FROM `#@__sys_function` f  where   $wheresql and title='$title'      ORDER BY   	disorder ASC";
    $row = $dsql->GetOne($query);
    if (is_array($row)) {
        ShowMsg("已经存在相同名称的功能,请修改！", "-1");
        exit();
    }


    //如果添加的功能为暂时功能 ，则地址采用 “父栏目ID_随机数”
    if ($urladd == "#") {
        $urladd = $topid . "_" . mt_rand(10000, 99999);
    }

    $in_query = "INSERT INTO `#@__sys_function` (`depid`,`topid`,`urladd`,  `groups` ,`title`, `disorder`, `remark`,iconName)	VALUES ('{$depid}','{$topid}','{$urladd}','',  '{$title}', '{$disorder}', '{$remark}', '{$icon}')";
    //dump($in_query);
    if (!$dsql->ExecuteNoneQuery($in_query)) {
        ShowMsg("保存数据时失败，请检查你的填写资料是否存在问题！", "-1");
        exit();
    }


    $newid = $dsql->GetLastID();
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功添加功能！", $$ENV_GOBACK_URL);
    exit();

}//End dopost==save

//if(!empty($dopost)&&$dopost=="form1")
//{
//获取排序
$query = " SELECT f.title,f.disorder FROM `#@__sys_function` f  where  $wheresql       ORDER BY   	disorder deSC";
//dump($query);
$row = $dsql->GetOne($query);
if (is_array($row)) {
    $disorder = $row["disorder"] + 1;
} else {
    $disorder = 1;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" action='' method='post' class="form-horizontal" target="_parent">

        <input type='hidden' name='dopost' value='save'/>
        <input type='hidden' name='topid' id='topid' value='<?php echo $topid; ?>'/>
        <div class="form-group">
            <label class="col-sm-2 control-label">公司名称:</label>

            <div class="col-sm-2">
                <?php
                if ($GLOBALS['CUSERLOGIN']->getUserType() != 10 && $GLOBAMOREDEP) {
                    echo "<input type='hidden' value='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "' id='depid' name='depid'> ";
                    echo "<div class='form-control-static'>" . GetDepsNameByDepId($GLOBALS['NOWLOGINUSERTOPDEPID']) . "</div>";
                } else {
                    $depOptions = GetDepOnlyTopOptionList();
                    echo "<select name='depid' id='depid'  class='form-control m-b'  >\r\n";
                    echo "<option value='0'>超级管理员菜单</option>\r\n";
                    echo $depOptions;
                    echo "</select>";
                }
                ?>
            </div>
        </div><?php
        if ($topid > 0) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">上级功能:</label>

                <div class="col-sm-2">
                    <input type="text" disabled class="form-control" value="<?php echo $parentTitle ?>">
                </div>
            </div>
        <? }

        if ($topid == 0) {
            ?>

            <div class="form-group">
                <label class="col-sm-2 control-label">图标:</label>

                <div class="col-sm-2">
                    <input type="text" class="form-control" name="icon" id="icon" autocomplete="off">
                </div>
            </div>
        <?php } ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">显示名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="title" id="title" autocomplete="off">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">排序:</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="disorder" value="<?php echo $disorder; ?>">
            </div>
        </div>
        <?php

        if ($topid > 0) {
            $depid = 0;
            //获取排序
            $query = " SELECT depid FROM `#@__sys_function` f  where id='$topid'       ORDER BY   	disorder deSC";
            $row = $dsql->GetOne($query);
            if ($row) {
                $depid = $row["depid"];
            }
            $fun = new sys_function();
            $optionarr = $fun->getDirFileOption($depid);  //供栏目选择
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">选择功能:</label>

                <div class="col-sm-2">
                    <?php
                    //echo "<select name='urladd' class='form-control m-b'  data-toggle='tooltip' data-placement='top' title='添加顶级功能' >\r\n";
                    //提示功能暂时不用，后期要考虑，下面的大段文字 ，怎么整合到提示弹框内 160416
                    echo "<select name='urladd' id='urladd' class='form-control m-b'    >\r\n";
                    echo "<option value='#' style=''  selected>暂无功能...</option>\r\n";
                    echo $optionarr;
                    echo "</select>";
                    ?>
                    <br><img src='../images/ico/help.gif'/> <strong>暂无功能</strong>代表临时功能，可随后在列表页面编辑实际对应的功能地址！
                    <br><img src='../images/ico/help.gif'/> <strong>灰色背景</strong>代表功能父分类或已经添加到系统中的功能，不可选择！
                    <br><img src='../images/ico/help.gif'/> <strong>白色背景</strong>代表功能子分类，可选择！
                    <br><img src='../images/ico/help.gif'/> <strong>黄色背景</strong>代表功能父功能所包含的子栏目分类，可选择！选择后默认在菜单中自动加载栏目的所有下级子栏目。
                </div>
            </div>


            <?php
        } else {
            echo "<input name=\"urladd\"  type=\"hidden\"   />";
        }
        ?>


        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="remark" id="remark" autocomplete="off">
            </div>
        </div>

        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        $("#form1").validate({
            rules: {
                title: {required: !0},
                urladd: {isIntNotZero: !0}
            },
            messages: {
                title: {required: "请填写显示名称"},
                urladd: {isIntNotZero: "请选择白色条目(不要选择灰色)"}
            }
        });
        $("#urladd").change(function () {
            if ($("#urladd").val() != "" && $("#urladd").val() != 0) $("#title").val($.trim($("#urladd option:selected").text()));
        });
    });
</script>
</body>
</html>
