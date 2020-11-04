<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();


$sql="SELECT count(*) as dd FROM `#@__client`
                                LEFT JOIN `#@__client_depinfos` ON #@__client_depinfos.clientid=#@__client.id
                                WHERE  #@__client_depinfos.isdel=0   AND mobilephone='$mobilephone' AND #@__client.id!='$CLIENTID'
                                 ";
$chRow1 = $dsql->GetOne($sql);
//判断是否有多个手机号(不分验证与否)
//dump($sql);
$ismoreclient="";
if($chRow1["dd"]>1){
    $ismoreclient="[多个账户,谨慎操作]";
}


$chRow = $dsql->GetOne("SELECT #@__client.id,realname FROM `#@__client`
                                LEFT JOIN `#@__client_depinfos` ON #@__client_depinfos.clientid=#@__client.id
                                WHERE  #@__client_depinfos.isdel=0 AND mobilephone_check=1 AND mobilephone='$mobilephone' AND #@__client.id!='$CLIENTID'
                                 ");

//只获取 验证了的手机号

//dump($chRow);
if(is_array($chRow)){
    $realname=$chRow["realname"];
    if($realname==""){
        $realname="验证账户未填写姓名";
    }else{
        $realname="*".cn_substr_utf8($chRow["realname"],7,2);
    }
    echo $realname.$ismoreclient;
}else {
    echo "账户不可用";
}
exit();
