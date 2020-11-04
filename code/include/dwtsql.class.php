<?php if (!defined('DWTINC')) exit("Request Error!");
/**
 * 数据库类
 * 说明:系统底层数据库核心类
 *      调用这个类前,请先设定这些外部变量
 *      $GLOBALS['cfg_dbhost'];
 *      $GLOBALS['cfg_dbuser'];
 *      $GLOBALS['cfg_dbpwd'];
 *      $GLOBALS['cfg_dbname'];
 *      $GLOBALS['cfg_dbprefix'];
 *
 * @version        $Id: dedesql.class.php 3 20:57 7日
 * @package
 * @copyright
 * @license
 * @link
 */

// 在工程所有文件中均不需要单独初始化这个类，可直接用 $dsql 或 $db 进行操作
// 为了防止错误，操作完后不必关闭数据库
$dsql = $db = new DwtSql(FALSE);

/**
 * Dwt数据库类
 *
 * @package        DwtSql
 * @subpackage
 * @link
 */
class DwtSql
{
    var $linkID;
    var $dbHost;
    var $dbUser;
    var $dbPwd;
    var $dbName;
    var $dbPrefix;
    var $result;
    var $queryString;
    var $parameters;
    var $isClose;
    var $safeCheck;
    var $recordLog = false; // 记录日志到data/mysqli_record_log.inc便于进行调试
    var $isInit = false;
    var $pconnect = false;

    //用外部定义的变量初始类，并连接数据库
    function __construct($pconnect = FALSE, $nconnect = TRUE)
    {
        $this->isClose = FALSE;
        $this->safeCheck = TRUE;
        $this->pconnect = $pconnect;
        if ($nconnect) {
            $this->Init($pconnect);
        }
    }

    function DwtSql($pconnect = FALSE, $nconnect = TRUE)
    {
        $this->__construct($pconnect, $nconnect);
    }

    function Init($pconnect = FALSE)
    {
        $this->linkID = 0;
        //$this->queryString = '';
        //$this->parameters = Array();
        $this->dbHost = $GLOBALS['cfg_dbhost'];
        $this->dbUser = $GLOBALS['cfg_dbuser'];
        $this->dbPwd = $GLOBALS['cfg_dbpwd'];
        $this->dbName = $GLOBALS['cfg_dbname'];
        $this->dbPrefix = $GLOBALS['cfg_dbprefix'];
        $this->result["me"] = 0;
        $this->Open($pconnect);
    }

    //用指定参数初始数据库信息
    function SetSource($host, $username, $pwd, $dbname, $dbprefix = "x_")
    {
        $this->dbHost = $host;
        $this->dbUser = $username;
        $this->dbPwd = $pwd;
        $this->dbName = $dbname;
        $this->dbPrefix = $dbprefix;
        $this->result["me"] = 0;
    }

    function SelectDB($dbname)
    {
        mysql_select_db($dbname);
    }

    //设置SQL里的参数
    function SetParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    //连接数据库
    function Open($pconnect = FALSE)
    {
        global $dsql;
        //连接数据库
        if ($dsql && !$dsql->isClose && $dsql->isInit) {
            $this->linkID = $dsql->linkID;
        } else {
            $i = 0;
            while (!$this->linkID) {
                if ($i > 100) break;

                if (!$pconnect) {
                    $this->linkID = @mysql_connect($this->dbHost, $this->dbUser, $this->dbPwd);
                } else {
                    $this->linkID = @mysql_pconnect($this->dbHost, $this->dbUser, $this->dbPwd);
                }
                $i++;
            }

            //复制一个对象副本
            CopySQLPoint($this);
        }

        //处理错误，成功连接则选择数据库
        if (!$this->linkID) {
            $this->DisplayError("DwtCms错误警告：<font color='red'>连接数据库失败，可能数据库密码不对或数据库服务器出错！</font>");
            exit();
        }
        $this->isInit = TRUE;
        @mysql_select_db($this->dbName);
        $mysqlver = explode('.', $this->GetVersion());
        $mysqlver = $mysqlver[0] . '.' . $mysqlver[1];

        if ($mysqlver > 4.0) {
            @mysql_query("SET NAMES '" . $GLOBALS['cfg_db_language'] . "', character_set_client=binary, sql_mode='', interactive_timeout=3600 ;", $this->linkID);
        }

        return TRUE;
    }

