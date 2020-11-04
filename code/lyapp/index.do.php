<?php
require_once("include/config.php");


if (empty($dopost)) $dopost = '';




//用户登录  ,这个过程未使用,是提交表单登录 原在login中,现在未使用
if($dopost=="login")
{
    if(CheckUserID($mobilephone,'',false)!='ok')
    {
        ShowMsg("你输入的用户名 {$mobilephone} 不合法！","index.php");
        exit();
    }
    if($pwd=='')
    {
        ShowMsg("密码不能为空！","-1",0,2000);
        exit();
    }

    //检查帐号
    $rs = $cfg_ml->CheckUser($mobilephone,$pwd);


    if($rs==0)
    {
        ShowMsg("用户名不存在！", "index.php", 0, 2000);
        exit();
    }
    else if($rs==-1) {
        ShowMsg("密码错误！", "index.php", 0, 2000);
        exit();
    }
    else if($rs==-2) {
        ShowMsg("管理员帐号不允许从前台登录！", "index.php", 0, 2000);
        exit();
    }
    else
    {
        // 清除会员缓存
       // $cfg_ml->DelCache($cfg_ml->M_ID);
        if(empty($gourl) || preg_match("#action|_do#i", $gourl))
        {
            ShowMsg("成功登录，5秒钟后转向系统主页...","index.php",0,2000);
        }
        else
        {
            $gourl = str_replace('^','&',$gourl);
            ShowMsg("成功登录，现在转向指定页面...",$gourl,0,2000);
        }
        exit();
    }
}

//退出登录
 if($dopost=="exit")
{
    $cfg_ml->ExitCookie();

    ShowMsg("成功退出！","index.php",0,2000);
    exit();
}
