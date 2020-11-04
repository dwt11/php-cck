<?php


if (!defined('DWTINC')) exit('Request Error!');

/**
 * 部门管理
 *
 * @version        $Id: depunit.class.php 1 15:21 5日
 * @package
 * @copyright
 * @license
 * @link
 */
class DepUnit
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

    function DepUnit()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }

    //显示当前分类的员工人数
    function GetOnlyTotalEmp($tid)
    {
        $total = 0;
        $this->dsql = $GLOBALS['dsql'];
        $this->dsql->SetQuery("SELECT emp_dep,count(emp_dep) as dd FROM `#@__emp` WHERE emp_isdel=0 and  emp_dep=" . $tid . "   group by emp_dep");
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $total = $row['dd'];
        }
        return $total;
    }

    //显示当前分类的员工人数
    function GetOnlyTotalClient($tid)
    {
        $total = 0;
        $this->dsql = $GLOBALS['dsql'];
        $this->dsql->SetQuery("SELECT count(id) as dd FROM `#@__client_depinfos` WHERE isdel=0 and  depid='$tid'");
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $total = $row['dd'];
        }
        return $total;
    }


    //显示当前分类(包含子分类)的人数
    function GetChildTotalEmp($tid)
    {
        global $DepArray;
        $DepArray = "";
        $this->dsql = $GLOBALS['dsql'];
        $depids = GetDepChilds($tid);
        $sqlstr = "SELECT count(*) as dd FROM `#@__emp` WHERE emp_isdel=0 and   emp_dep in (" . $depids . ") ";
        // dump($sqlstr);
        $this->dsql->SetQuery($sqlstr);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $total = $row['dd'];
        }
        if ($total == "") $total = "0";
        return $total;
    }



    /*150925删除改为使用emp.help中的GetDepChilds
        //返回当前所选定的部门  的所有下级部门的子ID，列表供查询相关部门下包含的记录时使用
        function GetDepChildArray($selid=0)
        {
            global $DepArray, $dsql;


            //当前选中的部门
            if($selid > 0)
            {
                //$row = $dsql->GetOne("SELECT * FROM `#@__emp_dep` WHERE dep_id='$selid'");
                $DepArray .= $selid.",";
                $this->LogicGetDepArray($selid,$dsql);
            }

            //echo $OptionDepArrayList;
            return rtrim($DepArray, ",");
        }
        function LogicGetDepArray($selid,&$dsql)
        {
            global $DepArray;
            $dsql->SetQuery("SELECT * FROM `#@__emp_dep` WHERE dep_reid='".$selid."'  ORDER BY dep_id asc");
            $dsql->Execute($selid);
            while($row=$dsql->GetObject($selid))
            {
                $DepArray .= $row->dep_id.",";
                $this->LogicGetDepArray($row->dep_id,$dsql);
            }


        }

    */


    /**
     *  读出所有分类,在类目管理页(list_type)中使用
     *
     * @access    public
     *
     * @param     int $channel 频道ID
     * @param     int $nowdir  当前操作ID
     *
     * @return    string
     */
    function ListAllDep($nowdir = 0)
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->dsql->SetQuery("SELECT * FROM `#@__emp_dep` WHERE dep_reid=0 ORDER BY dep_id");
        $this->dsql->Execute(0);
        while ($row = $this->dsql->GetObject(0)) {

            $lastid = GetCookie('lastCid');
            $dep_name = $row->dep_name;
            $dep_id = $row->dep_id;
            //  dump($dep_name);
            $dep_id = $row->dep_id;
            echo "<ol class='dd-list'>\r\n";
            echo "    <li class='dd-item'>\r\n";

            /*            //如果有子类 则输出可以点击的连接,
            160111修改为netbale
            //这里有BUG,原来旧界面的ajax动态获取值实现不了,因为无法得到+号-号的当前状态. 现在是直接加载所有的数据,后期再改为AJAX的
                        $imgfile = "explode";
                        if ($lastid == $dep_id || isset($GLOBALS['exallct'])) $imgfile = "contract";//dump("1");}
                        if ($this->isSun($dep_id))
                        {
                            echo "<img style='cursor:pointer' id='img" . $dep_id . "' onClick=\"LoadSuns('suns" . $dep_id . "',$dep_id);\" src='../images/$imgfile.gif' width='11' height='11'>";
                        } else {
                            echo "<img   src='../images/empty.gif' width='11' height='11'>";
                        }*/

            echo "        <div class='dd-handle'><span class='label label-info'></span>$dep_name\r\n";
            echo "        <small  class='text-muted'>";
            echo "(部门总人数:" . GetDepAndChildTotalEmpNumb($dep_id);
            if ($this->isSun($dep_id)) echo ",不包含子部门：" . $this->GetOnlyTotalEmp($dep_id);
            echo ")        </small> \r\n";
            echo "            <span class='pull-right'>\r\n";

            require_once(DWTPATH . "/include/role.class.php");
            $roleCheck = new roleClass();
            //echo getUseGoods($dep_id);
            echo $roleCheck->RoleCheckToLink("emp/dep_add.php?dep_id={$dep_id}", "添加子部门", "", true);
            echo $roleCheck->RoleCheckToLink("emp/dep_edit.php?dep_id={$dep_id}", "", "", true);
            echo $roleCheck->RoleCheckToLink("emp/dep_del.php?dep_id={$dep_id}");


            echo "            </span></div>\r\n";
            $this->LogicListAllSunDep($dep_id, "　");
            echo "    </li>\r\n</ol>\r\n";
        }
    }

