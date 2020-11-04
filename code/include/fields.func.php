<?php if (!defined('DWTINC')) exit('dwtx');
/**
 *根据字段 输出自定义的表单相关代码
 * 自定义表单相关操作
 *
 * @version        $Id: fields.func.php 150930
 * @package
 * @copyright
 * @license
 * @link
 */

/**
 *  获得一个附加表单(发布时用)
 *
 * @access    public
 * @param     object $ctag 标签
 * @return    string
 */

global $formitem_temp_row, $formitem_temp_col;  //只在此页使用 ，添加\编辑\详细信息显示页 的HTML格式

//按行显示   添加 编辑 页面使用 表单名称在左 选项在右
$formitem_temp_row = " <div class=\"form-group\">
                            <label class=\"col-xs-2 control-label\">~name~：</label>
                            <div class=\"col-xs-2\">
                                ~form~
                            </div>
                        </div>   ";
//按列显示 前台用户微信界面显示   表单名称第一行  选项第二行
$formitem_temp_col = " <div class=\"list-group-item \">
                           <p>
                                <span class=\"label label-success\">~indexnumb~</span> ~name~
                            </p>
                            <p>
                                    ~form~
                            </p>
                       </div>
                       <br>";

/**
 *  获得表单  搜索、 添加、编辑时使用170112
 *
 * @access    public
 *
 * @param     object  $ctag     标签
 * @param string      $fvalue   当前值
 * @param bool $issearch 如果是搜索的，则radio checkbox的也输出为select
 * @param     string  $display  显示方式  151212增加 row按行显示  col按列显示
 *
 * @return string
 */
