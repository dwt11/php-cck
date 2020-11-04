<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 商品小助手
 *
 * @version        $Id: goods.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


/**获取单个栏目名称170106
 *
 * @param $tid
 *
 * @return string
 */
if (!function_exists('GetGoodsTypeName')) {
    function GetGoodsTypeName($tid)
    {
        global $cfg_Cs;
        if (empty($tid)) return '';
        if (!is_array($cfg_Cs)) {
            GetGoodsCatalogs();
        }
        //dump($cfg_Cs);
        if (isset($cfg_Cs[$tid])) {
            return base64_decode($cfg_Cs[$tid][2]);
        }
        return '';
    }
}


/**获取所有直通车卡的类型
 *
 * @param $tid
 *
 * @return string
 */
if (!function_exists('GetGoodsZTCclientTYPE')) {
    function GetGoodsZTCclientTYPE()
    {
        global $dsql;
        $ZTCclientType_t = array();
        $dsql->SetQuery("SELECT  `clientRank`  FROM `x_goods`   
            LEFT JOIN x_goods_addon_ztc  on x_goods_addon_ztc.goodsid=x_goods.id 
           WHERE x_goods.`status`='0' AND x_goods.`typeid` =1
            ORDER BY    id ASC");
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            $ZTCclientType_t[] = $row->clientRank;
        }
        return $ZTCclientType_t;
    }
}


/**
 *获取所有分类的数组170106
 */
if (!function_exists('GetGoodsCatalogs')) {
    function GetGoodsCatalogs()
    {
        global $cfg_Cs, $dsql;
        $dsql->SetQuery("SELECT id,reid,channeltype,typename FROM `#@__goods_type`");
        $dsql->Execute();
        $cfg_Cs = array();
        while ($row = $dsql->GetObject()) {
            // 将typename缓存起来
            $row->typename = base64_encode($row->typename);
            $cfg_Cs[$row->id] = array($row->reid, $row->channeltype, $row->typename);
        }
    }
}


/*订单成功支付后,根据这些内容来生成返还值
 * 通过优惠信息字符串,获取相关的优惠字段,
优惠信息保存在订单的benefitInfo字段中,格式为
                //规则时间:1484968790,
                //用户成长值等级:0,
                //用户类型:合伙人|直通车,
                //金币使用:0|0,
                //二级返还:0|0,
                //三级返还:30.00|30.00,
                //购买优惠:180
*/
if (!function_exists('GetBenefitInfoToField')) {

    /**
     * @param $benefitInfo
     * @param $fieldname
     *
     * @return string
     */
    function GetBenefitInfoToField($benefitInfo, $fieldname)
    {
        //规则时间:1484968790,
        //用户成长值等级:0,
        //用户类型:合伙人|直通车,
        //金币使用:0|0,
        //二级返还:0|0,
        //三级返还:30.00|30.00,
        //购买优惠:180
        $return_str = "";
        $benefitInfo_array = explode(",", $benefitInfo);
        if (is_array($benefitInfo_array) && count($benefitInfo_array) > 1) {

            $ejfhInfo = $benefitInfo_array[4];
            $ejfhInfo_array = explode(":", $ejfhInfo);
            $ejfhInfoNumb_array = explode("|", $ejfhInfo_array[1]);
            if ($fieldname == "jb2") $return_str = $ejfhInfoNumb_array[0];
            if ($fieldname == "jf2") $return_str = $ejfhInfoNumb_array[1];
            //dump($jb2);

            $sjfhInfo = $benefitInfo_array[5];
            $sjfhInfo_array = explode(":", $sjfhInfo);
            $sjfhInfoNumb_array = explode("|", $sjfhInfo_array[1]);
            if ($fieldname == "jb3") $return_str = $sjfhInfoNumb_array[0];
            if ($fieldname == "jf3") $return_str = $sjfhInfoNumb_array[1];
        }
        return $return_str;
    }
}


