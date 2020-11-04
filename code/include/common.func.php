<?php
/**
 * 系统核心函数存放文件
 * @version        $Id: common.func.php 4 16:39 6日
 * @package
 * @copyright
 * @license
 * @link
 */
if(!defined('DWTINC')) exit('dwtx');

/**
 *  载入小助手,系统默认载入小助手
 *  在/data/helper.inc.php中进行默认小助手初始化的设置
 *  使用示例:
 *      在开发中,首先需要创建一个小助手函数,目录在\include\helpers中
 *  例如,我们创建一个示例为test.helper.php,文件基本内容如下:
 *  <code>
 *  if ( ! function_exists('HelloDwt'))
 *  {
 *      function HelloDwt()
 *      {
 *          echo "Hello! Dwt...";
 *      }
 *  }
 *  </code>
 *  则我们在开发中使用这个小助手的时候直接使用函数helper('test');初始化它
 *  然后在文件中就可以直接使用:HelloDwt();来进行调用.
 *
 * @access    public
 * @param     mix   $helpers  小助手名称,可以是数组,可以是单个字符串
 * @return    void
 */
$_helpers = array();
function helper($helpers)
{
    //如果是数组,则进行递归操作
    if (is_array($helpers))
    {
        foreach($helpers as $dwt)
        {
            helper($dwt);
        }
        return;
    }

    if (isset($_helpers[$helpers]))
    {
        continue;
    }
    if (file_exists(DWTINC.'/helpers/'.$helpers.'.helper.php'))
    {
        include_once(DWTINC.'/helpers/'.$helpers.'.helper.php');
        $_helpers[$helpers] = TRUE;
    }
    // 无法载入小助手
    if ( ! isset($_helpers[$helpers]))
    {
        exit('Unable to load the requested file: helpers/'.$helpers.'.helper.php');
    }
}
//去除html中不规则内容字符 DEDE2015年6月18日版本更新
function dwt_htmlspecialchars($str) {
    global $cfg_soft_lang;
    if (version_compare(PHP_VERSION, '5.4.0', '<')) return htmlspecialchars($str);
    if ($cfg_soft_lang=='gb2312') return htmlspecialchars($str,ENT_COMPAT,'ISO-8859-1');
    else return htmlspecialchars($str);
}

/**
 *  控制器调用函数
 *
 * @access    public
 * @param     string  $ct    控制器
 * @param     string  $ac    操作事件
 * @param     string  $path  指定控制器所在目录
 * @return    string
 */
function RunApp($ct, $ac = '',$directory = '')
{

    $ct = preg_replace("/[^0-9a-z_]/i", '', $ct);
    $ac = preg_replace("/[^0-9a-z_]/i", '', $ac);
    $ac = empty ( $ac ) ? $ac = 'index' : $ac;
	if(!empty($directory)) $path = DEDECONTROL.'/'.$directory. '/' . $ct . '.php';
	else $path = DEDECONTROL . '/' . $ct . '.php';

	if (file_exists ( $path ))
	{
		require $path;
	} else {
		 if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Controller false!");
        }
        //生产环境中，找不到控制器的情况不需要记录日志
        else
        {
            header ( "location:/404.html" );
            die ();
        }
	}
	$action = 'ac_'.$ac;
    $loaderr = FALSE;
    $instance = new $ct ( );
    if (method_exists ( $instance, $action ) === TRUE)
    {
        $instance->$action();
        unset($instance);
    } else $loaderr = TRUE;

    if ($loaderr)
    {
        if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Method false!");
        }
        //生产环境中，找不到控制器的情况不需要记录日志
        else
        {
            header ( "location:/404.html" );
            die ();
        }
    }
}

/**
 *  载入小助手,这里用户可能载入用helps载入多个小助手
 *
 * @access    public
 * @param     string
 * @return    string
 */
function helpers($helpers)
{
    helper($helpers);
}

//兼容php4的file_put_contents
if(!function_exists('file_put_contents'))
{
    function file_put_contents($n, $d)
    {
        $f=@fopen($n, "w");
        if (!$f)
        {
            return FALSE;
        }
        else
        {
            fwrite($f, $d);
            fclose($f);
            return TRUE;
        }
    }
}


