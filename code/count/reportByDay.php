<?php
/**
 * 客户列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日Z tianya $
 * @package        DwtX.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

$whereSQL = "  ";
if (!isset($day_s) || $day_s=="")    $day_s=date("Y-m-d", time()-604800);
if (!isset($day_d) || $day_d=="")    $day_d=date("Y-m-d", time());




if($day_s!=""){
    $day_s_int=GetMkTime($day_s." 00:00:00");
    $whereSQL .= " AND  createtime>='$day_s_int'";
}
if($day_d!=""){
    $day_d_int=GetMkTime($day_d." 23:59:59");
    $whereSQL .= " AND  createtime<='$day_d_int'";
}




$report_array=array();



$whereSQL_str_sr=str_replace("createtime","paytime",$whereSQL);
//收入-订单收到的现金付款和微信 付款
$query="
        SELECT 
            '收入' AS r_name,
            FROM_UNIXTIME(paytime,'%Y-%m-%d') AS nowday,
            paytype,
            ordertype,
            SUM(paynum) AS  total,
            COUNT(x_order.id) AS  bs
        FROM x_order
         INNER JOIN x_client_depinfos ON x_order.clientid=x_client_depinfos.clientid
        WHERE x_order.sta=1 AND x_order.isdel=0 AND  x_client_depinfos.isdel=0 
       
                 
                $whereSQL_str_sr
        GROUP BY 
                FROM_UNIXTIME(paytime,'%Y-%m-%d'),
                paytype,
                ordertype
        ORDER BY createtime DESC,ordertype,paytype DESC
";
//dump($query);
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];
    $nowday = $row1["nowday"];
    $paytype = $row1["paytype"];
    $ordertype = $row1["ordertype"];
    $total = $row1["total"]/100;
    $bs = $row1["bs"];
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}
// dump($report_array["收入"]);

//收入-金币充值
$query="
        SELECT  
            '收入' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '金币充值' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                `desc` like '金币充值%'
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}





$whereSQL_str_del=str_replace("createtime","returntime",$whereSQL);

//支出-支付成功的订单被删除
$query="
        SELECT 
            '支出' AS r_name,
            FROM_UNIXTIME(returntime,'%Y-%m-%d') AS nowday,
            '订单删除' AS paytype,
            SUM(paynum) AS  total,
            COUNT(id) AS  bs
        FROM x_order
        WHERE  
                sta=1 AND isdel=1
                $whereSQL_str_del
        GROUP BY 
                FROM_UNIXTIME(returntime,'%Y-%m-%d')
        ORDER BY createtime DESC,ordertype,paytype DESC
";
//dump($query);
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];
    $nowday = $row1["nowday"];
    $paytype = $row1["paytype"];
    $ordertype = "";
    $total = $row1["total"]/100;
    $bs = $row1["bs"];
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

//dump($report_array["支出"]);




//支出-提现 微信付款和现金付款
$whereSQL_str=str_replace("createtime","payment_time",$whereSQL);
$query="

SELECT  
	'支出' AS r_name,
	FROM_UNIXTIME(payment_time,'%Y-%m-%d') AS nowday,
    '提现付款' AS paytype,
	SUM(jbnum) AS total,
	COUNT(id) AS  bs
FROM x_clientdata_extractionlog 
WHERE 
	(`status`=3 OR `status`=5 )/*3微信银包 5现金付款*/
  AND payment_time>0
  $whereSQL_str
GROUP BY 
	FROM_UNIXTIME(payment_time,'%Y-%m-%d')
ORDER BY createtime DESC		
/*金币提现付款成功*/
";
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}
















//---------------------------------------------------------------------------------------金币增加-二级
$whereSQL_str=str_replace("createtime","payment_time",$whereSQL);
$query="
SELECT
	'金币增加' AS r_name,
	FROM_UNIXTIME(createtime, '%Y-%m-%d') AS nowday,
	'二级金币' AS paytype,
	SUM(jbnum) AS total,
	COUNT(id) AS bs