//解释订单中的优惠信息,生成可以直观查看 的HTML,
if (!function_exists('GetBenefitInfoToHTML')) {

    function GetBenefitInfoToHTML($benefitInfo)
    {
        //规则时间:1484968790,
        //用户成长值等级:0,
        //用户类型:合伙人|直通车,
        //金币使用:0|0,
        //二级返还:0|0,
        //三级返还:30.00|30.00,
        //购买优惠:180
        $return_str = "";
        $benefitInfo_array = explode(",", $benefitInfo);
        if (is_array($benefitInfo_array) && count($benefitInfo_array) > 1) {

            //dump($benefitInfo_array);

            $return_str .= $benefitInfo_array[2];   //用户类型
//            $return_str .= " " . $benefitInfo_array[6];   //单价

            $jbInfo = $benefitInfo_array[3];
            $jbInfo_array = explode(":", $jbInfo);
            $jbInfoNumb_array = explode("|", $jbInfo_array[1]);
            $syjb = $jbInfoNumb_array[0];
            $syjf = $jbInfoNumb_array[1];
            if ($syjb > 0 || $syjf > 0) $return_str .= " <b>使用</b> 金币:$syjb 积分:$syjf ";

            $ejfhInfo = $benefitInfo_array[4];
            $ejfhInfo_array = explode(":", $ejfhInfo);
            $ejfhInfoNumb_array = explode("|", $ejfhInfo_array[1]);
            $ejjb = $ejfhInfoNumb_array[0];
            $ejjf = $ejfhInfoNumb_array[1];
            if ($ejjb > 0 || $ejjf > 0) $return_str .= " <b>返还上级</b> 金币:$ejjb 积分:$ejjf ";

            $sjfhInfo = $benefitInfo_array[5];
            $sjfhInfo_array = explode(":", $sjfhInfo);
            $sjfhInfoNumb_array = explode("|", $sjfhInfo_array[1]);
            $sjjb = $sjfhInfoNumb_array[0];
            $sjjf = $sjfhInfoNumb_array[1];
            if ($sjjb > 0 || $sjjf > 0) $return_str .= " <b>返还上上级</b> 金币:$sjjb 积分:$sjjf ";


            /* $ejfhInfo=$benefitInfo_array[4];
             $ejfhInfo_array=explode(":",$ejfhInfo);
             $ejfhInfoNumb_array=explode("|",$ejfhInfo_array[1]);
             if($fieldname=="jb2")$return_str=$ejfhInfoNumb_array[0];
             if($fieldname=="jf2")$return_str=$ejfhInfoNumb_array[1];
             //dump($jb2);

             $sjfhInfo=$benefitInfo_array[5];
             $sjfhInfo_array=explode(":",$sjfhInfo);
             $sjfhInfoNumb_array=explode("|",$sjfhInfo_array[1]);
             if($fieldname=="jb3")$return_str=$sjfhInfoNumb_array[0];
             if($fieldname=="jf3")$return_str=$sjfhInfoNumb_array[1];*/
        }
        return $return_str;
    }
}


