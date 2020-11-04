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
if (!isset($id)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}


if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    //如果用户没有保存过照片 则这里更新一下
    /* $questr = "SELECT c3.idpic,c3.clientid  FROM #@__order_addon_ztc c1
     LEFT JOIN #@__order c2 on c1.orderid=c2.id
     LEFT JOIN `#@__client_addon` c3 on c2.clientid=c3.clientid where  c1.id='$id'";
     $row = $dsql->GetOne($questr);
     // dump($questr);
     if ($row["idpic"] == "") {
         $clientid = $row["clientid"];
         $query = "UPDATE #@__client_addon SET   idpic='$idpic_1'      WHERE clientid='$clientid'; ";
         $dsql->ExecuteNoneQuery($query);
     }*/
    if (empty($idpic_desc)) $idpic_desc = '';



//更新乘车卡的照片
    $query = "UPDATE #@__order_addon_ztc SET idpic='$pic' ,idpic_desc='$idpic_desc'    WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据表时出错，请检查', $$ENV_GOBACK_URL);
        exit();
    }


    ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();
}
//读取归档信息
$arcQuery = "SELECT idpic  FROM #@__order_addon_ztc  WHERE id='$id'  ";

$arcRow = $dsql->GetOne($arcQuery);
$photo = "无";
if (!is_array($arcRow)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
} else {
    $photo = $arcRow["idpic"];

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
    <form name="form1" id="form1" action="" method="post" class="form-horizontal"   target="_parent">
        <input type="hidden" name="dopost" value="save">

        <div class="form-group">
            <label class="col-xs-2 control-label">照片:</label>

            <div class="col-xs-8">



                <?php
                $oldpic = "";
                if ($photo != "") $oldpic = $photo;
                $pater_input_name = "pic";//父页面input框名称,用于接受上传后的地址
                $fileSize = "3";//大小限制
                $fileType = "jpg";//大小限制
                $dirname_plus = "idpic";//文件要保存的目录,在uploads目录下

                $cs_str = "?oldpic={$oldpic}&pater_input_name={$pater_input_name}&fileSize={$fileSize}&dirname_plus={$dirname_plus}&fileType={$fileType}";
                //if ($backpic) $photo = "<A href=\"javascript:;\" onclick=\"showpic('{$backpic}')\" ><img src=\"{$backpic}\" width=\"50\" height=\"50\"/></A>";
                //echo $photo;
                ?>
                <input type="hidden" id="pic" name="pic" value="<?php echo $oldpic; ?>">
                <iframe class="" name="1111" width="200" height="170" src="../ui/js/webupload/upload.php<?php echo $cs_str ?>" scrolling="no" frameborder="0"></iframe>

            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-2 control-label">备注:</label>

            <div class="col-xs-8">
                <textarea name="idpic_desc"  class="form-control"  placeholder="备注" maxlength="255" rows="5" style="min-width: 200px"></textarea>

            </div>
        </div>


        <div class="hr-line-dashed"></div>
        <div class="form-group">
            <div class="col-xs-4 col-xs-offset-2 text-center">

                <button class="btn btn-primary" type="submit">保存内容</button>
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





