<?php if (!defined('DWTINC')) exit('Request Error!');
/**
 * 会员登录类
 *
 */

// 使用缓存助手
//helper('cache');
/**
 *  检查用户名的合法性
 *
 * @access    public
 *
 * @param     string $uid      用户UID
 * @param     string $msgtitle 提示标题
 * @param     string $ckhas    检查是否存在
 *
 * @return    string
 */
function CheckUserID($uid, $msgtitle = '用户名', $ckhas = TRUE)
{
    global $cfg_mb_notallow, $cfg_mb_idmin, $cfg_md_idurl, $cfg_soft_lang, $dsql;
    if ($cfg_mb_notallow != '') {
        $nas = explode(',', $cfg_mb_notallow);
        if (in_array($uid, $nas)) {
            return $msgtitle . '为系统禁止的标识！';
        }
    }
    if ($cfg_md_idurl == 'Y' && preg_match("/[^a-z0-9]/i", $uid)) {
        return $msgtitle . '必须由英文字母或数字组成！';
    }

    //if ($cfg_soft_lang == 'utf-8') {
    //    $ck_uid = utf82gb($uid);//160524代码转为UFT8后禁用
    //} else {
    $ck_uid = $uid;
    //}

    for ($i = 0; isset($ck_uid[$i]); $i++) {
        if (ord($ck_uid[$i]) > 0x80) {
            if (isset($ck_uid[$i + 1]) && ord($ck_uid[$i + 1]) > 0x40) {
                $i++;
            } else {
                return $msgtitle . '可能含有乱码，建议你改用英文字母和数字组合！';
            }
        } else {
            if (preg_match("/[^0-9a-z@\.-]/i", $ck_uid[$i])) {
                return $msgtitle . '不能含有 [@]、[.]、[-]以外的特殊符号！';
            }
        }
    }
    if ($ckhas) {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid' ");
        if (is_array($row)) return $msgtitle . "已经存在！";
    }
    return 'ok';
}


function FormatUsername($username)
{
    $username = str_replace("`", "‘", $username);
    $username = str_replace("'", "‘", $username);
    $username = str_replace("\"", "“", $username);
    $username = str_replace(",", "，", $username);
    $username = str_replace("(", "（", $username);
    $username = str_replace(")", "）", $username);
    return addslashes($username);
}

/**
 * 网站会员登录类
 *
 * @package          MemberLogin
 * @subpackage       DWTCMS.Libraries
 * @link             http://www.DWTcms.com
 */
class MemberLogin
{

    /*普通登录-----------------------*/
    var $M_ID;
    /*    var $M_UserName;*/
    var $M_LoginTime;
    var $M_KeepTime;
    var $fields;
    var $memberCache = 'memberlogin';
    /*普通登录-----------------------*/

    /*微信登录-----------------------*/
    var $weixinCode;   //授权获取用户信息时  微信回调返回的用户CODE,根据这个CODE获得OPENid
    var $client_access_token; //客户的token
    var $client_openid; //客户的openid
    var $AppId;//开发者ID
    var $Secret;//开发者密码
    var $depid;//公司代码


