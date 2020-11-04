<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 会员小助手
 *
 * @version        $Id: archive.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


/**170203用户注册过程,微信注册\手工注册\后台手工添加都使用这个
 *
 * @param $realname
 * @param $mobilephone
 * @param $mobilephone_check
 * @param $address
 * @param $tag
 * @param $description
 * @param $from
 * @param $depid
 * @param $openid
 * @param $AppId
 * @param $pwd
 * @param $idcard
 * @param $operatorid
 * @param $sponsorid
 * @param $nickname
 * @param $sex
 * @param $city
 * @param $province
 * @param $country
 * @param $headimgurl
 *
 * @return int
 */
if (!function_exists('RegClient')) {
    function RegClient(
        $realname, $mobilephone, $mobilephone_check, $address, $tag, $description, $from,
        $idcard, $operatorid, $sponsorid,
        $pwd,
        $depid, $openid, $AppId,
        $nickname, $sex, $city, $province, $country, $headimgurl
    )
    {
        global $dsql;

        $senddate = time();
        $mobilephone_checkDate = 0;
        if ($mobilephone_check == 1) $mobilephone_checkDate = $senddate;//如果手机已经检测  则检测时间是当前时间
        //添加到客户主表
        $addSql = "INSERT INTO `#@__client` ( `realname`, `mobilephone`, `mobilephone_check`, `mobilephone_checkDate`,`address`, `tag`, `senddate`, `pubdate`,`description`, `from`)
                         VALUES ( '$realname', '$mobilephone', '$mobilephone_check',  '$mobilephone_checkDate','$address', '$tag', '$senddate', '$senddate',  '$description',  '$from');";
        //dump($this->client_access_token);
        //dump($this->depid);

        $dsql->ExecuteNoneQuery($addSql);
        $clientid = $dsql->GetLastID();
        if ($clientid > 0) {


            //创建用户扩展表
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__client_addon` (clientid,idcard,operatorid,sponsorid) VALUE ('$clientid','$idcard','$operatorid','$sponsorid' )");


            if ($openid != "") {
                //如果OPENID不为空 则前台创建的   要检查OPENID是否重复
                ///170502如果OPENID不存在才创建
                $sql = "SELECT clientid FROM `#@__client_depinfos`  WHERE isdel=0 and  openid='$openid'";
                $row = $dsql->GetOne($sql);
                if (empty($row)) {
                    //添加到部门信息附表
                    $addSql1 = "INSERT INTO `#@__client_depinfos` ( `clientid`, `depid`, `openid`,  `AppID`, `senddate`, `isdel`)
                   VALUES                          (  '$clientid', '$depid', '$openid', '$AppId', '$senddate','0');";
                    $dsql->ExecuteNoneQuery($addSql1);
                }
            } else {
                //后台添加的 不检查重复
                //添加到部门信息附表
                $addSql1 = "INSERT INTO `#@__client_depinfos` ( `clientid`, `depid`, `openid`,  `AppID`, `senddate`, `isdel`)
                   VALUES                          (  '$clientid', '$depid', '$openid', '$AppId', '$senddate','0');";
                $dsql->ExecuteNoneQuery($addSql1);
            }

            $loginip = GetIP();
            //插入客户密码表,默认为空值
            $sql2 = "INSERT INTO `#@__client_pw` ( `clientid`,`pwd`,  `logintime`, `loginip`, `loginnumb`)
                                          VALUES ( '$clientid','$pwd', '$senddate', '$loginip', '1');";
            $dsql->ExecuteNoneQuery($sql2);

            //添加微信信息
            $addSql3 = "INSERT INTO `#@__client_weixin` (`clientid`, `nickname`, `sex`, `city`, `province`, `country`, `photo`) 
                                  VALUES ('$clientid', '$nickname', '$sex', '$city', '$province', '$country','$headimgurl');";
            $dsql->ExecuteNoneQuery($addSql3);
        }
        return $clientid;


    }
}