$arrs1 = array(0x63,0x66,0x67,0x5f,0x70,0x6f,0x77,0x65,0x72,0x62,0x79);
$arrs2 = array(0x20,0x3c,0x61,0x20,0x68,0x72,0x65,0x66,0x3d,0x68,0x74,0x74,0x70,0x3a,0x2f,0x2f,
0x77,0x77,0x77,0x2e,0x64,0x65,0x64,0x65,0x63,0x6d,0x73,0x2e,0x63,0x6f,0x6d,0x20,0x74,0x61,0x72,
0x67,0x65,0x74,0x3d,0x27,0x5f,0x62,0x6c,0x61,0x6e,0x6b,0x27,0x3e,0x50,0x6f,0x77,0x65,0x72,0x20,
0x62,0x79,0x20,0x44,0x65,0x64,0x65,0x43,0x6d,0x73,0x3c,0x2f,0x61,0x3e);


/**
 * 160114
 * 获取功能名称的主要文件名称 如员工管理 /emp/emp.php /emp/emp_add.php, 只得到emp
 * 用于用户首先打开列表员时将此emp做为cookie名称 保存,然后edit保存之后返回当前操作页
 * 如果不加此名称,则当多标签操作时cookie引用会混乱
 * ENV_GOBACK_URL
 *
 * @param $url
 */
function GetFunMainName($url){
    //例子"/emp/emp_add.php"
    $url_array=explode(".",$url);
    $url=$url_array[0];//取点之前的
    $url_array=explode("_",$url);
    $url=$url_array[0];//取_之前的
    $url_array=explode("/",$url);
    $url=$url_array[2];//取/之前的
    return $url;
}



/**
 *  短消息函数,可以在某个动作处理后友好的提示信息
 *
 * @param     string  $msg      消息提示信息
 * @param     string  $gourl    跳转地址
 * @param     int     $onlymsg  仅显示信息
 * @param     int     $limittime  限制时间
 * @param     int     $isWait  是否显示旋转的等待图片
 * @return    void
 */
function ShowMsg($msg, $gourl="", $onlymsg=0, $limittime=0,$isWait=0)
{

    //如果是在微信浏览器中，并且没有跳转页面。则调用微信提示,并关掉窗口
    if (IsWeixinBrowser()&& $gourl=="" ) {
            $msg11="<script>
                        document.addEventListener(\"WeixinJSBridgeReady\", function () {
                            alert(\"".$msg."\");
                            /*直接关闭*/
                            WeixinJSBridge.invoke(\"closeWindow\", {}, function (e) {
                            })
                        });
                    </script>";
        echo $msg11;
        return;
    }

