<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 验证小助手
 *
 * @version        $Id: validate.helper.php 1 2010-07-05 11:43:09
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */

//邮箱格式检查
if (!function_exists('CheckEmail')) {
    function CheckEmail($email)
    {
        if (!empty($email)) {
            return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*@([a-z0-9]+[\-]?[a-z0-9]+\.)+[a-z]{2,6}$/i', $email);
        }
        return FALSE;
    }
}

//手机格式检查
if (!function_exists('CheckMobilePhone')) {
    function CheckMobilePhone($str)
    {
        return preg_match("/^1[34578]{1}\d{9}$/", $str);
    }
}
