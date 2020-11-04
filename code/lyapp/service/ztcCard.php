<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP. "/datalistcp.class.php");

if(empty($keyword))$keyword="";
if($keyword!="")$wheresql="";
/*---------------------
 function action_save(){ }
 ---------------------*/
CheckRank();
//lyapp/order/line_add.PHP中也使用,更新时一块更新

$query = "SELECT `#@__order`.id AS orderid,`#@__order`.createtime,
          #@__order_addon_ztc.id AS orderListId,#@__order_addon_ztc.name,#@__order_addon_ztc.tel,#@__order_addon_ztc.idcard,#@__order_addon_ztc.idpic,
          #@__order_addon_ztc.idpic_desc ,#@__order_addon_ztc.editdate ,#@__order_addon_ztc.goodsid ,#@__order_addon_ztc.cardcode,
           #@__goods_addon_ztc.rankLenth
          FROM  `#@__order`
          LEFT JOIN #@__order_addon_ztc  ON `#@__order`.id=#@__order_addon_ztc.orderid
          LEFT JOIN #@__goods_addon_ztc  ON #@__order_addon_ztc.goodsid=#@__goods_addon_ztc.goodsid
          WHERE 
              (
                `#@__order`.clientid='$CLIENTID' AND `x_order`.ordertype='orderZtc'   AND `#@__order`.sta=1
              )
            AND `#@__order`.isdel=0 
          ORDER BY `#@__order`.createtime DESC ";
//dump($query);

$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetTemplate("ztcCard.htm");
$dlist->SetSource($query);
$dlist->Display();


