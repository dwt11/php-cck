<?php  if(!defined('DWTINC')) exit('dwtx');
/**
 * 字符串小助手
 *
 * @version        $Id: string.helper.php 5 14:24 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */
//拼音的缓冲数组
$pinyins = Array();




/**

 * @author ja颂
 * 把数字1-1亿换成汉字表述，如：123->一百二十三
 * @param [num] $num [数字]
 * @return [string] [string]
 */
if ( !function_exists('NumToWord')) {

    function NumToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('', '十', '百', '千', '万', '亿', '十', '百', '千');

        $chiStr = '';

        $num_int = (int)$num;
        $num_str = (string)$num_int;

        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字

        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else if ($count > 2) {
            $index = 0;
            for ($i = $count - 1; $i >= 0; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag) {
                        $chiStr = $chiNum[$temp_num] . $chiStr;
                        $last_flag = true;
                    }
                } else {
                    $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;

                    $zero_flag = false;
                    $last_flag = false;
                }
                $index++;
            }
        } else {
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }
}


//获取SQL语句中的查询字段名称

if ( !function_exists('Get_parse_sql_field')) {

//170526未使用这个 获取 出后 得到了带表名的(AA.ID)和(sum(id))这样的脏数据
    //在dwtsql中使用未果
    function Get_parse_sql_field($sql)
    {

        $result = array();
        //preg_match("/^select\s+(.*)\s+from/i", trim($sql), $arr);
        preg_match("/^select\s+(.*?)\s+from/i",trim($sql),$arr);
        if (!isset($arr[1])) return $result;
        $str = $arr[1];
        unset($arr);
        $tchar = array('"', "'", "(", ")", "\\", ",");
        $tchar_count = array_pad(array(), count($tchar), 0);
        $index = $prechar = 0;
        for ($i = 0, $len = strlen($str); $i < $len;) {

            if (!isset($result[$index])) $result[$index] = '';
            if (ord($str{$i}) >= 0x81) {
                $result[$index] .= substr($str, $i, 3);
                $i += 3;
            } else {
                $tk = array_search($str{$i}, $tchar);
                if (false !== $tk) {
                    switch ($tk) {
                        case 0 :
                        case 1 :
                            $t = $tk != 0 ? 0 : 1;
                            if ($tchar_count[$t] == 0 && $prechar != 4)
                                if (++$tchar_count[$tk] == 2) $tchar_count[$tk] = 0;
                            break;
                        case 2 :
                            if ($tchar_count[0] + $tchar_count[1] == 0) $tchar_count[$tk]++;
                            break;
                        case 3 :
                            if ($tchar_count[2] > 0 && $tchar_count[0] + $tchar_count[1] == 0) $tchar_count[2]--;
                            break;
                        case 4 :
                            break;
                        case 5 :
                            if (array_sum($tchar_count) == 0) $index++;
                            else $result[$index] .= $str{$i};
                            break;
                    }
                    $prechar = $tk;
                }
                if ($tk != 5) $result[$index] .= $str{$i};
                $i++;
            }
        }
        return $result;
    }
}




//获取身份证年龄

if ( !function_exists('GetIDcardAge'))
{
    /**
     * @param $idcard
     *
     * @return mixed
     *
     *
     */
    function GetIDcardAge($idcard)
    {

        //过了这年的生日才算多了1周岁
        if(empty($idcard)) return '';
        //$date=strtotime(substr($idcard,6,4));


        $year=substr($idcard,6,4);
        $nowyear = date("Y");

        //dump($nowyear);

        $age=$nowyear-$year;

        /*
        //获得出生年月日的时间戳
        $today=strtotime('today');
        //获得今日的时间戳
        $diff=floor(($today-$date)/86400/365);
        //得到两个日期相差的大体年数
        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age=strtotime(substr($idcard,6,8).' +'.$diff.'years')>$today?($diff+1):$diff;
*/

        return $age;
    }
}


//高亮专用

if ( !function_exists('GetRedKeyWord'))
{
    /**
     * @param $fstr 字符串
     * @param $k 关键词
     *
     * @return mixed
     */
    function GetRedKeyWord($fstr,$k)
    {
        $fstr = str_replace($k, "<b><span style='color:red'>$k</span></b>", $fstr);
        return $fstr;
    }
}


























