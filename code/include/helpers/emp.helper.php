<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 员工部门小助手
 *
 * @version        $Id: archive.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


if (!function_exists('GetEmpCodeByEmpId')) {
    /** 1通过员工ID获取员工编号
     * 仪表2015中未找到使用地方150925
     *
     * @param $empid
     *
     * @return string
     */
    function GetEmpCodeByEmpId($empid)
    {
        global $dsql;
        $questr1 = "SELECT emp_code FROM `#@__emp` WHERE emp_id='" . $empid . "'";
        //echo $questr1;
        $rowarc1 = $dsql->GetOne($questr1);
        if (!is_array($rowarc1)) {
            $str = "无记录";
        } else {
            $str = $rowarc1['emp_code'];
        }
        if (strlen($str) < 3) {
            for ($i = 0; $i <= 3 - strlen($str); $i++) {
                $str = "0" . $str;
            }
        }
        return $str;
    }
}


if (!function_exists('GetEmpNameById')) {
    /** 2根据员工ID获取员工姓名
     *
     * @param $empid
     *
     * @return string
     */
    function GetEmpNameById($empid)
    {
        $str = "";
        if (file_exists(DWTPATH . '/emp')) {//如果系统有EMP的功能,获取的相关数据
            global $dsql;
            $questr1 = "SELECT emp_realname FROM `#@__emp` WHERE emp_id='" . $empid . "'";
            //echo $questr1;
            $rowarc1 = $dsql->GetOne($questr1);
            if (!is_array($rowarc1)) {
                $str = "";
            } else {
                $str = $rowarc1['emp_realname'];
            }
        }
        return $str;
    }
}

if (!function_exists('GetEmpPhoneById')) {
    /** 2根据员工ID获取员工电话
     *
     * @param $empid
     *
     * @return string
     */
    function GetEmpPhoneById($empid)
    {
        $str = "";
        if (file_exists(DWTPATH . '/emp')) {//如果系统有EMP的功能,获取的相关数据
            global $dsql;
            $questr1 = "SELECT emp_mobilephone FROM `#@__emp` WHERE emp_id='" . $empid . "'";
            //echo $questr1;
            $rowarc1 = $dsql->GetOne($questr1);
            if (!is_array($rowarc1)) {
                $str = "";
            } else {
                $str = $rowarc1['emp_mobilephone'];
            }
        }
        return $str;
    }
}


if (!function_exists('GetEmpNameByUserId')) {
    /** 3 根据登录ID获取员工姓名 12次调用
     *
     * @param $userid
     *
     * @return string
     */
    function GetEmpNameByUserId($userid)
    {
        global $dsql;
        $str = "";
        if (file_exists(DWTPATH . '/emp')) {//如果系统有EMP的功能,获取的相关数据
            $str = "无";
            $questr = "SELECT userName,empid FROM `#@__sys_admin` WHERE  id='" . $userid . "'";
            $row = $dsql->GetOne($questr);
            if (is_array($row)) {
                $empid = $row['empid'];
                $questr1 = "SELECT emp_realname FROM `#@__emp` WHERE emp_id='" . $empid . "'";
                //echo $questr1;
                $rowarc1 = $dsql->GetOne($questr1);
                if (is_array($rowarc1)) {
                    $str = $rowarc1['emp_realname'];
                } else {
                    $str = $row['userName'];
                }
            }
        }
        return $str;
    }
}


if (!function_exists('GetEmpDepNameByEmpId')) {
    /** 4显示最后一级的部门名称 ，
     * 仪表2015中未找到使用地方150925
     *
     * @param $empid
     *
     * @return string
     */
    function GetEmpDepNameByEmpId($empid)
    {
        global $dsql;
        $questr1 = "SELECT dep_name FROM `#@__emp_dep` WHERE dep_id=(SELECT emp_dep FROM `#@__emp` WHERE emp_id='" . $empid . "')";
        //echo $questr1;
        $rowarc1 = $dsql->GetOne($questr1);
        if (!is_array($rowarc1)) {
            $str = "无部门记录";
        } else {
            $str = $rowarc1['dep_name'];
        }
        return $str;
    }
}


