<?php
/**
 * 部门添加
 *
 * @version        $Id: dep_add.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
$dep_id = isset($dep_id) ? intval($dep_id) : 0;
if ($dep_id == 0) {
} else {
    $checkID = empty($dep_id) ? $reid : $dep_id;
}

/**递归获取上级部门id
 *
 * @param $id
 * @param $stepTotal
 */
function logic_getTopDepId11($id)
{
    global $dsql;
    global $sunDepIdArray;
    $sql = "SELECT dep_reid FROM `#@__emp_dep` WHERE dep_id=$id";
    $dsql->SetQuery($sql);
    $dsql->Execute("gs" . $id);
    while ($row = $dsql->GetObject("gs" . $id)) {
        //dump($stepTotal);
        $nid = $row->dep_reid;
        if ($nid != 0) {
            return logic_getTopDepId11($nid);
        } else {
            return $id;
        }
    }
}

/*---------------------
function action_save(){ }
---------------------*/
if ($dopost == "save") {
    $dep_info = Html2Text($dep_info, 1);
    //获取最顶级的部门id
    $dep_topid = $dep_id;
    $questr1 = "SELECT dep_reid FROM `#@__emp_dep` WHERE dep_id=('$dep_id')";
    $rowarc1 = $dsql->GetOne($questr1);
    if (!is_array($rowarc1)) {
        $str = "";
    } else {
        if ($rowarc1['dep_reid'] != 0) {
            $dep_topid = logic_getTopDepId11($rowarc1['dep_reid']);
        }
    }

    //同级下部门名称是否重复
    $questr = "SELECT dep_name FROM `#@__emp_dep` WHERE dep_topid='$dep_topid' and  dep_reid='$dep_id' and  dep_name ='$dep_name'";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        ShowMsg("填写的部门名称已经存在,请检查！", "-1");
        exit();
    }

    if ($dep_id > 0) {//添加子部门
        $in_query = "INSERT INTO `#@__emp_dep`(dep_name,dep_info,dep_reid,dep_topid) VALUES('$dep_name','$dep_info','$dep_id','$dep_topid')";
        if (!$dsql->ExecuteNoneQuery($in_query)) {
            ShowMsg("保存数据时失败，请检查你的填写资料是否存在问题！", "-1");
            exit();
        }
    } else {
        //添加顶级 部门

        $in_query = "INSERT INTO `#@__emp_dep`(dep_name,dep_info,dep_reid,dep_topid) VALUES('$dep_name','$dep_info','0','0')";
        if (!$dsql->ExecuteNoneQuery($in_query)) {
            ShowMsg("保存数据时失败，请检查你的填写资料是否存在问题！", "-1");
            exit();
        }
        $newId=$dsql->GetLastID();
        $in_query = "update  `#@__emp_dep` set dep_topid='$newId' where dep_id='$newId'";
        $dsql->ExecuteNoneQuery($in_query);

    }


    ShowMsg("成功创建一个部门！", "dep.php");
    exit();

}//End dopost==save

//获取从父目录继承的默认参数
if ($dopost == '') {
    $topid = 0;
    if ($dep_id > 0) {
        $myrow = $dsql->GetOne(" SELECT * FROM `#@__emp_dep` WHERE dep_id=$dep_id ");
        $dep_reid = $myrow['dep_reid'];
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>
<body class="gray-bg">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="dep_add.php" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>
        <input type='hidden' name='dep_id' id='dep_id' value='<?php echo $dep_id; ?>'/>

        <div class="form-group">
            <label class="col-sm-2 control-label">部门名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="dep_name" id="dep_name">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">部门描述:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="dep_info">
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
            rules: {dep_name: {required: !0}},
            messages: {dep_name: {required: "请填写部门名称"}}
        })
    });
</script>

</body>
</html>