//获取商品的优惠价格
//默认只显示直通车的  金币使用价格
if (!function_exists('GetGoodBenefitInfoPrice')) {

    /**这个随后 要和 order.class.php下的  GetBenefitInfoHtmlToWeb整合一下,重复了
     *
     * @param        $goodsid         商品ID
     * @param int    $clientid        客户ID
     * @param string $clientTypeValue 优惠的类型 默认只取直通车的
     *
     * @param string $appttime        预约的时候 暂时只在旅游产品使用(170415)    获取 当前线路的优惠规则
     *
     * @return string
     */
    function GetGoodBenefitInfoPrice($goodsid, $clientid = 0, $clientTypeValue = "直通车", $appttime = "")
    {
        global $dsql;
        $return_str = "";
        $nowtime = time();
        if ($appttime != "") $nowtime = $appttime;

        $benefitInfo_array = array();
        $sql = "SELECT time_s,time_e,clientTypeValue,benefitType,jbnum,jfnum,createtime FROM `#@__goods_benefit` 
                WHERE goodsid='$goodsid'   AND isdel=0 
                AND (
                      (
                        FROM_UNIXTIME(time_s,'%Y-%m-%d')=FROM_UNIXTIME($nowtime,'%Y-%m-%d') AND FROM_UNIXTIME(time_e,'%Y-%m-%d')=FROM_UNIXTIME($nowtime,'%Y-%m-%d') 
                        )
                       OR
                      ( time_s=0 AND time_e=0)
                    ) 
                ORDER BY time_s DESC /*LIMIT 0,1*/
                ";
        //dump($sql);
        $dsql->SetQuery($sql);
        $dsql->Execute("170118");
        while ($row = $dsql->GetObject("170118")) {
            $time_s = $row->time_s;
            $time_e = $row->time_e;
            $jbnum100 = $row->jbnum;
            $jfnum100 = $row->jfnum;
            /*如果有时间范围,则判断{
         //              当前时间是否在范围内容{
         //                     如果在,则获取规则
          //             }else如果不在则跳过
         //  }如果没有时间范围,则全部获取
         */
            //  dump(GetDateMk($time_s)  );
            //   dump( GetDateMk($nowtime));
            //如果规则里有指定日期的 则 指定日期与当前选定的日期比较
            //是当前日期的 则获取 当前日期的优惠,否则 使用不限日期的
            $jbnum = $jbnum100 / 100;
            $jfnum = $jfnum100 / 100;
            $return_str = "";
            if ($time_s > 0 && GetDateMk($time_s) == GetDateMk($nowtime) && $row->clientTypeValue == $clientTypeValue) {
                //  $benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum100,$jfnum100);
                //dump($jfnum);
                //dump($jfnum);
                if ((int)$jbnum == 0 && (int)$jfnum == 0) {
                    $return_str = "免费";
                } else {
                    $return_str = "金币{$jbnum}  积分{$jfnum}";
                }
                return $return_str;
            } else if ($time_s == 0 && $row->clientTypeValue == $clientTypeValue) {
                if ((int)$jbnum == 0 && (int)$jfnum == 0) {
                    $return_str = "免费";
                } else {
                    $return_str = "金币{$jbnum}  积分{$jfnum}";
                }
                return $return_str;
            }
            //$benefitInfo_array[$row->benefitType] = array($row->jbnum, $row->jfnum);
        }


        return $return_str;
    }
}


/*获取指定商品,当前日期所有的优惠信息的会员类型名称*/
if (!function_exists('GetGoodBenefitInfo_clientTypeName_array')) {

    /**
     *
     * @param        $goodsid         商品ID
     * @param int    $clientid        客户ID
     * @param string $clientTypeValue 优惠的类型 默认只取直通车的
     *
     * @param string $appttime        预约的时候 暂时只在旅游产品使用(170415)    获取 当前线路的优惠规则
     *
     * @return string
     */
    function GetGoodBenefitInfo_clientTypeName_array($goodsid, $appttime = "")
    {
        global $dsql;
        $return_array = array();
        $nowtime = time();
        if ($appttime != "") $nowtime = $appttime;

        $benefitInfo_array = array();
        $sql = "SELECT time_s,time_e,clientTypeValue,benefitType,jbnum,jfnum,createtime FROM `#@__goods_benefit` 
                WHERE goodsid='$goodsid'   AND isdel=0 
                AND (
                      (time_s<$nowtime  AND time_e>$nowtime )
                       OR
                      ( time_s=0 AND time_e=0)
                    ) 
                ORDER BY time_s DESC 
                ";
        //dump($sql);
        $dsql->SetQuery($sql);
        $dsql->Execute("170118");
        $iiii = 0;
        while ($row = $dsql->GetObject("170118")) {
            $jbnum100 = $row->jbnum;
            $jfnum100 = $row->jfnum;
            $jbnum = $jbnum100 / 100;
            $jfnum = $jfnum100 / 100;
            $benefitInfo_array[$iiii]["clientTypeName"] = $row->clientTypeValue;
            $benefitInfo_array[$iiii]["jbnum"] = $jbnum;
            $benefitInfo_array[$iiii]["jfnum"] = $jfnum;
            $iiii++;
        }


        return $benefitInfo_array;
    }
}


