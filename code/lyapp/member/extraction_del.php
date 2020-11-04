<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
$id = trim(preg_replace("#[^0-9]#", '', $id));
if (empty($id)) {
    echo('参数无效');
    exit();
}


$query = "SELECT * FROM `#@__clientdata_extractionlog`  WHERE  id='$id' AND clientid='$CLIENTID'";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    echo("无权删除此信息!");
    exit();
}


$updatedate = time();
$row = $dsql->GetOne("SELECT `jbnum` FROM `#@__clientdata_extractionlog` WHERE id='$id' AND clientid='$CLIENTID'  AND (status=1 OR status=0) AND isdel=0");
$jbnum100 = $row['jbnum'];

//删除前 恢复扣除用户的金币
if ($jbnum100 > 0) {
    $query = "UPDATE `#@__clientdata_extractionlog` SET `isdel`='1'  where id='$id' AND clientid='$CLIENTID'";
    $dsql->ExecuteNoneQuery($query);
    $istrue = Update_jb($CLIENTID, $jbnum100, "删除提现明细，恢复金币");
    echo "删除成功";
} else {
    echo '删除失败';
}
exit();







