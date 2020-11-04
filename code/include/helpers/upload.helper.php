<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 上传处理小助手
 *
 * @version        $Id: upload.helper.php 1 2010-07-05 11:43:09
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


/**
 *  上传文件的通用函数
 *   upload.helper 150901更新
 *
 * @access    public
 *
 * @param     string $uploadname   上传名称
 * @param     string $ftype        文件类型
 * @param     string $rnddd        后缀数字
 * @param     bool   $watermark    是否水印
 * @param     string $filetype     image、media、addon
 *                                 $file_type='' 对于swfupload上传的文件， 因为没有filetype，所以需指定，并且有些特殊之处不同
 *
 *
 * @param     string $dirname_plus 扩展目录名称 如 员工照片则是 "empphoto"  设备device  新闻 archives
 *
 * @return    int   -1 没选定上传文件，0 文件类型不允许, -2 保存失败，其它：返回上传后的文件名
 */
if (!function_exists('AdminUpload')) {
    function AdminUpload($uploadname, $ftype = 'image', $rnddd = 0, $watermark = false, $filetype = '', $dirname_plus = '')
    {
        //dump($uploadname);
        global $dsql, $cfg_addon_savetype, $cfg_dir_purview;
        global $cfg_basedir, $cfg_image_dir, $cfg_soft_dir, $cfg_other_medias;
        global $cfg_imgtype, $cfg_softtype, $cfg_mediatype;
        //if ($watermark) include_once(DWTINC . '/image.func.php');
        include_once(DWTINC . '/image.func.php');


        $file_tmp = isset($GLOBALS[$uploadname]) ? $GLOBALS[$uploadname] : '';
        //dump( $GLOBALS[$uploadname]);  //此处$file_tmp为C:\Windows\Temp\php41D1.tmp,在PHP5.2下可以获取  在5.3下获取不到值为空 原因待查150902
        if ($file_tmp == '' || !is_uploaded_file($file_tmp)) {
            return -1;
        }

        $file_tmp = $GLOBALS[$uploadname];
        $file_size = filesize($file_tmp);
        $file_type = $filetype == '' ? strtolower(trim($GLOBALS[$uploadname . '_type'])) : $filetype;

        $file_name = isset($GLOBALS[$uploadname . '_name']) ? $GLOBALS[$uploadname . '_name'] : '';
        $file_snames = explode('.', $file_name);
        $file_sname = strtolower(trim($file_snames[count($file_snames) - 1]));

        //dump($file_sname);
        //dump($file_type);

        //$ftype这里这个有变量有问题 当前只用了img的,员工照片是这个,但设备知识库有图片或文件没有判断 151111?????????????
        //当前只用了imagelit
        if (!preg_match('/' . $cfg_softtype . '/', $file_sname) && !preg_match('/' . $cfg_imgtype . '/', $file_sname)) return 0;//如果不是系统配置中的文件类型 则不让上传

        if ($ftype == 'image' || $ftype == 'imagelit' || $ftype == '.png' || $ftype == '.jpg' || $ftype == '.gif' || $ftype == '.jpeg' || $ftype == '.bmp') {
            $filetype = '1';
            $sparr = Array('image/pjpeg', 'image/jpeg', 'image/gif', 'image/png', 'image/xpng', 'image/wbmp', '.jpg', '.jpeg', '.png', '.gif', '.bmp');
            //if(!in_array($file_type, $sparr)) return 0;//150902屏蔽掉此句 否则 不能上传其他 的东西
            if ($file_sname == '') {
                if ($file_type == 'image/gif') $file_sname = 'jpg';
                else if ($file_type == 'image/png' || $file_type == 'image/xpng') $file_sname = 'png';
                else if ($file_type == 'image/wbmp') $file_sname = 'bmp';
                else if ($file_type == '.gif') $file_sname = 'gif';
                else if ($file_type == '.png') $file_sname = 'png';
                else if ($file_type == '.jpeg') $file_sname = 'jpeg';
                else $file_sname = 'jpg';
            }
        } else if ($ftype == 'media') {
            $filetype = '3';
            if (!preg_match('/' . $cfg_mediatype . '/', $file_sname)) return 0;
        } else {
            $filetype = '4';
            $cfg_softtype .= '|' . $cfg_mediatype . '|' . $cfg_imgtype;
            $cfg_softtype = str_replace('||', '|', $cfg_softtype);
        }

        //echo $filetype."--".$ftype;

        //dump($dirname_plus);
        if($dirname_plus!="")$dirname_plus.="/";
        $dirname_date = "";//日期文件夹名称
        //if ($dirname_plus == "archives") $dirname_date = dd2char(MyDate('Ym', time())) . "/";//如果是新闻的上传图片 则文件夹下要按年月文件夹分类
        $dirname_date = MyDate('Ym', time()) . "/";//如果是新闻的上传图片 则文件夹下要按年月文件夹分类
        global $DEP_TOP_ID;
        $filedirall = $cfg_basedir . getUploadFileAdd($DEP_TOP_ID) . $dirname_plus . $dirname_date;//目录名称
        $filedir = getUploadFileAdd($DEP_TOP_ID) . $dirname_plus  . $dirname_date;//相对目录名称

        //dump($filedir);
        if (!is_dir($filedirall)) {
            CreateDir($filedir);//创建目录
        }
        $filename = $GLOBALS['CUSERLOGIN']->getUserID() . '-' . dd2char(MyDate('ymdHis', time())) . $rnddd;
        $filenameAll = $filename . '.' . $file_sname;
        $fileadd = $filedirall . $filenameAll;//实际文件地址   例:f:/cc/code/uploads

        //检修是否有同名文件存在
        if (file_exists($fileadd)) {
            $filename = $filename . '-' . rand(10, dd2char(MyDate('s', time())));//加后辍 10到当前秒的随机数  修复BUG151113
        }

        $fileurl = getUploadFileAdd($DEP_TOP_ID) . $dirname_plus  . $dirname_date . $filename . '.' . $file_sname;;//相对地址

        $rs = move_uploaded_file($file_tmp, $cfg_basedir . $fileurl);
        if (!$rs) return -2;

        //如果是图片要水印 则执行以下的
        if ($ftype == 'image' && $watermark) {
            WaterImg($cfg_basedir . $fileurl, 'up');
        }

        //dump($cfg_basedir . $fileurl);
        $ddd=ImageResize($cfg_basedir . $fileurl,800,800);//对图片进行压缩

        //dump($ddd);
        //保存信息到数据库
        $title = $filename . '.' . $file_sname;
        $inquery = "INSERT INTO `#@__uploads`(title,url,mediatype,width,height,playtime,filesize,uptime,mid)
            VALUES ('$title','$fileurl','$filetype','0','0','0','" . filesize($cfg_basedir . $fileurl) . "','" . time() . "','" . $GLOBALS['CUSERLOGIN']->getUserID() . "'); ";
        $dsql->ExecuteNoneQuery($inquery);
        return $fileurl;
    }
}


