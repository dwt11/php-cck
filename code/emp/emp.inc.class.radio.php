<?php


if (!defined('DWTINC')) exit('Request Error!');
/**
 * 员工选择RADIO
 *
 * @version        $Id: empMapRadio.class.admin.php 1 15:21 5日
 * @package
 * @copyright
 * @license
 * @link
 */


/**
 * 工种单元,主要用户管理后台管理处
 *
 * @package          empMapRadio
 * @subpackage
 * @link
 */
class empMapRadio
{
    var $dsql;
    var $idCounter;
    var $idArrary;

    //php5构造函数
    function __construct()
    {
        $this->idCounter = 0;
        $this->idArrary = '';
        $this->dsql = 0;
    }

    function empMapRadio()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }

    //
    function GetTotalEmp($depId,$keyword="")
    {

        $total="";

        $wheresql="";
        if($keyword!="")$wheresql=" AND (emp_realname LIKE '%$keyword%' OR emp_code LIKE '%$keyword%' )";
        $query=("SELECT emp_id,emp_realname,emp_code FROM `#@__emp` WHERE  emp_isdel=0 and emp_dep='$depId' $wheresql   ORDER BY convert(emp_realname using gbk) asc");
        //dump($query);
        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $id = $row['emp_id'];
            $emp_realname = $row['emp_realname'];
            $emp_code= GetIntAddZero($row['emp_code']);

            $total .= "
                       <label class=\"checkbox-inline i-checks\" style='width: 120px;line-height: 30px;font-size: 14px'>
                           <input type='radio' name='emp_id' id='emp_id' value='{$id}'  />{$emp_code}-{$emp_realname}
                       </label>
                       ";
            // $total = $row['dd'];
        }
        //($total == "")$total = "0";
        return $total;
    }

    /**
     *  读出所有部门 并分别显示包含员工
     *
     * @access    public
     *
     * @param string $emp_dep  只显示的部门
     * @param string $keyword 搜索关键字
     * @param string $no_emp_dep 不显示的部门
     *
     * @return string
     */
    function empAllRadio($emp_dep="",$keyword="",$no_emp_dep="")
    {
        $this->dsql = $GLOBALS['dsql'];
        //echo DWTINC.$GLOBALS['dsql'];
        global $DepArray_radiophp;    //保存已经查询过的部门ID

        global $funAllName;
        // echo $funAllName;
        //$wheresql .= getDepRole($funAllName, "dep_id");    //返回可以管理的部门ID的 查询语句
        //由于权限查出来的部门有可能,是没有子部门的权限的,所以这里和下面的部门查询部分,要检查 查询出来的子ID是否在部门权限里
        //返回的权限查询语句,里面包含所有的可以查询的ID,所以在获取子分类时 要检测是否已经查询过此ID
        global $DepRole;
        $wheresql ="";
        //$DepRole = getDepRole($funAllName);  //获得具有权限的ID,如果没有权限则跳过
        if ($wheresql == "") {
            $wheresql = " AND dep_reid=0";
        }

        if ($emp_dep != "")             $wheresql = " AND dep_id='$emp_dep'";
        if ($no_emp_dep != "")             $wheresql = " AND (dep_id!='$no_emp_dep' AND dep_reid!='$no_emp_dep' )";

        $query = " SELECT * FROM `#@__emp_dep`  WHERE 1=1 $wheresql   ORDER BY   dep_id ASC ";
        //dump($query);
        $this->dsql->SetQuery($query);
        $this->dsql->Execute(0);
        while ($row = $this->dsql->GetObject(0)) {
            //检查已经查询过的部门ID,如果已经查询过,则跳过
            $DepArray_radiophps = explode(',', rtrim($DepArray_radiophp, ","));
            if (in_array($row->dep_id, $DepArray_radiophps)) {
                //dump($row->dep_id);
                continue;
            }
            $nss="";

            $dep_name = $row->dep_name;
            $dep_id = $row->dep_id;
            echo "<table  width='630px'  border='0' cellspacing='0' cellpadding='2'>\r\n";
            echo "  <tr>\r\n";
            echo "  <td style='background-color:#FBFCE2;' class='bline'><table width='630px'  border='0' cellspacing='0' cellpadding='0'><tr><td width='50%'>{$nss}<strong>{$dep_name}</strong>";
            echo "<br>" . $this->GetTotalEmp($dep_id,$keyword) . "  ";
            echo "    </td></tr></table></td></tr>\r\n";
            echo "  <tr><td >";
            echo "    <table  width='630px'  border='0' cellspacing='0' cellpadding='0'>\r\n";
            $this->LogicListAllSunDep($dep_id, "　",$keyword,$no_emp_dep);
            echo "    </table>\r\n";
            echo "</td></tr>\r\n</table>\r\n";
            $DepArray_radiophp .= $row->dep_id . ",";
        }
    }


    /**
     *  获得子类目的递归调用
     *
     * @access    public
     *
     * @param     int    $id   工种ID
     * @param     string $step 层级标志
     *
     * @return    void
     */
    function LogicListAllSunDep($id, $step,$keyword="",$no_emp_dep="")
    {
        global $DepArray_radiophp;    //保存已经查询过的部门ID
        global $DepRole;    //保存已经查询过的部门ID
        $fid = $id;
        $wheresql="";
        if ($no_emp_dep != "")             $wheresql = " AND dep_id!='$no_emp_dep'";
        $this->dsql->SetQuery("SELECT * FROM `#@__emp_dep` WHERE dep_reid='{$id}' $wheresql ORDER BY dep_id");
        $this->dsql->Execute($fid);
        if ($this->dsql->GetTotalRow($fid) > 0) {
            while ($row = $this->dsql->GetObject($fid)) {
                if ($DepRole != "") {
                    $DepRoleArrays = explode(',', $DepRole);
                    if (!in_array($row->dep_id, $DepRoleArrays)) {
                        //dump($row->dep_id);
                        continue;
                    }
                }
                $dep_name = $row->dep_name;
                $topid = $row->dep_reid;
                $id = $row->dep_id;
                if ($step == "　") {
                    $stepdd = 2;
                } else {
                    $stepdd = 3;
                }

                echo "<tr height='24' >\r\n";
                echo "<td class='nbline'  width='630px' >";
                echo "<table  width='630px'  border='0' cellspacing='0' cellpadding='0'>";
                echo "<tr onMouseMove=\"javascript:this.bgColor='#FAFCE0';\" onMouseOut=\"javascript:this.bgColor='#FFFFFF';\"><td>";
                echo "$step ";


                //echo"<img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='/images/explode.gif' width='11' height='11'>";

                $nss="";

                echo " {$nss}<strong>$dep_name</strong>";
                echo "<br>$step" . $this->GetTotalEmp($id,$keyword) . "  ";
                echo "</td><td align='right'>";
                echo "</td></tr></table></td></tr>\r\n";


                echo "  <tr><td id='suns" . $id . "' ><table  width='630px'  border='0' cellspacing='0' cellpadding='0'>";
                $this->LogicListAllSunDep($id, $step . "　");
                echo "</table></td></tr>\r\n";
                $DepArray_radiophp .= $id . ",";
            }
        }
    }


}//End Class