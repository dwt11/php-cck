<?php if (!defined('DWTINC')) exit('Request Error!');
/**
 * 分类单元,主要用户管理后台管理处
 *
 */


/**
 * 分类单元,主要用户管理后台管理处
 *
 * @package          TypeUnit
 * @subpackage       DedeCMS.Libraries
 * @link             
 */
class GoodsTypeUnit
{
    var $dsql;
    var $typeInfoArrayS;    //所有分类的数组----数据库中的原始数据
    var $selTypeId;    //当前设定的分类id
    var $ispart; //当前浏览的分类页面  是否频道封面，在device.PHP页面中使用
    var $catalogGoodsNumsArray; //存储每个分类下商品的数量

    /**
     * php5构造函数
     *
     * @param int $typeid 当前选中的分类ID
     */
    function __construct($typeid = 0)
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->selTypeId = $typeid;
        $this->typeInfoArrayS = array();
        //载入单条类目信息
        $query = "SELECT tp.* FROM `#@__goods_type` tp WHERE tp.id='$typeid'";
        //dump($query);
        if ($typeid > 0) {
            $row = $this->dsql->GetOne($query);
            $this->ispart = $row['ispart'];//当前浏览的分类页面  是否频道封面，在goods.PHP页面中使用(频道页面不可以添加)
        }
        $this->getAllTypeInfoToArray();//获取所有分类信息多维数组
    }

    function GoodsTypeUnit($typeid)
    {
        $this->__construct($typeid);
    }

    //清理类
    function Close()
    {
    }


    /**170106
     * 所有分类信息存入数组 供使用
     * 这个给获取 分类id  分类上级名称 索引等使用(只有id reid 名称 三个字段)
     */
    function getAllTypeInfoToArray()
    {
        $query = "SELECT id,reid,typename,ispart FROM `#@__goods_type`  ORDER BY id,sortrank asc ";
       // dump($query);
        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetObject()) {
            $row->typename = base64_encode($row->typename);
            //格式 (id,上级ID,名称)
            $this->typeInfoArrayS[] = array("id" => $row->id, "reid" => $row->reid, "typename" => $row->typename, "ispart" => $row->ispart);
        }
        $this->typeInfoArrayS = SetNewTypeInfoArray($this->typeInfoArrayS);//重新的排序数据库
    }

    function GetTypeInfoArray(){
        return $this->typeInfoArrayS;
    }


    /**
     * 170106
     * 商品列表页 goods.php使用
     * 获取分类option选择项
     *
     * @param int $GetSonTypeId  如果指定此值，则只获取此ID下的分类
     *
     * @return 多个option字符串
     */
    function GetGoodsTypeOptionS($GetSonTypeId=0)
    {
        //$returnStr = "";
        $returnStr = "<option value='0' >请选择分类</option>\r\n";
        //获取当前选定的分类
        if ($this->selTypeId > 0) {
            $selTypeInfoArray = SearchOneArray($this->typeInfoArrayS, "id", $this->selTypeId);
            $typename = base64_decode($selTypeInfoArray['typename']);
            $id = $selTypeInfoArray['id'];
            $ispart = $selTypeInfoArray['ispart'];
            $reid = $selTypeInfoArray['reid'];
            if ($ispart == 1) $style = "style='background-color:#DFDFDB;color:#888888'"; else $style = "";  //option 背景颜色  封面是灰的;
            if ($reid == 0) $style = " class='option1'";//option 背景颜色  封面是浅绿;
            $returnStr .= "<option value='$id' $style selected>$typename</option>\r\n";
        }


        //获取其他的分类
        $arrayData = $this->typeInfoArrayS;
        if($GetSonTypeId>0){
            //如果指定了，限定ID，则只获取此ID下的分类
            $arrayData = GetTypeInfoAfterArray($this->typeInfoArrayS, $GetSonTypeId);//获取包含当前ID的所有子分类的信息数组

        }
        if (is_array($arrayData)) {
            foreach ($arrayData as $keyp => $valuep) {
                $typeInfoArray = $arrayData[$keyp];
                $typename = base64_decode($typeInfoArray['typename']);
                $id = $typeInfoArray['id'];
                $step = $typeInfoArray['step'];//层级
                $ispart = $typeInfoArray['ispart'];
                $reid = $typeInfoArray['reid'];
                $stepStr = "";
                for ($i = 0; $i < $step; $i++) {
                    $stepStr .= "─";
                }//层级显示
                if ($ispart == 1) $style = "style='background-color:#DFDFDB;color:#888888'"; else $style = "";  //option 背景颜色;
                if ($reid == 0) $style = " class='option1'";
                $returnStr .= "<option value='$id'  $style >$stepStr$typename</option>\r\n";
            }
        }
        return $returnStr;
    }



    //
    /**
     * 170106
     * 商品列表页 goods.php使用
     * 获得名字列表 如：类目一>>类目二>> 这样的形式
     *
     * @return string
     */
    function GetPositionName()
    {
        $returnStr = "";
        $newTypeInfoArray = GetTypeInfoBeforeArray($this->typeInfoArrayS, $this->selTypeId);//获取包含当前ID的所有父分类的信息数组
        if (is_array($newTypeInfoArray)) {
            foreach ($newTypeInfoArray as $keyp => $valuep) {
                $typeInfoArray = $newTypeInfoArray[$keyp];
                $typename = base64_decode($typeInfoArray['typename']);
                $returnStr .= " $typename > ";
            }
            $returnStr = trim($returnStr, " > ");
        }
        return $returnStr;
    }


    /**
     * 170106
     * 商品列表页 goods.php使用
     * 获得某id的所有下级id,供SQL查询使用
     *
     * 包含当前ID自身
     * 先获取所有的分类信息,然后根据当前设定的标志,获取下级分类ID
     *
     * @return 格式1,2
     */
    function GetGoodsSonIds()
    {
        $returnStr = "";
        $newTypeInfoArray = GetTypeInfoAfterArray($this->typeInfoArrayS, $this->selTypeId);//获取包含当前ID的所有子分类的信息数组
        if (is_array($newTypeInfoArray)) {
            foreach ($newTypeInfoArray as $keyp => $valuep) {
                $typeInfoArray = $newTypeInfoArray[$keyp];
                $typeid = $typeInfoArray['id'];
                $returnStr .= "$typeid,";
            }
            $returnStr = trim($returnStr, ",");
        }
        return $returnStr;
    }






    //
    /**170106
     *获取所有栏目的商品数量
     */
    function updateCatalogNum()
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->dsql->SetQuery("SELECT typeid,count(typeid) as dd FROM `#@__goods`  group by typeid");
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $this->catalogGoodsNumsArray[$row['typeid']] = $row['dd'];
        }
    }

    /**
     * 170106
     * 商品列表页 goods_del.php使用,判断删除的分类是否包含商品
     * 获取指定分类下的商品数量
     *
     * @param $tid
     *
     * @return int|mixed
     */
    function GetTotalGoods($tid)
    {
        // dump($tid);
        if (!is_array($this->catalogGoodsNumsArray)) {
            $this->UpdateCatalogNum();
        }
        if (!isset($this->catalogGoodsNumsArray[$tid])) {
            return 0;
        } else {
            $totalnum = 0;
            $ids = explode(',', GetArchiveSonIds($tid));
            foreach ($ids as $tid) {
                if (isset($this->catalogGoodsNumsArray[$tid])) {
                    $totalnum += $this->catalogGoodsNumsArray[$tid];
                }
            }
            return $totalnum;
        }
    }

    /**
     * 170106
     * 分类管理页面，输出页面显示
     *
     * @param int $channel
     * @param int $nowdir
     */
    function ListAllType($channel = 0, $nowdir = 0)
    {
        //获取其他的分类
        $arrayData = $this->typeInfoArrayS;
        $type_str = "";
        if (is_array($arrayData)) {
            foreach ($arrayData as $keyp => $valuep) {
                $typeInfoArray = $arrayData[$keyp];
                //dump($typeInfoArray);
                $typename = base64_decode($typeInfoArray['typename']);
                $id = $typeInfoArray['id'];
                $step = $typeInfoArray['step'];//层级
                $ispart = $typeInfoArray['ispart'];


                if ($step == 0) {
                    $type_str = "<ol class='dd-list'>\r\n";
                    $type_str .= "    <li class='dd-item'>\r\n";
                    $type_str .= "        <div class='dd-handle'><span class='label label-info'></span>$typename [ID:$id]\r\n";
                    if ($ispart == 1) $type_str .= "                <font color=\"#999999\">(频道封面) </font> ";
                    $type_str .= "                                    <span class='pull-right'>\r\n";
                    $type_str .= "                                   <a href='catalog_add.php?id={$id}'>增加子类</a>&nbsp;&nbsp;";
                    $type_str .= "                                    <a href='catalog_edit.php?id={$id}'>编辑</a>&nbsp;&nbsp;";
                    $type_str .= "                                   <a href='catalog_del.php?id={$id}&typeoldname=" . urlencode($typename) . "'>删除</a>";
                    $type_str .= "                                   </span></div>\r\n";
                    echo $type_str;

                    $this->logicListAllSunType($id, $step + 1);
                    echo "</li>\r\n</ol>\r\n";
                }
            }
        }
    }


    /**
     *  获得子类目的递归调用
     *
     * @access    public
     *
     * @param     int    $id   分类ID
     * @param     string $step 层级标志
     *
     * @return    void
     */
    function logicListAllSunType($id, $step_temp)
    {
        $newTypeInfoArray = GetTypeInfoAfterArray($this->typeInfoArrayS, $id);//获取包含当前ID的所有子分类的信息数组
        if (is_array($newTypeInfoArray)) {
            foreach ($newTypeInfoArray as $keyp => $valuep) {
                $typeInfoArray = $newTypeInfoArray[$keyp];
                $typename = base64_decode($typeInfoArray['typename']);
                $id = $typeInfoArray['id'];
                $step = $typeInfoArray['step'];//层级
                $ispart = $typeInfoArray['ispart'];

                if ($step == $step_temp) {
                    $type_str = "<ol class='dd-list'>\r\n";
                    $type_str .= "    <li class='dd-item'>\r\n";

                    $type_str .= "        <div class='dd-handle'><span class='label label-info'></span>$typename [ID:$id]\r\n";
                    if ($ispart == 1) $type_str .= "                <font color=\"#999999\">(频道封面) </font> ";
                    $type_str .= "                                    <span class='pull-right'>\r\n";
                    $type_str .= "                                   <a href='catalog_add.php?id={$id}'>增加子类</a>&nbsp;&nbsp;";
                    $type_str .= "                                    <a href='catalog_edit.php?id={$id}'>编辑</a>&nbsp;&nbsp;";
                    $type_str .= "                                   <a href='catalog_del.php?id={$id}&typeoldname=" . urlencode($typename) . "'>删除</a>";
                    $type_str .= "                                   </span></div>\r\n";
                    echo $type_str;
                    $this->logicListAllSunType($id, $step + 1);
                    echo "</li>\r\n</ol>\r\n";
                }
            }
        }
    }


    //返回当前栏目是否频道封面
    function GetIspart()
    {
        return $this->ispart;
    }


}//End Class









