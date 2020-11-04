<?php
require_once("../config.php");
require_once('goods.functions.php');
require_once(DWTINC . '/datalistcp.class.php');

// var_dump($id);exit;
if (empty($id)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
 </head>
<body class="gray-bg" style="min-width: 700px">

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>功能地址</th>
                        <th>单位</th>
                        <th>单价</th>
                        <th>数量</th>
                        <th>开始日期</th>
                        <th>结束日期</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query = "SELECT * FROM `#@__sys_goods_orderdetails`  WHERE orderId='$id' ";
                    // dump($query);
                    $dsql->SetQuery($query);
                    $dsql->Execute();
                    $i = 1;
                    while ($row = $dsql->GetArray()) {
                        $starDate = GetDateMk($row['startDate']);
                        $endDate = GetDateMk($row['endDate']);
                        echo '<tr >
                        <td > ' . $row['urladd'] . ' </td >
                        <td > ' . $row['unit'] . ' </td >
                        <td > ' . number_format($row['nowPrice'], 2) . ' </td >
                        <td > ' . $row['numb'] . ' </td >
                        <td > ' . $starDate . '</td >
                        <td > ' . $endDate . '</td >
                        </tr >
                        ';

                        $i++;
                    }
                    ?>
                    </tbody>
                </table>


                <!--商品明细 结束-->


            </div>
        </div>
    </div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<!--表格-->
<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>
