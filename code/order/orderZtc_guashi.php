<?php
require_once('../config.php');

$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($orderid == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
$orerid = $CUSERLOGIN->userID;


$arcQuery = "SELECT sta,jfnum,jbnum,clientid,ordernum,paynum FROM #@__order  WHERE isdel=0 AND id='$orderid' ";
//dump($arcQuery);
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    $return_str = "读取信息出错!";
}else {


    $orderSta = $arcRow["sta"];
    if ($orderSta == "1") {

        $createtime = time();
        $query = "UPDATE `#@__order` SET        returnOperatorid='$orerid',returntime='$createtime',         `isdel`='2'                WHERE  id='$orderid'              ";
        if ($dsql->ExecuteNoneQuery($query)) {

            //这里写删除送出的会员类型
            $arcQuery1 = "SELECT clientid FROM #@__clientdata_ranklog  WHERE orderid='$orderid' ";
            $arcRow1 = $dsql->GetOne($arcQuery1);
            if (is_array($arcRow1)) {
                $query = "DELETE FROM `#@__clientdata_ranklog` WHERE   orderid='$orderid'              ";
                $dsql->ExecuteNoneQuery($query);
            }
            $return_str = "操作失败";
        }
        $return_str = "操作成功";
    }
}
ShowMsg($return_str, $$ENV_GOBACK_URL);
exit();
