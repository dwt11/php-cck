<?php


if (!defined('DWTINC')) exit('Request Error!');

/**
 * 权限组相关
 *
 * @version        $Id: sysGroup.class.php 151010
 * @package
 * @copyright
 * @license
 * @link
 */
class sysGroup
{
    var $dsql;
    var $funFileName_array;  //文件的URL地址
    var $fileTitle_array;  //    第三行 输出带有扩展功能的标题
    var $depName;
    var $depId;
    var $allFileNumb;   //全部子功能的总个数
    var $childFileNumbArray;  //格式$childFileNumbArray[$key]   $key是功能文件从数据库中读取出的ID //功能文件包含的扩展动作文件的个数=数据库中功能文件个数+sys_function_date.PHP中读取出的扩展动作文件(新建-编辑-删除)
    var $fileActionPlusNumbArray;  //格式$fileActionPlusNumbArray[$key]   $key是功能文件从数据库中读取出的ID //=功能文件包含的扩展动作文件(新建-编辑-删除)的个数+自身的个数
    var $allDepNumb;   //全部部门的总个数
    var $save_webRole; //保存获取到的用户选择的权限
    var $save_depRole;//保存获取到的用户选择的权限
    var $deptopid;    //150611添加只获取分厂一级以下的所有部门,如果多部门的才有用
    var $funArray;    //此数据 不可以在类创建时 赋值,容易引起循环调用  造成死循环  必须在每个过程里调用

    var $panelJScode;  //控制面板COOK展开的JS代码


    var $json;//数据

    //php5构造函数
    /**
     * sysGroup constructor.
     *
     * @param int    $deptopid       当前先中的顶级部门
     * @param string $group_typename 组名称,用与判断 是否售卡点,售卡点行只显示对应的部门权限设定框,其他的不显示,列只显示排除名单除外 的
     */
    function __construct($deptopid = 0, $group_typename = "")//$deptopid当前先中的顶级部门
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->depName = array();
        $this->depId = array();
        $this->funFileName_array = array();
        $this->fileTitle_array = array();
        $this->childFileNumbArray = array();
        $this->fileActionPlusNumbArray = array();
        $this->allFileNumb = 0;
        $this->allDepNumb = 0;
        $this->save_webRole = "";
        $this->save_depRole = "";
        $this->json = "";

        require_once(DEDEDATA . "/sys_function_data.php");

        //引入功能类
        require_once("sysFunction.class.php");
        $fun = new sys_function();

        //引入禁用列表
        require_once(DEDEDATA . "/sys_function_noListDir.php");

        $NoDisplyFuc_str="";
        $NoDisplyFuc_str = $groupSETNoDisply_1;
        if ($group_typename != "" && strpos($group_typename, "售卡点子部门") !== false) {
            //如果售卡点子部门  则引入售卡点的禁用
            $NoDisplyFuc_str .= "," . $groupSETNoDisply_1_skd;
        }


        $this->fucArray = $fun->getSysFunArray($deptopid, $NoDisplyFuc_str);  //获取父功能
        $this->getFileArray();   //获取子功能相关数据 并存入数组


        // DUMP($this->fucArray);

        //dump($this->fileTitle_array);//功能的动作列表
        //dump($this->depName);
         
	        $depArray = GetDepSonId_array($deptopid);//获得部门二维数组  [0,x]部门名称 [1,x]对应的部门ID

	// dump($depArray);
        if ($group_typename != "" && strpos($group_typename, "售卡点子部门") !== false) {
            $groupName_temp = str_replace("售卡点子部门-", "", $group_typename);


            /*
                        //售卡点一级功能菜单禁用
                        /*--------------------------------------禁用最大的功能显示
                        foreach ($NoDisplyArray_1_skd as $value){
                            unset($this->fucArray[$value]);
                        }

                        //售卡点二级 级功能菜单禁用
                        /*--------------------------------------禁用第一行的功能显示
                        foreach ($NoDisplyArray_2_skd as $value){
                            foreach ($this->fucArray as $key1=>$value_temp_1){
                                foreach ($value_temp_1 as $key2=>$value_2){


                                    if(strpos($value_2, $value) !== false){
                                      //如果包含禁用列表里的功能 ,则从数组中移除
                                        unset($this->fucArray[$key1][$key2]);
                                    }
                                    //dump($value);

                                }
                            }

                            //dump(SearchOneArray($value));
                            //unset($this->fucArray[$value]);
                        }*/


            /*--------------------------------------禁用第一列部门显示*/
            //包含售卡点
            $onlyGroupTypenameKey = -1;
            //查找包含权限组名称的 部门KEY
            foreach ($depArray[0] as $key => $value) {
                if (strpos($value, $groupName_temp) !== false) {
                    $onlyGroupTypenameKey = $key;
                    $onlyGroupTypenameValue = $value;
                }
            }

            if ($onlyGroupTypenameKey > -1) {
                //如果是售卡点,则只显示对应的部门
                $onlyDEPID = $depArray[1][$onlyGroupTypenameKey];
                $depArray = array();
                $depArray[0][0] = $onlyGroupTypenameValue;
                $depArray[1][0] = $onlyDEPID;
            }

        }

