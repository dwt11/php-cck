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
$ENV_GOBACK_URL = (empty($_COOKIE['ENV_GOBACK_URL']) ? 'goods.php' : $_COOKIE['ENV_GOBACK_URL']);
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = isset($id) ? preg_replace("#[^0-9]#", '', $id) : '';


/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */


if ($dopost == 'GetOneClientInfo') {
    $retstr = "";
    $sql = "SELECT  cl.realname,cl.mobilephone,
          (cladd.jfnum/100) AS jfnum,(cladd.jbnum/100) AS jbnum,cladd.sponsorid,cladd.idcard
          FROM #@__client_depinfos
             LEFT JOIN #@__client cl on cl.id=#@__client_depinfos.clientid
             LEFT JOIN #@__client_addon cladd on cl.id=cladd.clientid
             WHERE #@__client_depinfos.clientid='$clientid'";
    $row = $dsql->GetOne($sql);
    if (is_array($row)) {
        $sponsorname = getOneCLientRealName($row["sponsorid"]);
        $row["sponsorname"] = $sponsorname;//增加推荐人
        $retstr = json_encode($row);
    }
    echo $retstr;
}

if ($dopost == 'GetOneClientJBJF') {
    if (empty($clientid)) $clientid = 0;

    $array["jbnum"] = GetClientJBJFnumb('jb', $clientid);
    $array["jfnum"] = GetClientJBJFnumb('jf', $clientid);
    echo json_encode($array);
}

