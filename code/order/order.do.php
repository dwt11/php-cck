<?php
/**
 * 商品跳转处理
 *
 * @version        $Id: goods.do.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once('../config.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}



if ($dopost == 'GetOneOrderInfo') {
    $retstr = "";
    $query = "SELECT x_order_addon_ztc.name,x_order_addon_ztc.idpic,x_order_addon_ztc.tel,x_order_addon_ztc.idcard,
                x_client.realname,
                x_order.desc,x_order.ordernum ,from_unixtime(x_order.createtime,'%Y-%m-%d') as createtime 
                FROM x_order_addon_ztc 
                LEFT JOIN x_order    ON x_order.id=x_order_addon_ztc.orderid
                LEFT JOIN x_client ON x_order.clientid=x_client.id
                WHERE x_order_addon_ztc.orderid='$orderid'";
    //dump($query);
    $dsql->SetQuery($query);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $row_list[] = $row;
    }
    if (is_array($row_list)) {
        $retstr = json_encode($row_list);
    }
    echo $retstr;
}
if ($dopost == 'GetOrderZtcListInfo') {
    $retstr = "";
    $orderZtcListId=rtrim($orderZtcListId,",");
    if($orderZtcListId==""){
        echo "";
    }else {

        $query = "SELECT x_order_addon_ztc.name,x_order_addon_ztc.idpic,x_order_addon_ztc.tel,x_order_addon_ztc.idcard
                FROM x_order_addon_ztc 
                WHERE id in($orderZtcListId)";
        //dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            $row_list[] = $row;
        }
        if (is_array($row_list)) {
            $retstr = json_encode($row_list);
        }
        echo $retstr;
    }
}