//是否包含子分类
    function isSun($id)
    {
        $this->dsql2 = $GLOBALS['dsql'];
        //如果有子类 则输出可以点击的连接,
        $this->dsql2->SetQuery("SELECT * FROM `#@__emp_dep` WHERE dep_reid='" . $id . "' ORDER BY dep_id");
        $this->dsql2->Execute($id);
        if ($this->dsql2->GetTotalRow($id) > 0) {
            return true;
        } else {
            return false;
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
    function LogicListAllSunDep($id, $step)
    {
        $fid = $id;
        $sql="SELECT * FROM `#@__emp_dep` WHERE dep_reid='$id' ORDER BY dep_id";
        //dump($sql);
        $this->dsql->SetQuery($sql);
        $this->dsql->Execute($fid);
        if ($this->dsql->GetTotalRow($fid) > 0) {
            while ($row = $this->dsql->GetObject($fid)) {
                $dep_name = $row->dep_name;
                $id = $row->dep_id;

                echo "<ol class='dd-list'>\r\n";
                echo "    <li class='dd-item'>\r\n";
                echo "        <div class='dd-handle'>$dep_name\r\n";
                echo "            <small  class='text-muted'>(部门总人数:" . GetDepAndChildTotalEmpNumb($id);
                if ($this->isSun($id)) echo ",不包含子部门：" . $this->GetOnlyTotalEmp($id);
                echo ")           </small> \r\n";
                echo "            <span class='pull-right'>\r\n";

                //require_once(DWTPATH . "/include/role.class.php");
                //$roleCheck = new roleClass();

                //这里暂时不启用权限判断   ,如果启用权限判断 的话,  "公司名""部门名""子部门名",这个第三级只能出来一个,剩下的出不来170404
                echo "
                        <a onclick=\"layer.open({type: 2,title: '添加子部门', content: 'dep_add.php?dep_id={$id}'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='添加子部门' > 添加子部门 </a>
                        <a onclick=\"layer.open({type: 2,title: '编辑', content: 'dep_edit.php?dep_id={$id}'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='编辑' > 编辑 </a>
                        <a onclick=\"layer.open({type: 2,title: '移动', content: 'dep_move.php?dep_id={$id}'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='移动' > 移动 </a>
                 <a onclick=\"layer.confirm('您确定要删除此内容吗？', {icon: 3, title: '提示'}, function (index) {location.href = 'dep_del.php?dep_id={$id}';layer.close(index);});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='删除' > 删除 </a>      
                        ";


                //echo $roleCheck->RoleCheckToLink("emp/dep_add.php?dep_id={$id}", "添加子部门", "", true);
                // echo $roleCheck->RoleCheckToLink("emp/dep_edit.php?dep_id={$id}", "", "", true);
                // echo $roleCheck->RoleCheckToLink("emp/dep_del.php?dep_id={$id}");

                echo "            </span>\r\n</div>\r\n";
                $this->LogicListAllSunDep($id, $step . "　");
                echo "    </li>\r\n";
                echo "</ol>\r\n";

            }
        }
    }

    /**
     *  返回与某个目相关的下级目录的类目ID列表(删除类目或文章时调用)
     *
     * @access    public
     *
     * @param     int $id      工种ID
     * @param     int $channel 频道ID
     *
     * @return    array
     */
    /*    
    function GetSunDeps($id, $channel=0)
        {
            $this->dsql = $GLOBALS['dsql'];
            $this->idArray[$this->idCounter]=$id;
            $this->idCounter++;
            $fid = $id;
            $this->dsql->SetQuery("SELECT id FROM `#@__emp_dep` WHERE topid=$id");
            $this->dsql->Execute("gs".$fid);

            //if($this->dsql->GetTotalRow("gs".$fid)!=0)
            //{
            while($row=$this->dsql->GetObject("gs".$fid))
            {
                $nid = $row->id;
                $this->GetSunDeps($nid,$channel);
            }
            //}
            return $this->idArray;
        }
    */
    /**
     *  删除
     *
     * @access    public
     *
     * @param     int $id 工种ID
     *
     * @return    string
     */
    function DelDep($id)
    {
        //dump($id);
        $query = "SELECT #@__emp.* FROM `#@__emp`  WHERE #@__emp.emp_dep='$id'";
        $this->dsql = $GLOBALS['dsql'];
//
//        $typeinfos = $this->dsql->GetOne($query);
//        if(!is_array($typeinfos))
//        {
//            return FALSE;
//        }//如果有员工属于此工种则不能删除 
//         dump($query);


        //删除数据库信息
        $sql = "DELETE FROM `#@__emp_dep` WHERE dep_id='$id'";
        //dump($sql);
        $this->dsql->ExecuteNoneQuery($sql);


        return TRUE;
    }


    /**
     * 所有部门信息存入数组 供使用
     *
     */
    function getDepInfoToArray()
    {
        $this->dsql = $GLOBALS['dsql'];
        $returnArray = array();
        $query = "SELECT dep_id,dep_name,dep_info,dep_reid FROM #@__emp_dep  ORDER BY dep_id asc ";
        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetObject()) {
            $row->plantname = base64_encode($row->dep_name);
            //格式 (id,上级ID,名称)
            $returnArray[] = array("id" => $row->dep_id, "reid" => $row->dep_reid, "dep_name" => $row->dep_name);
        }
        return $returnArray;

    }


}//End Class




function getUseGoods($depid){
    $dsql = $GLOBALS['dsql'];
    $sql1 = "SELECT count(*) as dd  FROM `#@__sys_goods_orderdetails` WHERE  depid='$depid'";
    $row = $dsql->GetOne($sql1);
    if (is_array($row)) {
        $depnumb = $row["dd"];
    }

    $dep_view = "<a onclick=\"layer.open({type: 2,title: '使用功能', content: 'dep.useUrlAdd.php?depid={$depid}'});\"  href='javascript:'  >使用功能(" . $depnumb . ")</a> ";
    return $dep_view;
}
