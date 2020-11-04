<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');

// var_dump($id);exit;
if (empty($depid)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
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
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <table id="datalist" data-striped="true">
                        <thead>
                        <tr>
                            <th>功能地址</th>
                            <th>开始日期</th>
                            <th>结束日期</th>
                            <th>剩余日期</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT od.* FROM `#@__sys_goods_orderdetails` od  WHERE depid='$depid' ";
                        // dump($query);
                        $dsql->SetQuery($query);
                        $dsql->Execute();
                        $i = 1;
                        while ($row = $dsql->GetArray()) {
                            $starDate = GetDateMk($row['startDate']);
                            $endDate = GetDateMk($row['endDate']);

                            $now = strtotime("now"); //当前时间
                            $endtime = $row['endDate']; //结束时间

                            $surplusDay = "";//剩余天数
                            if ($endtime > 0) {
                                $second = $endtime - $now; //获取毕业时间到现在时间的时间戳（秒数）
                                if ($second > 0) {
                                    $year = floor($second / 3600 / 24 / 365); //从这个时间戳中换算出年头数
                                    if ($year < 0) $year = 0;

                                    $temp = $second - $year * 365 * 24 * 3600; //从这个时间戳中去掉整年的秒数，就剩下月份的秒数
                                    $month = floor($temp / 3600 / 24 / 30); //从这个时间戳中共换算出月数
                                    //dump($temp);
                                    //dump($month);
                                    //if($month<0)$month=0;

                                    $temp = $temp - $month * 30 * 3600 * 24; //从时间戳中去掉整月的秒数，就剩下天的描述
                                    $day = floor($temp / 24 / 3600); //从这个时间戳中换算出剩余的天数
                                    //if($day<0)$day=0;

                                    // $temp=$temp-$day*3600*24; //从这个时间戳中去掉整天的秒数，就剩下小时的秒数
                                    // $hour = floor($temp/3600); //从这个时间戳中换算出剩余的小时数

                                    //$temp=$temp- $hour*3600; //从时间戳中去掉小时的秒数，就剩下分的秒数
                                    //$minute=floor($temp/60); //从这个时间戳中换算出剩余的分数

                                    //$second1=$temp-$minute*60; //最后只有剩余的秒数了

                                    $surplusDay = "";
                                    if ($year > 0) $surplusDay .= " $year 年 ";
                                    if ($month > 0) $surplusDay .= "  $month 月 ";
                                    if ($day > 0) $surplusDay .= " $day 天 ";
                                    if ($surplusDay != "") $surplusDay = "还剩下 " . $surplusDay;
                                } else {
                                    $surplusDay = "使用时间已经结束";
                                }
                            }

                            echo '<tr >
                        <td > ' . $row['urladd'] . ' </td >
                         <td > ' . $starDate . '</td >
                        <td > ' . $endDate . '</td >
                        <td > ' . $surplusDay . '</td >
                        </tr >
                        ';

                            $i++;
                        }
                        ?>
                        </tbody>
                    </table>


                    <!--商品明细 结束-->


                </div>
                <!--表格数据区------------结束-->
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
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>
