<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($dopost)) $dopost = '';
if (empty($id)) {
    showMsg("非法参数", "feedback.php");
    exit;
}

//End dopost==save
$query = "SELECT * FROM `#@__feedback`  WHERE  id='$id' and clientid='$CLIENTID'";
$row = $dsql->GetOne($query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>详细内容</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
</head>
<body>


<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5> 详细内容 </h5>
    </div>
    <div class="p-xs ibox-title" style="text-align: left">
        <div style="color: black;">
            <b>发布时间：</b><?php echo GetDateMk($row["dtime"]); ?>
        </div>
        <div style="color: black;margin-top: 3px">
            <b>建议内容：</b><?php echo $row["body"]; ?>
        </div>
        <?php
        if ($row["completeBody"] != "") {

            echo "<div style=\"color: black;\">
                    <b>反馈时间：</b>" . GetDateMk($row["completeTime"]) . "
                </div>
                <div style=\"color: black;margin-top: 3px\">
                    <b>反馈内容：</b>" . $row["completeBody"] . "
                </div>";
        }
        ?>
    </div>


</div>
<div class="text-center" style="margin-top: 5px;clear: both">
    <?php include("../index_foot.php"); ?>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>

</body>
</html>
