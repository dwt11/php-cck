<?php
/**
 * 商品操作相关函数
 *
 * @version        $Id: inc_device_functions.php 1 9:56 2010年7月21日
 * @package

 * @license
 * @link
 */


/**
 *  检测是否频道
 *  如果是频道封面 则不可以添加
 *170112
 * @access    public
 *
 * @param     int $typeid 分类ID
 *
 * @return    bool
 */
function CheckIsPart($typeid)
{
    global $dsql;
    if ($typeid == 0) return TRUE;

    $row = $dsql->GetOne("SELECT ispart,channeltype FROM `#@__device_type` WHERE id='$typeid' ");
    if ($row['ispart'] != 0 ) return FALSE;
    else return TRUE;
}



function getDeviceCode($devicetypename){
    global $dsql;
    $pinyin = strtoupper(GetPinyin($devicetypename, 1));
    $deviceCode = $pinyin . "001";
    $pinyin_lenth=strlen($pinyin)+1;
    $questr = "SELECT SUBSTRING(devicecode,$pinyin_lenth) AS devicecode FROM `#@__device` WHERE devicecode LIKE '%pinyin%'    ORDER BY SUBSTRING(devicecode,$pinyin_lenth)+0 DESC limit 0,1";
    //dump($questr);
    $rowarc = $dsql->GetOne($questr);
    if (isset($rowarc['devicecode'])&&$rowarc['devicecode'] != "") {
        $deviceCode = $rowarc['devicecode'];
        $deviceCode++;

        $deviceCode = $pinyin.GetIntAddZero($deviceCode);
    }
    return $deviceCode;
}