FROM
	x_clientdata_jblog
WHERE
	`desc` LIKE '下级会员购买赠送%'
	$whereSQL
GROUP BY
	FROM_UNIXTIME(createtime, '%Y-%m-%d')
	
ORDER BY
	createtime DESC;

";
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

$query="
SELECT
	'金币增加' AS r_name,
	FROM_UNIXTIME(createtime, '%Y-%m-%d') AS nowday,
	'三级金币' AS paytype,
	SUM(jbnum) AS total,
	COUNT(id) AS bs
FROM
	x_clientdata_jblog
WHERE
	`desc` LIKE '下下级会员购买赠送%'
	$whereSQL
GROUP BY
	FROM_UNIXTIME(createtime, '%Y-%m-%d')
ORDER BY
	createtime DESC;

";
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}






//金币增加
$query="
        SELECT  
            '金币增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '手工添加' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                `desc` like '管理员手工充值%' AND (jbnum>0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}
//金币增加
$query="
        SELECT  
            '金币增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '充值卡添加' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                `desc` like '管理员充值卡充值%' AND (jbnum>0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}


//金币增加
$query="
        SELECT  
            '金币增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '其他' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                (`desc` like '订单删除%' 
                 OR `desc` like '操作错误金币撤消%' 
                 OR `desc` like '购买多件赠送%' 
                 OR `desc` like '删除提现明细%'
                 OR `desc` like '提现审核不通过恢复金币%'
                  )
                 AND (jbnum>0)
                 
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

//金币减少
$whereSQL_str_jbjs=str_replace("createtime","x_clientdata_jblog.createtime",$whereSQL);
$query="
        SELECT  
            '金币减少' AS r_name,
            FROM_UNIXTIME(x_clientdata_jblog.createtime,'%Y-%m-%d') AS nowday,
          '消费' AS paytype,
             ordertype,
             SUM(x_clientdata_jblog.jbnum) AS total,
            COUNT(x_clientdata_jblog.id) AS  bs
        FROM x_clientdata_jblog 
        LEFT JOIN x_order ON x_order.id=x_clientdata_jblog.orderid
        WHERE 
                x_clientdata_jblog.`desc` like '消费%' AND (x_clientdata_jblog.jbnum<0)
               $whereSQL_str_jbjs
        GROUP BY 
            FROM_UNIXTIME(x_clientdata_jblog.createtime,'%Y-%m-%d'),ordertype
        ORDER BY x_clientdata_jblog.createtime DESC
       		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype = $row1["ordertype"];
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

//金币减少
$query="
        SELECT  
            '金币减少' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '手工扣除' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                `desc` like '管理员手工充值%' AND (jbnum<0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}



//金币减少
$query="
        SELECT  
            '金币减少' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '其他' AS paytype,
            SUM(jbnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jblog 
        WHERE 
                (`desc` like '转为合伙人金币减少%' 
                 OR `desc` like '订单删除%' 
                 OR `desc` like '管理员手工提现%' 
                 OR `desc` like '会员提现申请%'
                 OR `desc` like '操作错误金币撤消%'
                  )
                 AND (jbnum<0)
                 
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}















//积分增加-二级
$whereSQL_str=str_replace("createtime","payment_time",$whereSQL);
$query="
SELECT
	'积分增加' AS r_name,
	FROM_UNIXTIME(createtime, '%Y-%m-%d') AS nowday,
	'二级积分' AS paytype,
	SUM(jfnum) AS total,
	COUNT(id) AS bs
FROM
	x_clientdata_jflog
WHERE
	`desc` LIKE '下级会员购买赠送%'
	$whereSQL
GROUP BY
	FROM_UNIXTIME(createtime, '%Y-%m-%d')
	
ORDER BY
	createtime DESC;

";
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

$query="
SELECT
	'积分增加' AS r_name,
	FROM_UNIXTIME(createtime, '%Y-%m-%d') AS nowday,
	'三级积分' AS paytype,
	SUM(jfnum) AS total,
	COUNT(id) AS bs
