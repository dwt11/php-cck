<?php
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单

//读取归档信息
$arcQuery = "SELECT *  FROM #@__line  WHERE id='$id' ";

$arcRow = $dsql->GetOne($arcQuery);


$gotime = $arcRow['gotime'];
$backtime = $arcRow['backtime'];
$seats = $arcRow['seats'];
$goodsid = $arcRow['goodsid'];
$createtime = time();
$tmp = $arcRow['tmp'];
$carinfo_desc = $arcRow['carinfo_desc'];
$beforHours = $arcRow['beforHours'];
$diaodudianhua = $arcRow['diaodudianhua'];


    $query = "INSERT INTO `#@__line` (`goodsid`,`gotime`,`backtime`, `seats`,`createtime`,`carinfo_desc`,`beforHours`,`islock`,`tmp`,`diaodudianhua`)
              VALUES ('$goodsid','$gotime','$backtime', '$seats','$createtime','$carinfo_desc','$beforHours','0','$tmp','$diaodudianhua')";
//dump($query);
if (!$dsql->ExecuteNoneQuery($query)) {
    ShowMsg("添加数据时出错，请检查原因！", "-1");
    exit();
}

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
ShowMsg("成功复制线路信息！", $$ENV_GOBACK_URL);
exit();
