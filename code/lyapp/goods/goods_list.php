<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . '/datalistcp.class.php');
require_once( "../../goods/catalog.class.php");

$t1 = ExecTime();
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$go_date = isset($go_date) ? $go_date : "";
$typeid = isset($typeid) ? intval($typeid) : 0;
if (!isset($q)) $q = '';
if (!isset($dopost)) $dopost = '';

$listtemp=$channeltype="";
$query = "SELECT templist,typename,channeltype FROM  `#@__goods_type`      WHERE id='$typeid' ";
$goodRow = $dsql->GetOne($query);
//dump($query);
if (is_array($goodRow)) {
    $listtemp= $goodRow['templist'];
    $channeltype= $goodRow['channeltype'];

}




$title = " ";   //页面显示标题
//$title = " 旅游线路";   //页面显示标题
//获取当前点开的分类信息
$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$title = $positionname.$title;

//获取栏目分类
$linkstrs="";
$arrayData = GetTypeInfoAfterArray($tl->GetTypeInfoArray(), 4);//获取包含当前ID的所有子分类的信息数组
foreach ($arrayData as $keyp => $valuep) {
    $typeInfoArray = $arrayData[$keyp];
    $typename = base64_decode($typeInfoArray['typename']);
    $id = $typeInfoArray['id'];
    $reid = $typeInfoArray['reid'];

    $current=" ";
    if($id==$typeid)$current=" current ";
    if($id!=4){

        $linkstrs.=" <div class=\"topnav_item $current\" id=\"t$id\" >
                        <div class=\"topnav_item_box\">
                            <a href=\"goods_list.php?typeid=$id\">$typename</a>
                        </div>
                    </div>
                    ";
    }
}


//默认的搜索条件
$whereSql =$whereTypeSql= "  ";

if ($typeid > 0) {
    //如果是typeid则生成搜索所有子类的代码
    $whereSql .= " AND #@__goods.`typeid` IN ({$tl->GetGoodsSonIds()})";    //搜索用的
}

$query = "SELECT 
          #@__goods.id as goodsid,litpic,goodscode,goodsname,pubdate,price,'' AS jfnum 
          FROM  #@__goods    WHERE status='0' AND `isOnlyAdminDisplay`='0' $whereSql            ORDER BY #@__goods.weight DESC                ";


//旅游产品列表
if($channeltype==2) {
    if($go_date!="")$whereSql.=" AND (FROM_UNIXTIME(#@__line.gotime ,'%Y-%m-%d') ='$go_date') ";
    $query = "SELECT #@__goods.id as goodsid,litpic,goodscode,goodsname,pubdate,price,#@__line.gotime,#@__line.tmp,lycp.jfnum FROM `#@__line` 
                LEFT JOIN #@__goods ON  #@__goods.id=#@__line.goodsid
                LEFT JOIN #@__goods_addon_lycp lycp on lycp.goodsid=#@__goods.id
               WHERE #@__line.islock=1 AND #@__goods.`status`='0'  AND `isOnlyAdminDisplay`='0'
                  AND (
                            (
                                    #@__line.tmp='临时'
                                    and UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
                            )   /*临时线路，只获取 截止时间前的*/
                           OR ( #@__line.tmp='每日'  )/*固定线路*/
                      )
                      $whereSql 
                    GROUP BY #@__line.goodsid
                    ORDER BY #@__goods.weight ASC,#@__goods.typeid ASC,#@__line.id DESC,#@__line.gotime ASC
                ";
}

//车辆租赁产品
if($channeltype==3) {
    $query = "SELECT #@__goods.id as goodsid,litpic,goodscode,goodsname,pubdate,price,car.jfnum FROM `#@__goods` 
                LEFT JOIN #@__goods_addon_car car ON car.goodsid=#@__goods.id
                 WHERE status='0'  AND `isOnlyAdminDisplay`='0'  $whereSql            ORDER BY #@__goods.weight DESC  
                 ";
}
//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('q', $q);//input的搜索参数
$dlist->SetParameter('typeid', $typeid);//input的搜索参数
$dlist->SetParameter('go_date', $go_date);//按日期查询旅游线路
$dlist->SetParameter('dopost', $dopost);


//模板
if (empty($listtemp)) $listtemp = 'goods_list.htm';
if($dopost=="ajax"){
//如果是下拉的,则使用以下的模板
    $listtemp = str_replace(".htm","_ajax.htm",$listtemp);
}

$dlist->SetTemplate($listtemp);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
$dlist->Close();

$t2 = ExecTime();
//echo $t2 - $t1;



