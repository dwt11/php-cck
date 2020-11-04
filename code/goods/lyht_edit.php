<?php
/**
 * 文档编辑
 *
 * @version        $Id: archives_edit.php 1 8:26 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';


$id = isset($id) && is_numeric($id) ? $id : 0;
/*--------------------------------
function __save(){  }
-------------------------------*/
if ($dopost == 'save') {
    if (trim($title) == '') {
        ShowMsg('标题不能为空', '-1');
        exit();
    }

    //更新数据库的SQL语句
    $query = "UPDATE #@__lyht SET    title='$title',  body='$body'            WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据时出错，请检查', -1);
        exit();
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}


//读取归档信息
$addRow = $dsql->GetOne("SELECT * FROM `#@__lyht` WHERE id='$id'");
if (!is_array($addRow)) {
    ShowMsg("读取信息出错!", "javascript:;");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="<?php echo $cfg_soft_lang; ?>">
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
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>

                </div>
                <div class="ibox-content">
                    <form id="form1" name="form1" action="" method="post" class="form-horizontal">

                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="id" value="<?php echo $id ?>"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">标题：</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="title" id="title" value="<?php echo $addRow['title']; ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">合同内容:</label>
                            <div class="col-sm-2">
                                <?php echo GetEditor("body", $addRow['body']); ?>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2 ">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>

<script>


    $().ready(function () {
        $("#form1").validate({
            rules: {
                title: {required: !0}
            },
            messages: {
                title: {required: "请填写标题"}
            }
        })
    });
</script>


</body>
</html>