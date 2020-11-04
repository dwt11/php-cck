<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();

if (empty($keyword)) $keyword = '使用';
//dump($keyword);
if (!isset($dopost)) $dopost = '';

$whereSql="";
if($keyword=="使用")$whereSql .= "AND ( #@__clientdata_jblog.desc LIKE '转为合伙人金币减少'
                                         OR #@__clientdata_jblog.desc LIKE '消费%' 
                                         OR  #@__clientdata_jblog.desc LIKE '操作错误金币撤消%' 
                                         OR   #@__clientdata_jblog.desc LIKE '会员提现申请%'
                                         OR  #@__clientdata_jblog.desc LIKE '管理员手工提现%' 
                                         OR  #@__clientdata_jblog.desc LIKE '订单删除%' 
                                         OR  #@__clientdata_jblog.desc LIKE '转账%' 
                                            )
                                            AND jbnum<0";
if($keyword=="获得")$whereSql .= "AND ( #@__clientdata_jblog.desc LIKE '金币充值%'
                                       OR #@__clientdata_jblog.desc LIKE '下级会员购买赠送%'
                                         OR #@__clientdata_jblog.desc LIKE '下下级会员购买赠送%' 
                                         OR #@__clientdata_jblog.desc LIKE '购买多件赠送%' 
                                          OR #@__clientdata_jblog.desc LIKE '订单删除%'  
                                         OR  #@__clientdata_jblog.desc LIKE '操作错误金币撤消%' 
                                         OR  #@__clientdata_jblog.desc LIKE '管理员手工充值%'  
                                            OR  #@__clientdata_jblog.desc LIKE '提现审核不通过恢复金币%'
                                            OR  #@__clientdata_jblog.desc LIKE '删除提现明细，恢复金币%'
                                            OR  #@__clientdata_jblog.desc LIKE '收到%'
                                            )
                                            AND jbnum>0";

if($keyword=="明细")$whereSql ="";
$query = "SELECT * FROM #@__clientdata_jblog WHERE clientid='$CLIENTID' AND isdel=0 $whereSql   ORDER BY   createtime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize =10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('dopost', $dopost);
if (empty($listtemp)) $listtemp = 'jb.htm';

if($dopost=="ajax"){
//如果是下拉的,则使用以下的模板
    $listtemp =  "jb_ajax.htm" ;
}

$dlist->SetTemplate($listtemp);
$dlist->SetSource($query);
$dlist->Display();





