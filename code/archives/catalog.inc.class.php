<?php if (!defined('DWTINC')) exit("Request Error!");
/**
 * 外部调用获取相关的栏目信息
 *
 * @version        $Id: Catalog.Inc.class.php 1 15:21 5日
 * @package
 * @copyright
 * @license
 * @link
 */

/**
 * 外部调用栏目类
 *
 * @package          CatalogInc
 * @subpackage
 * @link
 */
class archivesCatalogInc
{
    var $dsql;

    //构造函数///////
    //php5构造函数
    function __construct()
    {
        $this->dsql = $GLOBALS['dsql'];
    }

    //对于使用默认构造函数的情况
    //GetPositionLink()将不可用
    function archivesCatalogInc()
    {
        $this->__construct();
    }

    //关闭数据库连接，析放资源
    function Close()
    {
    }






    /*地址中带有栏目分类的权限判断 XXX/XXX.PHP?CID=
      系统中栏目分类默认为 循环获取当前功能页面的CID的,所有上级功能页面对应的权限 数据
      url地址中的typeid必须紧跟在?号后面

         //    则判断地址是否为 文档管理 添加 编辑 删除的地址
        //       如果是则用于判断权限的地址要加上？后的参数
        //       如果不是文档管理的地址，则只取不带参数的地址 用于权限判断

        //如果再有其他的功能要带参数的权限判断 这段还可扩充，


      主要有:文档管理


       * @param     string  $n  功能名称  (已经在Test_webRole判断过,无权限.但地址中包含栏目分类CID参数,在这里再判断它的父栏目是否有权限 )
    */


    //使用当前访问地址,得到当前栏目ID的 所有上级栏目地址
    //role.func.php//中使用


    //150129优化传进CID获取所有上级的CID数组
    function GetAllParentUrlToRole($typeid)
    {

        //获取当前ID的所有上级
        global $reidArray;
        $reidArray = "";
        $this->LogicGetAllParentUrl($typeid);
        //dump($reidArray."0------");

        return $reidArray;
    }

    /**
     *  逻辑递归  获取当前栏目的 所有上级ID  返回数组 供userlogin.class.php权限判断
     *
     * @access    public
     *
     * @param     int $id 栏目ID
     *
     * @return    array
     */
    function LogicGetAllParentUrl($id)
    {
        global $reidArray;
        $this->dsql->SetQuery("SELECT id,reid FROM #@__archives_type WHERE id='" . $id . "'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        if ($this->dsql->GetTotalRow($id) > 0) {
            while ($row = $this->dsql->GetObject($id)) {
                $reid = $row->reid;
                if ($reid > 0) {
                    $reidArray[] = $reid;
                    $this->LogicGetAllParentUrl($reid);
                }
            }
        }

    }

















    //---------------------------根据给定的栏目 ID，获取它所有的子类  给index_menu.php 供下拉菜单使用
    //$typeid,指定栏目的ID
    //$ISGETALLSUN是否获取所有的子栏目
    function GetListToMenu($typeid=0, $isautoload = false)//150116增加是否自动加载子类
    {
        $archivesCataloglistToMenu = array();//160606修改，输出X系统的菜单格式
        if (!$this->dsql) $this->dsql = $GLOBALS['dsql'];
        $sql ="SELECT id,typename FROM #@__archives_type WHERE reid='$typeid'";
        $this->dsql->SetQuery($sql);
        $this->dsql->Execute("goods".$typeid);
        while ($row = $this->dsql->GetObject("goods".$typeid)) {
            $id = $row->id;
            $typeName = $row->typename;


            $archivesCataloglistToMenu[$id]["title"]=  $typeName;
            $archivesCataloglistToMenu[$id]["urladd"]= "archives/archives.php?typeid=$id";

        }

        return $archivesCataloglistToMenu;
        //return $cataloglistToMenu;
    }