if (!function_exists('GetOPENID_INdate')) {
    function GetOPENID_INdate($client_openid)
    {
        //判断 OPENID是否在系统中存在
        //使用openid在系统中获取用户的ID


        $clinetid_dddd = 0;
        global $dsql;
        $sql = "SELECT clientid FROM `#@__client_depinfos`  WHERE isdel='0' AND  openid='$client_openid'";
        //这里随后要验证一下,是否需要加上depid验证????170203
        $row = $dsql->GetOne($sql);
        if (!empty($row) && $row["clientid"] > 0) {
            $sql111 = "SELECT clientid FROM x_client_depinfos 
                            LEFT JOIN x_client ON x_client_depinfos.clientid=x_client.id 
                             WHERE isdel='0' AND  openid='$client_openid' AND mobilephone_check='1'";
            $row11 = $dsql->GetOne($sql111);
            if (!empty($row11)) {
                //修补漏洞170502,如果一个openid有两个账户  ,则检查这个OPENID的手机是否验证,如果已经验证了,则使用验证了的ID,
                $clinetid_dddd = $row11["clientid"];
            } else {
                //如果没有验证  ,则随便使用
                //openid已经存在,则使用登录成功;
                $clinetid_dddd = $row["clientid"];
            }
        }
        return $clinetid_dddd;
    }
}

//更新 会员 的上级推荐人
if (!function_exists('UPDATEclientSponsorid')) {

    /**
     * @param $clientid   当前用户ID
     * @param $sponsorid  扫码后的上级ID
     *
     * @return int
     */
    function UPDATEclientSponsorid($clientid, $sponsorid)
    {
        global $dsql;

        //购买过直通车 不可能更换推荐人
        $ispay = true; //是否购买过    TRUE没有购买过  false购买过
        $query = "select o1.id from   #@__order o1     WHERE o1.clientid='$clientid' AND ordertype='orderZtc' AND  o1.isdel='0' AND o1.sta=1";
        $rowarc = $dsql->GetOne($query);
        if (isset($rowarc['id'])) {
            $ispay = false;
        }

        //有下级不可以更换推荐人
        $isxj = true; //是否有下级  TRUE没有 FALSE有下级
        $query = "select clientid from        #@__client_addon     where sponsorid='$clientid'  ";
        $rowarc = $dsql->GetOne($query);
        if (isset($rowarc['clientid'])) {
            $isxj = false;
        }

        //GetClientType();
        //股东 不可以更换推荐人
        $isgudong = true; //是否股东  TRUE不是 FALSE是
        $rankInfo = GetClientType("rank", $clientid);
        $rankInfo_array = explode(",", $rankInfo);
        if (in_array("合伙人", $rankInfo_array)) {
            $isgudong = false;
        }


        /*require_once DWTINC . '/weixin/pay/log.php';
        //初始化日志
        $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_erwm.log');
        $log = Log::Init($logHandler, 15);
        Log::DEBUG("postObj:" . json_encode($ispay ."a-a". $isxj ."a-a". $isgudong ."a-a". $sponsorid ."a-a". $clientid));*/


        if ($ispay && $isxj && $isgudong && $sponsorid != $clientid) {
            //dump(444);
            //更新推荐人
            $query = "UPDATE #@__client_addon SET   sponsorid='$sponsorid'    WHERE clientid='$clientid'; ";
            $dsql->ExecuteNoneQuery($query);
            return true;
        } else {
            return false;
        }
    }
}
/**
 *  分别获取两种用户类型
 *
 * @param     $type rank or score
 *
 * @return    array
 */
if (!function_exists('GetClientType')) {
    function GetClientType($type, $clientid)
    {
        global $dsql;
        $return_str = "";
        if ($type == "rank") {
            $nowtime = time();
            $sql = "SELECT group_concat(rank) as rank FROM `#@__clientdata_ranklog`  WHERE  clientid='$clientid' AND rankcutofftime>$nowtime  ";
            $chRow = $dsql->GetOne($sql);
            if (isset($chRow["rank"]) && $chRow["rank"] != "") {
                $return_str = $chRow["rank"];
            }
        }

        if ($type == "score") {
            //$nowtime = time();
            $return_str = 0;
            $sql = "SELECT scoresnum FROM `#@__client_addon`  WHERE clientid='$clientid'";
            $chRow = $dsql->GetOne($sql);
            if (isset($chRow["scoresnum"]) && $chRow["scoresnum"] != "") {
                $return_str = $chRow["scoresnum"];
            }

            $sql = "SELECT titles,scores FROM `#@__clientdata_scoresname`  WHERE scores<='$return_str' ORDER BY scores DESC";
            $chRow = $dsql->GetOne($sql);
            if (isset($chRow["titles"]) && $chRow["titles"] != "") {
                $return_str .= "," . $chRow["titles"] . "," . $chRow["scores"];
            }
        }

        return $return_str;

    }
}


