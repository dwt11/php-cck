<?php  if(!defined('DWTINC')) exit('dwtx');
/**
 * ?????此文件是否有用待查,好像没用 150129
 权限小助手
 *
 * @version        $Id: grouptype.helper.php 1 16:49 20141009
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */




 


/**
 *  根据 多个权限组 id 获取 权限组的名称
 *4处调用
 * @param     string  $trank  权限值ID
 * @return    string
 */
if ( ! function_exists('GetUserTypeNames'))
{
    function GetUserTypeNames($trank)
    {
		global $groupRanks;
        //dump($groupRanks);
        if(!is_array($groupRanks))
        {
      // dump(is_array($groupRanks));
            GetGroupRanks();   
        }
        //dump($trank);
        $rankNames="";
		$usertypes = explode(',', $trank);
		//   dump($usertypes); 
		//$ns = explode(',',$n);
		foreach($usertypes as $usertype)
		{
		   //if(isset($groupRanks[$usertype])) $rankNames.=$usertype." ".$groupRanks[$usertype]."&nbsp;&nbsp;&nbsp;&nbsp;";
		   if(isset($groupRanks[$usertype])) $rankNames.=$groupRanks[$usertype]."  ";
		   //dump($usertype); 
		}
	
		//dump( $rankNames);
		if($rankNames!="")return $rankNames;
		else return "无任何权限";
    }
}





//获取所有权限组数据
function GetGroupRanks()
{
    global $groupRanks,$dsql;
	$dsql->SetQuery("SELECT rank,typename FROM `#@__sys_admintype` ");
	$dsql->Execute();
	while($row = $dsql->GetObject())
	{
		$groupRanks[$row->rank] = $row->typename;
	}
	    //$row->typename = base64_encode($row->typename);
}