if (!function_exists('GetEmpDepAllNameByEmpCode')) {
    /** 5
     * 根据员工code 显示全部的部门名称150130
     * 仪表2015中未找到使用地方150925
     *
     * @param $empcode
     *
     * @return string
     */
    function GetEmpDepAllNameByEmpCode($empcode)
    {
        global $dsql;
        global $sunDep;
        $str = "";
        $sunDep = "";
        $questr = "SELECT emp_dep FROM `#@__emp` WHERE emp_code='" . $empcode . "'";
        //echo $questr1;
        $rowarc = $dsql->GetOne($questr);
        if (!is_array($rowarc)) {
            $str = "无员工记录";
        } else {
            $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id='" . $rowarc['emp_dep'] . "'";
            //echo $questr1;
            $rowarc1 = $dsql->GetOne($questr1);
            if (!is_array($rowarc1)) {
                $str = "无部门记录";
            } else {
                if ($rowarc1['dep_reid'] != 0) $str = logicGetSunDeps($rowarc1['dep_reid']);
                $str .= $rowarc1['dep_name'];
            }

        }
        return $str;
    }
}


if (!function_exists('GetEmpDepAllNameByEmpId')) {
    /** 6
     * 根据员工ID 显示全部的部门名称
     * 4次调用
     *
     * @param $empid
     *
     * @return string
     */
    function GetEmpDepAllNameByEmpId($empid)
    {
        global $dsql;
        global $sunDep;
        $str = "";
        $sunDep = "";
        $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id=(SELECT emp_dep FROM `#@__emp` WHERE emp_id='" . $empid . "')";
        //echo $questr1;
        $rowarc1 = $dsql->GetOne($questr1);
        if (!is_array($rowarc1)) {
            $str = "无部门记录";
        } else {
            if ($rowarc1['dep_reid'] != 0) $str = logicGetSunDeps($rowarc1['dep_reid']);
            $str .= $rowarc1['dep_name'];
        }
        return $str;
    }
}


if (!function_exists('GetDepsNameByDepId')) {
    /** 7
     * 获取当前部门名称
     * 缺陷记录使用
     *
     * @param $depid 部门ID
     *
     * @return string
     */
    function GetDepsNameByDepId($depid)
    {   ////获取当前部门名称
        global $dsql;
        $depName = "";
        //150108添加 判断传入ID是否为空
        if ($depid != "") {
            $sql = "SELECT dep_name FROM `#@__emp_dep` WHERE dep_id='$depid'";//161031修补参数漏洞
            $row = $dsql->GetOne($sql);
            if (is_array($row)) {
                $depName = $row['dep_name'];
            }
        }
        return $depName;
    }
}


/** 递归获取上级部门名称,叠加用于获取全称
 * 只在本页使用
 *
 * @param $id
 *
 * @return string
 */
function logicGetSunDeps($id)
{
    global $dsql;
    global $sunDep;
    $sql = "SELECT dep_name,dep_reid FROM `#@__emp_dep` WHERE dep_id=$id";   //141214修改
    $dsql->SetQuery($sql);
    //dump($sql);
    $dsql->Execute("gs" . $id);
    while ($row = $dsql->GetObject("gs" . $id)) {
        $sunDep = $row->dep_name . "-" . $sunDep;
        $nid = $row->dep_reid;
        //dump($str);
        if ($nid != 0) logicGetSunDeps($nid);
    }
    //dump($sunDep);
    return $sunDep;
}


