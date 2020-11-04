<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 文档小助手
 *
 * @version        $Id: archive.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */

/**
 *  获取单篇文档信息
 *
 * @param     int $aid 文档id
 *
 * @return    array
 */
if (!function_exists('GetOneArchive')) {
    function GetOneArchive($aid)
    {
        global $dsql;
        //include_once(DWTINC."/channelunit.func.php");
        $aid = trim(preg_replace('/[^0-9]/', '', $aid));
        $reArr = array();

        $chRow = $dsql->GetOne("SELECT arc.*,ch.maintable,ch.addtable FROM `#@__archives_arctiny` arc LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid WHERE arc.id='$aid' ");

        if (!is_array($chRow)) {
            return $reArr;
        } else {
            if (empty($chRow['maintable'])) $chRow['maintable'] = '#@__archives';
        }


        $nquery = " SELECT arc.*,tp.topid ,arcives.*
                        FROM `{$chRow['addtable']}` arc
                         LEFT JOIN `#@__archives_type` tp ON tp.id=arc.typeid
                         LEFT JOIN #@__archives  arcives on arc.aid=arcives.id
                        WHERE arc.aid='$aid' ";
//dump($nquery);
        $arcRow = $dsql->GetOne($nquery);

        if (!is_array($arcRow)) {
            return $reArr;
        }


        if (empty($arcRow['description']) && isset($arcRow['body'])) {
            $arcRow['description'] = cn_substr(html2text($arcRow['body']), 250);
        }

//dump($arcRow);
        $reArr = $arcRow;
        $reArr['aid'] = $aid;
        $reArr['topid'] = $arcRow['topid'];
        $reArr['arctitle'] = $arcRow['title'];
        $reArr['arcurl'] = $cfg_install_path . "/web/archive_view.php?aid=" . $aid;
        return $reArr;

    }
}


/**
 *  获取模型的表信息
 *
 * @param     int    $id       模型ID
 * @param     string $formtype 表单类型
 *
 * @return    array
 */
if (!function_exists('GetArchiveChannelTable')) {
    function GetArchiveChannelTable($id, $formtype = 'channel')
    {
        global $dsql;
        if ($formtype == 'archive') {
            $query = "SELECT ch.maintable, ch.addtable FROM #@__archives_arctiny tin LEFT JOIN #@__archives_channeltype ch ON ch.id=tin.channelid WHERE tin.id='$id'";
        } else if ($formtype == 'typeid') {
            $query = "SELECT ch.maintable, ch.addtable FROM #@__archives_type act LEFT JOIN #@__archives_channeltype ch ON ch.id=act.channeltype WHERE act.id='$id'";
        } else {
            $query = "SELECT maintable, addtable FROM #@__archives_channeltype WHERE id='$id'";
        }
        $row = $dsql->GetOne($query);
        return $row;
    }
}

/**
 *  获取一个微表的索引键
 *
 * @access    public
 *
 * @param     string $issend    权限值
 * @param     int    $typeid    部门ID
 * @param     int    $sortrank  排序ID
 * @param     int    $channelid 模型ID
 * @param     int    $senddate  发布日期
 * @param     int    $userid    会员ID
 *
 * @return    int
 */
if (!function_exists('GetArchiveIndexKey')) {
    function GetArchiveIndexKey($issend, $typeid, $sortrank = 0, $channelid = 1, $senddate = 0, $userid = 1)
    {
        global $dsql, $senddate;
        if (empty($senddate)) $senddate = time();
        if (empty($sortrank)) $sortrank = $senddate;
        $iquery = "
          INSERT INTO `#@__archives_arctiny` (`typeid`,`issend`,`channelid`,`senddate`, `sortrank`, `userid`)
          VALUES ('$typeid','$issend', '$channelid','$senddate', '$sortrank', '$userid') ";
        $dsql->ExecuteNoneQuery($iquery);
        $aid = $dsql->GetLastID();
        return $aid;
    }
}


/**
 *  更新微表key及Tag
 *
 * @access    public
 *
 * @param     int    $id       文档ID
 * @param     string $issend   权限值
 * @param     int    $typeid   部门ID
 * @param     int    $sortrank 排序ID
 * @param     string $tags     tag标签
 *
 * @return    string
 */
if (!function_exists('UpArchiveIndexKey')) {
    function UpArchiveIndexKey($id, $issend, $typeid, $sortrank = 0)
    {
        global $dsql;
        $query = " UPDATE `#@__archives_arctiny` SET `issend`='$issend', `typeid`='$typeid', `sortrank`='$sortrank' WHERE id = '$id' ";
        $dsql->ExecuteNoneQuery($query);

    }
}


