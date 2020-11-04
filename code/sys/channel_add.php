<?php



/**
 * 自定义模型
 *
 * @version        $Id: channel_add.php 1 14:46 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");
if (empty($dopost)) $dopost = '';
$maintable="goods";//主表名称
$maintableid=$maintable."id"; //附加表主键的ID名称
$maintablename="x_".$maintable; //主表带前辍名称，保存到模型数据库时使用
if ($dopost == 'save') {

    //检查输入
    if (empty($id) || preg_match("#[^0-9-]#", $id)) {
        ShowMsg("<font color=red>'模型id'</font>必须为数字！", "-1");
        exit();
    }
    if (preg_match("#[^a-z0-9]#i", $nid) || $nid == "") {
        ShowMsg("<font color=red>'模型名字标识'</font>必须为英文字母或与数字混合字符串！", "-1");
        exit();
    }
    if ($addtable == "") {
        ShowMsg("附加表不能为空！", "-1");
        exit();
    }
    $trueTable2 = str_replace("#@__", $cfg_dbprefix, $addtable);


    //检查id是否重复
    $row = $dsql->GetOne("SELECT * FROM #@__sys_channeltype WHERE id='$id' OR nid LIKE '$nid' OR addtable LIKE '$addtable'");
    if (is_array($row)) {
        ShowMsg("可能‘模型id’、‘模型名称标识’、‘附加表名称’在数据库已存在，不能重复使用！", "-1");
        exit();
    }
    $mysql_version = $dsql->GetVersion();

    //创建附加表
    if ($trueTable2 != '') {
        $tabsql = "CREATE TABLE `$trueTable2`(                     `$maintableid` int(11) NOT NULL default '0',           ";
        if ($mysql_version < 4.1) {
            $tabsql .= "    PRIMARY KEY  (`$maintableid`)\r\n) TYPE=MyISAM; ";
        } else {
            $tabsql .= "    PRIMARY KEY  (`$maintableid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=" . $cfg_db_language . "; ";
        }
        $rs = $dsql->ExecuteNoneQuery($tabsql);
        if (!$rs) {
            ShowMsg("创建附加表失败!" . $dsql->GetError(), "javascript:;");
            exit();
        }
    }

    $fieldset = '';

    $inQuery = "INSERT INTO `#@__sys_channeltype`(id,nid,typename,maintable,addtable,templist,tempadd,tempedit,fieldset)
    VALUES ('$id','$nid','$typename','$maintablename','$addtable','$templist','$tempadd','$tempedit','$fieldset');";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("成功增加一个模型！", "channel.php");
    exit();
}
$row = $dsql->GetOne("SELECT id FROM `#@__sys_channeltype`   ORDER BY   id DESC LIMIT 0,1 ");
$newid = $row['id'] + 1;
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


                    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">模型ID</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="id" name="id" value="<?php echo $newid; ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">数字，创建后不可更改，并具有唯一性。</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">名字标识</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="nid" name="nid" value="ch<?php echo $newid; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">模型名称</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="typename" name="typename" value="模型<?php echo $newid; ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">模型的中文名称，在后台管理，前台发布等均使用此名字。</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">附加表</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="addtable" name="addtable" value="<?php echo $cfg_dbprefix,$maintable ,'_addon', $newid; ?>">
                            </div>
                            <div class="col-sm-6 form-control-static">必须由英文、数字、下划线组成 * 模型除主表以外其它自定义类型数据存放数据的表。</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">列表模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="templist" name="templist" value="goods.htm">
                            </div>
                            <div class="col-sm-6"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">添加模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="tempadd" name="tempadd" value="goods_add.htm">
                            </div>
                            <div class="col-sm-6"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">编辑模板</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="tempedit" name="tempedit" value="goods_edit.htm">
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