/**
 *  中文截取2，单字节截取模式
 *  如果是request的内容，必须使用这个函数
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if ( ! function_exists('cn_substrR'))
{
    function cn_substrR($str, $slen, $startdd=0)
    {
        $str = cn_substr(stripslashes($str), $slen, $startdd);
        return addslashes($str);
    }
}



/**
 *  中文截取2，单字节截取模式
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if ( ! function_exists('cn_substr'))
{
    function cn_substr($str, $slen, $startdd=0)
    {
        global $cfg_soft_lang;
        if($cfg_soft_lang=='utf-8')
        {
            return cn_substr_utf8($str, $slen, $startdd);
        }
        $restr = '';
        $c = '';
        $str_len = strlen($str);
        if($str_len < $startdd+1)
        {
            return '';
        }
        if($str_len < $startdd + $slen || $slen==0)
        {
            $slen = $str_len - $startdd;
        }
        $enddd = $startdd + $slen - 1;
        for($i=0;$i<$str_len;$i++)
        {
            if($startdd==0)
            {
                $restr .= $c;
            }
            else if($i > $startdd)
            {
                $restr .= $c;
            }

            if(ord($str[$i])>0x80)
            {
                if($str_len>$i+1)
                {
                    $c = $str[$i].$str[$i+1];
                }
                $i++;
            }
            else
            {
                $c = $str[$i];
            }

            if($i >= $enddd)
            {
                if(strlen($restr)+strlen($c)>$slen)
                {
                    break;
                }
                else
                {
                    $restr .= $c;
                    break;
                }
            }
        }
        return $restr;
    }
}

/**
 *  utf-8中文截取，单字节截取模式
 *
 * @access    public
 * @param     string  $str  需要截取的字符串
 * @param     int  $slen  截取的长度
 * @param     int  $startdd  开始标记处
 * @return    string
 */
if ( ! function_exists('cn_substr_utf8'))
{
    function cn_substr_utf8($str, $length, $start=0)
    {
        if(strlen($str) < $start+1)
        {
            return '';
        }
        preg_match_all("/./su", $str, $ar);
        $str = '';
        $tstr = '';
        //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
        for($i=0; isset($ar[0][$i]); $i++)
        {
            if(strlen($tstr) < $start)
            {
                $tstr .= $ar[0][$i];
            }
            else
            {
                if(strlen($str) < $length + strlen($ar[0][$i]) )
                {
                    $str .= $ar[0][$i];
                }
                else
                {
                    break;
                }
            }
        }
        return $str;
    }
}

/**
 *  HTML转换为文本
 *
 * @param    string  $str 需要转换的字符串
 * @param    string  $r   如果$r=0直接返回内容,否则需要使用反斜线引用字符串
 * @return   string
 */
if ( ! function_exists('Html2Text'))
{
    function Html2Text($str,$r=0)
    {
        if(!function_exists('SpHtml2Text'))
        {
            require_once(DWTINC."/inc_fun.php");
        }
        if($r==0)
        {
            return SpHtml2Text($str);
        }
        else
        {
            $str = SpHtml2Text(stripslashes($str));
            return addslashes($str);
        }
    }
}


/**
 *  文本转HTML
 *
 * @param    string  $txt 需要转换的文本内容
 * @return   string
 */
if ( ! function_exists('Text2Html'))
{
    function Text2Html($txt)
    {
        $txt = str_replace("  ", "　", $txt);
        $txt = str_replace("<", "&lt;", $txt);
        $txt = str_replace(">", "&gt;", $txt);
        $txt = preg_replace("/[\r\n]{1,}/isU", "<br/>\r\n", $txt);
        return $txt;
    }
}

/**
 *  获取半角字符
 *
 * @param     string  $fnum  数字字符串
 * @return    string
 */
if ( ! function_exists('GetAlabNum'))
{
    function GetAlabNum($fnum)
    {
        $nums = array("０","１","２","３","４","５","６","７","８","９");
        //$fnums = "0123456789";
        $fnums = array("0","1","2","3","4","5","6","7","8","9");
        $fnum = str_replace($nums, $fnums, $fnum);
        $fnum = preg_replace("/[^0-9\.-]/", '', $fnum);
        if($fnum=='')
        {
            $fnum=0;
        }
        return $fnum;
    }
}


/**
 *  获取拼音以gbk编码为准
 *
 * @access    public
 * @param     string  $str     字符串信息
 * @param     int     $ishead  是否取头字母
 * @param     int     $isclose 是否关闭字符串资源
 * @param     int     $isonehead  是否第一个字全拼 ,其他的字取头字母  此值为0时  ishead才起作用
 * @return    string
 */
