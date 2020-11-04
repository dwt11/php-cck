<?php
require_once("../config.php");
require_once(DWTINC . '/field.class.php');
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("goods.functions.php");
require_once(DWTINC . "/fields.func.php");
/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */
// requir
ExecTime();

$typeid = isset($typeid) ? intval($typeid) : 0;
if (empty($keyword)) $keyword = '';
if (empty($targetname)) $targetname = '';
$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetGoodsTypeOptionS();  //搜索表单的分类值//GetOptionArray

$whereSql = " WHERE  `status`='0' ";

if ($keyword != "") {
    $whereSql .= "AND (
                        cl.`goodsname` LIKE '%$keyword%' 
                         OR cl.`goodscode` LIKE '%$keyword%' 
                        
                       )";
}
if ($typeid > 0) {
    $whereSql .= " AND `typeid` IN (" . $tl->GetGoodsSonIds() . ")";    //搜索用的
}


//171103售卡点只可卖直通车卡
$usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
$isskd = strpos($usertypename, "售卡点子部门");//判断 是否售卡点
// dump($isskd);
if ($isskd === false) {
    //非售卡点的登录用户默认添加的会员部门ID是17
} else {
    //售卡点 是当前的部门
    $whereSql .= " AND `id`='1' ";
}



//获得数据表名
$sql = "SELECT  * FROM #@__goods cl $whereSql   ORDER BY   senddate DESC ";

//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('targetname', $targetname);

//模板
$s_tmplets = 'goods.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
