<?php
/**
 * 分类添加
 *
 * @version        $Id: catalog_add.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

if (empty($dopost)) $dopost = '';
if (empty($channelid)) $channelid = 1;
if (isset($channeltype)) $channelid = $channeltype;

//点击增加子类时的 上级ID
$id = isset($id) ? intval($id) : 0;
$reid = empty($reid) ? 0 : intval($reid);


if (empty($myrow)) $myrow = array();

$dsql->SetQuery("SELECT id,typename,nid FROM `#@__sys_channeltype`     ORDER BY   id");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if ($row->id == $channelid) {
        $nid = $row->nid;
    }
}
if ($dopost == 'save') {

    if ($topid == 0 && $reid > 0) $topid = $reid;
    $content = isset($content) ? $content : "";

    if ($typename == ""  ) {
        ShowMsg("名称不能为空！", "-1");
        exit;
    }

    $in_query = "INSERT INTO `#@__goods_type`(reid,topid,sortrank,typename,ispart,tempindex,templist,tempgoods,channeltype,`content`)
    VALUES('$reid','$topid','$sortrank','$typename','$ispart','$tempindex','$templist','$tempgoods','$channeltype','$content')";

    if (!$dsql->ExecuteNoneQuery($in_query)) {
        ShowMsg("保存目录数据时失败，请检查你的输入资料是否存在问题！", "-1");
        exit();
    }

    ShowMsg("成功创建一个分类！", "catalog.php");
    exit();

}//End dopost==save

//获取从父目录继承的默认参数
if ($dopost == '') {
    $channelid = 1;
    $reid = 0;
    $topid = 0;
    if ($id > 0) {
        $myrow = $dsql->GetOne(" SELECT tp.* FROM `#@__goods_type` tp WHERE tp.id=$id ");
        $channelid = $myrow['channeltype'];
        $topid = $myrow['topid'];
    }

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


                    <form name="form1" id="form1" action="catalog_add.php" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="reid" id="reid" value="<?php echo $id; ?>"/>
                        <input type='hidden' name='topid' id='topid' value='<?php echo $topid; ?>'/>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">内容模型:</label>
                            <div class="col-sm-2">
                                <select name="channeltype" id="channeltype" class="form-control" onchange="ParTemplet(this)">
                                    <?php
                                    foreach ($channelArray as $k => $arr) {
                                        if ($k == $channelid) echo "    <option value='{$k}' selected>{$arr['typename']}|{$arr['nid']}</option>\r\n";
                                        else  echo "    <option value='{$k}'>{$arr['typename']}|{$arr['nid']}</option>\r\n";
                                    }
                                    ?>
                                </select>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">分类名称:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="typename" id="typename"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">排列顺序:</label>
                            <div class="col-sm-2">
                                <input name="sortrank" type="text" class="form-control" value="50"/>
                            </div>
                            <div class="col-sm-6">（由低 -&gt; 高）</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">栏目属性:</label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input name="ispart" type="radio" id="radio" value="0" checked/>
                                    最终列表栏目（允许在本栏目发布文档）
                                </label>
                                <label class="checkbox-inline i-checks">
                                    <input name="ispart" type="radio" id="radio" value="1"/>
                                    频道封面（栏目本身不允许发布文档）
                                </label>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">APP封面模板:</label>

                            <div class="col-sm-2">
                                <input type='hidden' value='{style}' name='dfstyle'/>
                                <input type="text" class="form-control" name="tempindex" value="goods_index.htm"/>
                                <!-- <input type="button" name="set1" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.tempindex');"/>
-->
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">APP列表模板:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="templist" value="goods_list.htm"/>
                                <!--  <input type="button" name="set3" value="浏览... " class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.templist');"/>-->
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">APP内容模板:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="tempgoods" value="goods_view.htm"/>
                                <!--         <input type="button" name="set4" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.temparticle');"/> -->
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">分类备注:</label>
                            <div class="col-sm-10">
                                <?php
                                echo GetEditorSimple("content", "");
                                ?>
                                可在栏目模板中用{dwt:field.content/}调用，通常用于栏目简介之类的用途。
                            </div>
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