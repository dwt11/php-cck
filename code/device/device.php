<?php
/**
 * 商品自动列表
 *
 *
 * 思路:
 * 1、如果不带typeid参数直接打开，则只显示主表的字段内容。（这项一般不用，默认菜单中，没有不带连接的地址）
 *
 * 2、typeid>0打开页面后，先
 *              设置主表默认搜索的INPUT类型字段的名称，(存在数组中备用)
 *              设备主表默认搜索的select类型字段的名称,(存在数组中备用)
 *
 * 3、typeid>0 从field.class.php中根据typeid获取模型的相关信息
 *          $addonTable_fieldnames   附加 表的字段名称，用于SQL语句中获取数据，所有字段，全部列出
 *          $addtable                附加表名称
 *          $s_tmplets               模型对应的模板名称
 *          $addon_searchSelectnameArray    搜索的select单独字段名称
 *          $addon_searchInputnameArray     搜索的input字段名称
 *
 *4、合并默认input和附加 的input 数组。 生成最终的 搜索SQL代码  （ $searchInputFieldName_array ，$addon_searchInputnameArray
 *5、合并默认select和附加 的select数组。生成最终的搜索SQL代码
 *
 *6、生成SetParameter翻页使用的代码，
 *              这里input都使用的同一个keyword,所以直接列出
 *              select类型的参数，使用第5部合并后的参数 生成
 *
 *
 * 7、在HTML模板页面，获取搜索表单
 *              主表单的SELECT类型的，手动写入
 *              主表单的input类型的，说明文字和附加搜索字段的当前值 传入GetSearchFrom，生成SELECT和INPUT的HTML代码输出
 *
 *
 */
require_once("../config.php");
require_once(DWTINC . '/field.class.php');
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("device.functions.php");
require_once(DWTINC . "/fields.func.php");

$t1 = ExecTime();
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

$typeid = isset($typeid) ? intval($typeid) : 0;

$addonTable_fieldnames = $autoFieldHtmlTitle = $fieldname_array = $addtable = "";//初始化 附加 表的一些变量
$addon_searchInputnameArray = $addon_searchSelectnameArray = array();

if (!isset($keyword)) $keyword = '';
if (!isset($dopost)) $dopost = '';


$maintable = '#@__device';
$title = $sysFunTitle;   //页面显示标题

//获取当前点开的分类信息
$tl = new DeviceTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetDeviceTypeOptionS();  //搜索表单的分类值//GetOptionArray
$ispart = $tl->GetIspart();  //是否封面栏目  如果是封面栏目  则不输出添加
$title .= $positionname;


//默认的搜索条件
$whereSql = " where $maintable.`status`='0'";
$searchInputFieldName_array = array("devicename", "devicecode");//主表中 INPUT搜索框的默认字段
$searchSelectFieldName_array = array("typeid");//主表中 select搜索框的默认字段


//排序条件
$orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z0-9]#", "", $orderby);
$orderbyField = '' . $orderby . ' ASC';

//获取模型ID
$row = $dsql->GetOne("SELECT channeltype FROM `#@__device_type` WHERE id='$typeid'");
$channeltypeid = $row['channeltype'];

//附加 表的 参数获取
$af = new AutoField($channeltypeid);
if ($typeid > 0) {
    //获得数据表名
    $addonTable_fieldnames = $af->fieldnames;  //----------------------------------------------------------------------------SQL语句中的字段名称
    $addtable = $af->addtable;//---------------------------------------------------------------------------------------------附加表名称
    $s_tmplets = $af->templist;//----------------------------------------------------------------------------------------------模板名称
    $addon_searchSelectnameArray = $af->searchselectnameArray;    //搜索的select单独字段名称
    $addon_searchInputnameArray = $af->searchinputnameArray;    //搜索的input字段名称

    $autoFieldHtmlTitle = $af->itemnames;    //HTml模板自动显示 附加 表的列头
    $fieldname_array = $af->fieldnamArray;//用于在模板中获取每列的数据显示
}
//dump($addonTable_fieldnames);
$searchInputFieldName_array = array_merge($searchInputFieldName_array, $addon_searchInputnameArray);//合并主表INPUT搜索字段和附加 表的搜索字段
$searchInputSql = "";
//input共用模糊搜索SQL生成------------------------------------------------------------------------------------------------------
foreach ($searchInputFieldName_array as $filedname) {
    $sql_str = " `$filedname` like '%" . $keyword . "%'";
    if ($searchInputSql == "") {
        $searchInputSql .= $sql_str;
    } else {
        $searchInputSql .= "  or " . $sql_str;
    }
}


