<?php
/**
 * 客户列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package        DwtX.Administrator

 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("device.functions.php");
require_once(DWTINC . "/fields.func.php");

$t1 = ExecTime();
$typeid = isset($typeid) ? intval($typeid) : 0;
if (empty($keyword)) $keyword = '';
if (empty($targetname)) $targetname = '';//父页目标名称
$tl = new DeviceTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetDeviceTypeOptionS();  //搜索表单的分类值//GetOptionArray

$whereSql = " WHERE 1=1 ";

if ($keyword != "") {
    $whereSql .= "AND (
                        cl.`devicename` LIKE '%$keyword%' 
                         OR cl.`devicecode` LIKE '%$keyword%' 
                        
                        )";
}
if($typeid > 0)
{
    $whereSql .= " AND `typeid` IN (" . $tl->GetDeviceSonIds() . ")";    //搜索用的
}

//获得数据表名
$sql= "SELECT  * FROM #@__device cl $whereSql   ORDER BY   id ASC ";

//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('typeid', $typeid);

//模板
$s_tmplets = 'deviceQrCode.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
