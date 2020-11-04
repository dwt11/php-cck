<?php
/**
 *
 * 栏目列表/频道动态页
 *
 * @version        $Id: list.php 1 15:38 8日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once( "../../include/common.inc.php");

//$t1 = ExecTime();

$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);

$channelid = (isset($channelid) && is_numeric($channelid) ? $channelid : 0);

//if($tid==0 && $channelid==0) die(" Request Error! ");
if(isset($TotalResult)) $TotalResult = intval(preg_replace("/[^\d]/", '', $TotalResult));
/*170914AJAX读取未做完???????
 * if(isset($dopost)){
    $GLOBALS['dopost']=$dopost;//用于AJAX下一页
}*/


//$tinfos = $dsql->GetOne("SELECT tp.id FROM `#@__archives_type` tp LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype WHERE tp.channeltype='$channelid' And tp.reid=0 ORDER BY id asc");
////dump("SELECT tp.id FROM `#@__archives_type` tp LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype WHERE tp.channeltype='$channelid' And tp.reid=0 ORDER BY id asc");
//if(!is_array($tinfos)) die(" No catalogs in the channel! ");
//$tid = $tinfos['id'];

include('archives.listview.class.php');
$lv = new ListView($tid);


//if($lv->IsError) ParamError();

$lv->Display();