function GetFormItem($ctag, $fvalue = '', $issearch = false, $display = 'row')
{
    global $formitem_temp_row, $formitem_temp_col;
    $formitem_temp = $formitem_temp_row;
    if ($display == "col") $formitem_temp = $formitem_temp_col;
    $fieldname = $ctag->GetName();
    $ftype = $ctag->GetAtt('type');
    $itemname = $ctag->GetAtt('itemname');
    $formitem = $formitem_temp;
    $innertext = trim($ctag->GetInnerText());

    if ($innertext != '') {
        $formitem = $innertext;
    }
    $myformItem = '';
    if (preg_match("/select|radio|checkbox/i", $ftype)) {
        $items = explode(',', $ctag->GetAtt('default'));
    }
    if ($ftype == 'select' || (preg_match("/radio|checkbox/i", $ftype) && $issearch)) {
        $myformItem = "<select name='$fieldname' style='width:150px'  class='form-control'>";
        $myformItem .= "<option value='' >请选择$itemname</option>\r\n";
        if (is_array($items)) {
            foreach ($items as $v) {
                $v = trim($v);
                if ($v == '') continue;
                $myformItem .= ($fvalue == $v ? "<option value='$v' selected>$v</option>\r\n" : "<option value='$v'>$v</option>\r\n");
            }
        }
        $myformItem .= "</select>\r\n";
        $innertext = $myformItem;
    } else if ($ftype == 'depselect') {
        $myformItem = '';
        $depOptions = GetDepOptionListRole($fvalue);
        $myformItem .= "<select name='depid' id='depid'  class='form-control' >\r\n";
        $myformItem .= "<option value='0'>请选择部门...</option>\r\n";
        $myformItem .= $depOptions;
        $myformItem .= "</select>\r\n";
        $innertext = $myformItem;
    } else if ($ftype == 'empselect') {
        //150305增加 员工选择
        require_once(DWTPATH . "/emp/emp.inc.options.php");
        $myformItem = '';
        $EmpOptions = GetEmpOptionList($fvalue);
        $myformItem .= "<div style='width:280px'><span style='color:#999; float:right'>支持编号\汉字\拼音首字母搜索</span><select name='empid' id='empid'  m='search'  class='form-control'>\r\n";
        $myformItem .= "<option value='0' >请选择员工...</option>\r\n";
        $myformItem .= $EmpOptions;
        $myformItem .= "</select> \r\n
                        <!--//select界面选择框-->
                        <script type=\"text/javascript\" src=\"../js/jquery/jquery.selectseach.min.js\"></script>
                            <script>
                                function getmydata(){
                                    alert($('#sssss').val());
                                }
                                $(document).ready(function(){
                                    $('select').selectseach();
                                });
                            </script>
                        </div>";
        $innertext = $myformItem;
    } else if ($ftype == 'plantselect') {
        require_once(DWTPATH . "/device/plant.inc.options.php");
        $myformItem = '';
        $plantOptions = GetPlantOptionList($fvalue);
        $myformItem .= "<select name='$fieldname' id='$fieldname'  class='form-control' >\r\n";
        $myformItem .= "<option value='0'>请选择装置...</option>\r\n";
        $myformItem .= $plantOptions;
        $myformItem .= "</select>\r\n";
        $innertext = $myformItem;
    } else if ($ftype == 'stepselect' || (preg_match("/stepradio|stepcheckbox/i", $ftype) && $issearch)) {
        //select型的数据字典或  是搜索时的表单，则输出select
        require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
        $myformItem = GetEnumsForm($fieldname, $fvalue, $fieldname, $seltitle = '', 'select');
        $innertext = $myformItem;
    } else if (preg_match("/stepradio|stepcheckbox/i", $ftype) && !$issearch) {
        //数据字典radio和CHECKBOX，在非搜索表单下，输出 对应的表单
        require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
        $radirOrcheckbox="";
        if ($ftype == "stepradio") $radirOrcheckbox = "radio";
        if ($ftype == "stepcheckbox") $radirOrcheckbox = "checkbox";
        $myformItem = GetEnumsForm($fieldname, $fvalue, $fieldname, $seltitle = '', $radirOrcheckbox);
        $innertext = $myformItem;
    } else if ($ftype == 'radio' && !$issearch) {
        if (is_array($items)) {
            $items_i=0;
            foreach ($items as $v) {
                $items_i++;
                $v = trim($v);
                if ($v == '') continue;
                $checked="";
                if($fvalue==""){

                    //如果值为空,则第一个选中
                    if($items_i==1)$checked=" checked='checked' ";
                }else{
                    //如果有值,则是选中的
                    if($fvalue == $v)$checked=" checked='checked' ";
                }
                $myformItem_1 =  "<input type='radio' name='$fieldname'   value='$v' $checked />$v\r\n";
                $myformItem .= "<label class=\"checkbox-inline i-checks  \">".$myformItem_1."</label>";
            }
        }
        $innertext = $myformItem;
    } else if ($ftype == 'checkbox' && !$issearch) {
        $myformItem = '';
        $fvalues = explode(',', $fvalue);
        if (is_array($items)) {
            foreach ($items as $v) {
                $v = trim($v);
                if ($v == '') {
                    continue;
                }
                if (in_array($v, $fvalues)) {
                    $myformItem_1 = "<input type='checkbox' name='{$fieldname}[]'  value='$v' checked='checked' />$v\r\n";
                } else {
                    $myformItem_1 = "<input type='checkbox' name='{$fieldname}[]'   value='$v' />$v\r\n";
                }
                $myformItem .= "<label class=\"checkbox-inline i-checks  \">".$myformItem_1."</label>";
            }
        }
        $innertext = $myformItem;
    } else if ($ftype == "htmltext") {
        $myformItem = GetEditor("$fieldname", "$fvalue");
        $innertext = $myformItem;
    } else if ($ftype == "multitext") {
        $innertext = "<textarea name='$fieldname' id='$fieldname' style='width:30%;height:80px'>$fvalue</textarea>\r\n";
    } //日期类型和时间类型分开151119
    else if ($ftype == "date") {
        $nowtime = GetDateMk($fvalue);
        $innertext = "<input type=\"text\" name=\"$fieldname\" size=\"12\" value=\"$nowtime\"   class=\"form-control Wdate\"  onfocus=\"WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})\"/>";
    } else if ($ftype == "datetime") {
        $nowtime = GetDateTimeMk($fvalue);
        $innertext = "<input type=\"text\" name=\"$fieldname\" size=\"23\" value=\"$nowtime\"   class=\"form-control Wdate\"  onfocus=\"WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd H:m:s'})\"/>";
    } else if ($ftype == "img") {
        $ndtp = new DwtTagParse();
        $ndtp->LoadSource($fvalue);
        if (!is_array($ndtp->CTags)) {
            $ndtp->Clear();
            $fvalue = "";
        } else {
            $ntag = $ndtp->GetTag("img");
            $fvalue = trim($ntag->GetInnerText());
        }
        $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' style='width:300px'  class='text' /> <input name='" . $fieldname . "_bt' class='inputbut' type='button' value='浏览...' onClick=\"SelectImage('form1.$fieldname','big')\" />\r\n";
    } else if ($ftype == "int" ) {
        $innertext = "<input type=\"number\" class=\"form-control\" name='$fieldname' id='$fieldname' style='width:100px'    value='$fvalue' /> \r\n";
    }  else if ( $ftype == "float") {
        $innertext = "<input type=\"number\"  step=\"0.01\" class=\"form-control\" name='$fieldname' id='$fieldname' style='width:100px'    value='$fvalue' /> \r\n";
    } else {
        $innertext = "<input type='text'   class=\"form-control\" name='$fieldname' id='$fieldname'   value='$fvalue' />\r\n";
    }
    if ($issearch) {
        //如果搜索的，则直接输入表单
        $formitem = "<div class=\"pull-left\">" . $innertext . "</div>";
    } else {
        //如果添加和编辑的，则要加上样式
        $formitem = str_replace('~name~', $itemname, $formitem);
        $formitem = str_replace('~form~', $innertext, $formitem);
    }
    return $formitem;
}



