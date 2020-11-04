<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();

$whereSql = "  ";

$query = "SELECT * FROM  #@__ordergwc  WHERE  clientid='$CLIENTID'   ORDER BY  createtime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("GWC.htm");
$dlist->SetSource($query);
$dlist->Display();


//获取商品信息
function getGoodsInfo($goodsid){
    global $dsql;
    $str = "";
    $query3 = "SELECT litpic,goodscode,goodsname,price  FROM  #@__goods  WHERE id=$goodsid ";

    //dump($query3);
    $dsql->SetQuery($query3);
    $dsql->Execute("999");
    while ($row1 = $dsql->GetArray("999")) {
        $photo = $row1["litpic"];
        if ($photo == "") $photo = "/images/arcNoPic.jpg";
        $goodscode = $row1["goodscode"];
        $goodsname = $row1["goodsname"];
        $price = $row1["price"];
        $str = "<img src=\"$photo\" />
                【{$goodscode}】 $goodsname
                <br>
                <span class=\"text-danger \"> ￥$price</span>
                ";
    }

    return $str;
}


//获取购物车商品的子记录
function getGwcList($GWCid){
    global $dsql;
    $str = "";
    $query3 = "SELECT *  FROM  #@__ordergwc_addon_lycp  WHERE GWCid=$GWCid ";

    //dump($query3);
    $dsql->SetQuery($query3);
    $dsql->Execute("000");
    while ($row1 = $dsql->GetArray("000")) {
        $addonid = $row1["id"];
        $lineid = $row1["lineid"];
        $appttime=($row1["appttime"]);
        $appttime_str=GetDateNoYearMk($appttime);
        $realname= $row1["realname"];
        $tel= $row1["tel"];
        $idcard= $row1["idcard"];
        $state=$str_class="";//状态
        if(!GetLineBeforHoursIStrue($lineid, $appttime)){
            $state="失效";
            $str_class="style='text-decoration:line-through;'";
        }
        $str .= "
            <div class=\"GWCservicelist  description-code\">
                <div class=\"pull-right\">
                  $state  <a href='GWC_show.php?addonid=1878' >详情</a>
                </div>
              <div $str_class>  [$appttime_str] $realname $idcard</div>  
            </div>
            ";
    }

    return $str;

}