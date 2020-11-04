<?php
/**
 * @version        $Id: common.inc.php 3 17:44 2010-11-23 tianya $
 * @package
 * @copyright
 * @license
 * @link
 */

// 报错级别设定,一般在开发环境中用E_ALL,这样能够看到所有错误提示
// 系统正常运行后,直接设定为E_ALL || ~E_NOTICE,取消错误显示
error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE);//只显示 错误 不显示警告
define('DWTINC', str_replace("\\", '/', dirname(__FILE__)));
if (!defined("DWTPATH")) define('DWTPATH', str_replace("\\", '/', substr(DWTINC, 0, -8)));   //此句不能删除 在config.php里有定义 ,但login.php不能引起config.PHP所以这里要重新的定义一下140919
define('DEDEDATA', DWTPATH . '/data');
define('DEDEINC_APP', DWTPATH . '/lyapp/include');//170101增加定义APP的INCLUDE目录
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    set_magic_quotes_runtime(0);
}

//echo (DWTPATH);



global $DEP_TOP_ID;//这个是171111加的,在上传文件夹,获取微信等配置参数时,使用
$DEP_TOP_ID=17;

global $DEP_WEBSITE_NAME;//这个是171111加的,站点名称
$DEP_WEBSITE_NAME="http://xxxx.com";//


//是否启用mb_substr替换cn_substr来提高效率
$cfg_is_mb = $cfg_is_iconv = FALSE;
if (function_exists('mb_substr')) $cfg_is_mb = TRUE;
if (function_exists('iconv_substr')) $cfg_is_iconv = TRUE;

function _RunMagicQuotes(&$svar)
{
    if (!get_magic_quotes_gpc()) {
        if (is_array($svar)) {
            foreach ($svar as $_k => $_v) $svar[$_k] = _RunMagicQuotes($_v);
        } else {
            //if( strlen($svar)>0 && preg_match('#^(cfg_|GLOBALS|_GET|_POST|_COOKIE)#',$svar) )
            //161031修复 漏洞 dedecms SESSION变量覆盖导致SQL注入common.inc.php
            if (strlen($svar) > 0 && preg_match('#^(cfg_|GLOBALS|_GET|_POST|_COOKIE|_SESSION)#', $svar)) {
                exit('Request var not allow!');
            }
            $svar = addslashes($svar);
        }
    }
    return $svar;
}

if (!defined('DEDEREQUEST')) {
    //检查和注册外部提交的变量   (2011.8.10 修改登录时相关过滤)
    function CheckRequest(&$val)
    {
        if (is_array($val)) {
            foreach ($val as $_k => $_v) {
                if ($_k == 'nvarname') continue;  //140204加
                CheckRequest($_k);
                CheckRequest($val[$_k]);
            }
        } else {
            //140204注释掉,要搜索CFG开头的系统参数配置
            //161031取消注释  为了安全性  配置搜索随后再说
            if (strlen($val) > 0 && preg_match('#^(cfg_|GLOBALS|_GET|_POST|_COOKIE)#', $val)) {
                exit('Request var not allow!');
            }
        }
    }

    CheckRequest($_REQUEST);
    CheckRequest($_COOKIE);
    foreach (Array('_GET', '_POST', '_COOKIE') as $_request) {
        foreach ($$_request as $_k => $_v) {
            //echo($_v);
            if(!is_array($_v))$_v=trim($_v);//170601清除参数中的前事空格,如果传递过来的是数组,则不清除,如果清除数组 的空格  会报错
            if ($_k == 'nvarname') ${$_k} = $_v;//增加安全时增加161031
            else ${$_k} = _RunMagicQuotes($_v);
        }
    }
}


//系统相关变量检测
//if(!isset($needFilter))
//{
//    $needFilter = false;//141008  未搜索到使用地方 
//}

//由于register_globals设置控制PHP变量访问范围,如果开启会引起不必要的安全问题,所以这里对其进行了强制关闭.使用的DEDEAMPZ的套件是1打开的141008
//这个变量在原DEDE的 DEDE/INC/inc_list_functions.php里使用 inc_list_functions.php中的功能现已经移到channelunit.helper.php中
//$registerGlobals = @ini_get("register_globals");//141008  未搜索到使用地方 
//$isUrlOpen = @ini_get("allow_url_fopen");//141008多处赋值此变量  但只在customfields11111.func.php中使用了
$isSafeMode = @ini_get("safe_mode"); //141008开启之后，主要会对系统操作、文件、权限设置等方法产生影响.这里得到的是空值
if (preg_match('/windows/i', @getenv('OS'))) {
    $isSafeMode = false;
}

//Session保存路径
$sessSavePath = DEDEDATA . "/sessions/";
if (is_writeable($sessSavePath) && is_readable($sessSavePath)) {
    session_save_path($sessSavePath);
}

//系统配置参数,这里面有时区设置
require_once(DEDEDATA . "/config.cache.inc.php");