/*生成优惠券*/
if (!function_exists('CreateCoupon')) {

    /**
     *
     * @param int $clientid 客户ID
     *
     * @return string
     */
    function CreateCoupon($clientid)
    {
        //算法,从数据库读取记录最近2000个记录中只能有2个平均数以上的,
        global $dsql;

        $query = "SELECT isuse FROM #@__goods_coupon  WHERE id=1 ";
        $row = $dsql->GetOne($query);
        if (isset($row["isuse"]) && $row["isuse"] ==1) {
            $jbnum_100_array = array();
            $questr = "SELECT jbnum FROM `#@__clientdata_coupon` ORDER BY id DESC limit 0,2000 ";//获取最新的2000条记录
            $dsql->SetQuery($questr);
            $dsql->Execute();
            while ($row = $dsql->GetObject()) {
                $jbnum_100_array[] = $row->jbnum;
            }


            $query = "SELECT * FROM `#@__goods_coupon`";
            //dump($query);
            $row = $dsql->GetOne($query);
            $jbnum_max_100 = $row["jbnum_max"];

            $jbnum_min_100 = $row["jbnum_min"];

            $jbnum_100 = CreateRandNum($jbnum_max_100, $jbnum_min_100, $jbnum_100_array);//原值不带分,乘以10,保存到数据库

            $nowtime = time();
            $sql = "INSERT INTO `x_clientdata_coupon` (`id`, `goodsid`, `time_s`, `time_e`, `buynumb`, `clientType`, `clientTypeValue`, `benefitType`, `jbnum`, `jfnum`, `createtime`, `isuse`, `usetime`,`useOrderId`, `operatorid`, `clientid`) 
                                            VALUES ('', '0', '0', '0', '1', '', '', '',  '$jbnum_100','0', '$nowtime', '0', '','','', '$clientid');";
            $dsql->ExecuteNoneQuery($sql);

            return $jbnum_100/100;
        }else{
            return "";
        }
        //return $benefitInfo_array;
    }
}
/*生成随机数*/
if (!function_exists('CreateRandNum')) {

    /**
     *
     * @param int $jbnum_max_100 最大
     * @param int $jbnum_min_100 最小
     * @param     $jbnum_100_indata_array 已经存在的值
     *
     * @return string
     *
     */
    function CreateRandNum($jbnum_max_100,$jbnum_min_100,$jbnum_100_indata_array)
    {
        //算法,从数据库读取记录最近2000个记录中只能有2个中间数以上的,
        global $dsql;


        $jbnum_mid_100 = ($jbnum_max_100 - $jbnum_min_100) / 1 + $jbnum_min_100;//中间数  数字代表如果超过人数后人金额上限

        $jbnum_max_100_temp = $jbnum_max_100;


        $mid_100_max_Arr = array();//比中间值大的数组

        foreach ($jbnum_100_indata_array as $vo) {
            if ($vo > $jbnum_mid_100) $mid_100_max_Arr[] = $vo;
        }


        //数字代表  送过来的数据中,超过限制值人中奖个数
        if (count($mid_100_max_Arr) > 2) {
            //如果最近2000个记录中的红包金额大于中间值的超过两个,则生成红包的最大值,只能是中间数
            $jbnum_max_100_temp = $jbnum_mid_100;
        }

        $jbnum_100 = mt_rand($jbnum_min_100, $jbnum_max_100_temp);
       // dump($jbnum_min_100." -".$jbnum_max_100_temp." -".$jbnum_100);

        return $jbnum_100;


        //return $benefitInfo_array;
    }
}




