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
require_once(DWTINC . "/fields.func.php");
require_once(DWTINC . '/field.class.php');
require_once("archives.functions.php");
if (empty($dopost)) $dopost = '';


$id = isset($id) && is_numeric($id) ? $id : 0;
/*--------------------------------
function __save(){  }
-------------------------------*/
if ($dopost == 'save') {
    $flag = isset($flags) ? join(',', $flags) : '';
    //$worktype = isset($worktype) ? join(',', $worktype) : '';    //141221增加
    $worktype = "";
    $ispost = isset($ispost) && $ispost == 1 ? 1 : 0;

    if (empty($typeid)) {
        ShowMsg("请指定文档的栏目！", "-1");
        exit();
    }
    if (empty($channelid)) {
        ShowMsg("文档为非指定的类型，请检查你发布内容的表单是否合法！", "-1");
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg("你所选择的栏目不可以添加内容，请选择白色的选项！", "-1");
        exit();
    }
    if (trim($title) == '') {
        ShowMsg('标题不能为空', '-1');
        exit();
    }

    //对保存的内容进行处理
    $senddate = GetMkTime($senddate);
    $sortrank = AddDay($senddate, $sortup);
    $title = dwt_htmlspecialchars($title);
    $color = "";
    //$userid = $GLOBALS['CUSERLOGIN']->getUserId();



    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dwt_addonfields)) {
        $addonfields = explode(';', $dwt_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') continue;
                $vs = explode(',', $v);

                ${$vs[0]} = GetFieldValue(${$vs[0]}, $vs[1], $id);

                $inadd_f .= ",`{$vs[0]}` = '" . ${$vs[0]} . "'";
            }
        }
    }
    //缩略图
    //dump($body);
    $litpic = GetDDImgFromBody($body);
    //处理图片文档的自定义属性
     if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag . ',p');
    }

    //更新数据库的SQL语句
    $query = "UPDATE #@__archives SET
            typeid='$typeid',
            sortrank='$sortrank',
            click='$click',
            title='$title',
            color='$color',
            deptype='$deptype',
            litpic='$litpic',
            senddate='$senddate',
            ispost='$ispost',
            worktype='$worktype'
            WHERE id='$id'; ";
//dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据库archives表时出错，请检查', -1);
        exit();
    }

//dump($inadd_f);

    $cts = $dsql->GetOne("SELECT addtable FROM `#@__archives_channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $iquery = "UPDATE `$addtable` SET typeid='$typeid'{$inadd_f} WHERE archivesid='$id'";
       // dump($iquery);
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg("更新附加表 `$addtable`  时出错，请检查原因！", "javascript:;");
            exit();
        }
    }

    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost != 'save') {
    require_once('catalogLinkOption.class.php');   //获取栏目的SELECT
    require_once(DWTINC . "/dwttag.class.php");
    $channelid = isset($channelid) ? intval($channelid) : 0;


    //读取归档信息
    $query = "SELECT ch.typename AS channelname,arc.*
    FROM `#@__archives` arc
    LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid
     WHERE arc.id='$id' ";
    $arcRow = $dsql->GetOne($query);
    if (!is_array($arcRow)) {
        ShowMsg("读取档案基本信息出错!", "-1");
        exit();
    }
    $query = "SELECT * FROM `#@__archives_channeltype` WHERE id='" . $arcRow['channelid'] . "'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg("读取频道配置信息出错!", "javascript:;");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT * FROM `$addtable` WHERE archivesid='$id'");
    //dump("SELECT * FROM `$addtable` WHERE archivesid='$id'");
    if (!is_array($addRow)) {
        ShowMsg("读取附加表信息出错!", "javascript:;");
        exit();
    }
    $channelid = $arcRow['channelid'];
    $typeid = $arcRow['typeid'];


   // $af = new AutoField($channelid);

    $tl = new TypeLink($typeid);
    $positionname = $tl->GetArchivePositionName();
    $optionarr = $tl->GetArchiveOptionArray($arcRow['typeid']);  //搜索表单的值
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
                    <form id="form1" name="form1" action="" enctype="multipart/form-data" method="post" class="form-horizontal">

                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="channelid" value="<?php echo $channelid ?>"/>
                        <input type="hidden" name="id" value="<?php echo $id ?>"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">标题：</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="title" id="title" value="<?php echo $arcRow['title']; ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">栏目：</label>

                            <div class="col-sm-2">
                                <?php
                                $disabled = "";
                                if ($GLOBALS['CUSERLOGIN']->getUserType() != 10) {
                                    $disabled = "disabled=\"disabled\"";
                                    echo "<input type=\"hidden\" name=\"typeid\" value=\"$typeid\" />";
                                }

                                echo "<select name='typeid' id='typeid'  class='form-control m-b'  $disabled>\r\n";
                                if ($arcRow["typeid"] == "0") echo "<option value='0' selected>请选择栏目...</option>\r\n";
                                echo $optionarr;
                                echo "</select>";
                                ?>
                            </div>
                        </div>