/**
 *  获得指定类目的URL链接
 *  对于使用封面文件和单独页面的情况，强制使用默认页名称
 *
 * @param     int $typeid 栏目ID
 *
 * @return    string
 */
if (!function_exists('GetArchiveTypeUrl')) {

    function GetArchiveTypeUrl($typeid)
    {
        global $cfg_install_path;
        //动态
        $reurl = $cfg_install_path . "/web/archives_list.php?tid=" . $typeid;

        return $reurl;
    }
}


/**
 *  魔法变量，用于获取两个可变的值
 *
 * @param     string $v1 第一个变量
 * @param     string $v2 第二个变量
 *
 * @return    string
 */
if (!function_exists('MagicVar')) {
    function MagicVar($v1, $v2)
    {
        return $GLOBALS['autoindex'] % 2 == 0 ? $v1 : $v2;
    }
}

/**
 *  获取某个类目的所有上级栏目id
 *
 * @param     int $tid 栏目ID
 *
 * @return    string
 */
if (!function_exists('GetArchiveTopids')) {
    function GetArchiveTopids($tid)
    {
        $arr = GetArchiveParentIds($tid);
        return join(',', $arr);
    }
}


/**
 *  获取上级ID列表
 *
 * @access    public
 *
 * @param     string $tid 栏目ID
 *
 * @return    string
 */
if (!function_exists('GetArchiveParentIds')) {
    function GetArchiveParentIds($tid)
    {
        global $cfg_Cs;
        $GLOBALS['pTypeArrays'][] = $tid;
        if (!is_array($cfg_Cs)) {
            GetCatalogs();
        }
        if (!isset($cfg_Cs[$tid]) || $cfg_Cs[$tid][0] == 0) {
            return $GLOBALS['pTypeArrays'];
        } else {
            return GetParentIds($cfg_Cs[$tid][0]);
        }
    }
}


/**
 *  检测栏目是否是另一个栏目的父目录
 *
 * @access    public
 *
 * @param     string $sid 顶级目录id
 * @param     string $pid 下级目录id
 *
 * @return    bool
 */
if (!function_exists('IsParent')) {
    function IsArchiveParent($sid, $pid)
    {
        $pTypeArrays = GetParentIds($sid);
        return in_array($pid, $pTypeArrays);
    }
}


/**
 *  获取一个类目的顶级类目id
 *
 * @param     string $tid 栏目ID
 *
 * @return    string
 */
if (!function_exists('GetArchiveTopid')) {
    function GetArchiveTopid($tid)
    {
        global $cfg_Cs;
        if (!is_array($cfg_Cs)) {
            GetArchiveCatalogs();
        }
        if (!isset($cfg_Cs[$tid][0]) || $cfg_Cs[$tid][0] == 0) {
            return $tid;
        } else {
            return GetArchiveTopid($cfg_Cs[$tid][0]);
        }
    }
}


//获取所有分类的数组141009
function GetArchiveCatalogs()
{
    global $cfg_Cs, $dsql;
    $dsql->SetQuery("SELECT id,reid,channeltype,issend,typename FROM `#@__archives_type`");
    $dsql->Execute();
    $cfg_Cs = array();
    while ($row = $dsql->GetObject()) {
        // 将typename缓存起来
        $row->typename = base64_encode($row->typename);
        $cfg_Cs[$row->id] = array($row->reid, $row->channeltype, $row->issend, $row->typename);
    }
}


/*
//获取所有模型的数组141009
function GetChanneltypes()
{
    global $channeltypes,$dsql;
    $dsql->SetQuery("SELECT id,typename FROM `#@__archives_channeltype`");
    $dsql->Execute();
    $channeltypes=array();
    while($row=$dsql->GetObject())
    {
        // 将typename缓存起来
        //$row->typename = base64_encode($row->typename);
        $channeltypes[$row->id]=$row->typename;
    }
    //dump($channeltypes);
}
*/


// 获取单个栏目名称141009
function GetArchiveTypeName($tid)
{
    global $cfg_Cs;
    if (empty($tid)) return '';
    if (!is_array($cfg_Cs)) {
        GetArchiveCatalogs();
    }

//dump($cfg_Cs[$tid]);
    if (isset($cfg_Cs[$tid])) {
        return base64_decode($cfg_Cs[$tid][3]);
    }
    return '';
}


