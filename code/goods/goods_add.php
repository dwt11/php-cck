<?php
/**
 * 商品发布
 *
 * @version        $Id: goods_add.php 1 8:26 2010年7月12日
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
if (empty($typeid)) $typeid = '';
if ($typeid == "") {
    ShowMsg("请指定商品的分类！", "-1");
    exit();
}


$row = $dsql->GetOne("SELECT channeltype FROM `#@__goods_type` WHERE id='$typeid'; ");
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


    //判断修商品编号 是否冲突
    $query = "SELECT goodscode FROM `#@__goods` WHERE goodscode='$goodscode' ";
    $goodRow = $dsql->GetOne($query);
    if (is_array($goodRow)) {
        ShowMsg("商品编号已经存在,请修改!", "-1");
       exit();
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





    //保存到主表
    $query = "INSERT INTO `#@__goods`(`typeid`, `goodsname`, `goodscode`,`standard`, `chargeunit`, `price`, `stocknumb` ,`sortrank`, `senddate`, `pubdate`, `userid`, `litpic`, `status`, `click`, `weight`)
    VALUES ('$typeid','$goodsname','$goodscode','$standard','$chargeunit','$price100','$stocknumb','$pubdate','$pubdate','$pubdate','$userid','$pic','0','0','$weight');";
    //dump($query);
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        ShowMsg("把数据保存到数据库主表 `#@__goods` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
        exit();
    } else {

        $goodsid = $dsql->GetLastID();
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
                ${$vs[0]} = GetFieldValue(${$vs[0]}, $vs[1]);
                //dump($vs[0]."----".${$vs[0]});
                $inadd_f .= ",`{$vs[0]}`";    //字段名称
                $inadd_v .= ", '" . ${$vs[0]} . "'";//字段的值   //其他的字段 自动处理
            }
        }
    }
    //dump($inadd_v);

    //保存到附加表

    if ($addtable != '') {
        $query = "INSERT INTO `{$addtable}`(goodsid{$inadd_f}) Values('$goodsid'{$inadd_v})";
        // dump($query);
        if (!$dsql->ExecuteNoneQuery($query)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__goods` WHERE id='$goodsid'");
            ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错。" . str_replace('"', '', $gerr), "javascript:;");
            exit();
        }
    }


  ShowMsg("添加信息成功！", "goods.php?typeid=$typeid");
    exit();
}
if ($dopost != 'save') {
    //获取模型ID
    $row = $dsql->GetOne("SELECT channeltype FROM `#@__goods_type` WHERE id='$typeid'");
    $channeltypeid = $row['channeltype'];

    $af = new AutoField($channeltypeid);
    $tl = new GoodsTypeUnit($typeid);
    $positionname = $tl->GetPositionName();    //当前分类名称
    $optionarr = $tl->GetGoodsTypeOptionS($typeid);  //搜索表单的分类值//GetOptionArray


    // 自动获取 商品编号，未做完170113
    $toptypeid=GetTypeTopId($typeid);
    $goodstypename=GetGoodsTypeName($toptypeid);

    $goodsCode = getGoodsCode($goodstypename);



    $lyht_option="";
    $query3 = "SELECT title,id  FROM #@__lyht ORDER BY id DESC  ";
    $dsql->SetQuery($query3);
    $dsql->Execute("170131");
    while ($row1 = $dsql->GetArray("170131")) {
        $lyht_option.="<option value='{$row1["id"]}' >{$row1["title"]}</option>";
    }


    $dpl = new DWTTemplate();
    //模板
    if (empty($addtemp)) $addtemp = 'goods_add.htm';
    //$addtemp = 'goods_add.htm';
    $tpl = $addtemp;
    //dump($tpl);
    $dpl->LoadTemplate($tpl);
    $dpl->display();
}




function GetTypeTopId($typeid)
{
    global $dsql;
    //global $sunDepIdArray;
    //global $stepTotal;  //总级数
    //$stepTotal = 0;

    $str = $typeid;

        $questr1 = "SELECT reid,id FROM `#@__goods_type` WHERE id='$typeid'";
        //echo $questr1;
        $rowarc1 = $dsql->GetOne($questr1);
        if (is_array($rowarc1))
        {    if ($rowarc1['reid'] != 0) {
            $str = logic_GetTopTypeId($rowarc1['reid'],  1);
        }else{
            $str = $typeid;

        }
            //if ($sunDepIdArray != "") $sunDepIdArray[$stepTotal] = $sunDepId;
        }


    return $str;
}


function logic_GetTopTypeId($id, $stepTotal)
{
    global $dsql;
    global $sunDepIdArray;
    $sql = "SELECT * FROM `#@__goods_type` WHERE id=$id";
    $dsql->SetQuery($sql);
    $dsql->Execute("gs" . $id);
    while ($row = $dsql->GetObject("gs" . $id)) {
        //dump($stepTotal);
        //$sunDepIdArray[$stepTotal] = $row->dep_id;
        $nid = $row->reid;
        if ($nid != 0){ logic_GetTopDepId($nid, $stepTotal + 1);}else{
            return $row->id;
        }
    }
}
