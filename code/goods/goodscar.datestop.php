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
        SELECT *    
                FROM #@__car_stop
                WHERE goodsid='$goodsid'  
                /*(time_s>=(unix_timestamp(now())-86400*3) AND*/ 
                 
                 ORDER BY   stoptime DESC
                ";
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
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg" style="min-width: 700px">
<!--表格数据区------------开始-->
<div class="wrapper ibox-content animated fadeInRight" style="background-color: #ffffff">
    <!--标题栏和 添加按钮   结束-->


    <div class="ibox-content">


        <!--工具框   开始-->
      <!--  <div class="btn-group" id="Toolbar">
            <a href="javascript:DelSel();" id="DelSel" class="btn btn-white" data-toggle='tooltip' data-placement='top' title='删除选中'><i class='glyphicon glyphicon-minus' aria-hidden='true'></i></a>
        </div>-->

        <!--工具框   结束-->


        <!--表格数据区------------开始-->
        <div class="table-responsive">
            <table id="datalist" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-sort-order="desc" data-mobile-responsive="true">
                <thead>
                <tr>
          <!--          <th align="center" data-halign="center" data-align="center">
                        <input name='selAllBut' id='selAllBut' type='checkbox' class="i-checks" data-toggle='tooltip' data-placement='top' title='全选/全否'/>
                    </th>
             -->       <th align="center" data-halign="center" data-align="center">停用日期</th>
                    <th align="center" data-halign="center" data-align="left">操作员</th>
                    <th align="center" data-halign="center" data-align="center">操作</th>
                </tr>
                </thead>
                <?php
                $i = 0;
                foreach ($display_array as $display) {
                    $i++;
                    $stopdate = GetDateMk($display["stoptime"]);
                    ?>
                    <tr>
                    <!--    <td>
                            <input name='ids' id='ids' type='checkbox' class="i-checks" value='<?php /*echo $display['id'] */?>'/>
                        </td>-->
                        <td>
                            <?php echo $stopdate; ?>
                        </td>
                        <td>
                            <?php echo GetEmpNameByUserId($display['operatorid']); ?>
                        </td>

                        <td>
                            <a
                                    onclick="
                                    layer.confirm('您确定要删除此内容吗？',
                                     {icon: 3, title: '提示'},
                                     function (index) {
                                        location.href = 'goodscar.datestopdel.php?id=<?php echo $display['id'];?>&goodsid=<?php echo $goodsid;?>';layer.close(index);});"
                                    href='javascript:'
                                    data-toggle='tooltip'
                                    data-placement='top'
                                    title='删除'
                            > 删除 </a>

                        </td>
                    </tr>
                    <?php
                } ?>
            </table>


        </div>
        <!--表格数据区------------结束-->
    </div>
</div>



<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"});

        //是否全选
        $("input[name='selAllBut']").on('ifChecked', function (event) {
            $("input[name='ids']").iCheck('check');
        });
        $("input[name='selAllBut']").on('ifUnchecked', function (event) {
            $("input[name='ids']").iCheck('uncheck');
        });
      });

</script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script src="../ui/js/plugins/layer/layer.min.js"></script>

<script language="javascript">
    function DelSel() {
        var nid = getCheckboxItem('logs');
        if (nid == "") {
            layer.alert('请选择要删除的数据', {icon: 6});
            return;
        }
        layer.confirm('您确定要删除此内容吗？', {icon: 3, title: '提示'}, function (index) {
            location.href = "goodscar_datestopdel.php?ids=" + nid;
        });
    }

</script>
</body>
</html>