if ( ! function_exists('GetPinyin'))
{
    function GetPinyin($str, $ishead=0, $isclose=1,$isonehead=0 )
    {

        $str=utf82gb($str);
        global $pinyins;
        $restr = '';
        $str = trim($str);
        $slen = strlen($str);
        if($slen < 2)
        {
            return $str;
        }
        if(count($pinyins) == 0)
        {
            $fp = fopen(DWTINC.'/data/pinyin.dat', 'r');
            while(!feof($fp))
            {
                $line = trim(fgets($fp));
                $pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
            }
            fclose($fp);
        }
        for($i=0; $i<$slen; $i++)
        {//dump($slen);
            if(ord($str[$i])>0x80)
            {
                $c = $str[$i].$str[$i+1];
                $i++;
                if(isset($pinyins[$c]))
                {
                    // dump($i."---".$pinyins[$c]);
                    if($isonehead==0)
                    {
                        if($ishead==0)
                        {
                            $restr .= $pinyins[$c];
                        }
                        else
                        {
                            $restr .= $pinyins[$c][0];
                        }
                    }else
                    {
                        if($i==1)
                        {
                            $restr .= $pinyins[$c];
                        }else
                        {
                            $restr .= $pinyins[$c][0];
                        }

                    }


                }else
                {
                    $restr .= "_";
                }
            }else if( preg_match("/[a-z0-9]/i", $str[$i]) )
            {
                $restr .= $str[$i];
            }
            else
            {
                $restr .= "_";
            }
        }
        if($isclose==0)
        {
            unset($pinyins);
        }
        return $restr;




    }
}



/**
 *  将实体html代码转换成标准html代码（兼容php4）
 *
 * @access    public
 * @param     string  $str     字符串信息
 * @param     long    $options  替换的字符集
 * @return    string
 */

if ( ! function_exists('htmlspecialchars_decode'))
{
    function htmlspecialchars_decode($str, $options=ENT_COMPAT) {
        $trans = get_html_translation_table(HTML_SPECIALCHARS, $options);

        $decode = ARRAY();
        foreach ($trans AS $char=>$entity) {
            $decode[$entity] = $char;
        }

        $str = strtr($str, $decode);

        return $str;
    }
}


//随机数字小助手，生成随机数 
if ( ! function_exists('dotrand'))
{
    function dotrand($startnum,$endnum)
    {
        $newnum = rand($startnum,$endnum);
//			$newdot = rand($sdot,$edot);
//			if (strlen($newdot) == "1"){
//					$newdot = "0".$newdot;
//			}
        return $newnum;
    }

}


//150203将数字编号按要求的位数补充全(前面加000)
//用在员工编号\订做的商品的编号 补充

//$numbInt  数字,
//$digit  要求的位数

//return string
if ( ! function_exists('GetIntAddZero'))
{
    function GetIntAddZero($numbInt,$digit=4)
    {		//$numbInt="1";
        if(strlen($numbInt)<$digit){
            $addnumb=$digit-strlen($numbInt);
            //dump($addnumb);
            //150305优化算法
            for ($i = 0; $i <$addnumb; $i++)
            {
                $numbInt="0".$numbInt;
                //dump($i);
            }
        }
        return $numbInt;
    }
}



if ( ! function_exists('GetPhoneCode'))
{

    /**手机号中间四位隐藏
     * @param $phone
     *
     * @return mixed
     */
    function GetPhoneCode($phone)
    {
        //170123优化,手机隐藏中间四位
        //如果大于四位,如身份证,则从第五个开始
        $str=$phone;
        if(strlen($phone)==11) {
            //手机
            $str = substr_replace($phone, '****', 3, 4);
        }elseif(strlen($phone)>11){
            //身份证
            $str = substr_replace($phone, '*******', 5, 7);
        }
        //dump($phone);
        //dump(strlen($phone));
        return $str;
    }
}





if ( ! function_exists('unique_arr')) {
    /**
     * 去除二维数组中的重复项
     * PHP数组去除重复项 有个内置函数array_unique()，但是php的 array_unique函数只适用于一维数组，对多维数组并不适用，以下提供一个二维数组 的 array_unique函数
     *
     * @param            $array2D
     * @param bool|false $stkeep
     * @param bool|true  $ndformat
     *
     * @return mixed
     */
    function unique_arr($array2D, $stkeep = false, $ndformat = true)
    {
        // 判断是否保留一级数组键 (一级数组键可以为非数字)
        if ($stkeep) $stArr = array_keys($array2D);
        // 判断是否保留二级数组键 (所有二级数组键必须相同)
        if ($ndformat) $ndArr = array_keys(end($array2D));
        //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        foreach ($array2D as $v) {
            $v = join(",", $v);
            $temp[] = $v;
        }
        //去掉重复的字符串,也就是重复的一维数组
        $temp = array_unique($temp);
        //再将拆开的数组重新组装
        foreach ($temp as $k => $v) {
            if ($stkeep) $k = $stArr[$k];
            if ($ndformat) {
                $tempArr = explode(",", $v);
                foreach ($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
            } else $output[$k] = explode(",", $v);
        }
        return $output;
    }
}