<!--                        <div class="form-group">
                            <label class="col-sm-2 control-label">评论选项：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='ispost' class='np' value='0'<?php /*if ($arcRow['ispost'] == 0) echo " checked"; */?>/>
                                    允许评论
                                </label>
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='ispost' class='np' value='1'<?php /*if ($arcRow['ispost'] == 1) echo " checked "; */?>/>
                                    禁止评论
                                </label>
                            </div>
                        </div>
-->                        <div class="form-group">
                            <label class="col-sm-2 control-label">浏览次数：</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name='click' value='<?php echo $arcRow['click']; ?>'/></td>
                            </div>
                        </div>
<!--                        <div class="form-group">
                            <label class="col-sm-2 control-label">文章排序：</label>

                            <div class="col-sm-2">
                                <select name="sortup" id="sortup" class='form-control m-b'>
                                    <?php
/*                                    $subday = SubDay($arcRow["sortrank"], $arcRow["senddate"]);
                                    echo "<option value='0'>正常排序</option>\r\n";
                                    if ($subday > 0) echo "<option value='$subday' selected>置顶 $subday 天</option>\r\n";
                                    */?>
                                    <option value="7">置顶一周</option>
                                    <option value="30">置顶一个月</option>
                                    <option value="90">置顶三个月</option>
                                    <option value="180">置顶半年</option>
                                    <option value="360">置顶一年</option>
                                </select></td>
                            </div>
                        </div>
-->                       <!-- <div class="form-group">
                            <label class="col-sm-2 control-label">阅读权限：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'><input type='radio' name='deptype' class='np' value='0'<?php /*if ($arcRow['deptype'] == 0) echo " checked "; */?>/>
                                    正常浏览
                                </label>

                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='deptype' class='np' value='-1'<?php /*if ($arcRow['deptype'] == -1) echo " checked='1' "; */?>/>
                                    登录后浏览
                                </label>
                            </div>
                        </div>-->

                        <div class="form-group">
                            <label class="col-sm-2 control-label">更新时间：</label>

                            <div class="col-sm-2">
                                <?php
                                if ($arcRow['senddate'] == "") {
                                    $nowtime = GetDateTimeMk(time());
                                } else {
                                    $nowtime = GetDateTimeMk($arcRow['senddate']);
                                }

                                ?>
                                <input type="text" class="form-control Wdate " style="max-width: 185px" name="senddate" value="<?php echo $nowtime; ?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd H:m:ss'})"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">详细描述:</label>
                            <div class="col-sm-2">
                                <?php echo GetEditor("body", $addRow['body']); ?>
                            </div>
                        </div>
                        <input type='hidden' name='dwt_addonfields' value="body,htmltext;">



                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2 ">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                                <button class="btn btn-white" type="submit">取消</button>
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
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>

<!--照片上传-->
<script src="../ui/js/plugins/webuploader/webuploader.min.js"></script>
<link src="../ui/css/plugins/webuploader/webuploader.css">

<script>


    $().ready(function () {
        $("#form1").validate({
            rules: {
                title: {required: !0},

                typeid: {isIntGtZero: !0}
            },
            messages: {
                title: {required: "请填写标题"},

                typeid: {isIntGtZero: "请选择栏目"}
            }
        })
    });
</script>


</body>
</html>