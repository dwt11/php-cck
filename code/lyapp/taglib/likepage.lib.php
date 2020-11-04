<?php
/**
 * 单页文档相同标识调用标签
 *
 * @version        $Id: likepage.lib.php 1 9:29 6日
 * @package        DwtCMS.Taglib
 * @copyright
 * @license
 * @link
 */
 
/*>>dede>>
<name>单页文档相同标识调用标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>调用相同标识单页文档</description>
<demo>
{dwt:likepage likeid='' row=''/}
</demo>
<attributes>
    <iterm>row:调用条数</iterm> 
    <iterm>likeid:标识名</iterm>
</attributes> 
>>dede>>*/
 
if(!defined('DWTINC')) exit('Request Error!');
require_once(dirname(__FILE__).'/likesgpage.lib.php');

function lib_likepage(&$ctag,&$refObj)
{
    return lib_likesgpage($ctag, $refObj);
}
