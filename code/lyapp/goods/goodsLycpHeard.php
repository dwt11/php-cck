<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . '/datalistcp.class.php');
require_once("../../goods/catalog.class.php");

$t1 = ExecTime();
if (empty($typeid)) $typeid = '';
if (empty($q)) $q = '';


$tl = new GoodsTypeUnit();
//获取栏目分类
$linkstrs = "";
$current = " ";
if (3 == $typeid) $current = " current ";
$linkstrs = " <div class='topnav_item $current' id='t3' >
                        <div class='topnav_item_box'>
                            <a href='goods_list.php?typeid=3'>直通车</a>
                        </div>
                    </div>
                    ";

$arrayData = GetTypeInfoAfterArray($tl->GetTypeInfoArray(), 4);//只获取  其他旅游下的分类
foreach ($arrayData as $keyp => $valuep) {
    $typeInfoArray = $arrayData[$keyp];
    $typename = base64_decode($typeInfoArray['typename']);
    $id = $typeInfoArray['id'];
    $reid = $typeInfoArray['reid'];

    $current = " ";
    if ($id == $typeid) $current = " current ";
    if ($id != 4) {

        $linkstrs .= " <div class='topnav_item $current' id='t$id' >
                        <div class='topnav_item_box'>
                            <a href='goods_list.php?typeid=$id'>$typename</a>
                        </div>
                    </div>
                    ";
    }
}


echo "
        <div class='header'>
                <div class='pull-left index'>
                    <a href='/lyapp/'><i class='fa fa-home'></i>
                        <h5>
                            首页
                        </h5>
                    </a>
                </div>
            
                <div class='pull-right mycentre'>
                    <a href='/lyapp/mycentre.php'><i class='fa fa-user'></i>
                        <h5>
                            我的
                        </h5>
                    </a>
                </div>
                <div class='top-bar-c'>
                    <div class='s-input-select'>
                        <div class='s-input-frame'>
                            <form class='c-form-suggest' method='get' action='line_search.php'>
                                <div class='s-form-search search-form'>
                                    <input type='search' name='q' class='J_autocomplete' autocomplete='off' value='$q' placeholder='请输入目的地或景点名称'>
                                    <!--<input type='hidden' name='typeid' value='<?php /*echo $typeid */?>'>-->
                                </div>
                                <div class='c-form-btn'>
                                    <input type='submit' name='search' class='icons-search'>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class='main_nav_wrap main_nav_topnav_wrap'>
                <div class='topnav_list_contain'>
                    <div class='topnav_list_scroll_wrap'>
                        <div class='topnav_list'>
                            $linkstrs
                        </div>
                    </div>
                </div>
            </div>
";