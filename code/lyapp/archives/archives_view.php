<?php
/**
 *
 * 关于文章权限设置的说明
 * 文章权限设置限制形式如下：
 * 如果指定了会员等级，那么必须到达这个等级才能浏览
 * 如果指定了金币，浏览时会扣指点的点数，并保存记录到用户业务记录中
 * 如果两者同时指定，那么必须同时满足两个条件
 *
 * @version        $Id: view.php 1 15:38 8日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once( "../../include/common.inc.php");
require_once('archives.class.php');

$t1 = ExecTime();

if (empty($okview)) $okview = '';
if (!isset($dopost)) $dopost = '';

$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
//dump($aid);
if ($aid == 0) die(" Request Error! ");

$arc = new Archives($aid);
if ($arc->IsError) ParamError();


//检查阅读权限
$needRank = $arc->Fields['issend'];


if ($needRank < 0) {
    ShowMsg('无权查看!', 'javascript:;');
    exit();
}



$dsql->ExecuteNoneQuery(" UPDATE `#@__archives` SET click=click+1 WHERE id='$aid' ");


$arc->Display();

