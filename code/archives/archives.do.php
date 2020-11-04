<?php
/**
 * 文档跳转处理
 *
 * @version        $Id: archives.do.php 1 8:26 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');
$ENV_GOBACK_URL = (empty($_COOKIE['ENV_GOBACK_URL']) ? 'archives.php' : $_COOKIE['ENV_GOBACK_URL']);

if(empty($dopost))
{
    ShowMsg('对不起，你没指定运行参数！','-1');
    exit();
}
$archivesid = isset($archivesid) ? preg_replace("#[^0-9]#", '', $archivesid) : '';
$typeid = empty($typeid) ? 0 : intval($typeid);
$channelid = empty($channelid) ? 0 : intval($channelid);

/*--------------------------
//编辑文档
function editArchives(){ }
---------------------------*/
if($dopost=='editArchives')
{
    $query = "SELECT arc.typeid as typeid,arc.userid as userid ,ch.editcon
           FROM `#@__archives_arctiny` arc
           LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid
          WHERE arc.id='$archivesid' ";
    $row = $dsql->GetOne($query);
	//dump($row);
	$userid=$row['userid'];  //存入 session用于在userlogin.class.php中判断当前文档 是否当前登录的用户发布的 如果是则具有编辑权限(在userlogin.class.php中获取完后就注销掉)
	$_SESSION['session_userid_'.$GLOBALS['CUSERLOGIN'] -> getUserId()]=$userid;
    $typeid = $row['typeid'];
    $gurl = $row["editcon"];
    if($gurl=='') $gurl='article_edit.php';
    header("location:{$gurl}?typeid=$typeid&archivesid=$archivesid");
    exit();
}


/*--------------------------
//删除 文档
function editArchives(){ }
---------------------------*/
if($dopost=='delArchives')
{
    $query = "SELECT arc.typeid as typeid,arc.userid as userid,ch.editcon
           FROM `#@__archives_arctiny` arc
           LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid
          WHERE arc.id='$archivesid' ";
    $row = $dsql->GetOne($query);
	//dump($row);
	$userid=$row['userid'];  //存入 session用于在userlogin.class.php中判断当前文档 是否当前登录的用户发布的 如果是则具有编辑权限
	$_SESSION['session_userid_'.$GLOBALS['CUSERLOGIN'] -> getUserId()]=$userid;
    $typeid = $row['typeid'];
    if($gurl=='') $gurl='archives_del.php';
    header("location:{$gurl}?typeid=$typeid&archivesid=$archivesid&dopost=delArchives");
    exit();
}


/*--------------------------
//增加文档
function addArchives();
---------------------------*/
else if($dopost=="addArchives")
{
    //默认文章调用发布表单
    if(empty($typeid) && empty($channelid))
    {
      //  header("location:article_add.php");
      //  exit();
    }
    if(!empty($channelid))
    {
        //根据模型调用发布表单
        $row = $dsql->GetOne("SELECT addcon FROM #@__archives_channeltype WHERE id='$channelid'");
    }
    else
    {
        //根据栏目调用发布表单
        $row = $dsql->GetOne("SELECT ch.addcon FROM `#@__archives_type` tp LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid' ");
    }
    $gurl = $row["addcon"];
    if($gurl=="")
    {
        ShowMsg("对不起，你指的栏目可能有误！","catalog.php");
        exit();
    }

    //跳转并传递参数
    header("location:{$gurl}?typeid={$typeid}&channelid={$channelid}");
    exit();
}
/*--------------------------
//管理文档
function listArchives();
---------------------------*/
else if($dopost=="listArchives")
{
    if(!empty($gurl))
    {
        if(empty($issend))
        {
            $issend = '';
        }
        $gurl = str_replace('..','',$gurl);
        header("location:{$gurl}?issend={$issend}&typeid={$typeid}");
        exit();
    }
    if($typeid>0)
    {
        $row = $dsql->GetOne("SELECT #@__archives_type.typename,#@__archives_channeltype.typename AS channelname,#@__archives_channeltype.id,#@__archives_channeltype.mancon FROM #@__archives_type LEFT JOIN #@__archives_channeltype ON #@__archives_channeltype.id=#@__archives_type.channeltype WHERE #@__archives_type.id='$typeid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = $row["typename"];
        $channelname = $row["channelname"];
        if($gurl=="")
        {
            ShowMsg("对不起，你指的栏目可能有误！","catalog.php");
            exit();
        }
    }
    else if($channelid>0)
    {
        $row = $dsql->GetOne("SELECT typename,id,mancon FROM #@__archives_channeltype WHERE id='$channelid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = "";
        $channelname = $row["typename"];
    }
    
    if(empty($gurl)) $gurl = 'archives.php';
   // header("location:{$gurl}?channelid={$channelid}&cid={$typeid}");
    header("location:{$gurl}?typeid={$typeid}");
    exit();
}

