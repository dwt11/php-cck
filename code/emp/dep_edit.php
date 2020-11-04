<?php
/**
 * 部门编辑
 *
 * @version        $Id: dep_edit.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
$dep_id = isset($dep_id) ? intval($dep_id) : 0;


/*-----------------------
function action_save()
----------------------*/
if ($dopost == "save") {
    $dep_info = Html2Text($dep_info, 1);
    //同级下部门名称是否重复
    $questr = "SELECT dep_name FROM `#@__emp_dep` WHERE dep_reid='$dep_id' and  dep_name ='$dep_name' and  dep_id!='$dep_id' ";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        ShowMsg("填写的部门名称已经存在,请检查！", "-1");
        exit();
    }


    $upquery = "UPDATE `#@__emp_dep` SET
     dep_name='$dep_name',
     dep_info='$dep_info'    WHERE dep_id='$dep_id' ";

    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存当前部门更改时失败，请检查你的填写资料是否存在问题！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    ShowMsg("成功更改！", $$ENV_GOBACK_URL);
    exit();
}

//读取部门信息
$dsql->SetQuery("SELECT * FROM `#@__emp_dep`  WHERE dep_id=$dep_id");
////dump("SELECT * FROM `#@__em_dep`  WHERE dep_id=$dep_id");
$myrow = $dsql->GetOne();
$topid = $myrow['dep_reid'];

//PutCookie('lastCid',GetTopid($id),3600*24,"/");
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
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <input type="hidden" name="topid" value="<?php echo $myrow['topid']; ?>"/>

        <div class="form-group">
            <label class="col-sm-2 control-label">部门名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="dep_name" value="<?php echo $myrow['dep_name'] ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">部门描述:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="dep_info" value="<?php echo $myrow['dep_info'] ?>">
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