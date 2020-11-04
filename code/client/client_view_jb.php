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

/*从数据库获取积分信息*/
$display_array = array();
$query = "SELECT jb.id,jb.jbnum,jb.yenum,jb.`desc`,jb.info,jb.createtime,jb.operatorid,
            c1.realname,c1.mobilephone,c2.idcard
            FROM #@__clientdata_jblog jb
            LEFT JOIN #@__client c1 ON jb.clientid=c1.id
            LEFT JOIN #@__client_addon c2 ON jb.clientid=c2.clientid
            where jb.clientid='$id' and jb.isdel=0
              ORDER BY   jb.id desc";
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
                <th data-halign="center" data-align="left">用户名称</th>
                <th data-halign="center" data-align="center">变动</th>
                <th data-halign="center" data-align="center">余额</th>
                <th data-halign="center" data-align="left">变动说明</th>
                <th data-halign="center" data-align="center"  style="min-width:180px">操作时间</th>
                <th data-halign="center" data-align="center">操作员</th>
            </tr>
            </thead>
            <?php
            $i = 0;
            $totalMoney = 0;
            foreach ($display_array as $display) {
                $i++;

                $jbnum=$display["jbnum"]/100;
                $yenum=$display["yenum"]/100;
                $totalMoney +=$jbnum ;
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $display["realname"]; ?></td>
                    <td><?php echo $jbnum; ?></td>
                    <td><?php echo $yenum; ?></td>
                    <td><?php echo $display["desc"];
                        if ($display["info"] != "") echo "<br>备注：" . $display["info"] . "</b>";
                        ?></td>
                    <td>
                        <div style="min-width: 70px"></div>

                        <?php
                        echo GetDateMk($display['createtime']);
                        ?>
                    </td>
                    <td><?php echo GetEmpNameByUserId($display['operatorid']); ?></td>
                </tr>
            <?php }
            if ($display_array) {
                ?>
                <tr>
                    <td></td>
                    <td align="right">合计：</td>
                    <td>
                        <?php echo $totalMoney; ?>

                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php } ?>
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