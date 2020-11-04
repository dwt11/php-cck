<?php if (!defined('DWTINC')) exit("Request Error!");
/**
 * device目录外部调用获取相关的分类信息
 *
 */

/**
 * 外部调用分类类
 *
 * @package          DeviceCatalogInc
 * @subpackage       DedeCMS.Libraries
 * @link             
 */
class deviceCatalogInc
{
    var $dsql;

    //构造函数///////
    //php5构造函数
    function __construct()
    {
        $this->dsql = $GLOBALS['dsql'];
    }

    //对于使用默认构造函数的情况
    //GetPositionLink()将不可用
    function deviceCatalogInc()
    {
        $this->__construct();
    }

    //关闭数据库连接，析放资源
    function Close()
    {
    }






    /*地址中带有栏目分类的权限判断 XXX/XXX.PHP?CID=
      系统中栏目分类默认为 循环获取当前功能页面的CID的,所有上级功能页面对应的权限 数据
      url地址中的typeid必须紧跟在?号后面

         //    则判断地址是否为 文档管理 添加 编辑 删除的地址
        //       如果是则用于判断权限的地址要加上？后的参数
        //       如果不是文档管理的地址，则只取不带参数的地址 用于权限判断

        //如果再有其他的功能要带参数的权限判断 这段还可扩充，


      主要有:文档管理


       * @param     string  $n  功能名称  (已经在Test_webRole判断过,无权限.但地址中包含栏目分类CID参数,在这里再判断它的父栏目是否有权限 )
    */


    //使用当前访问地址,得到当前栏目ID的 所有上级栏目地址
    //role.func.php//中使用
    //150129优化传进CID获取所有上级的CID数组
    function GetAllParentUrlToRole($typeid)
    {

        //获取当前ID的所有上级
        global $reidArray;
        $reidArray = "";
        $this->LogicGetAllParentUrl($typeid);
        //dump($reidArray."0------");

        return $reidArray;
    }

    /**
     *  逻辑递归  获取当前栏目的 所有上级ID  返回数组 供userlogin.class.php权限判断
     *
     * @access    public
     *
     * @param     int $id 栏目ID
     *
     * @return    array
     */
    function LogicGetAllParentUrl($id)
    {
        global $reidArray;
        $this->dsql->SetQuery("SELECT id,reid FROM #@__archives_type WHERE id='" . $id . "'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        if ($this->dsql->GetTotalRow($id) > 0) {
            while ($row = $this->dsql->GetObject($id)) {
                $reid = $row->reid;
                if ($reid > 0) {
                    $reidArray[] = $reid;
                    $this->LogicGetAllParentUrl($reid);
                }
            }
        }

    }











    //---------------------------根据给定的分类 ID，获取它所有的子类  给index_menu.php 供下拉菜单使用
    //$typeid,指定栏目的ID
    //$ISGETALLSUN是否获取所有的子分类
    function GetListToMenu($typeid = 0, $isautoload = false)//150116增加是否自动加载子类
    {
        $DeviceCataloglistToMenu = array();//160606修改，输出X系统的菜单格式
        if (!$this->dsql) $this->dsql = $GLOBALS['dsql'];

        $sql = "SELECT id,typename FROM #@__device_type WHERE reid='$typeid'";

        $this->dsql->SetQuery($sql);
        $this->dsql->Execute("device" . $typeid);
        while ($row = $this->dsql->GetObject("device" . $typeid)) {
            $id = $row->id;
            $typeName = $row->typename;


            $DeviceCataloglistToMenu[$id]["title"] = $typeName;
            $DeviceCataloglistToMenu[$id]["urladd"] = "device/device.php?typeid=$id";

        }
        return $DeviceCataloglistToMenu;
    }


    //---------------------------根据给定的分类 ID，获取它所有的子类  给index_menu.php 供下拉菜单使用


    //---这个sys_function.class.php中引用------------------------获取栏目OPTION  给sys_function.class.php 供系统功能添加时使用
    function GetOptionListToSysFunAdd()
    {
        global $optionArrayListToSysFunAdd;//父栏目
        global $optionArrayListToSysFunAdd_sun; //子栏目 
        global $inDateUrlAddArray;  //系统功能 数据表中引用过

        if (!$this->dsql) $this->dsql = $GLOBALS['dsql'];
        $optionArrayListToSysFunAdd = '';
        $query = "SELECT id,typename FROM `#@__device_type` WHERE  reid=0   ORDER BY   sortrank ASC";

        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetObject()) {

            //$optionArrayListToSysFunAdd_sun = "";
            // $this->LogicGetOptionListToSysFunAdd($row->id, "─");

            $funUrladd = "device/device.php?typeid=" . $row->id;
            if (!is_array($inDateUrlAddArray)) $inDateUrlAddArray = array();//160606修改BUG，如果子公司第一次添加功能，则此处没有数组
            if (!in_array($funUrladd, $inDateUrlAddArray))//如果此地址没有在 系统功能 数据表中引用过，则添加到OPTION中
            {
                $optionArrayListToSysFunAdd .= "<option value='$funUrladd'  style='background-color:#FFFF00;color:#666666'>&nbsp;&nbsp;" . $row->typename . "</option>\r\n";
                // $optionArrayListToSysFunAdd .= $optionArrayListToSysFunAdd_sun;//170106添加功能时，只获取一级分类，其下的子目录不再获取
            }
        }
        return $optionArrayListToSysFunAdd;
    }

    /**
     *  逻辑递归
     *
     * @access    public
     *
     * @param     int $id   栏目ID
     * @param     int $step 步进标志
     * @param     int $oper 操作权限
     *
     * @return    string
     */
    function LogicGetOptionListToSysFunAdd($id, $step)
    {
        global $optionArrayListToSysFunAdd_sun;
        global $inDateUrlAddArray;
        $this->dsql->SetQuery("SELECT id,typename FROM #@__device_type WHERE reid='" . $id . "'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        while ($row = $this->dsql->GetObject($id)) {


            $funUrladd = "device/device.php?typeid=" . $row->id;
            if (!in_array($funUrladd, $inDateUrlAddArray)) {
                $optionArrayListToSysFunAdd_sun .= "<option value='$funUrladd'   style='background-color:#FFFF00;color:#666666'>&nbsp;&nbsp;$step" . $row->typename . "</option>\r\n";
                //$this->LogicGetOptionListToSysFunAdd($row->id, $step . "─"); 170106只获取两级，不再递归
            }


        }
    }
    //---这个文件在sys_function.class.php中引用------------------------获取栏目OPTION  给sys_function.class.php 供系统功能添加时使用

}
