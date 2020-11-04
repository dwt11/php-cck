<?php
/**
 * 商品发布
 *
 * @version        $Id: device_add.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/fields.func.php");
require_once(DWTINC . '/field.class.php');
require_once("device.functions.php");
require_once('catalog.class.php');
//require_once(DWTINC . "/dwttag.class.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) $dopost = '';
if (empty($typeid)) $typeid = '';
if ($typeid == "") {
    ShowMsg("请指定分类！", "-1");
    exit();
}


$row = $dsql->GetOne("SELECT channeltype FROM `#@__device_type` WHERE id='$typeid'; ");
$channelid = $row['channeltype'];

if (empty($channelid) || !$channelid > 0) {
    ShowMsg("获取模型出错！", "-1");
    exit();
}

//获取模型的相关参数
$sql = "SELECT fieldset,addtable,tempadd FROM `#@__sys_channeltype` WHERE id='$channelid'";
$cts = $dsql->GetOne($sql);
$addtable = trim($cts['addtable']);
$addtemp = trim($cts['tempadd']);
if (empty($addtable)) {
    ShowMsg("没找到当前模型[{$channelid}]的附加表信息，无法完成操作！。", "-1");
    exit();
}


/*--------------------------------
function __save(){  }
-------------------------------*/


if ($dopost == 'save') {

    if (!CheckIsPart($typeid)) {
        ShowMsg("你所选择的分类不可以添加内容，请选择白色的选项！", "-1");
        exit();
    }
    if (trim($devicename) == '') {
        ShowMsg('名称不能为空', '-1');
        exit();
    }
    if (trim($devicecode) == '') {
        ShowMsg('编号不能为空', '-1');
        exit();
    }
    if (empty($description)) $description = "";
    if (empty($standard)) $standard = "";
    if (empty($chargeunit)) $chargeunit = "";
    if (empty($stocknumb)) $stocknumb = "";


    //判断修商品编号 是否冲突
    $query = "SELECT devicecode FROM `#@__device` WHERE devicecode='$devicecode' ";
    $goodRow = $dsql->GetOne($query);
    if (is_array($goodRow)) {
        ShowMsg("编号已经存在,请修改!", "-1");
       exit();
    }


    //对保存的内容进行处理
    $pubdate = time();
    $devicename = htmlspecialchars($devicename);
    $userid = $CUSERLOGIN->userID;
    //$chargeunit='';






    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dwt_addonfields)) {
        $addonfields = explode(';', $dwt_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') continue;
                $vs = explode(',', $v);
                if (empty(${$vs[0]})) ${$vs[0]} = '';
                ${$vs[0]} = GetFieldValue(${$vs[0]}, $vs[1]);
                //dump($vs[0]."----".${$vs[0]});
                $inadd_f .= ",`{$vs[0]}`";    //字段名称
                $inadd_v .= ", '" . ${$vs[0]} . "'";//字段的值   //其他的字段 自动处理
            }
        }
    }
    //dump($inadd_v);


    //保存到主表
    $query = "INSERT INTO `#@__device`(`typeid`, `devicename`, `devicecode`,`standard`, `chargeunit` ,`sortrank`, `senddate`, `pubdate`, `userid`, `litpic`, `status`, `click`)
    VALUES ('$typeid','$devicename','$devicecode','$standard','$chargeunit','$pubdate','$pubdate','$pubdate','$userid','$pic','0','0');";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        ShowMsg("把数据保存到数据库主表 `#@__device` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
        exit();
    } else {

        $deviceid = $dsql->GetLastID();
    }

    //保存到附加表

    if ($addtable != '') {
        $query = "INSERT INTO `{$addtable}`(deviceid{$inadd_f}) Values('$deviceid'{$inadd_v})";
        // dump($query);
        if (!$dsql->ExecuteNoneQuery($query)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__device` WHERE id='$deviceid'");
            ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
            exit();
        }
    }


    ShowMsg("添加信息成功！", "device.php?typeid=$typeid");
    exit();
}
if ($dopost != 'save') {
    //获取模型ID
    $row = $dsql->GetOne("SELECT channeltype FROM `#@__device_type` WHERE id='$typeid'");
    $channeltypeid = $row['channeltype'];

    $af = new AutoField($channeltypeid);
    $tl = new DeviceTypeUnit($typeid);
    $positionname = $tl->GetPositionName();    //当前分类名称
    $optionarr = $tl->GetDeviceTypeOptionS($typeid);  //搜索表单的分类值//GetOptionArray


    // 自动获取 商品编号
    $deviceCode = getDeviceCode($positionname);



    $lyht_option="";
    $query3 = "SELECT title,id  FROM #@__lyht ORDER BY id DESC  ";
    $dsql->SetQuery($query3);
    $dsql->Execute("170131");
    while ($row1 = $dsql->GetArray("170131")) {
        $lyht_option.="<option value='{$row1["id"]}' >{$row1["title"]}</option>";
    }


    $dpl = new DWTTemplate();
    //模板
    if (empty($addtemp)) $addtemp = 'device_add.htm';
    //$addtemp = 'device_add.htm';
    $tpl = $addtemp;
    //dump($tpl);
    $dpl->LoadTemplate($tpl);
    $dpl->display();
}

