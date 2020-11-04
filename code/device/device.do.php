<?php
/**
 * 商品跳转处理
 *
 * @version        $Id: device.do.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');
$ENV_GOBACK_URL = (empty($_COOKIE['ENV_GOBACK_URL']) ? 'device.php' : $_COOKIE['ENV_GOBACK_URL']);
require_once DWTINC.'/enums.func.php';  //获取数据字典对应的值

if(empty($dopost))
{
    ShowMsg('对不起，你没指定运行参数！','-1');
    exit();
}
$id = isset($id) ? preg_replace("#[^0-9]#", '', $id) : '';
$typeid = empty($typeid) ? 0 : intval($typeid);


/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */






//AJAX获取商品信息  /采购添加  意向客户添加
if($dopost=='GetOneDeviceInfo')
{
		$retstr="";
		$query="SELECT  * FROM `#@__device` WHERE   id = '$deviceid'";
		$row = $dsql->GetOne($query);
		if(is_array($row)){
            $retstr=json_encode($row);
        }
		echo $retstr;
}

