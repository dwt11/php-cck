<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 订单小助手
 *
 * @version        $Id: archive.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


////与当前身份证，当前日期不在任何订单的一年范围内
if (!function_exists('Get_ztc_list_idcard_search')) {
    /**
     * @param     $idcard       身份证号
     *
     * @return string
     */

    function Get_ztc_list_idcard_search($idcard)
    {
        global $dsql;


        $query = "SELECT x_order_addon_ztc.idcard
             FROM x_order_addon_ztc 
            LEFT JOIN x_goods_addon_ztc  ON x_order_addon_ztc.goodsid=x_goods_addon_ztc.goodsid
            LEFT JOIN x_order  ON x_order.id=x_order_addon_ztc.orderid
            LEFT JOIN x_client_depinfos  ON x_client_depinfos.clientid=x_order.clientid
              WHERE 1=1 AND x_order.sta=1 AND x_order.isdel=0 
                AND x_client_depinfos.isdel=0 
                AND idcard='$idcard' 
            AND (
                    unix_timestamp(
                        DATE_ADD(from_unixtime(
                                                CASE  WHEN createtime<1483199999 THEN 1483199999 ELSE createtime END  /*1483199999-2016-12-31如果此日期前的订单则修改为此日期才过期*/
                                            ),INTERVAL rankLenth MONTH )/*到期日期*/
                    )
                )
                >UNIX_TIMESTAMP() /*不过期的卡*/";

        //dump($query);
        $rowarc = $dsql->GetOne($query);
        //dump($rowarc['idcard'] != "");
        //dump(isset($rowarc['idcard']));
        if (isset($rowarc['idcard']) && $rowarc['idcard'] != "") {
            return "0";///已经购买过
        } else {
            return "1";//未买过
        }


    }
}

////判断直通车订单中的身份证号 过期后的同一个周期内是否续费
if (!function_exists('Get_ztc_IDCard_IsXufei')) {
    /**
     * @param $idcard            身份证号
     * @param $orderCreateTime_o 旧订单的开始时间
     *
     * @return string
     */
    function Get_ztc_IDCard_IsXufei($idcard, $orderCreateTime_o)
    {


        //判断  订单创建日期 是否在旧的订单到期日和下一个周期内(有的话,代表续费了)
        $whereSql = "
                    AND 
                        createtime 
                        BETWEEN
                            (
                                unix_timestamp(
                                    DATE_ADD(from_unixtime(
                                                            CASE  WHEN {$orderCreateTime_o}<1483199999 THEN 1483199999 ELSE $orderCreateTime_o END  /*1483199999-2016-12-31如果此日期前的订单则修改为此日期才过期/*/
                                                        ),INTERVAL rankLenth month)/*到期日期*/
                                )
                            )
                            AND  
                            (
                                unix_timestamp(
                                    DATE_ADD(from_unixtime(
                                                            CASE  WHEN {$orderCreateTime_o}<1483199999 THEN 1483199999 ELSE $orderCreateTime_o END  /*1483199999-2016-12-31如果此日期前的订单则修改为此日期才过期/*/
                                                        ),INTERVAL rankLenth*2 month)/*到期日期*/
                                )
                            )
                            
                    ";

        $query = "
             SELECT 
             #@__order.ordernum,#@__order.paytime ,#@__order.createtime   
             FROM #@__order_addon_ztc 
              LEFT JOIN #@__goods_addon_ztc  ON #@__order_addon_ztc.goodsid=#@__goods_addon_ztc.goodsid
            LEFT JOIN #@__order  ON #@__order.id=#@__order_addon_ztc.orderid
            WHERE    #@__order.sta=1 AND #@__order.isdel=0 AND #@__order_addon_ztc.idcard='$idcard' $whereSql
            ";
        //dump($query);
        global $dsql;

        $row = $dsql->GetOne($query);
        if (isset($row["ordernum"]) && $row["ordernum"] != "") {
            return "续费订单号:" . $row["ordernum"] . "<br>支付时间:" . GetDateTimeMk($row["paytime"]) . "<br>新卡起始时间:" . GetDateTimeMk($row["createtime"]);
        }
        return "未续费";
    }
}


//如果重复就取最大的订单号+1
function logic_getMAXORDERcode($ordercode, $stepnumb = 0)
{
    global $dsql;
    //$stepnumb++;//用来记录递归 的次数,如果超过10次,则直接订单号加位
    //noroleordernumb  这个是子部门添加订单时不检查权限
    $questr1 = "SELECT   ordernum,'1' as noroleordernumb  FROM `#@__order`  where  ordernum='$ordercode' ";
    //dump($questr1);
    $rowarc1 = $dsql->GetOne($questr1);
    if (isset($rowarc1['ordernum']) && $rowarc1['ordernum'] != "") {
        //如果查询到此订单号,则+1
        $questr1 = "SELECT  MAX(CONVERT(ordernum,SIGNED)) as ordernum ,'1' as noroleordernumb FROM `#@__order`    ";
        $rowarc1 = $dsql->GetOne($questr1);
        $ordercode = $rowarc1['ordernum'];
        $ordercode++;
        return $ordercode;//用来记录递归 的次数,如果超过100次,则直接订单号加位
    } else {
        return $ordercode;
    }

}

////-------------------------------------1创建订单
if (!function_exists('CreateOrder')) {
    /**
     *
     * //这里不做用户输入的 金额校验了,把优惠信息保存到订单中,在后台订单列表中,提示判断 是否正确
     *
     * @param                   $clientid                 客户ID
     * @param                   $ordertype                订单类型
     * @param                   $desc                     订单描述备注
     * @param                   $jfnum100                 用户使用的抵扣金币数量
     * @param                   $jbnum100                 用户使用的抵扣积分数量
     * @param                   $operatorid               操作员ID
     * @param                   $total100                 订单总价(如果有优惠,则就是优惠后的)
     * @param                   $paynum100                用户需要实际支付的金额
     * @param int|              $benefitCreatetime        用户选择使用的优惠规则时间   (默认或没有优惠的话为0)
     * @param                   $fh_ejjb100               用户选择使用的 二级返还金币数量   (默认或没有优惠的话为0)
     * @param                   $fh_ejjf100               用户选择使用的 二级返还积分数量   (默认或没有优惠的话为0)
     * @param                   $fh_sjjb100               用户选择使用的 三级返还金币数量   (默认或没有优惠的话为0)
     * @param                   $fh_sjjf100               用户选择使用的 三级返还积分数量   (默认或没有优惠的话为0)
     * @param                   $buynumb                  购买数量
     * @param                   $couponid                 使用的优惠券ID
     *
     * @return int|string 返回:操作信息,订单号,订单ID
     */
    function CreateOrder(
        $clientid,
        $ordertype,
        $desc,
        $jfnum100,
        $jbnum100,
        $operatorid,
        $total100,
        $paynum100,
        $benefitCreatetime = 0,
        $fh_ejjb100,
        $fh_ejjf100,
        $fh_sjjb100,
        $fh_sjjf100,
        $buynumb,
        $couponid = 0
    )
    {
        //dump($jbnum100);
        global $dsql;
        $return_str = "";


        //---------------------获取订单编号
        $goodsOrderCode = date("Ym") . "0001";
        //noroleordernumb  这个是子部门添加订单时不检查权限
        $questr = "SELECT MAX(CONVERT(ordernum,SIGNED))  as ordernum ,'1' as noroleordernumb FROM `#@__order`   where  FROM_UNIXTIME(createtime,'%Y-%m') ='" . date("Y-m") . "' ";
        //dump($questr);
        $rowarc = $dsql->GetOne($questr);
        if ($rowarc['ordernum'] != "") {
            $goodsOrderCode = $rowarc['ordernum'];
            //不用下面的补位数了,直接添加
            /* if (strlen($goodsOrderCode) == 8) {
                 //原订单位数是年两位+月两位+编号 三位,这里要处理一下
                 //161107增加 如果订单数超过999则改为1000
                 //这里随后要删除了，从四月份开始做4位数的
                 $code_numb = substr($goodsOrderCode, 6, 3);
                 if ($code_numb == 999) {
                     $date_numb = substr($goodsOrderCode, 0, 6);
                     $goodsOrderCode = $date_numb . "0999";
                 }
             }*/
            $goodsOrderCode++;
        }


        /*上面的订单号,是根据当前日期来判断 的
        170406发现,如果直通车补办的卡  补办时订单号是根据当前日期生成的,但补卡后,会把创建日期改为补办的日期,
        所以造成订单号,可能是17年4月的,但创建日期是以前的日期,所以下次再生成时  订单号就会重复
        */
        //判断 订单号在所有的订单记录中是否重复,如果重复,则生成新订单号

        $goodsOrderCode = logic_getMAXORDERcode($goodsOrderCode);
        //---------------------获取订单编号

        $benefitInfo = "";
        //生成使用的优惠信息
        //除旅游线路外用的是时间 ，旅游线路用的是实际 单价
        if ($benefitCreatetime > 0) {
            $scroeInfo = GetClientType("score", $clientid);
            $scroeInfo_array = explode(",", $scroeInfo);
            $scoreNumb_biaozun = $scroeInfo_array[2];//所在等级的标准值
            $rankInfo = GetClientType("rank", $clientid);
            $rankInfo = str_replace(",", "|", $rankInfo);
            // $yhh_price = $total / $buynumb;
            $jbnum = $jbnum100 / 100;
            $jfnum = $jfnum100 / 100;
            $fh_ejjb = $fh_ejjb100 / 100;
            $fh_ejjf = $fh_ejjf100 / 100;
            $fh_sjjb = $fh_sjjb100 / 100;
            $fh_sjjf = $fh_sjjf100 / 100;
            $benefitInfo = "规则时间:$benefitCreatetime,用户成长值等级:$scoreNumb_biaozun,用户类型:$rankInfo,金币使用:$jbnum|$jfnum,二级返还:$fh_ejjb|$fh_ejjf,三级返还:$fh_sjjb|$fh_sjjf";
        } else {
            $benefitInfo = urldecode($benefitCreatetime);
        }
        $createtime = time();
        $desc = urldecode($desc);
        //这里不添加使用的积分和金币,创建订单成功后才更新
        $sqladdorder = "
            INSERT INTO  `#@__order` ( `clientid`, `ordertype`, `ordernum`, `desc`, `jfnum`, `jbnum`, `operatorid`, `createtime`, `total`, `paynum`, `paytype`, `paytime`, `pay_transaction_id`, `sta`, `isdel`, `benefitInfo`) 
                                  VALUES ( '$clientid', '$ordertype', '$goodsOrderCode', '$desc', '0', '0', '$operatorid', '$createtime', '$total100', '$paynum100', '', '', '', '0', '0', '$benefitInfo');    ";
        //dump($sqladdorder);
        $dsql->ExecuteNoneQuery($sqladdorder);
        $orderid = $dsql->GetLastID();


        if ($orderid > 0) {
            //if ($benefitCreatetime > 0) {
            //如果订单创建成功 客户减去相应的积分
            $isJBbool = true;
            $isJFbool = true;
            if ($jfnum100 > 0) {
                $isJFbool = Update_jf($clientid, "-$jfnum100", "消费 订单号:$goodsOrderCode", $orderid);
                if ($isJFbool) $dsql->ExecuteNoneQuery("UPDATE `#@__order`    SET jfnum='$jfnum100' WHERE id='$orderid' AND clientid='$clientid';    ");
            }
            //客户减去相应的金币
            if ($jbnum100 > 0) {
                $isJBbool = Update_jb($clientid, "-$jbnum100", "消费 订单号:$goodsOrderCode", $orderid);
                if ($isJBbool) $dsql->ExecuteNoneQuery("UPDATE `#@__order`    SET jbnum='$jbnum100' WHERE id='$orderid' AND clientid='$clientid';    ");
            }
            if ($isJFbool && $isJBbool) {
                $return_str = "订单创建成功,$goodsOrderCode,$orderid";
            } else {
                $return_str = "订单已创建 但操作(积分或金币失误) 请在订单管理中核对,$goodsOrderCode,$orderid";
                $dsql->ExecuteNoneQuery("UPDATE `#@__order`    SET `desc`='积分、金币操作时有误，请检查',sta='10' WHERE id='$orderid';    ");
            }


            if ($couponid > 0) {
                //生成订单的优惠券使用记录
                $query = "SELECT jbnum FROM #@__clientdata_coupon  WHERE id='$couponid'   AND (#@__clientdata_coupon.isuse='0')   ";
                $row = $dsql->GetOne($query);
                if (isset($row["jbnum"]) && $row["jbnum"] > 0) {
                    $coupon_jbnum_100 = $row["jbnum"];
                    // dump($paynum100);
                    // dump($coupon_jbnum_100);
                    // dump($total100);
                    if (($paynum100 + $coupon_jbnum_100) == $total100) {
                        //如果支付金额 +优惠券金额 ==  总价 才生成 优惠券使用记录
                        $sqladdorder = "INSERT INTO `x_order_benefit` (`id`, `jbnum`, `jfnum`, `createtime`, `info`, `orderid`,`clientdata_couponid`) VALUES ('', '{$coupon_jbnum_100}', '0', '{$createtime}', '优惠券-直通车购卡', '{$orderid}','$couponid'); ";
                        //dump($sqladdorder);
                        $dsql->ExecuteNoneQuery($sqladdorder);
                    } else {
                        $return_str = "订单创建失败 请在订单管理中核对(优惠券金额不正确),,0";
                    }
                } else {
                    $return_str = "订单创建失败 请在订单管理中核对(优惠券不正确),,0";
                }
            }
            //}
        } else {
            $return_str = "订单创建失败 请在订单管理中核对,,0";
        }
        //dump("----------" . $orderid . "---" . $return_str);
        return $return_str;
    }
}