if (!function_exists('GetEmpDepAllNameByUserId')) {
    /** 8
     * 根据登录ID 显示全部的部门名称
     *
     *
     * 如果包含部门和员工的功能,则根据USERID获取部门数据 ,
     * 如果不包含,则用USERTYPE获取权限名称,这里只是跳转一下
     *
     * @param     string $userid   用户登录 ID
     * @param     string $usertype 用户权限值 默认为0
     *
     * @return    string
     */
    function GetEmpDepAllNameByUserId($userid, $usertype = 0)
    {
        if (file_exists(DWTPATH . '/emp')) {//如果系统有EMP的功能,获取部门的相关数据
            global $dsql;
            global $sunDep;
            $str = "";
            $sunDep = "";
            $questr = "SELECT userName,empid,usertype FROM `#@__sys_admin` WHERE  id='" . $userid . "'";
            $row = $dsql->GetOne($questr);
            if (is_array($row)) {
                $empid = $row['empid'];
                $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id=(SELECT emp_dep FROM `#@__emp` WHERE emp_id='" . $empid . "')";
                //echo $questr1;
                $rowarc1 = $dsql->GetOne($questr1);
                if (!is_array($rowarc1)) {
                    $str = $row['userName'];
                    if ($row['usertype'] == 10) $str = "超级管理员";//151208如果超级管理员登录则部门名称 显示超级管理员  ???后期要加上  如果是部门管理员 则显示部门名称
                } else {
                    if ($rowarc1['dep_reid'] != 0) $str = logicGetSunDeps($rowarc1['dep_reid']);
                    $str .= $rowarc1['dep_name'];
                }
            }
            //dump($str);
        } else {//没有部门数据
            //直接输出用户的权限组名称
            $str = GetUserTypeNames($usertype);
        }
        return $str;
    }
}


/**150925删除  因为引用重复
 * 文档阅读页面 ,获取已经阅读的用户的顶级部门,然后分类
 * 根据登录ID 显示最顶级的部门名称
 * archives_view_history_ajax.PHP页面调用
 *
 * if (!function_exists('GetEmpDepTopNameByUserId')) {
 * function GetEmpDepTopNameByUserId($userid)
 * {
 * global $dsql;
 * global $sunDep;
 * $str = "";
 * $sunDep = "";
 * $questr = "SELECT userName,empid FROM `#@__sys_admin` WHERE  id='" . $userid . "'";
 *
 * $row = $dsql->GetOne($questr);
 * if (is_array($row)) {
 * $empid = $row['empid'];
 * $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id=(SELECT emp_dep FROM `#@__emp` WHERE emp_id='" . $empid . "')";
 * //echo $questr1;
 * $rowarc1 = $dsql->GetOne($questr1);
 * if (!is_array($rowarc1)) {
 * $str = "无部门";
 * } else {
 * $str = $rowarc1['dep_name'];
 * if ($rowarc1['dep_reid'] != 0) GetTopDeps($rowarc1['dep_reid']);
 * if ($sunDep != "") $str = $sunDep;
 * }
 * }
 * return $str;
 * }
 * } */


/**
 * 此段无用150925
 * 递归获取上级部门名称
 *
 * function GetTopDeps($id)
 * {
 * global $dsql;
 * global $sunDep;
 * $sql = "SELECT * FROM `#@__emp_dep` WHERE dep_id=$id and dep_reid!=0";
 * $dsql->SetQuery($sql);
 * $dsql->Execute("gs" . $id);
 * while ($row = $dsql->GetObject("gs" . $id)) {
 * $sunDep = $row->dep_name;
 * $nid = $row->dep_reid;
 * if ($nid != 0) GetTopDeps($nid);
 * }
 * }
 */


