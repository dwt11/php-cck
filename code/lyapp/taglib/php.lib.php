<?php
if (!defined('DWTINC'))
    exit('Request Error!');
/**
 * 
 *
 * @version        $Id: php.lib.php1 9:29 6日
 * @package        DwtCMS.Taglib
 * @copyright
 * @license
 * @link
 */
 
 /*>>dede>>
<name>PHP代码标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>调用PHP代码</description>
<demo>
{dwt:php}
$a = "dwt";
echo $a;
{/dwt:php}
</demo>
<attributes>
</attributes> 
>>dede>>*/
 
function lib_php(&$ctag, &$refObj)
{
    global $dsql;
    global $db;
    $phpcode = trim($ctag->GetInnerText());
	//dump($phpcode);
    if ($phpcode == '')
        return '';
    ob_start();
    extract($GLOBALS, EXTR_SKIP);
    @eval($phpcode);
    $revalue = ob_get_contents();
    ob_clean();
    return $revalue;
}