    //为了防止采集等需要较长运行时间的程序超时，在运行这类程序时设置系统等待和交互时间
    function SetLongLink()
    {
        @mysql_query("SET interactive_timeout=3600, wait_timeout=3600 ;", $this->linkID);
    }

    //获得错误描述
    function GetError()
    {
        $str = mysql_error();
        return $str;
    }

    //关闭数据库
    //mysql能自动管理非持久连接的连接池
    //实际上关闭并无意义并且容易出错，所以取消这函数
    function Close($isok = FALSE)
    {
        $this->FreeResultAll();
        if ($isok) {
            @mysql_close($this->linkID);
            $this->isClose = TRUE;
            $GLOBALS['dsql'] = NULL;
        }
    }

    //定期清理死连接
    function ClearErrLink()
    {
    }

    //关闭指定的数据库连接
    function CloseLink($dblink)
    {
        @mysql_close($dblink);
    }

    function Esc($_str)
    {
        if (version_compare(phpversion(), '4.3.0', '>=')) {
            return @mysql_real_escape_string($_str);
        } else {
            return @mysql_escape_string($_str);
        }
    }

    //执行一个不返回结果的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery($sql = '')
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }
        if (!empty($sql)) {
            $this->SetQuery($sql);
        } else {
            return FALSE;
        }
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $key => $value) {
                $this->queryString = str_replace("@" . $key, "'$value'", $this->queryString);
            }
        }
        //SQL语句安全检查
        if ($this->safeCheck) CheckSql($this->queryString, 'update');
        $t1 = ExecTime();
        $rs = mysql_query($this->queryString, $this->linkID);

        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr />\r\n";
        }
        return $rs;

        /*141102如果SQL语句有错误 则要保存错误
        $result=mysql_query($this->queryString,$this->linkID);
        if( $result===false)
        {
            $this->DisplayError(mysql_error()." <br />Error sql: <font color='red'>".$this->queryString."</font>");
        }
        return  $result;*/
    }


    //执行一个返回影响记录条数的SQL语句，如update,delete,insert等
    function ExecuteNoneQuery2($sql = '')
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }

        if (!empty($sql)) {
            $this->SetQuery($sql);
        }
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $key => $value) {
                $this->queryString = str_replace("@" . $key, "'$value'", $this->queryString);
            }
        }
        $t1 = ExecTime();
        mysql_query($this->queryString, $this->linkID);

        //查询性能测试
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
            //echo $this->queryString."--{$queryTime}<hr />\r\n";
        }

        return mysql_affected_rows($this->linkID);
    }

    function ExecNoneQuery($sql = '')
    {
        return $this->ExecuteNoneQuery($sql);
    }

    function GetFetchRow($id = 'me')
    {
        return @mysql_fetch_row($this->result[$id]);
    }

    function GetAffectedRows()
    {
        return mysql_affected_rows($this->linkID);
    }

    //执行一个带返回结果的SQL语句，如SELECT，SHOW等
    function Execute($id = "me", $sql = '')
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }
        if (!empty($sql)) {
            $this->SetQuery($sql);
        }

        //SQL语句安全检查
        if ($this->safeCheck) {
            CheckSql($this->queryString);
        }

        $t1 = ExecTime();

        $this->result[$id] = mysql_query($this->queryString, $this->linkID);
        if ($this->recordLog) {
            $queryTime = ExecTime() - $t1;
            $this->RecordLog($queryTime);
        }

        if ($this->result[$id] === FALSE) {
            //修复 显示 SQL错误
            $this->DisplayError(mysql_error() . " <br />Error sql: <font color='red'>" . $this->queryString . "</font>");
        }
    }

    function Query($id = "me", $sql = '')
    {
        $this->Execute($id, $sql);
    }

    //执行一个SQL语句,返回前一条记录或仅返回一条记录
    function GetOne($sql = '', $acctype = MYSQL_ASSOC)
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }
        if (!empty($sql)) {
            if (!preg_match("/LIMIT/i", $sql)) $this->SetQuery(preg_replace("/[,;]$/i", '', trim($sql)) . " LIMIT 0,1;");
            else $this->SetQuery($sql);
        }
        $this->Execute("one");
        $arr = $this->GetArray("one", $acctype);
        if (!is_array($arr)) {
            return '';
        } else {
            @mysql_free_result($this->result["one"]);
            return ($arr);
        }
    }

    //执行一个不与任何表名有关的SQL语句,Create等
    function ExecuteSafeQuery($sql, $id = "me")
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }
        $this->result[$id] = @mysql_query($sql, $this->linkID);
    }

    //返回当前的一条记录并把游标移向下一记录
    // MYSQL_ASSOC、MYSQL_NUM、MYSQL_BOTH
    function GetArray($id = "me", $acctype = MYSQL_ASSOC)
    {

        //dump($this->result[$id]);
        if ($this->result[$id] == 0) {
            return FALSE;
        } else {
            return mysql_fetch_array($this->result[$id], $acctype);
        }
    }

    function GetObject($id = "me")
    {
        if ($this->result[$id] == 0) {
            return FALSE;
        } else {
            return mysql_fetch_object($this->result[$id]);
        }
    }

    // 检测是否存在某数据表
    function IsTable($tbname)
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        $prefix = "#@__";
        $tbname = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tbname);
        if (mysql_num_rows(@mysql_query("SHOW TABLES LIKE '" . $tbname . "'", $this->linkID))) {
            return TRUE;
        }
        return FALSE;
    }

    //获得MySql的版本号
    function GetVersion($isformat = TRUE)
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }

        $rs = @mysql_query("SELECT VERSION();", $this->linkID);
        $row = @mysql_fetch_array($rs);
        $mysql_version = $row[0];
        @mysql_free_result($rs);
        if ($isformat) {
            $mysql_versions = explode(".", trim($mysql_version));
            $mysql_version = number_format($mysql_versions[0] . "." . $mysql_versions[1], 2);
        }
        return $mysql_version;
    }

    //获取特定表的信息
    function GetTableFields($tbname, $id = "me")
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        $this->result[$id] = mysql_list_fields($this->dbName, $tbname, $this->linkID);
    }

    //获取字段详细信息
    function GetFieldObject($id = "me")
    {
        return mysql_fetch_field($this->result[$id]);
    }

    //获得查询的总记录数
    function GetTotalRow($id = "me")
    {
        if ($this->result[$id] == 0) {
            return -1;
        } else {
            return mysql_num_rows($this->result[$id]);
        }
    }

    //获取上一步INSERT操作产生的ID
    function GetLastID()
    {
        //如果 AUTO_INCREMENT 的列的类型是 BIGINT，则 mysql_insert_id() 返回的值将不正确。
        //可以在 SQL 查询中用 MySQL 内部的 SQL 函数 LAST_INSERT_ID() 来替代。
        //$rs = mysql_query("Select LAST_INSERT_ID() as lid",$this->linkID);
        //$row = mysql_fetch_array($rs);
        //return $row["lid"];
        return mysql_insert_id($this->linkID);
    }

    //释放记录集占用的资源
    function FreeResult($id = "me")
    {
        @mysql_free_result($this->result[$id]);
    }

    function FreeResultAll()
    {
        if (!is_array($this->result)) {
            return '';
        }
        foreach ($this->result as $kk => $vv) {
            if ($vv) {
                @mysql_free_result($vv);
            }
        }
    }

    //设置SQL语句，会自动把SQL语句里的#@__替换为$this->dbPrefix(在配置文件中为$cfg_dbprefix)
    function SetQuery($sql)
    {
        $prefix = "#@__";
        $sql = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $sql);
        global $ROLE_WHERE_SQL;
        //dump($sql);
        //dump($ROLE_WHERE_SQL);
        if (trim($ROLE_WHERE_SQL) != "") $sql = $this->setRoleSql($sql);//如果有权限附加语句,则附加
        //dump($sql);
        $this->queryString = $sql;
    }


    /*
     * 判断是否有权限限制，附加上相应的SQL语句
     * $sql 替换了前辍后的SQL语句
    */
    function setRoleSql($sql)
    {
        //dump($sql);
        global $ROLE_WHERE_SQL, $ROLE_DATABASE;    //部门限制使用的查询数据,返回SQL语句,在列表页面直接使用
        //----获取当前数据表名称
        $isskd = strpos($sql, "noroleordernumb");//判断 如果是获取订单号  则不加权限  这个语句来自 order.helper.php
        if (!($isskd === false)) return $sql;


        //$sql  这里随后 要做一下,检查传入的字段,如果没有表前辍,就都要加上


        preg_match("/from\s+(.*?)\s* +/i", $sql, $matchs);//获取出表名
        if (count($matchs) > 1) {


            $databaseName = str_ireplace("from", "", $matchs[0]);
            $databaseName = trim(str_ireplace("`", "", $databaseName));
            //--------获取当前数据表名称
            $role_databasename = $GLOBALS['cfg_dbprefix'] . $ROLE_DATABASE;//获取要检查权限的数据表名称


            // $sql_field_array=Get_parse_sql_field($sql);

            //dump($sql);
            //---------------------------------------在次检查错误,将常见的通用字段没有表前辍的字段,加上前辍
            $tchar_array = array('id', "isdel", "clientid");//常见的通用字段名称,这些字段名称 要在SQL语句中判断是否有前辍表名称,如果没有则加上
            for ($dwtsql9999 = 0; $dwtsql9999 < count($tchar_array); $dwtsql9999++) {
                preg_match('/\.' . $tchar_array[$dwtsql9999] . '/', $sql, $matchs1111);//判断一下是否已经包含了表名,如果带有.包含了表名,则不加了
                if (count($matchs1111) == 0) {
                    $sql = preg_replace("/ {$tchar_array[$dwtsql9999]}/", " {$databaseName}.{$tchar_array[$dwtsql9999]}", $sql);//替换WHERE中的字段,和SELECT中第一次出现的字段
                    $sql = preg_replace("/,{$tchar_array[$dwtsql9999]}/", ",{$databaseName}.{$tchar_array[$dwtsql9999]}", $sql);//替换SELECT中的字段,逗号后出现 的
                }
            }
            //---------------------------------------在次检查错误,将常见的通用字段没有表前辍的字段,加上前辍

            //dump($sql);

            $role_leftjoin_sql = "";
            $role_where_sql = "";
            if (stripos($ROLE_WHERE_SQL, '|') !== false)//如果权限语句中有|则代码有LEFT JOIN语句
            {
                $str_array = explode("|", $ROLE_WHERE_SQL);
                $role_leftjoin_sql = $str_array[0];
                $role_where_sql = $str_array[1];
            } else {
                $role_where_sql = $ROLE_WHERE_SQL;
            }
            //$role_where_sql = $ROLE_WHERE_SQL;
            $role_where_sql = " AND (1=1   {$role_where_sql})";


            //dump($databaseName);
            //dump($ROLE_WHERE_SQL);
            //如果两者相同，则将权限相关SQL加入当前的语句
            if ($databaseName == $role_databasename) {
                //-----查找where语句
                //?????????此处未考虑,一个SQL中,嵌套查询(多个WHERe的情况,160311?????????????)
                if (stripos($sql, 'where') !== false) {
                    //如果有WHERE语句,直接替换 where

                    //170525增加LEFT JOIN 语句优化


                    $targetStr = " {$role_leftjoin_sql} where 1=1 {$role_where_sql} AND ";
                    $sql = preg_replace('/where/', $targetStr, $sql, 1);//只替换第一次出现的WHERE160408
                    $sql = preg_replace('/WHERE/', $targetStr, $sql, 1);//160607优化  增加对大写WHERE的查找

                    //dump("512:".$sql);
                    //$sql = str_ireplace("where", " where 1=1 " . $ROLE_WHERE_SQL . " and ", $sql);
                } else {
                    //如果没有where
                    if (stripos($sql, 'order') !== false) {
                        //如果有order,在order前附加
                        $sql = str_ireplace("order", " {$role_leftjoin_sql} WHERE 1=1 {$role_where_sql} ORDER ", $sql);
                    } elseif (stripos($sql, 'limit') !== false) {
                        //如果没有order 有limit,则在limit前附加
                        $sql = str_ireplace("limit", " {$role_leftjoin_sql} WHERE 1=1 {$role_where_sql} LIMIT ", $sql);
                    } elseif (stripos($sql, 'group') !== false) {
                        //如果没有order 没有limit 有 group ,则在group前附加   160807增加
                        $sql = str_ireplace("group", " {$role_leftjoin_sql} WHERE 1=1 {$role_where_sql} GROUP ", $sql);
                    } else {
                        //如果没有order也没有limit 则直接在最后附加
                        $sql = $sql . " {$role_leftjoin_sql} WHERE 1=1 {$role_where_sql}";
                    }
                }
            }
        }
        //$ROLE_WHERE_SQL=$ROLE_DATABASE="";    //161006增加 使用完后 清空，以免引起权限判断错误。
        //161016注释掉，起用部门数据权限后，这个暂时没有问题了，但这里是个BUG，后期看怎么初始化这个
        //这里如果不注释掉  会引起积分明细中的数据权限显示错误
        //         //这里如果注释掉，文档管理和订单管理的栏目不会显示到权限选择 菜单中
        //161017在role.class.php中加了初始化，待看效果
         // dump("dwt.sql.538--" . $sql);
        return $sql;
    }

    function SetSql($sql)
    {
        $this->SetQuery($sql);
    }

    function RecordLog($runtime = 0)
    {
        $RecordLogFile = dirname(__FILE__) . '/../data/mysqli_record_log.inc';
        $url = $this->GetCurUrl();
        $savemsg = <<<EOT

            ------------------------------------------
            SQL:{$this->queryString}
            Page:$url
            Runtime:$runtime
EOT;
        $fp = @fopen($RecordLogFile, 'a');
        @fwrite($fp, $savemsg);
        @fclose($fp);
    }

    //显示数据链接错误信息
    function DisplayError($msg)
    {
        $errorTrackFile = dirname(__FILE__) . '/../data/mysql_error_trace.inc';
        if (file_exists(dirname(__FILE__) . '/../data/mysql_error_trace.php')) {
            @unlink(dirname(__FILE__) . '/../data/mysql_error_trace.php');
        }
        $emsg = '';
        $emsg .= "<div><h3> Error Warning!</h3>\r\n";
        $emsg .= "<div style='line-helght:160%;font-size:14px;color:green'>\r\n";
        $emsg .= "<div style='color:blue'><br />Error page: <font color='red'>" . $this->GetCurUrl() . "</font></div>\r\n";
        $emsg .= "<div>Error infos: {$msg}</div>\r\n";
        $emsg .= "<br /></div></div>\r\n";

        if (DEBUG_LEVEL === TRUE) {
            //161107增加 如果调试模式 则增加
            echo $emsg;
        }
        $savemsg = 'Page: ' . $this->GetCurUrl() . "\r\nError: " . $msg . "\r\nDate: " . date("Y-m-d H:i:s", time());
        //保存MySql错误日志
        $fp = @fopen($errorTrackFile, 'a');
        @fwrite($fp, '<' . '?php  exit();' . "\r\n/*\r\n{$savemsg}\r\n*/\r\n?" . ">\r\n");
        @fclose($fp);
    }

    //获得当前的脚本网址
    function GetCurUrl()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        } else {
            $scriptName = $_SERVER["PHP_SELF"];
            if (empty($_SERVER["QUERY_STRING"])) {
                $nowurl = $scriptName;
            } else {
                $nowurl = $scriptName . "?" . $_SERVER["QUERY_STRING"];
            }
        }
        return $nowurl;
    }


    /**160303 事务化批量处理,不返回结果
     *
     * @param $query array SQL语句的数组
     */
    function ExecuteNoneCommit($query)
    {
        global $dsql;
        if (!$dsql->isInit) {
            $this->Init($this->pconnect);
        }
        if ($dsql->isClose) {
            $this->Open(FALSE);
            $dsql->isClose = FALSE;
        }


        mysql_query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
        mysql_query("BEGIN");//开始事务定义
        for ($i1 = 0; $i1 < (count($query)); $i1++) {
            if (!empty($query[$i1])) {
                $this->SetQuery($query[$i1]);
            } else {
                return FALSE;
            }
            //dump($query[$i1]);
            if (is_array($this->parameters)) {
                foreach ($this->parameters as $key => $value) {
                    $this->queryString = str_replace("@" . $key, "'$value'", $this->queryString);
                }
            }

            //dump($this->queryString);
            //SQL语句安全检查
            if ($this->safeCheck) CheckSql($this->queryString, 'update');
            if (!mysql_query($this->queryString, $this->linkID)) {
                mysql_query("ROLLBACK");//判断当执行失败时回滚
            }
        }
        mysql_query("COMMIT");//执行事务
    }

}


