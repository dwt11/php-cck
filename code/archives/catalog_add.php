<?php
/**
 * 栏目添加
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

$dsql->SetQuery("SELECT id,typename,nid FROM `#@__archives_channeltype`     ORDER BY   id");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if ($row->id == $channelid) {
        $nid = $row->nid;
    }
}


if (empty($depid)) $depid = "0";//如果包含多个分厂则先选择分厂
if ($id == 0 && empty($dopost) && $GLOBAMOREDEP) $dopost = "form1";//如果包含多个分厂则先选择分厂

if ($id != 0 && empty($dopost)) $dopost = "form2"; //不包含多个分厂则直接添加权限组
if (empty($dopost) && !$GLOBAMOREDEP) $dopost = "form2"; //不包含多个分厂则直接添加权限组


if ($dopost == 'save') {

    if ($topid == 0 && $reid > 0) $topid = $reid;
    $issend = isset($issend) ? intval($issend) : 0;
    $content = isset($content) ? $content : "";

    if ($typename == ""  ) {
        ShowMsg("名称不能为空！", "-1");
        exit;
    }

    $in_query = "INSERT INTO `#@__archives_type`(reid,topid,sortrank,typename,issend,channeltype,
    ispart,tempindex,templist,temparticle,modname,ishidden,`content`)
    VALUES('$reid','$topid','$sortrank','$typename','$issend','$channeltype',
    '$ispart','$tempindex','$templist','$temparticle','default','$ishidden','$content')";

    if (!$dsql->ExecuteNoneQuery($in_query)) {
        ShowMsg("保存目录数据时失败，请检查你的填写资料是否存在问题！", "-1");
        exit();
    }
    //UpDateCatCache();
    if ($reid > 0) {
        PutCookie('lastCid', GetArchiveTopid($reid), 3600 * 24, '/');
    }


    $newid = $dsql->GetLastID();
    // dump($GLOBAMOREDEP);
    // dump($depid);
    if ($GLOBAMOREDEP && $depid != "" && $depid != 0) { //将新的权限值保存到dep_plus中
        $row = $dsql->GetOne("SELECT * FROM #@__emp_dep_plus WHERE depid='$depid'");
        if (!is_array($row)) {
            $query = "INSERT INTO `#@__emp_dep_plus` (`depid`, `archivesids`) VALUES ('$depid', '$newid')";

            // dump($query);
            $dsql->ExecuteNoneQuery($query);
        } else {

            $query = "UPDATE `#@__emp_dep_plus` SET archivesids=CONCAT(IFNULL(archivesids,''),',$newid') where depid='$depid'";
            $dsql->ExecuteNoneQuery($query);
        }

    }
    //dump($query);


    ShowMsg("成功创建一个分类！", "catalog.php");
    exit();

}//End dopost==save


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


                    <?php if (!empty($dopost) && $dopost == "form1") { ?>


                        <form name='form1' id='form1' action='catalog_add.php' method='post' class="form-horizontal">
                            <input type='hidden' name='dopost' value='form2'>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">顶级部门名称:</label>

                                <div class="col-sm-2">
                                    <?php
                                    if ($GLOBALS['CUSERLOGIN']->getUserType() != 10 && $GLOBAMOREDEP) {
                                        //???????????????这里要取 部门管理员登录后的顶级部门ID
                                        $depid = 0;
                                    } else {
                                        $depOptions = GetDepOnlyTopOptionList($depid);
                                        echo "<select name='depid' id='depid'  class='form-control m-b'  >\r\n";
                                        echo "<option value='0'>请选择部门...</option>\r\n";
                                        echo $depOptions;
                                        echo "</select>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <button class="btn btn-primary" type="submit">下一步</button>
                                </div>
                            </div>
                        </form>

                    <?php }
                    if (!empty($dopost) && $dopost == "form2") {
                        ?>


                    <form name="form2" id="form2" action="catalog_add.php" method="post" class="form-horizontal">
                            <input type="hidden" name="dopost" value="save"/>
                            <input type="hidden" name="reid" id="reid" value="<?php echo $id; ?>"/>
                            <input type='hidden' name='topid' id='topid' value='<?php echo $topid; ?>'/>
                            <input type='hidden' name='depid' value='<?php echo $depid; ?>'>
                            <?php

                            if ($GLOBAMOREDEP && $id == 0) {


                                //150611如果是多级部门则获取部门的ID和NAME
                                $sql1 = "SELECT dep_name FROM  `#@__emp_dep` d  WHERE  dep_id=" . $depid;
                                $dsql->SetQuery($sql1);
                                $dsql->Execute(1);
                                $row1 = $dsql->GetObject(1);
                                if ($row1 != "") {
                                    $depname = $row1->dep_name;
                                }


                                echo "<div class='form-group'>
                                        <label class='col-sm-2 control-label'>所属顶级部门:</label>
                                        <div class='col-sm-2'>
                                                            <p  class='form-control-static'>$depname</p>
                                        </div>
                                    </div>";
                            } ?>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">是否前台显示:</label>

                                <div class="col-sm-10">
                                    <label class="checkbox-inline i-checks">
                                        <input type='radio' name='ishidden' value='0' class='np' checked="checked"/>
                                        显示　&nbsp;
                                        <input type='radio' name='ishidden' value='1' class='np'/>
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
                                    <input type="text" class="form-control" name="tempindex" value="article_index.htm"/>
                                    <!-- <input type="button" name="set1" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.tempindex');"/>
 -->
                                
                                </div>
                            </div>
                            <div class="form-group">
                            <label class="col-sm-2 control-label">APP列表模板:</label>

                                <div class="col-sm-2">
                                    <input type="text" class="form-control" name="templist" value="archives_list.htm"/>
                                    <!--  <input type="button" name="set3" value="浏览... " class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.templist');"/>-->
                                
                                </div>
                            </div>
                            <div class="form-group">
                            <label class="col-sm-2 control-label">APP内容模板:</label>

                                <div class="col-sm-2">
                                    <input type="text" class="form-control" name="temparticle" value="archives_view.htm"/>
                                    <!--         <input type="button" name="set4" value="浏览..." class="coolbg np" style="width:60px" onclick="SelectTemplets('form1.temparticle');"/> -->
                                
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">栏目内容:
                                </label>

                                <div class="col-sm-10">
                                    <?php
                                    echo GetEditorSimple("content", "");
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
                    <?php } ?>

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
    foreach ($channelArray as $k => $arr) {
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

        $("#form2").validate({
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
