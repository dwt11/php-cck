<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


$whereSql = " WHERE 1=1 ";

$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";

//4日期
if ($startdate == "") $startdate = date("Y-m", time()) . "-01";
if ($enddate == "") {
    $nowmonthmaxday = date('t', strtotime($startdate));//上下月的最大天数
    $enddate = date("Y-m", time()) . "-" . $nowmonthmaxday;

}
if ($startdate != "" && $enddate != "") {
    // $title.="日期:从".$startdate."到".$enddate;
    $startdate1 = GetMkTime($startdate);      //(时间戳)获得选定开始日期的开始时间 格式  2014-11-04 00:00:00
    $enddate1 = GetMkTime($enddate) + 86399;    //(时间戳)获得选定结束日期的结束时间格式2014-11-04 23:59:59   86399代表23小时59分59秒
 }


//模板
if (empty($s_tmplets)) $s_tmplets = 'service/leaseQuery.htm';
include DwtInclude($s_tmplets);

//echo $t2-$t1;


function getAppt($date)
{
    //$date = GetDateMk($date);
    $date_str = GetDateMk($date);
    $return_str = "";
    global $dsql;
    $query = "SELECT count(#@__goods.id) as dd,
                    #@__goods.goodsname,#@__goods.id as goodsid FROM
                             #@__order_addon_car 
                            LEFT JOIN #@__order  ON #@__order.id=#@__order_addon_car.orderid
                            LEFT JOIN #@__goods  ON #@__goods.id=#@__order_addon_car.goodsid
                            where   (#@__order_addon_car.start_date<='$date' AND #@__order_addon_car.end_date>='$date')
                            AND  #@__order.isdel=0   AND #@__order.sta=1
                             group by #@__goods.id
                   ";
    //dump($query);
    $dsql->SetQuery($query);
    $dsql->Execute("getAppt8170131");
    while ($row1 = $dsql->GetArray("getAppt8170131")) {
        $dd = $row1["dd"];//预约的数量
        $goodsid = $row1["goodsid"];
        $goodsname = $row1["goodsname"];

          $str="";

        $tr_bg="";
       /* if (time() > $row1["gotime"] - $row1["beforHours"] * 3600){
            //$str = "截止时间到";
            $tr_bg = " class='badge-warning'";
        }elseif($islock== 0){
            //$str="操作员下架";
            $tr_bg = " class='badge-success'";
        }elseif($seats > 0){
            //如果发车线路有座位数,则判断剩余的座位数
            $s_seats = GetLineSeatsNumb_yjyy($lineid);//已经预约座位数
            if ($s_seats > 0) {
                if ($s_seats >= $seats) {
                    //$str="人满下线";
                    $tr_bg = " class='badge-danger'";
                }else{
                    $sy_seats = $seats - $s_seats;
                    $str = " [剩{$sy_seats}件]";
                }
            }
        }*/


        $query = "SELECT  count(state) as dd ,state   FROM
                             x_order_addon_car 
                            LEFT JOIN #@__order  ON #@__order.id=#@__order_addon_car.orderid
                            where   (x_order_addon_car.start_date<='$date' AND x_order_addon_car.end_date>='$date')
                            AND  goodsid='$goodsid'
                             AND  #@__order.isdel=0   AND #@__order.sta=1
                                                       group by state
                   ";
      // dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute("getAppt8170516");
        while ($row221 = $dsql->GetArray("getAppt8170516")) {

            $satae=$row221["state"];
            $dddd=$row221["dd"];
            if ($satae == 0){ $str.= " <a href='lease.php?sta=0&goodsid={$goodsid}&startdate={$date_str}&enddate={$date_str}'>[未提{$dddd}]</a> ";}//else{$str.= " [未提0] ";}
            if ($satae == 1){ $str.= " <a href='lease.php?sta=1&goodsid={$goodsid}&startdate={$date_str}&enddate={$date_str}'>[未还{$dddd}]</a> ";}//else{ $str.= " [未还0] ";}
            if ($satae == 2) {$str.= " <a href='lease.php?sta=2&goodsid={$goodsid}&startdate={$date_str}&enddate={$date_str}'>[已还{$dddd}]</a> ";}//else{ $str.= " [已还0] ";}

        }


            $return_str .= "<div class='clearfix'>
               <a href='lease.php?sta=-1&goodsid={$goodsid}&startdate={$date_str}&enddate={$date_str}'>[共{$dd}辆]</a>  {$str} {$goodsname}
               </div>";
    }
    return $return_str;
}

