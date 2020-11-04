<?php
//数据库连接信息
$cfg_dbhost = '127.0.0.1';//IP地址要比LOCALhost快很多
$cfg_dbname = 'cck3.0xs';
$cfg_dbuser = 'root';
 $cfg_dbpwd = '';
$cfg_dbprefix = 'x_';
$cfg_db_language = 'utf8';
define('DEBUG_LEVEL', true);//是否启用调试模式
//define('DEBUG_LEVEL', false);//是否启用调试模式
define('DEBUG_LEVEL_ISSENDMSG', false);//是否发布微信 和短信消息  调试模式不发送