/**
 *
 * 170112
 *  处理不同类型的数据
 *  保存表单内容时使用  将扩展内容里的值 转换为合适的值
 *
 * @access    public
 *
 * @param     string $dvalue    默认值
 * @param     string $dtype     默认类型
 * @param     int    $aid       文档ID
 * @param     string $job       操作类型
 * @param     string $addvar    值
 * @param     string $fieldname 变量类型
 *
 * @return    string
 */
function GetFieldValue($dvalue, $dtype, $aid = 0, $job = 'add', $addvar = '', $fieldname = '')
{
    global $cfg_basedir;

    if ($dtype == 'int') {
        if ($dvalue == '') {
            return 0;
        }
        return GetAlabNum($dvalue);
    } else if ($dtype == 'stepselect') {
        $dvalue = trim(preg_replace("#[^0-9\.]#", "", $dvalue));
        return $dvalue;
    } else if ($dtype == 'float') {
        if ($dvalue == '') {
            return 0;
        }
        return GetAlabNum($dvalue);
    } else if ($dtype == 'datetime' || $dtype == 'date') {
        if ($dvalue == '') {
            return 0;
        }
        return GetMkTime($dvalue);
    } else if ($dtype == 'checkbox') {
        $okvalue = '';
        if (is_array($dvalue)) {
            $okvalue = join(',', $dvalue);
        }
        return $okvalue;
    } else if ($dtype == 'stepcheckbox')//141120增加 数据字典的checkbox保存值时 获取多先的值
    {
        $okvalue = '';
        if (is_array($dvalue)) {
            $okvalue = join(',', $dvalue);
        }
        return $okvalue;
    } //部门类型
    else if ($dtype == 'depselect') {
        //$okvalue = GetDepsNameByDepId($dvalue);   //151014注释掉此处  部门选择 员工选择  装置选择 后直接返回对应的ID,  在列表处根据ID获取值
        return $dvalue;
    } //员工类型
    else if ($dtype == 'empselect') {
        //$okvalue = GetEmpNameById($dvalue);//151014注释掉此处  部门选择 员工选择  装置选择 后直接返回对应的ID,  在列表处根据ID获取值
        return $dvalue;
    }    //装置名称
    else if ($dtype == 'plantselect') {
        //require_once(DWTPATH."/device/plant.inc.options.php");//151014注释掉此处  部门选择 员工选择  装置选择 后直接返回对应的ID,  在列表处根据ID获取值
        //$okvalue = GetPlantNameById($dvalue);
        return $dvalue;
    } else if ($dtype == "htmltext") {
        return $dvalue;
    } else if ($dtype == "multitext") {
        return $dvalue;
    } else if ($dtype == 'img' || $dtype == 'imgfile') {
        $iurl = stripslashes($dvalue);
        if (trim($iurl) == '') {
            return '';
        }
        $iurl = trim(str_replace($GLOBALS['cfg_basehost'], "", $iurl));
        $imgurl = "{dwt:img text='' width='' height=''} " . $iurl . " {/dwt:img}";
        if (preg_match("/^http:\/\//i", $iurl) && $GLOBALS['cfg_isUrlOpen']) {
            //远程图片
            $reimgs = '';
            if ($GLOBALS['cfg_isUrlOpen']) {
                $reimgs = GetRemoteImage($iurl, $adminid);
                if (is_array($reimgs)) {
                    if ($dtype == 'imgfile') {
                        $imgurl = $reimgs[1];
                    } else {
                        $imgurl = "{dwt:img text='' width='" . $reimgs[1] . "' height='" . $reimgs[2] . "'} " . $reimgs[0] . " {/dwt:img}";
                    }
                }
            } else {
                if ($dtype == 'imgfile') {
                    $imgurl = $iurl;
                } else {
                    $imgurl = "{dwt:img text='' width='' height=''} " . $iurl . " {/dwt:img}";
                }
            }
        } else if ($iurl != '') {
            //站内图片
            $imgfile = $cfg_basedir . $iurl;
            if (is_file($imgfile)) {
                $info = '';
                $imginfos = GetImageSize($imgfile, $info);
                if ($dtype == "imgfile") {
                    $imgurl = $iurl;
                } else {
                    $imgurl = "{dwt:img text='' width='" . $imginfos[0] . "' height='" . $imginfos[1] . "'} $iurl {/dwt:img}";
                }
            }
        }
        return addslashes($imgurl);
    } else {
        return $dvalue;
    }
}



