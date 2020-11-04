<?php
/**
 * 管理后台首页
 *
 * @version        $Id: index.php 1 11:06 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("config.php");
require_once("sys/sysFunction.class.php");
require_once(DWTINC . '/dwttag.class.php');
$t1 = ExecTime();

$CUSERLOGIN = new userLogin();
$usertype = $CUSERLOGIN->getUserType();//当前登录用户组
$menuArray = array();
$fun = new sys_function();
if ($GLOBAMOREDEP && $usertype != 10) {//如果包含多部门 并且当前登录的不是超级管理员 则只获取当前公司的功能
    $menuArray = $fun->getSysFunArray($GLOBALS['NOWLOGINUSERTOPDEPID']);
} else {
    $menuArray = $fun->getSysFunArray();
}//获取具有权限的功能 的相关信息 并存入数组

//dump($menuArray);

$dirs = "";
$files = "";
$childfilename_array = array();
//此段要优化到别的地方 ,因为很多地方 使用了????优化到sys/sysFunction.class.php   150118
//dump($menuArray);
foreach ($menuArray as $menu) {
    $parentMenu_array = explode(',', $menu[0]);  //获取父文件夹数组
    // dump($parentMenu_array);
    if ($parentMenu_array[1] == "") {//160523优化  判断是否父功能,(父功能的地址为空)
        $parentMenuTitle = $parentMenu_array[3];
        $parentMenuIconName = $parentMenu_array[7];//删除系统功能中不用的字段后，更新索引
        $childfilename_array = getchildfilename($parentMenuTitle, $menu);
        if (count($childfilename_array) > 0) $files[$parentMenuTitle . "|" . $parentMenuIconName] = $childfilename_array;
    }
}

//dump($dirs);
///dump($files);
//150124增加判断是否包含分类功能,
//返回树形菜单 代码
/**
 * @param $urladd   新闻 设备等包含子分类的功能地址
 * @param $dirtitle
 *
 * @return array
 */
function getCatalogMenu($urladd, $dirtitle)
{

    //dump($urladd);
    $reArray = array();
    $urlParameter = "";   //连接地址中的参数
    $urladdArray = explode('/', $urladd);  //分隔地址 用于获取文件名称

    //150118  自动搜索是否包含分类连接,如果有分类 则输出下接连接
    $dirName = $urladdArray[0];//获得文件夹名称
    $filenameArray = explode('?', $urladdArray[1]);  //按?分隔
    $filename = $filenameArray[0];  //文件名称
    //如果当前文件名称与目录名称+.PHP相同 并且不是系统目录 ,则扫描当前目录下是否有catalog.php分类功能,如果有分类功能,则自动加载分类的树形菜单

    if ($filename == $dirName . ".php" && $dirtitle != "系统") {
        $dh = dir(DWTPATH . "/" . $dirName);  //引段扫描目录 下的文件,可优化使用scandir获得目录下的所有文件存为数组,但PHP中一般是禁用的,故未使用
        while (($file = $dh->read()) !== false) {
            //屏蔽系统目录
            if (preg_match("#^_(.*)$#i", $file)) continue; #屏蔽FrontPage扩展目录和linux隐蔽目录
            if (preg_match("#^\.(.*)$#i", $file)) continue;
            //屏蔽 XXX.do.php xxx.class.php的页面
            $doClassFiles = explode('.', $file);
            if (count($doClassFiles) > 2) continue;

            //当前文件是否有catalog.php分类功能
            if ($file == "catalog.php") {
                if (count($filenameArray) > 1) $urlParameter = $filenameArray[1]; //连接参数
                $catalogId = 0;

                if ($urlParameter != "") {
                    $parameterArray = explode('=', $urlParameter);
                    $catalogId = $parameterArray[1];   //分隔参数，获得栏目的ID
                }/* else {
                    170106注释 掉，在管理员环境下，商品 新闻 未指定分类参数时，也要下拉
                    continue;//160811如果没有参数  则直接输出当前连接
                }*/
                require_once(DWTPATH . "/" . $dirName . "/catalog.inc.class.php");

                $classname = $dirName . "CatalogInc";
                $newClassName = $dirName . "ClI";
                $$newClassName = new $classname();
                // dump($$newClassName );
                $reArray = $$newClassName->GetListToMenu($catalogId, true);
                break;
            }
        }
    }
    //dump($reArray);
    return $reArray;
}


/**获取子功能,
 *
 * @param $dirtitle
 * @param $menu
 * @param $diri
 *
 * @return array
 */