if (!function_exists('GetEmpDepTopIdByUserId')) {
    /**9   150814增加获取指定用户 部门ID(最顶级部门的ID,可以指定级数倒排获取)
     *
     * @param     string $userid               用户登录 ID
     * @param     int    $step                 级数 默认为0获取最顶组的部门ID,
     *                                         1倒数第二级的(如果有分厂的话 倒数第二级为车间级)
     *                                         100表示获取当前用户的部门
     *
     *                           部门数组格式: 0-班组
     *                                        1-车间
     *                                        2-分厂
     *
     *
     * @return    string
     */
    function GetEmpDepTopIdByUserId($userid, $step = 0)
    {
        global $dsql;
        global $sunDepIdArray;
        global $stepTotal;  //总级数
        $stepTotal = 0;
        $str = "0";
        $sunDepIdArray = "";
        $questr = "SELECT userName,empid FROM `#@__sys_admin` WHERE  id='$userid'";
        $row = $dsql->GetOne($questr);
        if (is_array($row)) {
            $empid = $row['empid'];
            $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id=(SELECT emp_dep FROM `#@__emp` WHERE emp_id='$empid')";
            //echo $questr1;
            $rowarc1 = $dsql->GetOne($questr1);
            if (!is_array($rowarc1)) {
                $str = "";
            } else {
                $sunDepIdArray[$stepTotal] = $rowarc1['dep_id'];
                if ($rowarc1['dep_reid'] != 0) logic_GetTopDepId($rowarc1['dep_reid'], $stepTotal + 1);
                //if ($sunDepIdArray != "") $sunDepIdArray[$stepTotal] = $sunDepId;
            }
        }
        //dump($sunDepIdArray);
        if (is_array($sunDepIdArray)) {
            //修复BUG151015 管理员admin(无对应部门时)此$sunDepIdArray为空
            $array_numb = count($sunDepIdArray);
            if ($step < $array_numb) {
                $str = $sunDepIdArray[$array_numb - ($step + 1)];//获取倒数级数的部门ID
            } else {
                $str = $sunDepIdArray[0];//获取倒数级数的部门ID
            }
            //dump($array_numb . "---" . $step . "---" . $array_numb);
            //dump($sunDepIdArray);
        }
        if ($str == "") $str = "0";
        return $str;
    }
}


/**递归获取上级部门id
 *
 * @param $id
 * @param $stepTotal
 */
function logic_GetTopDepId($id, $stepTotal)
{
    global $dsql;
    global $sunDepIdArray;
    $sql = "SELECT * FROM `#@__emp_dep` WHERE dep_id=$id";
    $dsql->SetQuery($sql);
    $dsql->Execute("gs" . $id);
    while ($row = $dsql->GetObject("gs" . $id)) {
        //dump($stepTotal);
        $sunDepIdArray[$stepTotal] = $row->dep_id;
        $nid = $row->dep_reid;
        if ($nid != 0) logic_GetTopDepId($nid, $stepTotal + 1);
    }
}


/**    效率太低  本来是用在chartDeviceTotalToDep.php  查询当前部门ID的所属车间ID,但4万多条数据不停的查询SQL 速度 太慢  ,改用在数组中查询
 * if (!function_exists('GetDepTopIdByDepId')) {
 * 9   151105获取指定部门ID的最顶级部门ID(最顶级部门的ID,可以指定级数倒排获取)
 *
 * @param     string $depid                部门ID
 * @param     int    $step                 级数 默认为0获取最顶组的部门ID,
 *                                         1倒数第二级的(如果有分厂的话 倒数第二级为车间级)
 *                                         100表示获取当前用户的部门
 *
 *                           部门数组格式: 0-班组
 *                                        1-车间
 *                                        2-分厂
 *
 *
 * @return    string

function GetDepTopIdByDepId($depid, $step = 0)
 * {
 * global $dsql;
 * global $sunDepIdArray;
 * global $stepTotal;  //总级数
 * $stepTotal = 0;
 * $str = "";
 * $sunDepIdArray = "";
 *
 * $questr1 = "SELECT dep_name,dep_reid,dep_id FROM `#@__emp_dep` WHERE dep_id= $depid ";
 * $rowarc1 = $dsql->GetOne($questr1);
 * if (!is_array($rowarc1)) {
 * $str = "";
 * } else {
 * $sunDepIdArray[$stepTotal] = $rowarc1['dep_id'];
 * if ($rowarc1['dep_reid'] != 0) logic_GetTopDepId($rowarc1['dep_reid'], $stepTotal + 1);
 * //if ($sunDepIdArray != "") $sunDepIdArray[$stepTotal] = $sunDepId;
 * }
 *
 * //dump($sunDepIdArray);
 * if(is_array($sunDepIdArray)) {
 * //修复BUG151015 管理员admin(无对应部门时)此$sunDepIdArray为空
 * $array_numb = count($sunDepIdArray);
 * if($step<$array_numb){
 * $str = $sunDepIdArray[$array_numb - ($step + 1)];//获取倒数级数的部门ID
 * }else{
 * $str = $sunDepIdArray[0];//获取倒数级数的部门ID
 * }
 * //dump($array_numb . "---" . $step . "---" . $array_numb);
 * //dump($sunDepIdArray);
 * }
 * return $str;
 * }
 * }
 */

