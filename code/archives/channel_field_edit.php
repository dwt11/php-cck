<?php
/**
 * 自定义模型,字段编辑
 *
 * @version        $Id: channel_field_edit.php 1 15:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC."/dwttag.class.php");

if(empty($action)) $action = '';
if(empty($fieldname)) $fieldname = '';


$id = isset($id) && is_numeric($id) ? $id : 0;
$mysql_version = $dsql->GetVersion();




//获取模型信息
$row = $dsql->GetOne("SELECT fieldset,'' as maintable,addtable FROM `#@__archives_channeltype` WHERE id='$id'");
$fieldset = $row['fieldset'];
$trueTable = $row['addtable'];

$dtp = new DwtTagParse();
$dtp->SetNameSpace("field", "<", ">");
$dtp->LoadSource($fieldset);
foreach($dtp->CTags as $ctag)
{
    if(strtolower($ctag->GetName())==strtolower($fieldname)) break;
}
//170105这里换成了 dedeinc下的数组，在商品里新的代码 要替换这里

//字段类型信息
$ds = file(DWTDATA.'/fieldtype.txt');
foreach($ds as $d)
{
    $dds = explode(',', trim($d));
    $fieldtypes[$dds[0]] = $dds[1];
}





//保存更改
/*--------------------
function _SAVE()
----------------------*/
if($action=='save')
{
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



    //获取附加表中旧的字段的信息
    $fields = array();
    $rs = $dsql->SetQuery("SHOW fields FROM `{$row['addtable']}`");
    $dsql->Execute('a');
    while($nrow = $dsql->GetArray('a',MYSQL_ASSOC))
    {
        $fields[ strtolower($nrow['Field']) ] = $nrow['Type'];
    }

    
  
     //如果旧的字段类型和新的字段类型不一样 在附加表中修改字段类型
     if(isset($fields[$fieldname]) && $fields[$fieldname] != $buideType)
     {
		  $tabsql = "ALTER TABLE `$trueTable` CHANGE `$fieldname` ".$ntabsql;
          //dump($tabsql);
		  $rs = $dsql->ExecuteNoneQuery($tabsql);
		  if(!$rs)
		  {
			  $gerr = $dsql->GetError();
			  ShowMsg("修改字段失败，错误提示为：".$gerr,"javascript:;");
			  exit();
		  }

     }


    //检测旧数据类型，并替换为新配置
    foreach($dtp->CTags as $tagid=>$ctag)
    {
        if($fieldname==strtolower($ctag->GetName()))
        {
			//dump($fieldstring);
            $dtp->Assign($tagid, stripslashes($fieldstring), FALSE);
            break;
        }
    }
    $oksetting = $dtp->GetResultNP();

   //dump("UPDATE `#@__archives_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");
    $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__archives_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");


    if(!$rs)
    {
        $grr = $dsql->GetError();
        ShowMsg("保存模型的字段信息配置出错！".$grr, "javascript:;");
        exit();
    }
    
    ShowMsg("成功更改一个字段！","channel_field.php?id={$id}&dopost=edit");
    exit();
}
/*------------------
删除字段
function _DELETE()
-------------------*/
else if($action=="delete")
{

    //检测旧数据类型，并替换为新配置
    foreach($dtp->CTags as $tagid=>$ctag)
    {
        if(strtolower($ctag->GetName()) == strtolower($fname))
        {
            $dtp->Assign($tagid, "#@Delete@#");
        }
    }
    
    $oksetting = addslashes($dtp->GetResultNP());
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");
    $dsql->ExecuteNoneQuery("ALTER TABLE `$trueTable` DROP `$fname` ");
    ShowMsg("成功删除一个字段！","channel_field.php?id={$id}&dopost=edit");
    exit();
}