/**
 *  调用 AdminUpload  根据它返回的数值将信息返回给调用的程序  (实现上传图片 异步显示)151109
 *
 * 使用 emp上传照片  设备知识库上传图片或文件
 */
if (!function_exists('AdminUpload_plus')) {
    function AdminUpload_plus($uploadname, $ftype = 'image', $rnddd = 0, $watermark = false, $filetype = '', $dirname_plus = '')
    {
        $upfile = AdminUpload($uploadname, $ftype, $rnddd, $watermark, $filetype, $dirname_plus);
        if ($upfile == '-1') {
            $msg = "<script language='javascript'>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('你没指定要上传的文件或文件大小超过限制！');
            </script>";
        } else if ($upfile == '-2') {
            $msg = "<script language='javascript'>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('上传文件失败，请检查原因！');
            </script>";
        } else if ($upfile == '0') {
            $msg = "<script language='javascript'>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('文件类型不正确！');
            </script>";
        } else {
            //如果是图片,则异步显示
            //如果有图片或文件下载地址 的
            $fileImgHtmlCode = "<a href='$upfile' target='_blank'><img  src='/images/down.gif' title='点击下载'></a>";//默认显示图片()
            if (strpos($upfile, "jpg") || strpos($upfile, "gif") || strpos($upfile, "bmp") || strpos($upfile, "jpeg") || strpos($upfile, "png")) {
                $fileImgHtmlCode = "<img  src='$upfile' title='点击查看大图' style='cursor:pointer' width='150'    height='120' onclick='javascript:window.open(this.src);'>";
            }


            $msg = "<script language='javascript'>
								  parent.document.getElementById('uploadwait').style.display = 'none';//隐藏上传中的提示
								  parent.document.getElementById('picname').value = '{$upfile}';      //上传后的文件地址  赋给界面显示
								  if(parent.document.getElementById('divpicview'))//显示预览框
								  {
									  parent.document.getElementById('divpicview').style.width = '150px';
									  parent.document.getElementById('divpicview').innerHTML = \"$fileImgHtmlCode\";
								  }
			</script>";
        }
        return $msg;

    }

}


if (!function_exists('getUploadFileAdd')) {   // $upfile = AdminUpload($emp_code,'litpic', 'imagelit', 0, false );

    /**
     *  得到文件保存目录 相对地址
     *  默认地址:/uploads/
     *  如果有顶级单位则地址加单位ID+全拼如果超过8位则只取前六位/UPLOADS/28yibiao/
     *
     * @param $topDepId
     *
     * @return string  实际文件地址
     */
    function getUploadFileAdd($topDepId = 0)
    {
        //得到文件保存目录 实际地址

        //默认例子:/uploads/
        global $dsql;

        $fileadd = "/uploads/";

        if ($topDepId == 0) $topDepId = $GLOBALS['NOWLOGINUSERTOPDEPID'];

        if ($topDepId != "0")//151223修改BUG,原为判断空 改为判断0
        {
            $dirname = $topDepId . GetPinyin(GetDepsNameByDepId($topDepId));    //单位ID+全拼",如果超过8位则只取前8位
            if (strlen($dirname) > 8) $dirname = substr($dirname, 0, 8);
            $fileadd = "/uploads/" . $dirname . "/";
        }
        // dump($fileadd);
        return $fileadd;
    }
}
