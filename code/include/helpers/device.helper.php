<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 设备小助手
 *
 * @version        $Id: device.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


/**获取单个栏目名称170106
 *
 * @param $tid
 *
 * @return string
 */
if (!function_exists('GetDeviceTypeName')) {
    function GetDeviceTypeName($tid)
    {
        global $cfg_Cs;
        if (empty($tid)) return '';
        if (!is_array($cfg_Cs)) {
            GetDeviceCatalogs();
        }
        //dump($cfg_Cs);
        if (isset($cfg_Cs[$tid])) {
            return base64_decode($cfg_Cs[$tid][2]);
        }
        return '';
    }
}


/**
 *获取所有分类的数组170106
 */
if (!function_exists('GetDeviceCatalogs')) {
    function GetDeviceCatalogs()
    {
        global $cfg_Cs, $dsql;
        $dsql->SetQuery("SELECT id,reid,channeltype,typename FROM `#@__device_type`");
        $dsql->Execute();
        $cfg_Cs = array();
        while ($row = $dsql->GetObject()) {
            // 将typename缓存起来
            $row->typename = base64_encode($row->typename);
            $cfg_Cs[$row->id] = array($row->reid, $row->channeltype, $row->typename);
        }
    }
}

/**获取设备名称170106
 *
 * @param $tid
 *
 * @return string
 */
if (!function_exists('GetDeviceName')) {
    function GetDeviceName($deviceid)
    {
        global $dsql;
        $retstr="";
        $query="SELECT  devicename FROM `#@__device` WHERE   id = '$deviceid'";
        $row = $dsql->GetOne($query);
        if(is_array($row)){
            $retstr=$row["devicename"];
        }
        return  $retstr;

    }
}