/**
 *  获得扩展字段的值(管理列表页使用)
 *170112
 * @access    public
 *
 * @param     object $ctag     标签
 * @param     mixed  $fvalue   变量值
 *
 * @param bool       $isrow    是否行显示  默认是false 就是列显示  用于列表页显示;  true的话行显示,用于详细信息页,每个扩展内容以行显示
 * @param string     $display  如果isrow为true 此值为 row  则名称 和答案按两行显示   为 col则按两列显示
 *                             如果isrow为false 则此不起作用
 *
 *
 * @return string
 */
function GetListItem($ctag, $fvalue, $isrow = false, $display = 'row')
{
//dump($ctag);
    global $formitem_temp_row, $formitem_temp_col;
    $listitem = "";
    if ($isrow && $display == "row") $listitem_temp = $formitem_temp_row;  //行显示,用于详细信息页,每个扩展内容以行显示
    if ($isrow && $display == "col") $listitem_temp = $formitem_temp_col;

    $fieldname = $ctag->GetName();
    $itemname = $ctag->GetAtt('itemname');

    $innertext = trim($ctag->GetInnerText());   //如果tag模板有值  则使用tag的模板,这个怎么设定 随后再找141229

    if ($innertext != '') {
        $listitem = $innertext;
    }
    //dump($listitem."---");
    $ftype = $ctag->GetAtt('type');
    $mylistitem = '';
    //if (preg_match("/select|radio|checkbox/i", $ftype)) {
    //    $items = explode(',', $ctag->GetAtt('default'));
    //}

    //如果是 select radio checkbox 则直接输出数据库的值
    if ($ftype == 'select' || $ftype == 'radio' || $ftype == 'checkbox') {

        $mylistitem .= $fvalue;
        $innertext = $mylistitem;
    } else if ($ftype == 'depselect') {
        //部门类型
        $mylistitem = GetDepsNameByDepId($fvalue);
        $innertext = $mylistitem;
    } else if ($ftype == 'empselect') {
        //员工类型
        $mylistitem = GetEmpNameById($fvalue);
        $innertext = $mylistitem;
    } else if ($ftype == 'plantselect') {
        //装置名称
        require_once(DWTPATH . "/device/plant.inc.options.php");
        $mylistitem = GetPlantNameById($fvalue);
        $innertext = $mylistitem;
    } else if ($ftype == 'stepselect' || $ftype == 'stepradio' || $ftype == 'stepcheckbox') {
        //dump($fieldname);
        require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
        $mylistitem = GetEnumsValue($fieldname, $fvalue);
        $innertext = $mylistitem;
    } else if ($ftype == "date") {
        //日期类型和时间类型分开 151119
        $innertext = GetDateMk($fvalue);
    } else if ($ftype == "datetime") {
        $innertext = GetDateTimeMk($fvalue);
    } else {
        $innertext = $fvalue;
    }

    //以下这些暂时没用 后期看列表中是否需要这些功能项
    /*    else if($ftype=="htmltext")
        {
            //现在的功能是列表显示  如果后期需要显示的时候要转为纯文本 并截取一定的字数
            //$mylistitem = GetEditor("body","$fvalue");
            //$innertext = $mylistitem;
        }
        else if($ftype=="multitext")
        {
            //$innertext = "<textarea name='$fieldname' id='$fieldname' style='width:90%;height:80px'>$fvalue</textarea>\r\n";
        }
        else if($ftype=="img")
        {
           $ndtp = new DwtTagParse();
            $ndtp->LoadSource($fvalue);
            if(!is_array($ndtp->CTags))
            {
                $ndtp->Clear();
                $fvalue =  "";
            }
            else
            {
                $ntag = $ndtp->GetTag("img");
                $fvalue = trim($ntag->GetInnerText());
            }
            $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' style='width:300px'  class='text' /> <input name='".$fieldname."_bt' class='inputbut' type='button' value='浏览...' onClick=\"SelectImage('form1.$fieldname','big')\" />\r\n";
       }
        else if($ftype=="imgfile")
        {
           // $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' style='width:300px'  class='text' /> <input name='".$fieldname."_bt' class='inputbut' type='button' value='浏览...' onClick=\"SelectImage('form1.$fieldname','big')\" />\r\n";
        }
        else if($ftype=="media")
        {
            //$innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' style='width:300px'  class='text' /> <input name='".$fieldname."_bt' class='inputbut' type='button' value='浏览...' onClick=\"SelectMedia('form1.$fieldname')\" />\r\n";
        }
        else if($ftype=="addon")
        {
            //$innertext = "<input type='text' name='$fieldname' id='$fieldname' value='$fvalue' style='width:300px'  class='text' /> <input name='".$fieldname."_bt' class='inputbut' type='button' value='浏览...' onClick=\"SelectSoft('form1.$fieldname')\" />\r\n";
        }
    */

//dump($listitem);
    //增加150311 当简单列表的列表页面显示时,如果自由字段是部门的,则设定不能换行,其他的可以换行
    if ($ftype == 'depselect') {
        $listitem = str_replace('~nowrap~', "nowrap", $listitem);
    } else {
        $listitem = str_replace('~nowrap~', "", $listitem);
    }
    $listitem = str_replace("~indexnumb~", $ctag->GetAtt('index_numb'), $listitem);  //替换计数   //151212添加 只有WEB前面显示显示
    $listitem = str_replace("~name~", $ctag->GetAtt('itemname'), $listitem);
    $listitem = str_replace('~value~', $innertext, $listitem);

    //dump($innertext);
    if ($isrow) {
        //如果按行分隔显示
        $listitem = str_replace('~name~', $itemname, $listitem);
        $listitem = str_replace('~form~', $innertext ,$listitem);
    } else {
        //如果按列分隔
        $listitem = "<td>" . $innertext . "</td>";

    }


    return $listitem;
}