//未支付的订单 取消过程,前后台都使用
if (!function_exists('CancelOrderNoPay')) {
    /**
     * @param     $orderid    订单ID
     * @param int $clientid   会员ID 后台不传此参数,前台使用此参数(用于验证是否是用户自己的订单)
     * @param int $orerid     操作员ID,前台不传此参数
     *
     * @return string
     */
    function CancelOrderNoPay($orderid, $clientid = 0, $orerid = 0)
    {

        global $dsql;
        $return_str = $wheresql = "";
        if ($clientid > 0) $wheresql = " AND clientid='$clientid' ";//前台使用此参数(用于验证是否是用户自己的订单)
        $arcQuery = "SELECT sta,jfnum,jbnum,clientid,ordernum,paynum FROM #@__order  WHERE isdel=0 AND id='$orderid' $wheresql";
        //dump($arcQuery);
        $arcRow = $dsql->GetOne($arcQuery);
        if (!is_array($arcRow)) {
            $return_str = "读取信息出错!";
            return $return_str;
        }


        $orderSta = $arcRow["sta"];
        $jfnum100 = $arcRow["jfnum"];
        $jbnum100 = $arcRow["jbnum"];
        $clientid_data = $arcRow["clientid"];
        $ordernum = $arcRow["ordernum"];

        // dump($orderSta);
        if ($orderSta != "1") {
            $createtime = time();
            $query = "UPDATE `#@__order` SET      returnOperatorid='$orerid',returntime='$createtime',            `isdel`='1'                WHERE  id='$orderid'              ";
            //  dump($query);
            if ($dsql->ExecuteNoneQuery($query)) {
                ///----------------------订单本身用户使用了的金币和积分-------------------，
                if ($jbnum100 > 0) Update_jb($clientid_data, $jbnum100, "订单删除恢复金币 订单号:$ordernum", $orderid, $orerid);
                if ($jfnum100 > 0) Update_jf($clientid_data, $jfnum100, "订单删除恢复积分 订单号:$ordernum", $orderid, $orerid);
            } else {
                $return_str = "操作失败!";
                return $return_str;
            }
        }

        $return_str = "操作成功";
        return $return_str;

    }
}


//支付的订单 退款过程,前台旅游预约和后台所有订单使用
if (!function_exists('ReturnOrder')) {
    /**
     * @param     $orderid    订单ID
     * @param int $clientid   会员ID 后台不传此参数,前台使用此参数(用于验证是否是用户自己的订单)
     * @param int $orerid     操作员ID,前台不传此参数
     *
     * @return string
     */
    function ReturnOrder($orderid, $clientid = 0, $orerid = 0)
    {

        global $dsql;
        $return_str = $wheresql = "";


        if ($clientid > 0) $wheresql = " AND clientid='$clientid' AND (ordertype='orderLycp' || ordertype='orderCar')";//前台使用此参数(用于验证是否是用户自己的订单)  并且只可以退款(旅游线路和租车)的
        $arcQuery = "SELECT sta,jfnum,jbnum,clientid,ordernum,paynum,ordertype FROM #@__order  WHERE isdel=0 AND id='$orderid' $wheresql";
        $arcRow = $dsql->GetOne($arcQuery);
        if (!is_array($arcRow)) {
            $return_str = "读取信息出错!";
            return $return_str;
        }


        $orderSta = $arcRow["sta"];
        $ordertype = $arcRow["ordertype"];


        if ($orderSta == "1") {
            //安全--------------------------------------------------验证
            if ($ordertype == "orderLycp" && $clientid == 0 && $orerid > 0) {
                $query = "SELECT gotime,beforHours FROM #@__order_addon_lycp
                            LEFT JOIN #@__line  ON #@__line.id=#@__order_addon_lycp.lineid
                             WHERE orderid={$orderid}";
                $goodRow = $dsql->GetOne($query);
                //如果过期的线路 不可以取消
                if (time() > $goodRow["gotime"] - $goodRow["beforHours"] * 3600) {
                    return "不可取消";
                }
            }
            if ($ordertype == "orderCar" && $clientid == 0 && $orerid > 0) {
                $query = "SELECT start_date FROM #@__order_addon_car                     WHERE orderid={$orderid}";
                $goodRow = $dsql->GetOne($query);
                //如果过期的车辆租赁 不可以取消
                if (time() > $goodRow["start_date"]) {
                    return "不可取消";
                }
            }
            //安全--------------------------------------------------验证

            $createtime = time();
            //$orerid = $CUSERLOGIN->userID;
            $query = "UPDATE `#@__order` SET        returnOperatorid='$orerid',returntime='$createtime',         `isdel`='1'                WHERE  id='$orderid'              ";
            if ($dsql->ExecuteNoneQuery($query)) {
                //支付成功的订单和更新为isdel 1的才恢复金币


                //==恢复订单里的积分 金币
                $arcQuery = "SELECT jfnum,jbnum,clientid,ordernum,paynum FROM #@__order  WHERE id='$orderid' ";
                $arcRow = $dsql->GetOne($arcQuery);
                if (!is_array($arcRow)) {
                    ShowMsg("读取信息出错!", "-1");
                    exit();
                }
                $ordernum = $arcRow['ordernum'];


                //-------------------------------先恢复送出的积分和金币
                //这里要写金币里有订单ID的，
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_jblog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                //dump($arcQuery1);
                if (is_array($arcRow1)) {
                    //恢复送出的金币
                    $query3 = "SELECT jbnum,clientid FROM #@__clientdata_jblog  WHERE orderid='$orderid' ";
                    $dsql->SetQuery($query3);
                    $dsql->Execute("9991114");
                    while ($row1 = $dsql->GetArray("9991114")) {
                        $jbnum100_sc = $row1['jbnum'];
                        if ($jbnum100_sc > 0) {
                            $jbnum100_sc = -$jbnum100_sc;
                            Update_jb($row1['clientid'], $jbnum100_sc, "订单删除同时删除赠送的金币 订单号:$ordernum", $orderid, $orerid);
                        }
                    }
                }

                //这里要写积分有订单ID的，恢复过程
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_jflog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                //dump($arcQuery1);
                if (is_array($arcRow1)) {
                    //再恢复积分
                    $query3 = "SELECT jfnum,clientid FROM #@__clientdata_jflog  WHERE orderid='$orderid' ";
                    $dsql->SetQuery($query3);
                    $dsql->Execute("9991114");
                    while ($row1 = $dsql->GetArray("9991114")) {
                        $jfnum100_sc = $row1['jfnum'];

                        if ($jfnum100_sc > 0) {
                            $jfnum100_sc = -$jfnum100_sc;
                            Update_jf($row1['clientid'], $jfnum100_sc, "订单删除同时删除赠送的积分 订单号:$ordernum", $orderid, $orerid);
                        }
                    }
                }


                //这里写删除送出的会员类型
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_ranklog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                if (is_array($arcRow1)) {
                    $query = "DELETE FROM `#@__clientdata_ranklog` WHERE   orderid='$orderid'              ";
                    $dsql->ExecuteNoneQuery($query);
                }


                ///----------------------再恢复订单本身用户使用了的金币和积分-------------------，

                $jbnum100_use = $arcRow['jbnum'];//订单本身使用了的金币
                $jfnum100_use = $arcRow['jfnum'];//订单本身使用了的积分
                $paynum100_use = $arcRow['paynum'];//订单本身充值的现金
                if ($jbnum100_use > 0) Update_jb($arcRow['clientid'], $jbnum100_use, "订单删除恢复金币 订单号:$ordernum", $orderid, $orerid);
                if ($jfnum100_use > 0) Update_jf($arcRow['clientid'], $jfnum100_use, "订单删除恢复积分 订单号:$ordernum", $orderid, $orerid);
                //用户支付的现金,会转为系统金币
                if ($paynum100_use > 0) Update_jb($arcRow['clientid'], $paynum100_use, "订单删除用户支付的现金转为金币 订单号:$ordernum", $orderid, $orerid);
            } else {
                $return_str = "操作失败";
            }
        }


        $return_str = "操作成功";
        return $return_str;


    }
}