    function MemberLogin($depid = 0, $code = "")
    {
        $this->__construct($depid, $code);
    }
    //php5构造函数
    /**
     * @param int    $depid 访问的部门ID
     * @param string $code  微信回调回来的CODE
     *
     * @internal param bool|FALSE $cache
     */
    function __construct($depid = 0, $code = "")
    {

        global $dsql;

        //登录分为两种模式 1种浏览器登录 ,另一种微信登录
        //--------先从COOK中获取ID
        $this->M_ID = $this->GetNum(GetCookie("DWTUserID"));
        $this->M_LoginTime = GetCookie("DWTLoginTime");
        $this->fields = array();
        $this->weixinCode = $code;
        $this->depid = $depid;
        //$this->M_ID="2424";
        //用户没有登录过
        $this->M_KeepTime = 3600 * 24 * 7;//cookies默认有效期七天
        //dump($this->M_ID);
        if ($this->M_ID>0) {


            $this->M_ID = intval($this->M_ID);

            //$this->fields = $dsql->GetOne("SELECT cl.*,clw.nickname,clw.photo FROM `#@__client` cl LEFT JOIN `#@__client_depinfos` clw on cl.id=clw.clientid where cl.id='{$this->M_ID}' ");
            //存储会员相关的信息
            /*    $sql = "SELECT  cl.id,cl.realname,cl.mobilephone,cl.mobilephone_check,
                                depinfos.depid,depinfos.isdel,
                                clw.nickname,clw.photo
                                  FROM #@__client cl
                                  LEFT JOIN #@__client_depinfos depinfos  on cl.id=depinfos.clientid
                                  LEFT JOIN #@__client_weixin clw on cl.id=clw.clientid
                                  where cl.id='{$this->M_ID}'";
        */
            //待优化，随后 要核对，使用的字段内容170205??????
            $sql = "SELECT  cl.id,cl.realname,cl.mobilephone,cl.mobilephone_check,
                            depinfos.depid,depinfos.isdel,depinfos.senddate,
                            cladd.jfnum,cladd.jbnum,cladd.scoresnum,cladd.scorescutofftime,cladd.sponsorid,cladd.idcard,
                            clw.nickname,clw.photo
                              FROM #@__client cl
                              LEFT JOIN #@__client_depinfos depinfos  on cl.id=depinfos.clientid
                              LEFT JOIN #@__client_addon cladd on cl.id=cladd.clientid
                              LEFT JOIN #@__client_weixin clw on cl.id=clw.clientid
                              WHERE depinfos.clientid='{$this->M_ID}'";
            //dump($sql);
            $this->fields = $dsql->GetOne($sql);
            if (is_array($this->fields)&&$this->fields['id']!="") {
                if ($this->fields['isdel'] == 1) {
                    header("Location:dontUse.htm");
                }
                //间隔一小时更新一次用户登录时间
                if (time() - $this->M_LoginTime > 3600) {
                    $dsql->ExecuteNoneQuery("update `#@__client_pw` set logintime='" . time() . "',loginip='" . GetIP() . "' where clientid='" . $this->fields['id'] . "';");
                    PutCookie("DWTLoginTime", time(), $this->M_KeepTime);
                }
                $this->depid = $this->fields['depid'];
            } else {
                $this->ResetUser();
            }



        } else {


            $isLoginPage = false;//是否登录页
            if (preg_match('/login.php/i', GetCurUrl())) {
                $isLoginPage = true;
            }
            //如果是在微信浏览器中，并且DEPID大于0,并且不是从login.php中登录过来的（用户在微信中使用网址直接访问时，不从微信校验登录，而从账户登录 ），则调用微信登录过程
            if (IsWeixinBrowser() && $this->depid > 0 && !$isLoginPage) {

                $this->AppId = GetWeixinAppId($this->depid);
                $this->Secret = GetWeixinAppSecret($this->depid);


                if ($this->weixinCode == "") {
                    //用户第一次打开,没有回调回来CODE
                    //从这里访问微信 回调回来CODE
                    $nowUrl = urlencode("http://" . $_SERVER['HTTP_HOST'] . GetCurUrl());//160917加urlencode否则无法传递多个参数
                    //dump($nowUrl);
                    //访问此地址  微信回调返回CODE 供上面的isset($code) 使用
                    $weixin_gettoken_oauth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->AppId}&redirect_uri=$nowUrl&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
                    //dump($weixin_gettoken_oauth_url);
                    header("Location:$weixin_gettoken_oauth_url");
                    exit;
                } else {
                    //有了CODE后
                    $this->linkWeiXin();
                }

            }
        }


    }




    /*
 *
 *
 */
    /**
     * 获取微信CODE对应的OPENid 的用户  在系统中的clientID
     *授权方式 获取用户的openid和access_token
     */
    function linkWeiXin()
    {
        global $dsql;
        $this->getClientTokn();//授权方式 获取用户的openid和access_token

        if ($this->client_openid != "") {

            $clientid_asdfs=GetOPENID_INdate($this->client_openid);
            $clientid_asdfs=intval($clientid_asdfs);
            if ($clientid_asdfs>0) {
                    $this->M_ID = $clientid_asdfs;
            } else {
                //openid不存在,则创建用户
                if ($this->client_access_token != "" && $this->client_openid != "") {
                    //dump($this->client_openid . "---");
                    $this->M_ID = $this->createClient();
                }
            }
            if ($this->M_ID > 0) $this->PutLoginInfo($this->M_ID, time());//模拟登录
        }
    }


    /**
     *授权方式获取微信客户的access_token和openid
     */
    function getClientTokn()
    {
        //使用code换取用户的access_token
        $dep_appid = $this->AppId;
        $dep_secret = $this->Secret;
        $code = $this->weixinCode;
        //dump($dep_appid);
        //dump($dep_secret);
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$dep_appid&secret=$dep_secret&code=$code&grant_type=authorization_code";
        $handle = fopen($url, "rb");
        if ($handle) {
            $contents = "";
            while (!feof($handle)) {
                $contents .= fread($handle, 8192);
            }
            fclose($handle);
            //dump($contents);
            $json_array = json_decode($contents, TRUE);
            //dump($json_array);
            $this->client_access_token = $json_array['access_token'];//客户的token
            $this->client_openid = $json_array['openid'];
        }
    }


    /**获取微信用户信息 并在系统中创建客户信息
     *
     * @return mixed
     */
    function createClient()
    {

        $nickname = $sex_temp = $city = $province = $country = $headimgurl = "";
        //获得微信用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$this->client_access_token&openid=$this->client_openid&lang=zh_CN";

        $handle = fopen($url, "rb");
        if ($handle) {
            $contents = "";
            while (!feof($handle)) {
                $contents .= fread($handle, 8192);
            }
            fclose($handle);

            $json_array = json_decode($contents, TRUE);
            // $openid = $json_array['openid'];
            $nickname = XSSClean(addslashes(Html2Text($json_array['nickname'])));
            $sex = XSSClean($json_array['sex']);
            $city = XSSClean($json_array['city']);
            $province = XSSClean($json_array['province']);
            $country = XSSClean($json_array['country']);
            $headimgurl = $json_array['headimgurl'];

            $sex_temp = "未知";
            if ($sex == 1) $sex_temp = "男";
            if ($sex == 2) $sex_temp = "女";
        }
        //插入到客户扩展表
        $sponsorid = 0;
        if (GetCookie("DWTsponsorid") != "") $sponsorid = GetCookie("DWTsponsorid");
        //dump($sponsorid);
        $clientid = RegClient(
            $realname = "", $mobilephone = "", $mobilephone_check = "", $address = "", $tag = "", $description = "", $from = "微信",
            $idcard = "", $operatorid = "", $sponsorid,
            $pwd = "",
            $depid = $this->depid, $openid = $this->client_openid, $AppId = $this->AppId,
            $nickname, $sex_temp, $city, $province, $country, $headimgurl
        );
        return $clientid;

    }








    /**
     *  删除缓存,每次登录时和在修改用户资料的地方会清除
     *
     * @access    public
     *
     * @param     string
     *
     * @return    string
    function DelCache($mid)
     * {
     * DelCache($this->memberCache, $mid);
     * }*/


    /**
     *  退出cookie的会话
     *
     * @return    void
     */
    function ExitCookie()
    {
        $this->ResetUser();
    }

    /**
     *  验证用户是否已经登录
     *
     * @return    bool

    function IsLogin()
     * {
     * //dump($this->M_ID."415----");
     * if ($this->M_ID > 0) return TRUE;
     * else return FALSE;
     * }
     */


    //
    /**
     *  重置用户信息
     *
     * @return    void
     */
    function ResetUser()
    {
        $this->fields = '';
        $this->M_ID = '';
        /*        $this->M_LoginID = '';
                $this->M_Rank = 0;
                $this->M_Face = "";
                $this->M_Money = 0;
                $this->M_UserName = "";
                $this->M_LoginTime = 0;
                $this->M_MbType = '';
                $this->M_Scores = 0;
                $this->M_Spacesta = -2;
                $this->M_UpTime = 0;
                $this->M_ExpTime = 0;
                $this->M_JoinTime = 0;
                $this->M_HasDay = 0;*/
        $this->depid = "";
        DropCookie('DWTUserID');
        DropCookie('DWTLoginTime');
        DropCookie('DWTsponsorid');//160917增加退出时清空 推荐用户
        DropCookie('gourl');//160917增加退出时清空 gourl引导页
    }

    /**
     *  获取整数值
     *
     * @access    public
     *
     * @param     string $fnum 处理的数值
     *
     * @return    string
     */
    function GetNum($fnum)
    {
        $fnum = preg_replace("/[^0-9\.]/", '', $fnum);
        return $fnum;
    }


    /**
     *  检查用户是否合法
     *
     * @access    public
     *
     * @param     string $loginuser 登录用户名
     * @param     string $loginpwd  用户密码
     *
     * @return    string
     */
    function CheckUser(&$loginuser, $loginpwd)
    {
        global $dsql;

        //检测用户名的合法性
        $rs = CheckUserID($loginuser, '用户名', FALSE);
        //dump($loginuser);

        //用户名不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            $loginuser = $rs;
            return '0';
        }


        //$sql = "SELECT cp.clientid,cp.pwd,cp.logintime FROM `x_client` c LEFT JOIN `x_client_pw` cp on  c.id=cp.clientid  WHERE c.isdel='0' and FIND_IN_SET('17',c.depids) and mobilephone LIKE '$loginuser' ";
        $sql = "SELECT cp.clientid,cp.pwd,cp.logintime FROM #@__client cl
                LEFT JOIN #@__client_pw cp on  cl.id=cp.clientid
                LEFT JOIN #@__client_depinfos depinfos on cl.id=depinfos.clientid
                WHERE depinfos.isdel='0'  and cl.mobilephone LIKE '$loginuser' ";
        $row = $dsql->GetOne($sql);
        if (is_array($row)) {
            $pwd = substr(md5($loginpwd), 5, 20);
            if ($row['pwd'] != $pwd) {
                return -1;
            } else {
                $this->PutLoginInfo($row['clientid'], $row['logintime']);
                return 1;
            }
        } else {
            return 0;
        }
    }


    /**
     *  保存用户cookie
     *
     * @access    public
     *
     * @param            $clientid
     * @param int|string $logintime 登录限制时间
     *
     * @internal  param string $uid 用户ID
     */
    function PutLoginInfo($clientid, $logintime = 0)
    {
        global $dsql;
        //登录增加积分(上一次登录时间必须大于两小时)
        if ((time() - $logintime) > 7200) {
            //更新会员积分
            //UpdateScores($clientid, "cfg_client_login");
        }
        $this->M_ID = $clientid;
        $this->M_LoginTime = time();
        $loginip = GetIP();
        $inquery = "UPDATE `#@__client_pw` SET loginnumb=loginnumb+1,loginip='$loginip',logintime='" . $this->M_LoginTime . "' WHERE clientid='" . $clientid . "'";
        //160607增加  否则微信直接点发布菜单首次登录的话，得不到用户的信息
        //$this->fields = GetCache($this->memberCache, $this->M_ID);
        /*        if (empty($this->fields)) {
                    $this->fields = $dsql->GetOne("SELECT * FROM `#@__client` WHERE id='{$this->M_ID}' ");
                }*/
        //dump($inquery);
        $dsql->ExecuteNoneQuery($inquery);
        if ($this->M_KeepTime > 0) {
            PutCookie('DWTUserID', $clientid, $this->M_KeepTime);
            PutCookie('DWTLoginTime', $this->M_LoginTime, $this->M_KeepTime);
        } else {
            PutCookie('DWTUserID', $clientid);
            PutCookie('DWTLoginTime', $this->M_LoginTime);
        }
    }

    /**
     *  获得会员目前的状态
     *
     * @access    public
     *
     * @param     string $dsql 数据库连接
     *
     * @return    string
     */
    /*function GetSta($dsql)
    {
        $sta = '';
        if ($this->M_Rank == 0) {
            $sta .= "你目前的身份是：普通会员";
        } else {
            $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE rank='" . $this->M_Rank . "'");
            $sta .= "你目前的身份是：" . $row['membername'];
            $rs = $dsql->GetOne("SELECT id FROM `#@__admin` WHERE userid='" . $this->M_LoginID . "'");
            if (!is_array($rs)) {
                if ($this->M_Rank > 10 && $this->M_HasDay > 0) $sta .= " 剩余天数: <font color='red'>" . $this->M_HasDay . "</font>  天 ";
                elseif ($this->M_Rank > 10) $sta .= " <font color='red'>会员升级已经到期</font> ";
            }
        }
        $sta .= " 拥有金币：{$this->M_Money} 个， 积分：{$this->M_Scores} 分。";
        return $sta;
    }*/

    /**
     *  记录会员操作日志
     *
     * @access    public
     *
     * @param     string $type  记录类型
     * @param     string $title 记录标题
     * @param     string $note记录描述
     * @param     string $aid涉及到的内容的id
     *
     * @return    string

    function RecordFeeds($type, $title, $note, $aid)
     * {
     * global $dsql, $cfg_mb_feedcheck;
     * //确定是否需要记录
     * if (in_array($type, array('add', 'addsoft', 'feedback', 'addfriends', 'stow'))) {
     * $ntime = time();
     * $title = DWT_htmlspecialchars(cn_substrR($title, 255));
     * if (in_array($type, array('add', 'addsoft', 'feedback', 'stow'))) {
     * $rcdtype = array('add' => ' 成功发布了', 'addsoft' => ' 成功发布了软件',
     * 'feedback' => ' 评论了文章', 'stow' => ' 收藏了');
     * //内容发布处理
     * $arcrul = " <a href='/plus/view.php?aid=" . $aid . "'>" . $title . "</a>";
     * $title = DWT_htmlspecialchars($rcdtype[$type] . $arcrul, ENT_QUOTES);
     * } else if ($type == 'addfriends') {
     * //添加好友处理
     * $arcrul = " <a href='/member/index.php?uid=" . $aid . "'>" . $aid . "</a>";
     * $title = DWT_htmlspecialchars(' 与' . $arcrul . "成为好友", ENT_QUOTES);
     * }
     * $note = Html2Text($note);
     * $aid = (isset($aid) && is_numeric($aid) ? $aid : 0);
     * $ischeck = ($cfg_mb_feedcheck == 'Y') ? 0 : 1;
     * $query = "INSERT INTO `#@__member_feed` (`mid`, `userid`, `uname`, `type`, `archivesid`, `dtime`,`title`, `note`, `ischeck`)
     * Values('$this->M_ID', '$this->M_LoginID', '$this->M_UserName', '$type', '$aid', '$ntime', '$title', '$note', '$ischeck'); ";
     * $rs = $dsql->ExecuteNoneQuery($query);
     * return $rs;
     * } else {
     * return FALSE;
     * }
     * }*/
}//End Class  