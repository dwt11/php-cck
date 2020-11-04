<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP. "/datalistcp.class.php");
CheckRank();
if (empty($dopost)) $dopost = '';

/*---------------------
 function action_save(){ }
 ---------------------*/

$query = "SELECT os.id,olist.orderid,olist.id AS orderlistid,olist.name,olist.tel,olist.idcard,olist.idpic
            ,o1.createtime,olist.goodsid FROM    #@__ztc_share  os
           LEFT JOIN #@__order_addon_ztc olist ON olist.id=os.orderListId
           LEFT JOIN #@__order o1 on o1.id=olist.orderid
          WHERE  os.isdel='0' AND o1.isdel='0' AND o1.sta=1 AND os.clientid_n='$CLIENTID' ORDER BY o1.createtime DESC ";

//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 10;
$dlist->SetTemplate("ztc_share.htm");
$dlist->SetSource($query);
$dlist->Display();