FROM
	x_clientdata_jflog
WHERE
	`desc` LIKE '下下级会员购买赠送%'
	$whereSQL
GROUP BY
	FROM_UNIXTIME(createtime, '%Y-%m-%d')
ORDER BY
	createtime DESC;

";
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}

//积分增加
$query="
        SELECT  
            '积分增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '手工添加' AS paytype,
            SUM(jfnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jflog 
        WHERE 
                `desc` like '管理员手工添加%' AND (jfnum>0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}


//积分增加
$query="
        SELECT  
            '积分增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '赠送' AS paytype,
            SUM(jfnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jflog 
        WHERE 
              (  `desc` like '金币充值赠送%' 
               OR `desc` like '购买赠送%' 
               )
                AND (jfnum>0)
                AND (isdel=0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}


//积分增加
$query="
        SELECT  
            '积分增加' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '其他' AS paytype,
            SUM(jfnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jflog 
        WHERE 
                (`desc` like '转为合伙人赠送%' 
                 OR `desc` like '订单删除%' 
                 OR `desc` like '操作错误积分撤消%' 
                 OR `desc` like '删除提现明细%'
                 OR `desc` like '提现审核不通过恢复金币%'
                  )
                 AND (jfnum>0)
                 
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}




//积分减少
$whereSQL_str_jfjs=str_replace("createtime","x_clientdata_jflog.createtime",$whereSQL);
$query="
        SELECT  
            '积分减少' AS r_name,
            FROM_UNIXTIME(x_clientdata_jflog.createtime,'%Y-%m-%d') AS nowday,
          '消费' AS paytype,
             ordertype,
             SUM(x_clientdata_jflog.jfnum) AS total,
            COUNT(x_clientdata_jflog.id) AS  bs
        FROM x_clientdata_jflog 
        LEFT JOIN x_order ON x_order.id=x_clientdata_jflog.orderid
        WHERE 
                x_clientdata_jflog.`desc` like '消费%' AND (x_clientdata_jflog.jfnum<0)
               $whereSQL_str_jfjs
        GROUP BY 
            FROM_UNIXTIME(x_clientdata_jflog.createtime,'%Y-%m-%d'),ordertype
        ORDER BY x_clientdata_jflog.createtime DESC
       		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype = $row1["ordertype"];
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}
//dump($report_array["金币减少"]);
//dump($report_array["积分减少"]);

//积分减少
$query="
        SELECT  
            '积分减少' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '手工扣除' AS paytype,
            SUM(jfnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jflog 
        WHERE 
                `desc` like '管理员手工添加%' AND (jfnum<0)
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}



//积分减少
$query="
        SELECT  
            '积分减少' AS r_name,
            FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,
          '其他' AS paytype,
            SUM(jfnum) AS total,
            COUNT(id) AS  bs
        FROM x_clientdata_jflog 
        WHERE 
                (
                 `desc` like '订单删除%' 
                 OR `desc` like '操作错误积分撤消%'
                  )
                 AND (jfnum<0)
                 
                $whereSQL
        GROUP BY 
            FROM_UNIXTIME(createtime,'%Y-%m-%d')
        ORDER BY createtime DESC		
";
$dsql->SetQuery($query);
//dump($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {
    $r_name = $row1["r_name"];//报表名称
    $nowday = $row1["nowday"];//日期
    $paytype = $row1["paytype"];//支付方式
    $ordertype ="";//订单类型-------其他
    $total = $row1["total"]/100;//金额
    $bs = $row1["bs"];//笔数
    $report_array[$r_name][$nowday][$paytype][$ordertype]["笔数"]= $bs ;
    $report_array[$r_name][$nowday][$paytype][$ordertype]["金额"]= $total ;
}





include DwtInclude('count/reportByDay.htm');

   // dump($report_array["收入"]);
/*
//模板
if (empty($s_tmplets)) $s_tmplets = 'jbCountTypeByDay.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);
dwt
//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;*/



