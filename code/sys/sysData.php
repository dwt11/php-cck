<?php
/**
 * 数据库备份/还原
 *
 * @version        $Id: sysData.php 1 17:19 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

if (empty($dopost)) $dopost = '';

if ($dopost == "viewinfo") //查看表结构
{
    //echo "[<a href='#' onclick='javascript:HideObj(\"_mydatainfo\")'><u>关闭</u></a>]\r\n<xmp>";
    echo "<!DOCTYPE html><html><head>
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <link href=\"../ui/css/bootstrap.min.css\" rel=\"stylesheet\">
            <link href=\"../ui/css/style.min.css\" rel=\"stylesheet\">
            <style>html{  height: auto; /*160109 style.min.css将html设置为 {  height: 100%;} ,引起layer自动适应高度时错误 ,再这里将100%屏蔽掉*/ }</style>
           </head><body style=\"min-width:400px\"><div class='wrapper wrapper-content animated fadeInRight'  style='background-color: #ffffff'><pre>";
    if (empty($tablename)) {
        echo "没有指定表名！";
    } else { //dump("SHOW CREATE TABLE ".$dsql->dbName.".".$tablename);
        $dsql->SetQuery("SHOW CREATE TABLE " . $dsql->dbName . "." . $tablename);
        $dsql->Execute('me');
        $row2 = $dsql->GetArray('me', MYSQL_BOTH);
        $ctinfo = $row2[1];
        echo trim($ctinfo);
    }
    echo '</pre></div>';
    echo "    <script src=\"../ui/js/jquery.min.js\"></script>
                <script src=\"../ui/js/plugins/layer/layer.min.js\"></script>
                <script>
                //让层自适应iframe
                var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                parent.layer.iframeAuto(index);
                </script>
            </body></html>";
    exit();
} else if ($dopost == "opimize") //优化表
{
    // echo "[<a href='#' onclick='javascript:HideObj(\"_mydatainfo\")'><u>关闭</u></a>]\r\n<xmp>";
    if (empty($tablename)) {
        echo "没有指定表名！";
    } else {
        $rs = $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$tablename` ");
        if ($rs) {
            echo " $tablename  完成！";
        } else {
            echo " $tablename  失败，原因是：" . $dsql->GetError();
        }
    }
    //echo '</xmp>';
    exit();
} else if ($dopost == "repair") //修复表
{
    //echo "[<a href='#' onclick='javascript:HideObj(\"_mydatainfo\")'><u>关闭</u></a>]\r\n<xmp>";
    if (empty($tablename)) {
        echo "没有指定表名！";
    } else {
        $rs = $dsql->ExecuteNoneQuery("REPAIR TABLE `$tablename` ");
        if ($rs) {
            echo " $tablename  完成！";
        } else {
            echo " $tablename  失败，原因是：" . $dsql->GetError();
        }
    }
    //echo '</xmp>';
    exit();
}

//获取系统存在的表信息
//$otherTables = Array();//其他数据表
$dwtSysTables = Array();


$dsql->SetQuery("SHOW TABLES");
$dsql->Execute('t');
while ($row = $dsql->GetArray('t', MYSQL_BOTH)) {
    //if(preg_match("#^{$cfg_dbprefix}#", $row[0])||in_array($row[0],$channelTables))
    // {
    $dwtSysTables[] = $row[0];
    //}
    // else
    // {
    // $otherTables[] = $row[0];
    //  }
}
$mysql_version = $dsql->GetVersion();


function TjCount($tbname, &$dsql)
{
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM $tbname");
    return $row['dd'];
}


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
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?></h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <div class="table-responsive">


                        <form name="form1" onSubmit="checkSubmit()" action="sysData.done.php?dopost=bak" method="post" target="stafrm">
                            <input type='hidden' name='tablearr' value=''/>
                            <table id="datalist11" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-mobile-responsive="true" data-show-columns="false">
                                <thead>
                                <tr>
                                    <th align="center" data-halign="center" data-align="center">
                                        <input name='selAllBut' id='selAllBut' type='checkbox' class="i-checks" checked/>
                                    </th>
                                    <th align="center" data-halign="center" data-align="left">表名</th>
                                    <th align="center" data-halign="center" data-align="center">记录数</th>
                                    <th align="center" data-halign="center" data-align="center">操作</th>
                                    <th align="center" data-halign="center" data-align="center">选择</th>
                                    <th align="center" data-halign="center" data-align="left">表名</th>
                                    <th align="center" data-halign="center" data-align="center">记录数</th>
                                    <th align="center" data-halign="center" data-align="center">操作</th>
                                </tr>
                                </thead>
                                <?php
                                for ($i = 0; isset($dwtSysTables[$i]); $i++) {
                                    $t = $dwtSysTables[$i];
                                    echo "<tr  >\r\n";
                                    ?>
                                    <td>

                                        <input name='tables' type='checkbox' class='i-checks' value="<?php echo $t; ?>" checked/>


                                    </td>
                                    <td>
                                        <?php echo $t; ?>
                                    </td>
                                    <td>
                                        <?php echo TjCount($t, $dsql); ?>
                                    </td>
                                    <td>

                                        <a onclick="layer.open({type: 2,title: '优化数据表', content: 'sysData.php?dopost=opimize&tablename=<?php echo $t; ?>'});" href="javascript:;">优化</a> |
                                        <a onclick="layer.open({type: 2,title: '修复数据表', content: 'sysData.php?dopost=repair&tablename=<?php echo $t; ?>'});" href="javascript:;">修复</a> |
                                        <a onclick="layer.open({type: 2,title: '数据表结构',content: 'sysData.php?dopost=viewinfo&tablename=<?php echo $t; ?>'});" href="javascript:;">结构</a>
                                    </td>
                                    <?php
                                    $i++;
                                    if (isset($dwtSysTables[$i])) {
                                        $t = $dwtSysTables[$i];
                                        ?>
                                        <td>

                                            <input name='tables' type='checkbox' class='i-checks' value="<?php echo $t; ?>" checked/>


                                        </td>
                                        <td>
                                            <?php echo $t; ?>
                                        </td>
                                        <td>
                                            <?php echo TjCount($t, $dsql); ?>
                                        </td>
                                        <td>

                                            <a onclick="layer.open({type: 2,title: '优化数据表', content: 'sysData.php?dopost=opimize&tablename=<?php echo $t; ?>'});" href="javascript:;">优化</a> |
                                            <a onclick="layer.open({type: 2,title: '修复数据表', content: 'sysData.php?dopost=repair&tablename=<?php echo $t; ?>'});" href="javascript:;">修复</a> |
                                            <a onclick="layer.open({type: 2,title: '数据表结构',content: 'sysData.php?dopost=viewinfo&tablename=<?php echo $t; ?>'});" href="javascript:;">结构</a>
                                        </td>
                                        <?php
                                    } else {
                                        echo "<td></td><td></td><td></td><td></td>\r\n";
                                    }
                                    echo "</tr>\r\n";
                                }
                                ?>


                            </table>


                            <br>
                            <!--选项-->

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        数据备份选项
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body form-horizontal">

                                        <div class="form-group">
                                            <label class="col-sm-2  control-label">当前数据库版本:</label>

                                            <div class="col-sm-1  form-inline">
                                                <input value="<?php echo $mysql_version ?>" size="4" class="form-control" disabled/>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">备份数据格式:</label>

                                            <div class="col-sm-3">
                                                <label class="checkbox-inline i-checks">

                                                    <input name="datatype" type="radio" value="4.0"<?php if ($mysql_version < 4.1) echo " checked"; ?> />
                                                    MySQL3.x/4.0.x 版本
                                                </label>
                                                <label class="checkbox-inline i-checks">

                                                    <input type="radio" name="datatype" value="4.1" <?php if ($mysql_version >= 4.1) echo " checked"; ?> />
                                                    MySQL4.1.x/5.x 版本
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">分卷大小:</label>

                                            <div class="col-sm-1 form-inline">
                                                <input name="fsize" type="text" id="fsize" value="2048" size="4" class="form-control"/>
                                                K
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">表结构信息:</label>

                                            <div class="col-sm-3">
                                                <label class='checkbox-inline   i-checks'>
                                                    <input name="isstruct" type="checkbox" id="isstruct" value="1" checked/>
                                                    备份表结构信息
                                                </label>
                                            </div>
                                        </div>
                                        <?php if (@function_exists('gzcompress') && false) { //此功能未实现，随后再处理,提交后没有实现压缩ZIP的代码?>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">压缩成ZIP:</label>

                                                <div class="col-sm-3">
                                                    <label class='checkbox-inline   i-checks'>
                                                        <input name="iszip" type="checkbox" class="np" id="iszip" value="1" checked/>
                                                        完成后压缩成ZIP
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="form-group">
                                            <div class="col-sm-4 col-sm-offset-2">
                                                <button class="btn btn-primary" type="submit">提交</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!--选项-->
                            <br>

                            <!--进行状态-->
                            <div class="panel panel-default" id="status" style="display:none">
                                <div class="panel-heading">
                                    <h5 class="panel-title">
                                        进行状态
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%" style="min-height: 200px"></iframe>
                                </div>
                            </div>
                            <!--进行状态-->

                        </form>


                    </div>
                    <!--表格数据区------------结束-->
                </div>


            </div>
        </div>

    </div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
        //是否全选
        $("input[name='selAllBut']").on('ifChecked', function (event) {
            $("input[name='tables']").iCheck('check');
        });
        $("input[name='selAllBut']").on('ifUnchecked', function (event) {
            $("input[name='tables']").iCheck('uncheck');
        });
    });
</script>

<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--表格-->
<script type="text/javascript">
    function click_scroll() {
        //进度框 默认是隐藏的，当提交后，显示进度框，并将焦点移至进度框
        document.getElementById("status").style.display = "";
        var scroll_offset = $("#status").offset(); //得到pos这个div层的offset，包含两个值，top和left
        $("body,html").animate({
            scrollTop: scroll_offset.top //让body的scrollTop等于pos的top，就实现了滚动
        }, 0);
    }

    function checkSubmit() {
        var myform = document.form1;
        myform.tablearr.value = getCheckboxItem();
        click_scroll();
        return true;
    }


    //不能删除 获得选中文件的数据表
    function getCheckboxItem() {
        var myform = document.form1;
        var allSel = "";
        if (myform.tables.value) return myform.tables.value;
        for (i = 0; i < myform.tables.length; i++) {
            if (myform.tables[i].checked) {
                if (allSel == "")
                    allSel = myform.tables[i].value;
                else
                    allSel = allSel + "," + myform.tables[i].value;
            }
        }
        return allSel;
    }
    /*表格配置,,此段不能删除 如果删除 多选 按钮不起作用*/
    !function (e, t, o) {
        "use strict";
        !function () {
            o("#datalist11").bootstrapTable({});

        }()
    }(document, window, jQuery);

</script>


</body>
</html>