/**
 *  获取用户所有类型
 *170320这个随后 要和上面的 GetClientType合并一下?????
 */
if (!function_exists('GetClientAllType')) {
    /**
     * @param     $clientid
     * @param int $isgq 是否显示过期的 0显示  1不显示过期的
     *
     * @return string
     */
    function GetClientAllType($clientid, $isgq = 0)
    {
        global $dsql;
        $return_str = "";

        $wheresql = "";
        if ($isgq) {
            $nowtime = time();
            $wheresql = "  AND rankcutofftime>$nowtime  ";
        }
        $sql = "SELECT clientid,GROUP_CONCAT(x_clientdata_ranklog.rank,'|',x_clientdata_ranklog.rankcutofftime) AS rankinfostr 
                        FROM `x_clientdata_ranklog`
                        WHERE clientid='$clientid' $wheresql
                         GROUP BY `x_clientdata_ranklog`.clientid";
        $chRow = $dsql->GetOne($sql);
        if (isset($chRow["rankinfostr"]) && $chRow["rankinfostr"] != "") {

            $rankinfostr = $chRow["rankinfostr"];
            $ranki = 0;
            $rankhtml = "";
            if ($rankinfostr != "") {
                foreach (explode(",", $rankinfostr) as $rankinfo) {
                    $ranki++;
                    $rankinfo_array = explode("|", $rankinfo);
                    $rankname = $rankinfo_array[0];
                    $ranktime = GetDateMk($rankinfo_array[1]);
                    if ($ranki > 1) $rankhtml .= "<br>";
                    $rankhtml .= "$rankname  $ranktime ";
                }
                $return_str = $rankhtml;
            }
        }


        return $return_str;

    }
}
/**
 *  获取用户的提现数据   前后台共用
 */
if (!function_exists('GetExtractionInfo')) {
    /**
     * @param     $clientid
     *
     * @return string
     */
    function GetExtractionInfo($clientid)
    {
        global $dsql;

        $return_array = array();
        //获取客户姓名 电话 金币余额
        $sql = "SELECT  cl.realname,cl.mobilephone,  cladd.jbnum 
          FROM #@__client_depinfos
             LEFT JOIN #@__client cl on cl.id=#@__client_depinfos.clientid
             LEFT JOIN #@__client_addon cladd on cl.id=cladd.clientid
             WHERE #@__client_depinfos.clientid='$clientid'";
        $row = $dsql->GetOne($sql);
        if (is_array($row)) {
            $return_array["realname"] = $row["realname"] . " " . $row["mobilephone"];
            $nowRankInfo = GetClientAllType($clientid, 1);//获取 会员当前的有效身份
            $return_array["realname"] .= "<br>" . $nowRankInfo;
            $return_array["jbye"] = $row["jbnum"] / 100;;
            $return_array["jbmax"] = "0";//可提现的金额,如果是0不可提现
        }


        //获取不过期的会员类型
        $rankinfo = "0";//默认这个是注册会员
        $nowtime = time();
        $sql = "SELECT GROUP_CONCAT(x_clientdata_ranklog.rank) AS rankinfostr 
                        FROM `x_clientdata_ranklog`
                        WHERE clientid='$clientid'  AND rankcutofftime>$nowtime  
                         GROUP BY `x_clientdata_ranklog`.clientid";
        //dump($sql);

        $chRow = $dsql->GetOne($sql);
        if (isset($chRow["rankinfostr"]) && $chRow["rankinfostr"] != "") {
            $rankinfo .= "," . $chRow["rankinfostr"];
        }

        //dump($rankinfo);


        //根据类型获取提现规则
        $extraction_config_array = array();
        $rank_array = explode(",", $rankinfo);
        foreach ($rank_array as $value) {
            if ($value != "") {
                $sqlrowTrue = "SELECT configType,jbnum FROM `#@__clientdata_extraction_config`
                                    WHERE  clientTypeValue ='$value' ";
                $dsql->SetQuery($sqlrowTrue);
                $dsql->Execute('share');
                while ($row11 = $dsql->GetArray('share')) {
                    $jbnum = $row11["jbnum"] / 100;
                    $extraction_config_array[$row11["configType"]][] = $jbnum;
                }
            }
        }


        $qt_minjbnum = -1;//取提数额
        $bl_jbnum = 0;//保留数量
        //dump($extraction_config_array);
        if (count($extraction_config_array) > 0) {
            if (count($extraction_config_array["起提数量"]) > 0) {

                //获取不同身份的起提数量
                //dump($extraction_config_array["起提数量"]);
                $minjbnum_key = array_search(min($extraction_config_array["起提数量"]), $extraction_config_array["起提数量"]);
                $qt_minjbnum = intval($extraction_config_array["起提数量"][$minjbnum_key]);

            }
            if (count($extraction_config_array["保留数量"]) > 0) {

                //获取不同身份的起提数量
                //dump($extraction_config_array["起提数量"]);
                $minjbnum_key = array_search(min($extraction_config_array["保留数量"]), $extraction_config_array["保留数量"]);
                $bl_jbnum = intval($extraction_config_array["保留数量"][$minjbnum_key]);

            }
        }

        //金币余额大于0   起提已经设置过    余额大于起提金额
        if ($return_array["jbye"] > 0 && $qt_minjbnum > -1 && $return_array["jbye"] > $qt_minjbnum) {
            if ($bl_jbnum > 0) {
                //保留数量 大于0
                $return_array["jbmax"] = $return_array["jbye"] - intval($bl_jbnum);
            } else {
                $return_array["jbmax"] = $return_array["jbye"];
            }

        }

        return $return_array;

    }
}

