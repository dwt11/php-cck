<?php   if(!defined('DWTINC')) exit("Request Error!");
/**
 * 栏目连接
 *
 * @version        $Id: typelink.class.php 1 15:21 5日
 * @package
 * @copyright
 * @license
 * @link
 */
//require_once("channelunit.func.php");

/**
 * 栏目连接类
 *
 * @package          TypeLink
 * @subpackage
 * @link
 */
class TypeLink
{
    var $typeDir;
    var $dsql;
    var $modDir;
    var $indexUrl;
    var $TypeInfos;
    var $SplitSymbol;
    var $valuePosition;
    var $valuePositionName;
    var $OptionArrayList;
    var $ispart; //当前浏览的栏目页面  是否频道封面，在archives.PHP页面中使用

    //构造函数///////
    //php5构造函数
    function __construct($typeid=0)
    {
        $this->indexUrl = $GLOBALS['cfg_install_path'];
        $this->SplitSymbol = " > ";
        $this->dsql = $GLOBALS['dsql'];
        $this->TypeID = $typeid;
        $this->valuePosition = '';
        $this->valuePositionName = '';
        $this->OptionArrayList = '';
        $this->ispart = '';

        //载入类目信息
        $query = "SELECT tp.*,ch.typename as ctypename,ch.addtable FROM `#@__archives_type` tp LEFT JOIN `#@__archives_channeltype` ch
        on ch.id=tp.channeltype  WHERE tp.id='$typeid' ";
        if($typeid > 0)
        {
            $this->TypeInfos = $this->dsql->GetOne($query);
            if(is_array($this->TypeInfos))
            {
                $this->TypeInfos['tempindex'] = $this->TypeInfos['tempindex'];
                $this->TypeInfos['templist'] = $this->TypeInfos['templist'];
                $this->TypeInfos['temparticle'] = $this->TypeInfos['temparticle'];
            }
        }
    }

    //对于使用默认构造函数的情况
    //GetPositionLink()将不可用
    function TypeLink($typeid)
    {
        $this->__construct($typeid);
    }

    //关闭数据库连接，析放资源
    function Close()
    {
    }

    //重设类目ID
    function SetTypeID($typeid)
    {
        $this->TypeID = $typeid;
        $this->valuePosition = "";
        $this->valuePositionName = "";
        $this->typeDir = "";
        $this->OptionArrayList = "";

        //载入类目信息
        $query = "
        SELECT #@__archives_type.*,#@__archives_channeltype.typename as ctypename
        FROM #@__archives_type LEFT JOIN #@__archives_channeltype
        ON #@__archives_channeltype.id=#@__archives_type.channeltype WHERE #@__archives_type.id='$typeid' ";
        $this->dsql->SetQuery($query);
        $this->TypeInfos = $this->dsql->GetOne();
    }


    //获得某类目的链接列表 如：类目一>>类目二>> 这样的形式
    //islink 表示返回的列表是否带连接
    function GetArchivePositionLink($islink=true)
    {
        $indexpage = "<a href='".$this->indexUrl."/'>首页</a>";
        if($this->valuePosition!="" && $islink)
        {
            return $this->valuePosition;
        }
        else if($this->valuePositionName!="" && !$islink)
        {
            return $this->valuePositionName;
        }
        else if($this->TypeID==0)
        {
            if($islink)
            {
                return $indexpage;
            }
            else
            {
                return "没指定分类！";
            }
        }
        else
        {
            if($islink)
            {
                $this->valuePosition = $this->GetArchiveOneTypeLink($this->TypeInfos);
                if($this->TypeInfos['reid']!=0)
                {
                    //调用递归逻辑
                    $this->LogicGetArchivePosition($this->TypeInfos['reid'],true);
                }
                $this->valuePosition = $indexpage.$this->SplitSymbol.$this->valuePosition;
                return $this->valuePosition.$this->SplitSymbol;
            }
            else
            {
                $this->valuePositionName = $this->TypeInfos['typename'];
                if($this->TypeInfos['reid']!=0)
                {
                    //调用递归逻辑
                    $this->LogicGetArchivePosition($this->TypeInfos['reid'],false);
                }
                return $this->valuePositionName;
            }
        }
    }