////////////////////////////----------------------151012加的                               这个功能试好后要移入!!!!! string.helper.php供其他功能中的分类数组使用151012
/**
 * 在多维数组中搜索指定的键和值   对应的单条设备信息(数组)
 *
 * 多维数组格式 最少要包含id和reid
 * array(46) {
 * [0] => array(6) {
 * ["id"] => string(2) "34"
 * ["reid"] => string(2) "33"
 * ["flag"] => string(1) "S"
 * ["typename"] => string(16) "tee907Xj0bnBprHt"
 * ["ispart"] => string(1) "0"
 * ["sortrank"] => string(1) "1"
 * }
 * [1] => array(6) {
 * ["id"] => string(2) "35"
 * ["reid"] => string(2) "33"
 * ["flag"] => string(1) "S"
 * ["typename"] => string(8) "seTLzcb3"
 * ["ispart"] => string(1) "0"
 * ["sortrank"] => string(1) "2"
 * }
 * }
 *
 * @param $arrayData  array   多维数组
 * @param $key        string  指定的键
 * @param $value      string  指定的值
 *
 * @return 返回子数组信息
 */
function SearchOneArray($arrayData,$key,$value){
    //dump($arrayData);
    //dump($key);
    //dump($value);
    $returnArray="";
    if(is_array($arrayData)) {
        foreach ($arrayData as $keyp => $valuep) {
            if ($valuep[$key] == $value) $returnArray = $arrayData[$keyp];
        }
    }
    //dump($returnArray);
    return $returnArray;
}


/**
 *
 * 获取多维数组中最顶级的ID,   这个功能试好后要移入!!!!! string.helper.php供其他功能中的分类数组使用151012
 *
 * @param $arrayData  array    多维数组
 *
 * @return string  格式  1,2
 */
function GetTopIdInArray($arrayData)
{
    //$returnBool=false;
    $nowTopId="";//当前多维数组中   最顶部的部门ID
    //dump($arrayData);
    if(is_array($arrayData)) {
        foreach ($arrayData as $keyp => $valuep) {
            $id=$valuep["id"];
            $nowTypeArray=SearchOneArray($arrayData,"id",$id);//获取当前ID的分类信息
            $nowReid=$nowTypeArray["reid"];   //当前ID的 reid
            if(!is_array(SearchOneArray($arrayData,"id",$nowReid)))//用此reid判断当前id   在当前的多维数组中是否有上级分类
            {
                //如果没有上级分类,则再循环下一个数组
                $nowTopId.=$valuep["id"].",";   //获取最顶部的
            }
        }
        $nowTopId=trim($nowTopId,",");//清除
    }
    return $nowTopId;
}






/**
 * 将原始数据库中的数据,重新的排序,并分级排序,并将层级数组加入,存入新的数组 供使用
 *(这个处理后将数据全部返回,不筛选)
 * @param $arrayData  array    多维数组
 * @return string  格式  1,2
 */
function SetNewTypeInfoArray($arrayData)
{
    $step=0;//层级初始
    global $newTypeInfoArrayS;
    $newTypeInfoArrayS=array();//151120修复BUG,原来没有初始化,当设备分类和装置在同一个页面引用时,引起重复数据
    if(is_array($arrayData)) {
        $nowTopId=GetTopIdInArray($arrayData);//当前多维数组中   最顶部的部门ID
        $nowTopIdArray=explode(",",$nowTopId);
        foreach ($nowTopIdArray as $key => $value) {
            $typeArray=SearchOneArray($arrayData,"id",$value);
            $typeArray["step"] = $step;
            $newTypeInfoArrayS[] = $typeArray;
            logicSetNewTypeInfoArray($arrayData,$value, $step + 1);
        }
    }
    return $newTypeInfoArrayS;
}

/**
 *
 * 递归逻辑  获取下一级的数据
 *
 * @param     $arrayData
 * @param int $id   上级ID,
 * @param int $step 步进数
 */
