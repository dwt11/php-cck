<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: channel_edit.php 1 14:49 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");

if (empty($dopost)) $dopost = "";
$id = isset($id) && is_numeric($id) ? $id : 0;


/*------------
function __SaveEdit()
------------*/
if ($dopost == "save") {

    $query = "Update `#@__sys_channeltype` set
    typename = '$typename',
    templist = '$templist',
    tempadd = '$tempadd',
    tempedit = '$tempedit'
    where id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功更改一个模型！", "channel.php");
    exit();
}


$row = $dsql->GetOne("SELECT * FROM `#@__sys_channeltype` WHERE id='$id' ");


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
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>

                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                        <input type='hidden' name='id' value='<?php echo $id ?>'>
                        <input type="hidden" name="dopost" value="save"/>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">模型ID</label>
                            <div class="col-sm-2">
                                <label class="control-label"><?php echo $row['id']; ?></label>
                            </div>
                            <div class="col-sm-6 form-control-static">数字，创建后不可更改，并具有唯一性。</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">名字标识</label>
                            <div class="col-sm-2">
                                <label class="control-label"><?php echo $row['nid']; ?></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">模型名称</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="typename" name="typename" value="<?php echo $row['typename']; ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">模型的中文名称，在后台管理，前台发布等均使用此名字。</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">附加表</label>
                            <div class="col-sm-2">
                                <label class="control-label"><?php echo $row['addtable']; ?></label>
                            </div>
                            <div class="col-sm-6 form-control-static">模型除主表以外其它自定义类型数据存放数据的表。( #@__ 是表示数据表前缀)</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">列表模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="templist" name="templist" value="<?php echo $row['templist']; ?>">
                            </div>
                            <div class="col-sm-6"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">添加模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="tempadd" name="tempadd" value="<?php echo $row['tempadd']; ?>">
                            </div>
                            <div class="col-sm-6"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">编辑模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="tempedit" name="tempedit" value="<?php echo $row['tempedit']; ?>">
                            </div>
                            <div class="col-sm-6"></div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>
                    </form>
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
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/toastr/toastr.min.js"></script>
<link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        $(".i-checks").iCheck({
            checkboxClass: "icheckbox_square-green",
            radioClass: "iradio_square-green",
        })
        $("#form1").validate({
            rules: {
                typename: {required: !0}
            },
            messages: {
                typename: {required: "请填写名称"}
            }
        })
    });


</script>
</body>
</html>