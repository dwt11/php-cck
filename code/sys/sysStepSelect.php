<?php
/**
 * 联动选择管理
 *
 * @version        $Id: sys_stepselect.php 2 13:23 2011-3-24 tianya $
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once(DWTINC . "/datalistcp.class.php");
require_once(DWTINC . '/enums.func.php');
/*-----------------
前台视图
function __show() { }
------------------*/
$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
$$ENV_GOBACK_URL = (isset($$ENV_GOBACK_URL) ? $$ENV_GOBACK_URL : 'sysStepSelect.php');
if (empty($dopost)) {
    setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
    if (!isset($egroup)) $egroup = '';
    if (!isset($reevalue)) $reevalue = '';
    $etypes = array();
    $egroups = array();
    $dsql->Execute('me', 'SELECT * FROM `#@__sys_stepselect`   ORDER BY   id DESC');
    while ($arr = $dsql->GetArray()) {
        $etypes[] = $arr;
        $egroups[$arr['egroup']] = $arr['itemname'];
    }

    //获取所有的 类别组 供 option选择显示使用
    $selgroup = ""; //当前选中的类别组名
    $egroupOptionS = "<option value='0'>--所有组--</option>";
    foreach ($etypes as $arr) {
        $stylecolor = "";
        if ($arr['issystem'] == 1) $stylecolor = " style='color:#999999' ";
        if ($egroup == $arr['egroup']) {
            $selgroup = $arr['itemname'];
            $egroupOptionS .= "<option value='{$arr['id']}' $stylecolor selected='1'>{$arr['egroup']}|{$arr['itemname']}</option>\r\n";
        } else {
            $egroupOptionS .= "<option value='{$arr['id']}' $stylecolor>{$arr['egroup']}|{$arr['itemname']}</option>\r\n";
        }
    }


    //子内容 显示
    if ($egroup != '') {
        if (!empty($reevalue)) {
            //查询子内容
            // $egroupsql = " WHERE egroup LIKE '$egroup' AND reevalue='$reevalue' or (evalue='$reevalue' and egroup='$egroup')";
            $egroupsql = " WHERE egroup LIKE '$egroup' AND reevalue='$reevalue' ";
        } else {
            //只获取顶级内容,然后到界面中在调用功能 递归调取 下级内容
            $egroupsql = " WHERE egroup LIKE '$egroup' and (reevalue='0' or isnull(reevalue) or reevalue='') ";
        }
        //$orderby = '  ORDER BY   CAST(evalue as SIGNED)  ASC';
        $sql = "SELECT * FROM `#@__sys_enum` $egroupsql  ORDER BY disorder ASC";

        //子内容 OPTION
        $childOptionS = "<option value='0'>$selgroup(顶级)...</option>";
        $arr = $dsql->GetOne("SELECT * FROM `#@__sys_stepselect` WHERE egroup='{$egroup}' ");
        //$childOptionS .= getOptionsList($egroup, $reevalue);   ///170205禁止使用子类，因为要全部保存值里，无法同时更新子值的上级值??????
        $sysFunTitle .= " 子内容列表";
    } else {
        $egroupsql = '';
        $sql = "SELECT * FROM `#@__sys_stepselect`   ORDER BY   id DESC";
        $sysFunTitle .= " 类别组列表";
    }


    //echo $sql;exit;
    $dlist = new DataListCP();
    $dlist->pageSize = 20;
    $dlist->SetParameter('egroup', $egroup);
    $dlist->SetParameter('reevalue', $reevalue);
    $dlist->SetTemplet("sysStepSelect.htm");
    $dlist->SetSource($sql);
    $dlist->display();
    exit();
} else if ($dopost == 'edit' || $dopost == 'addnew' || $dopost == 'addenum' || $dopost == 'view') {
    // AjaxHead();
    include('sysStepSelect_showAjax.htm');
    exit();
} /*-----------------
保存组修改
function __edit_save() { }
------------------*/
else if ($dopost == 'edit_save') {
    if (preg_match("#[^0-9a-z_-]#i", $egroup)) {
        ShowMsg("组名称不能有全角字符或特殊符号！", "-1");
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__sys_stepselect` SET `itemname`='$itemname',`egroup`='$egroup',`description`='$description' WHERE id='$id'; ");
    ShowMsg("成功修改一个分类！", "sysStepSelect.php");
    exit();
} /*-----------------
保存新组
function __addnew_save() { }
------------------*/
else if ($dopost == 'addnew_save') {
    if (preg_match("#[^0-9a-z_-]#i", $egroup)) {
        ShowMsg("组名称不能有全角字符或特殊符号！", "-1");
        exit();
    }
    $arr = $dsql->GetOne("SELECT * FROM `#@__sys_stepselect` WHERE itemname LIKE '$itemname' OR egroup LIKE '$egroup' ");
    if (is_array($arr)) {
        ShowMsg("你指定的类别名称或组名称已经存在，不能使用！", "sysStepSelect.php");
        exit();
    }

    //这里部门默认取17，随后要根据公司来变???161026
    $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_stepselect`(`itemname`,`egroup`,`issign`,`issystem`,`description`,depid) VALUES('$itemname','$egroup','0','0','$description',$DEP_TOP_ID); ");
    WriteEnumsCache($egroup);
    ShowMsg("成功添加一个组！", "sysStepSelect.php?egroup=$egroup");
    exit();
} /*添加子内容
---------------------*/
else if ($dopost == 'addenum_save') {
    if (empty($ename)) {
        Showmsg("子内容名称不能为空！", "-1");
        exit();
    }


    if ($reevalue != "")//如果上级值不为空,则代表的是添加二级以下的子内容
    {
        $enames = explode(',', $ename);
        foreach ($enames as $ename) {

            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE  egroup='$egroup'  and ename = '$ename' ");
            if (is_array($arr)) {
                ShowMsg("你填写的子内容名称已经存在，不能使用！", $$ENV_GOBACK_URL);
                exit();
            }


            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup='$egroup'   ORDER BY   id DESC ");
            // echo $sql;exit;
            if (!is_array($arr)) {
                $disorder = $evalue = 1;
            } else {
                $disorder = $arr['disorder'] + 1;
                //$evalue = $arr['evalue'] + 1 ;
            }

            $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`reevalue`,`egroup`,`disorder`,`issign`) 
                                    VALUES('$ename','$ename','$reevalue','$egroup','$disorder','$issign'); ");
        }

    } else {//添加一级子内容
        $enames = explode(',', $ename);
        foreach ($enames as $ename) {

            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE  egroup='$egroup'  and ename = '$ename' ");
            if (is_array($arr)) {
                ShowMsg("你填写的子内容名称已经存在，不能使用！", $$ENV_GOBACK_URL);
                exit();
            }


            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup='$egroup'    ORDER BY   id DESC ");
            // echo $sql;exit;
            if (!is_array($arr)) {
                $disorder = $evalue = 1;
            } else {
                $disorder = $arr['disorder'] + 1;
                //$evalue = $arr['evalue'] + 1 ;
            }

            $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`) 
                                    VALUES('$ename','$disorder','$egroup','$disorder','$issign'); ");
        }
    }
    WriteEnumsCache($egroup);//更新缓存
    //dump($sdfdf);
    ShowMsg("成功添加子内容！" . $dsql->GetError(), $$ENV_GOBACK_URL);
    exit();
} /*-----------------
修改子内容名称和排序
function __upenum() { }
------------------*/
else if ($dopost == 'upenum') {
    $ename = trim(str_replace("-", '', $ename));
    if ($evalue == "" || $evalue === 0) {
        //ShowMsg("子内容值不能为空或不能为0,请检查！", '-1');
        exit();
    }

//170124增加 保存时按组内排序
    $row = $dsql->GetOne("SELECT egroup FROM `#@__sys_enum` WHERE id = '$aid' order by disorder asc");   ///141223修改 获取 组名称 1\用于判断子内容值是否重复 2\更新子内容的JS
    $row1 = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup = '" . $row['egroup'] . "' and evalue='$evalue' and id!=$aid ");//判断子内容值是否重复
    if (is_array($row1)) {
        ShowMsg("子内容值重复,请检查！", '-1');
    } else {

        $dsql->ExecuteNoneQuery("UPDATE `#@__sys_enum` SET `ename`='$ename',`evalue`='$evalue',`disorder`='$disorder' WHERE id='$aid'; ");
        ShowMsg("成功修改一个子内容！", $$ENV_GOBACK_URL);
        WriteEnumsCache($row['egroup']);
    }
    exit();
}  /*-----------------
修改全部子内容名称和排序
function __upenum() { }
------------------*/
else if ($dopost == 'saveall') {
    //dump($allids);
    $allids_array = explode(",", $allids);

    $falsenumber=0;
    foreach ($allids_array as $aid) {
        $ename_str = "ename" . $aid;
        $ename = $$ename_str;//从传递的多个值中取值

        $evalue_str = "evalue" . $aid;
        $evalue = $$evalue_str;//从传递的多个值中取值

        $disorder_str = "disorder" . $aid;
        $disorder = $$disorder_str;//从传递的多个值中取值


        $ename = trim(str_replace("-", '', $ename));
        if ($evalue == "" || $evalue === 0) {
            //ShowMsg("子内容值不能为空或不能为0,请检查！", '-1');
            exit();
        }

        $row = $dsql->GetOne("SELECT egroup FROM `#@__sys_enum` WHERE id = '$aid' order by disorder asc");   ///141223修改 获取 组名称 1\用于判断子内容值是否重复 2\更新子内容的JS
        $row1 = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup = '" . $row['egroup'] . "' and evalue='$evalue' and id!=$aid ");//判断子内容值是否重复
        if (is_array($row1)) {
            $falsenumber++;
            //ShowMsg("子内容值重复,请检查！", '-1');
        } else {

            $dsql->ExecuteNoneQuery("UPDATE `#@__sys_enum` SET `ename`='$ename',`evalue`='$evalue',`disorder`='$disorder' WHERE id='$aid'; ");
            //ShowMsg("成功修改一个子内容！", $$ENV_GOBACK_URL);
            WriteEnumsCache($row['egroup']);
        }
    }
    $str="成功修改";
    if($falsenumber>0)$false_str="有${falsenumber}个因为子内容值重复，更新失败";

//dump($str);
ShowMsg($str, $$ENV_GOBACK_URL);


    exit();
} /*-----------------
更新枚举缓存
function __upallcache() { }
------------------*/
else if ($dopost == 'upallcache') {

    if (!isset($egroup)) $egroup = '';
    WriteEnumsCache($egroup);
    ShowMsg("成更新缓存！", $$ENV_GOBACK_URL);
    exit();
}