    /*
     * //160607注释掉 菜单中不获取多级菜单，
     *    //是否包含子分类
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
         *  逻辑递归
         *
         * @access    public
         *
         * @param     int $id   栏目ID
         * @param     int $step 步进标志
         *
         * @return    string


        这个在catalog.do.php   里有单独调用 AJAX获取栏目的子类,菜单用

        function LogicGetListToMenu($fid, $step, $isautoload = false) //150116增加是否自动加载子类
        {
            global $cataloglistToMenu;
            $this->dsql->SetQuery("SELECT id,typename FROM #@__archives_type WHERE reid='" . $fid . "'    ORDER BY   sortrank ASC");
            $this->dsql->Execute($fid);
            if ($this->dsql->GetTotalRow($fid) > 0) {
                $step++;
                while ($row = $this->dsql->GetObject($fid)) {

                    $id = $row->id;
                    $imgfile = "explode";
                    if ($isautoload) $imgfile = "contract";//dump("1");//150116增加是否自动加载子类
                    if ($this->isSun($id) > 0) {
                        $timg = "<img style='cursor:pointer' id='arcimg" . $id . "' onClick=\"arcLoadSuns('arcsuns" . $id . "',$id,$step);\" src='../images/$imgfile.gif' width='11' height='11'> ";
                    } else {
                        $timg = "<img    src='../images/empty.gif' width='11' height='11'> ";
                    }

                    $stepstr = "&nbsp;";  //各级设备分类的分隔间距,不自动加载所有的分类, 所以这里引入了级数
                    for ($stepi = 1; $stepi < $step; $stepi++) {
                        $stepstr .= $stepstr;
                    }

                    $typeName = $row->typename;
                    $cataloglistToMenu .= "  <table class='sunlist'>\r\n";
                    $cataloglistToMenu .= "   <tr>\r\n";
                    $cataloglistToMenu .= "     <td align='left' style='white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>" . $stepstr . $timg . " <a href='archives/archives.php?typeid=" . $id . "'>" . $typeName . "</a></td>\r\n";
                    $cataloglistToMenu .= "   </tr>\r\n";
                    $cataloglistToMenu .= "  </table>\r\n";
                    $cataloglistToMenu .= "<div id='arcsuns" . $id . "' class='sunct'>";
                    if ($isautoload) $this->LogicGetListToMenu($id, $step, $isautoload);
                    $cataloglistToMenu .= "</div>\r\n";
                }
            }
        }*/


    //---------------------------根据给定的栏目 ID，获取它所有的子类  给index_menu.php 供下拉菜单使用


///////////////-----main.php 获取新闻未读内容

    //获取的条数
    function GetTopNoViewdArchives($returnNumb)
    {
        $restrArray = array();//返回字符
        $userCatalogSqlArray = "";
        $whereSql = "";
        $rowi = 0;
        $userid = $GLOBALS['CUSERLOGIN']->getUserId();
        $allRoleCatalogIdarr = $this->GetAllRoleCatalogId();  //获取所有可浏览的栏目ID
        //dump($allRoleCatalogIdarr);


        if (is_array($userCatalogSqlArray) && count($userCatalogSqlArray) > 0) {
            $whereSql = " and  typeid in ($allRoleCatalogIdarr) ";
        }

        $query = "SELECT id,title,senddate FROM `#@__archives` WHERE deptype='-1' and !FIND_IN_SET('$userid', userhistory) $whereSql ORDER BY senddate desc";
        //dump($query);
        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        $total = $this->dsql->GetTotalRow();
        while ($row = $this->dsql->GetObject()) {
            $title = $row->title;
            //if (strlen($title) > 28) $title = cn_substr($title, 28) . "...";
            $restrArray["url"][] = "[" . GetDateNoYearMk($row->senddate) . "] <a href='app/archives_view.php?archivesid=" . $row->id . "' target='_blank'>" . $title . "</a>";
            $rowi++;
            if ($rowi > $returnNumb) break;
        }
        $restrArray["totalNumb"] = $total;

        return $restrArray;
    }


    //----------------获取当前登录用户 所有可浏览的栏目ID,供main.PHP获取当前用户,未查看的内容
    function GetAllRoleCatalogId()
    {
        global $user_catalog_array;
        $web_role = "";
        $user_catalog_array = array();  //160303修改BUG,原为=""
        $usertypes = explode(',', $GLOBALS['CUSERLOGIN']->getUserType());
        //	  dump($usertypes);
        foreach ($usertypes as $usertype) {
            //直接从数据 库获取 权限内容
            if ($usertype > 0) {//修复BUG150814  如果用户没有任何权限 则此处为-1,所以要大于0
                $sql = "SELECT web_role FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='" . $usertype . "'";
                $groupSet = $this->dsql->GetOne($sql);
                $web_role .= $groupSet['web_role'] . "|";
            }
        }

        $web_role = rtrim($web_role, "|");
        $web_roleArray = explode('|', $web_role);
        foreach ($web_roleArray as $urladd) {
            if (strpos($urladd, "archives.php") !== false) {
                $star = strpos($urladd, "typeid=") + 7;
                $lenth = strlen($urladd) - $star;
                $typeid = substr($urladd, $star, $lenth);   //得到CID
                $user_catalog_array[] = $typeid;
                $this->LogicGetAllRoleCatalogId($typeid);   //获取当前栏目ID的所有子栏目,系统中,如果父功能具有权限,则代表他所有的子功能,也要权限

            }

        }
        if (is_array($user_catalog_array)) {
            $user_catalog_array = array_unique($user_catalog_array);

            sort($user_catalog_array);
        }
        return $user_catalog_array;

    }

