<?php

/**
 * 退出
 *
 *
 * @version        $Id: exit.php 151009
 * @package
 * @copyright
 * @license
 * @link
 */
require_once(dirname(__FILE__).'/include/common.inc.php');
require_once(DWTINC.'/userlogin.class.php');
$GLOBALS['CUSERLOGIN'] = new userLogin();
$GLOBALS['CUSERLOGIN']->exitUser();
if(empty($needclose))
{
    header('location:login.php');
}
else
{
    $msg = "<script language='javascript'>
            if(document.all) window.opener=true;
            window.close();
            </script>";
    echo $msg;
}