/*已支付的订单 报废过程,后台所有订单使用
//更新订单状态为10
会将送给上级的金币和积分扣除;
<br>会将送给自己的金币和积分扣除;
<br>会将所获得的会员类型删除;
<br>用户支付的现金\金币\积分不会恢复
*/
if (!function_exists('BaofeiOrder')) {
    /**
     * @param     $orderid    订单ID
     * @param int $orerid     操作员ID,前台不传此参数
     *
     * @return string
     */
    function BaofeiOrder($orderid, $orerid)
    {

        global $dsql;
        $return_str = "";


        $arcQuery = "SELECT sta,jfnum,jbnum,clientid,ordernum,paynum,ordertype FROM #@__order  WHERE isdel=0 AND id='$orderid' ";
        $arcRow = $dsql->GetOne($arcQuery);
        if (!is_array($arcRow)) {
            $return_str = "读取信息出错!";
            return $return_str;
        }


        $orderSta = $arcRow["sta"];


        if ($orderSta == "1") {


            $createtime = time();
            //$orerid = $CUSERLOGIN->userID;
            $query = "UPDATE `#@__order` SET        returnOperatorid='$orerid',returntime='$createtime',         `isdel`='10'                WHERE  id='$orderid'              ";
            if ($dsql->ExecuteNoneQuery($query)) {
                //支付成功的订单和更新为isdel 1的才恢复金币


                //==恢复订单里的积分 金币
                $arcQuery = "SELECT jfnum,jbnum,clientid,ordernum,paynum FROM #@__order  WHERE id='$orderid' ";
                $arcRow = $dsql->GetOne($arcQuery);
                if (!is_array($arcRow)) {
                    ShowMsg("读取信息出错!", "-1");
                    exit();
                }
                $ordernum = $arcRow['ordernum'];


                //-------------------------------先恢复送出的积分和金币
                //这里要写金币里有订单ID的，
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_jblog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                //dump($arcQuery1);
                if (is_array($arcRow1)) {
                    //恢复送出的金币
                    $query3 = "SELECT jbnum,clientid FROM #@__clientdata_jblog  WHERE orderid='$orderid' ";
                    $dsql->SetQuery($query3);
                    $dsql->Execute("9991114");
                    while ($row1 = $dsql->GetArray("9991114")) {
                        $jbnum100_sc = $row1['jbnum'];
                        if ($jbnum100_sc > 0) {
                            $jbnum100_sc = -$jbnum100_sc;
                            Update_jb($row1['clientid'], $jbnum100_sc, "订单删除同时删除赠送的金币 订单号:$ordernum", $orderid, $orerid);
                        }
                    }
                }

                //这里要写积分有订单ID的，恢复过程
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_jflog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                //dump($arcQuery1);
                if (is_array($arcRow1)) {
                    //再恢复积分
                    $query3 = "SELECT jfnum,clientid FROM #@__clientdata_jflog  WHERE orderid='$orderid' ";
                    $dsql->SetQuery($query3);
                    $dsql->Execute("9991114");
                    while ($row1 = $dsql->GetArray("9991114")) {
                        $jfnum100_sc = $row1['jfnum'];

                        if ($jfnum100_sc > 0) {
                            $jfnum100_sc = -$jfnum100_sc;
                            Update_jf($row1['clientid'], $jfnum100_sc, "订单删除同时删除赠送的积分 订单号:$ordernum", $orderid, $orerid);
                        }
                    }
                }


                //这里写删除送出的会员类型
                $arcQuery1 = "SELECT clientid FROM #@__clientdata_ranklog  WHERE orderid='$orderid' ";
                $arcRow1 = $dsql->GetOne($arcQuery1);
                if (is_array($arcRow1)) {
                    $query = "DELETE FROM `#@__clientdata_ranklog` WHERE   orderid='$orderid'              ";
                    $dsql->ExecuteNoneQuery($query);
                }


                /* ///----------------------再恢复订单本身用户使用了的金币和积分-------------------，

                 $jbnum100_use = $arcRow['jbnum'];//订单本身使用了的金币
                 $jfnum100_use = $arcRow['jfnum'];//订单本身使用了的积分
                 $paynum100_use = $arcRow['paynum'];//订单本身充值的现金
                 if ($jbnum100_use > 0) Update_jb($arcRow['clientid'], $jbnum100_use, "订单删除恢复金币 订单号:$ordernum", $orderid, $orerid);
                 if ($jfnum100_use > 0) Update_jf($arcRow['clientid'], $jfnum100_use, "订单删除恢复积分 订单号:$ordernum", $orderid, $orerid);
                 //用户支付的现金,会转为系统金币
                 if ($paynum100_use > 0) Update_jb($arcRow['clientid'], $paynum100_use, "订单删除用户支付的现金转为金币 订单号:$ordernum", $orderid, $orerid);*/
            } else {
                $return_str = "操作失败";
            }
        }


        $return_str = "操作成功";
        return $return_str;


    }
}


//支付的订单 部分退款功能
if (!function_exists('ReturnOrderBF')) {
    /**
     * @param     $orderid    订单ID
     * @param     $jbnum
     * @param     $jfnum
     * @param     $info       备注
     * @param int $orerid     操作员ID,前台不传此参数
     *
     * @return string
     * @internal param int $clientid 会员ID 后台不传此参数,前台使用此参数(用于验证是否是用户自己的订单)
     */
    function ReturnOrderBF($orderid, $jbnum, $jfnum, $info, $orerid)
    {

        global $dsql;
        $return_str = "";


        $arcQuery = "SELECT sta,clientid,ordernum FROM #@__order  WHERE isdel=0 AND id='$orderid' ";
        $arcRow = $dsql->GetOne($arcQuery);
        if (!is_array($arcRow)) {
            $return_str = "读取信息出错!";
            return $return_str;
        }


        $orderSta = $arcRow["sta"];
        $clientid = $arcRow["clientid"];
        $ordernum = $arcRow["ordernum"];
        if ($orderSta == "1") {
            $createtime = time();
            $query = "UPDATE `#@__order` SET   `desc`=concat(`desc`,'<br>{$info}<br>[部分退款]:金币{$jbnum},积分:{$jfnum}'),    returnOperatorid='$orerid',returntime='$createtime',         `isdel`='4'                WHERE  id='$orderid'              ";
            //dump($query);
            if ($dsql->ExecuteNoneQuery($query)) {
                $jbnum100_use = $jbnum * 100;
                $jfnum100_use = $jfnum * 100;
                if ($jbnum100_use > 0) Update_jb($clientid, $jbnum100_use, "订单删除恢复金币[部分退款] 订单号:$ordernum", $orderid, $orerid);
                if ($jfnum100_use > 0) Update_jf($clientid, $jfnum100_use, "订单删除恢复积分[部分退款] 订单号:$ordernum", $orderid, $orerid);

            }
        }


        $return_str = "操作成功";
        return $return_str;


    }
}


////-------------------------------------1创建购物车
if (!function_exists('CreateOrderGWC')) {
    /**
     *
     *
     * @param $clientid             客户ID
     * @param $ordertype            订单类型
     * @param $desc                 订单描述备注
     *
     * @return int|string   返回:操作信息,订单号,订单ID
     */
    /* function CreateOrderGWC(
         $goodsid,
         $clientid,
         $ordertype,
         $desc
     )
     {
         global $dsql;
         $return_str = "";


         $createtime = time();


         $GWCid=0;
         //判断 购物车中是否有重复的商品
         $query="SELECT id FROM `#@__orderGWC` WHERE goodsid='$goodsid' AND clientid='$clientid'";
         $row = $dsql->GetOne($query);
         if(is_array($row)){
             $GWCid=$row["id"];
             //如果有此商品,则更新日期
             $sql = "UPDATE `#@__orderGWC` SET `createtime`='$createtime' WHERE id='{$GWCid}' ";
             if (!$dsql->ExecuteNoneQuery($sql)) {
                 //如果更新 出错误
             }

             //后期实体商品上来后,这里还要更新商品的数量 旅游线路不需要???????170216

         }else {
             $sqladdorder = "
             INSERT INTO  `#@__orderGWC` ( `goodsid`,`clientid`, `ordertype`, `desc`,  `createtime`)
                                   VALUES ( '$goodsid','$clientid', '$ordertype',  '$desc', '$createtime');    ";
             $dsql->ExecuteNoneQuery($sqladdorder);
             $GWCid = $dsql->GetLastID();

         }




         if ($GWCid > 0) {
             $return_str = "加入购物车成功,$GWCid";
         } else {
             $return_str = "加入购物车失败,0";
         }
         //dump("----------" . $orderid . "---" . $return_str);
         return $return_str;
     }*/
}


/** 支付成功后的回调过程
 *  更新支付状态,如果有返利 ,则返回
 *
 * @param $paytype    支付类型 微信
 * @param $result     返回字符串
 *
 * @return string
 */