/**
 *  获得一级以下的递归调用
 *
 * @access    public
 *
 * @param            $egroup   分组名称
 * @param            $evalue   内容值
 * @param     string $step     层级标志
 *
 * @return    void
 */
function LogicListAllSun($egroup, $evalue, $step)
{


    $fevalue = $evalue;
    global $dsql;
    $dsql->SetQuery("SELECT * FROM `#@__sys_enum` WHERE reevalue='$fevalue' and egroup='$egroup' ORDER BY disorder");
    $dsql->Execute($fevalue);
    if ($dsql->GetTotalRow($fevalue) > 0) {
        while ($row = $dsql->GetObject($fevalue)) {
            //$egroup = $row->egroup;
            $ename = $step . $row->ename;
            $reevalue = $row->reevalue;
            $evalue = $row->evalue;
            $disorder = $row->disorder;
            $id = $row->id;


            echo "<tr >
				  <td> <input type='checkbox' class='i-checks' name='ids' value='$id'  /></td>
				  <td>$id</td>
				  <td>";

            echo (strlen($step) + 1) . "级";
            echo "</td>
				  <td>$egroup</td>
				  <td>
				  
				  <input type='text' class='form-control' id='ename{$id}' value='$ename'  /></td>
				  <td><input type='text' class='form-control' id='evalue{$id}' value='$evalue'  /></td>
				  <td><input type='text' class='form-control' id='disorder{$id}' value='$disorder'  /></td>
				  <td>";
            if (!empty($egroup)) {

                echo "   <a href='javascript:updateItem({$id});'>保存</a> <a href='javascript:isdel(\"sysStepSelect_del.php?dopost=delenum&id={$id}\");'>删除</a>";

            } else {
                echo "<a href='sysStepSelect.php?egroup={$egroup}'><u>" . $egroup . "</u></a>";
            }
            echo "</td>
				</tr>";


            LogicListAllSun($egroup, $evalue, $step . $step);
        }
    }
}


