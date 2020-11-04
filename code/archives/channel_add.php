<?php
/**
 * 自定义模型
 *
 * @version        $Id: channel_add.php 1 14:46 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC."/dwttag.class.php");
if(empty($action)) $action = '';

if($action=='add')
{
    //检查填写
    if(empty($id) || preg_match("#[^0-9-]#", $id))
    {
        ShowMsg("<font color=red>'模型id'</font>必须为数字！","-1");
        exit();
    }
    if(preg_match("#[^a-z0-9]#i", $nid) || $nid == "")
    {
        ShowMsg("<font color=red>'模型名字标识'</font>必须为英文字母或与数字混合字符串！","-1");
        exit();
    }
    if($addtable == "")
    {
        ShowMsg("附加表不能为空！","-1");
        exit();
    }
    $trueTable2 = str_replace("#@__",$cfg_dbprefix,$addtable);


    //检查id是否重复
    $row = $dsql->GetOne("SELECT * FROM #@__archives_channeltype WHERE id='$id' OR nid LIKE '$nid' OR addtable LIKE '$addtable'");
    if(is_array($row))
    {
        ShowMsg("可能‘模型id’、‘模型名称标识’、‘附加表名称’在数据库已存在，不能重复使用！","-1");
        exit();
    }
    $mysql_version = $dsql->GetVersion();

    //创建附加表
    if($trueTable2!='')
    {
           $tabsql = "CREATE TABLE `$trueTable2`(
                      `archivesid` int(11) NOT NULL default '0',
                    `typeid` int(11) NOT NULL default '0',
           ";
            if($mysql_version < 4.1)
            {
                $tabsql .= "    PRIMARY KEY  (`archivesid`), KEY `typeid` (`typeid`)\r\n) TYPE=MyISAM; ";
            }
            else
            {
                $tabsql .= "    PRIMARY KEY  (`archivesid`), KEY `typeid` (`typeid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
            }
            $rs = $dsql->ExecuteNoneQuery($tabsql);
            if(!$rs)
            {
                ShowMsg("创建附加表失败!".$dsql->GetError(),"javascript:;");
                exit();
            }
    }

    $fieldset = '';
   
    $inQuery = "INSERT INTO `#@__archives_channeltype`(id,nid,typename,addtable,addcon,mancon,editcon,fieldset)
    VALUES ('$id','$nid','$typename','$addtable','$addcon','$mancon','$editcon','$fieldset');";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("成功增加一个模型！", "channel.php");
    exit();
}
$row = $dsql->GetOne("SELECT id FROM `#@__archives_channeltype`   ORDER BY   id DESC LIMIT 0,1 ");
$newid = $row['id'] + 1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title><?php echo $sysFunTitle?></title>
<link href="../css/base.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.STYLE2 {
	color: #666666;
}
-->
</style>
</head>
<body background='../images/allbg.gif' leftmargin='8' topmargin='8'>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6">
  <tr>
    <td height="30" background="../images/tbg.gif" bgcolor="#E7E7E7" style="padding-left:20px;"><b><?php echo $sysFunTitle?></b></td>
  </tr>
  <tr>
    <td  align="center" valign="top" bgcolor="#FFFFFF"><form name="form1" action="channel_add.php" method="post">
        <input type='hidden' name='action' value='add'>
        <table width="100%" border="0"  cellspacing="0" cellpadding="0" style="text-align:left;background:#ffffff;">
          <tr>
            <td  width="35%" class="bline" align="right">模型ID：</td>
            <td  class="bline" ><input name="id" type="text" id="id" size="10" value="<?php echo $newid; ?>" class="pubinputs" />
              * </td>
          </tr>
          <tr>
            <td class="bline" align="right">名字标识：</td>
            <td class="bline" ><input name="nid" type="text" id="nid" value="ch<?php echo $newid; ?>" class="pubinputs" />
              * <br />
              <span class="STYLE2" id="help2">与文档的模板相关连，建议由英文、数字或下划线组成，因为部份Unix系统无法识别中文文件，模型默认文档模板是 “default/article_名字标识.htm”，列表模板、封面模板类推。 </span></td>
          </tr>
          <tr>
            <td class="bline" align="right">模型名称：</td>
            <td class="bline" ><input name="typename" type="text" id="typename" value="模型<?php echo $newid; ?>" class="pubinputs" />
              * <br />
              <span class="STYLE2" id="help3">模型的中文名称，在后台管理，前台发布等均使用此名字。</span></td>
          </tr>
          <tr>
            <td class="bline" align="right">附加表：</td>
            <td class="bline" ><input name="addtable" type="text" id="addtable" value="<?php echo $cfg_dbprefix,'archives_addon',$newid; ?>" class="pubinputs" />
              必须由英文、数字、下划线组成 * <br />
              <span class="STYLE2" id="help4">模型除主表以外其它自定义类型数据存放数据的表，如果您不使用主表关连的各种特性(推荐、会员权限等)，也可以使用完全以附加表作为存储数据。</span></td>
          </tr>
          <tr>
            <td class="bline" align="right">发布程序：</td>
            <td class="bline" ><input name="addcon" type="text" id="addcon" value="archives_add.php" class="pubinputs" />
              * </td>
          </tr>
          <tr>
            <td class="bline" align="right">修改程序：</td>
            <td class="bline" ><input name="editcon" type="text" id="editcon" value="archives_edit.php" class="pubinputs" />
              * </td>
          </tr>
          <tr>
            <td class="bline" align="right">管理程序：</td>
            <td class="bline" ><input name="mancon" type="text" id="mancon" value="archives.php" class="pubinputs" />
              * </td>
          </tr>
          <tr  bgcolor="#F9FCEF">
            <td height="45"></td>
            <td><input name="imageField" type="image" src="../images/button_ok.gif" width="60" height="22" class="np" border="0" style="cursor:pointer">
              <img src="../images/button_reset.gif" width="60" height="22" border="0" onClick="location.reload();" style="cursor:pointer"></td>
          </tr>
        </table>
      </form></td>
  </tr>
</table>
</body>
</html>