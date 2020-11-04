<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单


if (empty($nowMonth)) $nowMonth = date('Y-m', time());//如果为空则取当前月


//--------------------------------按月,显示 不过期的线路发车信息
$query3 = "SELECT #@__line.gotime  FROM  #@__line 
          WHERE   #@__line.islock=1
          AND  
                             UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
           GROUP BY  FROM_UNIXTIME(#@__line.gotime ,'%Y-%m') 
           ORDER BY  #@__line.gotime ASC
          ";
$dsql->SetQuery($query3);
$dsql->Execute("170126");
$yue_option = "";//临时线路分组
while ($row1 = $dsql->GetArray("170126")) {
    $gotime = date('m', $row1['gotime']);
    $year_monty = date('Y-m', $row1['gotime']);
    $class = "";
    if ($year_monty == $nowMonth) $class = "current";
    $gotime = NumToWord($gotime);
    $yue_option .= "<li class=\"{$class}\"><a href=\"?nowMonth={$year_monty}\">{$gotime}月</a></li>";
}

//--------------------------------按选择的月显示所有的日 不过期的线路发车信息
$query3 = "SELECT #@__line.gotime,count(#@__line.id) as linenumb  FROM  #@__line 
          WHERE   #@__line.islock=1
          AND  
                             UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
                             AND (FROM_UNIXTIME(#@__line.gotime ,'%Y-%m') ='$nowMonth')
           GROUP BY  FROM_UNIXTIME(#@__line.gotime ,'%Y-%m-%d') 
           ORDER BY  #@__line.gotime ASC
          ";
//dump($query3);
$dsql->SetQuery($query3);
$dsql->Execute("170126");
$ls_option = "";//临时线路分组
while ($row1 = $dsql->GetArray("170126")) {
    $week_str = GetWeekFormDateStr(GetDateMk($row1['gotime']));
    if ($week_str == "周六" || $week_str == "周日") $week_str = "<lable class='label-primary badge-primary'>{$week_str}</lable>";
    $gotime = date('m月d日', $row1['gotime']);
    $go_date = date('Y-m-d', $row1['gotime']);
    $linenumb = $row1['linenumb'];
    $ls_option .= "
                        <li class=\"list-group-item1   h4\">
                                                   <a href='goods_list.php?typeid=2&go_date={$go_date}'>
                                                   <B>{$gotime} {$week_str} </B>
                                                    <span class=\"pull-right  \">
                                                          [$linenumb]条
                                                    </span>
                       </a> </li>
                        ";
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>选择出行日期</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../ui/css/animate.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
    <link href="../css/goodslist.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php
    include("goodsLycpHeard.php");
    ?>
    <form id="form" class="form-horizontal">
        <div class="tab" id="s-header">
            <?php echo $yue_option ?>
        </div>

        <ul class="list-group list-group-plus-nomargintop list-font-color-black">


            <?php
            if ($ls_option != "") {
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                出发日期 
                                
                                <span class=\"pull-right  \">
                                 线路发车数量
                            </span>
                     </li>

                                
                                     $ls_option
                                    
                                    ";

            } else {
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                出发日期
                                <span class=\"pull-right    text-muted small\">
                                    {$nowMonth}暂无可选的出发日期,请查看其他月份
                                </span>
                            </li>";
            }


            ?>


        </ul>


    </form>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>


</body>
</html>


