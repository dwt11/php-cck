<?php
/**
 * 微信参数编辑
 *
 * @version        $Id: sysGroup_edit.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */

require_once("../config.php");
require_once(DWTINC . "/request.class.php");


if (empty($dopost)) $dopost = '';


if (!isset($id)) {
    ShowMsg("参数出错!", "-1");
    exit();
}


/*从数据库获取微信菜单*/
$query = "SELECT * FROM `#@__interface_weixin_menu` WHERE wid=$id   ORDER BY   disorder aSC, id ASC";
$dsql->Execute('me', $query);
$oneMenu_array = $twoMenu_array = $menu_array = array();
while ($row = $dsql->getarray()) {
    if ($row['reid'] == 0) {
        $oneMenu_array[] = $row;
    } else {
        $twoMenu_array[] = $row;
    }

}
foreach ($oneMenu_array as $oneMenu) {
    $menu_array[] = $oneMenu;
    foreach ($twoMenu_array as $key => $twoMenu) {
        if ($twoMenu['reid'] == $oneMenu['id']) {
            $menu_array[] = $twoMenu;
            unset($twoMenu_array[$key]);
        }
    }
}
/*从数据库获取微信菜单*/


/*分隔菜单 */
$display_array = array_filter($menu_array, 'oneeven');
$display_son_array = array_filter($menu_array, 'twoeven');
foreach ($display_array as $key => $display) {
    $son = "";
    $sonstr = "";
    $sonNumb = 0;
    //dump($display_son_array);
    foreach ($display_son_array as $display_son) {
        if ($display_son['reid'] == $display_array[$key]['id']) {
            if ($display_son['type'] == 'view') {
                $sonstr .= '{"type":"view","name":"' . $display_son['name'] . '","url":"' . $display_son['typeValue'] . '"},';
            } else {
                $sonstr .= '{"type":"click","name":"' . $display_son['name'] . '","key":"' . $display_son['typeValue'] . '"},';
            }
        }
    }
    $display_array[$key]['son'] = $son;
    $display_array[$key]['sonstr'] = $sonstr;
}


//格式化菜单
$menudata = '';
$startmenu = '{"button":[';
$endmenu = ']}';
$menustr = '';
foreach ($display_array as $display) {
    if (empty($display['sonstr'])) {
        if ($display['type'] == 'view') {
            $menustr .= '{"type":"view","name":"' . $display['name'] . '","url":"' . $display['typeValue'] . '"},';
        } else {
            $menustr .= '{"type":"click","name":"' . $display['name'] . '","key":"' . $display['typeValue'] . '"},';
        }
    } else {
        $menustr .= '{"name":"' . $display['name'] . '","sub_button":[' . substr($display['sonstr'], 0, -1) . ']},';
    }
}
$menudata = substr($menustr, 0, -1);
$wxmenu = $startmenu . $menudata . $endmenu;


$row = $dsql->GetOne("SELECT ACCESS_TOKEN,AppId,AppSecret FROM #@__interface_weixin where id=$id");
$AppId = $row['AppId'];
$AppSecret = $row['AppSecret'];
$ACCESS_TOKEN = Get_access_token($AppId, $AppSecret);//从缓存获取$ACCESS_TOKEN

//发送到微信

$request = uploadWeixinMenu($wxmenu, $ACCESS_TOKEN);

$data = json_decode($request, true);
if ($data["errcode"] == "42001") {
    //如果返回的是错误 表示$ACCESS_TOKEN超时,则重新获取$ACCESS_TOKEN
    //检测是否可以获取到$ACCESS_TOKEN  用于判断微信的参数是否正确
    $ACCESS_TOKEN = Get_access_token($AppId, $AppSecret);
    if ($ACCESS_TOKEN == "" || $ACCESS_TOKEN == "false") {
        ShowMsg("上传失败,请重试.如果多次尝试不成功,请在微信配置中检查AppId与AppSecret是否对应", "-1");
        exit();
    }
    $inQuery = "UPDATE `#@__interface_weixin` SET `TOKEN`='$ACCESS_TOKEN' WHERE (`id`='$wid')";
    $dsql->ExecuteNoneQuery($inQuery);

    //再次上传菜单
    $request = uploadWeixinMenu($wxmenu, $ACCESS_TOKEN);
}

$data = json_decode($request, true);
$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
if ($data["errcode"] == "0") {
    ShowMsg("上传信息到微信成功！", $$ENV_GOBACK_URL);
} else {
    ShowMsg("上传信息到微信失败,请联系管理员！", $$ENV_GOBACK_URL);
}
exit;

/*upload发送到微信*/


//过滤数组单元,获取一级菜单
function oneeven($var)
{
    return ($var['reid'] == 0);
}

//过滤数组单元,获取二级菜单
function twoeven($var)
{
    return ($var['reid'] != 0);
}

