<?php
require_once(DEDEDATA . "/sys_function_noListDir.php");

if (!defined('DWTINC')) exit('Request Error!');
/**
 * 功能管理
 *
 * @package
 * @copyright
 * @license
 * @link
 */


/**
 * 系统功能管理
 *
 */
class sys_baseconfg
{


    var $actionFileHtmlHead;   ///功能文件列表的头
    var $actionFileHtmlFoot;   ///功能文件列表的尾
    var $dsql;
    var $panelJScode;  //控制面板COOK展开的JS代码

    //php5构造函数
    function __construct()
    {
        require_once(DEDEDATA . "/sys_function_data.php");
    }

    function sys_baseconfg()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }








//----------------------------------------sys_function_set2file.php中使用-----------begin--------------------------------
    /**
     *  列出所有父功能(文件夹)
     *sys/sys_function中使用
     *
     * @access    public
     * @return    string
     */
    function listDir()
    {
        $diri = 0;
        $inDateDirNameArray = array(); //已经保存到配置文件中的文件夹名称
        global $baseConfigFunArray;
        foreach ($baseConfigFunArray as $key => $row) {
            if (in_array($key, $GLOBALS['$noListDirArray'])) continue;//如果文件夹在禁用列表里 则跳过160603添加
            //dump($row);
            //1、获取已经保存的父文件夹
            $fun_info = explode(',', $row[0]);  //获取父文件夹数组
            $rowtitle = $fun_info[0];
            $rowdir = $key;
            $inDateDirNameArray[] = $rowdir;   //已经保存到配置文件中的文件夹名称
            if (file_exists($GLOBALS["cfg_basedir"] . "/" . $rowdir))   //查看文件夹 是否实际存在
            {
                $diri++;
                echo $this->dirHtmlsHeard($diri, $rowtitle, $rowdir); //输出父文件夹
                echo $this->getFileInfo($diri, $rowdir, $row);
                echo $this->dirHtmlsFoot();
            }
        }


        //2获取未保存到数据库中的目录
        foreach ($this->listNoDate("", $inDateDirNameArray, 1) as $dirName) {
            // dump($dirName);
            $diri++;
            echo $this->dirHtmlsHeard($diri, "", $dirName); //输出父文件夹
            echo $this->getFileInfo($diri, $dirName);
            echo $this->dirHtmlsFoot();
        }

        echo "<input type='hidden' name='dirnumb' value='$diri'>";
    }


    /**
     * 列出不在文件中中保存的文件夹或目录
     *
     * @param $nowDir             当前目录
     * @param $inDateDirNameArray 已经保存到数据库中的名称
     * @param $isDir              列出目录还是文件  1列目录 0列PHP文件
     * @param $fileName           为空的话 返回主功能文件, 不为空则返回主功能文件的动作文件
     *
     * @return array
     *
     * 160307修改,原判断不显示的目录使用字符串,改为直接使用数组. 原手工写的值,保存到data目录下,可以动态配置
     *
     */
    function listNoDate($nowDir, $inDateDirNameArray, $isDir, $fileName = "")
    {
        $return_array = array();
        $noDisplayDirArray = $inDateDirNameArray;//不显示在列表里的系统文件夹   只搜索目录时用
        if ($isDir == 1) {
            //引入保存的不显示的目录 名称 数组

            //dump($GLOBALS['$noListDirArray']);
            $noDisplayDirArray = array_merge($GLOBALS['$noListDirArray'], $noDisplayDirArray);
        }

        //dump($noDisplayDirArray);
        //添加已经保存到数据库里的文件夹名称
        $dh = dir(DWTPATH . "/" . $nowDir);
        while (($file = $dh->read()) !== false) {
            //屏蔽系统目录
            if (preg_match("#^_(.*)$#i", $file)) continue; #屏蔽FrontPage扩展目录和linux隐蔽目录
            if (preg_match("#^\.(.*)$#i", $file)) continue;

            //屏蔽已经保存到数据库的目录或文件夹
            //dump($file);
            $checkdir = false;

            if (is_array($noDisplayDirArray)) {
                foreach ($noDisplayDirArray as $dirFileName) {
                    //dump($dirName."---".$file)
                    if ($dirFileName == $file) {
                        $checkdir = TRUE;
                        continue;
                    }

                }
            }
            if ($checkdir) continue;

            //屏蔽 XXX.do.php xxx.class.php的页面
            $doClassFiles = explode('.', $file);
            //dump(count($doClassFiles)."-----".$file);
            if (count($doClassFiles) > 2) continue;

            //如果是检索目录 则判断是目录的输出
            if ($isDir == 1 && is_dir(DWTPATH . "/$file")) {
                $return_array[] = $file;
            }

            //如果是检索文件 则判断文件是否以PHP结尾
            if ($isDir == 0 && preg_match("#\.(php)#i", $file)) {
                //如果未传入主功能文件名称 则将不带下划线的主功能文件 返回
                if ($fileName == "" && !strpos($file, "_")) {
                    $return_array[] = $file;
                }

                //如果传入主功能文件名称 则将带下划线的 属于此主功能文件的动作文件 返回
                if ($fileName != "" && strpos($file, str_replace(".php", "", $fileName) . "_") !== false) {
                    $return_array[] = $file;
                    //dump($file);
                }
            }
        }
        return $return_array;
    }



    //父功能 文件夹的格式化文本
    //return str

    /**
     * @param $diri         文件夹记数
     * @param $rowtitle     文件夹标题
     * @param $rowdir       文件夹名称
     *
     * @return string
     */
    function dirHtmlsHeard($diri, $rowtitle, $rowdir)
    {
        if($diri==1){
            $this->panelJScode.="
                var lastShowBaseFileId=$.cookie('lastShowBaseFileId');//获取COOK中最后展开的面板
                if(lastShowBaseFileId){
                    //如果有COOK，则展开
                    $('#dir_panel_'+lastShowBaseFileId).collapse('show');
                }else{
                    //如果没有COOK，则默认打开第一个COOK
                            $('#dir_panel_1').collapse('show');
                }\r\n";
        }

        $this->panelJScode .= "$('#dir_panel_$diri').on('show.bs.collapse', function () {//当点击面板显示的时候  保存当前面板的ID到COOK
                                $.cookie('lastShowBaseFileId', '$diri', { expires: 7 }); // 存储一个带7天期限的 cookie
                             });";

        $rstr = "<div class='panel panel-default'>
                            <div class='panel-heading'>
                                <h5 class='panel-title'>
                                    <a data-toggle='collapse' data-parent='#function' href='#dir_panel_$diri'>
                                           &nbsp;&nbsp;<i class='fa fa-chevron-down' data-toggle='tooltip' data-placement='top' title='点击显示子功能' style='float: left'></i>
                                           &nbsp;&nbsp;<span style='float: left;margin-left: 5px'>$diri</span>
                                           &nbsp;&nbsp;<div   style='width: 50px;float: left;margin-left: 5px'>$rowdir</div>
                                    </a>
                                    <input type='text' name='title_$diri' value='" . $rowtitle . "'  />\r\n
                                    <input type='hidden'  name='dir_$diri' value='" . $rowdir . "'  />\r\n
                                </h5>
                            </div>
                            ";
        return $rstr;
    }

    function dirHtmlsFoot()
    {
        $rstr = "        </div>";
        return $rstr;
    }


    /**
     * 主功能文件信息输出
     *
     * @param                     $diri                  文件夹记数
     * @param                     $filei                 文件记数
     * @param                     $dir                   文件夹名称
     * @param                     $fileName              文件名称
     * @param string|标题名称         $fileTitle             标题名称
     * @param string|数据表名称        $fileDataBaseName      数据表名称
     * @param ID编号的字段名称|string    $fileDataIdName        ID编号的字段名称
     * @param string|涉及部门数据的字段名称  $fileDataDepName       涉及部门数据的字段名称
     * @param string|涉及用户数据的字段名称  $fileDataUserName      涉及用户数据的字段名称
     * @param string|涉及子分类数据的字段名称 $fileDataChildName     涉及子分类数据的字段名称
     * @param string|是否检查部门数据     $fileIsDepCheck        是否检查部门数据 1不检查, 0或空检查
     *
     * @return string
     */
    function fileHtmls($diri, $filei, $dir, $fileName, $fileTitle = "", $fileDataBaseName = "", $fileDataIdName = "", $fileDataDepName = "", $fileDataUserName = "", $fileDataChildName = "", $fileIsDepCheck = "")
    {
        $rstr = "\n<tr>\r\n";
        $rstr .= " <td>$diri-" . $filei . " $fileName </td>\r\n";
        $rstr .= " <td><input type='text'   name='fileTitle_" . $diri . "_" . $filei . "' value='$fileTitle'  />
                        <input type='hidden'   name='fileName_" . $diri . "_" . $filei . "' value='$fileName' />
				   </td>\r\n";
        $rstr .= " <td> <input type='text'  name='fileDataBaseName_" . $diri . "_" . $filei . "' id='fileDataBaseName_" . $diri . "_" . $filei . "' value='$fileDataBaseName' ></td>\r\n";
        $rstr .= " <td> <input type='text'  name='fileDataIdName_" . $diri . "_" . $filei . "'   id='fileDataIdName_" . $diri . "_" . $filei . "' value='$fileDataIdName' ></td>\r\n";
        $rstr .= " <td> <input type='text'  name='fileDataDepName_" . $diri . "_" . $filei . "'  id='fileDataDepName_" . $diri . "_" . $filei . "' value='$fileDataDepName' ></td>\r\n";
        $rstr .= " <td> <input type='text'  name='fileDataUserName_" . $diri . "_" . $filei . "'  id='fileDataUserName_" . $diri . "_" . $filei . "' value='$fileDataUserName' ></td>\r\n";
        $rstr .= " <td> <input type='text'  name='fileDataChildName_" . $diri . "_" . $filei . "'  id='fileDataChildName_" . $diri . "_" . $filei . "' value='$fileDataChildName' ></td>\r\n";
        //如果有部门或用户字段,如果$fileIsDepCheck为1,则代表不判断部门权限
        $fileIsDepCheck_str = "";
        if ($fileDataDepName != "" || $fileDataUserName != "") {
            $fileIsDepCheck_str = $fileIsDepCheck == 1 ? " checked='checked'" : '';
        } else {
            //如果没有部门和用户字段,则$fileIsDepCheck禁用
            $fileIsDepCheck_str = "disabled";
        }
        $rstr .= " <td> <input type='checkbox' name='fileIsDepCheck_" . $diri . "_" . $filei . "'  ";
        $rstr .= "$fileIsDepCheck_str";
        $rstr .= "></td>\r\n";

/*        //获取使用的公司
        $depnumb = 0;
        $dep_view = "";
        global $GLOBAMOREDEP;
        $this->dsql = $GLOBALS['dsql'];
        if ($GLOBAMOREDEP) {
            $funName = $dir . "/" . $fileName;
            $sql1 = "SELECT count(*) as dd  FROM `#@__emp_dep_plus` p LEFT JOIN  `#@__emp_dep` d on d.dep_id=p.depid WHERE  FIND_IN_SET('$funName',functionNames)";
            $row = $this->dsql->GetOne($sql1);
            if (is_array($row)) {
                $depnumb = $row["dd"];
            }

            $url_code = urlencode($funName);
            //系统菜单设定为公用功能，无需设定公司是否可以使用160608
            if($funName!="sys/sysFunction.php")$dep_view = "<a onclick=\"layer.open({type: 2,title: '使用公司', content: 'sysFileBaseConfig_dep.php?filename={$url_code}'});\"  href='javascript:'  >使用公司(" . $depnumb . ")</a> ";
        }
        //dump($depname);
        $rstr .= "  <td>$dep_view</td>";*/
        $rstr .= "</tr>  ";
        return $rstr;
    }

    /**
     * 子功能动作文件信息输出
     *
     * @param                    $diri                       文件夹记数
     * @param                    $filei                      主功能文件记数
     * @param                    $childfileii                动作文件记数
     * @param                    $childFileName              文件名称
     * @param string|标题名称        $childFileTitle             标题名称
     * @param string|是否检查部门数据    $childFileIsDepCheck        是否检查部门数据 1不检查, 0或空检查
     * @param string|涉及部门数据的字段名称 $fileDataDepName            涉及部门数据的字段名称
     * @param string|涉及用户数据的字段名称 $fileDataUserName           涉及用户数据的字段名称
     *
     * @return string
     */
    function childFileHtmls($diri, $filei, $childfileii, $childFileName, $childFileTitle = "", $childFileIsDepCheck = "", $fileDataDepName = "", $fileDataUserName = "")
    {
        $rstr = "\n<tr>\r\n";
        $rstr .= "  <td >&nbsp;&nbsp;&nbsp;&nbsp;$diri-$filei-" . $childfileii . " $childFileName </td>\r\n";

        $rstr .= " <td>&nbsp;&nbsp;&nbsp;&nbsp;<input type='text'   name='childFileTitle_" . $diri . "_" . $filei . "_" . $childfileii . "' value='$childFileTitle'  />
							   <input type='hidden'  name='childFileName_" . $diri . "_" . $filei . "_" . $childfileii . "' value='$childFileName'  />
				   </td>\r\n";

        $rstr .= "  <td> </td>\r\n";
        $rstr .= "  <td> </td>\r\n";
        $rstr .= "  <td> </td>\r\n";
        $rstr .= "  <td> </td>\r\n";
        $rstr .= "  <td> </td>\r\n";

        //如果有部门或用户字段,如果$fileIsDepCheck为1,则代表不判断部门权限
        if ($fileDataDepName != "" || $fileDataUserName != "") {
            $fileIsDepCheck_str = $childFileIsDepCheck == 1 ? " checked='checked'" : '';
        } else {
            //如果没有部门和用户字段,则$fileIsDepCheck禁用
            $fileIsDepCheck_str = "disabled";
        }
        $rstr .= " <td> <input type='checkbox' name='childFileIsDepCheck_" . $diri . "_" . $filei . "_" . $childfileii . "'  ";
        $rstr .= "$fileIsDepCheck_str";
        $rstr .= "></td>\r\n";
        $rstr .= "</tr> ";
        return $rstr;
    }

    /**获取功能,
     *
     * @param $diri    文件夹记数
     * @param $rowdir  文件夹名称
     * @param $row     从配置文件中读取出来的数组
     *
     * @return string
     */
    function getFileInfo($diri, $rowdir, $row = "")
    {
        $fileInDateNameArray = array();  //已经保存在配置文件中的  文件 信息
        $retuStr = "";
        $fileii = 0;
        for ($filei = 1; $filei < count($row); $filei++) {
            if (isset($row[$filei])) {
                $file_info_array = explode(',', $row[$filei]);  //获取子文件数组
                //dump($file_info_array);
                $fileName = $file_info_array[0];//文件名称
                $fileTitle = $file_info_array[1];  //标题名称
                $fileDataBaseName = $file_info_array[2];//数据表名称
                $fileDataIdName = $file_info_array[3];//ID编号的字段名称
                $fileDataDepName = $file_info_array[4];//涉及部门数据的字段名称
                $fileDataUserName = $file_info_array[5];//涉及用户数据的字段名称
                $fileDataChildName = $file_info_array[6];//涉及子分类数据的字段名称
                $fileIsDepCheck = $file_info_array[7];//是否检查部门数据 1不检查, 0或空检查
                $fileInDateNameArray[] = $fileName;   //已经保存到配置中的文件名称
                $fileii++;
                if (file_exists($GLOBALS["cfg_basedir"] . "/" . $rowdir . "/" . $fileName))   //查看文件 是否实际存在
                {
                    $retuStr .= $this->fileHtmls($diri, $fileii, $rowdir, $fileName, $fileTitle, $fileDataBaseName, $fileDataIdName, $fileDataDepName, $fileDataUserName, $fileDataChildName, $fileIsDepCheck, $fileInDateNameArray);
                }
                //输出动作文件
                $retuStr .= $this->getActionInfo($diri, $rowdir, $fileii, $fileName, $fileDataDepName, $fileDataUserName, $row);
            }
        }


        //输出未保存的主功能文件
        foreach ($this->listNoDate($rowdir, $fileInDateNameArray, 0) as $fileName) {
            $fileii++;
            $retuStr .= $this->fileHtmls($diri, $fileii, $rowdir, $fileName);
            //输出未保存的动作文件
            $retuStr .= $this->getActionInfo($diri, $rowdir, $fileii, $fileName);


        }
        $retuStr .= "<input type='text'  name='filenumb_" . $diri . "' value='$fileii'  style='display:none'/><!--功能文件的记数-->\n";

        if ($retuStr == "") {
            $retuStr_temp = "文件夹下无功能名称";
        } else {


            $retuStr_temp = "<div id='dir_panel_$diri' class='panel-collapse collapse '>
                                <div class='panel-body'>
                                    <div class='table-responsive'>
                                        <table id='table$diri'  data-toggle='table'  data-striped='true'>";
            $retuStr_temp .= "<thead>
                        <tr >
														<th  data-halign='center' data-align='left'>文件名称</th>
														<th  data-halign='center' data-align='left'  >
														功能说明
														<i class='glyphicon glyphicon-question-sign' aria-hidden='true'  data-toggle='tooltip' data-placement='top'   data-html=\"true\"  title=\"<p align='left'>用下划线分割便于用户组设定时友好显示</p>\"></i>
                                                        </th>
														<th  data-halign='center' data-align='left' >数据表名称</th>
														<th  data-halign='center' data-align='left' >字段_ID编号名称</th>
														<th  data-halign='center' data-align='left' >字段_部门数据
														<i class='glyphicon glyphicon-question-sign' aria-hidden='true'  data-toggle='tooltip' data-placement='top'  data-html=\"true\"  title=\"<p align='left'>多联表方法:did(device|id|depid)A部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称)</p>\"></i>
														</th>
														<th  data-halign='center' data-align='left' >字段_用户数据</th>
														<th  data-halign='center' data-align='left' >字段_子分类
															<i class='glyphicon glyphicon-question-sign' aria-hidden='true'  data-toggle='tooltip' data-placement='top'  data-html=\"true\"  title=\"<p align='left'>多联表方法:did(device|id|depid)A部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称)</p>\"></i>
														</th>
														<th  data-halign='center' data-align='center' >不参于部门权限判断
														<i class='glyphicon glyphicon-question-sign' aria-hidden='true'  data-toggle='tooltip' data-placement='top'  data-html=\"true\"  title=\"<p align='left'>选择后部门或用户字段不参于权限判断(用户组设定中不显示部门选择框)</p>\"></i>
														</th>

													  </tr>
									</thead><tbody>			  ";
            $retuStr_temp .= $retuStr . "</tbody></table></div></div></div>";
        }
        return $retuStr_temp;


    }


    /**
     * 获取主功能的动作文件
     *
     * @param $diri                  文件夹记数
     * @param $rowdir                文件夹名称
     * @param $fileii                主功能记数
     * @param $fileName              主功能文件名称
     * @param $fileDataDepName       主功能涉及部门数据的字段名称
     * @param $fileDataUserName      主功能涉及用户数据的字段名称
     * @param $row                   从配置文件中读取出来的数组
     *
     * @return mixed|string
     */
    function getActionInfo($diri, $rowdir, $fileii, $fileName, $fileDataDepName = "", $fileDataUserName = "", $row = "")
    {
        //输出主功能文件的动作文件  trchild000000用于当前无动作文件时,隐藏此行
        $retuStr = "";
        $childfileii = 0;
        //已经在配置文件中的数据
        $childInDateNameArray = array();
        if (is_array($row)) {
            if (isset($row[$fileName])) {
                $childFile_array = $row[$fileName];
                if (is_array($childFile_array) && count($childFile_array) > 0) {//160412修复BUG，判断是否数组
                    sort($childFile_array);  //重新按文件字母顺序排序
                    for ($childfilei = 0; $childfilei < count($childFile_array); $childfilei++) {
                        $childFile_info_array = explode(',', $childFile_array[$childfilei]);
                        $childFileName = $childFile_info_array[0];//文件名称
                        $childFileTitle = $childFile_info_array[1];  //标题名称
                        $childFileIsDepCheck = $childFile_info_array[2];//是否检查部门数据 1不检查, 0或空检查
                        $childInDateNameArray[] = $childFileName;   //已经保存到配置中的文件名称
                        if (file_exists($GLOBALS["cfg_basedir"] . "/" . $rowdir . "/" . $childFileName))   //查看文件 是否实际存在
                        {
                            $childfileii = $childfilei + 1;
                            //160729判断当前动作文件,是否当前功能文件的动作文件.(原BUG如果保存过错误的信息,这里没有判断 引起错误)
                            $fileName_temp=str_replace(".php", "",$fileName);//获取功能文件的名称,去除扩展名
                            if (strpos($childFileName, $fileName_temp."_")===0) {
                                $retuStr .= $this->childFileHtmls($diri, $fileii, $childfileii, $childFileName, $childFileTitle, $childFileIsDepCheck, $fileDataDepName, $fileDataUserName);
                            }
                        }
                    }
                }
            }
        }
        //输出未保存的动作文件
        foreach ($this->listNoDate($rowdir, $childInDateNameArray, 0, $fileName) as $childFileName) {
            $childfileii++;
            $retuStr .= $this->childFileHtmls($diri, $fileii, $childfileii, $childFileName, "", "", $fileDataDepName, $fileDataUserName);
        }
        $retuStr .= $this->actionFileHtmlFoot;
        //if ($childfileii == 0) $retuStr .= "<script> $(\"#trchild$diri$fileii\").hide();</script>";//如果没有动作文件,则隐藏
        $retuStr .= "<input type='text'  name='actionnumb_" . $diri . "_" . $fileii . "' value='$childfileii'   style='display:none' /><!--动作文件的记数-->\n";
        return $retuStr;
    }





    //判断文件夹或文件是否存在,如果不存在则给出删除连接
    //$dirFileName  文件夹或文件的名称
    //$dateId   在数据库中保存的ID
    //return bool