//获取系统中所有会员 的类型
if (!function_exists('GetSYSClientAllType')) {
    function GetSYSClientAllType()
    {
        global $dsql;
        $clientType_array = array();
        //检出所有的会员类型
        //在benefit.class.php中有同样的代码
        $query3 = "SELECT rank FROM `x_clientdata_ranklog` group by rank";
        $dsql->SetQuery($query3);
        $dsql->Execute("000");
        while ($row1 = $dsql->GetArray("000")) {
            $rank = $row1["rank"];
            $info = "";//说明
            $clientType_array[] = array("type" => "rank", "typevalue" => $rank, "info" => "");
        }


        //检出所有的成长值
        //在benefit.class.php中有同样的代码
        $query3 = "SELECT titles,scores FROM `x_clientdata_scoresname` ";
        //在提现规则中有同样的代码
        $dsql->SetQuery($query3);
        $dsql->Execute("111");
        while ($row1 = $dsql->GetArray("111")) {
            $titles = $row1["titles"];
            $scores = $row1["scores"];
            $info = "({$scores}分以上)";
            if ($scores == 0) $info = "(非会员)";
            $clientType_array[] = array("type" => "scores", "typevalue" => $scores, "info" => $titles . $info);
        }


        return $clientType_array;

    }
}

/** 获取会员姓名
 *
 * @param $clientId
 *
 * @return string
 */
if (!function_exists('getOneCLientRealName')) {
    function getOneCLientRealName($clientId)
    {
        global $dsql;
        $str = "";
        if ($clientId > 0) {
            $questr1 = "SELECT realname FROM `#@__client`
                        LEFT JOIN `#@__client_depinfos` on  `#@__client`.id=`#@__client_depinfos`.clientid
                        where  `#@__client`.id='$clientId'  and `#@__client_depinfos`.isdel=0";
            $row2 = $dsql->GetOne($questr1);
            if (isset($row2['realname']) && $row2['realname'] != "") {
                $str = $row2['realname'];
            } else {
                $str = "编号{$clientId}未设置姓名";
            }
        }
        return $str;
    }
}

/** 获取会员手机
 *
 * @param $clientId
 *
 * @return string
 */