function logicSetNewTypeInfoArray($arrayData,$id,$step)
{
    global $newTypeInfoArrayS;
    if(is_array($arrayData)) {
        foreach ($arrayData as $keyp => $valuep) {
            if ($valuep["reid"] == $id)   //获取子分类
            {
                $arrayData[$keyp]["step"]=$step;
                $newTypeInfoArrayS[] = $arrayData[$keyp];
                logicSetNewTypeInfoArray($arrayData,$arrayData[$keyp]["id"],$step+1);
            }
        }
    }
}





/**
 *将指定ID的所有上级的信息返回
 *
 * (上级都是单线的,树型无限向上递归)
 * (筛选数据后返回)(包含当前ID)
 *
 * (这步骤GetTypeInfoBeforeArray是引用一下,初始化$newTypeInfoBeforeArrayS,防止编码名称 混淆
 * ,直接引用logicGetTypeInfoBeforeArray也是可以的)
 *
 *@param $arrayData  array    多维数组(不加查询所有分类)
 * @param $nowId  int    指定ID
 * 将原始数据库中的数据,重新的排序,并分级排序,并将层级数组加入,存入新的数组 供使用
 *
 *@return string  格式  1,2
 */
function GetTypeInfoBeforeArray($arrayData,$nowId)
{
    global $newTypeInfoBeforeArrayS;
    $newTypeInfoBeforeArrayS="";
    if($nowId!=""&&$nowId>0&&is_array($arrayData))
    {
        //$step=0;//层级初始
        $tempArry=SearchOneArray($arrayData,"id",$nowId);
        if(is_array($tempArry)) {
            $newTypeInfoBeforeArrayS[]=$tempArry;
            $reid=$tempArry["reid"];
            if($reid!=0)logicGetTypeInfoBeforeArray($arrayData,$reid);
        }
        sort($newTypeInfoBeforeArrayS);//倒序
    }
    return $newTypeInfoBeforeArrayS;
}


/**
 *
 * 递归逻辑  获取上一级的数据
 *
 * @param     $arrayData  array    多维数组(不加查询所有分类)
 * @param int $reid       上级ID,
 */
function logicGetTypeInfoBeforeArray($arrayData,$reid)
{
    global $newTypeInfoBeforeArrayS;
    if(is_array($arrayData)) {
        $tempArry=SearchOneArray($arrayData,"id",$reid);
        if(is_array($tempArry)) {
            $newTypeInfoBeforeArrayS[]=$tempArry;
            $reid=$tempArry["reid"];
            if($reid!=0)logicGetTypeInfoBeforeArray($arrayData,$reid);
        }
    }
}












/**
 *将指定ID的所有下级的信息返回
 * (下级可能有多分支,树型无限向下递归)
 * (筛选数据后返回)(包含当前ID)
 *
 * (这步骤GetTypeInfoBeforeArray是引用一下,初始化$newTypeInfoBeforeArrayS,防止编码名称 混淆
 * ,直接引用logicGetTypeInfoBeforeArray也是可以的)
 *
 *@param $arrayData  array    多维数组(不加查询所有分类)
 * @param $nowId  int    指定ID
 * 将原始数据库中的数据,重新的排序,并分级排序,并将层级数组加入,存入新的数组 供使用
 *
 *@return string  格式  1,2
 */
function GetTypeInfoAfterArray($arrayData,$nowId)
{
    global $newTypeInfoAfterArrayS;
    $newTypeInfoAfterArrayS="";
    if($nowId!=""&&$nowId>0&&is_array($arrayData))
    {
        //$step=0;//层级初始
        $tempArry=SearchOneArray($arrayData,"id",$nowId);
        if(is_array($tempArry)) {
            $newTypeInfoAfterArrayS[]=$tempArry;
            logicGetTypeInfoAfterArray($arrayData,$nowId);
        }
        //   sort($newTypeInfoBeforeArrayS);//倒序
    }
    return $newTypeInfoAfterArrayS;
}


/**
 *
 * 递归逻辑  获取下一级的数据
 *
 * @param $arrayData  array    多维数组(不加查询所有分类)
 * @param $nowId int $id 上级ID,
 *
 */
function logicGetTypeInfoAfterArray($arrayData,$nowId)
{
    global $newTypeInfoAfterArrayS;
    if(is_array($arrayData)) {

        foreach ($arrayData as $keyp => $valuep) {
            if ($valuep["reid"] == $nowId)   //获取子分类
            {
                $newTypeInfoAfterArrayS[] = $arrayData[$keyp];
                logicGetTypeInfoAfterArray($arrayData,$arrayData[$keyp]["id"]);
            }
        }
    }
}







////////////////////////////----------------------151012加的                               这个功能试好后要移入!!!!! string.helper.php供其他功能中的分类数组使用151012

