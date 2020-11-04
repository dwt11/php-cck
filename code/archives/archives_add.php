<?php
/**
 * 文档发布-非新闻的其他模型 都调用这个
 *
 * @version        $Id: archives_add.php 1 8:26 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/fields.func.php");
require_once("archives.functions.php");

if (empty($dopost)) $dopost = '';

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
    $userid = $GLOBALS['CUSERLOGIN']->getUserId();

    //缩略图
    $litpic = GetDDImgFromBody($body);//如果内容中有图片 则提取第一个为缩略图

    $issend = 0;//是否审核 这个功能没有启用 以后再用

    //生成文档ID
    $arcID = GetArchiveIndexKey($issend, $typeid, $sortrank, $channelid, $senddate, $userid);

    if (empty($arcID)) {
        ShowMsg("无法获得主键，因此无法进行后续操作！", "-1");
        exit();
    }

    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dwt_addonfields)) {
        $addonfields = explode(';', $dwt_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') continue;
                $vs = explode(',', $v);

                ${$vs[0]} = GetFieldValue(${$vs[0]}, $vs[1]);
                // dump($vs[0]."----".${$vs[0]});
                $inadd_f .= ",`{$vs[0]}`";    //字段名称
                $inadd_v .= ", '" . ${$vs[0]} . "'";//字段的值
            }
        }
    }

    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag . ',p');
    }


    //保存到主表
    $query = "INSERT INTO `#@__archives`(id,typeid,sortrank,flag,channelid,deptype,click,title, color,senddate,userid,ispost,userhistory,litpic,worktype)
    VALUES ('$arcID','$typeid','$sortrank','$flag','$channelid','$deptype','$click','$title',    '$color','$senddate','$userid','$ispost','','$litpic','$worktype');";
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("把数据保存到数据库主表 `#@__archives` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
        exit();
    }

    //保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__archives_channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到当前模型[{$channelid}]的主表信息，无法完成操作！。", "javascript:;");
        exit();
    }
    $query = "INSERT INTO `{$addtable}`(archivesid,typeid{$inadd_f}) Values('$arcID','$typeid'{$inadd_v})";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("添加信息成功！", $$ENV_GOBACK_URL);
    exit();

}
if ($dopost != 'save') {
    require_once('catalogLinkOption.class.php');   //获取栏目的SELECT
    require_once(DWTINC . '/dwttag.class.php');
    $channelid = isset($channelid) ? intval($channelid) : 0;
    $typeid = empty($typeid) ? 0 : intval($typeid);


    //获得频道模型ID
    if ($typeid > 0 && $channelid == 0) {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__archives_type` WHERE id='$typeid'; ");
        $channelid = $row['channeltype'];
    } else {
        if ($channelid == 0) {
//            ShowMsg('无法识别模型信息，因此无法操作！', '-1');
//            exit();
            $channelid = 1;
        }
    }

    //获得频道模型信息
    $cInfos = $dsql->GetOne(" Select * From  `#@__archives_channeltype` WHERE id='$channelid' ");
    //dump(" SELECT * FROM  `#@__archives_channeltype` WHERE id='$channelid' ");
    //$channelid = $cInfos['id'];
    //获取文章最大id以确定当前权重
    $maxWright = $dsql->GetOne("SELECT COUNT(*) AS cc FROM #@__archives");
    $tl = new TypeLink($typeid);
    $positionname = $tl->GetArchivePositionName();

    $optionarr = $tl->GetArchiveOptionArray($typeid);  //供栏目选择
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
                    <form id="form1" name="form1" action="archives_add.php?typeid=<?php echo $typeid ?>" enctype="multipart/form-data" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="channelid" value="<?php echo $channelid ?>"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">标题：</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="title" id="title">
                            </div>
                        </div>

             <!--           <div class="form-group">
                            <label class="col-sm-2 control-label">自定义属性：</label>

                            <div class="col-sm-6">
                                <?php
/*                                $dsql->SetQuery("SELECT * FROM `#@__archives_arcatt`   ORDER BY   sortid ASC");
                                $dsql->Execute();
                                while ($trow = $dsql->GetObject()) {
                                    echo "<label class='checkbox-inline i-checks'>
                                            <input   type='checkbox' name='flags[]' id='flags{$trow->att}' value='{$trow->att}'  />{$trow->attname}[{$trow->att}]
                                            </label>";
                                }
                                */?>
                            </div>
                        </div>-->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">栏目：</label>

                            <div class="col-sm-2">
                                <?php
                                $disabled = "";
                                /*if ($GLOBALS['CUSERLOGIN']->getUserType() != 10) {
                                    $disabled = "disabled=\"disabled\"";
                                    echo "<input type=\"hidden\" name=\"typeid\" value=\"$typeid\" />";
                                }*/

                                echo "<select name='typeid' id='typeid'  class='form-control m-b'  $disabled>\r\n";
                                echo "<option value='0' >请选择栏目...</option>\r\n";
                                echo $optionarr;
                                echo "</select>";
                                ?>
                            </div>
                        </div>


                       <!-- <div class="form-group">
                            <label class="col-sm-2 control-label">评论选项：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='ispost' class='np' value='0'/>允许评论
                                </label>
                                <label class='checkbox-inline i-checks'>
                                    <input type='radio' name='ispost' class='np' value='1' checked='1'/>禁止评论
                                </label>
                            </div>
                        </div>-->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">浏览次数：</label>

                            <div class="col-sm-2">
                                <input type="number" class="form-control" name='click' value='0'/>
                            </div>
                        </div>
                  <!--      <div class="form-group">
                            <label class="col-sm-2 control-label">文章排序：</label>

                            <div class="col-sm-2">
                                <select name="sortup" id="sortup" class='form-control m-b'>

                                    <option value="0">正常排序</option>
                                    <option value="7">置顶一周</option>
                                    <option value="30">置顶一个月</option>
                                    <option value="90">置顶三个月</option>
                                    <option value="180">置顶半年</option>
                                    <option value="360">置顶一年</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">阅读权限：</label>

                            <div class="col-sm-2">
                                <label class='checkbox-inline i-checks'><input type='radio' name='deptype' class='np' value='0' checked='1'/>
                                    正常浏览
                                </label>

                                <label class='checkbox-inline i-checks'><input type='radio' name='deptype' class='np' value='-1'/>
                                    登录后浏览
                                </label>
                            </div>
                        </div>
-->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">更新时间：</label>

                            <div class="col-sm-2">
                                <?php
                                $nowtime = GetDateTimeMk(time());
                                ?>
                                <input type="text" class="form-control  Wdate " style="max-width: 185px" name="senddate" value="<?php echo $nowtime; ?>"  onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd H:m:ss'})"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">详细描述:</label>
                            <div class="col-sm-2">
                                <?php echo GetEditor("body", ''); ?>
                            </div>
                        </div>
                        <input type='hidden' name='dwt_addonfields' value="body,htmltext;">


                        <div class="form-group">
                            <div class="text-center">
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


<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>

<!--照片上传-->
<script src="../ui/js/plugins/webuploader/webuploader.min.js"></script>
<link src="../ui/css/plugins/webuploader/webuploader.css">

<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    });
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
