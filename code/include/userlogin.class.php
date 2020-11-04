<?php   if(!defined('DWTINC')) exit('Request Error!');

session_start();








/**
 * 登录类
 *
 * @package          userLogin
 * @subpackage
 * @link
 */
class userLogin
{
	var $userName = '';  //登录前存储用户填写的登录名或手机号,登录后存储用户登录名
	var $userPwd = '';   //密码
	var $userID = '';    //登录ID
	var $userEmpID = ''; //员工ID
	var $userType = '';  //用户权限值
	var $userMobilephone = '';  //手机号
	var $keepuserNameTag = 'x_admin_name';
	var $keepUserIDTag = 'x_admin_id';
	var $keepUserEmpIDTag = 'x_admin_empid';
	var $keepUserTypeTag = 'x_admin_type';
	var $keepuserMobilephoneTag = 'x_admin_mobilephone';

	//php5构造函数
	function __construct()
	{
		if(isset($_SESSION[$this->keepUserIDTag]))
		{
			$this->userName = $_SESSION[$this->keepuserNameTag];
			$this->userMobilephone = $_SESSION[$this->keepuserMobilephoneTag];
			$this->userID = $_SESSION[$this->keepUserIDTag];
			$this->userEmpID = $_SESSION[$this->keepUserEmpIDTag];
			$this->userType = $_SESSION[$this->keepUserTypeTag];
		}
	}


	/**
	 *  检验用户是否正确
	 *
	 * @access    public
	 * @param     string    $userName  用户名
	 * @param     string    $userpwd  密码
	 * @return    string
	 */
	function checkUser($userName, $userpwd)
	{

		//$this->sysInfoToConfig();//写入系统运行信息

		global $dsql;

		//dump($userName);
		//只允许用户名和密码用0-9,a-z,A-Z,'@','_','.','-'这些字符
		//$this->userName = preg_replace("/[^0-9a-zA-Z_@!\.-]/", '', $userName);
		$this->userName =  $userName;
		$this->userPwd = preg_replace("/[^0-9a-zA-Z_@!\.-]/", '', $userpwd);
		$pwd = substr(md5($this->userPwd), 5, 20);

		$sql="
                SELECT admin.*,emp.emp_mobilephone 
                FROM `#@__sys_admin` admin  
                LEFT JOIN `#@__emp` emp ON emp.emp_id=admin.empid  
                WHERE (admin.userName LIKE '".$this->userName."'  OR emp.emp_mobilephone LIKE '$this->userName')
                 AND 
                 (
                    (emp.emp_isdel=0 AND admin.empid>0)
                        OR admin.empid=0
                )
                        LIMIT 0,1";
		//dump($sql);
		$dsql->SetQuery($sql);
		$dsql->Execute();
		$row = $dsql->GetObject();
		if(!isset($row->pwd)){return -1;}
		else if($pwd!=$row->pwd){return -2;}
		else{
			//dump($row->pwd);
			$loginip = GetIP();
			$this->userID = $row->id;
			$this->userEmpID = $row->empid;
			$this->userType = $row->usertype;
			$this->userName = $row->userName;
			$this->userMobilephone = $row->emp_mobilephone;
			$inquery = "UPDATE `#@__sys_admin` SET loginip='$loginip',logintime='".time()."',loginnumb=loginnumb+1 WHERE id='".$row->id."'";
			$dsql->ExecuteNoneQuery($inquery);
			return 1;
		}
	}










	/**
	 *  保持用户的会话状态
	 *
	 * @access    public
	 * @return    int    成功返回 1 ，失败返回 -1
	 */
	function keepUser()
	{

		//dump($_SESSION[$this->keepUserIDTag]."----");
		//if($this->userID != '' && $this->userType != '')
		if($this->userID != '' )
		{
			//升级到PHP7,注释不用户的函数160312
			//@session_register($this->keepUserIDTag);
			$_SESSION[$this->keepUserIDTag] = $this->userID;
			//dump($this->userID);

			//@session_register($this->keepUserEmpIDTag);
			$_SESSION[$this->keepUserEmpIDTag] = $this->userEmpID;

			//@session_register($this->keepUserTypeTag);
			$_SESSION[$this->keepUserTypeTag] = $this->userType;

			//@session_register($this->keepuserNameTag);
			$_SESSION[$this->keepuserNameTag] = $this->userName;

			$_SESSION[$this->keepuserMobilephoneTag] = $this->userMobilephone;

			//PutCookie('DwtUserID', $this->userID, 3600 * 24, '/');
			//PutCookie('DwtLoginTime', time(), 3600 * 24, '/');
			return 1;
		}
		else{return -1;}
	}