if (!function_exists('saveTruePayOrder')) {
    function saveTruePayOrder($result, $paytype)
    {

        //这里支付成功，将信息写入数据库,要判断订单是否重复
        //$orderid_retsult = $result["out_trade_no"];//返回的商户订单，有时间码
        global $dsql;
        global $DEP_TOP_ID;
        // dump($result["out_trade_no"]);
        $ordercode_array = explode("-", $result["out_trade_no"]);//返回的商户订单，有时间码，要处理
        // dump($ordercode_array);

        //count($ordercode_array)>1这里验证这个  是为了防止别人勿传数据，所有的商户订单必须加-的时间尾辍
        if (is_array($ordercode_array) && count($ordercode_array) > 1) {
            $ordercode = $ordercode_array[0];
            $transaction_id = $result["transaction_id"];//返回的微信订单号

            $paytime = time();//记录日期


            //查询未支付的订单（刚创建的订单）sta=0  或  支付失败的订单 sta==12 sta==2
            $questr = "SELECT id,ordertype,paynum,clientid,benefitInfo,'1' as noroleordernumb  FROM `#@__order`  where  ordernum='$ordercode' and (sta=0 or sta=2 or sta=12) and isdel=0 ";
            $row = $dsql->GetOne($questr);
            if (isset($row["id"]) && $row["id"] != "") {

                $ordertype = $row["ordertype"];
                $orderid = $row["id"];
                $clientid = $row["clientid"];
                $orderpaynum100 = $row["paynum"];//订单的支付金额

                //更新订单的支付信息
                if ($orderpaynum100 == $result['total_fee']) {
                    $sql = "UPDATE `#@__order`    SET `paytype`='$paytype',sta=sta+1,paytime='$paytime',pay_transaction_id='$transaction_id' WHERE id='$orderid';    ";
                    $dsql->ExecuteNoneQuery($sql);
                }
                if ($orderpaynum100 != $result['total_fee']) {
                    $sql = "UPDATE `#@__order`    SET `paytype`='$paytype',sta=sta+3,paytime='$paytime',pay_transaction_id='$transaction_id' WHERE id='$orderid';    ";
                    $dsql->ExecuteNoneQuery($sql);
                }


                //如果商品附加 表中,有clientRank字段,则创建会员类型到rnaklog表
                createRankLog($orderid, $clientid);


                $goodsnum = GetOrderListNum($orderid);//商品数量
                $clientopenid = GetClientOpenID($clientid);//会员 的OPENid用于判断 是否发送微信通知


                //=======更新自己的积分
                //直通车会员 合伙人，并且不是充值卡订单，在交易后，送现金数额等值的积分


                $rankInfo = GetClientType("rank", $clientid);
                //dump(($rankInfo));
                $rankInfo_array = explode(",", $rankInfo);

                if (
                    /*(
                        in_array("直通车", $rankInfo_array) ||
                        in_array("合伙人", $rankInfo_array) ||
                        in_array("爱心卡", $rankInfo_array) ||
                        in_array("学生卡", $rankInfo_array)
                    ) */
                    $rankInfo != ""/*除了刚注册的会员,只要有会员身份的所有的会员卡都返一半的积分171103修改*/
                    &&
                    $ordertype != "orderCzk"
                ) {
                    //171031修改为购买后赠送现金一半的积分
                    $mynumjf100 = intval($orderpaynum100 / 2);//用户现金支付部分,增送积分.如果是使用金币支付的,在充值金币时已经送过积分了
                    Update_jf($clientid, $mynumjf100, "购买赠送 订单号:$ordercode", $orderid);
                    //===================================


                    if ($mynumjf100 > 0 && $clientopenid != "") {
                        $weixinMsgDataArray = array();
                        //微信发送----返积分成功通知
                        $weixinMsgDataArray["frist"] = "现金交易成功，赠送积分到账通知。";
                        $mynumjf_str = $mynumjf100 / 100;
                        $weixinMsgDataArray["keyword1"] = "{$mynumjf_str}积分";
                        $weixinMsgDataArray["keyword2"] = "点击详情查看";
                        $weixinMsgDataArray["remark"] = "订单号:" . $ordercode;
                        SendTemplateMessage("积分到帐提醒", $clientid, $DEP_TOP_ID, $weixinMsgDataArray);
                    }
                }


                //更新优惠券状态
                $query = "SELECT clientdata_couponid FROM #@__order_benefit  WHERE orderid='$orderid'     ";
                $row11 = $dsql->GetOne($query);
                if (isset($row11["clientdata_couponid"]) && $row11["clientdata_couponid"] > 0) {
                    $sql = "UPDATE `#@__clientdata_coupon`    SET `isuse`='1',usetime='$paytime',useOrderId='$orderid' WHERE id='{$row11["clientdata_couponid"]}';    ";
                    $dsql->ExecuteNoneQuery($sql);
                }


                //根据订单的优惠规则  获取返利的金币和积分
                //订单的优惠信息中保存的是实际的数量  未乘以100
                $jbnum100_2 = GetBenefitInfoToField($row["benefitInfo"], "jb2") * 100;
                $jfnum100_2 = GetBenefitInfoToField($row["benefitInfo"], "jf2") * 100;

                $jbnum100_3 = GetBenefitInfoToField($row["benefitInfo"], "jb3") * 100;
                $jfnum100_3 = GetBenefitInfoToField($row["benefitInfo"], "jf3") * 100;


                $realname = getOneCLientRealName($clientid);   //订单用户姓名


                //给上级返利
                $clientid_sjid = getOneClientSponsorid($clientid);
                //如果有上级介绍 人,并且返还金币或积分大于0,并且$orderpaynum100实付金额大于返还金币
                //才返还
                if (
                    $clientid_sjid > 0
                    && ($jbnum100_2 > 0 || $jfnum100_2 > 0)
                    && ($orderpaynum100 > $jbnum100_2)
                ) {
                    $fhjbnum100_2 = $jbnum100_2 * $goodsnum;
                    $fhjfnum100_2 = $jfnum100_2 * $goodsnum;
                    $text_str = "下级会员购买赠送 订单号:$ordercode  姓名:$realname";
                    Update_jf($clientid_sjid, $fhjfnum100_2, $text_str, $orderid);
                    Update_jb($clientid_sjid, $fhjbnum100_2, $text_str, $orderid);
                    //dump($fhjb2);
                    /* $mobilephone_sj = getOneClientMobilephone($clientid_sjid);//上级用户电话
                     if ($mobilephone_sj > 0) {
                         //if ($paytype != "模拟支付") {
			 //171111这里随后要优化,把$NAME都做成一样的,然后根据DEPDI去数据库里获取对应的参数
                         SendPhoneMSG($mobilephone_sj, $name = "朋友购买成功", $clientid_sjid, $depid = $DEP_TOP_ID, $data = array());
                         //}
                     }*/

                    //二级发送微信
                    $clientopenid_2 = GetClientOpenID($clientid_sjid);
                    if ($clientopenid_2 != "") {
                        //微信发送----返金币成功通知
                        $weixinMsgDataArray = array();
                        $weixinMsgDataArray["frist"] = "您的朋友 {$realname} 购买直通车会员卡成功,返还金币已经到账";
                        $weixinMsgDataArray["order"] = $ordercode;
                        $fhjbnum_2_str = $fhjbnum100_2 / 100;
                        $weixinMsgDataArray["money"] = "{$fhjbnum_2_str}金币";
                        SendTemplateMessage("返现到账通知", $clientid_sjid, $DEP_TOP_ID, $weixinMsgDataArray);


                        $weixinMsgDataArray = array();
                        //微信发送----返积分成功通知
                        $weixinMsgDataArray["frist"] = "您的朋友 {$realname} 购买直通车会员卡成功,返还积分已经到账";
                        $fhjfnum_2_str = $fhjfnum100_2 / 100;
                        $weixinMsgDataArray["keyword1"] = "{$fhjfnum_2_str}积分";
                        $weixinMsgDataArray["keyword2"] = "点击详情查看";
                        $weixinMsgDataArray["remark"] = "订单号:" . $ordercode;
                        SendTemplateMessage("积分到帐提醒", $clientid_sjid, $DEP_TOP_ID, $weixinMsgDataArray);
                    }


                    //给上上级返利
                    $clientid_ssssjid = getOneClientSponsorid($clientid_sjid);
                    if ($clientid_ssssjid > 0 && ($jbnum100_3 > 0 || $jfnum100_3 > 0)) {
                        $fhjbnum100_3 = $jbnum100_3 * $goodsnum;
                        $fhjfnum100_3 = $jfnum100_3 * $goodsnum;
                        $text_str_1 = "下下级会员购买赠送 订单号:$ordercode 姓名:$realname";
                        //$text_str_1 = "下下级会员购买赠送 订单号:$ordercode 姓名:$realname";

                        Update_jf($clientid_ssssjid, $fhjfnum100_3, $text_str_1, $orderid);
                        Update_jb($clientid_ssssjid, $fhjbnum100_3, $text_str_1, $orderid);
                        /*$mobilephone_ssssj = getOneClientMobilephone($clientid_ssssjid);//上上级用户电话

                        //发送 朋友购买成功
                        if ($mobilephone_ssssj > 0) {
                            //if ($paytype != "模拟支付")
                            SendPhoneMSG($mobilephone_ssssj, $name = "朋友购买成功", $clientid_ssssjid, $depid = $DEP_TOP_ID, $data = array());
                        }*/


                        //三级发送微信
                        $clientopenid_ssssjid = GetClientOpenID($clientid_ssssjid);
                        if ($clientopenid_ssssjid != "") {
                            //微信发送----返金币成功通知
                            $weixinMsgDataArray = array();
                            $weixinMsgDataArray["frist"] = "您的朋友 {$realname} 购买直通车会员卡成功,返还金币已经到账";
                            $weixinMsgDataArray["order"] = $ordercode;
                            $fhjbnum_3_str = $fhjbnum100_3 / 100;
                            $weixinMsgDataArray["money"] = "{$fhjbnum_3_str}金币";
                            SendTemplateMessage("返现到账通知", $clientid_ssssjid, $DEP_TOP_ID, $weixinMsgDataArray);


                            $weixinMsgDataArray = array();
                            //微信发送----返积分成功通知
                            $weixinMsgDataArray["frist"] = "您的朋友 {$realname} 购买直通车会员卡成功,返还积分已经到账";
                            $fhjfnum_3_str = $fhjfnum100_3 / 100;
                            $weixinMsgDataArray["keyword1"] = "{$fhjfnum_3_str}积分";
                            $weixinMsgDataArray["keyword2"] = "点击详情查看";
                            $weixinMsgDataArray["remark"] = "订单号:" . $ordercode;
                            SendTemplateMessage("积分到帐提醒", $clientid_ssssjid, $DEP_TOP_ID, $weixinMsgDataArray);
                        }
                    }
                }


                $mobilephone = getOneClientMobilephone($clientid);//订单用户电话
                //金额大于等于200才发送购买成功短信
                //170526不发送定购成功短信
                /*if ($mobilephone > 0&&$orderpaynum100>=20000) {
                    //发送成功订单信息
                    //这里的DEPID17随后订单做多公司时，再看怎么做，先用17
                    SendPhoneMSG($mobilephone, $name = "购买成功", $clientid, $depid = $DEP_TOP_ID, $data = array('body' => urlencode($ordercode)));
                }*/


                if ($clientopenid != "") {
                    //微信发送-----购买成功通知
                    $goodsname = "商品";
                    if ($ordertype == "orderZtc") $goodsname = "直通车会员卡";
                    if ($ordertype == "orderHyk") $goodsname = "合伙人会员卡";
                    if ($ordertype == "orderLycp") $goodsname = "旅游线路";
                    if ($ordertype == "orderCar") $goodsname = "车辆租赁";
                    if ($ordertype == "orderCzk") $goodsname = "充值卡";
                    $weixinMsgDataArray = array();
                    $weixinMsgDataArray["frist"] = "商品购买成功通知";
                    $weixinMsgDataArray["OrderId"] = $ordercode;
                    $weixinMsgDataArray["ProductId"] = str_replace("order", "", $ordertype);
                    $weixinMsgDataArray["ProductName"] = $goodsname;
                    SendTemplateMessage("新订单生成通知", $clientid, $DEP_TOP_ID, $weixinMsgDataArray);
                }


            }
        }


    }

}


/**
 *  根据订单创建用户类型和截止时间  只在这里使用
 *
 * @param     $orderID rank or score
 *
 * @return    array
 */
if (!function_exists('CreateRankLog')) {
    function createRankLog($orderID, $clientid)
    {
        global $dsql;
        if ($orderID > 0) {
            //根据模型 获取订单的附加 表

             $query = "SELECT addtable,'noroleordernumb' AS AA FROM
                      #@__order 
                      LEFT JOIN #@__sys_channeltype ON #@__sys_channeltype.nid=#@__order.ordertype
                      where #@__order.id='$orderID'";
            $rowOrder = $dsql->GetOne($query);
            //dump($rowOrder);
            $addTableName = "";
            if (isset($rowOrder["addtable"]) && $rowOrder["addtable"] != "") {
                $addTableName = $rowOrder["addtable"];
            }
            //dump($addTableName);
            if ($addTableName == "") return;

            //dump(1);
            //根据订单附加 表,获取商品ID,只获取一个商品ID
            $goodsid = "";
            $query3 = "SELECT goodsid  FROM $addTableName                WHERE orderid=$orderID                ";
            $row = $dsql->GetOne($query3);
            if (isset($row["goodsid"]) && $row["goodsid"] != "") {
                $goodsid = $row["goodsid"];
            }


            //根据商品ID,获取商品的模型ID
            if ($goodsid == "") return;
            $query = "SELECT gt.channeltype as channelid FROM `#@__goods` goods
                  LEFT JOIN `#@__goods_type` gt ON gt.id=goods.typeid
                  WHERE goods.id='$goodsid' ";
            $goodRow = $dsql->GetOne($query);
            if (!is_array($goodRow)) return;
            $channelid = $goodRow['channelid'];
            if (empty($channelid) || !$channelid > 0) return;

            //获取商品模型的附加表
            $sql = "SELECT addtable FROM `#@__sys_channeltype` WHERE id='$channelid'";
            $cts = $dsql->GetOne($sql);
            $addtable = trim($cts['addtable']);
            if (empty($addtable)) return;


            //获取附加 表的 会员有效期和名称
            $ranknamerow = $dsql->ExecuteNoneQuery2("describe `$addtable` clientRank");
            $ranklenthrow = $dsql->ExecuteNoneQuery2("describe `$addtable` rankLenth");
            //dump($ranknamerow);
            //dump($ranklenthrow);
            //如果存在这两个字段
            if ($ranknamerow && $ranklenthrow) {
                $addRowAddtable = $dsql->GetOne("SELECT clientRank,rankLenth FROM `$addtable` WHERE goodsid='$goodsid'");
                if (!is_array($addRowAddtable)) return;

                $clientRank = $addRowAddtable["clientRank"];
                $rankLenth = $addRowAddtable["rankLenth"];


                $ranktime = time();
                $rankcutofftime = strtotime("+{$rankLenth} month", $ranktime);
                $sql = "INSERT INTO `#@__clientdata_ranklog` ( `clientid`, `rank`, `ranktime`, `rankcutofftime`, `info`,orderid) 
                  VALUES ('$clientid', '$clientRank', '$ranktime', '$rankcutofftime', '','$orderID');";
                //dump($sql);
                $dsql->ExecuteNoneQuery($sql);
            }
        }


    }
}


