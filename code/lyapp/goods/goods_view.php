<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($id)) {
    showMsg("非法参数", "index.php");
    exit;
}

if (empty($u)) $u = 0;//推荐人


/*161030微信用户登录时已经注册过一次推荐人ID，这里再补充一下
必须是ID商品1 直能车 才可以修改二维码
如果u不为空  没有交易过，没有下级 ,不是股东 则更新
*/

if ($id == 1 && $u > 0 && $u != $CLIENTID) {

    UPDATEclientSponsorid($CLIENTID, $u);//更新用户的上级会员 ,要判断 是否符合更换的条件


    //更新推荐人

}

$query = "SELECT goods.*,gt.channeltype as channelid,gt.tempgoods FROM `#@__goods` goods
          LEFT JOIN `#@__goods_type` gt ON gt.id=goods.typeid
          WHERE goods.id='$id' ";
$goodRow = $dsql->GetOne($query);
//dump($query);
if (!is_array($goodRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
//判断 是否下架
if (isset($goodRow["status"]) && $goodRow["status"] != 0) {
    ShowMsg("已下架", -1);
    exit();
}
//判断 是否只后台
if (isset($goodRow["isOnlyAdminDisplay"]) && $goodRow["isOnlyAdminDisplay"] == 1) {
    ShowMsg("商品不可使用", -1);
    exit();
}

$viewtemp = $goodRow['tempgoods'];
$goodsoldcode = $goodRow['goodscode'];
$typeid = $goodRow['typeid'];
$channelid = $goodRow['channelid'];
if (empty($typeid)) {
    ShowMsg("请指定商品的分类！", "-1");
    exit();
}
if (empty($channelid) || !$channelid > 0) {
    ShowMsg("获取模型出错！", "-1");
    exit();
}
//获取模型的相关参数
$sql = "SELECT fieldset,addtable,tempedit FROM `#@__sys_channeltype` WHERE id='$channelid'";
$cts = $dsql->GetOne($sql);
$addtable = trim($cts['addtable']);
if (empty($addtable)) {
    ShowMsg("没找到当前模型[{$channelid}]的表信息，无法完成操作！。", "-1");
    exit();
}
$addRowAddtable = $dsql->GetOne("SELECT * FROM `$addtable` WHERE goodsid='$id'");
if (!is_array($addRowAddtable)) {
    ShowMsg("读取附加表信息出错!", "javascript:;");
    exit();
}


//判断 是否停用
if (isset($addRowAddtable["islock"]) && $addRowAddtable["islock"] == 0) {
    ShowMsg("已停用", -1);
    exit();
}
$dsql->ExecuteNoneQuery(" UPDATE `#@__goods` SET click=click+1 WHERE id='$id' ");
$dpl = new DWTTemplate();
//模板
if (empty($viewtemp)) $viewtemp = 'goods_view.htm';
//$addtemp = 'goods_add.htm';
$tpl = $viewtemp;
//dump($tpl);
$dpl->LoadTemplate($tpl);
$dpl->display();