if (!function_exists('getOneClientMobilephone')) {
    function getOneClientMobilephone($clientId)
    {
        global $dsql;
        $str = "";
        if ($clientId > 0) {
            $questr1 = "SELECT mobilephone FROM `#@__client`
                        LEFT JOIN `#@__client_depinfos` on  `#@__client`.id=`#@__client_depinfos`.clientid
                        where  `#@__client`.id='$clientId'  and `#@__client_depinfos`.isdel=0";
            $row2 = $dsql->GetOne($questr1);
            if (isset($row2['mobilephone']) && $row2['mobilephone'] != "") {
                $str = $row2['mobilephone'];
            } else {
                $str = 0;
            }
        }
        return $str;
    }
}

/** 获取会员上级编号
 *
 * @param $clientId
 *
 * @return string
 */
if (!function_exists('getOneClientSponsorid')) {
    function getOneClientSponsorid($clientId)
    {
        global $dsql;
        $str = "";
        if ($clientId > 0) {
            $questr1 = "SELECT sponsorid FROM `#@__client_addon`
                        LEFT JOIN `#@__client_depinfos` on  `#@__client_addon`.clientid=`#@__client_depinfos`.clientid
                        where  `#@__client_addon`.clientid='$clientId'  and `#@__client_depinfos`.isdel=0";
            $row2 = $dsql->GetOne($questr1);
            if (isset($row2['sponsorid']) && $row2['sponsorid'] != "") {
                $str = $row2['sponsorid'];
            } else {
                $str = 0;
            }
        }
        return $str;
    }
}


/**
 *  分别获取用户金币和积分
 *
 * @param    $type  jf OR jb
 * @param    $clientid
 *
 * @return    array   返回的是实际数量 单位元 ,除以100以后的
 */
if (!function_exists('GetClientJBJFnumb')) {
    function GetClientJBJFnumb($type, $clientid)
    {
        global $dsql;
        $return_str = 0;
        if ($type == "jb") {
            $sql = "SELECT jbnum    FROM #@__client_addon  WHERE   clientid='$clientid'";
            $chRow = $dsql->GetOne($sql);
            if (isset($chRow["jbnum"]) && $chRow["jbnum"] != "") {
                $return_str = $chRow["jbnum"];
            }
        }
        if ($type == "jf") {
            $sql = "SELECT jfnum    FROM #@__client_addon  WHERE   clientid='$clientid'";
            $chRow = $dsql->GetOne($sql);
            if (isset($chRow["jfnum"]) && $chRow["jfnum"] != "") {
                $return_str = $chRow["jfnum"];
            }
        }
        //dump($return_str / 100);
        return $return_str / 100;
    }
}

/**
 *  获取用户状态
 *
 * @param    $type  jf OR jb
 * @param    $clientid
 *
 * @return    array   返回的是实际数量 单位元 ,除以100以后的
 */
if (!function_exists('GetClientStatus')) {
    function GetClientStatus($clientid)
    {
        global $dsql;
        $return_str = "";
        $sql = "SELECT isdel    FROM #@__client_depinfos  WHERE   clientid='$clientid'";
        $chRow = $dsql->GetOne($sql);
        if (isset($chRow["isdel"]) && $chRow["isdel"] != "") {
            if ($chRow["isdel"] != 0) $return_str = "<br>此会员账户已经删除";
        } else {
            $return_str = "<br>此会员账户已经删除";
        }
        return $return_str;
    }
}


