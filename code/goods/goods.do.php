<?php
/**
 * 商品跳转处理
 *
 * @version        $Id: goods.do.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once('../config.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("goods.functions.php");

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = isset($id) ? preg_replace("#[^0-9]#", '', $id) : '';
$typeid = empty($typeid) ? 0 : intval($typeid);


//判断直通车和会员卡添加的消费后的会员类型是否重复
if ($dopost == 'checkClientRank') {

    $ztc_sfcf = false;//直通车是否重复  默认重复不可用
    $hyk_sfcf = false;//会员卡是否重复  默认重复不可用


    $sql = ("SELECT clientRank FROM `#@__goods_addon_hyk`  WHERE   clientRank='$clientRank' ");
    $chRow = $dsql->GetOne($sql);
    //dump($sql);
    if (is_array($chRow)) {
        $hyk_sfcf = false;//存在,新名称不可用
    } else {
        $hyk_sfcf = true;//不存在,新名称可用
    }
    $sql = ("SELECT clientRank FROM `#@__goods_addon_ztc`  WHERE   clientRank='$clientRank' ");
    $chRow = $dsql->GetOne($sql);
    //dump($sql);
    if (is_array($chRow)) {
        $ztc_sfcf = false;//存在,新名称不可用
    } else {
        $ztc_sfcf = true;//不存在,新名称可用
    }


    //两个都不存在重复的,才可以
    //dump ( $hyk_sfcf);
    //dump ($ztc_sfcf ) ;
    //dump ($ztc_sfcf && $hyk_sfcf) ;
    if ($ztc_sfcf && $hyk_sfcf) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}


/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */


if ($dopost == 'validateCheckIsPart') {
    if (CheckIsPart($typeid)) {
        echo "false";//是频道 返回 不可以操作
    } else {
        echo "true";//不是频道可以操作
    }

}


if ($dopost == 'validateGoodscode') {

    if ($goodsoldcode == "" || $goodsoldcode != $goodscode) {
        //判断修商品编号 是否冲突
        $query = "SELECT goodscode FROM `#@__goods` WHERE goodscode='$goodscode' ";
        $goodRow = $dsql->GetOne($query);
        if (is_array($goodRow)) {
            echo "false";//重复返回 不可以操作
        } else {
            echo "true";//不重复
        }
    } else {
        echo "true";
    }

}


//AJAX获取商品信息  /采购添加  意向客户添加
if ($dopost == 'GetOneGoodsInfo') {
    $retstr = "";
    $query = "SELECT  goodsname,FORMAT((price/100),2) AS price FROM `#@__goods` WHERE   id = '$goodsid'";
    //dump($query);
    $row = $dsql->GetOne($query);
    if (is_array($row)) {
        $retstr = json_encode($row);
    }
    echo $retstr;
}

//AJAX获取商品信息  /采购添加  意向客户添加
if ($dopost == 'GetOneLineInfo') {
    $retstr = "";
    $query = "SELECT  `#@__goods`.id AS goodsid ,goodscode,goodsname,FORMAT((price/100),2) as price,FORMAT((#@__goods_addon_lycp.jfnum/100),2) as jfnum,
                #@__goods_addon_lycp.tjsite,
                DATE_FORMAT(FROM_UNIXTIME(gotime),'%Y-%m-%d %H:%i') AS gotime,
                tmp as tmpType 
                FROM `#@__line`
                LEFT JOIN `#@__goods` ON `#@__line`.goodsid=`#@__goods`.id
                LEFT JOIN `#@__goods_addon_lycp` ON `#@__goods_addon_lycp`.goodsid=`#@__goods`.id
                WHERE  `#@__line`.id = '$lineid'";
    $row = $dsql->GetOne($query);
    if (is_array($row)) {
        $retstr = json_encode($row);
    }
    echo $retstr;
}

//更新前后台显示状态
if ($dopost == 'UpGoodsOnlyAdmin') {
    $ENV_GOBACK_URL = (GetFunMainName("/goods/goods.php") . "ENV_GOBACK_URL");
    if ($id == '') {
        ShowMsg("参数无效！", $$ENV_GOBACK_URL);
        exit();
    }
    if ($isOnlyAdminDisplay == "1") {
        $isOnlyAdminDisplay = "1";
    } else {
        $isOnlyAdminDisplay = "0";
    }
    $dsql->ExecuteNoneQuery("UPDATE #@__goods SET isOnlyAdminDisplay='$isOnlyAdminDisplay' WHERE id IN ($id);");
    ShowMsg("设置成功！", $$ENV_GOBACK_URL);
    exit();

}

