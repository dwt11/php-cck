<?php
/**
 * 用户登录信息查看
 *
 * @version        $Id: .php 1 14:31 12日
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


$display_array = array();
$query = "SELECT * FROM `#@__client_pw` WHERE clientid='$id' ";
$row = $dsql->GetOne($query);


//($display_array);


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
                <th data-halign="center" data-align="center">用户名</th>
                <th data-halign="center" data-align="center">最后登录日期</th>
                <th data-halign="center" data-align="center">最后登录IP</th>
                <th data-halign="center" data-align="center">登录次数</th>
            </tr>
            </thead>

                <tr>

                    <td><?php echo $row["userName"]; ?></td>
                    <td><?php echo GetDateTimeMk($row["logintime"]); ?></td>
                    <td><?php echo $row["loginip"]; ?></td>
                    <td><?php echo $row["loginnumb"]; ?></td>
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

<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>