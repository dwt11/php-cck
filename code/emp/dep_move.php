<?php
/**
 * 部门编辑
 *
 * @version        $Id: dep_edit.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
$dep_id = isset($dep_id) ? intval($dep_id) : 0;


/**
 * @param $dep_id   当前部门ID
 * @param $new_id   要移动到的目录 ID
 *
 * @return bool
 */
function IsDEPParent($new_id,$dep_id)
{
    $pTypeArrays = GetDEPParentIds($new_id);
    return in_array($dep_id, $pTypeArrays);
}

//获取所有分类的数组141009
function GetDEPCatalogs()
{
    global $cfg_Cs_DEP, $dsql;
    $dsql->SetQuery("SELECT dep_id,dep_reid  FROM `#@__emp_dep`");
    $dsql->Execute();
    $cfg_Cs_DEP = array();
    while ($row = $dsql->GetObject()) {

        $cfg_Cs_DEP[$row->dep_id] = array($row->dep_reid);
    }
}

/**
 *  获取上级ID列表
 *
 * @access    public
 *
 * @param     string $dep_id 部门ID
 *
 * @return    string
 */

function GetDEPParentIds($dep_id)
{

    global $cfg_Cs_DEP;
    $GLOBALS['pTypeArraysDEP'][] = $dep_id;
    if (!is_array($cfg_Cs_DEP)) {
        GetDEPCatalogs();
    }
    if (!isset($cfg_Cs_DEP[$dep_id]) || $cfg_Cs_DEP[$dep_id][0] == 0) {
        return $GLOBALS['pTypeArraysDEP'];
    } else {
        return GetDEPParentIds($cfg_Cs_DEP[$dep_id][0]);
    }

}

/*-----------------------
function action_save()
----------------------*/
if ($dopost == "save") {

    if ($dep_id == $new_id) {
        echo '两个部门不能相同！';
        exit();
    }
    if (IsDEPParent( $new_id,$dep_id)) {
        echo '不能从父类移动到子类！';
        exit();
    }
    $dsql->ExecuteNoneQuery(" UPDATE `#@__emp_dep` SET dep_reid='$new_id' WHERE dep_id='$dep_id' ");

    echo "成功";
    exit();



}

//读取部门信息
$dsql->SetQuery("SELECT * FROM `#@__emp_dep`  WHERE dep_id=$dep_id");
////dump("SELECT * FROM `#@__em_dep`  WHERE dep_id=$dep_id");
$myrow = $dsql->GetOne();
$topid = $myrow['dep_reid'];

//PutCookie('lastCid',GetTopid($id),3600*24,"/");
?>


<!DOCTYPE html>
<html>

<head>

    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>

        <div class="form-group">
            <label class="col-sm-4 control-label">部门名称:</label>

            <div class="col-sm-6 ">
                <p class='form-control-static'><?php echo $myrow['dep_name'] ?> </p>
                <input type="hidden" name="dep_id" id="dep_id" value="<?php echo $dep_id ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">要移动到哪个部门下:</label>

            <div class="col-sm-6">
                <select class="form-control m-b" name='new_id' id='new_id'>
                    <option value='0'>请选择部门...</option>
                    <?php
                    $depOptions = GetDepOptionListRole();
                    echo $depOptions;
                    ?>
                </select>

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
                dep_name: {required: !0},
                new_id: {
                    isIntGtZero: !0
                }
            },
            messages: {
                dep_name: {required: "请填写部门名称"},
                new_id: {
                    isIntGtZero: "请选择要移动到哪个部门下"
                }
            }, submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "dep_move.php",
                    data: "dopost=save&new_id=" + $("#new_id").val()+"&dep_id=" + $("#dep_id").val(),
                    dataType: 'html',
                    success: function (result) {
                        if (result == "成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                parent.location.href = "dep.php";
                            });
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
                            });
                        }
                    }
                });
            }
        })
    });
</script>

</body>
</html>