<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


$whereSql = " WHERE 1=1  ";

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
    //dump(GetDateTimeMk($startdate1).GetDateTimeMk($enddate1));
    $whereSql .= " And (appttime>= '$startdate1' AND appttime<= '$enddate1')"; //qq
}


//在数据表里加了优化，这里不再优化了，GROUP BY 无法优化总有索引170928
$query = "SELECT * FROM
(
			SELECT appttime 
			FROM #@__order_addon_lycp
	                                  INNER JOIN #@__order  ON #@__order.id=#@__order_addon_lycp.orderid
	                                  WHERE   (#@__order.isdel=0 OR #@__order.isdel=4 ) 
	                                        AND  #@__order_addon_lycp.isdel=0 
	                                        AND  #@__order.sta=1 /*AND UNIX_TIMESTAMP(now())-86400*4< (appttime)仅显示包含当前日期三天前 和当前日期 以后的数据 */
	                                         AND #@__order_addon_lycp.id > 21748
		  ORDER BY   #@__order_addon_lycp.id asc
) as yuyue
$whereSql
GROUP BY FROM_UNIXTIME(appttime,'%Y-%m-%d')";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 50;


//模板
if (empty($s_tmplets)) $s_tmplets = 'apptQuery.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getAppt($date)
{
    $date = GetDateMk($date);
    $date_min_str=$date." 00:00:00";//当天最小时间
    $date_max_str=$date." 23:59:59";//当天最大时间
    $date_min_int=GetMkTime($date_min_str);
    $date_max_int=GetMkTime($date_max_str);

    $return_str = "";
    global $dsql;
    $query = "SELECT count(#@__line.id) as dd,#@__line.tmp,#@__line.id as lineid,#@__line.islock,#@__line.seats,#@__line.gotime,#@__line.beforHours,
                    #@__goods.goodsname,#@__order_addon_lycp.appttime FROM
                             #@__order_addon_lycp 
                            INNER JOIN #@__order  ON #@__order.id=#@__order_addon_lycp.orderid
                            INNER JOIN #@__line  ON #@__line.id=#@__order_addon_lycp.lineid
                            INNER JOIN #@__goods  ON #@__goods.id=#@__line.goodsid
                            WHERE   
                             (
                              #@__order_addon_lycp.appttime >='{$date_min_int}'
                              AND 
                              #@__order_addon_lycp.appttime  <='{$date_max_int}'
                          )
                          AND  (#@__order.isdel=0 OR #@__order.isdel=4 ) AND  #@__order_addon_lycp.isdel=0 AND #@__order.sta=1
                             GROUP BY #@__order_addon_lycp.lineid
                   ";
    // dump($query);
    $dsql->SetQuery($query);
    $dsql->Execute("getAppt8170131");
    while ($row1 = $dsql->GetArray("getAppt8170131")) {
        $dd = $row1["dd"];//预约的人数
        $type = $row1['tmp'];
        $lineid = $row1["lineid"];
        $goodsname = $row1["goodsname"];
        $apptime = $row1["appttime"];
        $islock = $row1["islock"];//1开放 0下架
        $seats = $row1['seats'];//总座位数
        $str="";

        $tr_bg="";
        if (time() > $row1["gotime"] - $row1["beforHours"] * 3600){
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
        }

        $return_str .= "<div class='clearfix'><a style='margin-top: 3px' title='$goodsname' $tr_bg href='/service/apptQuery_list.php?gotime=$date&lineid=$lineid'><b>[$type][{$dd}人]</b> {$goodsname}</b> {$str}</a></div>";
    }
    return $return_str;
}

