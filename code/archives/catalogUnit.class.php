<?php


if (!defined('DWTINC')) exit('Request Error!');

/**
 * 栏目单元,主要用户管理后台管理处
 *
 * @version        $Id: typeunit.class.admin.php 1 15:21 5日
 * @package
 * @copyright
 * @license
 * @link
 */
class TypeUnit
{
    var $dsql;
    var $idCounter;
    var $idArrary;
    var $CatalogNums;
    var $indexUrl;

    //php5构造函数
    function __construct()
    {
        $this->indexUrl = $GLOBALS['cfg_install_path'];
        $this->idCounter = 0;
        $this->idArrary = '';
        $this->dsql = 0;
    }

    function TypeUnit()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }

    //获取所有栏目的文档ID数
    function UpdateCatalogNum()
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->dsql->SetQuery("SELECT typeid,count(typeid) as dd FROM `#@__archives_arctiny` WHERE issend <>-2 group by typeid");
        $this->dsql->Execute();
        while ($row = $this->dsql->GetArray()) {
            $this->CatalogNums[$row['typeid']] = $row['dd'];
        }
        //dump($this->CatalogNums);
    }

    function GetTotalArc($tid)
    {
        // dump($tid);
        if (!is_array($this->CatalogNums)) {
            $this->UpdateCatalogNum();
        }
        if (!isset($this->CatalogNums[$tid])) {
            return 0;
        } else {
            $totalnum = 0;
            $ids = explode(',', GetArchiveSonIds($tid));
            foreach ($ids as $tid) {
                if (isset($this->CatalogNums[$tid])) {
                    $totalnum += $this->CatalogNums[$tid];
                }
            }
            return $totalnum;
        }
    }

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
    function ListAllType($channel = 0, $nowdir = 0)
    {
        global $cfg_admin_channel, $user_catalogs;
        $this->dsql = $GLOBALS['dsql'];

        //检测用户有权限的顶级栏目
        if ($cfg_admin_channel == 'array') {
            $user_catalog = join(',', $user_catalogs);
            $this->dsql->SetQuery("SELECT reid FROM `#@__archives_type` WHERE id in($user_catalog) group by reid ");
            $this->dsql->Execute();
            $topidstr = '';
            while ($row = $this->dsql->GetObject()) {
                if ($row->reid == 0) continue;
                $topidstr .= ($topidstr == '' ? $row->reid : ',' . $row->reid);
            }
            $user_catalog .= ',' . $topidstr;
            $user_catalogs = explode(',', $user_catalog);
            $user_catalogs = array_unique($user_catalogs);
        }

        $this->dsql->SetQuery("SELECT tp.*,ch.typename as ctypename FROM `#@__archives_type` tp  LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype  WHERE reid=0 ORDER BY sortrank");
        $this->dsql->Execute(0);
        while ($row = $this->dsql->GetObject(0)) {
            if ($cfg_admin_channel == 'array' && !in_array($row->id, $user_catalogs)) {
                continue;
            }
            $typeName = $row->typename;
            $ispart = $row->ispart;
            $id = $row->id;
            $rank = $row->sortrank;
            $lastid = GetCookie('lastCid');
            if ($row->ishidden == '1') {
                $nss = "<font color='red'>[隐]</font>";
            } else {
                $nss = '';
            }

            echo "<ol class='dd-list'>\r\n";
            echo "<li class='dd-item'>\r\n";
            /*            //如果有子类 则输出可以点击的连接,
            160111修改为netbale
            //这里有BUG,原来旧界面的ajax动态获取值实现不了,因为无法得到+号-号的当前状态. 现在是直接加载所有的数据,后期再改为AJAX的
				$imgfile="explode";
				if( $lastid==$id || isset($GLOBALS['exallct']) )  $imgfile="contract";//dump("1");}
				if($this->isSun($id)>0)
				  {
						  echo "<img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='../images/$imgfile.gif' width='11' height='11'>";
						  
				  }
				  else{
							  echo "<img   src='../images/empty.gif' width='11' height='11'>";
				  
                        }*/


            echo " <div class='dd-handle'><span class='label label-info'></span>" . $nss . $typeName . "[ID:" . $id . "]";
            echo "<small  class='text-muted'>";
            //$ispart==0普通列表输出文档数   //$ispart==1带封面的频道不输出文档数
            if ($ispart == 0) echo " (文档：" . $this->GetTotalArc($id) . ") ";
            //echo "(" . $row->ctypename . ")  ";
            if ($ispart == 1) echo " (频道封面)  ";

            $CUSERLOGIN = new userLogin();

            //150610添加
            //判断_emp_dep_plus是否存在,如果存在,则按顶级部门来查询显示不同的权限组
            if ($GLOBALS['GLOBAMOREDEP']) {
                $sql1 = "SELECT dep_name FROM `#@__emp_dep_plus` p LEFT JOIN  `#@__emp_dep` d on d.dep_id=p.depid WHERE  FIND_IN_SET('" . $id . "',archivesids)";
                $this->dsql->SetQuery($sql1);
                $this->dsql->Execute(1);
                $row1 = $this->dsql->GetObject(1);
                if ($row1 != "") {
                    $depname = "<strong>" . $row1->dep_name . "</strong> ";//150304修改变量名称,不然的话,和系统中冲突
                } else {
                    $depname = " 无分组 ";

                }

                if ($CUSERLOGIN->getUserType() == 10) echo " 所属部门:" . $depname;

            }
            echo " </small> \r\n";


            echo "<span class='pull-right'>\r\n";
            //if($ispart==0)echo "<a href='archives_add.php?typeid={$id}'>增加内容</a>|";
            //echo "<a href='".$this->indexUrl."/web/archives_list.php?tid={$id}' target='_blank'>预览</a>";
            //echo "|<a href='archives.php?typeid={$id}'>管理内容</a>";
            require_once(DWTPATH . "/include/role.class.php");
            $roleCheck = new roleClass();


            if ($CUSERLOGIN->getUserType() == 10) {

                echo " <a href='catalog_add.php?id={$id}'>增加子类</a> ";
                echo " <a href='catalog_edit.php?id={$id}'>更改</a> ";
                if ($ispart == 0) echo " <a href='catalog.do.php?dopost=unitCatalog&typeid={$id}'>合并</a> ";
                echo " <a href='catalog.do.php?dopost=moveCatalog&typeid={$id}'>移动</a> ";
                echo " <a href='catalog_del.php?id={$id}&typeoldname=" . urlencode($typeName) . "'>删除</a> ";
                //	echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table>\r\n";
                echo "</span></div>\r\n";

            }
            //echo "  <tr><td colspan='2' id='suns".$id."'>";
            //if($channel==$id || $lastid==$id || isset($GLOBALS['exallct']) || $cfg_admin_channel=='array')
            //{
            //    echo "    <table width='100%' border='0' cellspacing='0' cellpadding='0'>\r\n";
            $this->LogicListAllSunType($id, "　");
            //    echo "    </table>\r\n";
            //}
            echo "</li>\r\n</ol>\r\n";
        }
    }