if (!function_exists('GetDepAllUserId')) {
    /** 10
     *     获取部门中所有员工的登录ID
     *     用于：于文档管理等有保存登录用户ID发布的内容，与这个返回的用户ID比较 显示内容
     *
     * @param $depids 部门数据  string 格式 1，2
     *
     * @return string 格式 1，2
     */
    function GetDepAllUserId($depids)
    {
        global $dsql;
        //有员工功能才执行下一步
        if ($depids != "" && file_exists(DWTPATH . '/emp')) {
            $str = "0";   ///141205修改 默认值原为空  要引起错误 改为0:如果查看不到值则默认为0 代表什么也查询不到
            $questr = "SELECT admin.id FROM  #@__sys_admin  admin  LEFT JOIN `#@__emp` emp on admin.empid=emp.emp_id where emp.emp_dep in (" . $depids . ") ";  //141206优化此句,此句是获得userid,所以先查询admin在左连emp
            //dump($questr);
            $dsql->SetQuery($questr);
            $dsql->Execute();
            while ($row = $dsql->GetObject()) {
                $str .= "," . $row->id;
            }
            $str = rtrim($str, ",");
            //$str="0,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,498,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,144,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,499,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,476,477,478,479,480,481,482,483,484,485,486,487,489,490,491,492,493,494,495,500,501,502,504,505,453";
            //dump("555555");
        }
        return $str;
    }
}


if (!function_exists('GetWorkTypeAllUserId')) {
    /**11 获取指定部门中含有的指定工种 的所有员工的登录ID
     *     用于：工作日志 获取 厂领导 车间领导 专工的员工登录ID
     *
     * @param string $worktypeids 工种数据  string 格式 1，2
     * @param string $depids      部门数据  string 格式 1，2
     *
     * @return string 格式 1，2
     */
    function GetWorkTypeAllUserId($worktypeids, $depids = "")
    {
        global $dsql;
        $str = "";
        //有员工功能才执行
        if (file_exists(DWTPATH . '/emp')) {
            $wheresql = "";
            if ($depids != "") $wheresql = " emp.emp_dep in (" . $depids . ") and ";
            $questr = "SELECT admin.id FROM #@__sys_admin  admin LEFT JOIN `#@__emp` emp on admin.empid=emp.emp_id where $wheresql emp.emp_worktype in (" . $worktypeids . ") ORDER BY emp_dep asc,emp.emp_id asc";
            $dsql->SetQuery($questr);
            $dsql->Execute();
            while ($row = $dsql->GetObject()) {
                $str .= $row->id . ",";
            }
            $str = rtrim($str, ",");
        }
        return $str;
    }
}


if (!function_exists('GetDepAndChildTotalEmpNumb')) {
    /** 12 150925
     *   获取指定部门中的所有员工数量(包含子部门的员工数量)
     * 用于：部门管理中的人数统计
     * 文档已查看历史中的人数统计
     *
     * @param $depid 部门ID
     *
     * @return string 数量
     */
    function GetDepAndChildTotalEmpNumb($depid)
    {
        global $Deps;
        global $dsql;
        $Deps = "";
        $depids = GetDepChilds($depid);
        $sqlstr = "SELECT count(*) as dd FROM `#@__emp` WHERE emp_isdel=0 and   emp_dep in (" . $depids . ") ";
        //dump($sqlstr);
        $dsql->SetQuery($sqlstr);
        $dsql->Execute();
        $total = "0";
        while ($row = $dsql->GetArray()) {
            $total = $row['dd'];
        }
        return $total;
    }
}


