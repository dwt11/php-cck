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
class goods
{
    var $dsql;

    //php5构造函数
    function __construct()
    {
        $this->dsql = 0;
    }

    function goods()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }


    /**
     *
     * 已经在商品数据库中的功能
     *$urladd_temp  当前记录选中的值
     * @return array
     */

    function getInGoodsArray($urladd_temp)
    {

        $this->dsql = $GLOBALS['dsql'];
        $sys_goods_array = array();
        $query = " SELECT f.* FROM `#@__sys_goods` f  where urladd!='$urladd_temp'    ";
        $this->dsql->SetQuery($query);
        $sqlid = time();
        $this->dsql->Execute($sqlid);//$sqlid 使用时间标记查询,以免和其他的查询重复
        while ($row = $this->dsql->GetObject($sqlid)) {
            $id = $row->id;//0
            $dir = $row->dir;//1
            $urladd = $row->urladd;//1
            //数组顺序和数据库相同
            $sys_goods_array[$dir][] = "$urladd";
        }
        //dump($sys_goods_array);
        return $sys_goods_array;
    }


    /**数组格式:   文件夹名称，文件名称，文件功能说明标题，是否跳转，是否含有部门数据\r\n");
     *
     *$urladd_temp  当前记录选中的值
     *
     * @return string
     */
    function getDirFileOption($urladd_temp="")
    {
        require_once(DEDEDATA . "/sys_function_data.php");//引入功能的文本文件
        $baseConfigFunArray = array();
        if (is_array($GLOBALS['baseConfigFunArray'])) $baseConfigFunArray = $GLOBALS['baseConfigFunArray'];
        //获得已经保存在数据库里的功能地址 存入数组,与文件中的判断
        //如果数据库中已经有了此功能,则不再列出
        $inDateArray = $this->getInGoodsArray($urladd_temp);


        if ($baseConfigFunArray) {
            $rertur_arry = array();   //要返回的OPTION字符串，先放入此数组，每组数组 判断个数大于1才输出（功能文件个数大于0）
            //在文本文件里判断
            foreach ($baseConfigFunArray as $key => $row) {
                //dump($key);
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

                        //查看文件夹 是否实际存在,并且不是跳转数据
                        //dump($funFile);
                        if ($funFile == "") //如果只是目录,不是实际功能的地址,则输出灰色连接,用户保存时 提示用户 这个不可以选
                        {
                            $rertur_arry[$key][] = "<option value='0' style='background-color:#DFDFDB;color:#888888' >" . $funTitle . "</option>\r\n";
                        } else {
                            if (file_exists($GLOBALS["cfg_basedir"] . "/" . $funUrladd)) {
                                //如果文件中的功能,未在数据库中添加过 则显示
                               // dump(array_key_exists($dirName,$inDateArray));
                                if (!(count($inDateArray) > 0 && array_key_exists($dirName,$inDateArray) && in_array($funUrladd, $inDateArray[$dirName]))) {
                                   // dump($funUrladd);
                                    //dump($urladd_temp);
                                    $selected="";
                                    if($urladd_temp==$funUrladd)$selected=" selected ";
                                    $rertur_arry[$key][] = "<option value='" . $funUrladd . "' $selected>&nbsp;&nbsp;" . $funTitle . "</option>\r\n";
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

        return $rtuStr;

    }
}//End Class