<?php
/**
 * 商品编辑
 *
 * @version        $Id: goods_edit.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/fields.func.php");
require_once(DWTINC . '/field.class.php');
require_once("goods.functions.php");
require_once('catalog.class.php');
//require_once(DWTINC . "/dwttag.class.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) $dopost = '';


$id = isset($id) && is_numeric($id) ? $id : 0;
/*--------------------------------
function __save(){  }
-------------------------------*/


//读取归档信息,显示用,然后保存的时候要判断用户是否修改了编号,如果修改了编号要判断新的编号是否与数据库冲突
$query = "SELECT goods.*,gt.channeltype as channelid FROM `#@__goods` goods
    LEFT JOIN `#@__goods_type` gt ON gt.id=goods.typeid
 WHERE goods.id='$id' ";
$goodRow = $dsql->GetOne($query);
if (!is_array($goodRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
$goodsoldcode = $goodRow['goodscode'];//用于保存的时候判断用户是否修改了编号,如果修改了编号要判断新的编号是否与数据库冲突
$typeid_old = $goodRow['typeid'];
$channelid = $goodRow['channelid'];
if (empty($typeid_old)) {
    ShowMsg("请指定商品的分类！", "-1");
    exit();
}
if (empty($channelid) || !$channelid > 0) {
    ShowMsg("获取模型出错！", "-1");
    exit();
}
//获取模型的相关参数
$sql = "SELECT id,fieldset,addtable,tempedit FROM `#@__sys_channeltype` WHERE id='$channelid'";
$cts = $dsql->GetOne($sql);
$addtable = trim($cts['addtable']);
$edittemp = trim($cts['tempedit']);
if (empty($addtable)) {
    ShowMsg("没找到当前模型[{$channelid}]的主表信息，无法完成操作！。", "-1");
    exit();
}


/*--------------------------------
function __save(){  }
-------------------------------*/

if ($dopost == 'save') {


    if (CheckIsPart($typeid)) {
        ShowMsg("你所选择的分类不可以添加内容，请选择白色的选项！", "-1");
        exit();
    }
    if (trim($goodsname) == '') {
        ShowMsg('名称不能为空', '-1');
        exit();
    }
    if (trim($goodscode) == '') {
        ShowMsg('编号不能为空', '-1');
        exit();
    }

    if (empty($description)) $description = "";
    if (empty($standard)) $standard = "";
    if (empty($chargeunit)) $chargeunit = "";
    if (empty($stocknumb)) $stocknumb = "";


    //判断修改后的商品编号 是否冲突
    if ($goodscode != $goodsoldcode) {
        $query = "SELECT goodscode FROM `#@__goods` WHERE goodscode='$goodscode' ";
        $goodRow = $dsql->GetOne($query);
        if (is_array($goodRow)) {
            ShowMsg("新修改的商品编号已经存在,请修改!", "-1");
            exit();
        }
    }

    //对保存的内容进行处理
    $pubdate = time();
    $goodsname = htmlspecialchars($goodsname);
    $userid = $CUSERLOGIN->userID;

    $price100=0;
    //如果是旅游产品  车辆租赁 则 价格=金币+积分
    if($channelid==2||$channelid==3) {
        $jbnum = $jbnum * 100;
        $jfnum = $jfnum * 100;//此参数在附加 表时保存
        $price100 = $jbnum + $jfnum;
    }else{
        $price100=$price*100;//现金基准的商品价格
    }


    if ($pic != '') $sql_pic = "    litpic='$pic',";







    //更新数据库的SQL语句
    $query = "UPDATE #@__goods SET
    typeid='$typeid',
    goodsname='$goodsname',
    goodscode='$goodscode',
    standard='$standard',
    chargeunit='$chargeunit',
    price='$price100',
    sortrank='$pubdate',
    pubdate='$pubdate',
    weight='$weight',
    $sql_pic
    userid='$userid'
    WHERE id='$id'; ";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('更新数据库goods表时出错，请检查', -1);
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
                if (empty(${$vs[0]})) ${$vs[0]} = '';
                ${$vs[0]} = GetFieldValue(${$vs[0]}, $vs[1], $id);
                $inadd_f .= "`{$vs[0]}` = '{${$vs[0]}}',";
            }
        }
    }
//    dump($inadd_f);
    $inadd_f = rtrim($inadd_f, ",");//清除右侧空格

    if ($addtable != '' && $inadd_f != '') {
        $iquery = "UPDATE `$addtable` SET {$inadd_f} WHERE goodsid='$id'";
        //dump($iquery);
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg("更新附加表 `$addtable`  时出错，请检查原因！", "javascript:;");
            exit();
        }
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("更新信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost != 'save') {


    $addRowAddtable = $dsql->GetOne("SELECT * FROM `$addtable` WHERE goodsid='$id'");
    //dump($addRowAddtable);
    if (!is_array($addRowAddtable)) {
        ShowMsg("读取附加表信息出错!", "javascript:;");
        exit();
    }

    //dump($addRowAddtable["lyhtid"]);

    //获取模型ID
    $row = $dsql->GetOne("SELECT channeltype FROM `#@__goods_type` WHERE id='$typeid_old'");
    $channeltypeid = $row['channeltype'];

    $af = new AutoField($channeltypeid);


    $tl = new GoodsTypeUnit($typeid_old);
    $positionname = $tl->GetPositionName();    //当前分类名称
    $optionarr = $tl->GetGoodsTypeOptionS();  //搜索表单的分类值//GetOptionArray


   /* $lyht_option="";
    $query3 = "SELECT title,id  FROM #@__lyht ORDER BY id DESC  ";
    $dsql->SetQuery($query3);
    $dsql->Execute("170131");
    while ($row1 = $dsql->GetArray("170131")) {
        $selected="";
        if($addRowAddtable["lyhtid"]==$row1["id"])$selected=" selected ";
        $lyht_option.="<option value='{$row1["id"]}' $selected>{$row1["title"]}</option>";
    }*/


    $dpl = new DWTTemplate();
    //模板
    if (empty($edittemp)) $edittemp = 'goods_edit.htm';
     //$edittemp = 'goods_edit.htm';
    $tpl = $edittemp;
    //dump($tpl);
    $dpl->LoadTemplate($tpl);
    $dpl->display();
}
