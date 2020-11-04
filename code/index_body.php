<?php
/**
 * 管理后台首页主体
 *
 * @version        $Id: index_body.php 1 11:06 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require('config.php');
require(DWTINC . '/dwttag.class.php');
require_once("sys/sysFunction.class.php");
//$t1 = ExecTime();

//2获取其他连接


//默认主页
if (empty($dopost)) {


    //1\获取功能菜单
    $menuLink = "";
    $parents = $childs = array();
    $fun = new sys_function();
    if ($GLOBAMOREDEP) {
        $menuArray = $fun->getSysFunArray($GLOBALS['NOWLOGINUSERTOPDEPID']);
    } else {
        $menuArray = $fun->getSysFunArray();
    }//获取具有权限的功能 的相关信息 并存入数组
    //dump($menuArray);

    $oldct = "";
    //获取用户的禁用功能
    $myMenu = DEDEDATA . '/indexBody/menu/menu-' . $CUSERLOGIN->getUserId() . '.txt';
    $fp = fopen($myMenu, 'r');//如果不存在就创建
    if (filesize($myMenu) > 0) $oldct = trim(fread($fp, filesize($myMenu)));
    fclose($fp);

    $del_menu_array = explode(",", $oldct); //用户禁用的功能数组
    foreach ($menuArray as $menu) {
        for ($childi = 1; $childi < count($menu); $childi++) {
            $childMenu = explode(',', $menu[$childi]);  //获取子功能数组
            $childid = $childMenu[0];
            if (!in_array($childid, $del_menu_array)) {//不在禁用列表中 就显示出来
                $urladd = $childMenu[1];
                $childtitle = $childMenu[3];
                $iconName = $childMenu[7];//删除系统功能中不用的字段后，更新索引


                if (UrlAddFileExists($urladd))   //
                {
                    if (strpos($urladd, ".asp") !== false) {
                        $CUSERLOGIN = new userLogin();
                        $urladd .= "&username=" . $CUSERLOGIN->getUserName();
                    }
                    $menuLink .= "<a  data-index='$urladd' href='$urladd' name='menu' id='menu{$childid}'  class='J_menuItem btn btn-info   btn-outline' >
                                <i class='fa fa-$iconName'> </i>
                                {$childtitle}
                                <button name='closeMenu'  data-dismiss='alert' class='close'  data-toggle='tooltip' data-placement='top' title='删除此功能' style=\"margin-left: 10px;display: none\" type='button' onclick=\"delMenu('{$childid}')\">×</button>
                            </a>\r\n ";
                }
            }
        }
    }

    //1\获取历史菜单
    $myMenu = DEDEDATA . '/indexBody/history/history-' . $CUSERLOGIN->getUserId() . '.txt';
    $fp = fopen($myMenu, 'r');//如果不存在就创建
    $oldct = trim(fread($fp, filesize($myMenu)));
    fclose($fp);
    $historyLink = "";
    $history_menu_array = explode("|", $oldct);
    krsort($history_menu_array);//倒序排列
    foreach ($history_menu_array as $url) {
        //$s_scriptNames = explode('/', $url);
        $url = ltrim($url, "/");
        //得到功能的相关信息
        $sysFunInfo = $dsql->getone("SELECT id,title,urladd,iconName FROM #@__sys_function where urladd='$url'");/////160201?????此处有问题,未考虑带参数的连接
        //dump("SELECT id,title,urladd,iconName FROM #@__sys_function where urladd='$url'");
        //dump($sysFunInfo);
        if ($sysFunInfo) {
            $childid = $sysFunInfo['id'];
            $childtitle = $sysFunInfo['title'];
            $iconName = $sysFunInfo['iconName'];
            $urladd = $sysFunInfo['urladd'];
            $historyLink .= "<a  data-index='$urladd' href='$urladd' name='History' id='History{$childid}'  class='J_menuItem btn btn-info   btn-outline' >
                                <i class='fa fa-$iconName'> </i>
                                {$childtitle}
                                <button name='closeHistory'  data-dismiss='alert' class='close'  data-toggle='tooltip' data-placement='top' title='删除此功能' style=\"margin-left: 10px;display: none\" type='button' onclick=\"delHistory('{$childid}')\">×</button>
                            </a>\r\n ";
            // echo $historyLink;//用于AJAX在界面立即显示 增加的连接
        }

    }


    //3获取用户信息
    $userName = $CUSERLOGIN->getUserName();
    $realName = GetEmpNameByUserId($CUSERLOGIN->getUserId());
    $depAllName = GetEmpDepAllNameByUserId($CUSERLOGIN->getUserId(), $CUSERLOGIN->getUserType());


    //获取权限级名称
    $groupNames = GetUserTypeNames($CUSERLOGIN->getUserType());

    //直接从数据 库获取 权限内容,如有多个权限组的话 合并输出
    $sql = "SELECT logintime,loginip,loginnumb FROM `#@__sys_admin` WHERE id=" . $CUSERLOGIN->getUserId() . "";
    $groupSet = $dsql->GetOne($sql);
    $loginTime = GetDateTimeMk($groupSet['logintime']);
    $loginIp = $groupSet['loginip'];
    $loginNumb = $groupSet['loginnumb'];


    //获取其他网址
    //$defaultQuickMenu = DEDEDATA . '/indexBody/quick/quick.txt';
    $myQuickMenu = DEDEDATA . '/indexBody/quick/quick-' . $CUSERLOGIN->getUserId() . '.txt';
//dump($myQuickMenu);
    //if (!file_exists($myQuickMenu)) $myQuickMenu = $defaultQuickMenu;
    $quick = "";
    $dtp = new DwtTagparse();
    $dtp->SetNameSpace('menu', '<', '>');
    $dtp->LoadTemplet($myQuickMenu);
    if (is_array($dtp->CTags)) {
        foreach ($dtp->CTags as $ctag) {
            $title = $ctag->GetAtt('title');
            $id = $ctag->GetAtt('id');//这个用于删除时的标识
            $link = $ctag->GetAtt('link');
            $quick .= "
            <a href='{$link}' id='quick{$id}' target=\"_blank\" class='btn btn-info   btn-outline' >
                <i class='fa fa-bookmark-o'> </i>
                {$title}
                <button name='closeQuick'  data-dismiss='alert' class='close'  data-toggle='tooltip' data-placement='top' title='删除网址' style=\"margin-left: 10px;display: none\" type='button' onclick=\"delQuick('{$id}')\">×</button>
            </a>\r\n";
        }
    }


    include DwtInclude('index_body.htm');

} /*-----------------------
删除网址
function delQuick() {   }
-------------------------*/
else if ($dopost == 'delQuick') {
    if (!empty($id)) {
        $myQuickMenu = DEDEDATA . '/indexBody/quick/quick-' . $CUSERLOGIN->getUserId() . '.txt';

        $quickLink = "";
        $dtp = new DwtTagparse();
        $dtp->SetNameSpace('menu', '<', '>');
        $dtp->LoadTemplet($myQuickMenu);
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                $oldid = $ctag->GetAtt('id');
                if ($id != $oldid) {
                    $title = $ctag->GetAtt('title');
                    $link = $ctag->GetAtt('link');
                    $quickLink .= "\r\n<menu:item  link=\"{$link}\" title=\"{$title}\"  id=\"{$oldid}\" />";
                }

            }
        }
        $fp = fopen($myQuickMenu, 'w');
        fwrite($fp, $quickLink);
        fclose($fp);

    }
    exit();
} /*删除功能
此功能为反逻辑:删除后的功能ID保存入文件,显示的时候与数据库比较 ,文件中没有的才显示
function delMenu() {   }
-------------------------*/
else if ($dopost == 'delMenu') {
    if (!empty($id)) {

        //打开用户的文件,获取已经禁用的功能信息
        $myMenu = DEDEDATA . '/indexBody/menu/menu-' . $CUSERLOGIN->getUserId() . '.txt';
        $fp = fopen($myMenu, 'r');//如果不存在就创建
        $oldct = trim(fread($fp, filesize($myMenu)));
        fclose($fp);

        $oldct .= ",{$id}";

        //将新的功能ID写入文件
        $fp = fopen($myMenu, 'w');
        fwrite($fp, $oldct);
        fclose($fp);


    }
    exit();
} /*删除历史
function delHistory() {   }
-------------------------*/
else if ($dopost == 'delHistory') {
    if (!empty($id)) {


        //获得要删除的网址
        $sysFunInfo = $dsql->getone("SELECT id,title,urladd,iconName FROM #@__sys_function where id='$id'");/////160201?????此处有问题,未考虑带参数的连接
        //dump("SELECT id,title,urladd,iconName FROM #@__sys_function where urladd='$url'");
        //dump($sysFunInfo);
        if ($sysFunInfo) {
            $urladd = "/" . $sysFunInfo['urladd'];

            //打开用户的文件,获取已经禁用的功能信息
            $myMenu = DEDEDATA . '/indexBody/history/history-' . $CUSERLOGIN->getUserId() . '.txt';
            $fp = fopen($myMenu, 'r');//如果不存在就创建
            $oldct = trim(fread($fp, filesize($myMenu)));
            fclose($fp);
            $newct = "";
            $history_menu_array = explode("|", $oldct);
            if (is_array($history_menu_array)) {
                foreach ($history_menu_array as $history_menu) {
                    if ($history_menu != $urladd) {
                        $newct .= ($newct == "") ? "" : "|";
                        $newct .= "{$history_menu}";
                    }

                }
            }

            //将新的功能ID写入文件
            $fp = fopen($myMenu, 'w');
            fwrite($fp, $newct);
            fclose($fp);
        }
    }
    exit();
}
?>
       
    