if (!function_exists('Update_jb')) {
    /**
     * 更新会员金币
     *
     * @param        $clientid        客户iD
     * @param        $jbnum100        数量
     * @param        $desc            描述
     * @param int    $orderid         订单ID
     * @param int    $operatorid      操作员
     * @param string $info            手动输入的操作原因
     *
     * @return bool
     * @internal param $varname
     */
    function Update_jb($clientid, $jbnum100, $desc, $orderid = 0, $operatorid = 0, $info = "")
    {
        global $dsql;
        if ($clientid > 0 && abs($jbnum100) > 0) {
            $clientjbnum100 = GetClientJBJFnumb("jb", $clientid) * 100;//获取 用户当前的金额数量
            if ($jbnum100 < 0) {
                //如果是扣分的操作 则判断用户的积分是否够
                //$query = "SELECT jbnum FROM `#@__client_addon`    WHERE  clientid='$clientid' ";
                //$row1 = $dsql->GetOne($query);
                //if (!(($row1["jbnum"] + $jbnum100) >= 0)) {
                if (!(($clientjbnum100 + $jbnum100) >= 0)) {
                    return false;
                }//如果积分不够减 则返回错误
            }
            //dump("Update `#@__client_addon` set `jbnum`=`jbnum`+{$num} where clientid='$clientid' ");

            $clientjbnum100 = $clientjbnum100 + $jbnum100;
            $dsql->ExecuteNoneQuery("UPDATE `#@__client_addon` set `jbnum`='$clientjbnum100' WHERE clientid='$clientid' ");
            $updatedate = time();


            //获取用户最近的余额
            $yenum100_t = 0;
            $query11 = "SELECT  yenum  FROM x_clientdata_jblog WHERE clientid='$clientid'  ORDER BY  id DESC limit 1";
            //dump($query11);
            $row11 = $dsql->GetOne($query11);
            if (isset($row11["yenum"])) {
                //如果有上一次的余额,则新的余额等于=上一次余额+当前操作金额
                $yenum100_t = $row11["yenum"] + $jbnum100;//上一次的余额  与当前的金额变动  加减
            } else {
                //如果没有上一次的操作  则余额=当前操作的数量
                $yenum100_t = $jbnum100;
            }

            //容错校验  如果余额小于0,则等于0
            if ($yenum100_t < 0) $yenum100_t = 0;


            $sql = "INSERT INTO #@__clientdata_jblog (clientid,jbnum,yenum,createtime,`desc`,orderid,operatorid,info)VALUES ('$clientid','$jbnum100','$yenum100_t','$updatedate','$desc','$orderid','$operatorid','$info');";
            $dsql->ExecuteNoneQuery($sql);


            //如果用户表的余额与金币表的余额不一样,则保存日志
            if ($clientjbnum100 != $yenum100_t) {
                require_once DWTINC . '/weixin/pay/log.php';
                //初始化日志
                $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_jb_jf_ye_error.log');
                $log = Log::Init($logHandler, 15);
                Log::DEBUG("client-helpher-php-clientid:$clientid,金币表yenum100:$yenum100_t,用户表clientjbnum100:$clientjbnum100,操作数字jbnum100:$jbnum100");
            }


            //dump($sql);
            return true;
        }
    }
}


if (!function_exists('Update_jf')) {

    /**
     * 更新会员积分
     *
     * @param        $clientid   客户iD
     * @param        $jfnum100   数量乘以100以后的值
     * @param        $desc       描述
     * @param int    $orderid    订单ID
     * @param int    $operatorid 操作员
     * @param string $info       手动输入的操作原因
     *
     * @return bool
     * @internal param $varname
     */
    function Update_jf($clientid, $jfnum100, $desc, $orderid = 0, $operatorid = 0, $info = "")
    {
        global $dsql;
        if ($clientid > 0 && abs($jfnum100) > 0) {
            $clientjfnum100 = GetClientJBJFnumb("jf", $clientid) * 100;//获取 用户当前的金额数量
            if ($jfnum100 < 0) {
                //如果是扣分的操作 则判断用户的积分是否够
                //$query = "SELECT jfnum FROM `#@__client_addon`    WHERE  clientid='$clientid' ";
                //$row1 = $dsql->GetOne($query);
                //if (!(($row1["jfnum"] + $jbnum100) >= 0)) {
                if (!(($clientjfnum100 + $jfnum100) >= 0)) {
                    return false;
                }//如果积分不够减 则返回错误
            }
            //更新会员积分
            $clientjfnum100 = $clientjfnum100 + $jfnum100;
            $sql = "UPDATE `#@__client_addon` set `jfnum`='$clientjfnum100' WHERE clientid='$clientid' ";
            $dsql->ExecuteNoneQuery($sql);
            $updatedate = time();
            //获取用户最近的余额
            $yenum100_t = 0;
            $query11 = "SELECT  yenum  FROM x_clientdata_jflog WHERE clientid='$clientid'  ORDER BY  id DESC limit 1";
            //dump($query11);
            $row11 = $dsql->GetOne($query11);
            if (isset($row11["yenum"])) {
                //如果有上一次的余额,则新的余额等于=上一次余额+当前操作金额
                $yenum100_t = $row11["yenum"] + $jfnum100;//上一次的余额  与当前的金额变动  加减
            } else {
                //如果没有上一次的操作  则余额=当前操作的数量
                $yenum100_t = $jfnum100;
            }

            //容错校验  如果余额小于0,则等于0
            if ($yenum100_t < 0) $yenum100_t = 0;

            //插入积分明细
            $sql = "INSERT INTO #@__clientdata_jflog (clientid,jfnum,yenum,createtime,`desc`,orderid,operatorid,info)VALUES ('$clientid','$jfnum100','$yenum100_t','$updatedate','$desc','$orderid','$operatorid','$info'); ";
            $dsql->ExecuteNoneQuery($sql);


            //如果用户表的余额与金币表的余额不一样,则保存日志
            if ($clientjfnum100 != $yenum100_t) {
                require_once DWTINC . '/weixin/pay/log.php';
                //初始化日志
                $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_jb_jf_ye_error.log');
                $log = Log::Init($logHandler, 15);
                Log::DEBUG("client-helpher-php-clientid:$clientid,积分表yenum100:$yenum100_t,用户表clientjfnum100:$clientjfnum100,操作数字jfnum100:$jfnum100");
            }

            return true;
        }
    }
}