//获取子订单的个数
if (!function_exists('GetOrderListNum')) {
    function GetOrderListNum($orderid)
    {
        global $dsql;
        //根据模型 获取订单的附加 表
        $query = "SELECT addtable FROM
          #@__order 
          LEFT JOIN #@__sys_channeltype ON #@__sys_channeltype.nid=#@__order.ordertype
          where #@__order.id='$orderid'";
        $rowOrder = $dsql->GetOne($query);
        $addTableName = "";
        if (isset($rowOrder["addtable"]) && $rowOrder["addtable"] != "") {
            $addTableName = $rowOrder["addtable"];
        }
        if ($addTableName == "") return 0;
        $str = 0;
        $query3 = "SELECT count(*) as dd  FROM $addTableName                WHERE orderid=$orderid                ";
        $row = $dsql->GetOne($query3);
        $str = $row["dd"];
        return $str;
    }
}
//获取订单的状态
if (!function_exists('GetOrderState')) {
    function GetOrderState($orderid)
    {
        //订单isdel的说明0正常未删除 1已删除 2已挂失 3已补卡170316？？？？这里随后 要完善
        global $dsql;
        $return = "异常";
        //根据模型 获取订单的附加 表
        $query = "SELECT addtable,ordertype FROM          #@__order          LEFT JOIN #@__sys_channeltype ON #@__sys_channeltype.nid=#@__order.ordertype          WHERE #@__order.id='$orderid'";
        $rowOrder = $dsql->GetOne($query);
        $addTableName = $ordertype = "";
        //dump($query);
        if (isset($rowOrder["addtable"]) && $rowOrder["addtable"] != "") {
            $addTableName = $rowOrder["addtable"];
            $ordertype = $rowOrder["ordertype"];
        }

        if ($ordertype == "orderCzk" && $addTableName != "") {
            $query3 = "SELECT usedate  FROM $addTableName                WHERE orderid=$orderid        AND usedate>0        ";
            $row = $dsql->GetOne($query3);
            if (isset($row["usedate"]) && $row["usedate"] > 0) {
                $return = "已使用";
            } else {
                $return = "正常";
            }
        } else {
            $return = "正常";
        }
        return $return;
    }
}


//获取子订单的商品图片
if (!function_exists('getOrderGoodsList')) {
    function getOrderGoodsList($orderid)
    {
        global $dsql;

        //根据模型 获取订单的附加 表
        $query = "SELECT addtable,ordertype,'1' as noroleordernumb  FROM
          #@__order 
          LEFT JOIN #@__sys_channeltype ON #@__sys_channeltype.nid=#@__order.ordertype
          where #@__order.id='$orderid'";
        $rowOrder = $dsql->GetOne($query);
        $addTableName = $ordertype = "";
        if (isset($rowOrder["addtable"]) && $rowOrder["addtable"] != "") {
            $addTableName = $rowOrder["addtable"];
            $ordertype = $rowOrder["ordertype"];
        }
        if ($addTableName == "") return "";


        $str = "";
        if ($ordertype == "orderCzk") {
            $photo = "/images/arcNoPic.jpg";
            $goodsname = "充值卡";
            $str = "<img src=\"$photo\" width=\"60\" height=\"60\" style='float:left; margin-right: 5px'/>

                $goodsname";

        } else {
            $query3 = "
                SELECT litpic,goodscode,goodsname,'1' as noroleordernumb   FROM $addTableName
                LEFT JOIN #@__goods ON $addTableName.goodsid=#@__goods.id
                WHERE orderid=$orderid
                ";

            //dump($query3);
            $dsql->SetQuery($query3);
            $dsql->Execute("999");
            while ($row1 = $dsql->GetArray("999")) {
                $photo = $row1["litpic"];
                if ($photo == "") $photo = "/images/arcNoPic.jpg";
                $goodscode = $row1["goodscode"];
                $goodsname = $row1["goodsname"];
                $str = "<img src=\"$photo\" width=\"60\" height=\"60\" style='float:left; margin-right: 5px'/>

                【{$goodscode}】 $goodsname";
            }
        }
        return $str;
    }
}


/**
 * @param $ordertime 判断直通车卡订单是否过期
 */
if (!function_exists('GetZtcCardTimeIsBool')) {

    function GetZtcCardTimeIsBool($ordertime, $goodsid = 0)
    {
        //dump($ordertime);
        $return_str = "到期";
        global $dsql;
        //获得有效期
        $rankLenth = 12;
        $query1111 = "SELECT rankLenth   FROM #@__goods_addon_ztc WHERE goodsid='{$goodsid}'";
        $rowOrder = $dsql->GetOne($query1111);
        if (isset($rowOrder["rankLenth"]) && $rowOrder["rankLenth"] > 0) $rankLenth = $rowOrder["rankLenth"];

        if (date('Y', $ordertime) == '2016' && time() < strtotime("2017-12-31 23:59:59")) {
            $return_str = '2017-12-31';
        } else {
            if ((time() < strtotime("+$rankLenth month", $ordertime))) {
                $return_str = GetDateMk(strtotime("+$rankLenth month", $ordertime));
            }
        }
        return $return_str;
    }
}

/**
 * //根据子订单ID，生成直通车的卡号
 *
 * @param $orderlist_id
 *
 * @return string
 */
if (!function_exists('GetZtcCardCode')) {

    function GetZtcCardCode($orderlist_id)
    {
        //echo ($orderid);
        //echo ($orderlist_id);
        if ($orderlist_id == "") return "无法获取";
        $return_str = "订单不成功(或订单被物理删除)，无法生成卡号";
        global $dsql;


        //根据模型 获取订单的附加 表
        $query1111 = "SELECT orderid
                                FROM #@__order_addon_ztc WHERE id='{$orderlist_id}'";
        $rowOrder = $dsql->GetOne($query1111);
        $orderid = $rowOrder["orderid"];


        $query = "SELECT o.id,order1.ordernum,o.cardcode,order1.createtime,order1.isdel,order1.sta,order1.clientid,o.goodsid
                                FROM #@__order_addon_ztc o
                                 LEFT JOIN #@__order   order1 on order1.id=o.orderid
                                WHERE o.orderid='{$orderid}' ";
        // dump($orderid."--".$orderlist_id);
        //dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute("nu8");
        $i = 0;
        while ($row1 = $dsql->GetArray("nu8")) {
            $i++;
            //dump($dsql->GetTotalRow("nu8")."---".$i."---".$orderlist_id ."---". $row1['id']);
            if ($dsql->GetTotalRow("nu8") > 1) {
                if ($orderlist_id == $row1['id']) {
                    $return_str = " 乘车卡号:" . $row1['ordernum'] . "-" . $i;
                    if ($row1['cardcode'] != "") $return_str .= " 实体卡号:" . $row1['cardcode'] . "-" . $i;
                }
            } else {
                $return_str = " 乘车卡号:" . $row1['ordernum'];
                if ($row1['cardcode'] != "") $return_str .= " 实体卡号:" . $row1['cardcode'];
            }


            if (GetZtcCardTimeIsBool($row1['createtime'], $row1['goodsid']) == '到期') $return_str .= "<br>此卡已经到期";
            //dump($row1['sta']);

            $clientstatus = GetClientStatus($row1["clientid"]);
            if ($clientstatus != "") $return_str .= $clientstatus;
            if ($row1['isdel'] == '1') $return_str .= " 此卡已经删除";
            if ($row1['sta'] != '1') $return_str .= " 此卡未支付";

        }
        return $return_str;
    }
}

/**根据直通车订单子ID获取直通车卡的类型
 *
 * @param $tid
 *
 * @return string
 */
if (!function_exists('GetZTCOrderGoodsTYPE')) {
    function GetZTCOrderGoodsTYPE($orderlistztcid)
    {
        global $dsql;
        $return_str = array();
        $dsql->SetQuery("SELECT  `clientRank`  FROM `x_order_addon_ztc`   
            LEFT JOIN x_goods_addon_ztc  on x_goods_addon_ztc.goodsid=x_order_addon_ztc.goodsid 
           WHERE id='$orderlistztcid'");
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            $return_str = $row->clientRank;
        }
        return $return_str;
    }
}


//获取商品的优惠价格

/**170823添加   因为爱心卡会员登录时,获取不到共享直通车卡的价格,导致多付款
 * 这个随后 要和 order.class.php下的  GetBenefitInfoHtmlToWeb整合一下,重复了
 *
 * @param        $goodsid         商品ID
 * @param string $clientTypeValue 优惠的类型 默认只取直通车的
 *
 * @param string $appttime        预约的时候 暂时只在旅游产品使用(170415)    获取 当前线路的优惠规则
 *
 * @return string
 */
function GetGoodBenefitInfoPrice_111111($goodsid, $clientTypeValue, $appttime)
{
    global $dsql;
    $enname_global = GetPinyin($clientTypeValue, $ishead = 1);//在HTML页面中的标识名称
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
        $return_str = "无";
        if ($time_s > 0 && GetDateMk($time_s) == GetDateMk($nowtime) && $row->clientTypeValue == $clientTypeValue) {
            //  $benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum100,$jfnum100);
            //dump($jfnum);
            //dump($jfnum);
            //ID<span id='benefitID_$benefit_numb_i'>$id</span>//做了，id但暂时先不用170316？？？？
            $tmpName = "每个卡可使用";
            $return_str = "
                                        [$tmpName]
                                        <span class=\"pull-right\"  >
                                            金币<span id='zdsyjb_$enname_global'>$jbnum</span>
                                            积分<span id='zdsyjf_$enname_global'>$jfnum</span>
                                        </span>
                                        <br>
                                        ";
            return $return_str;
        } else if ($time_s == 0 && $row->clientTypeValue == $clientTypeValue) {
            $tmpName = "每个卡可使用";
            $return_str = "
                                        [$tmpName]
                                        <span class=\"pull-right\"  >
                                            金币<span id='zdsyjb_$enname_global'>$jbnum</span>
                                            积分<span id='zdsyjf_$enname_global'>$jfnum</span>
                                        </span>
                                        <br>
                                        ";
            return $return_str;
        }
        //$benefitInfo_array[$row->benefitType] = array($row->jbnum, $row->jfnum);
    }


    return $return_str;
}


/**
 * 获取在有效期内的直通车卡
 * 不在这里判断 卡是否预约过当前线路,用户提交时判断
 *
 * @param $clientid
 * @param $appttime 用户选择的出行时间
 *
 * @return array
 *
 */
