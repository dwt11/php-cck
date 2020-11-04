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



if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == 'save') {

    /*$lineids = $id;
    $lineid_array = explode(",", $lineids);

    foreach ($lineid_array as $id) {*/

        $inQuery = "UPDATE `#@__line` SET 
                `beforHours`='$beforHours'
                where id IN ($id)
                ";

        if (!$dsql->ExecuteNoneQuery($inQuery)) {
            ShowMsg("更新数据时出错，请检查原因！", "-1");
            exit();
        }
/*}*/
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("更新信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {

    $lineids = $id;
    $lineid_array = explode(",", $lineids);
    $lineid = $lineid_array[0];//默认取第一个,
    //读取归档信息
    $arcQuery = "SELECT beforHours  FROM #@__line  WHERE id='$lineid' ";

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
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>


        <div class="form-group">
            <label class="col-sm-2 control-label">预约截止时间(发车前):</label>
            <div class=" col-sm-1">
                <input type="number" class="form-control" name="beforHours" id="beforHours" value="<?php echo $arcRow['beforHours'] ?>">
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
