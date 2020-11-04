<?php
/**
 * 删除部门
 *
 * @version        $Id: dep_del.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');

$id = preg_replace("#[^0-9]#", '', $id);
$dopost = isset($dopost) ? $dopost : "";   

    if(empty($userok)) $userok="";
    if($userok!="yes")
    {
        $randcode = mt_rand(10000, 99999);
        $safecode = substr(md5($cfg_cookie_encode.$randcode),0,24);
        require_once(DWTINC."/oxwindow.class.php");
        $wintitle = "删除登录信息";
        $wecome_info = "::删除登录信息";
        $win = new OxWindow();
        $win->Init("emp_user_del.php","js/blank.js","POST");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("userok", "yes");
        $win->AddHidden("randcode", $randcode);
        $win->AddHidden("safecode", $safecode);
        $win->AddHidden("id", $id);
        $win->AddTitle("系统警告！");
        $win->AddMsgItem("你确信要删除用户：$userName 吗？","50");
        $win->AddMsgItem("安全验证串：<input name='safecode' type='text' id='safecode' size='16' style='width:200px' />&nbsp;(复制本代码： <font color='red'>$safecode</font>)","30");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode),0,24);
    if($safecodeok!=$safecode)
    {
        ShowMsg("请填写正确的安全验证串！");
        exit();
    }

    //不能删除id为1的创建人帐号，不能删除自己
    $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__sys_admin` WHERE id='$id' AND id<>1 AND id<>'".$GLOBALS['CUSERLOGIN']->getUserId()."' ");
    if($rs>0)
    {
        $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");

        ShowMsg("成功删除一个帐户！",$$ENV_GOBACK_URL);
    }
    else
    {
        ShowMsg("不能删除id为1的创建人帐号，不能删除自己！","-1",0,3000);
    }
    exit();
