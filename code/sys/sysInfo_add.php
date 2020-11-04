<?php
/**
 * 添加
 *
 * @version
 * @package
 * @copyright
 * @license
 * @link
 * 151218 注销掉判断部门相关的功能,改为添加功能时不选择部门,添加后再编辑所属的部门
 */
require_once("../config.php");
require_once("sysFunction.class.php");

if (empty($dopost)) $dopost = '';
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    if ($vartype == 'bool' && ($nvarvalue != 'Y' && $nvarvalue != 'N')) {
        ShowMsg("布尔变量值必须为'Y'或'N',注意大小写!", "-1");
        exit();
    }
    if (trim($nvarname) == '' || preg_match("#[^a-z_]#i", $nvarname)) {
        ShowMsg("变量名不能为空并且必须为[a-z_]组成!", "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT varname FROM `#@__sys_sysOtherConfig` WHERE varname LIKE '$nvarname' AND depid='$depid' ");
    if (is_array($row)) {
        ShowMsg("该变量名称已经存在!", "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT id FROM `#@__sys_sysOtherConfig`   ORDER BY   id DESC ");
    $id = $row['id'] + 1;
    $inquery = "INSERT INTO `#@__sys_sysOtherConfig`(`id`,`depid`,`varname`,`info`,`value`,`type`,`groupname`)
                  VALUES ('$id','$depid','$nvarname','$varmsg','$nvarvalue','$vartype','$vargroup')
                  ";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if (!$rs) {
        ShowMsg("新增失败，可能有非法字符！", $$ENV_GOBACK_URL);
        exit();
    }


    ShowMsg("成功保存！", $$ENV_GOBACK_URL);
    exit();

}//End dopost==save


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" action='' method='post' class="form-horizontal" target="_parent">

        <input type='hidden' name='dopost' value='save'/>

        <div class="form-group">
            <label class="col-sm-2 control-label">公司名称:</label>

            <div class="col-sm-2">
                <?php
                if ($GLOBALS['CUSERLOGIN']->getUserType() != 10 && $GLOBAMOREDEP) {
                    echo "<input type='hidden' value='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "' id='depid' name='depid'> ";
                    echo "<div class='form-control-static'>" . GetDepsNameByDepId($GLOBALS['NOWLOGINUSERTOPDEPID']) . "</div>";
                } else {
                    $depOptions = GetDepOnlyTopOptionList();
                    echo "<select name='depid' id='depid'  class='form-control m-b'  >\r\n";
                    echo "<option value='0'>请选择公司</option>\r\n";
                    echo $depOptions;
                    echo "</select>";
                }
                ?>
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">参数说明:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="varmsg" id="varmsg" autocomplete="off">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">组名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="vargroup" id="vargroup" autocomplete="off">
            </div>
            <div class="col-sm-2 form-control-static">
                添加新的,会自动生成新的TAB标签组
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">变量名称:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="nvarname" id="nvarname" autocomplete="off">
            </div>
            <div class="col-sm-2 form-control-static">
                必须英文,例:cfg_client_login
            </div>

        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">变量值:</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="nvarvalue" name="nvarvalue" value="">
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">变量类型:</label>

            <div class="col-sm-4">
                <label class="checkbox-inline i-checks">
                    <input name="vartype" type="radio" value="string" checked='checked'/>
                    文本
                </label>
                <label class="checkbox-inline i-checks">
                    <input name="vartype" type="radio" value="number"/>
                    数字
                </label>
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="vartype" value="bool"/>
                    布尔(Y/N)
                </label>
                <label class="checkbox-inline i-checks">
                    <input type="radio" name="vartype" value="bstring"/>
                    多行文本
                </label>
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
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>


<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    });
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        $("#form1").validate({
            rules: {
                depid: {isIntGtZero: !0},
                varmsg: {required: !0},
                vargroup: {required: !0},
                nvarname: {required: !0},
                nvarvalue: {required: !0}
            },
            messages: {
                depid: {isIntGtZero: "请选择公司"},
                varmsg: {required: "请填写参数说明"},
                vargroup: {required: "请填写组名称"},
                nvarname: {required: "请填写变量名称"},
                nvarvalue: {required: "请填写变量值"}
            }
        });

    });
</script>
</body>
</html>
