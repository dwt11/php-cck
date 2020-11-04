<?php
/**
 * @version        $Id: sysData_revert.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

$bkdir = DEDEDATA."/".$cfg_backup_dir;
$filelists = Array();
$dh = dir($bkdir);
$structfile = "没找到数据结构文件";
while(($filename=$dh->read()) !== false)
{
    if(!preg_match("#txt$#", $filename))
    {
        continue;
    }
    if(preg_match("#tables_struct#", $filename))
    {
        $structfile = $filename;
    }
    else if( filesize("$bkdir/$filename") >0 )
    {
        $filelists[] = $filename;
    }
}
$dh->close();
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

                <?php if(count($filelists)==0)echo " <div class=\"ibox-content\"><b>{$cfg_backup_dir}</b>目录中,没找到任何备份文件... </div>"; ?>
                <?php if(count($filelists)>0) {?>
                <div class="ibox-content">
                    <!--搜索框   开始-->

                    <!--搜索框   结束-->


                    <!--表格数据区------------开始-->

                        <form name="form1" onSubmit="checkSubmit()" action="sysData.done.php" method="post" target="stafrm"  >
                            <input type='hidden' name='dopost' value='redat' />
                            <input type='hidden' name='bakfiles' value='' />
                            <table id="datalist11" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true"  data-mobile-responsive="true" data-show-columns="false">
                                <thead>
                                <tr>
                                    <th align="center" data-halign="center" data-align="center"><input name='selAllBut'  id='selAllBut' type='checkbox'   class="i-checks"   /></th>
                                    <th align="center" data-halign="center" data-align="left">表名</th>
                                    <th align="center" data-halign="center" data-align="center">选择</th>

                                    <th align="center" data-halign="center" data-align="left">表名</th>

                                </tr>
                                </thead>
                                <?php
                                for($i=0;$i<count($filelists);$i++)
                                {
                                    echo "<tr   >\r\n";
                                    $mtd = "<td  >

                                            <input name='bakfile' id='bakfile' type='checkbox' class='i-checks' value='".$filelists[$i]."' />
                                                  </label>

                                             </td>
                                             <td >{$filelists[$i]}</td>\r\n";
                                    echo $mtd;
                                    if(isset($filelists[$i+1]))
                                    {
                                        $i++;
                                        $mtd = "<td >
                                            <input name='bakfile' id='bakfile' type='checkbox' class='i-checks'  value='".$filelists[$i]."' />

                                                  </td>
                                                  <td >{$filelists[$i]}</td>\r\n";
                                        echo $mtd;
                                    }else{
                                        echo "<td></td><td></td>\r\n";
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
                                        其他选项
                                    </h5>
                                </div>
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <table width="90%" border="0" cellspacing="0" cellpadding="0">

                                            <tr  bgcolor="#FFFFFF">
                                                <td height="24"  >
                                                    <label class='checkbox-inline   i-checks'>
                                                        <input name="structfile" type="checkbox" class="np" id="structfile" value="<?php echo $structfile?>" checked='1' />
                                                    还原表结构信息(<?php echo $structfile?>)
                                                        </label>
                                                    <label class='checkbox-inline   i-checks'>
                                                    <input name="delfile" type="checkbox" class="np" id="delfile" value="1" />
                                                        还原后删除备份文件
                                                        </label>
                                                    <br>
                                                    <input type="submit" name="Submit" value="开始还原数据" class="btn btn-sm btn-primary"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--选项-->
                            <br>

                            <!--进行状态-->
                            <div class="panel panel-default"  id="status" style="display:none">
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

                <?php }?>

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
        $("input[name='selAllBut']").on('ifChecked', function(event){
            $("input[name='bakfile']").iCheck('check');
        });
        $("input[name='selAllBut']").on('ifUnchecked', function(event){
            $("input[name='bakfile']").iCheck('uncheck');
        });
    });

</script>

<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<!--表格-->
<script type="text/javascript">
    function click_scroll() {
        //进度框 默认是隐藏的，当提交后，显示进度框，并将焦点移至进度框
        document.getElementById("status").style.display="";
        var scroll_offset = $("#status").offset(); //得到pos这个div层的offset，包含两个值，top和left
        $("body,html").animate({
            scrollTop:scroll_offset.top //让body的scrollTop等于pos的top，就实现了滚动
        },0);
    }

    function checkSubmit()
    {
        var myform = document.form1;
        myform.bakfiles.value = getCheckboxItem();
        click_scroll();
        return true;
    }
    //不能删除 获得选中文件的数据表
    function getCheckboxItem(){
        var myform = document.form1;
        var allSel="";
        if(myform.bakfile.value) return myform.bakfile.value;
        for(i=0;i<myform.bakfile.length;i++)
        {
            if(myform.bakfile[i].checked){
                if(allSel=="")
                    allSel=myform.bakfile[i].value;
                else
                    allSel=allSel+","+myform.bakfile[i].value;
            }
        }
        return allSel;
    }

    /*表格配置,,此段不能删除 如果删除 多选 按钮不起作用*/
    !function (e, t, o) {
        "use strict";
        !function () {
            o("#datalist11").bootstrapTable({
            });

        }()
    }(document, window, jQuery);
</script>


</body>
</html>
