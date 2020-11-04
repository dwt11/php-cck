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

if (!isset($id)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}

/*$query = "SELECT clientid from  #@__client_depinfos       WHERE id='$id' ";
$row = $dsql->GetOne($query);
$clientid = $row['clientid'];*/

/*从数据库获取微信菜单*/
$display_array = array();
$query = "SELECT * FROM `#@__client_weixin` WHERE clientid=$id ";
$row = $dsql->GetOne($query);

$sql = "SELECT  openid        FROM #@__client_depinfos             WHERE #@__client_depinfos.clientid='$id'";
$row1 = $dsql->GetOne($sql);
$openid=$row1["openid"];

//($display_array);

/*
function getWeixinAppNames($AppID)
{
    global $dsql;
    $AppName = "";
    $sql = "SELECT depid,weixin_type FROM #@__interface_weixin    WHERE AppId='$AppID'";
    //dump($sql);
    $row = $dsql->GetOne($sql);
    if (is_array($row)) {
        $depName = GetDepsNameByDepId($row['depid']);
        $AppName = $depName . " " . $row['weixin_type'];
    }
    return $AppName;
}*/

?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">


</head>
<body class="gray-bg" style="min-width: 700px">


<!--表格数据区------------开始-->
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <div class="table-responsive">
        <table data-toggle="table" data-striped="true">
            <thead>
            <tr>
                <th data-halign="center" data-align="center">微信头像</th>
                <th data-halign="center" data-align="left">微信呢称</th>
                <th data-halign="center" data-align="center">性别</th>
                <th data-halign="center" data-align="left">城市</th>
            </tr>
            </thead>
                <tr>
                    <td>
                        <?php $photo = $row["photo"];
                        if ($photo == "") $photo = "/images/zw.jpg";
                        ?>
                        <img src="<?php echo $photo; ?>" width="80" height="80"/>
                    </td>
                    <td><?php echo $row["nickname"]; ?></td>
                    <td><?php echo $row["sex"]; ?></td>
                    <td><?php
                        echo $row["province"] . "-" . $row["city"];
                    echo "<BR>OPENID:{$openid}";
                    ?></td>
                </tr>
        </table>


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

<SCRIPT src="../ui/js/jquery.lazyload.js" type=text/javascript></SCRIPT>
<SCRIPT src="../ui/js/jquery.lazyload.plus.js" type=text/javascript></SCRIPT>
<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>