        // dump($this->fucArray);//功能列表
        //dump($this->fileTitle_array);//功能的动作列表

        //dump($depArray);
        $this->allDepNumb = count($depArray[0]); //获取部门总数
        $this->depName = $depArray[0];
        $this->depId = $depArray[1];
    }


    function sysGroup()
    {
        $this->__construct();
    }

    //清理类
    function Close()
    {
    }




    //输出
    /**
     * @param string     $groupWebRanks 编辑或查看时传过来的用户的页面权限值
     * @param string     $groupDepRanks 编辑或查看时传过来的用户的部门权限值
     * @param bool|false $isView
     */
    function getRoleTable($groupWebRanks = "", $groupDepRanks = "", $isView = false)
    {


        $disp = "";
        //if (!$isView && $groupWebRanks != "" && in_array("admin_AllowAll", $groupWebRanks)) $disp = "style='display:none'";//如果是管理员输出显示,页面加载时自动隐藏;不是管理员 ,页面加栽时自动显示
        //echo "<div  id='roleTable'  $disp >\r\n<div class='panel-group' id='group'>";
        echo "<div class='panel-group' id='group'>";
        $i = 0;
        foreach ($this->fucArray as $key => $menu) {
            //$retuStr="";;
            //输出外围的大功能标题  使用工具框

            //如果子功能记录数大于0才输出父功能

            if ($this->childFileNumbArray[$key] > 0) {
                $i++;
                if ($i == 1) {
                    $this->panelJScode .= "
                        var lastShowGroupKey=$.cookie('lastShowGroupKey');//获取COOK中最后展开的面板
                        if(lastShowGroupKey){
                            //如果有COOK，则展开
                            $('#collapse'+lastShowGroupKey).collapse('show');
                        }else{
                            //如果没有COOK，则默认打开第一个COOK
                                    $('#collapse{$key}').collapse('show');
                        }\r\n";
                }

                $this->panelJScode .= "$('#collapse{$key}').on('show.bs.collapse', function () {//当点击面板显示的时候  保存当前面板的ID到COOK
                                $.cookie('lastShowGroupKey', '$key', { expires: 7 }); // 存储一个带7天期限的 cookie
                             });";

                $parentMenu_array = explode(',', $menu[0]);
                $parentTitle = $parentMenu_array[3];

                //输出父功能名称
                echo "<div class='panel panel-default'>
                                <div class='panel-heading'>
                                    <h5 class='panel-title'>
                                    <a data-toggle='collapse' data-parent='#group' href='#collapse{$key}'>
                                                                         <i class='fa fa-chevron-down' data-toggle='tooltip' data-placement='top' title='点击显示子功能'></i>$parentTitle</a>

                                    </h5>
                                </div>
                                <div id='collapse$key' class='panel-collapse collapse'>
                                    <div class='panel-body'>";
                echo "<div class='table-responsive'>\r\n";


                echo "<table id='datalist$key' data-striped='true' class='table  table-condensed' >\r\n</table>\r\n";

                $height = "";
                if (($this->allDepNumb) > 15) $height = ",                            height:600";//如果部门多于15个，则限定表格高度
                $this->json .= "<script>";
                $this->json .= $this->getTabaleHeard($key, $menu, $groupWebRanks, $groupDepRanks, $isView) . "\r\n";
                $this->json .= $this->getData($key, $menu, $groupWebRanks, $groupDepRanks, $isView) . "\r\n";
                $this->json .= "$(function () {
                        $('#datalist$key').bootstrapTable({
                            data: data$key,
                            columns: columns$key
                            $height

                        });
                    });";
                $this->json .= "\r\n</script>\r\n";

                echo "                </div>
                                </div>
                              </div>
                      </div>";
            }


        }
        echo "</div>\r\n ";
    }


    /**
     *输出格式化后的表格数据
     */
    function getJson()
    {
        echo $this->json;
    }


    /** 输出表头
     *
     * @param            $key           父功能的数据库ID
     * @param            $menu          父功能的名称
     * @param string     $groupWebRanks 编辑或查看时传过来的用户的页面权限值
     * @param string     $groupDepRanks 编辑或查看时传过来的用户的部门权限值
     * @param bool|false $isView        false输出checkbox   TRUE输出五角星,查看权限页面用
     *
     * @return string
     */


    function getTabaleHeard($key, $menu, $groupWebRanks = "", $groupDepRanks = "", $isView = false)
    {

        /*默认表头rowspan三行*/
        //左上角第一行第一列
        $returnStr = "
        var columns$key = [
        [{
            \"field\": \"depname$key\",
            \"title\": \"\",
            \"colspan\": 1,
            \"rowspan\": 3,
            \"align\": 'left',
            \"width\":150,
            \"valign\": 'middle',
            class:'colOneWidth'
        },";

        //表头第一行（父功能名称）
        for ($filei = 1; $filei < count($menu); $filei++) {
            $fileMenu = explode(',', $menu[$filei]);
            //dump($fileMenu);
            $fileId = $fileMenu[0];
            $fileTitle = $fileMenu[3];

            $plusi = 0;
            $plusi = $this->fileActionPlusNumbArray[$fileId];
            $colspan = 1;
            $rowspan = 1;
            if ($plusi > 1) {
                $colspan = $plusi;
                $rowspan = 1;
            } else {
                $colspan = 1;
                $rowspan = 2;
            }
            $returnStr .= " {
                            \"title\": \"$fileTitle\",
                            \"colspan\": $colspan,
                            \"rowspan\": $rowspan,
                            \"align\": 'center',
                            \"valign\": 'middle'
                        }";
            if ($filei != count($menu) - 1) $returnStr .= ",";
        }
        $returnStr .= "],[";
        //dump($this->fileTitle_array);

        //第二行 动作功能 从文件中读取 (新建 编辑 删除等)
        if (array_key_exists($key, $this->fileTitle_array)) {
            $countNumb = count($this->fileTitle_array[$key]) - 1;
            for ($actioni = 0; $actioni <= $countNumb; $actioni++) {
                $actionTitle = $this->fileTitle_array[$key][$actioni];
                $returnStr .= "{
                                \"title\": \"$actionTitle\",
                                \"colspan\": 1,
                                \"rowspan\": 1,
                                \"align\": 'center',
                                \"valign\": 'middle'
                           }";
                if ($actioni != $countNumb) $returnStr .= ",";
            }
        }
        $returnStr .= "],[";//如果第二行为空则输出一个空的[]

        //第三行---------------------------------------------CHECKBOX
        //dump($this->fileTitle_array);
        $countNumb = count($this->funFileName_array[$key]) - 1;
        for ($i1 = 0; $i1 <= $countNumb; $i1++) {
            //如果此功能包含部门数据输出部门的总数 否则输出0 用于JS判断 列的CHECKBOX的全选
            $inputStr = "";
            if ($this->isDepDate($this->funFileName_array[$key][$i1])) {
                if (!$isView) $inputStr = " <input  type='checkbox'  id='file_" . $i1 . $key . "'  value=''  onClick=\\\"col_Sel('" . $i1 . $key . "','" . $this->allDepNumb . "')\\\"  >";

                $returnStr .= "{
                                \"field\": \"counts$key.file_$i1$key\",
                                \"title\": \"$inputStr\",
                                \"colspan\": 1,
                                \"rowspan\": 1,
                                \"align\": 'center',
                                \"valign\": 'middle'
                            }";
            } else {
                //如果此功能 不包含部门数据 则输出下面这个checkbox 用于保存页面直接获取 只文件功能的名称
                if (!$isView) $inputStr = " <input name='dep" . $key . "[]' type='checkbox' id='file_" . $i1 . "_-100' value='" . $this->funFileName_array[$key][$i1] . "' ";
                if ($groupWebRanks != "" || $groupDepRanks != "") $inputStr .= " " . $this->CRank(0, $this->funFileName_array[$key][$i1], $groupWebRanks, $groupDepRanks, $isView);
                if (!$isView) $inputStr .= ">";
                $returnStr .= "{
                                \"field\": \"counts$key.file_" . $i1 . "_-100\",
                                \"title\": \"$inputStr\",
                                \"colspan\": 1,
                                \"rowspan\": 1,
                                \"align\": 'center',
                                \"valign\": 'middle'
                            }";
            }
            if ($i1 != $countNumb) $returnStr .= ",";
        }
        $returnStr .= "]];";
        return $returnStr;
    }


    /**
     * 输出所有部门的行:部门
     * 第一列部门名称,第二列CHECKBOX用于行全选
     *
     * @param            $key           父功能的数据库ID
     * @param            $menu          父功能的名称
     * @param string     $groupWebRanks 编辑和查看时使用的  数据库中保存的用户的权限 是数组
     * @param string     $groupDepRanks 编辑和查看时使用的  数据库中保存的用户的权限 是数组
     * @param bool|false $isView        是否查看页面  为1的话 不输出checkbox
     *
     * @return string
     */
    function getData($key, $menu, $groupWebRanks = "", $groupDepRanks = "", $isView = false)
    {
        //dump($this->allDepNumb);
        //dump($this->depName);
        $returnStr = "var data$key = [";

        if ($this->allDepNumb > 0) {
            //dump($this->allDepNumb);
            for ($i2 = 0; $i2 < $this->allDepNumb; $i2++) {
                $depname = $this->depName[$i2];
                if ($depname == "") $depname = "暂无子部门";
                $allCheckBox = "";
                if (!$isView) $allCheckBox = " <span  style='float:right; _position:relative;'><input name='dep" . $i2 . $key . "[]' type='checkbox'   value=''  onClick=\\\"row_Sel('" . $i2 . $key . "')\\\"   ></span>";
                //$returnStr .= "\"$depname\"";


                $returnStr .= "\r\n{\r\n";
                $returnStr .= "\"depname$key\": \"$depname $allCheckBox\",\r\n";
                $returnStr .= "\"counts$key\": {\r\n";

                //输出checkbox
                $countNumb = count($this->funFileName_array[$key]) - 1;
                for ($i3 = 0; $i3 <= $countNumb; $i3++) {
                    $inputStr = "";
                    //页面文件名称 用于存入数据库 $funFileName_array[$i3]
                    //部门ID,用于存入数据库$depId[$i2]
                    //CHECKBOX  行全选用getElementsByName  以dep[]命名
                    //CHECKBOX  列全选用getElementById  以file[]命名
                    // 判断 是否选中 ".CWebRank($row->dir)."
                    if ($this->isDepDate($this->funFileName_array[$key][$i3]))  //如果此功能包含部门数据才输出checkbox 否则输出"-"
                    {
                        if (!$isView) $inputStr = "<input name='dep" . $i2 . $key . "[]' type='checkbox'   id='file_" . $i3 . $key . "_" . $i2 . "' value='" . $this->depId[$i2] . "," . $this->funFileName_array[$key][$i3] . "' ";
                        if ($groupWebRanks != "" || $groupDepRanks != "") $inputStr .= $this->CRank($this->depId[$i2], $this->funFileName_array[$key][$i3], $groupWebRanks, $groupDepRanks, $isView);
                        if (!$isView) $inputStr .= ">";
                    } else {
                        $inputStr = "―";
                    }
                    $returnStr .= "\"file_$i3$key\":\"$inputStr\"";
                    if ($i3 != $countNumb) $returnStr .= ",\r\n";
                }
                $returnStr .= "\r\n}\r\n}";
                if ($i2 != ($this->allDepNumb - 1)) $returnStr .= ",";
            }
        }
        $returnStr .= "];";
        return $returnStr;
    }


    /** 输出第四行 checkbox :用于列全选
     *
     * @param            $key           父功能的数据库ID
     * @param            $menu          父功能的名称
     * @param string     $groupWebRanks 编辑或查看时传过来的用户的页面权限值
     * @param string     $groupDepRanks 编辑或查看时传过来的用户的部门权限值
     * @param bool|false $isView        false输出checkbox   TRUE输出五角星,查看权限页面用
     */


    /*
        160421没有用了
          function getCheckbox($key, $menu, $groupWebRanks = "", $groupDepRanks = "", $isView = false)
        {


            echo "        <th   data-halign='center' data-align='left'   class='th_w_100'><!--第一列和第一行的左上角空白--></th>";   //如果有员工和部门的相关功能,才输出这个第一列

            //dump($this->fileTitle_array);
            //dump($menu);

            //如果没有部门,则输出单行 全选checkbox
            if ($this->allDepNumb == 0) {
                echo "<th   data-halign='center' data-align='center'  >";
                if (!$isView) echo "<input name=\"dep" . $key . "[]\" type='checkbox'  value=''  onClick='row_Sel(\"$key\")'   >";   //没有部门数据时的 列全选
                echo "</th >";
            }

            //dump($this->fileTitle_array);
            for ($i1 = 0; $i1 <= count($this->funFileName_array[$key]) - 1; $i1++) {
                echo "<th  data-halign='center' data-align='center'  >";
                //如果此功能包含部门数据输出部门的总数 否则输出0 用于JS判断 列的CHECKBOX的全选


                //dump($key."---".$i1);
                $title = $this->fileTitle_array[$key][$i1] . "";//输出功能名称//////160112这里是否换行待查  加换行后 会导致表格下面多出


                if ($this->isDepDate($this->funFileName_array[$key][$i1])) {
                    echo $title;
                    if (!$isView) echo " <input  type='checkbox'  id='file_" . $i1 . $key . "'  value=''  onClick='col_Sel(\"" . $i1 . $key . "\",\"" . $this->allDepNumb . "\")'  >";
                } else {   //这个是隐藏的
                    //				   if(!$isView)echo "<input name=\"onlyfile[]\"  type=\"checkbox\" class='np'  id='file_".$i1."_-100'  value=\"".$this->funFileName_array[$key][$i1]."\"";
                    //				   if(!$isView&&($groupWebRanks!=""||$groupDepRanks!=""))echo $this->CRank(0,$this->funFileName_array[$key][$i1],$groupWebRanks,$groupDepRanks,$isView);  //是否选中
                    //				   //if(!$isView)echo " style=\"display:none\"> ";
                    //				   if(!$isView)echo " > ";

                    //dump($key." ".$i1);

                    //如果此功能 不包含部门数据 则输出下面这个checkbox 用于保存页面直接获取 只文件功能的名称
                    echo $title;
                    if (!$isView) echo " <input name=\"dep" . $key . "[]\" type='checkbox' id='file_" . $i1 . "_-100' value=\"" . $this->funFileName_array[$key][$i1] . "\" ";
                    if ($groupWebRanks != "" || $groupDepRanks != "") echo " " . $this->CRank(0, $this->funFileName_array[$key][$i1], $groupWebRanks, $groupDepRanks, $isView);
                    if (!$isView) echo ">";
                }
                echo "</th>\r\n";
            }
        }*/


    /**
     * 输出所有部门的行:部门
     * 第一列部门名称,第二列CHECKBOX用于行全选
     *
     * @param            $key           父功能的数据库ID
     * @param            $menu          父功能的名称
     * @param string     $groupWebRanks 编辑和查看时使用的  数据库中保存的用户的权限 是数组
     * @param string     $groupDepRanks 编辑和查看时使用的  数据库中保存的用户的权限 是数组
     * @param bool|false $isView        是否查看页面  为1的话 不输出checkbox
     */
    /*
     * //160421没用了
     *  function getDeps($key, $menu, $groupWebRanks = "", $groupDepRanks = "", $isView = false)
     {
         //dump($this->allDepNumb);
         //dump($this->depName);
         if ($this->allDepNumb > 0) {
             //dump($key);
             for ($i2 = 0; $i2 < $this->allDepNumb; $i2++) {

                 $colspan = "";
                 if ($isView) $colspan = " colspan='2'";//如果是权限查看则将此列设置为2
                 $depname = $this->depName[$i2];
                 if ($depname == "") $depname = "暂无子部门";
                 echo "<tr >
                                <td  $colspan>$depname";
                 if (!$isView) echo " <span  style='float:right; _position:relative;'><input name=\"dep" . $i2 . $key . "[]\" type='checkbox'   value=\"\"  onClick='row_Sel(\"" . $i2 . $key . "\")'   ></span></td>\r\n";

                 //输出checkbox
                 for ($i3 = 0; $i3 <= count($this->funFileName_array[$key]) - 1; $i3++) {
                     //页面文件名称 用于存入数据库 $funFileName_array[$i3]
                     //部门ID,用于存入数据库$depId[$i2]


                     //CHECKBOX  行全选用getElementsByName  以dep[]命名
                     //CHECKBOX  列全选用getElementById  以file[]命名
                     // 判断 是否选中 ".CWebRank($row->dir)."
                     echo "<td >";

                     if ($this->isDepDate($this->funFileName_array[$key][$i3]))  //如果此功能包含部门数据才输出checkbox 否则输出"-"
                     {
                         if (!$isView) echo "<input name=\"dep" . $i2 . $key . "[]\" type='checkbox'   id='file_" . $i3 . $key . "_" . $i2 . "' value=\"" . $this->depId[$i2] . "," . $this->funFileName_array[$key][$i3] . "\" ";
                         if ($groupWebRanks != "" || $groupDepRanks != "") echo $this->CRank($this->depId[$i2], $this->funFileName_array[$key][$i3], $groupWebRanks, $groupDepRanks, $isView);
                         if (!$isView) echo ">";
                     } else {
                         echo "―";
                     }
                     echo "</td>\r\n";
                 }
                 echo "</tr>";
             }
         }
     }*/


    /**  检查是否已经有此权限
     *
     * @param            $depId         部门ID
     * @param            $funFileName   页面文件名称
     * @param            $groupWebRanks 数组库中保存的值
     * @param            $groupDepRanks 数组库中保存的值
     * @param bool|false $isView
     *
     * @return string
     *        说明:
     * web_role保存数据为:
     *
     * emp/emp.php|emp/emp_add.php|salary/salary.php|sys/sys_user_add.php|emp/dep.php|emp/dep_add.php|emp/worktype.php|emp/worktype_add.php|emp/dep_del.php|emp/dep_edit.php|emp/emp_do.php|emp/emp_edit.php|emp/worktype_del.php|emp/worktype_do.php|emp/worktype_edit.php|emp/emp_del.php|checkin/c_input.php|checkin/c_check.php|checkin/c_list.php|checkin/c_config.php|checkin/c_check_1.php|checkin/c_input_1.php|integral/integral.php|integral/integral_add.php|integral/integral_input.php|integral/integral_checkinConfig.php|integral/integral_guizhe.php|integral/integral_guizhe_add.php|integral/trundle.php|integral/integral_query.php|integral/integral_do.php|integral/integral_guizhe_edit.php|integral/integral_input_1.php|integral/integral_del.php|salary/salary_add.php|salary/salary_day.php|salary/salary_t.php|salary/salary_config.php|salary/salary_do.php|salary/salary_edit.php|salary/salary_del.php|sys/sysInfo.php|sys/sysStepSelect.php|sys/log.php|sys/sysFunction.php|sys/sysCacheUp.php|sys/sysData.php|sys/sysData_revert.php|sys/sysGroup.php|sys/sysGroup_add.php|sys/sys_user.php|sys/log_del.php|sys/sys_user_edit.php|sys/sysData.done.php|sys/sysGroup_edit.php|sys/sysGroup_del.php|sys/sys_user_del.php
     *
     * dep_role保存数据为:
     *
     * 1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51|1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51|1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51|1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28|26,28
     *
     *
     * 数据以|为分隔符   web和dep是对应的个数
     *
     * 检查权限时,先搜索当前页面名称filename在web_role中以|分隔的数组中的索引key
     * 然后获取 dep_role以|分隔的数组KEY为filenameKEY的值,将这个值以","分隔为数组,然后查找depId是否在此数组中,如果在则返回checked
     */
    function CRank($depId, $funFileName, $groupWebRanks, $groupDepRanks, $isView = false)
    {
        $return_str = "";
        //dump($funFileName);
        //dump($groupWebRanks);
        $funFileNameKey = array_search($funFileName, $groupWebRanks);
        if ($funFileNameKey !== false)     //当用 === 或 !== 进行比较时则不进行类型转换，因为此时类型和数值都要比对(因为key值有可能是0,如果用!=比较的话0也是false)
        {
            if ($depId == 0)//如果部门数据为0则表示 不包含部门数据  直接输出判断
            {
                $return_str = " checked";
                if ($isView) $return_str = " ★";
            } else {
                if (in_array($depId, explode(',', $groupDepRanks[$funFileNameKey]))) {
                    $return_str = " checked";
                    if ($isView) $return_str = " ★";
                }
            }
        }
        return $return_str;
    }


    /** 获取用户选中的 功能权限和部门权限 字符串 用于保存到数据库
     *
     * @param $checkBoxArrary
     */
    function getSaveValue($checkBoxArrary)
    {
        //2将 页面文件名称相同的数组,对应的部门ID合并
        $tmpArray = array();
        foreach ($checkBoxArrary as $row) {
            $key = $row['webRole'];
            if (array_key_exists($key, $tmpArray)) {
                $tmpArray[$key]['depRole'] = $tmpArray[$key]['depRole'] . ',' . $row['depRole'];
            } else {
                $tmpArray[$key] = $row;
            }
        }

        //dump($tmpArray);
        /*		  array(2) {
         ["emp/emp.php"] => array(2) {
         ["webRole"] => string(11) "emp/emp.php"
         ["depRole"] => string(89) "1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51"
         }
         ["emp/emp_add.php"] => array(2) {
         ["webRole"] => string(15) "emp/emp_add.php"
         ["depRole"] => string(89) "1,9,23,24,27,52,10,25,26,31,32,11,28,29,30,2,12,13,14,15,16,17,18,19,20,22,3,5,6,35,49,51"
         }
         }
         */


        $All_Role = "";
        $All_webRole = "";
        $All_depRole = "";
        //3将合并后的数组 组合为字符串
        foreach ($tmpArray as $row) {
            $All_Role .= $row["webRole"] . "|" . $row["depRole"] . " ";
            $All_webRole .= $row["webRole"] . "|";
            $All_depRole .= $row["depRole"] . "|";
        }
        $this->save_webRole = rtrim($All_webRole, "|");
        $this->save_depRole = rtrim($All_depRole, "|");
    }


    /**是否包含部门数据 包含返回TRUE 不包含FALSE
     *
     * @param $dirFileName
     *
     * @return bool  功能的文件名称 需要分隔开/号使用
     */
    function isDepDate($dirFileName)
    {


        //dump( $dirFileName);
        // dump(strpos($dirFileName, "http"  ));
        if (!(strpos($dirFileName, "http") !== false))//150814修改BUG,不包含HTTP的内部 功能  才执行下面的判断是否有部门数据
        {
            $dirFileName = ClearUrlAddParameter($dirFileName);   //清除地址里的参数
            $dirFileNames = explode('/', $dirFileName);
            //dump($dirFileName);
            //dump(is_array($dirFileNames));
            if (count($dirFileNames) > 1) {
                $dirName = $dirFileNames[0];
                $url_fileName = ClearUrlAddParameter($dirFileNames[1]);   //清除了文件连接后带的参数
                $url_masterFileName = $url_fileName;


                //在文本文件里判断
                $basicConfig_depName = "";//涉及部门数据的字段名称
                $basicConfig_userName = "";//涉及用户数据的字段名称
                $basicConfig_isdepcheck = "";//是否部门数据 0不是, 1或空是

                //如果当前的文件地址为动作页面,则获取主页面的文件地址
                if (stripos($url_fileName, '_') !== false) {
                    $url_fileName_array = explode("_", $url_fileName);//判断主页面还是动作页面,主页面emp.php 动作页面emp_add.php,动作页面带有下划线
                    $url_masterFileName = $url_fileName_array[0] . ".php";  //得到主文件的名称
                }

                if (isset($GLOBALS['baseConfigFunArray'][$dirName])) {
                    $oneBaseConfigs_array = $GLOBALS['baseConfigFunArray'][$dirName];
                    //从主页面配置中获取是否包含部门数据
                    foreach ($oneBaseConfigs_array as $oneBaseConfigs) {
                        if (is_string($oneBaseConfigs)) {
                            $oneBaseConfigsArray = explode(',', $oneBaseConfigs);
                            $basicConfig_fileName = $oneBaseConfigsArray[0];
                            //如果配置文件中的文件名称 与 当前文件名称 一样 则获取他的相关信息
                            if ($basicConfig_fileName == $url_masterFileName) {
                                $basicConfig_depName = $oneBaseConfigsArray[4];//涉及部门数据的字段名称
                                $basicConfig_userName = $oneBaseConfigsArray[5];//涉及用户数据的字段名称
                                $basicConfig_isdepcheck = $oneBaseConfigsArray[7];//是否部门数据 0不是, 1或空是
                                break;
                            }
                        }
                    }

                    //如果当前的文件地址为动作页面,则将动作页面的显示名称 替换掉
                    if (isset($oneBaseConfigs_array[$url_masterFileName])) {
                        if (stripos($url_fileName, '_') !== false && count($oneBaseConfigs_array[$url_masterFileName]) > 0) {
                            //获取动作页面配置,并将主页面配置中的数据库信息附加上
                            $action_array = $oneBaseConfigs_array[$url_masterFileName];
                            //dump($action_array);
                            foreach ($action_array as $oneActionConfigs) {
                                $oneActionConfigsArray = explode(',', $oneActionConfigs);
                                $basicConfig_fileName = $oneActionConfigsArray[0];
                                //如果配置文件中的文件名称 与 当前文件名称 一样 则获取他的相关信息
                                //dump($oneBaseConfigsArray);
                                if ($basicConfig_fileName == $url_fileName) {
                                    $basicConfig_isdepcheck = $oneActionConfigsArray[2];//是否部门数据 0不是, 1或空是
                                    break;
                                }
                            }
                        }
                    }
                    if (($basicConfig_depName != "" || $basicConfig_userName != "") && $basicConfig_isdepcheck == "") return true;
                }
            }
        }
        return FALSE;
    }




    //-------------------------------------以下只在本页面内调用  外部不引用 --------------------------------------------------


    /**
     *    //获取子功能,并存入数组
     * //$dir 父功能名称
     * //$diri 父功能记记数
     */
    function getFileArray()
    {
        //先获取数据库中的功能,然后根据数据库的功能,获取文本文件里的扩展功能
        foreach ($this->fucArray as $parentIdKey => $menu) {
            $this->childFileNumbArray[$parentIdKey] = count($menu) - 1;    //这里累加父功能的子功能数
            //dump($parentIdKey);
            for ($childi = 1; $childi < count($menu); $childi++) {
                //先获取数据库中的 父功能下的子功能的相关信息
                $childMenu = explode(',', $menu[$childi]);
                $childId = $childMenu[0];
                $childUrlAdd = $childMenu[1];
                $childTitle = $childMenu[3];

                //dump($childUrlAdd);
                $this->fileActionPlusNumbArray[$childId] = 1;

                //再获取子功能的扩展功能
                $isFilePlus = false;
                //150814修改BUG,如果是外部功能 带http的则不判断扩展功能


                if (!(strpos($childUrlAdd, "http") !== false)) $isFilePlus = $this->getIsFilePlusArray($childUrlAdd, $parentIdKey, $childId); //是否包含扩展功能


                //$childUrlAdd=str_replace("*","",$childUrlAdd);
                //dump($childUrlAdd);
                $this->funFileName_array[$parentIdKey][] = $childUrlAdd;              //这个是第四行开始的CHECKBOX使用的功能的 实际文件地址,这个要每个都在入数组    //1201修改为带KEY的数组  便于按父功能分组显示
                //如果包含扩展功能,则将数据库包含的功能加入数组 用于第三行的 标题输出
                //不包含扩展功能,则第二行已经输出了这个标题,就不再输出了
                //????这里有问题,如果列表页不是管理,怎么显示141026
                //这个是从数据库获取的,如何确认与文本文件中的_后面的对应上,以后考虑

                //dump($childTitle);
                if ($isFilePlus) {
                    $this->fileTitle_array[$parentIdKey][] = "列表";//1201修改为带KEY的数组  便于按父功能分组显示   //160112添加了$childTitle   更换了表格显示方式  标题和CHECKBOX在同一个td中显示

                    $this->getFilePlusArray($childUrlAdd, $parentIdKey, $childId, $childTitle);
                }
            }
        }
    }


    /** 判断子功能 是否包含扩展功能
     *
     * @param $childUrlAdd 子功能的实际地址
     * @param $parentIdKey 父功能的数据库ID
     * @param $childId     子功能的数据库ID
     *
     * @return bool 带表 当前数据库中的子功能 是否包含扩展功能
     */
    function getIsFilePlusArray($childUrlAdd, $parentIdKey, $childId)
    {
        global $baseConfigFunArray;
        //得到所有包含filename的字符串
        $filenameArray = explode('/', $childUrlAdd);
        $dirname = $filenameArray[0];//父功能的目录名称
        if (count($filenameArray) > 1) {
            if (isset($baseConfigFunArray[$dirname])) {//160318判断配置文件是否存在
                $oneBaseConfigs_array = $baseConfigFunArray[$dirname];
                if (is_array($oneBaseConfigs_array))// 150814修改BUG 判断数组是否为空
                {
                    $filename = ClearUrlAddParameter($filenameArray[1]); //获取功能名称
                    if (isset($oneBaseConfigs_array[$filename]) && count($oneBaseConfigs_array[$filename]) > 0) {
                        return true; //如果主功能,有子数组,则代表 有动作的扩展功能
                    }
                }
            }
        }
        return false;
    }


    /**获取扩展功能的数据 并存入数组
     *
     * @param $childUrlAdd 子功能的实际地址
     * @param $parentIdKey 父功能的数据库ID
     * @param $childId     子功能的数据库ID
     *
     * 不返回数据
     */

    function getFilePlusArray($childUrlAdd, $parentIdKey, $childId, $childTitle)

    {
        //引入功能的文本文件
        global $baseConfigFunArray;
        $filenameParameter = "";//文档相关页面地址后的 CID参数

        //得到所有包含filename的字符串
        $filenameArray = explode('/', $childUrlAdd);
        $dirname = $filenameArray[0];//父功能的目录名称
        $filename = ClearUrlAddParameter($filenameArray[1]); //获取文件名称
        $filenameParameter = ReturnUrlAddParameter($filenameArray[1]); //获取文档相关页面地址后的 CID参数
        foreach ($baseConfigFunArray[$dirname][$filename] as $value) {
            $childMenu_plus = explode(',', $value);
            $childUrlAdd_plus = $dirname . "/" . $childMenu_plus[0];   //文本文件中的数据地址只有文件名称无目录名称 所以要加上目录名称,供存入数组调取
            if ($filenameParameter != "") $childUrlAdd_plus .= $filenameParameter;
            $childTitle_plus_array = explode('_', $childMenu_plus[1]);
            $childTitle_plus = "";
            if (is_array($childTitle_plus_array) && count($childTitle_plus_array) > 1) $childTitle_plus = $childTitle_plus_array[1];     //150131修复BUG原来没有计算长度
            $this->childFileNumbArray[$parentIdKey]++;    //这里累加父功能的子功能数  第一行
            $this->fileActionPlusNumbArray[$childId]++;   //子功能的扩展功能记数  第二行
            $this->allFileNumb++;  //第三行的列数
            $this->funFileName_array[$parentIdKey][] = $childUrlAdd_plus;   //1201修改为带KEY的数组  便于按父功能分组显示
            $this->fileTitle_array[$parentIdKey][] = $childTitle_plus;      //1201修改为带KEY的数组  便于按父功能分组显示
        }
    }


    //-------------------------------------以上只在本页面内调用  外部不引用 --------------------------------------------------


}//End Class