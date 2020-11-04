<?php
/**
 *  编辑
 *
 * @version        $Id:  _edit.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {

    if (empty($icon)) $icon = '';

    //查询功能是否重名
    if ($oldtitle != $title) {
        $wheresql = " 1=1 ";
        if ($topid == 0) {
            $wheresql .= " and f.topid=0";
        } else {
            $wheresql .= " and f.topid={$topid}";
        }
        $query = " SELECT f.title,f.disorder FROM `#@__sys_function` f  where  $wheresql and title='$title' and depid='$depid'     ORDER BY   disorder ASC";
        $row = $dsql->GetOne($query);
        if (is_array($row)) {
            ShowMsg("已经存在相同名称的功能,请修改！", "-1");
            exit();
        }
    }


    /*//160606删除此段，功能菜单和权限分开
     * if ($depids != "" && $GLOBAMOREDEP) {
        //将当前功能ID，保存到所稳定的部门的emp_plus中
        foreach ($depid_array as $depid) {
            //如果没有当前部门的功能设定ID,则创建
            $row = $dsql->GetOne("SELECT * FROM #@__emp_dep_plus WHERE depid='$depid'");
            if (!is_array($row)) {
                $query = "INSERT INTO `#@__emp_dep_plus` (`depid`, `functionids`) VALUES ('$depid', '$id')";
                // dump($query);
                $dsql->ExecuteNoneQuery($query);
            } else {
                $functionids = "";
                //如果当前功能ID,不在所选部门中,则更新
                $query = " SELECT functionids FROM `#@__emp_dep_plus` f  where depid='$depid' and FIND_IN_SET('" . $id . "',functionids) ";
                $row = $dsql->GetOne($query);
                if (!is_array($row)) {
                    $query = "UPDATE `#@__emp_dep_plus` SET functionids=if(ISNULL(functionids),$id,CONCAT(functionids,',','$id')) where depid='$depid'";
                    $dsql->ExecuteNoneQuery($query);
                }
            }
        }
    }

    //160421在emp_emp_plus中搜索 当前功能ID,并且功能不在上面$depids中的设定,将当前功能ID删除后更新
    if ($GLOBAMOREDEP) { //将权限值从dep_plus中删除
        $questr = "SELECT depid FROM `#@__emp_dep_plus` WHERE FIND_IN_SET('" . $id . "',functionids) ";
        //dump($questr);
        $dsql->Execute('n', $questr);
        while ($rowarc = $dsql->GetArray('n')) {
            $depid = $rowarc["depid"];
            if (!in_array($depid, $depid_array)) {
                $questr = "SELECT functionids FROM `#@__emp_dep_plus` where  `depid` = '$depid' ";
                $rowarc = $dsql->GetOne($questr);
                $functionid_array = explode(",", $rowarc["functionids"]);
                //将删除的RANK从DEP_PLUS中移除
                if (array_search($id, $functionid_array) !== false) {
                    //dump($functionid_array);
                    unset($functionid_array[array_search($id, $functionid_array)]);//如果替换掉里面的 其他-1
                    $functionids = join(',', $functionid_array);
                    $dsql->ExecuteNoneQuery("UPDATE `#@__emp_dep_plus` SET functionids='$functionids' WHERE depid='$depid'");
                }
            }
        }
    }*/


    //更新
    if (empty($urladd)) $urladd = '';
    if (empty($groups)) $groups = '';
    if ($groups == '默认功能') $groups = '';
    //if($title==''){ ShowMsg("显示标题不能为空！", "-1");exit();}
    //if($urladd==''){ ShowMsg("功能地址不能为空！", "-1");exit();}
    $sql = "UPDATE `#@__sys_function` SET  `urladd`='$urladd', `groups`='$groups',`disorder`='$disorder',`title`='$title',  `remark`='$remark',  `iconName`='$icon' WHERE id='$id' ";

    if (!$dsql->ExecuteNoneQuery($sql)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    //dump($$ENV_GOBACK_URL);
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == '') {

    //require_once(DWTPATH . "/emp/worktype.inc.options.php");
    //读取 信息
    $query = "SELECT *  FROM #@__sys_function  WHERE id='$id' ";
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
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="sysFunction_edit.php" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="depid" value="<?php echo $row["depid"]; ?>">
        <input type="hidden" name="dopost" value="save">
        <?php
        //显示子部门的可以选项
        if ($row["topid"] > 0) {
            ?>
            <input type="hidden" name="topid" value="<?php echo $row["topid"]; ?>">
            <div class="form-group">
                <label class="col-sm-2 control-label">分组名称:</label>

                <div class="col-sm-2">
                    <input type="text" class="form-control" name="groups" value="<?php echo $row["groups"]; ?>">
                    <span class="help-block m-b-none">(留空，则不分组)</span>
                </div>
            </div>
        <? }

        if ($row["topid"] == 0){?>
        <div class="form-group">
            <label class="col-sm-2 control-label">图标:
                <?php
                $icon = $row["iconName"];
                if ($icon != "") echo "<i class='fa  fa-$icon'></i>" ?>
            </label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="icon" value="<?php echo $icon; ?>">
            </div>
        </div>
        <?php }?>
        <div class="form-group">
            <label class="col-sm-2 control-label">显示名称:</label>
            <div class="col-sm-2">
                <input type="hidden" name="oldtitle" value="<?php echo $row["title"]; ?>">
                <input type="text" class="form-control" name="title" value="<?php echo $row["title"]; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">排序:</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="disorder" value="<?php echo $row["disorder"]; ?>">
            </div>
        </div>
        <?php
        //显示子部门的可以选项
        if ($row["topid"] > 0) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">功能地址:</label>

                <div class="col-sm-2">
                    <input type="text" class="form-control" name="urladd" value="<?php echo $row["urladd"]; ?>">
                </div>
            </div>
        <?php } ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="remark" value="<?php echo $row["remark"]; ?>">
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
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        $("#form1").validate({
            rules: {title: {required: !0}},
            messages: {title: {required: "请填写显示名称"}}
        })
    });

</script>

</body>
</html>



