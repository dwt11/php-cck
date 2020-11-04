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

$where = "";
if (!isset($nowyear)) $nowyear = '';
if (!isset($nowmonth)) $nowmonth = '';
if (!isset($typename)) $typename = '';
if ($nowyear==""&&$nowmonth == "") {
    $nowyear = date("Y", time());
    $where = " where  FROM_UNIXTIME(createtime,'%Y')='$nowyear'";
    //dump($nowmonth);
}
if ($nowyear!= "") {
    $where = " where  FROM_UNIXTIME(createtime,'%Y')='$nowyear'";
    //dump($nowmonth);
}

if($nowmonth!=""){
    $where = " where  FROM_UNIXTIME(createtime,'%Y-%m')='$nowmonth'";
}

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

//dump($typename);
//if($nowyear==""){
// $nowyear=date("Y",time());
// $where= " where  FROM_UNIXTIME(createtime,'%Y')='$nowyear'";
//}
$total_jbnum =$total_bs= 0;
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$query = "SELECT  FROM_UNIXTIME(createtime,'%Y-%m') AS nowmonth,desc_t,sum(jbnum) as jbnum,count(*) AS  bs
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
						  GROUP BY FROM_UNIXTIME(createtime,'%Y-%m'),desc_t
						  
						  
						  
						 ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 300;


//模板
if (empty($s_tmplets)) $s_tmplets = 'jbCountTypeByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getNumb($clientid, $js)
{
    global $dsql;
    $questr = "select sum(jb.jbnum) as dd  from #@__clientdata_jblog jb  WHERE jb.desc LIKE '下级会员购买赠送%' and jb.clientid='$clientid'   and jb.isdel=0 
                  /*筛选出撤消了的金币，如果当前ID，在期中，则不计算为返利金币
                  如果未找到撤消 则赋值为空，如果不赋值 则会出错
                  */
                  and   
                              IFNULL(
                                      (
                                        select CONCAT(GROUP_CONCAT(jbaa.info),',') from #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0 and jbaa.desc LIke '操作错误金币撤消' and jbaa.clientid='$clientid'    
                                      )
                                      ,''
                                     )
                               
                      not like CONCAT('%原编号',jb.id,',%')
                
                 group by jb.clientid  ";
    if ($js == 2) $questr = "select sum(jb.jbnum) as dd  from #@__clientdata_jblog jb  WHERE jb.desc LIKE '下下级会员购买赠送%' and jb.clientid='$clientid'   and jb.isdel=0 
                         and   
                                                      IFNULL(
                                                              (
                                                                select CONCAT(GROUP_CONCAT(jbaa.info),',') from #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0 and jbaa.desc LIke '操作错误金币撤消' and jbaa.clientid='$clientid'    
                                                              )
                                                              ,''
                                                             )
                                              not like CONCAT('%原编号',jb.id,',%')
                        group by jb.clientid  ";
    //dump($questr);
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        $str = "金额：" . $rowarc["dd"] . " 人数：" . $rowarc['dd'] / 50;
        $str = $rowarc['dd'] / 50;
        //if($js==2)$str= "金额：".$rowarc["dd"]." 人数：".$rowarc['dd']/30;
        if ($js == 2) $str = $rowarc['dd'] / 30;
        return $str;
    }
    return "";
}
