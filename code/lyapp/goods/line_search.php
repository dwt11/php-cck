<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . '/datalistcp.class.php');
require_once("../../goods/catalog.class.php");

$t1 = ExecTime();
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

$typeid = isset($typeid) ? intval($typeid) : 0;
if (!isset($q)) $q = '';

$listtemp = $channeltype = "";
$query = "SELECT templist,typename,channeltype FROM  `#@__goods_type`      WHERE id='$typeid' ";
$goodRow = $dsql->GetOne($query);
//dump($query);
if (is_array($goodRow)) {
    $listtemp = $goodRow['templist'];
    $channeltype = $goodRow['channeltype'];

}


$title = " ";   //页面显示标题
//$title = " 旅游线路";   //页面显示标题
//获取当前点开的分类信息
$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$title = $positionname . $title;

//获取栏目分类
$linkstrs = "";
$arrayData = GetTypeInfoAfterArray($tl->GetTypeInfoArray(), 4);//获取包含当前ID的所有子分类的信息数组
foreach ($arrayData as $keyp => $valuep) {
    $typeInfoArray = $arrayData[$keyp];
    $typename = base64_decode($typeInfoArray['typename']);
    $id = $typeInfoArray['id'];
    $reid = $typeInfoArray['reid'];

    $current = " ";
    if ($id == $typeid) $current = " current ";
    if ($id != 4) {

        $linkstrs .= " <div class=\"topnav_item $current\" id=\"t$id\" >
                        <div class=\"topnav_item_box\">
                            <a href=\"goods_list.php?typeid=$id\">$typename</a>
                        </div>
                    </div>
                    ";
    }
}


//默认的搜索条件
$whereSql = $whereTypeSql = "  ";

if ($typeid > 0) {
    //如果是typeid则生成搜索所有子类的代码
    $whereTypeSql = " AND #@__goods.`typeid` IN ({$tl->GetGoodsSonIds()})";    //搜索用的
}


if ($q != "") {
    $whereSql .= " and ( ";
    $whereSql .= " #@__goods.goodsname LIKE '%$q%' ";
    $whereSql .= " ) ";
}

    $query = "SELECT #@__goods.id as goodsid,litpic,goodscode,goodsname,pubdate,price,#@__line.gotime,#@__line.tmp,lycp.jfnum FROM `#@__line` 
                INNER JOIN #@__goods ON  #@__goods.id=#@__line.goodsid
                INNER JOIN #@__goods_addon_lycp lycp on lycp.goodsid=#@__goods.id
               WHERE #@__line.islock=1 AND #@__goods.`status`='0' AND `isOnlyAdminDisplay`='0' $whereSql 
                  AND (
                            (
                                    #@__line.tmp='临时'
                                    and UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
                            )   /*临时线路，只获取 截止时间前的*/
                           OR ( #@__line.tmp='每日'  )/*固定线路*/
                      )
                    GROUP BY #@__line.goodsid
                    ORDER BY #@__goods.weight ASC,#@__line.id DESC,#@__line.gotime ASC
                ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('q', $q);//input的搜索参数


//模板
if (empty($listtemp)) $listtemp = 'goods_list_lycp.htm';
$dlist->SetTemplate($listtemp);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
$dlist->Close();

$t2 = ExecTime();
//echo $t2 - $t1;



