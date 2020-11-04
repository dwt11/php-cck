<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($dopost)) $dopost = '';
//if (empty($isjihuo_not_panduan)) $isjihuo_not_panduan = '';            //是否判断乘车卡激活使用            //171102修改为不用激活判断

if (empty($target)) $target = '';

/*查找会员*/
if ($dopost == "") {
    include DwtInclude('order/orderZtc.select1.htm');
    exit;
}
/*按优惠类型获取查找会员的乘车卡,*/
if ($dopost == "search") {

    $clientid = "";
    $query = "SELECT #@__client.id FROM `#@__client`
                    LEFT JOIN #@__client_depinfos ON #@__client_depinfos.clientid=#@__client.id
                      WHERE  mobilephone='$mobilephone'  AND isdel=0";
    //dump($query);
    $chRow = $dsql->GetOne($query);
    if (is_array($chRow)) {
        $clientid = $chRow["id"];//选择的目标用户的ID
    }

    $htmlcode = "未找到此手机号对应的会员信息";

    if ($clientid != "") {
        //dump($isjihuo_not_panduan);
        //171102修改为不用激活判断
        /*if($isjihuo_not_panduan==1){
            //乘车卡激活使用
            $appttime=0;
            $clientTypeName="";
        }*/
        //$ztcCard_array = getZtcCard($clientid, $appttime, $only_client_type = $clientTypeName, "HT",$isshareCLIENTID = 0, $goodsid = 0,$isjihuo_not_panduan);

        //171102修改为不用激活判断
        $ztcCard_array = getZtcCard($clientid, $appttime, $only_client_type = $clientTypeName, "HT",$isshareCLIENTID = 0, $goodsid = 0);
        if (isset($ztcCard_array["ztcinfo"]) && is_array($ztcCard_array["ztcinfo"])) {
            $htmlcode = "
                                     <div class=\"form-group\">
                                        <label class=\"col-sm-2 control-label\">卡选择:</label>
                                        <div class=\"col-sm-10 form-control-static\">
                                    ";
            foreach ($ztcCard_array["ztcinfo"] as $ztcinfo) {
                $htmlcode .= $ztcinfo;
            }
            $htmlcode .= " <input id='clientid' value='$clientid' type='hidden'> ";
            $htmlcode .= "                            </div>
                                                    </div> ";
        } else {
            $htmlcode = "此会员没有 [{$clientTypeName}] 类型的乘车卡";
        }
    }
    include DwtInclude('order/orderZtc.select2.htm');
    exit;
}

