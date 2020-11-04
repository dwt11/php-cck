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
require_once(DWTINC . "/dwttag.class.php");
require_once(DWTINC . "/oxwindow.class.php");

if (empty($dopost)) $dopost = "";
$id = isset($id) && is_numeric($id) ? $id : 0;


/*------------
function __SaveEdit()
------------*/
if ($dopost == "save") {
    $fieldset = preg_replace("#[\r\n]{1,}#", "\r\n", $fieldset);

    $query = "Update `#@__archives_channeltype` set
    fieldset = '$fieldset'
    where id='$id' ";
    if (trim($fieldset) != '') {
        $dtp = new DwtTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource(stripslashes($fieldset));
        if (!is_array($dtp->CTags)) {
            ShowMsg("文本配置参数无效，无法进行解析！", "-1");
            exit();
        }
    }
    $dsql->ExecuteNoneQuery($query);


    ShowMsg("成功更改一个模型的字段！", "channel_field.php?id=$id ");
    exit();
}


$row = $dsql->GetOne("SELECT * FROM `#@__archives_channeltype` WHERE id='$id' ");


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <title><?php echo $sysFunTitle ?></title>
    <script src="../js/jquery.min.js"></script>

    <script src="../js/main.js"></script>
    <link href="../css/base.css" rel="stylesheet" type="text/css">
</head>
<body background='../images/allbg.gif' leftmargin='8' topmargin='8'>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6">
    <tr>
        <td height="30" background="../images/tbg.gif" bgcolor="#E7E7E7" style="padding-left:20px;">
            <b><?php echo $sysFunTitle ?> <?php echo $row['typename']; ?>模型包含的字段</b></td>
    </tr>
    <tr>
        <td align="center" valign="top" bgcolor="#FFFFFF">
            <form name="form1" action="channel_field.php" method="post">
                <input type='hidden' name='id' value='<?php echo $id ?>'>
                <input type='hidden' name='dopost' value='save'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="text-align:left;background:#ffffff;">
                    <tr>
                        <td height="28" bgcolor="#ffffff">
                            <div class="toolbox">
                                <a href="channel_field_add.php?id=<?php echo $id; ?>" class='np coolbg'>增加字段</a></div>
                        </td>
                        <td bgcolor="#ffffff"></td>
                    </tr>
                    <tr>
                        <td colspan='2' bgcolor="#FFFFFF" style="padding:6px">
                            <table width="100%" border="0" cellpadding="1" cellspacing="1" align="center" style="background:#cfcfcf;">
                                <tr align="center" bgcolor="#FBFCE2" height="24">
                                    <td width="28%">提示文字</td>
                                    <td width="18%">字段名</td>
                                    <td width="20%">数据类型</td>
                                    <td width="18%">表单类型</td>
                                    <td></td>
                                </tr>
                                <?php


                                //170105这里换成了 dedeinc下的数组，在商品里新的代码 要替换这里
                                $ds = file(DWTDATA . '/fieldtype.txt');
                                foreach ($ds as $d) {
                                    $dds = explode(',', trim($d));
                                    $fieldtypes[$dds[0]] = $dds[1];
                                }
                                $fieldset = $row['fieldset'];
                                $dtp = new DwtTagParse();
                                $dtp->SetNameSpace("field", "<", ">");
                                $dtp->LoadSource($fieldset);
                                if (is_array($dtp->CTags)) {
                                    foreach ($dtp->CTags as $ctag) {
                                        ?>
                                        <tr align="center" bgcolor="#FFFFFF" height="26" onMouseMove="javascript:this.bgColor='#FCFDEE';" onMouseOut="javascript:this.bgColor='#FFFFFF';">
                                            <td><?php
                                                $itname = $ctag->GetAtt('itemname');
                                                if ($itname == '') echo "没指定";
                                                else echo $itname;
                                                ?></td>
                                            <td><?php echo $ctag->GetTagName(); ?></td>
                                            <td><?php
                                                $ft = $ctag->GetAtt('type');
                                                if (isset($fieldtypes[$ft])) echo $fieldtypes[$ft];
                                                else  echo "未知";
                                                ?></td>
                                            <td><?php
                                                $ft = $ctag->GetAtt('autofield');
                                                if ($ft == '' || $ft == 0) {
                                                    echo "固化字段";
                                                } else {
                                                    echo "自动表单";
                                                }
                                                ?></td>
                                            <td>
                                                <a href='channel_field_edit.php?id=<?php echo $id; ?>&fieldname=<?php echo $ctag->GetTagName(); ?>'>更改</a>

                                                <?php if ($ft == 1) { ?>
                                                    <a href='#' onClick='javascript:isdel("channel_field_edit.php?fname=<?php echo $ctag->GetTagName(); ?>&action=delete&id=<?php echo $id; ?>");'>删除</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="24" width="35%" bgcolor="#FFFFFF"><strong>模型字段配置(文本模式) </strong><br/>
                            修改配置文本可调整字段顺序，<strong>但不会更改字段属性</strong>！
                        </td>
                        <td width="65%" bgcolor="#FFFFFF">
                            <textarea name="fieldset" style="width:99%;height:300px" rows="10" id="fieldset"><?php echo $row['fieldset']; ?></textarea>
                        </td>
                    </tr>
                    <tr bgcolor="#F9FCEF">
                        <td height="45"></td>
                        <td>
                            <input name="imageField" type="image" src="../images/button_ok.gif" width="60" height="22" class="np" border="0" style="cursor:pointer">
                            <img src="../images/button_reset.gif" width="60" height="22" border="0" onClick="location.reload();" style="cursor:pointer">
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>