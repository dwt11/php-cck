<?php
require_once(dirname(__FILE__) . "/../include/config.php");


//获取   我的预约的SQL语句,会员中心和我的预约共用

/**
 * @param            $clientid
 * @param            $whereSQL
 * @param            $orderby
 * @param int|string $sta 1未出行,显示详细  2已出行显示简单点
 *
 * @return string
 */
function getApptSQL($clientid, $whereSQL, $orderby, $sta = "1")
{


    //获取当前登录人所有的直通车卡,然后从旅游线路预约中,查询不是当前clientid预约的此直通车卡的预约记录
    global $dsql;
    $ztccard_WHERESQL = "";
    $query44 = "
    SELECT GROUP_CONCAT(#@__order_addon_ztc.id) AS orderlistztcid  FROM  `#@__order_addon_ztc` 
                        LEFT JOIN   `#@__order` ON   `#@__order`.id=  `#@__order_addon_ztc`.orderid   
                         WHERE `#@__order`.clientid='{$clientid}' AND `#@__order`.isdel=0 AND `#@__order`.sta=1";
    $row44getztc = $dsql->getone($query44);
    if (isset($row44getztc["orderlistztcid"]) && $row44getztc["orderlistztcid"] != "") {
        $orderlistztcid_s = $row44getztc["orderlistztcid"];
        $ztccard_WHERESQL = " OR ( #@__order.clientid!='{$clientid}' AND #@__order_addon_lycp.orderlistztcid IN ($orderlistztcid_s) )  ";
    }


    //默认列表和统计条数时,用简单的,打开快
    $query = "SELECT 
                 #@__order_addon_lycp.appttime,
           #@__order_addon_lycp.orderCarId,
           #@__order_addon_lycp.tjsite,
          #@__order_addon_lycp.realname,
          #@__order_addon_lycp.tel,
          #@__order_addon_lycp.idcard,
          
          #@__order_addon_lycp.info,
          #@__order_addon_lycp.seatNumber,
          
          #@__order_addon_lycp.orderlistztcid,
          #@__order.clientid AS orderclientid,
          #@__order.createtime,
          #@__order_addon_lycp.goodsid
            FROM #@__order_addon_lycp 
            INNER JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
            WHERE 
            #@__order.sta=1 AND  (#@__order.isdel=0 OR #@__order.isdel=4 )  
            AND #@__order_addon_lycp.isdel=0
            AND ( #@__order.clientid='{$clientid}'  $ztccard_WHERESQL )/*其他人*/
            AND x_order.ordertype='orderLycp'
            $whereSQL
             
            ";


    //未出行,显示详细的信息
    if ($sta == "1") {
        //获取当前登录人的订单,包含自己的直通车卡  共享的直能车卡  以及其他 人
        $query = "SELECT 
                 #@__order_addon_lycp.appttime,
                 #@__line.linedaynumb,
               #@__line.diaodudianhua,
           #@__order_addon_lycp.orderCarId,
           #@__order_addon_lycp.tjsite,
            #@__line.backtime,
          #@__order_addon_lycp.realname,
          #@__order_addon_lycp.tel,
          #@__order_addon_lycp.idcard,
          #@__order.`desc`,
          #@__order_addon_lycp.info,
          #@__order_addon_lycp.seatNumber,
          
          #@__order_addon_lycp.orderlistztcid,
          #@__order.clientid AS orderclientid,
          #@__order.createtime,
          #@__order_addon_lycp.goodsid
            FROM #@__order_addon_lycp 
            INNER JOIN #@__line  ON #@__line.id = #@__order_addon_lycp.lineid
            INNER JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
            WHERE 
            #@__order.sta=1 AND  (#@__order.isdel=0 OR #@__order.isdel=4 )  
            AND #@__order_addon_lycp.isdel=0
            AND ( #@__order.clientid='{$clientid}'  $ztccard_WHERESQL )/*其他人*/
            AND x_order.ordertype='orderLycp'
            $whereSQL
            
            ";
/*        ORDER  BY   #@__order_addon_lycp.appttime $orderby */
    }


//dump($query);
    return $query;
}

