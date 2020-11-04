<?php
/**
 * 员工选项函数
 *
 * @version        $Id: inc_catalog_options.php 1 10:32 21日
 * @package
 * @copyright
 * @license
 * @link
 */
 
/**
 *  获取选项列表
 *
 * @access    public
 * @param     string  $selid  选择ID
 * @return    string
 
 
 
 
 
 */
 
 
function GetEmpOptionList($selid=0)
{
    global  $dsql;


    
	
    $wheresql="";//150130添加
	$OptionEmpArrayList="";
	
        //当前选中的员工
    if($selid > 0)
    {
        $row = $dsql->GetOne("SELECT * FROM `#@__emp` WHERE emp_id='$selid'");
        $OptionEmpArrayList .= "<option value='".$row['emp_id']."' selected='selected'>".GetIntAddZero($row['emp_code'])."-".$row['emp_realname']."</option>\r\n";
    }

//部门权限数据限制
	global $funAllName;
    $wheresql  .= getDepRole($funAllName,"emp_dep");    //返回可以管理的部门ID的 查询语句
    if($wheresql==""){$wheresql=" ";}  

	$query = " SELECT * FROM `#@__emp`  WHERE emp_isdel=0 $wheresql   ORDER BY   convert(emp_realname using gbk) ASC ";
	//dump($query);
    $dsql->SetQuery($query);
    $dsql->Execute();//150130删除$DEPID
    while($row1=$dsql->GetObject())
    {
            $OptionEmpArrayList .= "<option value='".$row1->emp_id."' >--".GetIntAddZero($row1->emp_code)." ".$row1->emp_realname."</option>\r\n";
	}

   
   
   
    return $OptionEmpArrayList;
}








