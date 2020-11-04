<?php
/**
 * 系统配置
 *
 * @version        $Id: sysInfo.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

if (empty($dopost)) $dopost = "";
$keywords = isset($keywords) ? dwt_strip_tags($keywords) : '';


// 如果超级管理员则可以查询别的公司的菜单，子公司管理员 则只能查看自己公司的
if ($CUSERLOGIN->getUserType() == 10) {
    if ($GLOBAMOREDEP) {
        if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
    } else {
        if (empty($depid)) $depid = "0";
    }
} else if ($CUSERLOGIN->getUserType() == 9) {
    $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    ShowMsg("无效参数！", "-1");
    exit();
}
//更新配置函数
function ReWriteConfig()
{

    global $dsql ;
    $configfile = DEDEDATA . '/depconfig.cache.inc.php';
    if (!is_writeable($configfile)) {
        echo "配置文件'{$configfile}'不支持写入，无法修改系统配置参数！";
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<" . "?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupname` FROM `#@__sys_sysOtherConfig` WHERE depid={$DEP_TOP_ID}   ORDER BY   id ASC ");//150128添加aid判断  不将注册和运行信息写入缓存 文件
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if ($row['type'] == 'number') {
            if ($row['value'] == '') $row['value'] = 0;
            fwrite($fp, "\${$row['varname']} = " . $row['value'] . ";\r\n");
        } else {
            fwrite($fp, "\${$row['varname']} = '" . str_replace("'", '', $row['value']) . "';\r\n");
        }
    }
    fwrite($fp, "?" . ">");
    fclose($fp);

}

//保存配置的改动
if ($dopost == "save") {
    foreach ($_POST as $k => $v) {
        if (preg_match("#^edit___#", $k)) {
            $v = cn_substrR(${$k}, 1024);
        } else {
            continue;
        }
        $k = preg_replace("#^edit___#", "", $k);
        $dsql->ExecuteNoneQuery("UPDATE `#@__sys_sysOtherConfig` SET `value`='$v' WHERE varname='$k' ");
    }
    ReWriteConfig();
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功更改配置！", $$ENV_GOBACK_URL);
    exit();
}


// 搜索配置
if ($keywords != "") {
    $i = 1;
    $ds[0] = "1,搜索";
    $tabnames = "<li  class=\"active\" ><a  > 搜索</a>  </li> ";
} else {
    $tabnames = "";

    $sql = "SELECT groupname FROM `#@__sys_sysOtherConfig` WHERE depid='{$depid}' GROUP BY groupname  ORDER BY convert(groupname using gbk) ASC;";
    $dsql->SetQuery($sql);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {

        $ds[] = $row["groupname"];
        //dump($row["groupname"]);


    }


    if (isset($ds)) {
        $totalGroup = count($ds);
        $i = 0;
        foreach ($ds as $dl) {
            $dl = trim($dl);
            if (empty($dl)) continue;
            $i++;
            if ($i > 1) {
                $tabnames .= "<li class=\"\" id=\"tabname-{$i}\"><a href='javascript:ShowConfig($i,$totalGroup)'    > {$dl}</a>  </li> ";
            } else {
                $tabnames .= " <li class=\"active\" id=\"tabname-{$i}\"><a href='javascript:ShowConfig($i,$totalGroup)' >{$dl}</a></li> ";
            }
        }
    }
}
include DwtInclude('sys/sysInfo.htm');

