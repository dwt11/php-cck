<?php
/**
 * 自定义模型字段添加
 *
 * @version        $Id: channel_field_add.php 1 15:07 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC."/dwttag.class.php");

if(empty($action)) $action = '';
$mysql_version = $dsql->GetVersion();

/*----------------------
function Save()
---------------------*/
if($action=='save')
{
			//dump($fieldstring);
    
    $dfvalue = trim($vdefault);
    $mxlen = $maxlength;
    if(preg_match("#^(select|radio|checkbox)$#i", $dtype))
    {
        if(!preg_match("#,#", $dfvalue))
        {
            ShowMsg("你设定了字段为 {$dtype} 类型，必须在默认值中指定元素列表，如：'a,b,c' ","-1");
            exit();
        }
    }
    
    if($dtype=='stepselect'||$dtype=='stepradio'||$dtype=='stepcheckbox')   //141216修复BUG,原无stepcheckbox
    {
        $arr = $dsql->GetOne("SELECT * FROM `#@__sys_stepselect` WHERE egroup='$fieldname' ");
        if(!is_array($arr))
        {
            ShowMsg("你设定了字段为数字字典类型，但系统中没找到与你定义的字段名相同的组名!","-1");
            exit();
        }
    }



    //模型信息,获取附加表的名称和一些参数
    $row = $dsql->GetOne("SELECT fieldset,addtable FROM `#@__archives_channeltype` WHERE id='$id'");
    $fieldset = $row['fieldset'];
    $dtp = new DwtTagParse();
    $dtp->SetNameSpace("field", "<", ">");
    $dtp->LoadSource($fieldset);
    $trueTable = $row['addtable'];

    //获取 新的字段的 SQL语句 和类型
    $fieldinfos = GetFieldMake($dtype, $fieldname, $dfvalue, $mxlen);
    $ntabsql = $fieldinfos[0];//创建用的SQL语言
    $buideType = $fieldinfos[1];//新的字段类型
	//dump($buideType);
    //在附加表中创建新的字段
    $rs = $dsql->ExecuteNoneQuery(" ALTER TABLE `$trueTable` ADD  $ntabsql ");
    if(!$rs)
    {
        $gerr = $dsql->GetError();
        ShowMsg("增加字段失败，错误提示为：".$gerr,"javascript:;");
        exit();
    }

    //检测模型的字段旧配置信息，并替换为新配置
    $ok = FALSE;
    $fieldname = strtolower($fieldname);
    if(is_array($dtp->CTags))
    {
        foreach($dtp->CTags as $tagid=>$ctag)
        {
            if($fieldname == strtolower($ctag->GetName()))
            {
                $dtp->Assign($tagid, stripslashes($fieldstring), FALSE);
                $ok = true;
                break;
            }
        }
        $oksetting = $ok ? $dtp->GetResultNP() : $fieldset."\n".stripslashes($fieldstring);
    }
    else
    {
        $oksetting = $fieldset."\r\n".stripslashes($fieldstring);
    }
    $oksetting = addslashes($oksetting);
//dump("UPDATE `#@__archives_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");
    $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__archives_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");


    if(!$rs)
    {
        $grr = $dsql->GetError();
        ShowMsg("保存模型的字段信息配置出错！".$grr, "javascript:;");
        exit();
    }
    
    ShowMsg("成功增加一个字段！", "channel_field.php?id={$id}&dopost=edit");
    exit();
}







//$trueTable = $row['addtable'];
//$tabsql = "CREATE TABLE IF NOT EXISTS  `$trueTable`( `archivesid` int(11) NOT NULL default '0',\r\n `typeid` int(11) NOT NULL default '0',\r\n ";
//if($mysql_version < 4.1)
//{
//    $tabsql .= " PRIMARY KEY  (`archivesid`), KEY `".$trueTable."_index` (`typeid`)\r\n) TYPE=MyISAM; ";
//}
//else
//{
//    $tabsql .= " PRIMARY KEY  (`archivesid`), KEY `".$trueTable."_index` (`typeid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
//}
//$dsql->ExecuteNoneQuery($tabsql);




