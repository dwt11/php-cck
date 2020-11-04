<?php   if(!defined('DWTINC')) exit("Request Error!");
/**
 * 频道模型单元类
 * @version        $Id: channelunit.class.php
 * @package
 * @copyright
 * @license
 * @link
 */
require_once(DWTINC."/dwttag.class.php");
//require_once("archives.channelunit.func.php");
if(!isset($cfg_mainsite)) extract($GLOBALS, EXTR_SKIP);
global $pTypeArrays,$idArrary,$envs,$v1,$v2;

$pTypeArrays = $idArrary = $PubFields = $envs = array();
$envs['typeid'] = 0;
$envs['reid'] = 0;
$envs['aid'] = 0;
$envs['idlist'] = '';

/*----------------------------------
function C____ChannelUnit();
-----------------------------------*/
class ChannelUnit
{
    var $ChannelInfos;
    var $ChannelFields;
    var $AllFieldNames;
    var $ChannelID;
    var $ArcID;
    var $dsql;
    var $SplitPageField;

    //php5构造函数
    function __construct($typeid,$aid=0)
    {
        $this->ChannelInfos = '';
        $this->ChannelFields = '';
        $this->AllFieldNames = '';
        $this->SplitPageField = '';
        $this->ChannelID = $typeid;
        $this->ArcID = $aid;
        $this->dsql = $GLOBALS['dsql'];
        $sql = " SELECT * FROM `#@__archives_channeltype` WHERE id='$typeid' ";
        $this->ChannelInfos = $this->dsql->GetOne($sql);
        if(!is_array($this->ChannelInfos))
        {
            echo '读取频道信息失败，无法进行后续操作！';
            exit();
        }
        $dtp = new DwtTagParse();
        $dtp->SetNameSpace('field','<','>');
        $dtp->LoadSource($this->ChannelInfos['fieldset']);
        if(is_array($dtp->CTags))
        {
            $tnames = Array();
            foreach($dtp->CTags as $ctag)
            {
                $tname = $ctag->GetName();
                if(isset($tnames[$tname]))
                {
                    break;
                }
                $tnames[$tname] = 1;
                if($this->AllFieldNames!='')
                {
                    $this->AllFieldNames .= ','.$tname;
                }
                else
                {
                    $this->AllFieldNames .= $tname;
                }
                if(is_array($ctag->CAttribute->Items))
                {
                    $this->ChannelFields[$tname] = $ctag->CAttribute->Items;
                }
                $this->ChannelFields[$tname]['value'] = '';
                $this->ChannelFields[$tname]['innertext'] = $ctag->GetInnerText();
                if(empty($this->ChannelFields[$tname]['itemname']))
                {
                    $this->ChannelFields[$tname]['itemname'] = $tname;
                }
                if($ctag->GetAtt('page')=='split')
                {
                    $this->SplitPageField = $tname;
                }
            }
        }
        $dtp->Clear();
    }

    function ChannelUnit($typeid,$aid=0)
    {
        $this->__construct($typeid,$aid);
    }

    /**
     *  设置档案ID
     *
     * @access    private
     * @param     int   $aid  档案ID
     * @return    void
     */
    function SetArcID($aid)
    {
        $this->ArcID = $aid;
    }

    /**
     *  处理某个字段的值
     *
     * @access    public
     * @param     string  $fname  字段名称
     * @param     string  $fvalue  字段值
     * @param     string  $addvalue  增加值
     * @return    string
     */
    function MakeField($fname, $fvalue, $addvalue='')
    {        
        //dump($fvalue);
		//处理各种数据类型
        $ftype = $this->ChannelFields[$fname]['type'];
        if($fvalue=='')
        {
            if($ftype != 'checkbox') $fvalue = $this->ChannelFields[$fname]['default'];
        }
        if($ftype=='datetime')
        {
            $fvalue = MyDate('Y-m-d H:i',$fvalue);
        }

        if($ftype=='text')
        {
            $fvalue = HtmlReplace($fvalue);
        }
        else if($ftype=='addon')
        {
            $foldvalue = $fvalue;
            $tmptext = GetSysTemplets("channel_addon.htm");
            $fvalue  = str_replace('~link~',$foldvalue,$tmptext);
            $fvalue  = str_replace('~phpurl~',$GLOBALS['cfg_phpurl'],$fvalue);
        }
        else if(file_exists(DWTINC.'/taglib/channel/'.$ftype.'.lib.php'))
        {
            include_once(DWTINC.'/taglib/channel/'.$ftype.'.lib.php');
            $func = 'ch_'.$ftype;
            $fvalue = $func($fvalue,$addvalue,$this,$fname);
        }
        return $fvalue;
    }
    
    /**
     *  获取缩略图链接
     *
     * @access    public
     * @param     string  $fvalue  表单值
     * @return    string
     */
    function GetlitImgLinks($fvalue)
    {
        if($GLOBALS["htmltype"]=="dm"){
            if(empty($GLOBALS["pageno"])) $NowPage = 1;
            else $NowPage = intval($GLOBALS["pageno"]);
        }else{
            if(empty($GLOBALS["stNowPage"])) $NowPage = 1;
            else $NowPage = intval($GLOBALS["stNowPage"]);
        }
        
        $revalue = "";
        $dtp = new DwtTagParse();
        $dtp->LoadSource($fvalue);
        if(!is_array($dtp->CTags)){
            $dtp->Clear();
            return "无图片信息！";
        }
        $ptag = $dtp->GetTag("pagestyle");
        if(is_object($ptag)){
            $pagestyle = $ptag->GetAtt('value');
            $maxwidth = $ptag->GetAtt('maxwidth');
            $ddmaxwidth = $ptag->GetAtt('ddmaxwidth');
            $irow = $ptag->GetAtt('row');
            $icol = $ptag->GetAtt('col');
            if(empty($maxwidth)) $maxwidth = $GLOBALS['cfg_album_width'];
        }else{
            $pagestyle = 2;
            $maxwidth = $GLOBALS['cfg_album_width'];
            $ddmaxwidth = 200;
        }
        if($pagestyle == 3){
          if(empty($irow)) $irow = 4;
          if(empty($icol)) $icol = 4;
        }
        $mrow = 0;
        $mcol = 0;
        $photoid = 1;
        $images = array();
        $TotalPhoto = sizeof($dtp->CTags);
    
        foreach($dtp->CTags as $ctag){
            if($ctag->GetName()=="img")
            {
                $iw = $ctag->GetAtt('width');
                $ih = $ctag->GetAtt('heigth');
                $alt = str_replace("'","",$ctag->GetAtt('text'));
                $src = trim($ctag->GetInnerText());
                $ddimg = $ctag->GetAtt('ddimg');
                if($iw > $maxwidth) $iw = $maxwidth;
                $iw = (empty($iw) ? "" : "width='$iw'");
                if($GLOBALS["htmltype"]=="dm") {
                    $imgurl = "view.php?aid=$this->ArcID&pageno=$photoid";
                }else{
                    if($photoid==1){
                        $imgurl = $GLOBALS["fileFirst"].".html";
                    }else{
                        $imgurl = $GLOBALS["fileFirst"]."_".$photoid.".html";
                    }
                }
                $imgcls = "image".($photoid-1);
                $revalue .= "<dl><dt>$alt<dd>$ddimg<dd>$ddimg<dd>$ddimg<dd><dd><div></div><div></div><dd><dd>$photoid</dd></dl>\r\n";
                $photoid++;
            }        
        }    
        unset($dtp);
        unset($images);
        return $revalue;        
    }

    //关闭所占用的资源
    function Close()
    {
    }

}//End  class ChannelUnit