//转换上传的文件相关的变量及安全处理、并引用前台通用的上传函数
//151111做设备知识库时引入此文件,但检查了uploadsafe.inc.php文件,但好像不起作用
if ($_FILES) {
    require_once(DWTINC . '/uploadsafe.inc.php');
}

//数据库配置文件
require_once(DEDEDATA . '/common.inc.php');

//载入系统验证安全配置
if (file_exists(DEDEDATA . '/safe/inc_safe_config.php')) {
    require_once(DEDEDATA . '/safe/inc_safe_config.php');
    if (!empty($safe_faqs)) $safefaqs = unserialize($safe_faqs);
}

//Session跨域设置
if (!empty($cfg_domain_cookie)) {
    @session_set_cookie_params(0, '/', $cfg_domain_cookie);
}

//php5.1版本以上时区设置
//由于这个函数对于是php5.1以下版本并无意义，因此实际上的时间调用，应该用MyDate函数调用
if (PHP_VERSION > '5.1') {
    $time51 = $cfg_cli_time * -1;
    @date_default_timezone_set('Etc/GMT' . $time51);
}


//系统的一些常用配置信息   调用方法 $GLOBALS['cfg_install_path']


//$cfg_install_path  程序安装目录   在程序配置中保存  data/config.cache.inc.php
//引用方法 $GLOBALS['cfg_install_path'];

$cfg_install_path = "";
//站点根目录   要用120603
$cfg_basedir = preg_replace('#' . $cfg_install_path . '\/include$#i', '', DWTINC);
//echo ($cfg_basedir );  //程序实际安装路径I:/hc/code

//前台WEB模板的存放目录141015  这个要放入 其他文件中，只有文档管理用
//161217不使用此功能，直接在APP下调用
//$cfg_web_templets_dir = $cfg_basedir . $cfg_install_path . '/app/templets';
//前台app模板的存放目录160511增加  目前只有文档管理用 随后其他 的APP也要使用
//$cfg_app_templets_dir = $cfg_basedir . $cfg_install_path . '/app/templets';


$cfg_version = 'V3.0';
$cfg_soft_lang = 'utf8';


//新建目录的权限，如果你使用别的属性，本程不保证程序能顺利在Linux或Unix系统运行
if (isset($cfg_ftp_mkdir) && $cfg_ftp_mkdir == 'Y') {
    $cfg_dir_purview = '0755';
} else {
    $cfg_dir_purview = 0755;
}


if (!isset($cfg_NotPrintHead)) {
    header("Content-Type: text/html; charset={$cfg_soft_lang}");
}

//自动加载类库处理
function __autoload($classname)
{
    global $cfg_soft_lang;
    $classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
    if (class_exists($classname)) {
        return TRUE;
    }
    //dump(DWTMODEL . '/' . $classname);
    $classfile = $classname . '.php';
    $libclassfile = $classname . '.class.php';
    if (is_file(DWTINC . '/' . $libclassfile)) {
        require DWTINC . '/' . $libclassfile;
    } else if (is_file(DWTMODEL . '/' . $classfile)) {
        require DWTMODEL . '/' . $classfile;
    } else {
        if (DEBUG_LEVEL === TRUE) {
            echo '<pre>';
            echo $classname . '类找不到';
            echo '</pre>';
            exit ();
        } else {
            header("location:/404.html");
            die ();
        }
    }
}

//引入数据库类
require_once(DWTINC . '/dwtsql.class.php');  //本程序主数据库


//全局常用函数
require_once(DWTINC . '/common.func.php');

// 模块MVC框架需要的控制器和模型基类
require_once(DWTINC . '/control.class.php');
require_once(DWTINC . '/model.class.php');

//载入小助手配置,并对其进行默认初始化
if (file_exists(DEDEDATA . '/helper.inc.php')) {
    require_once(DEDEDATA . '/helper.inc.php');
    // 若没有载入配置,则初始化一个默认小助手配置
    if (!isset($cfg_helper_autoload)) {
        $cfg_helper_autoload = array('util', 'charset', 'string', 'time', 'cookie');
    }
    // 初始化小助手
    helper($cfg_helper_autoload);
}




//保存系统出错信息到文件160314   引文件 不能放置在config.php中 如果放置在config.php中,roleCheck.func.php中无法引用
function SaveErrorToFile($msg)
{
    $errorTrackFile = dirname(__FILE__) . '/../data/sys_error_trace.inc';
    if (file_exists(dirname(__FILE__) . '/../data/sys_error_trace.php')) {
        @unlink(dirname(__FILE__) . '/../data/sys_error_trace.php');
    }

    $savemsg = 'Page: ' . GetCurUrl() . "\r\nError: " . $msg . "\r\nDate: " . date("Y-m-d H:i:s", time());
    //保存MySql错误日志
    $fp = @fopen($errorTrackFile, 'a');
    @fwrite($fp, '<' . '?php  exit();' . "\r\n/*\r\n{$savemsg}\r\n*/\r\n?" . ">\r\n");
    @fclose($fp);
}