if (!function_exists('getZtcCard')) {
    /**
     * @param  $clientid
     * @param  $appttime                        用户选择的出行时间
     * @param  $only_client_type                卡对应的商品  生成的会员类型,只显示哪种会员类型
     *
     * @param  $QTorHT                          前台还是后台   显示的界面内容不一样  前台无照片,后台有照片
     * @param  $isshareCLIENTID                 为空则获取当前登录用户所有的卡,包含共享卡
     *                                          如果输入数字  则代表的是登录的当前用户的ID  不获取 已经共享给他的卡
     * @param  $goodsid                         170904增加 如果是0则不判断当前商品,当前客户购买次数
     *                                          如果大于0,则根据这个商品id判断当前商品可以购买的次数
     * @param  $isjihuo_not_panduan             如果为1则不进行乘车卡激活判断
     *
     * @return array
     */


    function getZtcCard($clientid, $appttime, $only_client_type, $QTorHT, $isshareCLIENTID = 0, $goodsid = 0, $isjihuo_not_panduan = 0)
    {
        $return = array();
        if (!$clientid > 0) return $return;//如果没有用户ID，则退出
        global $dsql;


        //170904获取当前商品可重复购买的次数
        $onlynumb = 0;
        if ($goodsid > 0) {
            //读取归档信息
            $arcQuery = "SELECT onlynumb  FROM #@__goods  WHERE id='$goodsid' ";
            $arcRow = $dsql->GetOne($arcQuery);
            if (isset($arcRow['onlynumb']) && $arcRow['onlynumb'] > 0) {
                $onlynumb = $arcRow['onlynumb'];
            }

        }


        //后台显示模板
        $HT_web_HTML_TEMP = "                <li class=\"list-group-item1  text-muted small\">
                                                    ztcCard_web_HTML_TEMP
                                                    <span class=\"pull-right  \">
                                                        checkbox_web_HTML_TEMP
                                                    </span>
                                                </li>
                                                <li class=\"list-group-item1 list-group-item-border   text-muted small\">
                                                    <div class='pull-left text-danger' style='max-width: 190px;'>photo_web_HTML_TEMP</div>
                                                    <div class=\"pull-right  \">
                                                        <div style=\"max-width: 250px\">
                                                            <div>
                                                                <div class=\"col-xs-5\">
                                                                    name_web_HTML_TEMP
                                                                </div>
                                                                <div class=\"col-xs-7\">
                                                                    tel_web_HTML_TEMP
                                                                </div>
                                                            </div>
                                                            <div class=\"clearfix\"></div>
                                                            <div style=\"margin-top: 5px\">
                                                                <div class=\"col-xs-12\">
                                                                    idcard_web_HTML_TEMP
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class=\"clearfix\"></div>
                                                </li>";
        //前台显示模板
        $QT_web_HTML_TEMP = "                <li class=\"list-group-item1  text-muted small\">
                                                    ztcCard_web_HTML_TEMP
                                                    <span class=\"pull-right  \">
                                                        checkbox_web_HTML_TEMP
                                                    </span>
                                                </li>
                                                <li class=\"list-group-item1 list-group-item-border   text-muted small\">
                                                    <div class='pull-left' >name_web_HTML_TEMP tel_web_HTML_TEMP</div>
                                                    <div class=\"pull-right\"> idcard_web_HTML_TEMP </div>
                                                    <div class=\"clearfix\"></div>
                                                    <div class='pull-left text-danger'>photo_web_HTML_TEMP</div>
                                                    <div class=\"pull-right  \"></div>
                                                    <div class=\"clearfix\"></div>
                                            </li>";

        $web_HTML_TEMP = "";
        if ($QTorHT == "HT") $web_HTML_TEMP = $HT_web_HTML_TEMP;
        if ($QTorHT == "QT") $web_HTML_TEMP = $QT_web_HTML_TEMP;


        //此查询语句在ZTCCARD.PHP中也使用,更新时一块更新
        //获取自己的
        $wheresql = "";
        $enname_global = GetPinyin($only_client_type, $ishead = 1);//在HTML页面中的标识名称
        if ($only_client_type != "") $wheresql = " AND #@__goods_addon_ztc.clientRank='$only_client_type'";

        //不获取已经加了的共享卡
        if ($isshareCLIENTID > 0) $wheresql = "               AND   #@__order_addon_ztc.id NOT IN (SELECT #@__ztc_share.orderListId FROM #@__ztc_share WHERE #@__ztc_share.clientid_n='$isshareCLIENTID' AND #@__ztc_share.isdel='0')";
        $query = "SELECT `#@__order`.id AS orderid,`#@__order`.operatorid,
          #@__order_addon_ztc.id AS orderListId,#@__order_addon_ztc.name,#@__order_addon_ztc.tel,#@__order_addon_ztc.idcard,#@__order_addon_ztc.idpic,
          #@__order_addon_ztc.idpic_desc ,#@__order_addon_ztc.goodsid ,#@__order.createtime 
          FROM  `#@__order`
          LEFT JOIN #@__order_addon_ztc  ON `#@__order`.id=#@__order_addon_ztc.orderid
          LEFT JOIN #@__goods_addon_ztc  ON #@__order_addon_ztc.goodsid=#@__goods_addon_ztc.goodsid
          WHERE 
                  (
                    `#@__order`.clientid='$clientid' AND `x_order`.ordertype='orderZtc'   AND `#@__order`.sta=1
                   )
              AND `#@__order`.isdel=0  
              $wheresql
          ORDER BY `#@__order`.createtime DESC ";
        //dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute("170124");
        $iiiicck = 0;
        while ($row = $dsql->GetArray("170124")) {

            $isdq = GetZtcCardTimeIsBool($row['createtime'], $row['goodsid']);

            $is_dq_fw = $row['createtime'] > time();//没有到卡的使用开始日期(提前续费的卡存在开始日期大于当前日期的情况 )171008
            if ($isdq == "到期" || $is_dq_fw) continue;//如果到期或没有到卡的使用开始日期(提前续费的卡存在开始日期大于当前日期的情况 )则跳过
            $iiiicck++;

            $orderid = $row['orderid'];
            $orderListId = $row['orderListId'];
            $ztcCard = GetZtcCardCode($orderListId);

            $photo_file = $row["idpic"];
            if ($photo_file == "") {
                if ($QTorHT == "QT") $photo = "请在[会员中心]-[直通车乘车卡]页面上传照片";
                if ($QTorHT == "HT" && !$isshareCLIENTID) $photo = "请在[会员卡订单管理]页面上传照片后,刷新此页面";
                //$photo = "请上传乘车卡照片<br><a href='/lyapp/service/ztcCard.php'>点此上传</a>";
                $isphoto = false;
            } else {
                if ($QTorHT == "QT") $photo = "";
                if ($QTorHT == "HT") $photo = "<img src=\"$photo_file\" width=\"60\" height=\"60\">";
                $isphoto = true;
            }


            //是否需要审核  随后要做成数据库里的
            //老人卡\学生卡需要审核,其他的卡不需要审核
            if ($row["goodsid"] == 142 || $row["goodsid"] == 143) {
                //除了商品1 直通车 其他的 都要需要审核才能使用
                $idpic_desc = $row["idpic_desc"];
                $issh = strpos($idpic_desc, "审核通过");//判断 是否包含审核通过字样
                //dump($isskd);
                if ($issh === false) {
                    //没有审核
                    if ($QTorHT == "QT" || $isshareCLIENTID > 0) {
                        if ($idpic_desc == "") $photo = "未审核,请联系工作人员审核";
                        if ($idpic_desc != "") $photo = "审核不通过原因($idpic_desc)";
                    }
                    if ($QTorHT == "HT" && !$isshareCLIENTID) $photo = "请在[会员卡订单管理]页面,进行审核";
                    $isphoto = false;
                } else {
                    $isphoto = true;
                }
            }

            //每个卡当天没有重复预约多条线路 这里要判断 是否已经预订过
            $idcard = $row["idcard"];
            $isappt = false;
            if ($appttime > 0) $isappt = GetIdcardIStrueAppt($idcard, $appttime);
            //如果没有照片 或卡当天预订过  乘车卡不可以选择
            $checkbox_disabled = $checkbox_disabled_str = "";
            if (!$isphoto) {
                $checkbox_disabled = "disabled";
                $checkbox_disabled_str = "照片不可用";
            }
            if ($isappt) {
                $checkbox_disabled = "disabled";
                $checkbox_disabled_str = "所选日期已经预约过";
            } else {

                 //如果有限制购买次数  则获取当前乘车卡购买的次数
                if ($onlynumb > 0) {
                    $buynumb = 0;//已经购买的次数
                    //搜索其他乘车人的身份证,是否是否预订过
                    $query35534545 = "SELECT COUNT(#@__order_addon_lycp.id) as dd FROM #@__order_addon_lycp
                      LEFT JOIN #@__order ON #@__order_addon_lycp.orderid= #@__order.id
                      WHERE
                        (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order_addon_lycp.isdel=0 AND #@__order.sta=1  AND
                        #@__order_addon_lycp.idcard='$idcard'
                             AND 
                         goodsid='$goodsid';
                         ";//每个卡当天没有重复预约多条线路
                    //dump($query35534545);
                    $rowarc355234234 = $dsql->GetOne($query35534545);
                    if (isset($rowarc355234234['dd']) && $rowarc355234234['dd'] > 0) {
                        $buynumb = $rowarc355234234['dd'];
                    }
                    if ($buynumb >= $onlynumb) {
                        $checkbox_disabled = "disabled";
                        $checkbox_disabled_str = "此商品只可预约{$onlynumb}次(已用{$buynumb}次)";
                    }
                }


                //如果前台预约则,并且卡不是手工添 加(微信添加)判断是否激活 /20171001以后的卡才判断是否激活
                // dump($isjihuo_not_panduan);
                //171102修改为不用激活判断
                /* if ($isjihuo_not_panduan == 0 && $row['operatorid'] == 0 && $row['createtime'] > 1506787200) {

                     //if ($isjihuo_not_panduan == 0  ) {
                     // dump($isjihuo_not_panduan);
                     $buynumb = 0;//已经购买的次数
                     //搜索乘车卡,预约过的次数
                     $query35534545 = "SELECT COUNT(#@__order_addon_lycp.id) as dd FROM #@__order_addon_lycp
                       LEFT JOIN #@__order ON #@__order_addon_lycp.orderid= #@__order.id
                       WHERE
                         (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order_addon_lycp.isdel=0 AND #@__order.sta=1  AND
                         #@__order_addon_lycp.orderlistztcid='{$row['orderListId']}'
                             ;
                          ";
                     //dump($query35534545);
                     $rowarc355234234 = $dsql->GetOne($query35534545);
                     if (isset($rowarc355234234['dd']) && $rowarc355234234['dd'] > 0) {
                         $buynumb = $rowarc355234234['dd'];
                     }


                     $isjihuo = 0;
                     $query = "SELECT orderListId,createtime FROM #@__ztc_jihuo   WHERE orderListId='{$row['orderListId']}' ";
                     $row234234 = $dsql->GetOne($query);
                     if (isset($row234234["orderListId"]) && $row234234["orderListId"] > 0) {
                         $isjihuo = 1;
                     }
                     //  dump($buynumb);
                     //卡未激活 并且预约次数大于0,则提示要激活
                     if ($isjihuo == 0 && $buynumb > 0) {
                         $checkbox_disabled = "disabled";
                         $checkbox_disabled_str = "请在就近代售点激活此卡后使用";
                         $photo = "<a onclick='msg_from_arc(\"9\",\"售卡点地址\")' href='#'>售卡点地址</a>";
                     }
                 }*/
            }
            $checkbox = "
                        <label class=\"i-checks text-muted\">
                            $checkbox_disabled_str <input type=\"checkbox\" value='$orderListId'  name=\"cck_$enname_global\" id=\"cck_$enname_global\" $checkbox_disabled/>
                        </label>
                        ";

            $name = $row["name"];
            $idcard = GetPhoneCode($row["idcard"]);
            $tel = GetPhoneCode($row["tel"]);


            $WEB_HTML = str_replace("ztcCard_web_HTML_TEMP", $ztcCard, $web_HTML_TEMP);
            $WEB_HTML = str_replace("checkbox_web_HTML_TEMP", $checkbox, $WEB_HTML);
            $WEB_HTML = str_replace("photo_web_HTML_TEMP", $photo, $WEB_HTML);
            $WEB_HTML = str_replace("name_web_HTML_TEMP", $name, $WEB_HTML);
            $WEB_HTML = str_replace("tel_web_HTML_TEMP", $tel, $WEB_HTML);
            $WEB_HTML = str_replace("idcard_web_HTML_TEMP", $idcard, $WEB_HTML);
            $return["ztcinfo"][] = $WEB_HTML;
        }


        //获取共享 卡
        if (!$isshareCLIENTID) {
            $query = "SELECT olist.orderid,olist.id as orderListId,olist.name,olist.tel,olist.idcard,olist.idpic,olist.goodsid,olist.idpic_desc, 
              o1.createtime,#@__goods_addon_ztc.goodsid,
              o1.operatorid
              
            FROM    #@__ztc_share  os
           LEFT JOIN #@__order_addon_ztc olist on olist.id=os.orderListId
            LEFT JOIN #@__goods_addon_ztc  ON  olist.goodsid=#@__goods_addon_ztc.goodsid
         LEFT JOIN #@__order o1 on o1.id=olist.orderid
          WHERE  os.isdel='0' and o1.isdel='0' and o1.sta=1 and os.clientid_n='$clientid'

           $wheresql
           ORDER BY o1.createtime DESC ";
            $dsql->SetQuery($query);
            $dsql->Execute("17012412");
            while ($row = $dsql->GetArray("17012412")) {
                $isdq = GetZtcCardTimeIsBool($row['createtime'], $row['goodsid']);

                if ($isdq == "到期") continue;//如果到期则跳过
                $iiiicck++;

                $orderid = $row['orderid'];
                $orderListId = $row['orderListId'];
                $ztcCard = GetZtcCardCode($orderListId);


                $photo_file = $row["idpic"];
                //dump($photo);
                if ($photo_file == "") {
                    //$photo = "请让好友上传照片";
                    if ($QTorHT == "QT") $photo = "请让好友上传照片";
                    if ($QTorHT == "HT") $photo = "请在[会员卡订单管理]页面上传照片后,刷新此页面";
                    $isphoto = false;
                } else {
                    if ($QTorHT == "QT") $photo = "";
                    if ($QTorHT == "HT") $photo = "<img src=\"$photo_file\" width=\"60\" height=\"60\">";
                    $isphoto = true;
                }

                //老人卡\学生卡需要审核,其他的卡不需要审核
                if ($row["goodsid"] == 142 || $row["goodsid"] == 143) {
                    //除了商品1 直通车 其他的 都要需要审核才能使用
                    $idpic_desc = $row["idpic_desc"];
                    $issh = strpos($idpic_desc, "审核通过");//判断 是否包含审核通过字样
                    //dump($isskd);
                    if ($issh === false) {
                        //没有审核
                        if ($QTorHT == "QT") {
                            if ($idpic_desc == "") $photo = "未审核,请联系工作人员审核";
                            if ($idpic_desc != "") $photo = "审核不通过原因($idpic_desc)";
                        }
                        if ($QTorHT == "HT") $photo = "请在[会员卡订单管理]页面,进行审核";
                        $isphoto = false;
                    } else {
                        if ($QTorHT == "QT") $photo = "";
                        if ($QTorHT == "HT") $photo = "<img src=\"$photo_file\" width=\"60\" height=\"60\">";
                        $isphoto = true;
                    }
                }


                $idcard = $row["idcard"];
                $isappt = false;
                if ($appttime > 0) $isappt = GetIdcardIStrueAppt($idcard, $appttime);

                //如果没有照片 或卡当天预订过  乘车卡不可以选择
                $checkbox_disabled = $checkbox_disabled_str = "";
                if (!$isphoto) {
                    $checkbox_disabled = "disabled";
                    $checkbox_disabled_str = "照片不可用";
                }
                if ($isappt) {
                    $checkbox_disabled = "disabled";
                    $checkbox_disabled_str = "所选日期已经预约过";
                } else {
                     if ($onlynumb > 0) {
                        //如果有限制购买次数  则获取当前乘车卡购买的次数
                        $buynumb = 0;//已经购买的次数
                        //搜索其他乘车人的身份证,所选的当日  是否是否预订过
                        $query35534545 = "SELECT COUNT(#@__order_addon_lycp.id) as dd FROM #@__order_addon_lycp
                      LEFT JOIN #@__order ON #@__order_addon_lycp.orderid= #@__order.id
                      WHERE
                        (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order_addon_lycp.isdel=0 AND #@__order.sta=1  AND
                        #@__order_addon_lycp.idcard='$idcard'
                             AND 
                         goodsid='$goodsid';
                         ";//每个卡当天没有重复预约多条线路
                        //dump($query35534545);
                        $rowarc355234234 = $dsql->GetOne($query35534545);
                        if (isset($rowarc355234234['dd']) && $rowarc355234234['dd'] > 0) {
                            $buynumb = $rowarc355234234['dd'];
                        }
                        if ($buynumb >= $onlynumb) {
                            $checkbox_disabled = "disabled";
                            $checkbox_disabled_str = "此商品只可预约{$onlynumb}次(已用{$buynumb}次)";
                        }
                    }

                    //如果前台预约则,并且卡不是手工添 加(微信添加)判断是否激活 /20171001以后的卡才判断是否激活
                    // dump($isjihuo_not_panduan);
                    //171102修改为不用激活判断
                    /*  if ($isjihuo_not_panduan == 0 && $row['operatorid'] == 0 && $row['createtime'] > 1506787200) {
                          $buynumb = 0;//已经购买的次数
                          //搜索乘车卡,预约过的次数
                          $query35534545 = "SELECT COUNT(#@__order_addon_lycp.id) as dd FROM #@__order_addon_lycp
                        LEFT JOIN #@__order ON #@__order_addon_lycp.orderid= #@__order.id
                        WHERE
                          (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order_addon_lycp.isdel=0 AND #@__order.sta=1  AND
                          #@__order_addon_lycp.orderlistztcid='{$row['orderListId']}'
                              ;
                           ";//每个卡当天没有重复预约多条线路
                          //dump($query35534545);
                          $rowarc355234234 = $dsql->GetOne($query35534545);
                          if (isset($rowarc355234234['dd']) && $rowarc355234234['dd'] > 0) {
                              $buynumb = $rowarc355234234['dd'];
                          }


                          $isjihuo = 0;
                          $query = "SELECT orderListId,createtime FROM #@__ztc_jihuo   WHERE orderListId='{$row['orderListId']}' ";
                          $row234234 = $dsql->GetOne($query);
                          if (isset($row234234["orderListId"]) && $row234234["orderListId"] > 0) {
                              $isjihuo = 1;
                          }
                          //卡未激活 并且预约次数大于0,则提示要激活
                          if ($isjihuo == 0 && $buynumb > 0) {
                              $checkbox_disabled = "disabled";
                              $checkbox_disabled_str = "请在就近代售点激活此卡后使用";
                              $photo = "<a onclick='msg_from_arc(\"9\",\"售卡点地址\")' href='#'>售卡点地址</a>";
                          }
                      }*/

                }
                //dump($isphoto);
                //dump($checkbox_disabled_str);
                $checkbox = "
                        <label class=\"i-checks text-muted\">
                        $checkbox_disabled_str <input type=\"checkbox\" value='$orderListId'  name=\"cck_$enname_global\" id=\"cck_$enname_global\"  $checkbox_disabled/>
                    </label>
                    ";


                //dump($checkbox);
                $name = $row["name"];
                $idcard = GetPhoneCode($row["idcard"]);
                $tel = GetPhoneCode($row["tel"]);
                $WEB_HTML = str_replace("ztcCard_web_HTML_TEMP", $ztcCard . "[共享卡]", $web_HTML_TEMP);
                $WEB_HTML = str_replace("checkbox_web_HTML_TEMP", $checkbox, $WEB_HTML);
                $WEB_HTML = str_replace("photo_web_HTML_TEMP", $photo, $WEB_HTML);
                $WEB_HTML = str_replace("name_web_HTML_TEMP", $name, $WEB_HTML);
                $WEB_HTML = str_replace("tel_web_HTML_TEMP", $tel, $WEB_HTML);
                $WEB_HTML = str_replace("idcard_web_HTML_TEMP", $idcard, $WEB_HTML);
                $return["ztcinfo"][] = $WEB_HTML;

            }

        }
        $return["number"] = $iiiicck;
        return $return;
    }
}


/**获取其他 乘车人表单
 *
 * @param $name
 * @param $tel
 * @param $idcard
 *
 * @return string
 * @internal param 乘车卡数量 $ztcCard_numb
 *
 */
if (!function_exists('getQtCCR')) {

    function getQtCCR($name = "", $tel = "", $idcard = "")
    {

        $str = "<li class=\"list-group-item1 list-group-item-border\">
                    其他乘车人
                    <span class=\"pull-right  \">
                        <a onclick=\"AddGoodsTr();\"><i class='glyphicon glyphicon-plus' aria-hidden='true'></i> </a>
                    </span>
                    <span id=\"buyNumb\" class=\"pull-right\">1</span>
                </li>

                <li class=\"list-group-item1\" id=\"tr_1\">
                    乘车人信息1
                    <div class=\"pull-right  \">
                        <div style=\"max-width: 250px\">
                            <div>
                                <div class=\"col-xs-5\">
                                    <input type=\"text\" class=\"form-control\" name=\"realname_1\"
                                           value=\"$name\" id=\"realname_1\"
                                           placeholder=\"姓名必填\">
                                </div>
                                <div class=\"col-xs-7\">
                                    <input type=\"number\" class=\"form-control\" name=\"mobilephone_1\"
                                           value=\"$tel\" id=\"mobilephone_1\"
                                           placeholder=\"手机号\">
                                </div>
                            </div>
                            <div class=\"clearfix\"></div>
                            <div style=\"margin-top: 5px\">
                                <div class=\"col-xs-12\">
                                    <input type=\"text\" name=\"idcard_1\" id=\"idcard_1\" class=\"form-control\"
                                           value=\"$idcard\"
                                           placeholder=\"身份证号\">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=\"clearfix\"></div>
                </li>
    ";
        return $str;
    }
}


/*按字段获取订单的内容
 *
 *
 * */
if (!function_exists('GetOrderOneInfo')) {

    function GetOrderOneInfo($orderId, $fieldName)
    {
        global $dsql;

        $return = "";
        if ($orderId > 0) {
            //根据模型 获取订单的附加 表
            $query = "SELECT $fieldName FROM #@__order           where #@__order.id='$orderId'";
            //dump($query);
            $rowOrder = $dsql->GetOne($query);
            if (isset($rowOrder[$fieldName]) && $rowOrder[$fieldName] != "") {
                $return = $rowOrder[$fieldName];
                if ($fieldName == "ordertype") {
                    $return = strtoupper(str_replace("order", "", $return));
                    if ($return == "ZTC") $return = "直通车会员卡";
                    if ($return == "CAR") $return = "车辆租赁";
                    if ($return == "HYK") $return = "合伙人会员卡";
                    if ($return == "LYCP") $return = "旅游线路";
                    if ($return == "CZK") $return = "充值卡";
                }
            }
        }
        return $return;

    }
}


//判断身份证 是否已经预约过当日的线路
if (!function_exists('GetIdcardIStrueAppt')) {
    function GetIdcardIStrueAppt($idcard, $appttime)
    {
        global $dsql;


        $date_str = GetDateMk($appttime);
        //dump($date_str);
        $date_min_str = $date_str . " 00:00:00";//当天最小时间
        $date_max_str = $date_str . " 23:59:59";//当天最大时间
        $date_min_int = GetMkTime($date_min_str);
        $date_max_int = GetMkTime($date_max_str);


        //搜索其他乘车人的身份证,所选的当日  是否是否预订过
        $query355 = "SELECT #@__order_addon_lycp.id FROM #@__order_addon_lycp
                      LEFT JOIN #@__order ON #@__order_addon_lycp.orderid= #@__order.id
                      WHERE
                        (#@__order.isdel=0 OR #@__order.isdel=4 ) AND #@__order_addon_lycp.isdel=0 AND #@__order.sta=1  AND
                        #@__order_addon_lycp.idcard='$idcard'
                             AND 
                         (
                              #@__order_addon_lycp.appttime >='{$date_min_int}'
                              AND 
                              #@__order_addon_lycp.appttime  <='{$date_max_int}'
                          );
                         ";//每个卡当天没有重复预约多条线路
        //  dump($query355);
        $rowarc355 = $dsql->GetOne($query355);
        if (isset($rowarc355['id']) && $rowarc355['id'] > 0) {
            return true;
        } else {
            echo false;
        }

    }
}


//判断预约时间  是否在 线路截止日期前
if (!function_exists('GetLineBeforHoursIStrue')) {

    /**
     * @param $lineid   线路
     * @param $appttime 用户输入的预约时间
     *
     * @return bool  可以预约TRUE 不可以预约false
     */
    function GetLineBeforHoursIStrue($lineid, $appttime)
    {
        global $dsql;

        $return = false;
        $query11 = "SELECT beforHours,gotime FROM #@__line where id='$lineid' ";
        $rowarc = $dsql->GetOne($query11);
        //dump($appttime);
        //dump($rowarc['beforHours']);
        if (isset($rowarc['gotime']) && $rowarc['gotime'] > 0) {
            $app_time = date('Y-m-d', $appttime) . date(' H:i:00', $rowarc['gotime']);//预约的日期+发车的时间
            $app_time_int = strtotime($app_time);
            // dump(GetDateMk($appttime));
            $sy_int = ((int)($app_time_int) - (time()));
            //dump(GetDateMk($sy_int));
            $sy_hours = $sy_int / 3600;  //当前日期距发车的小时数

            //如果预约截止时间大于0,则与剩余时间比较
            if ($rowarc['beforHours'] > 0 && $sy_hours > $rowarc['beforHours']) {
                $return = true;
            }
            //如果预约截止时间为0,则于当前时间比较
            //dump(!($rowarc['beforHours']>0));
            //dump($app_time_int );
            //dump(time());
            if (!($rowarc['beforHours'] > 0) && $app_time_int > time()) {
                // dump(GetDateMk($appttime));
                //dump(GetDateMk(time()));
                $return = true;

            }
        }

        return $return;

    }
}


//返回当前线中还可以预约的人数  订单提交时 与提交的数量 判断
//
if (!function_exists('GetLineSeatsNumb')) {

    /**
     * @param $lineid   线路
     * @param $appttime 用户输入的预约时间
     *
     * @param $buynumb  用户购买的数量
     *
     * @return        //大于0,表示还可以预约的人数
     * //返回空的话,表示未满员或不限制人数
     *                返回0表示不可以预约
     */
    function GetLineSeatsNumb($lineid, $appttime, $buynumb)
    {
        global $dsql;

        $return = "";
        $query11 = "SELECT seats FROM #@__line where id='$lineid' AND seats>0";
        $rowarc = $dsql->GetOne($query11);
        //显示没有预约过的订单详细列表
        if (isset($rowarc['seats'])) {

            $s_seats = GetLineSeatsNumb_yjyy($lineid, $appttime);//已经预约的人数
            $t_seats = $rowarc['seats'];//座位数
            $sy_seats = $t_seats - $s_seats;//剩余座位数
            if ($s_seats > 0 && $s_seats >= $t_seats) {

                $return = 0;

            } else if ($buynumb > $sy_seats) {
                $return = $sy_seats;
            }
        }
        return $return;
    }
}

//返回当前线是否超员
//
if (!function_exists('GetLineCAOYUAN')) {

    /**
     * @param $lineid   线路
     * @param $appttime 用户输入的预约时间
     *
     * @param $buynumb  用户购买的数量
     *
     * @return        //大于0,表示还可以预约的人数
     * //返回空的话,表示未满员或不限制人数
     *                返回0表示不可以预约
     */
    function GetLineCAOYUAN($lineid, $appttime, $buynumb)
    {
        global $dsql;

        $return = true;
        $query11 = "SELECT seats FROM #@__line where id='$lineid' AND seats>0";
        $rowarc = $dsql->GetOne($query11);
        //dump($query11);
        if (isset($rowarc['seats'])) {

            //如果有座位限定 则判断数量
            $s_seats = GetLineSeatsNumb_yjyy($lineid, $appttime);//已经预约的人数
            $t_seats = $rowarc['seats'];//座位数
            $sy_seats = $t_seats - $s_seats;//剩余座位数
            //dump($sy_seats);
            if ($sy_seats > 0 && $sy_seats >= $buynumb) {
                $return = false;
            }
        } else {
            //没有座位设定 则不判断人数
            $return = false;
        }
        return $return;
    }
}


//返回当前线中已经预约的人数
//
if (!function_exists('GetLineSeatsNumb_yjyy')) {

    /**
     * @param $lineid   线路
     * @param $appttime 固定线路的预约时间
     *
     * @return        //已经预约的人数
     */
    function GetLineSeatsNumb_yjyy($lineid, $appttime = "")
    {
        global $dsql;

        $return = 0;
        $wheresql = "";
        if ($appttime > 0) $wheresql = " AND appttime='$appttime'";
        $query = ("select count(#@__order_addon_lycp.id) as dd FROM #@__order_addon_lycp 
                                LEFT JOIN #@__order ON #@__order.id=#@__order_addon_lycp.orderid
                                where  lineid='$lineid' AND   (#@__order.isdel=0 OR #@__order.isdel=4 ) AND  #@__order_addon_lycp.isdel=0 AND  #@__order.sta=1 $wheresql");
        $goodRow = $dsql->GetOne($query);
        if ($goodRow) $return = $goodRow['dd'];//座位数


        return $return;

    }
}


//获取 当前线路 当前发车时间 当前车辆  可以使用的最大的座位号
//如果数据中有断的座位号,则使用断的补用
if (!function_exists('GetLineAppttimeMaxSeatsNumb')) {

    /**
     * @param $lineid    线路
     * @param $appttime  用户输入的预约时间
     *
     * @param $deviceid  车辆ID  (用户预约时这个是空,后台编辑车辆时,这个要输入车辆ID)
     *
     * @return int      当前最大的座位号
     *
     */
    function GetLineAppttimeMaxSeatsNumb($lineid, $appttime, $deviceid = "")
    {
        global $dsql;

        $seatNumber = 1;//默认1号
        $wheresql = "";
        //if ($deviceid != "") $wheresql = " AND deviceid='$deviceid'";//这个参数没有用了,删除 掉
        $questr = "SELECT group_concat(seatNumber) AS seatNumber_str  FROM `#@__order_addon_lycp` WHERE  lineid='$lineid' AND appttime='$appttime' $wheresql";
        $rowarc = $dsql->GetOne($questr);
        if ($rowarc['seatNumber_str'] != "") {
            $seatNumber_str = $rowarc['seatNumber_str'];//所有的座位号数组
            $seatNumber_array = explode(",", $seatNumber_str);
            $seatNumber_max = max($seatNumber_array);//获取最大的座位号，
            $isBreak = true; //是否有中断的座位号，默认是true代表没有中断
            for ($ii = 1; $ii < $seatNumber_max; $ii++) {
                if (array_search($ii, $seatNumber_array) === false) {
                    //如果发现数组中没有此座位号，则座位号为此，并跳出
                    $seatNumber = $ii;
                    $isBreak = false;
                    break;
                }
            }
            //没有中断
            if ($isBreak) $seatNumber = $seatNumber_max + 1;
        }


        return $seatNumber;

    }
}

//根据子订单ID号,在车辆使用记录里,查询 车辆租赁 的用车记录
if (!function_exists('GetOrderUseDeviceLog')) {

    function GetOrderUseDeviceLog($orderAddonId)
    {
        $return_str = "";
        global $dsql;
        $query = "SELECT #@__device_automobile_uselog.guideid,#@__device_automobile_uselog.driverid,#@__device.devicename  FROM  `#@__device_automobile_uselog`
          LEFT JOIN #@__device ON #@__device.id=#@__device_automobile_uselog.deviceid
          WHERE orderAddonId='$orderAddonId'
          ORDER BY #@__device_automobile_uselog.id ASC ";
        //dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute("170222");
        $iuuuu = 0;
        while ($row = $dsql->GetArray("170222")) {
            $iuuuu++;
            $devicename = $row["devicename"];
            $driverid = GetEmpNameById($row["driverid"]);
            $driverid_phone = GetEmpPhoneById($row["driverid"]);;
            $guideid = GetEmpNameById($row["guideid"]);
            $guideid_phone = GetEmpPhoneById($row["guideid"]);;

            if ($iuuuu > 1) $return_str .= "<br>";
            $return_str .= "车牌号:$devicename";
            if ($row["driverid"] > 0) $return_str .= "<br> 司机:{$driverid} {$driverid_phone}  ";
            if ($row["guideid"] > 0) $return_str .= " <br> 乘务:{$guideid} {$guideid_phone} ";
        }

        return $return_str;
    }
}


//生成16位不重复充值卡密码
if (!function_exists('GetOrderCZKpassword')) {

    function GetOrderCZKpassword()
    {
        global $dsql;
        $passwrod = rand(10, 99) . date('i') . rand(0, 9) . date('m') . rand(0, 9) . date('d') . rand(0, 9) . date('y') . rand(0, 9) . date('s');


        $query3 = "SELECT id  FROM #@__order_addon_czk                WHERE czk_password='$passwrod'";
        $row = $dsql->GetOne($query3);
        if (isset($row["id"]) && $row["id"] > 0) {
            $passwrod = rand(10, 99) . date('i') . rand(0, 9) . date('m') . rand(0, 9) . date('d') . rand(0, 9) . date('y') . rand(0, 9) . date('s');
        }

        //dump(strlen($passwrod));


        return $passwrod;


    }
}

if (!function_exists('GetOrderNumb')) {
    function GetOrderNumb($goodsid, $orderaddtable = "")
    {
        global $dsql;
        if ($orderaddtable == "") {
            $query = "SELECT gt.channeltype as channelid FROM `#@__goods` goods
                  LEFT JOIN `#@__goods_type` gt ON gt.id=goods.typeid
                  WHERE goods.id='$goodsid' ";
            $goodRow = $dsql->GetOne($query);
            if (!is_array($goodRow)) return;
            $channelid = $goodRow['channelid'];
            if (empty($channelid) || !$channelid > 0) return;

            //获取商品模型的附加表
            $sql = "SELECT addtable FROM `#@__sys_channeltype` WHERE id='$channelid'";
            $cts = $dsql->GetOne($sql);
            $orderaddtable = trim($cts['addtable']);
            if (empty($orderaddtable)) return;
        }
        $orderaddtable = str_replace("x_goods", "x_order", $orderaddtable);
        global $dsql;

        $order_numb = "0";

        $row = $dsql->GetOne("SELECT count(goodsid) as dd  FROM $orderaddtable
                             LEFT JOIN x_order  ON $orderaddtable.orderid=x_order.id
                             WHERE goodsid='$goodsid'
                             and x_order.isdel=0 And x_order.sta=1 ");
        if (isset($row['dd']) && $row['dd'] > 0) $order_numb = $row['dd'];

        return $order_numb;
    }

}

//后台判断直接车实体卡号是否重复
if (!function_exists('ValidateZtcCardCodeISon')) {
    /**
     * @param        $cardcode    操作员输入的实体卡号
     * @param string $orderid     订单号,这个是订单创建后输入实体卡号时,不与当前订单判断
     *
     * @return bool|string
     */
    function ValidateZtcCardCodeISon($cardcode, $orderid = "")
    {
        global $dsql;
        $wheresql = "";
        if (!is_numeric($cardcode)) {
            return "输入的实体卡号必须是数字";
        }
        if ($orderid != "") $wheresql = " AND   #@__order.id!='$orderid'";
        $arcQuery = "SELECT ztc.cardcode  FROM #@__order
                     LEFT JOIN #@__order_addon_ztc  ztc ON ztc.orderid=#@__order.id
                     WHERE  cardcode='$cardcode'  AND  #@__order.isdel=0 ";
        //dump($arcQuery);
        $arcRow = $dsql->GetOne($arcQuery);
        if (isset($arcRow["cardcode"]) && $arcRow["cardcode"] != "") {
            return "输入的实体卡号,已经被别的订单使用!";
        } else {
            return "可以使用";
        }
    }
}