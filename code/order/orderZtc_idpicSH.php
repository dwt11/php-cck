<?php
/**
 * 添加系统管理员
 *
 * @version        $Id: sys_user_add.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (!isset($ztc_list_id)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}

$id=$ztc_list_id;
if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    if (empty($idpic_desc)) $idpic_desc = '';
    $idpic_desc=str_replace("审核不通过","",$idpic_desc);
    $idpic_desc = "审核通过 " . $idpic_desc;




//更新乘车卡的照片
    $query = "UPDATE #@__order_addon_ztc SET idpic_desc='$idpic_desc'    WHERE id='$id'; ";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据表时出错，请检查', $$ENV_GOBACK_URL);
        exit();
    }


    ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();
}
if ($dopost == 'nosh') {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    if (empty($idpic_desc)) $idpic_desc = '';
    $idpic_desc=str_replace("审核通过","",$idpic_desc);
    $idpic_desc = "审核不通过 " . $idpic_desc;

    //更新乘车卡的照片
    $query = "UPDATE #@__order_addon_ztc SET idpic='', idpic_desc='$idpic_desc'    WHERE id='$id'; ";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据表时出错，请检查', $$ENV_GOBACK_URL);
        exit();
    }


    ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();
}
//读取归档信息
$arcQuery = "SELECT idpic,idpic_desc  FROM #@__order_addon_ztc  WHERE id='$id'  ";

$arcRow = $dsql->GetOne($arcQuery);
$photo = "无";
if (!is_array($arcRow)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
} else {
    $photo = $arcRow["idpic"];
    $idpic_desc = $arcRow["idpic_desc"];

    if ($photo != "") $photo = "<a href='$photo' target='_blank'> <img src='$photo' width='80' height='80'/></a>";
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
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg" style="min-width: 300px">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" enctype="multipart/form-data" target="_parent">
        <input type="hidden" name="dopost" value="save">

        <div class="form-group">

            <div class="col-sm-12">
                <?php echo $photo; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">操作原因:</label>

            <div class="col-sm-8">
                <textarea name="idpic_desc" id="idpic_desc" placeholder="将显示给用户看" maxlength="255" rows="5"><?php echo $idpic_desc ?></textarea>

            </div>
        </div>


        <div class="hr-line-dashed"></div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 text-center">


                <button class="btn btn-primary" type="submit">审核通过</button>
                <br> 通过,保留用户上传的照片
                <br><br></div>
            <div class="col-sm-4 col-sm-offset-2 text-center">
                <script>
                    function cc(id,ztc_list_id) {
                        var desc = $("#idpic_desc").val();
                        parent.location.href = 'orderZtc_idpicSH.php?id=' + id + '&ztc_list_id='+ztc_list_id+'&dopost=nosh&idpic_desc=' + desc;
                    }
                </script>

                <button class="btn btn-warning" type="button" onclick="cc(<?php echo $id ?>,<?php echo $ztc_list_id ?>)">审核不通过清空照片</button>
                <br>不通过,清空用户照片
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>

<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>