//是否包含子分类
    function isSun($id)
    {
        $this->dsql2 = $GLOBALS['dsql'];

        //如果有子类 则输出可以点击的连接,
        $this->dsql2->SetQuery("SELECT id FROM `#@__archives_type` WHERE reid='" . $id . "' ");
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
     * @param     int    $id   栏目ID
     * @param     string $step 层级标志
     *
     * @return    void
     */
    function LogicListAllSunType($id, $step)
    {
        global $cfg_admin_channel, $user_catalogs;
        $fid = $id;
        $this->dsql->SetQuery("SELECT tp.*,ch.typename as ctypename FROM `#@__archives_type` tp  LEFT JOIN `#@__archives_channeltype` ch ON ch.id=tp.channeltype   WHERE reid='" . $id . "' ORDER BY sortrank");
        $this->dsql->Execute($fid);
        if ($this->dsql->GetTotalRow($fid) > 0) {
            while ($row = $this->dsql->GetObject($fid)) {
                if ($cfg_admin_channel == 'array' && !in_array($row->id, $user_catalogs)) {
                    continue;
                }
                $typeName = $row->typename;
                $reid = $row->reid;
                $id = $row->id;
                $ispart = $row->ispart;
                if ($step == "　") {
                    $stepdd = 2;
                } else {
                    $stepdd = 3;
                }
                $rank = $row->sortrank;
                if ($row->ishidden == '1') {
                    $nss = "<font color='red'>[隐]</font>";
                } else {
                    $nss = '';
                }


                echo "<ol class='dd-list'>\r\n";
                echo "<li class='dd-item'>\r\n";
                //echo "<input class='np' type='checkbox' name='tids[]' value='{$id}'>$step ";
                //echo "<img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='../images/explode.gif' width='11' height='11'>";
                //如果有子类 则输出可以点击的连接,
                //dump($this->isSun($id));
                /*                if ($this->isSun($id) > 0) {
                                    echo "<img style='cursor:pointer' id='img" . $id . "' onClick=\"LoadSuns('suns" . $id . "',$id);\" src='/images/contract.gif' width='11' height='11'>";

                                } else {
                                    echo "<img   src='/images/empty.gif' width='11' height='11'>";

                                }*/
                echo "<div class='dd-handle'>" . $nss . $typeName . "[ID:" . $id . "]\r\n";
                echo "<small  class='text-muted'>";
                //普通列表输出文档数   //带封面的频道不输出文档数
                if ($ispart == 0) echo "  (文档：" . $this->GetTotalArc($id) . ")  ";
                //echo "(" . $row->ctypename . ")  ";
                if ($ispart == 1) echo " (频道封面) ";
                echo " </small> \r\n";

                echo "<span class='pull-right'>\r\n";
                //if($ispart==0)echo "<a href='archives_add.php?typeid={$id}'>增加内容</a>|";
                //echo "<a href='".$this->indexUrl."/web/archives_list.php?tid={$id}' target='_blank'>预览</a>";
                //echo "|<a href='archives/archives.php?typeid={$id}'>内容</a>";
                require_once(DWTPATH . "/include/role.class.php");
                $roleCheck = new roleClass();

                echo " <a href='catalog_add.php?id={$id}'>增加子类</a> ";
                echo " <a href='catalog_edit.php?id={$id}'>更改</a> ";
                if ($ispart == 0) echo " <a href='catalog.do.php?dopost=unitCatalog&typeid={$id}'>合并</a> ";
                echo " <a href='catalog.do.php?dopost=moveCatalog&typeid={$id}'>移动</a> ";
                echo " <a href='javascript:isdel(\"catalog_del.php?id={$id}\");'>删除</a> ";


                echo "</span>\r\n</div>\r\n";
                $this->LogicListAllSunType($id, $step . "　");
                echo "</li>\r\n</ol>\r\n";

            }
        }
    }

    /**
     *  返回与某个目相关的下级目录的类目ID列表(删除类目或文章时调用)
     *
     * @access    public
     *
     * @param     int $id      栏目ID
     * @param     int $channel 频道ID
     *
     * @return    array
     */
    function GetSunTypes($id, $channel = 0)
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->idArray[$this->idCounter] = $id;
        $this->idCounter++;
        $fid = $id;
        if ($channel != 0) {
            $csql = " And channeltype=$channel ";
        } else {
            $csql = "";
        }
        $this->dsql->SetQuery("SELECT id FROM `#@__archives_type` WHERE reid=$id $csql");
        $this->dsql->Execute("gs" . $fid);

        //if($this->dsql->GetTotalRow("gs".$fid)!=0)
        //{
        while ($row = $this->dsql->GetObject("gs" . $fid)) {
            $nid = $row->id;
            $this->GetSunTypes($nid, $channel);
        }
        //}
        return $this->idArray;
    }

    /**
     *  删除类目
     *
     * @access    public
     *
     * @param     int $id 栏目ID
     *
     * @return    string
     */
    function DelType($id)
    {
        $this->idCounter = 0;
        $this->idArray = "";
        $this->GetSunTypes($id);
        $query = "
        SELECT #@__archives_type.*,#@__archives_channeltype.typename AS ctypename,
        #@__archives_channeltype.addtable
        FROM `#@__archives_type` LEFT JOIN #@__archives_channeltype
        ON #@__archives_channeltype.id=#@__archives_type.channeltype
        WHERE #@__archives_type.id='$id'
        ";
        $typeinfos = $this->dsql->GetOne($query);
        //$topinfos = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__archives_type` WHERE id='".$typeinfos['topid']."'");
        if (!is_array($typeinfos)) {
            return FALSE;
        }
        //$indir = $typeinfos['typedir'];
        $addtable = $typeinfos['addtable'];
        $ispart = $typeinfos['ispart'];
        //$defaultname = $typeinfos['defaultname'];

        //删除数据库里的相关记录
        foreach ($this->idArray as $id) {
            $myrow = $this->dsql->GetOne("SELECT * FROM `#@__archives_type` WHERE id='$id'");
//            if($myrow['topid']>0)
//            {
//                $mytoprow = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__archives_type` WHERE id='".$myrow['topid']."'");
//                if(is_array($mytoprow) && !empty($mytoprow))
//                {
//                    foreach($mytoprow as $k=>$v)
//                    {
//                        if(!preg_match("/[0-9]/",$k))
//                        {
//                            $myrow[$k] = $v;
//                        }
//                    }
//                }
//            }

            //删除目录和目录里的所有文件 ### 禁止了此功能
            //删除单独页面
//            if($myrow['ispart']==2 && $myrow['typedir']=='')
//            {
//                if( is_file($this->baseDir.'/'.$myrow['defaultname']) )
//                {
//                    @unlink($this->baseDir.'/'.$myrow['defaultname']);
//                }
//            }

            //删除数据库信息
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__archives_type` WHERE id='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__archives_arctiny` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE typeid='$id'");
            // $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__spec` WHERE typeid='$id'");
            // $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE typeid='$id'");
            if ($addtable != "") {
                $this->dsql->ExecuteNoneQuery("DELETE FROM $addtable WHERE typeid='$id'");
            }
        }

        //删除目录和目录里的所有文件 ### 禁止了此功能
        //删除单独页面
        if ($ispart == 2 && $indir == "") {
            if (is_file($this->baseDir . "/" . $defaultname)) {
                @unlink($this->baseDir . "/" . $defaultname);
            }
        }
        @reset($this->idArray);
        $this->idCounter = 0;
        return TRUE;
    }

    /**
     *  删除指定目录的所有文件
     *
     * @access    public
     *
     * @param     string $indir 指定目录
     *
     * @return    int
     */
    function RmDirFile($indir)
    {
        if (!file_exists($indir)) return;
        $dh = dir($indir);
        while ($file = $dh->read()) {
            if ($file == "." || $file == "..") {
                continue;
            } else if (is_file("$indir/$file")) {
                @unlink("$indir/$file");
            } else {
                $this->RmDirFile("$indir/$file");
            }
            if (is_dir("$indir/$file")) {
                @rmdir("$indir/$file");
            }
        }
        $dh->close();
        return (1);
    }
}//End Class