    //获得名字列表
    function GetArchivePositionName()
    {
        return $this->GetArchivePositionLink(false);
    }

    //获得某类目的链接列表，递归逻辑部分
    function LogicGetArchivePosition($id,$islink)
    {
        $this->dsql->SetQuery("SELECT id,reid,typename,ispart FROM #@__archives_type WHERE id='".$id."'");
        $tinfos = $this->dsql->GetOne();
        if($islink)
        {
            $this->valuePosition = $this->GetArchiveOneTypeLink($tinfos).$this->SplitSymbol.$this->valuePosition;
        }
        else
        {
            $this->valuePositionName = $tinfos['typename'].$this->SplitSymbol.$this->valuePositionName;
        }
        if($tinfos['reid']>0)
        {
            $this->LogicGetArchivePosition($tinfos['reid'],$islink);
        }
        else
        {
            return 0;
        }

    }

    //获得某个类目的超链接信息
    function GetArchiveOneTypeLink($typeinfos)
    {
        $typepage = $this->GetArchiveOneTypeUrl($typeinfos);
        $typelink = "<a href='".$this->indexUrl.$typepage."'>".$typeinfos['typename']."</a>";
        return $typelink;
    }

    //获得某分类连接的URL
    function GetArchiveOneTypeUrl($typeinfos)
    {
        return GetArchiveTypeUrl($typeinfos['id']);
    }






































    //返回当前栏目是否频道封面，必须先引用 GetOptionArray  才能引用这个功能 
    function GetArchiveIspart()
    {
        return $this->ispart;
    }


    //获得栏目类别的SELECT列表
    //hid 是指默认选中类目，0 表示“请选择类目”或“不限类目”
    //oper 是用户允许管理的类目，0 表示所有类目
    //channeltype 是指类目的内容类型，0 表示不限频道
    function GetArchiveOptionArray($hid=0)
    {
        return $this->GetArchiveOptionList($hid);
    }

    function GetArchiveOptionList($hid=0)
    {
        $channeltype=0;
        if(!$this->dsql) $this->dsql = $GLOBALS['dsql'];
        $this->OptionArrayList = '';
        
        //获得当前选择的栏目
		if($hid>0)
        {
            $row = $this->dsql->GetOne("SELECT id,typename,ispart,channeltype,reid FROM #@__archives_type WHERE id='$hid'");
            $channeltype = $row['channeltype'];  //得到当前模型的ID
            $this->ispart=$row['ispart'];  //当前浏览的栏目页面  是否频道封面，在archives.PHP页面中使用
            
			$style="style='background-color:#DFDFDB;color:#888888'";
			if($row['reid']=="0")$style=" class='option1'";
			
			
			if($row['ispart']==1) {
                $this->OptionArrayList .= "<option value='".$row['id']."' ".$style."  selected>".$row['typename']."</option>\r\n";
            }
            else {
                $this->OptionArrayList .= "<option value='".$row['id']."' selected>".$row['typename']."</option>\r\n";
            }
        }


		//dump($channeltype);
		if($channeltype==0) $ctsql = '';
        else $ctsql=" AND channeltype='$channeltype' ";  //限定在当前的模型ID里搜索 栏目
        
        $user_catalog=GetCatalogIdFormWebRole();    //获取当前登录用户 可以操作的栏目ID
	    if($user_catalog != '')
        {
            $query = "SELECT id,typename,ispart,reid FROM `#@__archives_type` WHERE  id in({$user_catalog})  $ctsql ";
        }
        else
        {
            $query = "SELECT id,typename,ispart,reid FROM `#@__archives_type` WHERE  reid=0 $ctsql   ORDER BY   sortrank ASC";
        }


//dump($query);
        $this->dsql->SetQuery($query);
        $this->dsql->Execute();
        while($row=$this->dsql->GetObject())
        {
            if($row->id!=$hid)
            {
				$style="style='background-color:#DFDFDB;color:#888888'";
				if($row->reid=="0")$style=" class='option1'";
                if($row->ispart==1) {
                    $this->OptionArrayList .= "<option value='".$row->id."' ".$style." >".$row->typename."</option>\r\n";
                }
                else {
                    $this->OptionArrayList .= "<option value='".$row->id."'>".$row->typename."</option>\r\n";
                }
            }
            $this->LogicGetArchiveOptionArray($row->id, "─");
        }
        return $this->OptionArrayList;
    }

