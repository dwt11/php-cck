<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: channel.php 1 15:26 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC.'/datalistcp.class.php');
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");


$sql = "SELECT id,nid,typename,addtable,addcon,editcon,mancon FROM `#@__archives_channeltype`   ORDER BY   id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet("channel.htm");
$dlist->SetSource($sql);
$dlist->display();

