<?php  if(!defined('DWTINC')) exit('dwtx');
/**
 * 文件处理小助手
 *
 * @version        $Id: file.helper.php 1 2010-07-05 11:43:09
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */

$g_ftpLink = false;

/**
 *  使用FTP方法创建文件夹目录
 *
 * @param     string  $truepath  真实目标地址
 * @param     string  $mmode  创建模式
 * @param     string  $isMkdir  是否创建目录
 * @return    bool
 */
if ( ! function_exists('FtpMkdir'))
{
    function FtpMkdir($truepath,$mmode,$isMkdir=true)
    {
        global $cfg_basedir,$cfg_ftp_root,$g_ftpLink;
        OpenFtp();
        $ftproot = preg_replace('/'.$cfg_ftp_root.'$/', '', $cfg_basedir);
        $mdir = preg_replace('/^'.$ftproot.'/', '', $truepath);
        if($isMkdir)
        {
            ftp_mkdir($g_ftpLink, $mdir);
        }
        return ftp_site($g_ftpLink, "chmod $mmode $mdir");
    }
}

/**
 *  改变目录模式
 *
 * @param     string  $truepath  真实地址
 * @param     string  $mmode   模式
 * @return    bool
 */
if ( ! function_exists('FtpChmod'))
{
    function FtpChmod($truepath, $mmode)
    {
        return FtpMkdir($truepath, $mmode, false);
    }
}


/**
 *  打开FTP链接,打开之前确保已经设置好了FTP相关的配置信息
 *
 * @return    void
 */
if ( ! function_exists('OpenFtp'))
{
    function OpenFtp()
    {
        global $cfg_basedir,$cfg_ftp_host,$cfg_ftp_port, $cfg_ftp_user,$cfg_ftp_pwd,$cfg_ftp_root,$g_ftpLink;
        if(!$g_ftpLink)
        {
            if($cfg_ftp_host=='')
            {
                echo "由于你的站点的PHP配置存在限制，程序尝试用FTP进行目录操作，你必须在后台指定FTP相关的变量！";
                exit();
            }
            $g_ftpLink = ftp_connect($cfg_ftp_host,$cfg_ftp_port);
            if(!$g_ftpLink)
            {
                echo "连接FTP失败！";
                exit();
            }
            if(!ftp_login($g_ftpLink,$cfg_ftp_user,$cfg_ftp_pwd))
            {
                echo "登陆FTP失败！";
                exit();
            }
        }
    }
}


/**
 *  关闭FTP链接
 *
 * @return    void
 */
if ( ! function_exists('CloseFtp'))
{
    function CloseFtp()
    {
        global $g_ftpLink;
        if($g_ftpLink)
        {
            @ftp_quit($g_ftpLink);
        }
    }
}


/**
 *  创建所有目录
 *
 * @param     string  $truepath  真实地址
 * @param     string  $mmode   模式
 * @return    bool
 */
if ( ! function_exists('MkdirAll'))
{
    function MkdirAll($truepath,$mmode)
    {
        global $cfg_ftp_mkdir,$isSafeMode,$cfg_dir_purview;
        if( $isSafeMode || $cfg_ftp_mkdir=='Y' )
        {
            return FtpMkdir($truepath, $mmode);
        }
        else
        {
            if(!file_exists($truepath))
            {
                mkdir($truepath, $cfg_dir_purview);
                chmod($truepath, $cfg_dir_purview);
                return true;
            }
            else
            {
                return true;
            }
        }
    }
}
			 
	 


/**
 *  更改所有模式
 *
 * @access    public
 * @param     string  $truepath  文件路径
 * @param     string  $mmode   模式
 * @return    string
 */
if ( ! function_exists('ChmodAll'))
{
    function ChmodAll($truepath,$mmode)
    {
        global $cfg_ftp_mkdir,$isSafeMode;
        if( $isSafeMode || $cfg_ftp_mkdir=='Y' )
        {
            return FtpChmod($truepath, $mmode);
        }
        else
        {
            return chmod($truepath, '0'.$mmode);
        }
    }
}


/**
 *  创建目录
 *
 * @param     string  $spath  创建的文件夹
 * @return    bool
 */