	//
	/**
	 *  结束用户的会话状态
	 *
	 * @access    public
	 * @return    void
	 */
	function exitUser()
	{
		//ClearMyAddon();

		//升级到PHP7,注释不用户的函数160312
		//@session_unregister($this->keepUserEmpIDTag);
		//@session_unregister($this->keepUserIDTag);
		//@session_unregister($this->keepUserTypeTag);
		//@session_unregister($this->keepuserNameTag);
		$_SESSION[$this->keepUserIDTag] = "";
		$_SESSION[$this->keepUserEmpIDTag] = "";
		$_SESSION[$this->keepUserTypeTag] = "";
		$_SESSION[$this->keepuserNameTag] = "";
		//DropCookie('DwtUserID');
		//DropCookie('DwtLoginTime');
		$_SESSION[$this->keepuserMobilephoneTag] = '';
		$_SESSION = array();
	}

    /**
     *  获得用户的权限组名称
     *
     * @access    public
     * @return    int
     */
    function getUserTypeName()
    {
        global $dsql;
        if($this->userType != ''){

            $query44 = "SELECT typename FROM #@__sys_admintype  WHERE rank='{$this->userType}'";
            $row44 = $dsql->getone($query44);
            if (isset($row44["typename"]) && $row44["typename"]!="") {
                return $row44["typename"];
            }
        }
        else{return "";}
    }


	/**
	 *  获得用户的权限值
	 *
	 * @access    public
	 * @return    int
	 */
	function getUserType()
	{
		if($this->userType != ''){return $this->userType;}
		else{return -1;}
	}

	/**
	 *  获取用户权限值
	 *
	 * @access    public
	 * @return    int

	function getUserRank()
	{
	return $this->getUserType();
	}*/

	/**
	 *  获得用户的ID
	 *
	 * @access    public
	 * @return    int
	 */
	function getUserId()
	{
		if($this->userID != '')
		{
			return $this->userID;
		}
		else
		{
			return -1;
		}
	}

	/**
	 *  获得用户的员工ID
	 *
	 * @access    public
	 * @return    int
	 */
	function getUserEmpID()
	{
		if($this->userEmpID != '')
		{
			return $this->userEmpID;
		}
		else
		{
			return -1;
		}
	}

	/**
	 *  获得用户的登录名
	 *
	 * @access    public
	 * @return    string
	 */
	function getuserName()
	{
		if($this->userName != '')
		{
			return $this->userName;
		}
		else
		{
			return -1;
		}
	}


	/**
	 *  获得用户的登录手机号
	 *
	 * @access    public
	 * @return    string
	 */
	function getuserMobilephone()
	{
		if($this->userMobilephone != '')
		{
			return $this->userMobilephone;
		}
		else
		{
			return -1;
		}
	}


	/*//系统运行信息到数据库并判断150128
	function sysInfoToConfig()
	{
		$obj = new COM("PHPdll.dwt11");//调用VB写的DLL，PHPdll是工程名，test是类名
		$new_computer_code=$obj->getCode(); // 获得机器码

		global $dsql;
		//获取数据库保存的机器码
		$sql="SELECT value FROM `#@__sys_sysconfig`  WHERE aid='1000'";
		$dsql->SetQuery($sql);
		$dsql->Execute();
		$row = $dsql->GetObject();
		if($new_computer_code!=$row->value)
		{//如果获取的机器码与数据库保存不一致,则更新数据库的机器码为最新的,并设定系统的开始日期为当前日期
			$inquery = "UPDATE `#@__sys_sysconfig` SET value='$new_computer_code' WHERE aid='1000'";
			$dsql->ExecuteNoneQuery($inquery);
			//此处存在问题,如果数据库中没有时间或0的话,不会更新随后再想怎么处理??????150129
			$inquery = "UPDATE `#@__sys_sysconfig` SET value='".time()."' WHERE aid='2001'";
			$dsql->ExecuteNoneQuery($inquery);
		}

		//更新系统运行次数
		$inquery = "UPDATE `#@__sys_sysconfig` SET value=value+1 WHERE aid='2000'";
		$dsql->ExecuteNoneQuery($inquery);

	}*/

}