    //递归
    function LogicGetAllRoleCatalogId($id)
    {
        global $user_catalog_array;
        $this->dsql->SetQuery("SELECT id,typename FROM #@__archives_type WHERE reid='" . $id . "'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        while ($row = $this->dsql->GetObject($id)) {
            $user_catalog_array[] = $row->id;
            $this->LogicGetAllRoleCatalogId($row->id);
        }
    }

///////////////-----main.php 获取新闻未读内容


    //---这个sysFunction.class.php中引用------------------------获取栏目OPTION  给sysFunction.class.php 供系统功能添加时使用
    //$depid  如果有分厂级别的 则只按部门ID来显示
    function GetOptionListToSysFunAdd($depid = 0)
    {
        global $optionArrayListToSysFunAdd;//父栏目
        global $optionArrayListToSysFunAdd_sun; //子栏目 
        global $inDateUrlAddArray;  //系统功能 数据表中引用过

        if (!$this->dsql) $this->dsql = $GLOBALS['dsql'];
        $optionArrayListToSysFunAdd = '';
	//这里在x目录下archives，原来有判断多公司的权限 ，随后需要的时候再看
        $query = "SELECT id,typename FROM `#@__archives_type` WHERE  reid=0   ORDER BY   sortrank ASC";

        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while ($row = $this->dsql->GetObject()) {

            //$optionArrayListToSysFunAdd_sun = "";
            // $this->LogicGetOptionListToSysFunAdd($row->id, "─");

            $funUrladd = "archives/archives.php?typeid=" . $row->id;
            if (!is_array($inDateUrlAddArray)) $inDateUrlAddArray = array();//160606修改BUG，如果子公司第一次添加功能，则此处没有数组
            if (!in_array($funUrladd, $inDateUrlAddArray))//如果此地址没有在 系统功能 数据表中引用过，则添加到OPTION中
            {
                //dump( $inDateUrlAddArray);
                $optionArrayListToSysFunAdd .= "<option value='$funUrladd'  style='background-color:#FFFF00;color:#666666'>&nbsp;&nbsp;" . $row->typename . "</option>\r\n";
                // $optionArrayListToSysFunAdd .= $optionArrayListToSysFunAdd_sun;//170106添加功能时，只获取一级分类，其下的子目录不再获取
            }
        }
        return $optionArrayListToSysFunAdd;
    }

    /**
     *  逻辑递归
     *
     * @access    public
     *
     * @param     int $id   栏目ID
     * @param     int $step 步进标志
     * @param     int $oper 操作权限
     *
     * @return    string
     */
    function LogicGetOptionListToSysFunAdd($id, $step)
    {
        global $optionArrayListToSysFunAdd_sun;
        global $inDateUrlAddArray;
        $this->dsql->SetQuery("SELECT id,typename FROM #@__archives_type WHERE reid='" . $id . "'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        while ($row = $this->dsql->GetObject($id)) {


            $funUrladd = "archives/archives.php?typeid=" . $row->id;
            if (!in_array($funUrladd, $inDateUrlAddArray)) {
                $optionArrayListToSysFunAdd_sun .= "<option value='$funUrladd'   style='background-color:#FFFF00;color:#666666'>&nbsp;&nbsp;$step" . $row->typename . "</option>\r\n";
                //$this->LogicGetOptionListToSysFunAdd($row->id, $step . "─"); 170106只获取两级，不再递归
            }


        }
    }
    //---这个文件在sysFunction.class.php中引用------------------------获取栏目OPTION  给sysFunction.class.php 供系统功能添加时使用


}