//复制一个对象副本
function CopySQLPoint(&$ndsql)
{
    $GLOBALS['dsql'] = $ndsql;
}

//SQL语句过滤程序，由80sec提供，这里作了适当的修改
if (!function_exists('CheckSql')) {
    function CheckSql($db_string, $querytype = 'select')
    {
        global $cfg_cookie_encode;
        $clean = '';
        $error = '';
        $old_pos = 0;
        $pos = -1;
        $log_file = DWTINC . '/../data/' . md5($cfg_cookie_encode) . '_safe.txt';
        $userIP = GetIP();
        $getUrl = GetCurUrl();

        //如果是普通查询语句，直接过滤一些特殊语法
        if ($querytype == 'select') {
            //$notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";
            $notallow1 = "[^0-9a-z@\._-]{1,}(sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";  //160601前台表中要用union所以禁用了union检查

            //$notallow2 = "--|/\*";
            if (preg_match("/" . $notallow1 . "/i", $db_string)) {
                fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||SelectBreak\r\n");
                exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
            }
        }

        //完整的SQL检查
        while (TRUE) {
            $pos = strpos($db_string, '\'', $pos + 1);
            if ($pos === FALSE) {
                break;
            }
            $clean .= substr($db_string, $old_pos, $pos - $old_pos);
            while (TRUE) {
                $pos1 = strpos($db_string, '\'', $pos + 1);
                $pos2 = strpos($db_string, '\\', $pos + 1);
                if ($pos1 === FALSE) {
                    break;
                } elseif ($pos2 == FALSE || $pos2 > $pos1) {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr($db_string, $old_pos);
        $clean = trim(strtolower(preg_replace(array('~\s+~s'), array(' '), $clean)));

        if (strpos($clean, '@') !== FALSE OR strpos($clean, 'char(') !== FALSE OR strpos($clean, '"') !== FALSE
            OR strpos($clean, '$s$$s$') !== FALSE
        ) {
            $fail = TRUE;
            if (preg_match("#^create table#i", $clean)) $fail = FALSE;
            $error = "unusual character";
        }

        //160601前台表中要用union所以禁用了union检查
        //老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
        /*if (strpos($clean, 'union') !== FALSE && preg_match('~(^|[^a-z])union($|[^[a-z])~is', $clean) != 0) {
            $fail = TRUE;
            $error = "union detect";
        } else
        170105修改SQL有注释会引起错误//发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
        if (strpos($clean, '/*') > 2 || strpos($clean, '--') !== FALSE || strpos($clean, '#') !== FALSE) {
            $fail = TRUE;
            $error = "comment detect";
        } //这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
        else*/
        if (strpos($clean, 'sleep') !== FALSE && preg_match('~(^|[^a-z])sleep($|[^[a-z])~is', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'benchmark') !== FALSE && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~is', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'load_file') !== FALSE && preg_match('~(^|[^a-z])load_file($|[^[a-z])~is', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        } elseif (strpos($clean, 'into outfile') !== FALSE && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~is', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        }

        //老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
        //elseif (preg_match('~\([^)]*?select~is', $clean) != 0)
        // {
        //     $fail = TRUE;
        //     $error="sub select detect";
        // }
        if (!empty($fail)) {
            fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||$error\r\n");
            exit("<font size='5' color='red'>Safe Alert: Request Error step 2!</font>");
        } else {
            return $db_string;
        }
    }
}