function getchildfilename($dirtitle, $menu)
{
    //dump($dirtitle);
    //dump($menu);
    $childMenuLinkArray = array();
    //将子功能菜单连接，按分组存入数组中
    for ($childi = 1; $childi < count($menu); $childi++) {
        $childMenuLink = "";

        $childMenu = explode(',', $menu[$childi]);  //获取子功能数组
        $childid = $childMenu[0];
        $urladd = $childMenu[1];
        $groupName = $childMenu[2];
        $childtitle = $childMenu[3];

        //如果文件存在 则输出连接
        if (UrlAddFileExists($urladd)) {
            $link_array = getCatalogMenu($urladd, $dirtitle);
            //dump($link_array);
            //如果有子分类连接，则输出子分类，
            //如果没有子分类，则直接输出 当前地址
            if (count($link_array) > 0) {
                $groupName = $childtitle;
                foreach ($link_array as $link) {
                    $childMenuLink = array("title" => $link["title"], "urladd" => $link["urladd"]);
                    $childMenuLinkArray[$groupName][] = $childMenuLink;

                }

            } else {
                $childMenuLink = array("title" => $childtitle, "urladd" => $urladd);
                $childMenuLinkArray[$groupName][] = $childMenuLink;

            }
        } else {//如果文件不存在  则只输出灰色文字
            $childMenuLink = array("title" => $childtitle, "urladd" => "");
            $childMenuLinkArray[$groupName][] = $childMenuLink;

        }

    }

    //dump( $childMenuLinkArray);
    return $childMenuLinkArray;
}


$menuStr = "";
//分隔获取菜单
//dump($files);
if (!empty($files)) {
    //dump($files);
    $i = 0;
    $defaultShowDir = "";
    foreach ($files as $dir_names => $groupArray) {   //父名称
        //dump($files);
        $dir_name_array = explode("|", $dir_names);
        $dir_name = $dir_name_array[0];
        //dump($dir_name);
        if ($CUSERLOGIN->getuserName() == "lyadmin2" && $dir_name != "系统") continue;//公司操作员只显示权限相关内容
        $iconName = "bars";//默认的图标
        $icon = $dir_name_array[1];
        if ($icon != "") $iconName = $icon;

        $i++;
        if ($i == 1) $defaultShowDir = "mainmenu1";//默认展开的菜单
        $menuStr .= "<li id=\"mainmenu$i\">
                    <a href=\"#\">
                        <i class='fa  fa-$iconName'></i>
                        <span class=\"nav-label\">$dir_name</span>
                        <span class=\"fa arrow\"></span>
                    </a>
                    <ul class=\"nav nav-second-level\">";
        foreach ($groupArray as $group_name => $functionArray) {  //组名称
            //如果有分组,则显示三级
            if ($group_name != "") {
                //dump($group_name);
                $menuStr .= " <li>
                                <a href='#'>$group_name <span class='fa arrow'></span></a>
                                <ul class='nav nav-third-level'>
                              ";
            }
            //dump($functionArray);
            foreach ($functionArray as $functionArray_s) {    //功能数组

                $title = $functionArray_s["title"];//功能名称
                $urladd = $functionArray_s["urladd"];//功能地址

                //dump($urladd);
                //dump(UrlAddFileExists($urladd));
                //dump($title);
                if ($urladd != "")   ///如果文件存在 则输出连接
                {
                    $menuStr .= " <li><a class=\"J_menuItem\" href=\"$urladd\" data-index=\"$urladd\">$title</a></li>";
                } else {//160421如果地址不存在,则输出不可以访问
                    $menuStr .= " <li style='margin-left: 50px'> $title </li>";
                }
            }
            if ($group_name != "") {
                $menuStr .= " </ul></li>";
            }
        }
        $menuStr .= "</ul>
                </li>";
    }
}


//获取未进行的工作
require_once("include/schedule.class.php");
$sc = new schedule();
$scheduletotalNumb = $sc->totalNumb;
if ($scheduletotalNumb > 0) {
    $scheduleurlsstr = $sc->urlsstr; //未读连接地址
    $scheduletotalStr = "<li class=\"dropdown\" >
                            <a class=\"dropdown-toggle count-info\" data-toggle=\"dropdown\" href=\"#\">
                                <i class=\"fa fa-bell\"></i> <span class=\"label label-warning\">$scheduletotalNumb</span>
                            </a>
                            <ul class=\"dropdown-menu dropdown-alerts\">
                                $scheduleurlsstr
                            </ul>
                        </li>";
}


//dump($files);
include DwtInclude('main.htm');
