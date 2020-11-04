<?php if (!defined('DWTINC')) exit("Request Error!");
require_once(DWTINC . "/dwttag.class.php");
/**
 *
 *  自动加载 指定分类的扩展数据到列表页面
 */

/**
 *
 * function
 *
 * @package          DedeTag
 * @subpackage
 * @link
 */
class AutoField
{

    var $fieldTag_array = array();
    var $fieldnames;   //SQL语句中的字段名称
    var $itemnames;    //HTml表头显示
    var $addtable;    //附加表名称
    var $templist;    //列表模板名称
    var $fieldnamArray = array();//用于列表显示获取值
    var $searchfrom;  //表单代码
    var $searchselectnameArray = array();    //搜索的select单独字段名称  单独 and SQL语句
    var $searchinputnameArray = array();    //搜索的input共用字段名称  OR SQL语句

    function __construct($channeltypeid = 0)
    {
        //获取已经存在的字段信息    //这段在多处使用  以后看是否合并到field.function.php 141217
        global $dsql;
        //global $fieldset_tmp;
        if (!$channeltypeid > 0) return "";

        //获取模型ID
        //$row = $dsql->GetOne("SELECT channeltype FROM `#@__goods_type` WHERE id='$typeid'");
        //$channeltypeid = $row['channeltype'];
        $sql = "SELECT fieldset,addtable,templist FROM `#@__sys_channeltype` WHERE id='$channeltypeid'";
        //dump($sql);
        $row = $dsql->GetOne($sql);
        if (!is_array($row)) return "";
        $this->addtable = $row['addtable'];
        $this->templist = $row['templist'];
        $fieldset = $row['fieldset'];
        //dump($fieldset);
        if ($fieldset == "") return "";
        $dtp = new DwtTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource($fieldset);
        $this->fieldTag_array = $dtp->CTags;
        if (!is_array($this->fieldTag_array)) return "";
        foreach ($this->fieldTag_array as $ctag) {
            //dump($ctag);
            $tagname = $ctag->GetTagName();
            $this->fieldnames .= ",`$tagname`";//字段名称,用于SQL搜索
            $this->itemnames .= "<th>" . $ctag->GetAtt('itemname') . "</th>\r\n";    //字段说明文字
            $this->fieldnamArray[] = $tagname;
            //判断是否搜索
            $issearch = $ctag->GetAtt('issearch');
            if ($issearch == 1) {
                $type = $ctag->GetAtt('type');
                //如果是数据字典等类型的值，则使用select选择框搜索
                if (preg_match("#^(select|radio|checkbox|depselect|stepselect|stepradio|stepcheckbox)$#i", $type)) {
                    $this->searchselectnameArray[] = $tagname;    //搜索的select单独字段名称
                } else {
                    $this->searchinputnameArray[] = $tagname;    //搜索的input共用字段名称  OR SQL语句
                }
            }
        }

    }

    function AutoField($typeid = 0)
    {
        $this->__construct($typeid);
    }


    /**
     * 返回搜索用的表单 获取搜索的相关信息
     *
     * @param        $mainSearchTis 主表单搜索的提示文字
     *
     * @param string $fieldValues 值
     *
     * @return string
     * @internal param array $valueArray 值的数组
     *
     */
    function GetSearchFrom($mainSearchTis, &$fieldValues = "")
    {
        $return_str = "";
        if (!is_array($this->fieldTag_array)) return "";
        foreach ($this->fieldTag_array as $ctag) {
            $tagname = $ctag->GetTagName();
            $value = "";
            if (isset($fieldValues[$tagname])) $value = $fieldValues[$tagname];
            $itemname = $ctag->GetAtt("itemname");
            //判断是否搜索
            $issearch = $ctag->GetAtt('issearch');
            if ($issearch == 1) {
                $type = $ctag->GetAtt('type');
                //如果是数据字典等类型的值，则使用select选择框搜索
                if (preg_match("#^(select|radio|checkbox|depselect|stepselect|stepradio|stepcheckbox)$#i", $type)) {
                    $return_str .= GetFormItem($ctag, $value, true);
                } else {
                    //input类型的模糊搜索
                    $mainSearchTis .= "/" . $itemname;
                }
            }
        }

        $value = "";
        if (isset($valueArray["keyword"])) $value = $valueArray["keyword"];
        $return_str .= " <div class=\"pull-left\">
                                    <input type='text' name='keyword' value='$value' class='form-control' placeholder='$mainSearchTis'   data-toggle='tooltip' data-placement='bottom'  data-html=\"true\"  title=\"<p align='left'>$mainSearchTis</p>\"/>
                       </div>";
        return $return_str;

    }


    /**
     * 170112
     *  处理某个设备扩展字段的值  用于列表显示  多条设备信息
     *
     * @access    public
     *
     * @param            $fieldname 字段名称
     * @param     string $fvalue 字段值
     *
     * @return string
     *
     *
     */
    function MakeFieldValueToCol($fieldname, $fvalue)
    {
        //dump($fvalue);
        //处理各种数据类型
        $fvalue = HtmlReplace($fvalue);
        if (!is_array($this->fieldTag_array)) return "";
        foreach ($this->fieldTag_array as $ctag) {
            if (($fieldname) == $ctag->GetTagName()) {
                $fvalue_str = GetListItem($ctag, $fvalue);
                break;
            }
        }

        return $fvalue_str;
    }


    /**
     * 170112
     *  载入自定义表单(用于编辑添加的表单)
     *
     * @access    public
     *
     * @param     string $fieldValues 字段值
     *
     * @return string
     *
     */
    function MakeFieldToForm(&$fieldValues = "")
    {

        if (!is_array($this->fieldTag_array)) return "";
        //dump($this->fieldTag_array);
        $dwt_addonfields = $form_str = "";
        foreach ($this->fieldTag_array as $tid => $ctag) {
            $dwt_addonfields .= ($dwt_addonfields == '' ? $ctag->GetName() . "," . $ctag->GetAtt('type') : ";" . $ctag->GetName() . "," . $ctag->GetAtt('type'));
            $value = "";
            if (isset($fieldValues[$ctag->GetName()])) $value = $fieldValues[$ctag->GetName()];
            echo GetFormItem($ctag, $value);
        }
        echo "<input type='hidden' name='dwt_addonfields' value=\"" . $dwt_addonfields . "\">\r\n";

    }


}



    