//???此处样式的相对位置 引用  在登录后的提示页面有问题 登录时是根目录 151216
   // $htmlhead  = "<html>\r\n<head>\r\n<title>提示信息</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" />\r\n";
    //$htmlhead .= "<base target='_self'/>\r\n<style>div{line-height:160%;}</style></head>\r\n<body leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>".(isset($GLOBALS['ucsynlogin']) ? $GLOBALS['ucsynlogin'] : '')."\r\n<center>\r\n<script>\r\n";
    //$htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";
    $cfg_install_path="";
    $htmlhead  = "<!DOCTYPE html>\r\n
                    <html>\r\n
                    <head>\r\n
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\r\n
                    <title>提示信息</title>\r\n
                    <link href='".$cfg_install_path."/ui/css/bootstrap.min.css' rel='stylesheet'>\r\n
                    <link href='".$cfg_install_path."/ui/css/font-awesome.min.css' rel='stylesheet'>\r\n
                    <link href='".$cfg_install_path."/ui/css/animate.min.css' rel='stylesheet'>\r\n
                    <link href='".$cfg_install_path."/ui/css/style.min.css' rel='stylesheet'>\r\n
                    <base target='_self'/>\r\n
                    </head>\r\n";
    $htmlhead .= "<body class='white-bg'  >\r\n<script>\r\n";
    $htmlfoot  = "</script>\r\n</body>\r\n</html>\r\n";

    $litime = ($limittime==0 ? 1000 : $limittime);
    $func = '';

    if($gourl=='-1')
    {
        if($limittime==0) $litime = 5000;
        $gourl = "javascript:window.location.href=document.referrer;";//171030不用-1了,用这个页面可以强制刷新
    }
    if($gourl=='-2')
    {
        if($limittime==0) $litime = 5000;
        $gourl = "javascript:history.go(-2);";
    }

    if($gourl=='' || $onlymsg==1)
    {
        $msg = "<script>alert(\"".str_replace("\"","“",$msg)."\");</script>";//160415这里直接使用原生弹窗，因为无法加载JS特效，无法使用layer
    }
    else
    {
        //当网址为:close::objname 时, 关闭父框架的id=objname元素
        if(preg_match('/close::/',$gourl))
        {
            $tgobj = trim(preg_replace('/close::/', '', $gourl));
            $gourl = 'javascript:;';
            $func .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
        }

        $func .= "      var pgo=0;
                  function JumpUrl(){
                    if(pgo==0){ location='$gourl'; pgo=1; }
                  }\r\n";
        $rmsg = $func;

        $rmsg .="document.write(\"<div class='wrapper wrapper-content animated fadeInRight'>\");";
        $rmsg .="document.write(\"<div style='width: 33%; min-width: 200px;text-align:center; margin:0 auto;text-align: left'>\");";
        $rmsg .="document.write(\" <div class='panel panel-info'>\");";
        $rmsg .="document.write(\"<div class='panel-heading'>\");";
        $rmsg .="document.write(\"<i class='fa fa-info-circle'></i> <b>提示信息</b>\");";
        $rmsg .="document.write(\" </div>\");";
        $rmsg .="document.write(\"<div class='panel-body'> <b>\");";

        //$rmsg .= "document.write(\"<br /><div style='width:450px;padding:0px;border:1px solid #DADADA;'>";
        //$rmsg .= "<div style='padding:6px;font-size:12px;border-bottom:1px solid #DADADA;background:#DBEEBD url(../images/wbg.gif)';'><b>提示信息！</b></div>\");\r\n";
        //$rmsg .= "document.write(\"<div style='height:130px;font-size:10pt;background:#ffffff'><br />\");\r\n";
        $rmsg .= "document.write(\"".str_replace("\"","“",$msg)."</b>\");\r\n";
        $rmsg .= "document.write(\"";
         if($isWait==1)$rmsg .= "<img src='../images/loading.gif'>";
        if($onlymsg==0)
        {
            if( $gourl != 'javascript:;' && $gourl != '')
            {
                $rmsg .= "<br /><a href='{$gourl}'>如果你的浏览器没反应，请点击这里...</a>";
                $rmsg .= "</div>        </div>    </div></div>\");\r\n";
                $rmsg .= "setTimeout('JumpUrl()',$litime);";
            }
            else
            {
                $rmsg .= "</div>        </div>    </div></div>\");\r\n";
            }
        }
        else
        {
            $rmsg .= "</div>        </div>    </div></div>\");\r\n";
        }





        $msg  = $htmlhead.$rmsg.$htmlfoot;
    }
    echo $msg;
}

/**
 *  获取验证码的session值
 *
 * @return    string
 */
function GetCkVdValue()
{
	@session_id($_COOKIE['PHPSESSID']);
    @session_start();
    return isset($_SESSION['securimage_code_value']) ? $_SESSION['securimage_code_value'] : '';
}

/**
 *  PHP某些版本有Bug，不能在同一作用域中同时读session并改注销它，因此调用后需执行本函数
 *
 * @return    void
 */
function ResetVdValue()
{
    @session_start();
    $_SESSION['securimage_code_value'] = '';
}

// 自定义函数接口
// 这里主要兼容早期的用户扩展,v5.7之后我们建议使用小助手helper进行扩展
if( file_exists(DWTINC.'/extend.func.php') )
{
    require_once(DWTINC.'/extend.func.php');
}



/**
 * 更新会员积分
 *
 * @param $clientid
 * @param $varname
 */
function UpdateScores($clientid, $varname)
{
    global $dsql;
    if ($clientid > 0) {
        $query = "SELECT info,value FROM `#@__sys_sysOtherConfig`    WHERE varname='$varname'";
        $row = $dsql->GetOne($query);
        if ($row) {
            $scores = $row["value"];//获取要操作的积分
            $info = $row["info"];//积分的说明信息
            $iszj = 0;
            if ($scores < 0) {
                //如果是扣分的操作 则判断用户的积分是否够
                $iszj = 1;
                $query = "SELECT scores FROM `#@__client`    WHERE  id='$clientid' ";
                $row1 = $dsql->GetOne($query);
                if(!(($row1["scores"]+$scores)>=0)){
                    return false;
                }//如果积分不够减 则返回错误
            }
            //更新会员积分
            $dsql->ExecuteNoneQuery("Update `#@__client` set `scores`=`scores`+{$scores} where id='$clientid' ");
            $updatedate = time();
            //插入积分明细
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__scores_log` ( `clientid`, `scores`, `iszj`, `type`, `updatedate`) VALUES ( '$clientid', '$scores','$iszj','$info', '$updatedate');");
            return true;
        }
    }
}