    /**
     *  逻辑递归
     *
     * @access    public
     * @param     int   $id   栏目ID
     * @param     int   $step   步进标志
     * @return    string
     */
    function LogicGetArchiveOptionArray($id, $step)
    {
        
        $this->dsql->SetQuery("SELECT id,typename,ispart FROM #@__archives_type WHERE reid='".$id."'    ORDER BY   sortrank ASC");
        $this->dsql->Execute($id);
        while($row=$this->dsql->GetObject($id))
        {
            if($row->ispart==1) {
                $this->OptionArrayList .= "<option value='".$row->id."' style='background-color:#EFEFEF;color:#666666'>$step".$row->typename."</option>\r\n";
            }
            else {
                $this->OptionArrayList .= "<option value='".$row->id."'>$step".$row->typename."</option>\r\n";
            }
            $this->LogicGetArchiveOptionArray($row->id, $step."─");
        }
    }
	
}//End Class








/********这一段权限判断代码是否要清除掉随后要看*****/
////根据当前的网址，如果是archives_相关的，则根据网址，
//从当前登录用户的权限组,获取栏目ID，
//然后获取后有的子栏目ID，存入string 供SQL使用
// return String
function GetCatalogIdFormWebRole()
{
	  //2用户有属于多个权限组的话,则合并输出页面权限值
	 $dsql= $GLOBALS['dsql'];
	  $web_role="";
	  $user_catalog="";
	  $usertypes = explode(',', $GLOBALS['CUSERLOGIN'] -> getUserType());
	  foreach($usertypes as $usertype)
	  {
			//直接从数据 库获取 权限内容
			$sql="SELECT web_role FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='".$usertype."'";
			$groupSet =$dsql->GetOne($sql);
			$web_role .= $groupSet['web_role']."|";
	  }
  
	  //2.1如果是管理员 则返回空 ，代表可查看所有
	  if(preg_match('/admin_AllowAll/i',$web_role))
	  {
		  return "";
	  }
	 
  
	  $web_role = rtrim($web_role,"|");
	  $web_roleArray = explode('|', $web_role);
  
//dump($web_roleArray);

	  $nowurl=ltrim(ClearUrlAddParameter(GetCurUrl()),"/");  //清除当前地址中的参数（当前地址最前面有/，所以也清除掉）
	 // dump($nowurl);    
	  foreach($web_roleArray as $urladd)
	  {
			if ( strpos(  $urladd,$nowurl  ) !==false &&(strpos( $urladd , "typeid=" )!==false))    //141206加 &&(strpos( $urladd , "cid=" )!==false   不加这个的话,下面的star起始位置会出错
		   {
				  $star=strpos( $urladd , "typeid=" )+4;
				  $lenth=strlen($urladd)-$star;
				  $typeid=substr($urladd,$star,$lenth);   //得到CID
				  $user_catalog.=$typeid.",";
		   }		  
	 }
	 if($user_catalog!="") $user_catalog = rtrim($user_catalog,",");   //清除最右侧 多余的，号




	return $user_catalog;

}