if ( ! function_exists('CreateDir'))
{
    function CreateDir($spath)
    {
        if(!function_exists('SpCreateDir'))
        {
            require_once(DWTINC.'/inc_fun.php');
        }
        return SpCreateDir($spath);
    }
}
/**
 *  删除文件
 *
 * @param     string  $spath  创建的文件夹
 * @return    bool
 */
if ( ! function_exists('DeleteFile'))
{
    function DeleteFile($filename)
    {
        if(!function_exists('SpDeleteFile'))
        {
            require_once(DWTINC.'/inc_fun.php');
        }
        return SpDeleteFile($filename);
    }
}

/**
 *  写文件
 *
 * @access    public
 * @param     string  $file  文件名
 * @param     string  $content  内容
 * @param     int  $flag   标识
 * @return    string
 */
if ( ! function_exists('PutFile'))
{
    function PutFile($file, $content, $flag = 0)
    {
        $pathinfo = pathinfo ( $file );
        if (! empty ( $pathinfo ['dirname'] ))
        {
            if (file_exists ( $pathinfo ['dirname'] ) === FALSE)
            {
                if (@mkdir ( $pathinfo ['dirname'], 0777, TRUE ) === FALSE)
                {
                    return FALSE;
                }
            }
        }
        if ($flag === FILE_APPEND)
        {
            return @file_put_contents ( $file, $content, FILE_APPEND );
        }
        else
        {
            return @file_put_contents ( $file, $content, LOCK_EX );
        }
    }
}

/**
 *  用递归方式删除目录
 *
 * @access    public
 * @param     string    $file   目录文件
 * @return    string
 */
if ( ! function_exists('RmRecurse'))
{
    function RmRecurse($file)
    {
        if (is_dir($file) && !is_link($file))
        {
            foreach(glob($file . '/*') as $sf)
            {
                if (!RmRecurse($sf))
                {
                    return false;
                }
            }
           return @rmdir($file);
        } else {
            return @unlink($file);
        }
    }
}




if ( ! function_exists('UrlAddFileExists'))
{
/**
 * file.helper 判断系统中的连接地址是否存在（只留下实际连接地址，将？后的参数清除后判断）
 *
 * @access    public
 * @param     string    $urladd   文件名称(相对地址) 或外部连接(绝对)
 * @return    bool 地址可以访问返回真
 */    
     function UrlAddFileExists($urladd)
    {
		if($urladd=="")return false;//160421修复
        $filenameArray=explode("?",$urladd);  //将地址按？号分隔，只取问号前的，用于判断文件是否存在
			$filename=$filenameArray[0];
 			
		//	dump($filename);
		//	dump(strpos($filename,"http"));
			if(strpos($filename,"http") !== false)
			{
			//含有外部地址 则判断 是否访问
			//注释掉判断 太慢150813
				  //if(@fopen($filename,"r"))   //150812 引入外部网址 判断 网址访问
				 // {
					  
					  return true;
				 // }else
				 // {
				//	  return false;
				 // }

			}else{
				 $filename=DWTPATH."/".$filename;   //加实际文件地址
				  if(file_exists($filename))   //
				  //本地地址 的话 判断文件是否存在,不能都用网址访问 太慢了
				  {
					  return true;
				  }else
				  {
					  return false;
				  }

			}
    }
}

/**
 *  清除连接地址中？号后的参数 （只留下实际连接地址，将？后的参数清除后判断）
 *
 * @access    public
 * @param     string    $file   目录文件
 * @return    string
 */
if ( ! function_exists('ClearUrlAddParameter'))
{
    function ClearUrlAddParameter($urladd)
    {
			$filenameArray=explode("?",$urladd);  //将地址按？号分隔，只取问号前的
			return $filenameArray[0];
    }
}

/**
 *  返回连接地址中？号后的参数 
 *
 * @access    public
 * @param     string    $file   目录文件
 * @return    string
 */
if ( ! function_exists('ReturnUrlAddParameter'))
{
    function ReturnUrlAddParameter($urladd)
    {
			$filenameArray=explode("?",$urladd);  //将地址按？号分隔，只取问号后的
			if(count($filenameArray)>1)
			{
				return "?".$filenameArray[1];
			}else
			{
				return "";
				}
    }
}

