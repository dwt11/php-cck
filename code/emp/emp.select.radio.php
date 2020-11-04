<?php
require_once("../config.php");

if (empty($targetname)) $targetname = '';
if (empty($keyword)) $keyword = '';
if (empty($emp_dep)) $emp_dep = '';
if (empty($no_emp_dep)) $no_emp_dep = '';


?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>选择员工</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg" style="min-width:700px">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="btn-group" id="Toolbar2">
                        <input type="submit" id="closepage" value="确定" class="btn  btn-primary">
                    </div>
                    <div class="btn-group" style="margin-left: 5px">
                        <form name="form2" method="get" action="">
                            <div class="input-group">
                                <div class="pull-left">
                                    <?php
                                    $depOptions = GetDepOptionListNoRole($emp_dep);
                                    //dump($emp_dep);
                                    echo "<select style='max-width: 260px'  class='form-control' name='emp_dep' id='emp_dep'  >\r\n";
                                    echo "
                                    <option value='0'>请选择部门...</option>
                                                                                          \r\n";
                                    echo $depOptions;
                                    echo "</select>";
                                    ?>


                                </div>
                                <div class="pull-left ">
                                    <input name="keyword" type="text" placeholder="姓名/编号" class="form-control" value="">
                                    <input name="targetname" type="hidden" value="<?php echo $targetname ?>">
                                </div>
                                <div class="pull-left ">
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-white">
                                            搜索
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>

                    <!--表格数据区------------开始-->
                    <?php

                    require_once('../emp/emp.inc.class.radio.php');

                    $tus = new empMapRadio();

                    $tus->empAllRadio($emp_dep,$keyword,$no_emp_dep); ?>

                    <!--表格数据区------------结束-->
                </div>
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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    });
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
<!--表格-->
<script type="text/javascript">
    var targetname='<?php echo $targetname?>';
    if(targetname=="")targetname="emp_id";
    $('#closepage').click(function () {
        var emp_id = $('input:radio[name=emp_id]:checked').val();
        if (!emp_id) {
            alert("请选择员工");
            return false;
        }
        parent.$("#"+targetname).val(emp_id);
        parent.layer.closeAll('iframe');
    })
</script>
</body>
</html>
