<?php
/**
 * 配置文件
 *
 * @version        $Id: config.php 151009
 * @package
 * @copyright
 * @license
 * @link
 */
define('DWTPATH', str_replace("\\", '/', dirname(__FILE__)));//定义系统的目录为当前目录
require_once(DWTPATH . '/include/common.inc.php');
require_once(DWTINC . '/userlogin.class.php');
require_once('dwtkey.php');

header('Cache-Control:private');
$dsql->safeCheck = FALSE;
$dsql->SetLongLink();
 //获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项
$dwtNowUrl = $s_scriptName = '';
//$isUrlOpen = @ini_get('allow_url_fopen');//141008多处赋值此变量  但只在customfields11111.func.php中使用了
$dwtNowUrl = GetCurUrl();
$dwtNowUrls = explode('?', $dwtNowUrl);//操作的参数,用于日志记录


//150610添加
//global $GLOBAMOREDEP;
//160311将这两个改为大写,全局声明
global $CUSERLOGIN, $GLOBAMOREDEP;
$GLOBAMOREDEP = $dsql->IsTable("#@__emp_dep_plus");//判断多部门扩展表是否存在(是否包含分厂)
$CUSERLOGIN = new userLogin();

global $NOWLOGINUSERTOPDEPID;
$userid = $CUSERLOGIN->getUserId();//当前登录用户的ID
$NOWLOGINUSERTOPDEPID = GetEmpDepTopIdByUserId($userid);//当前用户 所属的顶级部门ID   //这个很少用,要在多部门同一数据库时启用171111
//dump($NOWLOGINUSERTOPDEPID);

//权限判断
require_once("include/role.class.php");
$roleCheck = new roleClass();
$roleCheck->RoleCheckToOpen();
$sysFunTitle = $roleCheck->funName;





//检验用户登录状态
//$GLOBALS['CUSERLOGIN'] = new userLogin();
//dump($GLOBALS['CUSERLOGIN']->getUserId());
if ($GLOBALS['CUSERLOGIN']->getUserId() == -1) {
    //ShowMsg("用户登录信息失效,请重新登录!",""); 此名不能用  被 header忽略了
    //如果直接打开网站主页 不提示用户 填写用户名和密码
    //如果是打开的其他网页 则要提示用户 填写 用户名和密码登录
    if ($dwtNowUrl == "/" || $dwtNowUrl == "/main.php") {
        $jumpurl = "$cfg_install_path/login.php?gotopage=" . urlencode($dwtNowUrl);
    } else {
        $jumpurl = "$cfg_install_path/login.php?msg=nologin&gotopage=" . urlencode($dwtNowUrl);
    }
    header("location:$jumpurl");
    exit();
}




$s_scriptName = $dwtNowUrl;
putHistory($s_scriptName);//160201添加 记录用户的最近访问的10个功能地址 在首页显示


//记录系统操作日志
if ($cfg_dwt_log == 'Y') {
    $s_nologfile = 'sys/log.php';
    /*	$s_needlogfile = 'sys_|file_';
        $s_scriptNames = explode('/', $s_scriptName);
        $s_scriptNames = $s_scriptNames[count($s_scriptNames) - 1];*/
    $s_scriptNames = isset($dwtNowUrls[0]) ? ltrim($dwtNowUrls[0], "/") : '';
    $s_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    $s_query = isset($dwtNowUrls[1]) ? $dwtNowUrls[1] : '';
    $s_userip = GetIP();
    $doClassFiles_array = explode('.', $s_scriptNames);//如果是 XXX.do.php xxx.class.php的页面  (这些页面不参与权限判断)
    //只判断有一个点的文件
    //if( $s_method=='POST' || (!preg_match("#".$s_nologfile."#i", $s_scriptNames) && $s_query!='') || preg_match("#".$s_needlogfile."#i",$s_scriptNames) )
    if (!preg_match("#" . $s_nologfile . "#i", $s_scriptNames) && (count($doClassFiles_array) == 2)) {

        $inquery = "INSERT INTO `#@__sys_log`(adminid,filename,method,query,cip,dtime)
				 VALUES ('" . $GLOBALS['CUSERLOGIN']->getUserId() . "','{$s_scriptNames}','{$s_method}','" . addslashes($s_query) . "','{$s_userip}','" . time() . "');";
        //dump($inquery);
        $dsql->ExecuteNoneQuery($inquery);
    }
}


/*记录用户最近访问的二级功能地址,保存到文件data\indexBody\history\history-用户id.txt。
 *最新的在最下面,将原来的相同的地址删除掉
 * 最多保存10个
 * //160201添加 记录用户的最近访问的10个功能地址 在首页显示
 *
 * $functionUrl 带目录的当前访问的二级地址
 */
function putHistory($functionUrl)
{
    $CUSERLOGIN = new userLogin();
    $oldct = "";
    //打开用户的文件,获取已经历史访问功能
    $myMenu = DEDEDATA . '/indexBody/history/history-' . $CUSERLOGIN->getUserId() . '.txt';
    $fp = fopen($myMenu, 'r');//如果不存在就创建
    if (filesize($myMenu) > 0) $oldct = trim(fread($fp, filesize($myMenu)));
    fclose($fp);


    $newct = "";
    if ($oldct != "") {
        $history_array = explode("|", $oldct);
        if (is_array($history_array)) {

            foreach ($history_array as $history) {
                if ($history != "" & $history != $functionUrl) {
                    $newct .= ($newct == "") ? "" : "|";
                    $newct .= "{$history}";
                }
            }
        }
    }
    //dump($newct);

    if ($newct != "") {
        $history_array = explode("|", $newct);
        if (count($history_array) > 9) $history_array = array_slice($history_array, 1, 9);//最多十个,多了之后后,从数组中取最后的九个
        $newct = implode("|", $history_array);
    }


    $newct .= ($newct == "") ? "" : "|";
    $newct .= "{$functionUrl}";

    //将新的功能ID写入文件
    $fp = fopen($myMenu, 'w');
    fwrite($fp, $newct);
    fclose($fp);
}



/**
 *  引入模板文件  这个很少用了,一般原DEDE是EDIT ADD页面用 ,现在改为直接在PHP页面引用 140821
 *
 * @access    public
 *
 * @param     string $filename 文件名称
 * @param     bool   $isabs    是否为管理目录
 *
 * @return    string
 */
function DwtInclude($filename, $isabs = FALSE)
{
    return $isabs ? $filename : DWTPATH . '/' . $filename;
}

helper('cache');