?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>更改字段</title>
<link href="../css/base.css" rel="stylesheet" type="text/css" />
<script language="javascript">
function GetFields()
{
	var theform = document.form1;
	var fieldname = theform.fieldname.value;
	var itemname = theform.itemname.value;
	var dtype = theform.dtype.value;
	var vdefault = theform.vdefault.value;
	var maxlength = theform.maxlength.value;
	var autofield = (theform.autofield[0].checked ? theform.autofield[0].value : theform.autofield[1].value);
	
	if(itemname=="")
	{
		alert("表单提示名称不能为空！");
		theform.itemname.focus();
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
  <form name="form1" action="" method="post" onSubmit="return GetFields();">
  	<input type='hidden' name='action' value='save' />
  	<input type='hidden' name='id' value='<?php echo $id?>' />
  	<input type='hidden' name='fieldname' value='<?php echo $fieldname?>' />
	
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
      	<input name="itemname" type="text" id="itemname" value="<?php echo $ctag->GetAtt('itemname')?>" class="pubinputs">
        *（发布内容时显示的项名字）      </td>
    </tr>
          <tr>
            <td  class="bline" align="right"><strong>字段名称：</strong></td>
      <td  class="bline"> 
        <?php echo $fieldname?>     </td>
    </tr>
	<tr>
            <td class="bline" align="right"><strong>字段显示：</strong></td>
      <td class="bline">
	  <input name="autofield" type="radio" class="np" value="1" checked="checked"<?php echo ($ctag->GetAtt('autofield')==1 ? " checked":"");  ?>/>
              在前台内容页和后台添加编辑页自动加载此字段
      <input name="autofield" type="radio" class="np" value="0"<?php echo ( ($ctag->GetAtt('autofield')==''||$ctag->GetAtt('autofield')=='0') ? " checked":"");  ?>/>
              选中此项需要在前台模板和后台添加编辑页中手动设置此字段 </td>
    </tr>
    <tr> 
      <td  class="bline" align="right"><strong>数据类型：</strong></td>
      <td  class="bline">
      	<select name="dtype" id="type" style="width:200px">
          <?php 
          $dtype = $ctag->GetAtt('type');
          if($dtype!='' && isset($fieldtypes[$dtype]))
          {
          	echo "          <option value='{$dtype}'>{$fieldtypes[$dtype]}</option>\r\n";
          	$canchange = true;
          }
          else
          {
          	echo "          <option value='{$dtype}'>未知</option>\r\n";
          	$canchange = false;
          }
          if($canchange)
          {
          ?>
          <option value="text">单行文本(varchar)</option>
          <option value="textchar">单行文本(char)</option>
          <option value="multitext">多行文本</option>
          <option value="htmltext">HTML文本</option>
          <option value="int">整数类型</option>
          <option value="float">小数类型</option>
              <option value="date">日期类型</option>
              <option value="datetime">时间类型</option>
 <!--         <option value="img">图片</option>
          <option value="imgfile">图片(仅网址)</option>
          <option value="media">多媒体文件</option>
          <option value="addon">附件类型</option>
-->
          <option value="select">使用select下拉框</option>
          <option value="radio">使用radio选项卡</option>
          <option value="checkbox">Checkbox多选框</option>
          <option value="stepselect">数据字典select下拉框</option>
          <option value="stepradio">数据字典radio选项卡</option>
          <option value="stepcheckbox">数据字典checkbox选项卡</option>
          <?php
           }
          ?>
        </select>        </td>
    </tr>
    <tr> 
            <td   class="bline" align="right"><strong>默认值：</strong></td>
 <td class="bline">
<textarea name="vdefault" type="text" id="vdefault" style="width:70%;height:60px"><?php echo $ctag->GetAtt('default'); ?></textarea>
              <br>
              <span class="STYLE2"> 如果定义数据类型为select、radio、checkbox时，此处填写被选择的项目(用“,”分开，如“男,女,保密”)。 </span></td>
    </tr>
    <tr> 
            <td  class="bline" align="right"><strong>最大长度：</strong></td>
            <td class="bline">
      	<input name="maxlength" type="text" id="maxlength" value="<?php echo $ctag->GetAtt('maxlength')?>" style="width:80px;height:24px;padding-top:3px;">
	
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