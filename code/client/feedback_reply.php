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


    //更新
    $completetime = time();
    $sql = "UPDATE `#@__feedback` SET `completeTime`='$completetime', `completeBody`='$completebody' WHERE id='$id' ";
//dump($sql);
    if (!$dsql->ExecuteNoneQuery($sql)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    ShowMsg("反馈信息成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == '') {

    //require_once(DWTPATH . "/emp/worktype.inc.options.php");

    //读取 信息
    $query = "SELECT *  FROM #@__feedback  WHERE id='$id' ";
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
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" name="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="dopost" value="save">

<!--        <div class="form-group">
            <label class="col-sm-2 control-label">功能名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="filename11" id="filename11" value="<?php /*echo $row["filename"]; */?>">
            </div>
        </div>
-->
        <div class="form-group">
            <label class="col-sm-2 control-label">处理说明:</label>

            <div class="col-sm-2">
                <textarea name="completebody" id="completebody" class="form-control" placeholder="请填写针对建议进行的处理内容" rows="5"><?php echo $row["completeBody"]; ?></textarea>
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
            rules: {
                filename11: {required: !0}, completebody: {required: !0}
            },
            messages: {
                filename11: {required: "请填写功能名称"}, completebody: {required: "请填写处理说明"}
            }
        })
    });


</script>

</body>
</html>



