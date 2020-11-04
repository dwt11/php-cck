<?php
/**
 * 内容列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC.'/datalistcp.class.php');
require_once DWTINC.'/enums.func.php';  //获取数据字典对应的值
 require_once("goods.functions.php");

// requir
ExecTime();

if(!isset($id)) $id = 1;
if(!isset($keyword)) $keyword = '';
if(!isset($dopost)) $dopost = '';
//if(!isset($goodscode)) $goodscode = '';




//获得数据表名
$sql = "SELECT * FROM #@__sys_goods    ORDER BY    id deSC";

//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
//$dlist->SetParameter('keyword', $keyword);
//$dlist->SetParameter('orderby', $orderby);
$dlist->SetParameter('inputid', $inputid);

//模板
$s_tmplets = 'sysgoods.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
