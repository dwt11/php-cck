<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();
if (empty($keyword)) $keyword = '使用';
//dump($keyword);
if (!isset($dopost)) $dopost = '';

$whereSql = "";
if ($keyword == "使用") $whereSql .= "AND ( 
                                            #@__clientdata_jflog.desc LIKE '消费%' 
                                            OR #@__clientdata_jflog.desc LIKE '订单删除%' 
                                            OR #@__clientdata_jflog.desc LIKE '转账%' 
                                             OR #@__clientdata_jflog.desc LIKE '管理员手工添加%' 
                                         )
                                            AND jfnum<0";
if ($keyword == "获得") $whereSql .= "AND ( #@__clientdata_jflog.desc LIKE '金币充值赠送%'
                                            OR #@__clientdata_jflog.desc LIKE '下级会员购买赠送%' 
                                            OR #@__clientdata_jflog.desc LIKE '下下级会员购买赠送%' 
                                            OR  #@__clientdata_jflog.desc LIKE '购买赠送%' 
                                            OR  #@__clientdata_jflog.desc LIKE '转为合伙人赠送%'
                                            OR #@__clientdata_jflog.desc LIKE '订单删除恢复积分%'          
                                            OR #@__clientdata_jflog.desc LIKE '管理员手工添加%' 
                                            OR #@__clientdata_jflog.desc LIKE '操作错误积分撤消%'
                                            OR #@__clientdata_jflog.desc LIKE '收到%'
                                        )
                                            AND jfnum>0";

if($keyword=="明细")$whereSql ="";
$query = "SELECT * FROM #@__clientdata_jflog where clientid='$CLIENTID' AND isdel=0 $whereSql   ORDER BY   createtime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize =10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('dopost', $dopost);
if (empty($listtemp)) $listtemp = 'jf.htm';

if($dopost=="ajax"){
//如果是下拉的,则使用以下的模板
    $listtemp =  "jf_ajax.htm" ;
}

$dlist->SetTemplate($listtemp);
$dlist->SetSource($query);
$dlist->Display();