// 获取包含当前栏目 的 所有上级栏目的名称141010
function GetArchiveAllTypeName($tid)
{
    global $cfg_Cs, $rstr;
    $rstr = "";
    if (empty($tid)) return '';
    if (!is_array($cfg_Cs)) {
        GetArchiveCatalogs();
    }
    if (isset($cfg_Cs[$tid])) {
        GetArchiveReNamesLogic($cfg_Cs[$tid][0]);
        $rstr .= "-" . base64_decode($cfg_Cs[$tid][3]);
        return ltrim($rstr, "-");
    }
    return '';
}


//递归逻辑
function GetArchiveReNamesLogic($reid)
{
    global $cfg_Cs, $rstr;
    //dump($reid);
    if ($reid != 0) {
        GetReNamesLogic($cfg_Cs[$reid][0]);
        $rstr .= "-" . base64_decode($cfg_Cs[$reid][3]);
    }
}


// 获取模型名称141009
function GetArchiveChannelTypeName($tid)
{
    global $channeltypes;
    if (empty($tid)) return '';
    if (!is_array($channeltypes)) {
        GetChanneltypes();
    }

//dump($channeltypes[$tid]);
    if (isset($channeltypes[$tid])) {
        return $channeltypes[$tid];
    }
    return '';
}


/**
 *  获得某id的所有下级id
 *
 * @param     string $id      栏目id
 * @param     string $channel 模型ID
 * @param     string $addthis 是否包含本身
 *
 * @return    string
 */
function GetArchiveSonIds($id, $channel = 0, $addthis = true)
{
    global $cfg_Cs;
    $GLOBALS['idArray'] = array();
    if (!is_array($cfg_Cs)) {
        GetArchiveCatalogs();
    }
    GetArchiveSonIdsLogic($id, $cfg_Cs, $channel, $addthis);
    $rquery = join(',', $GLOBALS['idArray']);
    $rquery = preg_replace("/,$/", '', $rquery);
    return $rquery;
}

//递归逻辑
function GetArchiveSonIdsLogic($id, $sArr, $channel = 0, $addthis = false)
{
    if ($id != 0 && $addthis) {
        $GLOBALS['idArray'][$id] = $id;
    }
    if (is_array($sArr)) {
        foreach ($sArr as $k => $v) {
            if ($v[0] == $id && ($channel == 0 || $v[1] == $channel)) {
                GetArchiveSonIdsLogic($k, $sArr, $channel, true);
            }
        }
    }
}

/**
 *  栏目目录规则
 *
 * @param     string $typedir 栏目目录
 *
 * @return    string
 */
//function MfTypedir($typedir)
//{
//    if(preg_match("/^http:|^ftp:/i", $typedir)) return $typedir;
//    $typedir = str_replace("{cmspath}",$GLOBALS['cfg_install_path'],$typedir);
//    $typedir = preg_replace("/\/{1,}/", "/", $typedir);
//    return $typedir;
//}


/**
 *  清除用于js的空白块
 *
 * @param     string $atme 字符
 *
 * @return    string
 */
function FormatScript($atme)
{
    return $atme == '&nbsp;' ? '' : $atme;
}

/**
 *  给属性默认值
 *
 * @param     array $atts    属性
 * @param     array $attlist 属性列表
 *
 * @return    string
 */
function FillAttsDefault(&$atts, $attlist)
{
    $attlists = explode(',', $attlist);
    for ($i = 0; isset($attlists[$i]); $i++) {
        list($k, $v) = explode('|', $attlists[$i]);
        if (!isset($atts[$k])) {
            $atts[$k] = $v;
        }
    }
}


/**
 *  获取某栏目的url
 *
 * @param     array $typeinfos 栏目信息
 *
 * @return    string
 */
function GetArchiveOneTypeUrlA($typeinfos)
{
    return GetArchiveTypeUrl($typeinfos['id']);
}

/**
 *  设置全局环境变量
 *
 * @param     int    $typeid   栏目ID
 * @param     string $typename 栏目名称
 * @param     string $aid      文档ID
 * @param     string $title    标题
 * @param     string $curfile  当前文件
 *
 * @return    string
 */
function SetSysEnv($typeid = 0, $typename = '', $aid = 0, $title = '', $curfile = '')
{
    global $_sys_globals;
    if (empty($_sys_globals['curfile'])) {
        $_sys_globals['curfile'] = $curfile;
    }
    if (empty($_sys_globals['typeid'])) {
        $_sys_globals['typeid'] = $typeid;
    }
    if (empty($_sys_globals['typename'])) {
        $_sys_globals['typename'] = $typename;
    }
    if (empty($_sys_globals['aid'])) {
        $_sys_globals['aid'] = $aid;
    }
}



