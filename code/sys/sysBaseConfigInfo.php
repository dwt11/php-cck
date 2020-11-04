<?php
/**
 * 系统配置
 *
 *
 * ????这页checkbox和table有冲突,导致不能选择
 * @version        $Id: sysBaseConfigInfo.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

if (empty($dopost)) $dopost = "";
$keywords = isset($keywords)? dwt_strip_tags($keywords) : '';

$configfile = DEDEDATA . '/config.cache.inc.php';

//更新配置函数
function ReWriteConfig()
{
    global $dsql, $configfile;
    if (!is_writeable($configfile)) {
        echo "配置文件'{$configfile}'不支持写入，无法修改系统配置参数！";
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<" . "?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sys_sysBaseConfig` WHERE aid<1000   ORDER BY   aid ASC ");//150128添加aid判断  不将注册和运行信息写入缓存 文件
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
if($dopost=="save")
{
    foreach($_POST as $k=>$v)
    {
        if(preg_match("#^edit___#", $k))
        {
            $v = cn_substrR(${$k}, 1024);
        }
        else
        {
            continue;
        }
        $k = preg_replace("#^edit___#", "", $k);
        $dsql->ExecuteNoneQuery("UPDATE `#@__sys_sysBaseConfig` SET `value`='$v' WHERE varname='$k' ");
    }
    ReWriteConfig();
    ShowMsg("成功更改站点配置！", "sysBaseConfigInfo.php");
    exit();
}
//添加新变量
else if($dopost=='add')
{
    if($vartype=='bool' && ($nvarvalue!='Y' && $nvarvalue!='N'))
    {
        ShowMsg("布尔变量值必须为'Y'或'N'!","-1");
        exit();
    }
    if(trim($nvarname)=='' || preg_match("#[^a-z_]#i", $nvarname) )
    {
        ShowMsg("变量名不能为空并且必须为[a-z_]组成!","-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT varname FROM `#@__sys_sysBaseConfig` WHERE varname LIKE '$nvarname' ");
    if(is_array($row))
    {
        ShowMsg("该变量名称已经存在!","-1");
    }
    $row = $dsql->GetOne("SELECT aid FROM `#@__sys_sysBaseConfig`   ORDER BY   aid DESC ");
    $aid = $row['aid'] + 1;
    $inquery = "INSERT INTO `#@__sys_sysBaseConfig`(`archivesid`,`varname`,`info`,`value`,`type`,`groupid`)
    VALUES ('$aid','$nvarname','$varmsg','$nvarvalue','$vartype','$vargroup')";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if(!$rs)
    {
        ShowMsg("新增变量失败，可能有非法字符！", "sysBaseConfigInfo.php?gp=$vargroup");
        exit();
    }
    if(!is_writeable($configfile))
    {
        ShowMsg("成功保存变量，但由于 $configfile 无法写入，因此不能更新配置文件！","sysBaseConfigInfo.php?gp=$vargroup");
        exit();
    }else
    {
        ReWriteConfig();
        ShowMsg("成功保存变量并更新配置文件！","sysBaseConfigInfo.php?gp=$vargroup");
        exit();
    }
    exit();
}

//更新code
else if ($dopost=='make_encode'){
    $chars='abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
    $hash='';
    $length = rand(28,32);
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    echo $hash;
    exit();
}
//默认打开


// 搜索配置
if ($keywords !="")
{
    $i = 1;
    $ds[0] = "1,搜索";
    $tabnames = "<li  class=\"active\" ><a  > 搜索</a>  </li> ";
}else {
    $tabnames = "";
    $ds[0] = "1,系统参数";
    $ds[1] = "2,附件设置";
    $totalGroup = count($ds);
    $i = 0;
    foreach ($ds as $dl) {
        $dl = trim($dl);
        if (empty($dl)) continue;
        $dls = explode(',', $dl);
        $i++;
        if ($i > 1) {
            $tabnames .= "<li class=\"\" id=\"tabname-{$i}\"><a href='javascript:ShowConfig($i,$totalGroup)'    > {$dls[1]}</a>  </li> ";
        } else {
            $tabnames .= " <li class=\"active\" id=\"tabname-{$i}\"><a href='javascript:ShowConfig($i,$totalGroup)' >{$dls[1]}</a></li> ";
        }
    }
}
include DwtInclude('sys/sysBaseConfigInfo.htm');

?>