//检测模型相关信息，并初始化相关数据
$row = $dsql->GetOne("SELECT '#@__archives' AS maintable,addtable FROM `#@__archives_channeltype` WHERE id='$id'");

//检测主表里已经含有的字段，如果添加的附加表的字段名称和主表名称重复 则提示
$fields = array();
if(empty($row['maintable'])) $row['maintable'] = '#@__archives';
$rs = $dsql->SetQuery("SHOW fields FROM `{$row['maintable']}`");
$dsql->Execute('a');
while($nrow = $dsql->GetArray('a', MYSQL_ASSOC))
{
    if(!isset($fields[strtolower($nrow['Field'])]))
    {
        $fields[strtolower($nrow['Field'])] = 1;
    }
}
$f = '';
foreach($fields as $k=>$v)
{
    $f .= ($f=='' ? $k : ' '.$k);
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>增加字段</title>
<link href="../css/base.css" rel="stylesheet" type="text/css">
<script language="javascript">
var notAllow = " <?php echo $f; ?> ";
function GetFields()
{
	var theform = document.form1;
	var fieldname = theform.fieldname.value;
	var itemname = theform.itemname.value;
	var dtype = 'text';
	var enums = document.getElementsByName('dtype');
	for(i=0;i<enums.length;i++)
	{
		if(enums[i].checked) dtype = enums[i].value;
	}
	var vdefault = theform.vdefault.value;
	var maxlength = theform.maxlength.value;
	var autofield = (theform.autofield[0].checked ? theform.autofield[0].value : theform.autofield[1].value);
	
	if(itemname=="")
	{
		alert("表单提示名称不能为空！");
		theform.itemname.focus();
		return false;
	}
	
	if(fieldname=="") 
	{
		alert("字段名称不能为空！");
		theform.fieldname.focus();
		return false;
	}
	
	if(notAllow.indexOf(" "+fieldname+" ") >-1 ) 
	{
		alert("字段名称不合法，如下字段名已经存在，建议在名称后加上数字以区分：\n"+notAllow);
		return false;
	}
	
	if((dtype=="radio" || dtype=="select" || dtype=="checkbox") && vdefault=="")
	{
		alert("你选择的select或radio、checkbox类型，必须默认值设置选择的项目（用逗号[,]分开）！");
		return false;
	}
	
	
	revalue =  "<field:"+fieldname+" itemname=\""+itemname+"\" autofield=\""+autofield+"\"  type=\""+dtype+"\"";
	revalue += "  default=\""+vdefault+"\" ";
	revalue += " maxlength=\""+maxlength+"\">\r\n</field:"+fieldname+">\r\n";
	document.form1.fieldstring.value = revalue;
 
  return true;
  
}
</script>
<style type="text/css">
<!--
td {
	padding: 2px;
	padding-left: 6px;
	line-height: 150%;
}
.STYLE2 {
	color: #666666
}
.cls {
	clear: both;
}
-->
</style>
</head>
<body background='../images/allbg.gif' leftmargin='8' topmargin='8'>
<form name="form1" action="channel_field_add.php" method="post" onSubmit="return GetFields();">
  <input type='hidden' name='action' value='save' />
  <input type='hidden' name='id' value='<?php echo $id?>' />
  <input type='hidden' name='fieldstring' value='' />
  <table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6">
    <tr>
      <td height="30" background="../images/tbg.gif" bgcolor="#E7E7E7" style="padding-left:20px;"><b><?php echo $sysFunTitle?></b></td>
    </tr>
    <tr>
      <td  align="center" valign="top" bgcolor="#FFFFFF"><table width="100%" border="0"  cellspacing="0" cellpadding="0" style="text-align:left;background:#ffffff;">
          <tr>
            <td colspan="2" bgcolor="#FFFFFF"   class="bline" ><div style="color:red">
            <img src='../images/ico/help.gif' />所有填写的内容不允许包含双引号[<strong>&quot;</strong>]，否则配置将无法写入。
            <br/><img src='../images/ico/help.gif' />模型中只可以加入一个 HTML文本 的字段类型。
            
            </div></td>
          </tr>
          <tr>
            <td  width="10%" class="bline" align="right"><strong>表单提示文字：</strong></td>
            <td class="bline">
	    <input name="itemname" type="text" id="itemname" class="pubinputs" />
              *<br>
              <span class="STYLE2">发布内容时显示的提示文字</span></td>
          </tr>
          <tr>
            <td  class="bline" align="right"><strong>字段名称：</strong></td>
            <td class="bline"><input name="fieldname" type="text" id="fieldname" class="pubinputs" />
              * <br>
              <span class="STYLE2"> 只能用英文字母或数字，数据表的真实字段名，如果数据类型是数据字典类型，该项应该填写数据字典的<a href='<?php  echo $GLOBALS['cfg_install_path']?>/sys/sysStepSelect.php' target='_blank'><u>[英文组名称]</u></a>。 </span></td>
          </tr>
          <tr>
            <td class="bline" align="right"><strong>字段显示：</strong></td>
            <td class="bline"><input name="autofield" type="radio" value="1" class="np" checked="checked" />
              在前台内容页和后台添加编辑页自动加载此字段
              <input type="radio" name="autofield" class="np" value="0" />
              选中此项需要在前台模板和后台添加编辑页中手动设置此字段 </td>
          </tr>
          <tr>
            <td  class="bline" align="right"><strong>数据类型：</strong></td>
            <td class="bline"><div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype1' value="text" checked='1'>
                单行文本(varchar)</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype2' value="textchar">
                单行文本(char)</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype3' value="multitext">
                多行文本</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype4' value="htmltext">
                HTML文本</div>
              <br class='cls' />
             
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype6' value="int">
                整数类型</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype7' value="float">
                小数类型</div>
                <div class='nw200'>
                    <input type='radio' class='np' name='dtype' id='dtype8' value="date">
                    日期类型</div>
                <div class='nw200'>
                    <input type='radio' class='np' name='dtype' id='dtype8' value="datetime">
                    时间类型</div>
              <!-- <br class='cls' />
             <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype9' value="img">
                图片</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype10' value="imgfile">
                图片(仅网址)</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype11' value="media">
                多媒体文件</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype12' value="addon">
                附件类型</div>-->
              <br class='cls' />
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype13' value="select">
                使用select下拉框</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype14' value="radio">
                使用radio选项卡</div>
              <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype15' value="checkbox">
                Checkbox多选框</div>
                <br class='cls' />
            <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype16' value="stepselect">
                数据字典select下拉框</div>
            <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype16' value="stepradio">
                数据字典radio选项卡</div>
            <div class='nw200'>
                <input type='radio' class='np' name='dtype' id='dtype17' value="stepcheckbox">
                数据字典checkbox选项卡</div>
                
                </td>
          </tr>
          <tr>
            <td   class="bline" align="right"><strong>默认值：</strong></td>
            <td class="bline">
	    <textarea name="vdefault" type="text" id="vdefault" style="width:70%;height:60px"></textarea>
              <br>
              <span class="STYLE2"> 如果定义数据类型为select、radio、checkbox时，此处填写被选择的项目(用“,”分开，如“男,女,保密”)。 </span></td>
          </tr>
          <tr>
            <td   class="bline" align="right"><strong>最大长度：</strong></td>
            <td class="bline">
	    <input name="maxlength" type="text" id="maxlength" class="pubinputs" value="250" style="width:80px;" />
              <br>
              <span class="STYLE2"> 文本数据必须填写，大于255为text类型 </span></td>
          </tr>
          <tr  bgcolor="#F9FCEF">
            <td height="45"></td>
            <td><input  type="submit" name="Submit"  value="确定" class="coolbg np" />
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
</body>
</html>