if (!function_exists('GetDepChilds')) {
    /**13  150925
     * PS这段没有加部门数据权限  因为这段用于管理页面的查询,查询的同时已经加了部门数据权限
     * 返回当前所选定的部门  的所有下级部门的子ID，列表供查询相关部门下包含的记录时使用
     *
     * @param int $selid
     *
     * @return string 子部门数据以逗号分隔 11,22,33
     */
    function GetDepChilds($selid = 0)
    {
        global $Deps, $dsql;
        $Deps = "";//修复BUG151015 初始化
        if ($dsql->IsTable("#@__emp_dep")) {
            //当前选中的部门
            if ($selid > 0) {
                $Deps .= $selid . ",";
                logicGetDeps($selid, $dsql);
                $Deps = rtrim($Deps, ",");
            }
        } else {
            $Deps = "";
        }

        //echo $Deps;
        return $Deps;
    }
}


/** 循环获取子部门ID
 *
 * @param $selid
 * @param $dsql
 */
function logicGetDeps($selid, &$dsql)
{
    global $Deps;
    $dsql->SetQuery("SELECT * FROM `#@__emp_dep` WHERE dep_reid='" . $selid . "'  ORDER BY dep_id asc"); //and depid>27屏蔽掉合成的  150925删除这句
    $dsql->Execute($selid);
    while ($row = $dsql->GetObject($selid)) {
        $Deps .= $row->dep_id . ",";
        logicGetDeps($row->dep_id, $dsql);
    }
}


if (!function_exists('GetAllDepArray')) {
    /**获取部门的数组 用于报表(系统权限)页面  左列输出部门列表
     *180107修改为使用数组调用
     *
     * @return mixed
     */
    function GetAllDepArray()
    {
        global $dsql;
        $depArray = array();
        $query = "SELECT dep_id,dep_reid,dep_name FROM `#@__emp_dep`  ORDER BY dep_id ASC ";
        // dump($query);
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            //$row->dep_name = base64_encode($row->dep_name);
            //格式 (id,上级ID,名称)
            $depArray[] = array("id" => $row->dep_id, "reid" => $row->dep_reid, "dep_name" => $row->dep_name);
        }
        return $depArray;


    }
}


if (!function_exists('GetDepSonIds')) {
    /**获取部门 某id的所有下级id,供SQL查询使用
     *
     * 包含当前ID自身
     * 先获取所有的分类信息,然后根据当前设定的标志,获取下级分类ID
     *
     * @return 格式1,2
     */
    function GetDepSonIds($depid)
    {
        $returnStr = "";
        $allDepId_array=GetAllDepArray();
        $newTypeInfoArray = GetTypeInfoAfterArray($allDepId_array,$depid);//获取包含当前ID的所有子分类的信息数组
        if (is_array($newTypeInfoArray)) {
            foreach ($newTypeInfoArray as $keyp => $valuep) {
                $typeInfoArray = $newTypeInfoArray[$keyp];
                $typeid = $typeInfoArray['id'];
                $returnStr .= "$typeid,";
            }
            $returnStr = trim($returnStr, ",");
        }
        return $returnStr;
    }
}
if (!function_exists('GetDepSonId_array')) {
    /**获取部门 某id的所有下级id,
     *
     * 包含当前ID自身
     * 先获取所有的分类信息,然后根据当前设定的标志,获取下级分类ID
     *
     * @return 格式array
     */
    function GetDepSonId_array($depid)
    {
        $depNumb = 0;//初始化
        $depArray=array();
        $allDepId_array=GetAllDepArray();
        $newTypeInfoArray = GetTypeInfoAfterArray($allDepId_array,$depid);//获取包含当前ID的所有子分类的信息数组
        if (is_array($newTypeInfoArray)) {
            foreach ($newTypeInfoArray as $keyp => $valuep) {
                $typeInfoArray = $newTypeInfoArray[$keyp];
                $depid = $typeInfoArray['id'];
                $dep_name = $typeInfoArray['dep_name'];
                $depArray[0][$depNumb] = $dep_name;
                $depArray[1][$depNumb] = $depid;
                $depNumb++;

            }

        }
        return $depArray;
    }
}
/*---------------以下是返回option列表-------*/

