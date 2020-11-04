<?php
require_once(dirname(__FILE__) . "/../include/config.php");
//CheckRank();此处不能加权限  后台也要引用
//-----------------------------------后台orderztc.list.idcard.search.php内容重复
//与当前身份证，当前日期不在任何订单的一年范围内

    echo Get_ztc_list_idcard_search($idcard);
/*return "0";///已经购买过
     return "1";//未买过*/

    exit;