function getLogicSunNumb($egroup, $evalue)
{
    $fevalue = $evalue;
    global $dsql;
    global $i;
    $dsql->SetQuery("SELECT * FROM `#@__sys_enum` WHERE reevalue='$fevalue' and egroup='$egroup' ORDER BY disorder");
    $dsql->Execute($fevalue);
    if ($dsql->GetTotalRow($fevalue) > 0) {
        while ($row = $dsql->GetObject($fevalue)) {
            //$egroup = $row->egroup;
            // $ename = $step.$row->ename;
            $reevalue = $row->reevalue;
            $evalue = $row->evalue;
            $disorder = $row->disorder;
            $id = $row->id;


            $i++;
            getLogicSunNumb($egroup, $evalue);
        }
    }
}















/**
 *  获得当前内容的 所在级数
 * evalue 为空为第一级
 *
 * @access    public
 *
 * @param            $egroup   分组名称
 * @param            $evalue   内容值
 * @param     string $step     层级标志
 *
 * @return    void

function getSunNumb($egroup,$evalue)
 * {
 * $fevalue = $evalue;
 * global $dsql;
 * global $i;
 * $dsql->SetQuery("SELECT * FROM `#@__sys_enum` WHERE evalue='$fevalue' and egroup='$egroup' ORDER BY disorder");
 * //dump("SELECT * FROM `#@__sys_enum` WHERE evalue='$fevalue' and egroup='$egroup' ORDER BY disorder");
 *
 * $i=1;
 * $dsql->Execute();
 * if($dsql->GetTotalRow()>0)
 * {
 * while($rownumb = $dsql->GetObject())
 * {
 * $reevalue = $rownumb->reevalue;
 * if(empty($reevalue)||$reevalue==""||$reevalue=="0")
 * {
 * //dump($i);
 * return "<strong>1</strong>级";
 * }else
 * {
 * //$i++;
 * //dump($i);
 * getLogicSunNumb($egroup,$reevalue,$i);
 * return $i."级";
 * }
 * }
 * }
 * }
 *
 *
 * function getLogicSunNumb($egroup,$evalue,$i=1)
 * {
 * $fevalue = $evalue;
 * global $dsql;
 * global $i;
 *
 *
 * $sql="SELECT * FROM `#@__sys_enum` WHERE evalue='$fevalue' and egroup='$egroup' ORDER BY disorder";
 * $dsql->SetQuery($sql);
 *
 *
 * $dsql->Execute($fevalue.$i.$i);
 * if($dsql->GetTotalRow($fevalue.$i.$i)>0)
 * {
 *
 * while($rownumbl = $dsql->GetObject($fevalue.$i.$i))
 * {
 * $reevaluel = $rownumbl->reevalue;
 *
 * dump($reevaluel);
 * $i++;
 * dump($i);
 * getLogicSunNumb($egroup,$reevaluel,$i);
 * }
 * }
 * }
 */




























