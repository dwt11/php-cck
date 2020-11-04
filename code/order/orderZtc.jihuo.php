<?php
/*部分退款*/
require_once('../config.php');

//$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));

$cckid_array = explode(",", $cckids);
foreach ($cckid_array as $cckid) {
    if ($cckid != "") {
        $operatorid = $CUSERLOGIN->userID;

        $depid=GetEmpDepTopIdByUserId($CUSERLOGIN->getUserId(), 100);
        $createtime = time();
        $sql = "INSERT INTO `#@__ztc_jihuo` (`orderListId`, `dep_id`, `createtime`, `operatorid`) VALUES ($cckid, $depid, $createtime,$operatorid);";
        $dsql->ExecuteNoneQuery($sql);
    }
}


echo "操作成功";