<?php
/**
 * 参数 列表
 *
 * @version        $Id: 2016年4月29日 14:46
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("goods.functions.php");
require_once(DWTINC . "/datalistcp.class.php");
require_once(DWTINC . "/common.func.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$whereSql = "where 1=1 ";

if (!isset($keyword)) $keyword = '';
if (!isset($flag)) $flag = '';
if (!isset($dir)) $dir = '';


if ($keyword != '') {
    $whereSql .= " AND ( name LIKE '%$keyword%' or urladd LIKE '%$keyword%') ";
}
if ($flag != '') {
    $whereSql .= " AND FIND_IN_SET('$flag', flag) ";
}
if ($dir != '') {
    $whereSql .= " AND dir = '$dir' ";
}


$optiondir = "";
$query = "SELECT dir FROM `#@__sys_goods` group by dir   ORDER BY   dir ASC";
$dsql->Execute('me', $query);
while ($row = $dsql->getarray()) {
    $rowdir = $row['dir'];
    $selected = "";
    if ($dir == $rowdir) $selected = " selected ";
    $optiondir .= "<option value='$rowdir' $selected >$rowdir</option>>";
}

$optionflag = "";
$flags = "";
$query = "SELECT flag FROM `#@__sys_goods` WHERE flag!='' group by flag   ORDER BY   flag ASC";
$dsql->Execute('me', $query);
while ($row = $dsql->getarray()) {
    //dump($row);
    $flags .= $row['flag'] . ",";
    //$selected="";
    //if($flag==$rowflag)$selected=" selected ";
    //$optionflag.="<option value='$rowflag' $selected >$rowflag</option>>";
}
$flags_array = explode(",", $flags);
$flags_array = array_unique($flags_array);
foreach ($flags_array as $value) {
    if ($value != "") {
        if ($flag == $value) $selected = " selected ";
        $optionflag .= "<option value='$value' $selected >$value</option>>";
    }
}


$sql = "SELECT * FROM #@__sys_goods $whereSql   ORDER BY    id aSC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('dir', $dir);
$dlist->SetParameter('flag', $flag);
$dlist->SetParameter('keyword', $keyword);
$dlist->SetTemplate("sysgoods.htm");
$dlist->SetSource($sql);
$dlist->Display();


function getUseDep($urladd){
    $dsql = $GLOBALS['dsql'];
    $sql1 = "SELECT count(*) as dd  FROM `#@__sys_goods_orderdetails` WHERE  urladd='$urladd'";
    $row = $dsql->GetOne($sql1);
    if (is_array($row)) {
        $depnumb = $row["dd"];
    }

    $url_code = urlencode($urladd);
     $dep_view = "<a onclick=\"layer.open({type: 2,title: '使用公司', content: 'goods.useDep.php?urladd={$url_code}'});\"  href='javascript:'  >使用公司(" . $depnumb . ")</a> ";
    return $dep_view;
 }
