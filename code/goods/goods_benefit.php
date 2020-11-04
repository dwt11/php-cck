<?php
require_once("../config.php");

if (!isset($goodsid)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}

/*$query = "SELECT clientid from  #@__client_depinfos       WHERE id='$id' ";
$row = $dsql->GetOne($query);
$clientid = $row['clientid'];*/

/*从数据库获取积分信息*/
$display_array = array();
$query = "
        SELECT * FROM 
             (
                 (
                    SELECT 
                    GROUP_CONCAT(time_s ) AS time_s,
                    GROUP_CONCAT(id) AS ids
                    ,clientType,clientTypeValue,benefitType
                    ,jbnum,jfnum,isdel,operatorid,createtime
                FROM #@__goods_benefit
                WHERE goodsid='$goodsid' AND isdel=0
               AND (time_s=0)
                 group by clientTypeValue,jbnum,jfnum
                 ORDER BY   time_s ASC
                 ) 
                 union 
                 (     SELECT 
                    GROUP_CONCAT(time_s ) AS time_s,
                    GROUP_CONCAT(id) AS ids
                    ,clientType,clientTypeValue,benefitType
                    ,jbnum,jfnum,isdel,operatorid,createtime
                FROM #@__goods_benefit
                WHERE goodsid='$goodsid' AND isdel=0
               AND /*(time_s>=(unix_timestamp(now())-86400*3) AND*/ time_s>0
                 group by clientTypeValue,jbnum,jfnum
                 ORDER BY   time_s ASC
                ) 
            
            ) AS AA";
//显示当前日期(前三天)之后的有效内容
 //dump($query);
$dsql->Execute('me', $query);
while ($row = $dsql->getarray()) {
    $display_array[] = $row;
}


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
<body class="gray-bg" style="min-width: 750px">
<!--表格数据区------------开始-->
<div class="wrapper ibox-content animated fadeInRight" style="background-color: #ffffff">
    <div class="table-responsive">
        <table data-toggle="table" data-striped="true">
            <thead>
            <tr>
                <th data-halign="center" data-align="center">序号</th>
                <th data-halign="center" data-align="left">有效期</th>
                <th data-halign="center" data-align="center">会员类型</th>
                <th data-halign="center" data-align="center">优惠类型</th>
                <th data-halign="center" data-align="left">优惠额度</th>
                <th data-halign="center" data-align="center">规则添加时间</th>
                <th data-halign="center" data-align="center">操作员</th>
                <th data-halign="center" data-align="center">操作</th>
            </tr>
            </thead>
            <?php
            $i=0;
            foreach ($display_array as $display) {
                $i++;
                $jbnum=$display["jbnum"]/100;
                $jfnum=$display["jfnum"]/100;
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td>
                        <?php
                        $time_s_str_array = explode(",", $display['time_s']);
                        foreach ($time_s_str_array as $key => $time_s_str) {

                            if($time_s_str>0){
                                echo "<span class=\"badge badge-success\">".GetDateMk($time_s_str)."</span> ";

                            }elseif($time_s_str=="0"){
                                echo "<span class=\"badge badge-success\">不限时间</span> ";
                            }

                        }



                        ?>
                    </td>
                    <td>
                        <?php
                        $clientTypeValue=$display["clientTypeValue"];
                        //dump($clientTypeValue);
                        if($clientTypeValue=="0"){
                            echo "非会员";
                        }else{
                            echo $clientTypeValue;
                        }
                        ?>
                    </td>
                    <td><?php
                        $benefitType=$display["benefitType"];
                        echo $benefitType;
                        ?></td>
                    <td>
                        <div style="min-width: 70px"></div>

                        <?php
                        if($benefitType=="购买优惠"){
                            if($jbnum==0){
                                echo "免费";
                            }else{
                                echo $jbnum."%";
                            }
                        }else{
                            echo "金币:$jbnum<br>";
                            echo "积分:$jfnum";
                        }

                        ?>
                    </td>
                    <td><?php echo GetDateTimeMk($display['createtime']); ?></td>
                    <td><?php echo GetEmpNameByUserId($display['operatorid']); ?></td>
                    <td><?php
                        echo $roleCheck->RoleCheckToLink("goods/goods_benefitdel.php?id={$display['ids']}&goodsid=$goodsid");
                        ?></td>
                </tr>
            <?php }
            ?>
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
<!--表格-->

<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
</body>
</html>