if (!function_exists('GetDepOnlyTopOptionList')) {
    /** 14
     *  只获取顶级部门,有部门PLUS表时才用这个  三级部门以上 显示不同的内容时使用
     *  这不只有管理员使用  不用分权限
     *
     * @param int $selid 选择ID
     *
     * @return string $OptionDepArrayList
     */
    function GetDepOnlyTopOptionList($selid = 0)
    {
        global $OptionDepArrayList;    //返回OPTION的语句
        global $dsql;
        global $DepArray;    //保存已经查询过的部门ID
        $wheresql = "";
        //当前选中的部门
        /*if ($selid > 0) {
            $row = $dsql->GetOne("SELECT * FROM `#@__emp_dep` WHERE dep_id='$selid' and dep_reid=0");
            if ($row) $OptionDepArrayList .= "<option value='" . $row['dep_id'] . "' selected='selected'>" . $row['dep_name'] . "</option>\r\n";
        }*/
        //dump ($wheresql);
        $wheresql = " and dep_reid=0";
        $query = " SELECT * FROM `#@__emp_dep`  WHERE 1=1 $wheresql   ORDER BY   dep_id ASC ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            $selected="";
            if($selid==$row->dep_id)$selected=" selected='selected'" ;
            $OptionDepArrayList .= "<option value='{$row->dep_id}' ' $selected>{$row->dep_name}</option>\r\n";
        }
        //dump($DepArray);
        return $OptionDepArrayList;
    }
}


if (!function_exists('GetDepOptionListRole')) {
    /** 15
     *  获取带权限查询的部门选项列表(当前登录用户可以管理的部门)
     *
     * @access    public
     *
     * @param     string $selid 选择ID
     *
     * @return    string $OptionDepArrayList
     */
    function GetDepOptionListRole($selid = 0)
    {
        global $OptionDepArrayList;    //返回OPTION的语句
        global $dsql;
        global $DepArray;    //保存已经查询过的部门ID
        $DepArray = "";//初始值为空 150811修复BUG
        $wheresql = "";
        //当前选中的部门
        /*if ($selid > 0) {
            //$row = $dsql->GetOne("SELECT * FROM `#@__emp_dep` WHERE dep_id='$selid'");
            $row = $dsql->GetOne("SELECT * FROM `#@__emp_dep` WHERE dep_id='$selid'");
            if ($row) $OptionDepArrayList .= "<option value='" . $row['dep_id'] . "' selected='selected'>" . $row['dep_name'] . "</option>\r\n";
        }*/


        ///160308修改,直接从权限判断中获取,不再重复获取
        //部门限制使用的查询数据
        global $ROLE_WHERE_IN_DEP_ID_STR;
        if ($ROLE_WHERE_IN_DEP_ID_STR != "") {
            $wheresql .= " and dep_id in ($ROLE_WHERE_IN_DEP_ID_STR)";  //返回可以管理的部门ID的 查询语句
        }
        //由于权限查出来的部门有可能,是没有子部门的权限的,所以这里和下面的部门查询部分,要检查 查询出来的子ID是否在部门权限里
        //返回的权限查询语句,里面包含所有的可以查询的ID,所以在获取子分类时 要检测是否已经查询过此ID
        global $DepRole;
        $DepRole = $ROLE_WHERE_IN_DEP_ID_STR;  //获得具有权限的ID,如果没有权限则跳过
        //dump ($wheresql);
        if ($wheresql == "") {
            $wheresql = " and dep_reid=0";
        }
        $query = " SELECT * FROM `#@__emp_dep`  WHERE 1=1  $wheresql   ORDER BY   dep_id ASC ";
        //dump($ROLE_WHERE_IN_DEP_ID_STR);
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            //检查已经查询过的部门ID,如果已经查询过,则跳过
            // dump($DepArray);
            $DepArrays = explode(',', rtrim($DepArray, ","));
            if (in_array($row->dep_id, $DepArrays)) {
                continue;
            }
            $sonCats = '';
            logicGetDepOptionArray($row->dep_id, '─', $dsql, $sonCats,$selid);
            $selected="";
            if($selid==$row->dep_id)$selected=" selected='selected'" ;
            $OptionDepArrayList .= "<option value='" . $row->dep_id . "' class='option1' $selected>" . $row->dep_name . "</option>\r\n";
            $OptionDepArrayList .= $sonCats;
            $DepArray .= $row->dep_id . ",";
        }
        //dump($OptionDepArrayList);
        return $OptionDepArrayList;
    }
}

