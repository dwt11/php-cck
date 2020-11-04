<?php
/**
 * 客户列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日Z tianya $
 * @package        DwtX.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

$where = "WHERE 1=1 ";


if (!isset($day_s) || $day_s=="")    $day_s=date("Y-m-d", time()-604800);


if (!isset($day_d) || $day_d=="")    $day_d=date("Y-m-d", time());




if (!isset($typename)) $typename = '';




if($day_s!=""){
    $day_s_int=GetMkTime($day_s." 00:00:00");
    $where .= " AND  createtime>='$day_s_int'";
}
if($day_d!=""){
    $day_d_int=GetMkTime($day_d." 23:59:59");
    $where .= " AND  createtime<='$day_d_int'";
}
//dump($startdate);

if ($typename == "") {
    $wheretypename = "
                                when `desc` like '下级会员购买赠送%' then '增加-二级赠送' 
                            when `desc` like '下下级会员购买赠送%' then '增加-三级赠送'
                            when `desc` like '金币充值%' then '增加-金币充值'
                            when (`desc` like '删除提现明细%') or (`desc` like '提现审核不通过恢复金币%') then '增加-提现删除或不通过恢复'
                            when (`desc` like '购买多件赠送%')     then '增加-购买多件赠送' 
                            when (`desc` like '操作错误金币撤消%') AND   (jbnum>0) then '增加-操作错误金币增加'
                            when `desc` like '订单删除恢复金币%' then '增加-订单删除恢复本人金币'
                            when (`desc` like '管理员手工充值%') AND (jbnum>0) then '增加-手工充值'
                            
                            when `desc` like '转为合伙人金币减少%' then '减少-转为合伙人金币减少'
                            when `desc` like '订单删除同时删除赠送的金币%' then '减少-订单删除扣除送给他人的金币'
                            when (`desc` like '管理员手工充值%') AND (jbnum<0) then '减少-手工扣除'
                            when (`desc` like '管理员手工提现%')  then '减少-手工提现'
                            when (`desc` like '会员提现申请%')  then '减少-会员提现申请'
                            when (`desc` like '操作错误金币撤消%') AND   (jbnum<0) then '减少-操作错误金币扣除'

    ";
    //dump($nowmonth);
} else {

    if ($typename == "增加-二级赠送") $wheretypename = " when `desc` like '下级会员购买赠送%' then '增加-二级赠送' ";
    if ($typename == "增加-三级赠送") $wheretypename = "when `desc` like '下下级会员购买赠送%' then '增加-三级赠送'";
    if ($typename == "增加-金币充值") $wheretypename = "when `desc` like '金币充值%' then '增加-金币充值'";
    if ($typename == "增加-提现删除或不通过恢复") $wheretypename = "when (`desc` like '删除提现明细%') or (`desc` like '提现审核不通过恢复金币%') then '增加-提现删除或不通过恢复'";
    if ($typename == "增加-购买多件赠送") $wheretypename = "when (`desc` like '购买多件赠送%')     then '增加-购买多件赠送' ";
    if ($typename == "增加-操作错误金币增加") $wheretypename = "when (`desc` like '操作错误金币撤消%') AND   (jbnum>0) then '增加-操作错误金币增加'";
    if ($typename == "增加-订单删除恢复本人金币") $wheretypename = "when `desc` like '订单删除恢复金币%' then '增加-订单删除恢复本人金币'";
    if ($typename == "增加-手工充值") $wheretypename = "when (`desc` like '管理员手工充值%') AND (jbnum>0) then '增加-手工充值'";

    if ($typename == "减少-转为合伙人金币减少") $wheretypename = "when `desc` like '转为合伙人金币减少%' then '减少-转为合伙人金币减少'";
    if ($typename == "减少-订单删除扣除送给他人的金币") $wheretypename = "when `desc` like '订单删除同时删除赠送的金币%' then '减少-订单删除扣除送给他人的金币'";
    if ($typename == "减少-手工扣除") $wheretypename = "when (`desc` like '管理员手工充值%') AND (jbnum<0) then '减少-手工扣除'";
    if ($typename == "减少-手工提现") $wheretypename = "when (`desc` like '管理员手工提现%')  then '减少-手工提现'";
    if ($typename == "减少-会员提现申请") $wheretypename = "when (`desc` like '会员提现申请%')  then '减少-会员提现申请'";
    if ($typename == "减少-操作错误金币扣除") $wheretypename = "when (`desc` like '操作错误金币撤消%') AND   (jbnum<0) then '减少-操作错误金币扣除'";


}

$total_jbnum = $total_bs=0;
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$query = "SELECT  FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,desc_t,sum(jbnum) as jbnum
						FROM (

                            SELECT 
                            jbnum,createtime,
                            (select case 
                            
                            $wheretypename


                            end )
                            as `desc_t`
                            
                            
                             FROM #@__clientdata_jblog
      					    INNER JOIN x_client_depinfos ON x_clientdata_jblog.clientid=x_client_depinfos.clientid
						  WHERE  x_client_depinfos.isdel=0 
                       ORDER BY desc_t
) temp_t
						  $where   AND desc_t!=''
						  GROUP BY FROM_UNIXTIME(createtime,'%Y-%m-%d'),desc_t
						  
						  
						  
						 ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 300;


//模板
if (empty($s_tmplets)) $s_tmplets = 'jbCountTypeByDay.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

