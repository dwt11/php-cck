<?php
/**
 *  编辑
 *
 * @version        $Id:  _edit.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
if (empty($id)) $id = '';
if (empty($ids)) $ids = '';
/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {

    //dump($ids);
    //dump($id);
    $oper_id = $CUSERLOGIN->userID;
    //更新
    $completetime = time();

    $sql = "";
    $dquery = "";
    if ($ids != "") {
        $ids = explode('`', $ids);
        foreach ($ids as $idvalue) {
            if ($dquery == "") {
                $dquery .= " id='$idvalue' ";
            } else {
                $dquery .= " OR id='$idvalue' ";
            }
        }
        if ($dquery != "") {
            $dquery = " where " . $dquery;
            $sql = "UPDATE `#@__order_addon_lycp` SET `infodate`='$completetime', `info`='$info',infooperatorid='$oper_id' $dquery ";
        }
    }
    if ($id != "") {
        $sql = "UPDATE `#@__order_addon_lycp` SET `infodate`='$completetime', `info`='$info',infooperatorid='$oper_id' WHERE id='$id' ";
    }
    //dump($sql);


    if (!$dsql->ExecuteNoneQuery($sql)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("确认信息成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == '') {

    $info = "";
    if ($id != "") {
        //读取信息
        $query = "SELECT *  FROM #@__order_addon_lycp  WHERE id='$id' ";
        $row = $dsql->GetOne($query);
        if (!is_array($row)) {
            ShowMsg("读取信息出错!", "-1");
            exit();
        }
        $info = $row["info"];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" name="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="ids" value="<?php echo $ids; ?>">
        <input type="hidden" name="dopost" value="save">

        保存后,用户会在手机端查看到车辆信息和座位号。

        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea name="info" id="info" class="form-control" placeholder="请填写内容" rows="5"><?php echo $info; ?></textarea>
            </div>
        </div>


        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        $("#form1").validate({
            rules: {
                info: {required: !0}
            },
            messages: {
                info: {required: "请填写内容"}
            }
        })
    });
</script>

</body>
</html>