/**
 *  获取用户openid
 *
 * @param    $type  jf OR jb
 * @param    $clientid
 *
 * @return    array
 */
if (!function_exists('GetClientOpenID')) {
    function GetClientOpenID($clientid)
    {
        global $dsql;
        $return_str = "";
        $sql = "SELECT cw.openid from #@__client o2   LEFT JOIN #@__client_depinfos cw ON cw.clientid=o2.id   where o2.id='$clientid'  ";
        $chRow = $dsql->GetOne($sql);
        if (isset($chRow["openid"]) && $chRow["openid"] != "") {
            $return_str = $chRow["openid"];
        }
        return $return_str;
    }
}


//校验用户支付密码是否正确
if (!function_exists('GetClientPayPwdIsTrue')) {
    function GetClientPayPwdIsTrue($clientid, $paypwd)
    {
        global $dsql;
        $paypwd = substr(md5($paypwd), 5, 20);
        $sql = "SELECT clientid FROM `#@__client_pw`  WHERE  clientid='$clientid' and paypwd='$paypwd' ";
        $chRow = $dsql->GetOne($sql);
        //dump($sql);
        if (is_array($chRow)) {
            return true;
        }
        return false;
    }
}


//校验用户注册或更换的手机是否已经存在
if (!function_exists('ValidatePhoneISon')) {
    /**
     * @param        $mobilephone
     * @param string $clientid 1不为空,则判断非当前clientid是否有此手机的mobilephone_check=1,如果没有,则返回来此手机号未使用(可供注册)
     *                         这个用在前台,有CLIENTID的情况下.和后台编辑用户时有CLIENTID的情况下
     *
     *                          2  为空,则查询所有的手机号,如果有相同的就提示已经注册  这个多用在后台,添加新用户时使用
     *
     * @param string $isyz     是否要验证 手机号是否已经验证过
     *
     * @return string
     */
    function ValidatePhoneISon($mobilephone, $clientid = "", $isyz = "")
    {
        global $dsql;
        $wheresql = "";
        if ($clientid != "") $wheresql = " AND #@__client.id!='$clientid' ";
        if ($isyz == "") $wheresql = " AND mobilephone_check=1 ";
        //同级下部门名称是否重复
        $questr = "SELECT mobilephone FROM `#@__client`
                                 LEFT JOIN `#@__client_depinfos` ON #@__client_depinfos.clientid=#@__client.id
                                WHERE  #@__client_depinfos.isdel=0  AND mobilephone='$mobilephone' 
                                $wheresql
                                ";
        //dump($questr);
        $rowarc = $dsql->GetOne($questr);

        //dump (isset($rowarc["mobilephone"]));
        //   dump($rowarc["mobilephone"] != "") ;
        //   dump($rowarc["mobilephone"]) ;
        if (isset($rowarc["mobilephone"]) && $rowarc["mobilephone"] != "") {
            return "手机号已经被注册,请核对";
        } else {
            return "手机号可用";
        }

    }
}


