<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
//下级返回的金币数量
$jbnum = 0;
$query = "SELECT sum(jbnum) AS dd FROM #@__clientdata_jblog 
            WHERE clientid='$CLIENTID' AND isdel=0
 and (`desc` like '下下级会员购买赠送%' or `desc` like '下级会员购买赠送%' OR  `desc`  LIke '订单删除同时删除赠送的金币%')
	         AND   
                                                      IFNULL(
                                                              (
                                                                SELECT CONCAT(GROUP_CONCAT(jbaa.info),',') FROM #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0 and jbaa.desc LIke '操作错误金币撤消' and jbaa.clientid='$CLIENTID'    
                                                              )
                                                              ,''
                                                             )
                                                       
                                              not like CONCAT('%原编号',id,',%')
  ";
//dump($query);
$row = $dsql->GetOne($query);
if (isset($row['dd'])) {
    $jbnum100 = $row['dd'];
    //dump($jbnum100);
    $jbnum = $jbnum100 / 100;
}

if($jbnum<=0)$jbnum=0;

//获取两级人数
global $client_yxj_Array;
$client_yxj_Array = array();
giveme2($CLIENTID);


//dump($client_yxj_Array);
$rennum = 0;
if (isset($client_yxj_Array[0]) ) $rennum = count($client_yxj_Array[0]);
if ( isset($client_yxj_Array[1])) $rennum +=   count($client_yxj_Array[1]);
//递归下级
function giveme2($id, $gi = 0)
{
    if ($gi >= 2) return;
    global $dsql, $client_yxj_Array;
    $returnArray = "";
    $sqlstr = "SELECT #@__client_addon.clientid ,cl.realname,clw.nickname,clw.photo FROM `#@__client_addon`
            LEFT JOIN `#@__client` cl on cl.id=`#@__client_addon`.clientid
            LEFT JOIN #@__client_weixin clw on cl.id=clw.clientid
            where sponsorid=$id ";
    $dsql->SetQuery($sqlstr);
    $dsql->Execute($gi . $id);
    while ($row = $dsql->GetArray($gi . $id)) {
        //dump($row['clientid']);
        $client_yxj_Array[$gi][] = array("clientid" => $row['clientid'], "realname" => $row['realname'], "nickname" => $row['nickname'], "photo" => $row['photo']); //
        giveme2($row['clientid'], ($gi + 1));
    }
}

$yjnum = $ejnum = 0;

$questr = "select sum(jb.jbnum) as dd ,count(*) as dd1 from #@__clientdata_jblog jb  WHERE  (jb.desc LIKE '下级会员购买赠送%' OR  (jb.`desc`  LIke '订单删除同时删除赠送的金币%' and jb.jbnum='-50000'))  and jb.clientid='$CLIENTID'  and jb.isdel=0 
                  /*筛选出撤消了的金币，如果当前ID，在期中，则不计算为返利金币
                  如果未找到撤消 则赋值为空，如果不赋值 则会出错
                  */
and   
                                                      IFNULL(
                                                              (
                                                                select CONCAT(GROUP_CONCAT(jbaa.info),',') from #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0 and jbaa.desc LIke '操作错误金币撤消' and jbaa.clientid='$CLIENTID'    
                                                              )
                                                              ,''
                                                             )
                                                       
                                              not like CONCAT('%原编号',jb.id,',%')
group by jb.clientid  ";
$rowarc = $dsql->GetOne($questr);
if (is_array($rowarc)) {
    $yjnum100 = $rowarc['dd'];
    $yjnum = $yjnum100 / 100 / 50;
}


$questr = "select sum(jb.jbnum) as dd ,count(*) as dd1 from #@__clientdata_jblog jb  WHERE  (jb.desc LIKE '下下级会员购买赠送%' OR  (jb.`desc`  LIke '订单删除同时删除赠送的金币%' and jb.jbnum='-3000'))  and jb.clientid='$CLIENTID'  and jb.isdel=0 
and   
                                                      IFNULL(
                                                              (
                                                                select CONCAT(GROUP_CONCAT(jbaa.info),',') from #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0 and jbaa.desc LIke '操作错误金币撤消' and jbaa.clientid='$CLIENTID'    
                                                              )
                                                              ,''
                                                             )
                                                       
                                              not like CONCAT('%原编号',jb.id,',%')
group by jb.clientid  ";
$rowarc = $dsql->GetOne($questr);
if (is_array($rowarc)) {
    $ejnum100 = $rowarc['dd'];
    $ejnum = $ejnum100 / 100 / 30;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>我介绍的好友</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
    <style>.imglist {
            min-width: 75px;
            max-width: 75px;
            display: inline-block;
            margin: 5px;
            max-height: 105px;
            min-height: 105px;
            white-space: nowrap;
            text-overflow: ellipsis;
            -o-text-overflow: ellipsis;
            overflow: hidden;
        }</style>
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left">
                <h3>我介绍的好友</h3>
            </div>
            <div class="col-xs-6 text-right">
                <h3 class="font-bold"><?php echo $rennum; ?>人</h3>
            </div>
       <br>
       <br>
            <div class="col-xs-6 text-left">
                <h3>好友共为您创收</h3>
            </div>
            <div class="col-xs-6 text-right">
                <h3 class="font-bold"><?php echo (int)($jbnum);; ?>金币</h3>
            </div>
            <h1 class="text-center ">
                <br>一级:<?php echo $yjnum; ?>人  二级:<?php echo $ejnum; ?>人
            </h1>
        </div>
    </div>

    <div class="ibox-content">
        <div class="row text-center">

            <?php
            if (isset($client_yxj_Array[0]) && is_array($client_yxj_Array[0])) {
                foreach ($client_yxj_Array[0] as $clientinfo) {
                    $name = "无姓名";
                    $realname = $clientinfo["realname"];
                    $nickname = $clientinfo["nickname"];
                    if ($realname != "") {
                        $name = $realname;
                    } elseif ($realname == "" && $nickname != "") {
                        $name = $nickname;
                    }
                    $photo = $clientinfo["photo"];
                    if ($photo == "") $photo = "../../images/zw.jpg";

                    echo "<div  class=\"imglist\">
                                <img  class=\"img-circle m-t-xs img-responsive\"  src=\"$photo\"  data-original=\"{$photo}\" width='60' height='60'>$name
                            </div>
                        ";
                }
            }

            ?>


            <?php
            if (isset($client_yxj_Array[0]) &&isset($client_yxj_Array[1]) && is_array($client_yxj_Array[1])) {
                echo '<div class="hr-line-solid" style="margin: 0; padding: 0;margin-top: 5px;margin-bottom:  5px"></div>';
                foreach ($client_yxj_Array[1] as $clientinfo) {
                    $name = "无姓名";
                    $realname = $clientinfo["realname"];
                    $nickname = $clientinfo["nickname"];
                    if ($realname != "") {
                        $name = $realname;
                    } elseif ($realname == "" && $nickname != "") {
                        $name = $nickname;
                    }
                    $photo = $clientinfo["photo"];
                    if ($photo == "") $photo = "../../images/zw.jpg";

                    echo "<div  class=\"imglist\">
                                <img  class=\"img-circle m-t-xs img-responsive\"  src=\"$photo\"  data-original=\"{$photo}\" width='60' height='60'>$name
                            </div>
                        ";
                }
            }
            ?>

        </div>

    </div>
    <?php include("../index_foot.php"); ?>

</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>
<script src="/lyapp/js/quickButton.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>

</body>
</html>
