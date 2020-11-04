<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
// requir
ExecTime();
if (empty($keyword)) $keyword = '';
if (empty($dopost)) $dopost = '';
//dump($NOWLOGINUSERTOPDEPID);
if ($dopost == 'save') {
    //$clientid_s  源用户  微信用户
    //$clientid_t 目标用户 手工添加用户
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    if ($clientid_s == '') {
        ShowMsg("参数无效！", $$ENV_GOBACK_URL);
        exit();
    }
    $clientid_t = $id;


    //dump($NOWLOGINUSERTOPDEPID);//这里随后多部门的时候要判断属于哪个公司 ??????170202

    //1判断手工添加的用户，是否存在微信表数据，如果存在则删除
    $questr = "SELECT openid  FROM #@__client_depinfos WHERE clientid='$clientid_t'    ";
    //dump($questr);
    $row_1 = $dsql->GetOne($questr);
    if(isset($row_1["openid"])&&$row_1["openid"]!=""){
        ShowMsg("绑定信息失败，用户存在微信信息！", $$ENV_GOBACK_URL);
        exit;
    }

    //2 判断源用户微信注册的账户在微信表中是否存在
    $questr = "SELECT *  FROM #@__client_depinfos WHERE clientid='$clientid_s'    ";
    $row = $dsql->GetOne($questr);
    if ($row) {



        //将微信账户禁用,并将OPENID清空
        $clientid_s_openid=$row["openid"];
        $dsql->ExecuteNoneQuery("UPDATE `#@__client_depinfos` SET `isdel`='1',openid='' WHERE (`clientid`='$clientid_s');");


        $questr11 = "SELECT *  FROM #@__client_weixin WHERE clientid='$clientid_s'    ";
        $row11 = $dsql->GetOne($questr11);
        if ($row11) {
            $nickname=$row11["nickname"];
            $sex=$row11["sex"];
            $city=$row11["city"];
            $province=$row11["province"];
            $country=$row11["country"];
            $photo=$row11["photo"];

            //将微信 的内容绑定到手工的账户上
            $query = "UPDATE #@__client_weixin SET  nickname='$nickname',sex='$sex',city='$city',province='$province',country='$country',photo='$photo'     WHERE clientid='$clientid_t' ";
            $dsql->ExecuteNoneQuery($query);


            //将微信 的内容绑定到手工的账户上
            $time=GetDateTimeMk(time());
            $clientname=getOneCLientRealName($clientid_t)." ".getOneClientMobilephone($clientid_t);
            $query = "UPDATE #@__client SET  description=concat(description,'<br>{$time}绑定到[{$clientname}]账户上,被禁用')     WHERE id='$clientid_s' ";
            //dump($query);
            $dsql->ExecuteNoneQuery($query);


        }



            //将手工账户OPENID绑定上
        $query = "UPDATE #@__client_depinfos SET        openid='$clientid_s_openid'    WHERE clientid='$clientid_t' ";
        $dsql->ExecuteNoneQuery($query);
        $query = "UPDATE #@__client SET        `from`='手工绑定'    WHERE id='$clientid_t' ";
        $dsql->ExecuteNoneQuery($query);
        //dump($sponsorid);


        ShowMsg("绑定信息成功！", $$ENV_GOBACK_URL);
        exit;
    } else {
        ShowMsg("绑定信息失败！", $$ENV_GOBACK_URL);
        exit;
    }
}



/*获取选定用户信息*/
$questr = "
    SELECT c1.*,c2.idcard,c2.sponsorid
    FROM x_client AS c1
    LEFT JOIN #@__client_addon AS c2
    ON c1.id=c2.clientid
    WHERE c1.id='$id'
    ";
$row_1 = $dsql->GetOne($questr);
$realname_1 = $row_1['realname'];
$mobilephone_1 = $row_1['mobilephone'];
$idcard_1 = $row_1['idcard'];
$sponsorname_1 = '';

if ($row_1['sponsorid'] > 0) {

        $sponsorname_1 = getOneCLientRealName($row_1['sponsorid']) . ' ' . getOneClientMobilephone($row_1['sponsorid']);


}




///*获取可以供选择的,没有交易 记录的微信用户*/
$whereSql = "";//不展示锁定会员

if (!isset($keyword)) $keyword = '';
if ($keyword != "") {
    $whereSql .= "AND (
    c1.`realname` LIKE '%$keyword%'
    OR c1.`mobilephone` LIKE '%$keyword%'
    or #@__client_weixin.nickname LIKE '%$keyword%'
    OR c2.`idcard` LIKE '%$keyword%' ) ";
}


//获得数据表名
$sql = "
            SELECT c1.id,c1.senddate,c1.realname,c1.mobilephone,c2.idcard,c2.sponsorid,
            #@__client_weixin.nickname,#@__client_weixin.photo ,
            #@__client_depinfos.depid
            FROM #@__client_weixin
            LEFT JOIN #@__client AS c1 ON c1.id=#@__client_weixin.clientid
            LEFT JOIN #@__client_depinfos  ON #@__client_depinfos.clientid=#@__client_weixin.clientid
            LEFT JOIN #@__client_addon AS c2 ON c1.id=c2.clientid
            LEFT JOIN #@__clientdata_jblog AS jb ON c1.id=jb.clientid
            LEFT JOIN #@__clientdata_jflog AS jf ON c1.id=jf.clientid
            LEFT JOIN #@__order AS lyorder ON c1.id=lyorder.clientid
             WHERE #@__client_depinfos.isdel=0
             and jb.id is NULL
            and jf.id is null
            and lyorder.id is null
            and c1.`from`='微信'
            and c2.sponsorid=0
             $whereSql
              ORDER BY   c1.id DESC
";
//dump($sql);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('id', $id);

//模板
$s_tmplets = 'client_updateWeixin.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