//----------------------------------------sys_function_set2file.php中使用---END----------------------------------------


    //根据文件名称 获取基本文件中单条信息
    //四个地方使用
    //3config.php    获取跳转文件页面的页面名称
    //4sysFunction.php  判断文件是否跳转页面


    //160406修改这里只返回了功能名称 ,代表功能是否在baseconfig中定义

    //$key  搜索的键值

    //返回包含键值的单条文件信息
    function getOneBaseConfig($keyword)
    {

        $keyword = $keyword . ",";//160128修复BUG，增加前后逗号,避免文件名查找重复（strpos）。例如，查找signin.php,如果不加前后的逗号，则在lottery_show3_signin.php中也会出现
        global $baseConfigFunArray;
        //在文本文件里判断
        foreach ($baseConfigFunArray as $row) {
            //dump($keyword);
            //dump($row);
            //dump(count($row));
            for ($funi = 0; $funi < count($row); $funi++) {
                //if ( strpos( $s , $key ) !== false )   //这里不用!==了  这个!==是怕找出字符串在0位,我们就是不要用0位的
                // dump($keyword);
                // dump($row[$funi]);
                if (isset($row[$funi]) && strpos($row[$funi], $keyword) == 0) {
                    // dump(strpos($row[$funi], $keyword));
                    return $row[$funi];
                }
            }
        }
    }
}//End Class