/** 16
 *
 * 150130添加
 *
 * 美味integral_query.htm 积分查询页面使用
 * HC 设备检修记录汇总 更换汇总中使用
 *  获取不带权限查询的部门选项列表
 *
 * @access    public
 *
 * @param     string $selid 选择ID
 *
 * @return    string $OptionDepArrayList
 */
if (!function_exists('GetDepOptionListNoRole')) {
    function GetDepOptionListNoRole($selid = 0)
    {
        global $OptionDepArrayList;    //返回OPTION的语句
        global $dsql;
        global $DepArray;    //保存已经查询过的部门ID
        $wheresql = "";
        //当前选中的部门
        if ($selid > 0) {
            $row = $dsql->GetOne("SELECT * FROM `#@__emp_dep` WHERE dep_id='$selid' ");
            $OptionDepArrayList .= "<option value='" . $row['dep_id'] . "' selected='selected'>" . $row['dep_name'] . "</option>\r\n";
        }
        //dump ($wheresql);
        if ($wheresql == "") {
            $wheresql = " and dep_reid=0";
        }
        $query = " SELECT * FROM `#@__emp_dep`  WHERE 1=1 $wheresql   ORDER BY   dep_id ASC ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetObject()) {
            //检查已经查询过的部门ID,如果已经查询过,则跳过
            $DepArrays = explode(',', rtrim($DepArray, ","));
            if (in_array($row->dep_id, $DepArrays)) {
                //dump($row->dep_id);
                continue;
            }
            $sonCats = '';
            logicGetDepOptionArray($row->dep_id, '─', $dsql, $sonCats,$selid);
            $OptionDepArrayList .= "<option value='" . $row->dep_id . "' class='option1'>" . $row->dep_name . "</option>\r\n";
            $OptionDepArrayList .= $sonCats;
            $DepArray .= $row->dep_id . ",";
        }
        //dump($DepArray);
        return $OptionDepArrayList;
    }
}


/**
 * @param     $id
 * @param     $step
 * @param     $dsql
 * @param     $sonCats
 * @param int $selid  选中的部门
 */
function logicGetDepOptionArray($id, $step, &$dsql, &$sonCats,$selid = 0)
{
    global $OptionDepArrayList;    //返回OPTION的语句
    global $DepArray;    //保存已经查询过的部门ID
    global $DepRole;    //保存已经查询过的部门ID
    $sql = "SELECT * FROM `#@__emp_dep` WHERE dep_reid='$id'   ORDER BY dep_id asc";
    $dsql->SetQuery($sql);//屏蔽掉合成的  随后做的时候再启用150811
    $dsql->Execute($id);
    while ($row = $dsql->GetObject($id)) {
        if ($DepRole != "") {
            $DepRoleArrays = explode(',', $DepRole);
            if (!in_array($row->dep_id, $DepRoleArrays)) {
                //dump($row->dep_id);
                continue;
            }
        }
        $selected="";
        if($selid==$row->dep_id)$selected=" selected='selected'" ;
        $sonCats .= "<option value='" . $row->dep_id . "' class='option3'  $selected><span style='color:#666666'>$step</span>" . $row->dep_name . "</option>\r\n";
        logicGetDepOptionArray($row->dep_id, $step . '─', $dsql, $sonCats,$selid);
        $DepArray .= $row->dep_id . ",";
    }
}

























