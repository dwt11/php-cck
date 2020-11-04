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


if (!isset($nowyear)) $nowyear = '';
if($nowyear==""){
    $nowyear=date("Y",time());
}
$total_total=$total_addjb=$total_bs=$total_subjb=0;
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$query = "SELECT  FROM_UNIXTIME(createtime,'%Y-%m') AS nowmonth,sum(IF(jbnum>0,jbnum,0)) AS addjb,
						 sum(IF(jbnum<0,jbnum,0)) AS subjb,count(x_clientdata_jblog.id) AS  bs,
						 sum(jbnum) AS 				 total
						 
						FROM #@__clientdata_jblog
					    INNER JOIN x_client_depinfos ON x_clientdata_jblog.clientid=x_client_depinfos.clientid
						  WHERE  x_client_depinfos.isdel=0 AND    FROM_UNIXTIME(createtime,'%Y')='$nowyear'
						  GROUP BY FROM_UNIXTIME(x_clientdata_jblog.createtime,'%Y-%m')";



//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;



//模板
if (empty($s_tmplets)) $s_tmplets = 'jbCountByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;




function getNumb($clientid,$js)
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
    if($js==2)$questr = "select sum(jb.jbnum) as dd  from #@__clientdata_jblog jb  WHERE jb.desc LIKE '下下级会员购买赠送%' and jb.clientid='$clientid'   and jb.isdel=0 
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
        $str= "金额：".$rowarc["dd"]." 人数：".$rowarc['dd']/50;
        $str= $rowarc['dd']/50;
        //if($js==2)$str= "金额：".$rowarc["dd"]." 人数：".$rowarc['dd']/30;
        if($js==2)$str=$rowarc['dd']/30;
        return $str;
    }
    return "";
}
