<?php


if (!defined('DWTINC')) exit('Request Error!');

/**
 * 功能管理
 *
 * @version        $Id: sysFunction.class.php 151005
 * @package
 * @copyright
 * @license
 * @link
 */
class sys_function
{
    var $dsql;

    //php5构造函数
    function __construct()
    {
        $this->dsql = 0;
    }

    function sys_function()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }


    /** sysFunction.php INDEX_MEUN.PHP  INDEX_BODY.PHP三个页面引用此类
     *
     * @param int    $depid           0  传递过来部门(公司)就获取当前部门的功能,(用在权限区分 显示 的时候)
     *                                不传递就全获取(用在菜单 快捷方式 获取全部功能,然后判断当前用户是否具有权限)
     *
     * @param string $NoDisplyFuc_str 权限组编辑时禁止使用的功能id(200,208,210)
     *
     * @return array
     */

    function getSysFunArray($depid = 0, $GroupSetNoDisplyFuc_str = "")
    {

        //dump("row47 ".$depid);
        $this->dsql = $GLOBALS['dsql'];
        $sys_function_array = array();


        //引入权限判断
        require_once(DWTPATH . "/include/role.class.php");
        $roleCheck = new roleClass();
        //$query = " SELECT f.* FROM `#@__sys_function` f  where f.topid=0 or f.id=1 $wheresql      ORDER BY   	disorder ASC";
        //原为先获取顶级功能,然后再获取子功能,存入数组
        //151223改为直接获取所有功能,按父ID分级存入数组

        //

        //dump($usertype);
        //如果部门ID不等于0 并且 不是超级管理员 则只获取当前部门所具有的功能(不显示全部功能 只显示公司可以使用的功能)
        $wheresqldep = " AND depid='$depid' ";
        if ($GroupSetNoDisplyFuc_str != "") $wheresqldep .= " AND (id NOT IN($GroupSetNoDisplyFuc_str) AND  topid NOT IN($GroupSetNoDisplyFuc_str) )";//权限组编辑时禁止使用的功能id(200,208,210)
        $query = " SELECT * FROM `#@__sys_function`    WHERE 1=1   $wheresqldep      ORDER BY   	groups asc,topid asc,disorder ASC";

        //dump($query);
        //   dump($depid);
        $this->dsql->SetQuery($query);
        $sqlid = time();
        $this->dsql->Execute($sqlid);//$sqlid 使用时间标记查询,以免和其他的查询重复
        while ($rowTopFunction = $this->dsql->GetObject($sqlid)) {
            if ($rowTopFunction->topid == 0) {
                $parentid = $rowTopFunction->id;
            } else {
                $parentid = $rowTopFunction->topid;
            }
            $id = $rowTopFunction->id;//0
            $urladd = $rowTopFunction->urladd;//1
            $groups = $rowTopFunction->groups;//2
            $parenttitle = $rowTopFunction->title;//3
            $disorder = $rowTopFunction->disorder;//4
            $remark = $rowTopFunction->remark;//5
            $isbasefuc = $rowTopFunction->isbasefuc;//6
            $iconName = $rowTopFunction->iconName;//7


            //dump ($rowTopFunction->topid > 0 && !$roleCheck->RoleCheckToBool($urladd)) ;//如果是子功能则判断是否具有权限,没有权限则跳转到下一个

            //dump("row89 ". $urladd);
            //dump($roleCheck->RoleCheckToBool($urladd));
            //strpos($urladd, "/")160620增加此判断 ，如果是“暂无功能”的临时功能 则不判断是否具有权限
            if (strpos($urladd, "/") && $rowTopFunction->topid > 0 && !$roleCheck->RoleCheckToBool($urladd)) continue;//如果是子功能则判断是否具有权限,没有权限则跳转到下一个;有权限则保存到数组

            if (!isset($sys_function_array[$parentid][0]) && $rowTopFunction->topid != 0) {
                /*
                 * 160421
                 * 当main.php菜单读取功能时,和index_body.php读取功能时
                 * 如果是非管理员用户登录
                 * 列出的功能会根据emp_dep_plus 字段functionids中的数据来限定,但这个functionids,在保存时只保存了子功能的ID,未保存父功能的ID,
                 * 所以这里,要判断一下.
                 * 这段的SQL在查询时 会先列出父功能 也就是TOPID=0的数据,topid=0时这个IF不运行
                 * 当topid!=0时,先将此子功能的父功能ID,保存到数组中[0]的位置,然后他的子功能在再[0]上自动加索引
                 *
                */
                $query = "SELECT  * FROM `#@__sys_function`    where id='$rowTopFunction->topid' ";
                //dump($query);
                $row = $this->dsql->GetOne($query);
                if (is_array($row)) {
                    $urladd1 = $row['urladd'];//1
                    $parenttitle1 = $row['title'];//3
                    $disorder1 = $row['disorder'];//4
                    $remark1 = $row['remark'];//5
                    $isbasefuc1 = $row['isbasefuc'];//6
                    $iconName1 = $row['iconName'];//7
                    $sys_function_array[$parentid][0] = "$rowTopFunction->topid,$urladd1,,$parenttitle1,$disorder1,$remark1,$isbasefuc1,$iconName1";
                }
            }
            //数组顺序和数据库相同
            $sys_function_array[$parentid][] = "$id,$urladd,$groups,$parenttitle,$disorder,$remark,$isbasefuc,$iconName";
        }
        //dump($sys_function_array);
        return $sys_function_array;
    }


    /**数组格式:   文件夹名称，文件名称，文件功能说明标题，是否跳转，是否含有部门数据\r\n");
     *系统功能添加编辑时使用
     *
     * @param int $depid 0  当有分厂级别的部门时  获取分厂所属的功能
     *
     * @return string
     */
    function getDirFileOption($depid = 0)
    {
        global $inDateUrlAddArray;  //系统功能 数据表中引用过
        require_once(DEDEDATA . "/sys_function_data.php");//引入功能的文本文件 151005修复
        $baseConfigFunArray = array();
        if (is_array($GLOBALS['baseConfigFunArray'])) $baseConfigFunArray = $GLOBALS['baseConfigFunArray'];
        $isRtuStrNotNull = false;
        //获得已经保存在数据库里的功能地址 存入数组,与文件中的判断
        //如果数据库中已经有了此功能,则不再列出
        $inDateArray = $this->getSysFunArray($depid);

        //dump($baseConfigFunArray);
        foreach ($inDateArray as $key => $menu) {
            if (count($menu) > 1) {
                for ($inDatei = 1; $inDatei < count($menu); $inDatei++) {
                    $inDateMenu = explode(',', $menu[$inDatei]);  //获取子功能数组
                    $inDateMenu_url = $inDateMenu[1];
                    $inDateUrlAddArray[] = $inDateMenu_url;
                }
            }
        }


        $funUrl_array = array();

        //dump($GLOBALS['baseConfigFunArray']);
        if ($baseConfigFunArray) {
            //160606增加 如果是管理员depid=0 则循环所有的功能，
            //如果是别的公司的 ，则只显示他所具有的功能

            $dirName_dep_array = array();
            //获取指定公司可以使用的功能
            $fileName_dep_array = array();
            //dump($depid);


            //$query = " SELECT functionNames FROM `#@__emp_dep_plus` f  where depid='$depid'";
            //160821改为从订单中获取 可以使用的功能
            $query = " SELECT GROUP_CONCAT(urladd) as functionNames FROM `#@__sys_goods_orderdetails` f  where depid='$depid'  and endDate>unix_timestamp()";
            //dump($query);
            $row = $this->dsql->GetOne($query);
            if ($row && $row["functionNames"] != "") {
                $functionNames = $row["functionNames"];
                $funUrl_array = explode(",", $functionNames);
                $dirName_dep_array = array();
                foreach ($funUrl_array as $funUrl) {
                    $dirName_dep = explode("/", $funUrl);
                    $dirName_dep_array[] = $dirName_dep[0];//文件夹名称
                    $fileName_dep_array[] = $dirName_dep[1];//功能文件名称
                }
            }


            // dump($dirName_dep_array);

            $dirName_dep_array = array_unique($dirName_dep_array);


            $rertur_arry = array();   //要返回的OPTION字符串，先放入此数组，每组数组 判断个数大于1才输出（功能文件个数大于0）
            //在文本文件里判断
            foreach ($baseConfigFunArray as $key => $row) {
                if ($dirName_dep_array && !in_array($key, $dirName_dep_array)) continue;//判断当前公司，当前文件夹是否有权限
                // dump($key);
                $dirName = $key;//获得文件夹名称
                for ($funi = 0; $funi < count($row); $funi++) {
                    if (isset($row[$funi])) {
                        $fun_info = explode(',', $row[$funi]);  //获取父文件夹数组
                        $funFile = "";
                        //dump($dirName);
                        //dump($fileName_dep_array);
                        if (count($fun_info) == 1) {
                            //文件夹
                            $funTitle = $fun_info[0];
                        } else {
                            //获取文件内容
                            //dump($fun_info);
                            $funUrladd = $dirName . "/" . $fun_info[0];
                            $funFile = $fun_info[1];
                            $funTitle = $fun_info[1];
                        }
                        //$isPutTypeDate=$fun_info[5];//调用功能栏目分类引用文件名称和地址(这里调用的是个文件名称,然后此入这个文件)

                        //dump($funUrladd);

                        //查看文件夹 是否实际存在,并且不是跳转数据
                        //dump($funFile);
                        if ($funFile == "") //如果只是目录,不是实际功能的地址,则输出灰色连接,用户保存时 提示用户 这个不可以选
                        {
                            $rertur_arry[$key][] = "<option value='0' style='background-color:#DFDFDB;color:#888888' >" . $funTitle . "</option>\r\n";
                            if (file_exists(DWTPATH . "/" . $dirName)) {
                                $dh = dir(DWTPATH . "/" . $dirName);  //引段扫描目录 下的文件,可优化使用scandir获得目录下的所有文件存为数组,但PHP中一般是禁用的,故未使用
                                while (($file = $dh->read()) !== false) {
                                    //dump(11);
                                    //屏蔽系统目录
                                    if (preg_match("#^_(.*)$#i", $file)) continue; #屏蔽FrontPage扩展目录和linux隐蔽目录
                                    if (preg_match("#^\.(.*)$#i", $file)) continue;
                                    //屏蔽 XXX.do.php xxx.class.php的页面
                                    $doClassFiles = explode('.', $file);
                                    if (count($doClassFiles) > 2) continue;
                                    //当前文件是否有catalog.php分类功能
                                    //if ($file == "catalog.php" && $depid > 0) {//160606添加depid>0 管理员不显示 子公司的分类功能
                                    if ($file == "catalog.php") {//170106分类功能
                                        require_once("../" . $dirName . "/catalog.inc.class.php");
                                        $classname = $dirName . "CatalogInc";
                                        $newClassName = $dirName . "ClI";
                                        $$newClassName = new $classname();
                                        $rertur_arry[$key][] = $$newClassName->GetOptionListToSysFunAdd($depid);
                                        $isRtuStrNotNull = true;
                                        break;
                                    }
                                }
                            }
                        } else {
                            //160620清除BUG，原是只判断文件名称，现改为 目录+文件名称判断
                            //160714清除BUG，$funUrl_array如果是管理员登录 情况下，为空判断
                            /*160811清除BUG，增加$funFile  $depid 判断
                                $funFile是文件目录，为空的话要输出目录名称，这里要判断 一下,不判断 的话，输出不了目录
                                depid要判断 一下是否管理员，管理员的话，则不进行下一句的判断
                            */
                            if (is_array($funUrl_array) && $funFile != "" && $depid > 0 && !in_array($dirName . "/" . $fun_info[0], $funUrl_array)) continue;//判断当前公司，当前功能文件是否有权限

                            //dump($funUrladd);
                            //dump($funTitle);
                            if (file_exists($GLOBALS["cfg_basedir"] . "/" . $funUrladd)) {
                                //如果文件中的功能,未在数据库中添加过 则显示
                                //dump($funUrladd);
                                //dump($inDateUrlAddArray);
                                //dump(in_array($funUrladd,$inDateUrlAddArray));
                                if (!is_array($inDateUrlAddArray)) $inDateUrlAddArray = array();//160606修改BUG，如果子公司第一次添加功能，则此处没有数组
                                if (!in_array($funUrladd, $inDateUrlAddArray)) {
                                    $rertur_arry[$key][] = "<option value='" . $funUrladd . "' >&nbsp;&nbsp;" . $funTitle . "</option>\r\n";
                                    $isRtuStrNotNull = true;
                                } else {
                                    //$rtuStr .= "<option  value='0' style='background-color:#DFDFDB;color:#888888'  >&nbsp;&nbsp;" . $funTitle . "</option>\r\n";
                                    //$isRtuStrNotNull = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        $rtuStr = "";
        //如果功能文件个数大于0  才输出 option
        foreach ($rertur_arry as $key => $row) {
            //dump($row);
            if (count($row) > 1) {
                foreach ($row as $value) {
                    $rtuStr .= $value;
                }
            }
        }
        // dump($rtuStr);
        //dump($isRtuStrNotNull);
        if ($isRtuStrNotNull) {
            return $rtuStr;
        } else {
            return "";
        }
    }
}//End Class