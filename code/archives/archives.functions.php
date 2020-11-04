<?php
/**
 * 文档操作相关函数
 *
 * @version        $Id: inc_archives_functions.php 1 9:56 21日
 * @package
 * @copyright
 * @license
 * @link
 */



/**
 *  载入自定义表单(用于发布)
 *
 * @access    public
 * @param     string  $fieldset  字段列表
 * @param     string  $loadtype  载入类型
 * @return    string
 */
function PrintAutoFieldsAdd(&$fieldset, $loadtype='all')
{
    //dump ($fieldset);
    $dtp = new DwtTagParse();
    $dtp->SetNameSpace('field','<','>');
    $dtp->LoadSource($fieldset);
    $x_addonfields = '';
    if(is_array($dtp->CTags))
    {
        foreach($dtp->CTags as $tid=>$ctag)
        {
            if($loadtype!='autofield'
            || ($loadtype=='autofield' && $ctag->GetAtt('autofield')==1) )
            {
                $x_addonfields .= ( $x_addonfields=="" ? $ctag->GetName().",".$ctag->GetAtt('type') : ";".$ctag->GetName().",".$ctag->GetAtt('type') );
                //dump($ctag);
				echo  GetFormItem($ctag);
            }
        }
    }
    echo "<input type='hidden' name='dwt_addonfields' value=\"".$x_addonfields."\">\r\n";
}

/**
 *  载入自定义表单(用于编辑)
 *
 * @access    public
 * @param     string  $fieldset  字段列表
 * @param     string  $fieldValues  字段值
 * @param     string  $loadtype  载入类型
 * @return    string
 */
function PrintAutoFieldsEdit(&$fieldset, &$fieldValues, $loadtype='all')
{
   // dump($fieldValues);
    $dtp = new DwtTagParse();
    $dtp->SetNameSpace("field", "<", ">");
    $dtp->LoadSource($fieldset);
    $x_addonfields = "";
    if(is_array($dtp->CTags))
    {
        foreach($dtp->CTags as $tid=>$ctag)
        {
            if($loadtype != 'autofield'
            || ($loadtype == 'autofield' && $ctag->GetAtt('autofield') == 1) )
            {
                $x_addonfields .= ( $x_addonfields=='' ? $ctag->GetName().",".$ctag->GetAtt('type') : ";".$ctag->GetName().",".$ctag->GetAtt('type') );
                echo GetFormItemValue($ctag, $fieldValues[$ctag->GetName()]);
            }
        }
    }

    echo "<input type='hidden' name='dwt_addonfields' value=\"".$x_addonfields."\">\r\n";
}




/**
 *  检测频道ID
 *
 * @access    public
 * @param     int  $typeid  栏目ID
 * @param     int  $channelid  频道ID
 * @return    bool
 */
function CheckChannel($typeid, $channelid)
{
    global $dsql;
    if($typeid==0) return TRUE;

    $row = $dsql->GetOne("SELECT ispart,channeltype FROM `#@__archives_type` WHERE id='$typeid' ");
    if($row['ispart']!=0 || $row['channeltype'] != $channelid) return FALSE;
    else return TRUE;
}







/**
 *  取第一个图片为缩略图
 *
 * @access    public
 * @param     string  $body  文档内容
 * @return    string
 */
function GetDDImgFromBody(&$body)
{
    $litpic = '';
    //170131修改只获取 本地uploads下的图片
    $body=stripslashes($body);//移除转义符
    preg_match_all("/(src)=[\"|']\/uploads\/{0,}([^>]*\.(gif|jpg|bmp|png))/isU",$body,$img_array);
    $img_array = array_unique($img_array[2]);


    if (count($img_array) > 0) {
        $litpic = preg_replace("/[\"|'| ]{1,}/", '', $img_array[0]);

    }

    if (strpos($litpic, "ueditor")) $litpic = "";   //如果地址含有 编辑器默认的图标,则返回空 (不输出)
    if($litpic!="")$litpic="/uploads".$litpic;
    return $litpic;
}





    /**
     *  获得子工种的递归调用   添加编辑 页面
     *
     * @access    public
     * @param     int  $id  工种ID
     * @param     string  $arcrow  arc中的值
     * @return    void
     */
    function LogicListAllSunWorktype($id,$arcwroktype="")
    {
		$dsql = $GLOBALS['dsql'];
        $fid = $id;
        $dsql->SetQuery("SELECT * FROM `#@__emp_worktype` WHERE worktype_topid='".$id."' ORDER BY worktype_id");
        $dsql->Execute($fid);
		if($dsql->GetTotalRow($fid)>0)
        {
            while($row = $dsql->GetObject($fid))
            {
				$checked="";
                $worktype_name = $row->worktype_name;
                $topid = $row->worktype_topid;
                $id = $row->worktype_id;


					//echo"<img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='/images/explode.gif' width='11' height='11'>";

			    if(in_array($id,explode(",",$arcwroktype)))$checked="checked='checked'";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<input name='worktype[]' $checked type='checkbox' class='np'  value='$id'>".$worktype_name;
                LogicListAllSunWorktype($id);
            }
        }
    }