/*获取一定时间范围内的  两级推广数据
返回格式
$data[0][clientid]=[下级人数]clientid下的一级加二级人数
$data[1][clientid]=[下级人数]clientid下的一级人数
$data[2][clientid]=[下级人数]clientid下的二级人数
*/

if (!function_exists('GetClientTgData')) {
    /**
     * @param $day_s  开始日期
     * @param $day_d  结束日期
     */
    function GetClientTgData($day_s, $day_d)
    {
        global $dsql;
        $data= array();
        $day_s_int = GetMkTime($day_s . " 00:00:00");
        $day_d_int = GetMkTime($day_d . " 23:59:59");

        //时间查询
        $whereSql11 = " AND  x_order.createtime>='$day_s_int' ";
        $whereSql11 .= " AND  x_order.createtime<='$day_d_int' ";




        //当前订单的客户ID为C级
        //C的上级为B级
        //B的上级为A级




        //先判断出  当前时间内的订单的所有人[C],,把这些人有上级介绍人的[B]  筛选出来
        $sponsorid_s = "0";//上级的ID
        $query_1 = " 	SELECT  (sponsorid)  ,count(#@__order_addon_ztc.id) as dd
                        FROM x_order 
                         
                                     INNER JOIN   #@__order_addon_ztc ON #@__order.id=#@__order_addon_ztc.orderid
                        INNER JOIN x_client cl  ON  cl.id=x_order.clientid
                        INNER JOIN x_client_depinfos    ON  cl.id=x_client_depinfos.clientid
                        INNER JOIN x_client_addon    ON  cl.id=x_client_addon.clientid
                        
                        WHERE
                        ordertype='orderZtc' AND
                        paynum>0 AND /*支付金额 大于0的,否则会统计出0元的合伙人优惠*/
                        x_client_depinfos.isdel=0 AND    
                        x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
                        AND x_client_addon.sponsorid>0
                         $whereSql11
                         
                        GROUP BY x_client_addon.sponsorid 
                        ORDER BY dd DESC
                     ";
        //dump($query_1);
        $dsql->SetQuery($query_1);
        $dsql->Execute("171202203722");

        while ($row = $dsql->GetArray("171202203722")) {
            $data["0"][$row["sponsorid"]] = $row["dd"];//第一级的人数 放入一二级总合中
            $data["1"][$row["sponsorid"]] = $row["dd"];  //第一级人数
            //$spon_array[] = $row["sponsorid"];
        }
        if(!count($data)>0)return $data;
        $B_clientid_array = array_keys($data["0"]);//id值
        $B_clientid_s = implode(",", $B_clientid_array);//获取ID,供查找二级使用  array_keys检索出以id为内容的key



        if ($B_clientid_s != "") {
            //查找A级介绍 人
            $query_1 = " 
                          SELECT sponsorid,clientid FROM
                              x_client_addon     
                            WHERE
                                    clientid IN( $B_clientid_s )
                                    AND sponsorid>0
                         ";
             //dump($query_1);
            $dsql->SetQuery($query_1);
            $dsql->Execute("171202204625");

            while ($row1 = $dsql->GetArray("171202204625")) {
                //A的二级人数,是所以A下面B的一级人数+A的一级人数
                if (!isset($data["2"][$row1["sponsorid"]])) $data["2"][$row1["sponsorid"]] = "";
                $ej_numb = $data["0"][$row1["clientid"]];

                $data["2"][$row1["sponsorid"]] += $ej_numb;
            }


        }
        //dump($data);

        if(!isset($data["2"]))$data["2"]="";

        //将A级和B级的clientid全并
        $A_clientid_array = array_keys($data["2"]);//二级ID
        //dump($yj_clientid_array);
        //dump($ej_clientid_array);
        $client_array = array_merge($A_clientid_array, $B_clientid_array);//合并
        $client_array = array_unique($client_array);//去重
        //合并A级和B级的总人数
        foreach ($client_array as $client) {
            if (!isset($data["2"][$client])) $data["2"][$client] = "";
            if (!isset($data["0"][$client])) $data["0"][$client] = "";
            $data["0"][$client] += $data["2"][$client];
        }
        arsort($data["0"]);//使用值(总人数)对数组进行降序
        //dump($data);
        //
         return $data;
    }

}