$searchValue_array = array();
if ($keyword != "") {
    $whereSql .= " and ( ";
    $whereSql .= $searchInputSql;
    $whereSql .= " ) ";
    $searchValue_array["keyword"] = $keyword;
}


//dump($searchInputFieldName_array);
//dump($addon_searchSelectnameArray);
$searchSelectFieldName_array = array_merge($searchSelectFieldName_array, $addon_searchSelectnameArray);//合并主表select搜索字段和附加 表的搜索字段
//将模型中的单独的搜索表单 语句获取出来
//这段先放在这里，随后看是否需要放到 fields.func.php,因为类似 typeid这样的，或depid这样公用的搜索代码生成可以共用170112??????????????
foreach ($searchSelectFieldName_array as $filedname) {
    //if ($addtable != "") $filedname = str_replace($addtable . ".", "", $filedname);//将表名参数去掉，从POST过来的参数中取值
    //$filedname = str_replace($maintable . ".", "", $filedname);//将表名参数去掉，从POST过来的参数中取值
    if (!isset($$filedname)) $$filedname = '';
    //dump($filedname);
    if ($$filedname != "") {
        if ($filedname == "typeid") {
            //如果是typeid则生成搜索所有子类的代码
            $whereSql .= " AND $maintable.`typeid` IN (" . $tl->GetDeviceSonIds() . ")";    //搜索用的
        } else {
            $whereSql .= " AND $addtable.`$filedname` ='" . $$filedname . "'";
        }
        $searchValue_array[$filedname] = $$filedname;
    }
}


//dump($searchValue_array);
$leftjoinSql = "";
if ($typeid > 0) $leftjoinSql = " LEFT JOIN $addtable  on $addtable.deviceid=$maintable.id ";

$query = "SELECT  $maintable.*{$addonTable_fieldnames} FROM `$maintable`   
           $leftjoinSql
          $whereSql
            ORDER BY   $orderbyField ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 20;

//GET参数
$dlist->SetParameter('keyword', $keyword);//input的搜索参数
$dlist->SetParameter('orderby', $orderby);
//将模型中的搜索参数获取出来
//dump($searchSelectFieldName_array);
if (is_array($searchSelectFieldName_array)) {
    foreach ($searchSelectFieldName_array as $filedname) {
        //if ($addtable != "") $filedname = str_replace($addtable . ".", "", $filedname);//将表名参数去掉，从POST过来的参数中取值
        //$filedname = str_replace($maintable . ".", "", $filedname);//将表名参数去掉，从POST过来的参数中取值
        //dump($filedname);
        $dlist->SetParameter($filedname, $$filedname);
    }
}


//$orderbySelected = "";
//if ($orderby == "price") $orderbySelected = "selected";
$searchFrom = " 
            <div class=\"pull-left\">
                <select name='typeid' class='form-control'>
                    $optionarr
                </select>
            </div>
            ";
$searchFrom .= $af->GetSearchFrom("编号/名称", $searchValue_array);


//模板
if (empty($s_tmplets)) $s_tmplets = 'device.htm';
//$s_tmplets = 'device.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
$dlist->Close();

$t2 = ExecTime();
//echo $t2 - $t1;

