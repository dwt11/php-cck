<?php
/**
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    if ($id == '') {
        ShowMsg("参数无效！", $$ENV_GOBACK_URL);
        exit();
    }
    $row = $dsql->GetOne("SELECT `jbnum` ,`clientid` FROM `#@__clientdata_extractionlog` WHERE id='$id'");
    $jbnum100 = $row['jbnum'];
    $clientid = $row['clientid'];

    //拒绝通过前 恢复扣除用户的金币
    $istrue = Update_jb($clientid, "$jbnum100", "提现审核不通过恢复金币", 0, $CUSERLOGIN->userID);
    $passtime = time();
    $query = "UPDATE `#@__clientdata_extractionlog` SET status='2' ,`no`='$no',passtime='$passtime',operatorid='{$CUSERLOGIN->userID}' WHERE id='$id'";

    if ($dsql->ExecuteNoneQuery($query)) {

        ShowMsg("执行成功！", $$ENV_GOBACK_URL);
        exit();

    } else {
        ShowMsg("执行失败！", $$ENV_GOBACK_URL);
        exit();

    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>

        <div class="form-group text-center">
            <label for="" class="col-sm-2 control-label">原因</label>

            <div class="col-sm-2">
                <textarea name="no" maxlength="255" rows="5"></textarea>
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
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>