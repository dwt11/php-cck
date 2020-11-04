<?php
require_once("../config.php");

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');


if (empty($dopost)) $dopost = '';
if (empty($emp_dep)) $emp_dep = '0';

if ($dopost == 'bb') {

    $dsql->ExecuteNoneQuery("update `#@__emp` set emp_bb='" . $bb . "' where emp_id='$aid';");
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("更新成功！", $$ENV_GOBACK_URL);
    exit();
}


$wheresql = " where emp_isdel=0 "; //默认语句 不显示 删除了的员工
//$title="员工管理"; //页面显示的标题


$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {

    $wheresql .= " And ( (emp_code = '$keyword' and emp_code != '0')"; //编号
    $wheresql .= " or emp_realname LIKE '%$keyword%'";  //姓名
    $wheresql .= " or u.userName like '%$keyword%')"; //编号
    // $wheresql  .= " or crm.aid  LIKE '%$keyword%'";  //CRM的自增长编号
    // $wheresql  .= " or emp_sfz  LIKE '%$keyword%'";  //编号
    //  $wheresql  .= " or emp_phone  LIKE '%$keyword%' ";    //资料编号


}

if ($GLOBAMOREDEP) {
    if (empty($emp_dep)) $emp_dep = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($emp_dep)) $emp_dep = "0";
}

if ($emp_dep != 0) {
    $emp_depids = GetDepChilds($emp_dep);
    //dump($emp_depids);
    $wheresql .= " and   emp_dep in (" . $emp_depids . ") ";    //资料编号
}


$neworderby = isset($neworderby) ? $neworderby : "";
$orderby = isset($orderby) ? $orderby : "";

//默认DESC降序
$neworderby = " ORDER BY emp_code asc,CONVERT( emp_realname USING gbk )  asc ";
if (!empty($orderby) && $orderby != "") {
    $orderby = preg_replace("#[^a-z0-9]#", "", $orderby);
    $neworderby = '  ORDER BY  ' . $orderby . " desc";
}


$sql = "SELECT e.*,
        u.userName,u.loginip,u.loginnumb,u.logintime,u.usertype,u.id AS adminid,
        #@__emp_client.clientid
        FROM `#@__emp` e
        LEFT JOIN `#@__sys_admin` u  ON u.empid=e.emp_id 
        LEFT JOIN `#@__emp_client`   ON #@__emp_client.emp_id=e.emp_id 
        $wheresql $neworderby  ";

//dump($funAllName);

//dump($sql);
$dlist = new DataListCP();

//设定每页显示记录数（默认25条）
$dlist->pageSize = 10;
$emp_ste = isset($emp_ste) ? $emp_ste : "";


$dlist->SetParameter("emp_dep", $emp_dep);  //员工状态参数
$dlist->SetParameter("emp_ste", $emp_ste);  //员工状态参数
$dlist->SetParameter("keyword", $keyword);      //关键词


$tplfile = "emp.htm";

//这两句的顺序不能更换
$dlist->SetTemplate($tplfile);      //载入模板
$dlist->SetSource($sql);            //设定查询SQL
$dlist->Display();                  //显示


//显示完整的部门名称
function GetGz($id)
{
    global $dsql;

    $questr1 = "SELECT worktype_name FROM `#@__emp_worktype` WHERE worktype_id='" . $id . "'";

    //echo $questr1;
    $rowarc1 = $dsql->GetOne($questr1);
    if (!is_array($rowarc1)) {
        $str = "";
    } else {

        $str = $rowarc1['worktype_name'];


    }

    return $str;

}


//显示登录用户名 
function GetUserName($id)
{
    global $dsql;
    $questr1 = "SELECT userName FROM `#@__sys_admin` WHERE empid='" . $id . "'";

    //echo $questr1;
    $rowarc1 = $dsql->GetOne($questr1);
    if (!is_array($rowarc1)) {
        $str = "无";
    } else {

        $str = $rowarc1['userName'];


    }
    echo json_encode($rowarc1);


    return $str;

}


?>