<?php
/**
 * 栏目编辑
 *
 * @version        $Id: catalog_edit.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
$id = isset($id) ? intval($id) : 0;

/*-----------------------
function action_save()
----------------------*/
if ($dopost == "save") {
    $issend = isset($issend) ? intval($issend) : 0;
    $content = isset($content) ? $content : "";
    if ($typename == ""  ) {
        ShowMsg("名称不能为空！", "-1");
        exit;
    }

    $upquery = "UPDATE `#@__archives_type` SET
     sortrank='$sortrank',
     typename='$typename',
     issend='$issend',
     channeltype='$channeltype',
     ispart='$ispart',
     tempindex='$tempindex',
     templist='$templist',
     temparticle='$temparticle',
    ishidden='$ishidden',
     `content`='$content'
    WHERE id='$id' ";

    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存当前栏目更改时失败，请检查你的填写资料是否存在问题！", "-1");
        exit();
    }

    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives_type` SET issend='$issend' WHERE id='$topid'; ");
    }


    //修改顶级栏目时强制修改下级的 是否隐藏
    $slinks = " id IN (" . GetArchiveSonIds($id) . ")";
    if ($topid == 0 && preg_match("#,#", $slinks)) {
        $upquery = "UPDATE `#@__archives_type` SET ishidden='$ishidden' WHERE 1=1 AND $slinks";
        $dsql->ExecuteNoneQuery($upquery);
    }

    //更改子栏目属性
    if (!empty($upnext)) {
        $upquery = "UPDATE `#@__archives_type` SET
       issend='$issend',
       channeltype='$channeltype',
       tempindex='$tempindex',
       templist='$templist',
       temparticle='$temparticle',
       ishidden='$ishidden'
	   WHERE 1=1 AND $slinks";
        if (!$dsql->ExecuteNoneQuery($upquery)) {
            ShowMsg("更改当前栏目成功，但更改下级栏目属性时失败！", "-1");
            exit();
        }
    }
    ShowMsg("成功更改一个栏目！", "catalog.php");
    exit();
}//End Save Action


//读取栏目信息
$dsql->SetQuery("SELECT tp.*,ch.typename as ctypename FROM `#@__archives_type` tp LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype WHERE tp.id=$id");
$myrow = $dsql->GetOne();
$topid = $myrow['topid'];
$myrow['content'] = empty($myrow['content']) ? "" : $myrow['content'];

//读取频道模型信息
$channelid = $myrow['channeltype'];
$dsql->SetQuery("SELECT id,typename,nid FROM `#@__archives_channeltype`    ORDER BY   id");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if ($row->id == $channelid) {
        $nid = $row->nid;
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
                    <form name="form1" id='form1' action="" method="post" class="form-horizontal">

                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="hidden" name="topid" value="<?php echo $myrow['topid']; ?>"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">是否前台显示:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input type='radio' name='ishidden' value='0' class='np'<?php if ($myrow['ishidden'] == "0") echo " checked='1' "; ?>/>
                                    显示　&nbsp;
                                    <input type='radio' name='ishidden' value='1' class='np'<?php if ($myrow['ishidden'] == "1") echo " checked='1' "; ?>/>
                                    隐藏
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">内容模型:</label>

                            <div class="col-sm-2">
                                <select name="channeltype" id="channeltype" class='form-control m-b'>
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
                            <label class="col-sm-2 control-label">栏目名称:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="typename" id="typename" value="<?php echo $myrow['typename'] ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">排列顺序:</label>

                            <div class="col-sm-2">
                                <input name="sortrank" size="6" type="text" value="<?php echo $myrow['sortrank'] ?>" class="form-control"/>
                            </div>
                            <div class="col-sm-6">（由低 -&gt; 高）</div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">栏目属性:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input name="ispart" type="radio" id="radio" value="0" <?php if ($myrow['ispart'] == 0) echo " checked "; ?>/>
                                    最终列表栏目（允许在本栏目发布文档）
                                </label>
                                <label class="checkbox-inline i-checks">
                                    <input name="ispart" type="radio" id="radio2" value="1" <?php if ($myrow['ispart'] == 1) echo " checked "; ?>/>
                                    频道封面（栏目本身不允许发布文档）
                                </label>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">封面模板:</label>

                            <div class="col-sm-2">
                                <input type='hidden' value='{style}' name='dfstyle'/>
                                <input type="text" class="form-control" name="tempindex" value="<?php echo $myrow['tempindex'] ?>"/>
                                <!-- <input type="button" name="set1" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.tempindex');"/>
-->
 
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">列表模板:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="templist" value="<?php echo $myrow['templist'] ?>"/>
                                <!--  <input type="button" name="set3" value="浏览... " class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.templist');"/>-->
 
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">文章模板:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="temparticle" value="<?php echo $myrow['temparticle'] ?>"/>
                                <!--         <input type="button" name="set4" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.temparticle');"/> -->
 
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">继承选项:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input name="upnext" type="checkbox" id="upnext" value="1" class="np"/>
                                    同时更改下级栏目的:是否需要审核、内容模型、模板风格的属性 </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">栏目内容:
                            </label>

                            <div class="col-sm-10">
                                <?php
                                echo GetEditorSimple("content", $myrow['content']);
                                ?>
                                可在栏目模板中用{dwt:field.content/}调用，通常用于栏目简介之类的用途。
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
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
    var channelArray = new Array();
    <?php
    $i = 0;
    foreach($channelArray as $k=>$arr)
    {
    echo "channelArray[$k] = \"{$arr['nid']}\";\r\n";
    }
    ?>


    function SelectTemplets(fname) {
        if (document.all) {
            var posLeft = window.event.clientY - 100;
            var posTop = window.event.clientX - 400;
        }
        else {
            var posLeft = 100;
            var posTop = 100;
        }
        window.open("../include/dialog/select_templets.php?f=" + fname, "poptempWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=" + posLeft + ", top=" + posTop);
    }


    function ParTemplet(obj) {
        var sevvalue = channelArray[obj.value];
        var tobj = document.getElementById('smclass');
        var tempindex = document.getElementsByName('tempindex');
        var templist = document.getElementsByName('templist');
        var temparticle = document.getElementsByName('temparticle');
        //默认模板路径
        /*  var dfstyle = document.getElementsByName('dfstyle');
         var dfstyleValue = dfstyle[0].value;
         tempindex[0].value = dfstyleValue+"/index_"+sevvalue+".htm";
         templist[0].value = dfstyleValue+"/list_"+sevvalue+".htm";
         temparticle[0].value = dfstyleValue+"/article_"+sevvalue+".htm";
         */
    }

    $(document).ready(function () {
        $(".i-checks").iCheck({
            checkboxClass: "icheckbox_square-green",
            radioClass: "iradio_square-green",
        })
        $("#form1").validate({
            rules: {
                depid: {isIntGtZero: !0}
            },
            messages: {
                depid: {isIntGtZero: "请选择公司"}
            }
        })

        $("#form1").validate({
            rules: {
                typename: {required: !0}
            },
            messages: {
                typename: {required: "请填写栏目名称"}
            }
        })
    });


</script>


</body>
</html>
