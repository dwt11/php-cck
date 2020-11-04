<?php
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


$pubdate = time();
if($flag=="h"){
    $flag="h";
}else{
    $flag="";
}
$sql="UPDATE #@__archives SET flag='$flag',pubdate='$pubdate' where id='$id';";
$dsql->ExecuteNoneQuery($sql);
//dump($sql);
ShowMsg("设置头条成功！", $$ENV_GOBACK_URL);
exit();