//获得推荐的标题
function GetCommendTitle($title, $iscommend)
{
    /*if(preg_match('#c#i',$iscommend))
    {
        $title = "$title<font color='red'>(推荐)</font>";
    }*/
    return $title;
}

//更换颜色
function GetColor($color1, $color2)
{
    $GLOBALS['RndTrunID'] = 1;
    $GLOBALS['RndTrunID']++;
    if ($GLOBALS['RndTrunID'] % 2 == 0) {
        return $color1;
    } else {
        return $color2;
    }
}

//检查图片是否存在
function CheckPic($picname)
{
    if ($picname != "") {
        return $picname;
    } else {
        return "images/dfpic.gif";
    }
}

//获得文档的状态141008
function GetArcSend($issend)
{
//是否审核,0无需审核或审核通过，1需要待审核，-1审核不通过，-2    global $arcArray,$dsql;
    if ($issend == 0) return "正常";
    if ($issend == 1) return "等待审核";
    if ($issend == -1) return "未审核通过";
    if ($issend == -2) return "回收站";
}


/**
 *   获取文档的浏览权限信息
 *
 * @return    string
 */
function GetArcDepTypeName($trank)
{
    if ($trank == "0") {
        return "正常浏览";
    } else if ($trank == "-1") {
        return "登录后浏览";
    }
}


//判断内容是否为图片文章
function IsPicArchives($picname)
{
    if ($picname != '') {
        return '<font color=\'red\'>(图)</font>';
    } else {
        return '';
    }
}


/**
 *  给块标记赋值 前台使用141014
 *
 * @param     object $dtp    模板解析引擎
 * @param     object $refObj 实例化对象
 * @param     object $parfield
 *
 * @return    string
 */
function MakeOneTag(&$dtp, &$refObj, $parfield = 'Y')
{
    global $cfg_disable_tags, $cfg_basedir;
    //$cfg_disable_tags = isset($cfg_disable_tags)? $cfg_disable_tags : 'php';  模板禁用标签
    // dump($cfg_disable_tags);
    $disable_tags = explode(',', $cfg_disable_tags);
    $alltags = array();
    $dtp->setRefObj($refObj);
    //读取自由调用tag列表
    $dh = dir($cfg_basedir . '/lyapp/taglib');
    while ($filename = $dh->read()) {
        if (preg_match("/\.lib\./", $filename)) {
            $alltags[] = str_replace('.lib.php', '', $filename);
        }
    }
    $dh->Close();

    //遍历tag元素
    if (!is_array($dtp->CTags)) {
        return '';
    }
    foreach ($dtp->CTags as $tagid => $ctag) {
        $tagname = $ctag->GetName();
        if ($tagname == 'field' && $parfield == 'Y') {
            $vname = $ctag->GetAtt('name');
            if ($vname == 'array' && isset($refObj->Fields)) {
                $dtp->Assign($tagid, $refObj->Fields);
            } else if (isset($refObj->Fields[$vname])) {
                $dtp->Assign($tagid, $refObj->Fields[$vname]);
            } else if ($ctag->GetAtt('noteid') != '') {
                if (isset($refObj->Fields[$vname . '_' . $ctag->GetAtt('noteid')])) {
                    $dtp->Assign($tagid, $refObj->Fields[$vname . '_' . $ctag->GetAtt('noteid')]);
                }
            }
            continue;
        }

        //由于考虑兼容性，原来文章调用使用的标记别名统一保留，这些标记实际调用的解析文件为inc_arclist.php
        if (preg_match("/^(artlist|likeart|hotart|imglist|imginfolist|coolart|specart|autolist)$/", $tagname)) {
            $tagname = 'arclist';
        }
        if (in_array($tagname, $alltags)) {
            if (in_array($tagname, $disable_tags)) {
                if (DEBUG_LEVEL) echo 'DwtX Error:Tag disabled:"' . $tagname . '" ';
                continue;
            }
            if (DEBUG_LEVEL == TRUE) {
                $ttt1 = ExecTime();
            }
            $filename = $cfg_basedir . '/lyapp/taglib/' . $tagname . '.lib.php';
            include_once($filename);
            $funcname = 'lib_' . $tagname;
            $dtp->Assign($tagid, $funcname($ctag, $refObj));
            if (DEBUG_LEVEL == TRUE) {
                $queryTime = ExecTime() - $ttt1;
                //echo '标签：' . $tagname . '载入花费时间：' . $queryTime . "<br />\r\n";
            }
        }
    }
}


