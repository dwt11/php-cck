<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: channel_edit.php 1 14:49 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC."/dwttag.class.php");

if(empty($dopost)) $dopost="";
$id = isset($id) && is_numeric($id) ? $id : 0;


/*------------
function __SaveEdit()
------------*/
 if($dopost=="save")
{

    $query = "Update `#@__archives_channeltype` set
    typename = '$typename',
    addcon = '$addcon',
    mancon = '$mancon',
    editcon = '$editcon'
    where id='$id' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功更改一个模型！","channel.php");
    exit();
}



$row = $dsql->GetOne("SELECT * FROM `#@__archives_channeltype` WHERE id='$id' ");


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title><?php echo $sysFunTitle?></title>
<link href="../css/base.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.STYLE2 { color: #666666;  }
-->
</style>

</head>
<body background='../images/allbg.gif' leftmargin='8' topmargin='8'>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6">
  <tr>
    <td height="30" background="../images/tbg.gif" bgcolor="#E7E7E7" style="padding-left:20px;"><b><?php echo $sysFunTitle?></b></td>
  </tr>
  <tr>
    <td  align="center" valign="top" bgcolor="#FFFFFF">
    <form name="form1" action="" method="post">
        <input type='hidden' name='id' value='<?php echo $id?>'>
        <input type='hidden' name='dopost' value='save'>
        <table width="100%" border="0"  cellspacing="0" cellpadding="0" style="text-align:left;background:#ffffff;">
          <tr>
            <td  width="10%" class="bline" align="right">模型ID：</td>
            <td  class="bline" >
	           <?php echo $row['id']; ?>
            <br />
              <span class="STYLE2" id='help1'>数字，创建后不可更改，并具有唯一性。</span> </td>
          </tr>
          <tr>
            <td class="bline" align="right">名字标识：</td>
            <td  class="bline" ><?php echo $row['nid']; ?><br />
              <span class="STYLE2" id="help2">与文档的模板相关连，建议由英文、数字或下划线组成，因为部份Unix系统无法识别中文文件，模型默认文档模板是 “default/article_名字标识.htm”，列表模板、封面模板类推。 </span></td>
          </tr>
          <tr>
            <td class="bline" align="right">模型名称：</td>
            <td  class="bline" ><input name="typename" type="text" id="typename" value="<?php echo $row['typename']; ?>" class="pubinputs" /><br />
              <span class="STYLE2" id="help3">模型的中文名称，在后台管理，前台发布等均使用此名字。</span></td>
          </tr>
          <tr>
            <td class="bline" align="right">附加表：</td>
            <td  class="bline" ><?php echo $row['addtable']; ?><br />
              <span class="STYLE2" id="help4">模型除主表以外其它自定义类型数据存放数据的表。</span>
              ( #@__ 是表示数据表前缀)</td>
          </tr>
          <tr>
            <td class="bline" align="right">发布程序：</td>
            <td  class="bline" ><input name="addcon" type="text" id="addcon" value="<?php echo $row['addcon']; ?>" class="pubinputs" /></td>
          </tr>
          <tr>
            <td class="bline" align="right">修改程序：</td>
            <td  class="bline" ><input name="editcon" type="text" id="editcon" value="<?php echo $row['editcon']; ?>" class="pubinputs" /></td>
          </tr>
          <tr>
            <td class="bline" align="right">管理程序：</td>
            <td  class="bline" ><input name="mancon" type="text" id="mancon" value="<?php echo $row['mancon']; ?>" class="pubinputs" /></td>
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