<?php
require_once(dirname(__FILE__) . "/../include/config.php");

CheckRank();
if (empty($id)) $id = '';
if ($id == '') {
    ShowMsg("非法参数!", "-1");
    exit();
}


//读取归档信息//目标用户和原始 用户都可以删除
$arcQuery = "SELECT * FROM #@__ztc_share WHERE id='$id' and (clientid_n='$CLIENTID' or clientid_o='$CLIENTID') ";
//dump($arcQuery);
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    echo "无权删除此信息!";
    exit();
}


$query = "UPDATE `#@__ztc_share` SET                 `isdel`='1'                WHERE  id='$id' and (clientid_n='$CLIENTID' or clientid_o='$CLIENTID')                ";

$dsql->ExecuteNoneQuery($query);
exit();







