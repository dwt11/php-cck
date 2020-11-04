<?php if (!defined('DWTINC')) exit("Request Error!");
/**
 * 权限类
 * 说明:系统底层权限核心类
 *
 * @version        $Id: role.class.php
 * @package
 * @copyright
 * @license
 * @link
 */

/**
 *
 *
 * @property  dsql
 * @package
 * @subpackage
 * @link
 */
class roleClass
{
    var $funName; //当前浏览的页面名称 用于标题显示
    var $url_dirName; //当前显示页面的目录 名称
    var $url_fileName;//当前显示页面的文件名称
    var $url_parameter;//当前显示页面的参数

    var $url_masterParameter;   //有分类页面,主页面中显示添加按钮时,要引用的分类参数
    //var $isRole;//是否有权限

    var $basicConfig_Name;      //文件功能名称
    var $basicConfig_dataBaseName;   //数据库名称
    var $basicConfig_idName;//ID编号的字段名称
    var $basicConfig_depName;//涉及部门数据的字段名称
    var $basicConfig_userName;//涉及用户数据的字段名称
    var $basicConfig_childName;//涉及子分类数据的字段名称
    var $basicConfig_isdepcheck;//是否检查部门数据 1不检查, 0或空检查


    var $parentUrlArray;//分类页面,所有的上级地址


    //-------------------------数据表中存储的
    var $web_role;//当前登录用户的 所有权限地址以|分割           用于判断当前打开的页面是否具有权限
    var $web_role_array;//当前登录用户的所有权限地址 数组        用于判断当前打开的页面是否具有权限
    var $web_role_more_array;//当前登录用户  以每个权限组为单位,将页面权限存入数组   用于得到当前页面,可以操作的部门数据的部门ID
    var $dep_role_more_array;//当前登录用户  以每个权限组为单位,将部门权限存入数组   用于得到当前页面,可以操作的部门数据的部门ID
    //-------------------------数据表中存储的


    var $isNoCheckRoleFileUrl;   //是否要检查权限的文件地址  是否不检查权限的文件   true 不检查   false检查

    /**
     *构造函数    php5构造函数
     *
     *
     *
     * 页面打开的主页面从这里判断,当主页面是动作页面时跳转到display_action判断
     *
     */
    function __construct()
    {
        $this->dsql = $GLOBALS['dsql'];


        //--------------------------读取配置文件
        require_once(DEDEDATA . "/sys_function_data.php");
        $dwtNowUrl = GetCurUrl();//得到当前的地址

        $dwtNowUrl_array = explode('/', $dwtNowUrl);
        $doClassFiles_array = explode('.', $dwtNowUrl);//如果是 XXX.do.php xxx.class.php的页面  (这些页面不参与权限判断)
        //只检查一级目录下的权限,如果是/include/ueditor/php/action.upload.php这样的三级目录的不检查
        //并且地址 不是外部连接地址
        //并且文件名称中只有一个 点emp.php,不检查emp.do.php
        if (count($dwtNowUrl_array) == 3 && !strpos($dwtNowUrl, "http") !== false && count($doClassFiles_array) == 2) {
            $this->url_dirName = $dwtNowUrl_array[1];//目录名称
            $url_fileNameAndParameter = $dwtNowUrl_array[2];//文件名称+参数
            $url_fileNameAndParameter_array = explode("?", $url_fileNameAndParameter);
            $this->url_fileName = $url_fileNameAndParameter_array[0];//文件名称
            if (count($url_fileNameAndParameter_array) > 1) $this->url_parameter = $url_fileNameAndParameter_array[1];//网址参数
        } else {
            $this->isNoCheckRoleFileUrl = true;
            //return;//此句不需要也可以160322?????
        }


    }


    //对于使用默认构造函数的情况
    function roleClass()
    {
        $this->__construct();
    }


    /**
     *权限判断的友好显示,
     * 没有权限返回提示框
     * 有权限,直接显示相应的页面
     */
    function RoleCheckToOpen()
    {

        if (!$this->RoleCheckToBool()) {
            //160729 删除跳转连接,这里只提示
            ShowMsg("对不起，你没有权限执行此操作！ ", 'javascript:;');
            exit();
        }
    }


    /**
     *
     * 判断当前页面是否具有权限
     * 返回真假
     * 1\用于这个类里调用,不传递地址
     * 2其他需要判断是否具有权限的地方,传递地址
     *
     * @param string $n
     *
     * @return bool
     */
    function RoleCheckToBool($n = "")
    {


        //dump($n);
        //原在setRoleSql中初始化，现在移到这里看效果 161017
        global $ROLE_WHERE_SQL;    //部门限制使用的查询数据,返回SQL语句,在SQL类DEDESQL.CLASS.PHP中固化
        global $ROLE_DATABASE;    //部门限制使用的查询数据时，同时要加上数据表名称，在SQL类DEDESQL.CLASS.PHP中固化
        $ROLE_WHERE_SQL = $ROLE_DATABASE = "";    //部门限制使用的查询数据时，同时要加上数据表名称，在SQL类DEDESQL.CLASS.PHP中固化
        //dump($ROLE_WHERE_SQL."---".$ROLE_DATABASE);
        if ($n == "") {
            //dump($this->isNoCheckRoleFileUrl);
            if ($this->isNoCheckRoleFileUrl) return true;
        }


        //如果有传入地址 刚从新的获取参数
        if ($n != "") {
            //此处传进来的地址 最前面没有/,所以要加上,判断是否检查权限的时候要用
            $dwtNowUrl = "/" . $n;//得到当前的地址
            $dwtNowUrl_array = explode('/', $dwtNowUrl);
            $doClassFiles_array = explode('.', $dwtNowUrl);//如果是 XXX.do.php xxx.class.php的页面  (这些页面不参与权限判断)
            //只检查一级目录下的权限,如果是/include/ueditor/php/action.upload.php这样的三级目录的不检查
            //并且地址 不是外部连接地址
            //并且文件名称中只有一个 点emp.php,不检查emp.do.php   //160606优化  有且只有一个点    ??????类初始化的时候也有这样一个判断 的过程，看看是否也要优化(原来的文件没有点的话，直接通过权限 )
            // dump($dwtNowUrl);
            //dump(!strpos($dwtNowUrl, "http") !== false);

            //dump(count($dwtNowUrl_array));
            //dump ( count($doClassFiles_array) == 2) ;
            if (!(count($dwtNowUrl_array) == 3 && !strpos($dwtNowUrl, "http") !== false) && count($doClassFiles_array) == 2) {
                return true;
            }
            //dump($n);

            $n_array = explode('/', $n);
            $this->url_dirName = $n_array[0];//目录名称
            if (count($n_array) > 1) {
                $url_fileNameAndParameter = $n_array[1];//文件名称+参数
                $url_fileNameAndParameter_array = explode("?", $url_fileNameAndParameter);
                $this->url_fileName = $url_fileNameAndParameter_array[0];//文件名称
                if (count($url_fileNameAndParameter_array) > 1) {
                    $this->url_parameter = $url_fileNameAndParameter_array[1];//网址参数
                } else {
                    $this->url_parameter = "";//网址参数
                }
            }
        }
        //160618添加 系统菜单设定  不限定权限
        $url_temp = $this->url_dirName . "/" . $this->url_fileName;
        //170313???????这里是临时的补丁   商品带子分类的  判断 不出添加的权限
        //RoleCheckToLink功能中也有一处
        //dump(stripos($url_temp,"_add")>0);
        if (
            stripos($url_temp, "goods_benefit.php") > 0
            || stripos($url_temp, "goods_benefitadd.php") > 0
            || stripos($url_temp, "goods_benefitdel.php") > 0
            || stripos($url_temp, "goods_add") > 0
            || stripos($url_temp, "goods_edit") > 0
            || stripos($url_temp, "goods_stop") > 0
        ) {
            //if (stripos($url_temp, "_benefit.php") > 0  || stripos($url_temp, "_edit") > 0 || stripos($url_temp, "_stop") > 0) {
            return true;
        }
        if ($url_temp == "sys/sysFunction.php" || $url_temp == "sys/sysFunction_add.php" || $url_temp == "sys/sysFunction_edit.php") return true;

        //读取配置信息
        $this->getConfig();

        //--------------------------获取显示名称
        $this->getFunName();

        //--------------------------获取用户权限
        $this->getUserWebRole();
        //1如果是管理员 则返回TRUE
        if ($this->checkAdminRole()) return true;


        //获取所有父分类的连接
        $this->getParentUrl();

        //加载可以显示的部门数据范围
        $this->displayDepDataToSqlClassGLOBAL();


        if (stripos($this->url_fileName, '_') !== false) {
            //判断地址中,是否有下划线,添加-编辑-删除等,使用display_action判断
            if ($this->display_action()) return true;
        } else {
            //不带下划线的主页面,这里判断
            //3判断当前地址 不包含参数 是否有权限
            //dump("row202 ".$this->url_fileName);
            $forCheckRole_dirAndFileName = $this->url_dirName . "/" . $this->url_fileName;  //用于检查权限的功能文件地址  默认是不带参数的
            //dump($this->web_role_array);
            if (in_array($forCheckRole_dirAndFileName, $this->web_role_array)) return true;

        }
        //4判断带子分类 类型的地址 ,所有的父分类地址 只要有一个权限,就返回真
        //array_intersect返回两个数组 的交集
        //dump($this->parentUrlArray);
        //dump($this->web_role_array);
        if (
            is_array($this->parentUrlArray)
            && is_array($this->web_role_array)
            && (count(array_intersect($this->parentUrlArray, $this->web_role_array)) > 0)
        ) return true;
        //160422移动到 getParentUrl  displayDepDataToSqlClassGLOBAL下面,实现部门管理员登录 后的权限判断
        //2如果权限是公司管理员(不用判断是否多公司版本,直接判断是否有dep_DepAll就可以了)

        return false;
    }


    /**
     * 页面中显示的动作地址 判断
     *返回 为真返回连接地址
     *     为假只返回文字
     *
     * @param string $n           文件地址
     * @param string $putTipName  提示文字 如果有提示文字 ,则直接输出提示文字
     * @param string $class       连接样式   160413添加X项目中使用，默认为空
     * @param bool   $isLayer     是 否输出弹窗  160413添加X项目中使用，（默认输出文本连接） true的话，输出弹窗
     *
     * @param string $icon        连接图标  默认为空
     *
     * @param bool   $isConfirm   是否弹出确认提示
     *
     * @param string $confirmInfo 确认提示窗的消息170203增加
     *
     * @return string
     */
    function RoleCheckToLink($n, $putTipName = "", $class = "", $isLayer = false, $icon = "", $isConfirm = false, $confirmInfo = "")
    {


        //170101要检查这里，在做后台积分明细 时，如果权限判断两个表有相同的字段，会出错
        //dump($n);
        $this->reset();
        //dump(stripos($n,"_add")>0);
        $returnStr = "";
        $role = "";
        $n_array = explode('/', $n);
        $this->url_dirName = $n_array[0];//目录名称
        if (count($n_array) < 2) {
            $this->saveConfigError();
            return "出错";
        }
        $url_fileNameAndParameter = $n_array[1];//文件名称+参数
        $url_fileNameAndParameter_array = explode("?", $url_fileNameAndParameter);
        $this->url_fileName = $url_fileNameAndParameter_array[0];//文件名称
        if (count($url_fileNameAndParameter_array) > 1) {
            $this->url_parameter = $url_fileNameAndParameter_array[1];//网址参数
        } else {
            $this->url_parameter = "";//网址参数
        }

        //读取配置信息
        $this->getConfig();
        $this->getFunName();


        //如果没有用户权限则获取
        if (!isset($this->dep_role_more_array)) $this->getUserWebRole();


        //---------------------获取显示的名称
        if ($putTipName == "") {
            $funName_array = explode("_", $this->funName);
            //获取连接显示的名称 例子  员工_添加
            if (count($funName_array) > 1) {
                $tipName = $funName_array[1];
            } else {
                $tipName = $this->funName;
            }
        } else {
            $tipName = $putTipName;
        }
        //dump($this->funName);

        $linkUrl = $this->url_fileName;//有权限时 输出的连接   无分类添加页面 //160413修改 输出兼容连接和弹窗
        if ($this->url_parameter != "") {
            //有权限时 输出的连接  编辑和删除页面
            $linkUrl = $this->url_fileName . "?" . $this->url_parameter;   //160413修改 输出兼容连接和弹窗
        } else {
            //如果当前连接没有参数,并包含子分类 则获取他的所有上级的地址
            if ($this->basicConfig_childName != "") {
                $masterPaeUrl = GetCurUrl();//当前浏览的主页面的地址
                preg_match_all("#" . $this->basicConfig_childName . "=[0-9]*#", $masterPaeUrl, $matchs);
                if (isset($matchs[0][0])) {
                    $this->url_masterParameter = $matchs[0][0];//获取主页面的分类参数
                    $linkUrl = $this->url_fileName . "?" . $this->url_masterParameter;//有权限时 输出的连接  有分类的添加页面的连接   //160413修改 输出兼容连接和弹窗
                }
            }
        }
        //---------------------获取显示的名称

        //160413修改 输出兼容连接和弹窗
        //$linkInfo = " <a href=\"$linkUrl\">$tipName</a>";//有权限时 输出的连接  有分类的添加页面的连接
        $role = $this->display_action();


        //这里是临时的补丁   商品带子分类的  判断 不出添加的权限
        //行141也有一处 RoleCheckToBool


        if (
            stripos($n, "goods_benefit.php") > 0
            || stripos($n, "goods_benefitadd") > 0
            || stripos($n, "goods_benefitdel") > 0
            || stripos($n, "goods_add") > 0
            || stripos($n, "goods_edit") > 0
            || stripos($n, "line_stop") > 0
        ) {
            // if (stripos($n, "goods_benefit.php") > 0  || stripos($n, "_edit") > 0 || stripos($n, "_stop") > 0) {
            $role = true;
        }

        if (!$role) {
            $returnStr = " <span style='color: #666666;text-decoration:line-through;'>" . $tipName . "</span> ";
            if ($icon != "") {
                $urlText = "<i class='$icon' aria-hidden='true'></i>";//如果图标不为空 则连接改为图标
                $returnStr = "<a   data-toggle='tooltip' data-placement='top'  title='$tipName 无权限'  class= 'btn btn-default' >$urlText</a>  ";
            }
        } else {
            if ($class != "") $class = " class= \"$class\"";//如果样式不为空 ，则加样式
            $urlText = $tipName;
            if ($icon != "") $urlText = "<i class='$icon' aria-hidden='true'></i>";//如果图标不为空 则连接改为图标
            $linkInfo = "<a href=\"$linkUrl\" data-toggle='tooltip' data-placement='top'  title='$tipName'  $class > $urlText </a>  ";//普通无弹窗


            ////弹出子窗体操作
            if ($isLayer) $linkInfo = "<a onclick=\"layer.open({type: 2,title: '$tipName', content: '$linkUrl'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='$tipName' $class> $urlText </a>  ";

            ////不是删除连接  操作确认为真的 弹出确认框
            ////if($confirmInfo=="您确定要进行此操作吗?")$confirmInfo="";170302注释掉,引起对话框无提示文字
            if ($confirmInfo == "") $confirmInfo = "您确定要进行此操作吗?";//170302修改
            if (stripos($linkUrl, "_del") === false && $isConfirm) $linkInfo = "<a onclick=\"layer.confirm('$confirmInfo', {icon: 3, title: '提示'}, function (index) {location.href = '$linkUrl';layer.close(index);});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='$tipName' $class> $urlText </a>  ";

            ////删除连接  不论操作确认是什么 都 弹出确认框
            if (stripos($linkUrl, "_del") !== false) $linkInfo = "<a onclick=\"layer.confirm('您确定要删除此内容吗？', {icon: 3, title: '提示'}, function (index) {location.href = '$linkUrl';layer.close(index);});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='$tipName' $class> $urlText </a>  ";

            $returnStr = $linkInfo . " ";
            //dump($returnStr."----");
        }
        return $returnStr;
    }


    /**
     *复位参数
     */
    function reset()
    {
        $this->parentUrlArray = "";    //每次权限判断后都要将父分类数组清空
        $this->funName = "";
        $this->basicConfig_Name = "";
    }


    /**
     *主页面中,的动作连接的权限
     */
    function display_action()
    {
        //1如果是管理员 则返回TRUE
        if ($this->checkAdminRole()) return true;


        //dump("111");
        //2如果权限是公司管理员(不用判断是否多公司版本,直接判断是否有dep_DepAll就可以了)
        //if ($this->checkDepRole()) return true;


        //获取所有父分类的连接
        $this->getParentUrl();
        // dump("role338");
        //dump($this->parentUrlArray);

        //包含参数,并且参数是编辑和删除页面用的ID
        //判断带ID的编辑和删除,在页面显示时的权限
        //$this->basicConfig_idName  数据库中的ID名称
        //$this->url_parameter  页面连接中传递过来的ID名称,这两个要一致
        preg_match_all("#" . $this->basicConfig_idName . "=[0-9]*#", $this->url_parameter, $matchs);

        //dump($this->basicConfig_idName);
        //dump( $this->url_parameter);
        //160408优化,上面语句截取出来的有可能是错误的,比如DEPID=40,但正则截取ID时也会出结果
        //加了下面的判断ID的起始位置必须是0
        //160729优化，增加$this->basicConfig_idName判断是否为空，避免 获取不到动作文件对应的功能文件的ID名称，而报错
        //160729原stripos两个==判断,修改为三个===
        //dump ($this->basicConfig_idName ) ;
        //dump ( stripos($this->url_parameter, $this->basicConfig_idName) ) ;
        //dump (isset($matchs[0][0])) ;
        if ($this->basicConfig_idName != "" && stripos($this->url_parameter, $this->basicConfig_idName) === 0 && isset($matchs[0][0])) {
            // dump(11);
            //如果传过来的参数是对应表的编号键,则判断用户是否具有权限
            $idParameter = $matchs[0][0];
            $parameterValue = str_replace($this->basicConfig_idName . "=", "", $idParameter);//获取参数的值
            $role_where_in_dep_id_str = "";
            //是否有用户id字段,有的话,先判断是否自己发布的,自己发布的有权限
            if ($this->basicConfig_userName != "") {
                $sql = "SELECT $this->basicConfig_userName,'1' as noroleordernumb FROM #@__$this->basicConfig_dataBaseName WHERE $this->basicConfig_idName='$parameterValue'";
                $depInfo = $this->dsql->getone($sql);
                if ($depInfo != "") {
                    $userName_value = $depInfo[$this->basicConfig_userName];//当前ID对应的部门数据
                    if ($userName_value == $GLOBALS['CUSERLOGIN']->getUserId()) return true;
                }
            }

            //是否有部门字段,有的话,判断可以管理的部门字段
            if ($this->basicConfig_depName != "" && $this->basicConfig_isdepcheck == "") {

                //dump($this->basicConfig_depName);
                /*
                 * 160407
                did(device|id|depid)
                A部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称)

                此处存在两种部门数据字式
                第一种,当前数据表中,直接保存有"部门id"字段,则直接取字段数据比较
                第二种,不在当前数据表中,与其他数据表关联. 则LEFT JOIN,从相关表中取部门的数据,格式如上注释
                */
                $fieldDepname = $this->basicConfig_depName;
                $sql = "SELECT #@__$this->basicConfig_dataBaseName.$fieldDepname FROM #@__$this->basicConfig_dataBaseName WHERE #@__$this->basicConfig_dataBaseName.$this->basicConfig_idName='$parameterValue'";
                if (stripos($this->basicConfig_depName, "(") !== false) {
                    //did(device|id|depid)
                    $nowDataBaseDepId = "";  //与关联表 关联的源数据表的ID
                    $nowDataBaseName = $this->basicConfig_dataBaseName;  //源数据表名称
                    $joinDataBaseName = "";  //关联判断部门数据的数据表名称
                    $joinDataBaseId = "";  //关联表的ID
                    $fieldDepname = "";  //要取出供使用的部门ID

                    $temp_array = explode("(", $this->basicConfig_depName);
                    $nowDataBaseDepId = $temp_array[0];
                    $join_array = explode("|", $temp_array[1]);
                    $joinDataBaseName = $join_array[0];
                    $joinDataBaseId = $join_array[1];
                    $fieldDepname = rtrim($join_array[2], ")");

                    //如果包含(,则使用第二种方式判断
                    //161017修复BUG，原有表的别名，现在取消掉，直接使用表名称
                    //170102增加字段名的表前辍
                    //170525权限这里的 在DWTSQL.CLASS.PHP中不再检查权限
                    $sql = "SELECT #@__$joinDataBaseName.$fieldDepname ,'1' as noroleordernumb
                                FROM #@__$nowDataBaseName
                                INNER JOIN #@__$joinDataBaseName  ON #@__$joinDataBaseName.$joinDataBaseId=#@__$nowDataBaseName.$nowDataBaseDepId
                                WHERE #@__$nowDataBaseName.$nowDataBaseDepId=(
                                  SELECT $nowDataBaseDepId FROM #@__$nowDataBaseName where $this->basicConfig_idName='$parameterValue'
                                )";

                }
                // dump($sql);
                $depInfo = $this->dsql->getone($sql);
                if ($depInfo != "") {
                    $basicConfig_depName_value = $depInfo[$fieldDepname];//当前ID对应的部门数据
                    $role_where_in_dep_id_str = $this->getDepRole();


                    foreach (explode(",", $basicConfig_depName_value) as $value) {//160422客户表中的depids为多部门公用 (1,2,1)这样的,所以分隔开再 判断
                        //if (stripos($role_where_in_dep_id_str, $value) !== false) return true;//判断从数据库中查出的部门数值,是否包含在当前登录用户可以操作的部门值中
                        if (in_array($value, explode(",", $role_where_in_dep_id_str))) return true;//判断从数据库中查出的部门数值,是否包含在当前登录用户可以操作的部门值中   160422修改原使用字符串判断  改为数组判断
                    }
                }
            }

            //如果部门字段为空,用户字段不为空时,才生成按用户字段查询的语句,(如果只判断用户字段不为空,就生成的话,数据会重复)
            if (($this->basicConfig_userName != "" && $this->basicConfig_depName == "") && $this->basicConfig_isdepcheck == "") {
                $sql = "SELECT $this->basicConfig_userName,'1' as noroleordernumb FROM #@__$this->basicConfig_dataBaseName WHERE $this->basicConfig_idName='$parameterValue'";
                $depInfo = $this->dsql->getone($sql);
                //dump($depInfo);
                if ($depInfo != "") {
                    //dump($this->url_dirName . "/" . $this->url_fileName);
                    $basicConfig_userName_value = $depInfo[$this->basicConfig_userName];//当前ID对应的部门数据
                    $role_where_in_dep_id_str_temp = $this->getDepRole();
                    if ($role_where_in_dep_id_str_temp != "") {
                        $role_where_in_dep_id_str = GetDepAllUserId($role_where_in_dep_id_str_temp);
                    }
                    //dump($role_where_in_dep_id_str);
                    //dump( $basicConfig_userName_value);
                    foreach (explode(",", $basicConfig_depName_value) as $value) {//160422客户表中的depids为多部门公用 (1,2,1)这样的,所以分隔开再 判断
                        //if (stripos($role_where_in_dep_id_str, $basicConfig_userName_value) !== false) return true;//判断从数据库中查出的部门数值,是否包含在当前登录用户可以操作的部门值中
                        if (in_array($value, explode(",", $role_where_in_dep_id_str))) return true;//判断从数据库中查出的部门数值,是否包含在当前登录用户可以操作的部门值中   160422修改原使用字符串判断  改为数组判断
                    }
                }
            }


            //160729是否有子分类字段,有的话,判断可以管理的数据
            if ($this->basicConfig_childName != "") {
                //dump("450");
                // dump($this->parentUrlArray);
                // dump($this->parentUrlArray);
                //4判断带子分类 类型的地址 ,所有的父分类地址 只要有一个权限,就返回真
                if (is_array($this->parentUrlArray) && is_array($this->web_role_array) && (count(array_intersect($this->parentUrlArray, $this->web_role_array)) > 0)) return true;
            }

            //dump("role434".$this->basicConfig_childName."00000");
            //160608添加 如果目标功能 不包含userid也不包含depid则默认 可以编辑这个分类下的任何数据
            //160729 如果目标功能不包含userid depid  无子分类数据  则可以动作这个分类下的任何数据
            //161001修改，原来这里没有判断 过来的地址是否在权限组字段内容中
            $forCheckRole_dirAndFileName = $this->url_dirName . "/" . $this->url_fileName;  //用于检查权限的功能文件地址  默认是不带参数的
            if ($this->basicConfig_depName == "" && $this->basicConfig_userName == "" && $this->basicConfig_childName == "" && in_array($forCheckRole_dirAndFileName, $this->web_role_array)) return true;
        } else {
            //当前地址 不包含参数 如果emp_add.php
            //140608或emp_add.php?depid=00这样的带其他 参数的添加页面
            //dump($this->url_parameter);

            $forCheckRole_dirAndFileName = $this->url_dirName . "/" . $this->url_fileName;  //用于检查权限的功能文件地址  默认是不带参数的
            //添加页面,不带子分类

            if (in_array($forCheckRole_dirAndFileName, $this->web_role_array)) return TRUE;

//dump($this->basicConfig_childName);
//dump($this->url_masterParameter);
            //如果当前功能包含子分类 则获取他的所有上级的地址
            // if ($this->basicConfig_childName != "" && $this->url_masterParameter != "") {

            //$this->url_masterParameter
            if ($this->basicConfig_childName != "") {
                $this->url_parameter = $this->url_masterParameter;//获取主页面的分类参数
                //获取所有父分类的连接
                $this->getParentUrl();//更新父分类的地址数组
                //4判断带子分类 类型的地址 ,所有的父分类地址 只要有一个权限,就返回真
                //dump($this->parentUrlArray);
                if (is_array($this->parentUrlArray) && is_array($this->web_role_array) && (count(array_intersect($this->parentUrlArray, $this->web_role_array)) > 0)) return true;
            }

        }


        return false;
    }

    /**
     *获取功能的配置信息
     */
    function getConfig()
    {
        if (isset($GLOBALS['baseConfigFunArray'][$this->url_dirName])) {
            $oneBaseConfigs_array = $GLOBALS['baseConfigFunArray'][$this->url_dirName];//得到配置文件中的所有数据
            $url_masterFileName = $this->url_fileName;
            //如果当前的文件地址为动作页面,则获取主页面的文件地址
            if (stripos($url_masterFileName, '_') !== false) {
                $url_fileName_array = explode("_", $url_masterFileName);//判断主页面还是动作页面,主页面emp.php 动作页面emp_add.php,动作页面带有下划线
                $url_masterFileName = $url_fileName_array[0] . ".php";  //得到主文件的名称
            }
            //从主页面配置中获取数据库表名称
            foreach ($oneBaseConfigs_array as $oneBaseConfigs) {
                if (is_string($oneBaseConfigs)) {
                    $oneBaseConfigsArray = explode(',', $oneBaseConfigs);
                    //dump("role.473--" );
                    //dump( $oneBaseConfigsArray);
                    $basicConfig_fileName = $oneBaseConfigsArray[0];

                    //如果配置文件中的文件名称 与 当前文件名称 一样 则获取他的相关信息

                    if ($basicConfig_fileName == $url_masterFileName) {
                        if (stripos($this->url_fileName, '_') === false) $this->basicConfig_Name = $oneBaseConfigsArray[1];  // 162504修复BUG,场景(在主页面中调用动作页面连接判断,但动作页面没有实际文件,则不获得名称)
                        $this->basicConfig_dataBaseName = $oneBaseConfigsArray[2];
                        $this->basicConfig_idName = $oneBaseConfigsArray[3];//ID编号的字段名称
                        $this->basicConfig_depName = $oneBaseConfigsArray[4];//涉及部门数据的字段名称
                        $this->basicConfig_userName = $oneBaseConfigsArray[5];//涉及用户数据的字段名称
                        $this->basicConfig_childName = $oneBaseConfigsArray[6];//涉及子分类数据的字段名称
                        $this->basicConfig_isdepcheck = $oneBaseConfigsArray[7];//是否检查部门数据 1不检查, 0或空检查
                        break;
                    } else {
                    }
                }
            }

            //如果当前的文件地址为动作页面,则将动作页面的显示名称 替换掉
            if (isset($oneBaseConfigs_array[$url_masterFileName])) {
                if (stripos($this->url_fileName, '_') !== false && count($oneBaseConfigs_array[$url_masterFileName]) > 0) {
                    //获取动作页面配置,并将主页面配置中的数据库信息附加上
                    $action_array = $oneBaseConfigs_array[$url_masterFileName];
                    //dump($action_array);
                    foreach ($action_array as $oneActionConfigs) {
                        $oneActionConfigsArray = explode(',', $oneActionConfigs);
                        $basicConfig_fileName = $oneActionConfigsArray[0];
                        //如果配置文件中的文件名称 与 当前文件名称 一样 则获取他的相关信息
                        if ($basicConfig_fileName == $this->url_fileName) {
                            $this->basicConfig_Name = $oneActionConfigsArray[1];
                            $this->basicConfig_isdepcheck = $oneActionConfigsArray[2];//是否检查部门数据 1不检查, 0或空检查
                            //dump($this->basicConfig_isdepcheck);
                            break;
                        }
                    }
                }
            } else {
                $this->saveConfigError();
            }
        } else {
            $this->saveConfigError();
        }
    }


    /**
     *读取不到配置信息时,将错误写入系统日志
     */
    function saveConfigError()
    {
        //SaveErrorToFile("未找到 目录:$this->url_dirName 文件: $this->url_fileName 的配置信息");//保存出错信息
    }

    /**
     * 要显示的部门数据的范围,存入公共,供SQL.class.php使用
     * 如果基本配置中 部门数据字段 不为空---------------获取可以管理的数据
     * 返回可以管理的部门ID的 查询语句
     * 一般是主页面显示数据列表时使用
     */
    function displayDepDataToSqlClassGLOBAL()
    {
        global $ROLE_WHERE_SQL;    //部门限制使用的查询数据,返回SQL语句,在SQL类DEDESQL.CLASS.PHP中固化
        global $ROLE_DATABASE;    //部门限制使用的查询数据时，同时要加上数据表名称，在SQL类DEDESQL.CLASS.PHP中固化
        global $ROLE_WHERE_IN_DEP_ID_STR;    //部门限制使用的查询数据,返回str字符串,未固化,在helper公共代码 中使用
        $ROLE_WHERE_SQL = $ROLE_WHERE_IN_DEP_ID_STR = "";  //初始化

        //如果部门数据不为空
        if ($this->basicConfig_depName != "" && $this->basicConfig_isdepcheck == "") {
            $ROLE_DATABASE = $this->basicConfig_dataBaseName;//如果有部门ID,则这里存储部门的可查询数据
            $fieldDepname = $this->basicConfig_depName;
            if (stripos($this->basicConfig_depName, "(") !== false) {
                //如果有联查数据表的
                //did(device|id|depid)
                $nowDataBaseName = $this->basicConfig_dataBaseName;  //源数据表名称
                $temp_array = explode("(", $this->basicConfig_depName);
                $nowDataBaseDepId = $temp_array[0];
                $join_array = explode("|", $temp_array[1]);
                $joinDataBaseName = $join_array[0];
                $joinDataBaseId = $join_array[1];
                $fieldDepname = "#@__$joinDataBaseName.".rtrim($join_array[2], ")");//171009增加附加表的名称,用于区分筛选字段,否则容易引起字段名称重复
                //$fieldDepname .= "#@__{$nowDataBaseName}.$fieldDepname";

                $join_sql = $this->getDepRole($fieldDepname);


                $leftjoinSQL=" INNER JOIN #@__$joinDataBaseName ON #@__$joinDataBaseName.$joinDataBaseId=#@__$nowDataBaseName.$nowDataBaseDepId ";

               // $ROLE_WHERE_SQL = " and #@__$nowDataBaseName.$nowDataBaseDepId in (select $joinDataBaseId FROM #@__$joinDataBaseName WHERE 1=1 $join_sql)";
                  $ROLE_WHERE_SQL = $leftjoinSQL." | $join_sql";//170525优化 增加LEFT JOIN原如果直接WHERE IN(这里)字段多的话速度会很慢

                $prefix = "#@__";
                $ROLE_WHERE_SQL = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $ROLE_WHERE_SQL);
            } else {
                $ROLE_WHERE_SQL = $this->getDepRole($fieldDepname);//如果有部门ID,则这里存储部门的可查询数据
                $ROLE_WHERE_IN_DEP_ID_STR = $this->getDepRole();
            }
        }

        //如果部门字段为空,用户字段不为空时,才生成按用户字段查询的语句,(如果只判断用户字段不为空,就生成的话,数据会重复)
        //if (($this->basicConfig_userName != "" && $this->basicConfig_depName == "") && $this->basicConfig_isdepcheck == "") {
        //170712权限判断优化,当部门权限和操作员权限同时设定时,两个权限规则可以同时起作用
        if (($this->basicConfig_userName != ""  ) && $this->basicConfig_isdepcheck == "") {
            if ($ROLE_WHERE_IN_DEP_ID_STR == "") {
                $ROLE_WHERE_IN_DEP_ID_STR_temp = $this->getDepRole();
            } else {
                $ROLE_WHERE_IN_DEP_ID_STR_temp = $ROLE_WHERE_IN_DEP_ID_STR;
            }
            //dump($ROLE_WHERE_IN_DEP_ID_STR_temp);
            if ($ROLE_WHERE_IN_DEP_ID_STR_temp != "") {
                $ROLE_DATABASE = $this->basicConfig_dataBaseName;//如果有部门ID,则这里存储部门的可查询数据
                $ROLE_WHERE_IN_DEP_ID_STR = $userids = GetDepAllUserId($ROLE_WHERE_IN_DEP_ID_STR_temp);
                //如果有用户id,则存储可管理的用户
                if ($userids != "" && $userids != 0) {
                    $userids = $userids . "," . $GLOBALS['CUSERLOGIN']->getUserId();
                    //获取可管理的部门的 所有员工的登录ID(如果用户调动了部门 则显示新的部门所含的用户和自己的) ???此句未删除重复的,加上当前登录用户ID后,可能有重复的值
                } else {
                    $userids = $GLOBALS['CUSERLOGIN']->getUserId();
                }
                //161016修改BUG 原$userids=0时出错
                //170712权限判断优化,当部门权限和操作员权限同时设定时,两个权限规则可以同时起作用
                if($this->basicConfig_depName == ""){
                    $ROLE_WHERE_SQL .= " AND " . $this->basicConfig_userName . " in ( $userids )";
                }else{
                    $ROLE_WHERE_SQL .= " OR " . $this->basicConfig_userName . " in ( $userids )";
                }
            }
        }
        //dump($ROLE_WHERE_SQL);
    }


    /**
     * 判断当前用户地址中是否超级管理员
     *
     */
    function checkAdminRole()
    {
        if (preg_match('/admin_AllowAll/i', $this->web_role)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取当前连接地址 的,所有父分类
     *
     */
    function getParentUrl()
    {
        $forCheckRole_dirAndFileName = $this->url_dirName . "/" . $this->url_fileName;  //用于检查权限的功能文件地址  默认是不带参数的
        $this->parentUrlArray = array();

        $childParameter = $this->url_parameter;
        $childParameterVlaue = "";


        //重新判断 配置的子分类字段名称 原来是单一下的字段名称 160729增加了,多级表联查
        //如果包含多级表连接,则分割
        if (stripos($this->basicConfig_childName, "(") !== false) {
            //did(device|id|depid)
            $nowDataBaseFeldName = "";  //与关联表 关联的源数据表的ID
            $nowDataBaseName = $this->basicConfig_dataBaseName;  //源数据表名称
            $joinDataBaseName = "";  //关联判断的数据表名称
            $joinDataBaseId = "";  //关联表的ID
            $joinFieldname = "";  //要取出供使用的目标 子分类字段名称

            $temp_array = explode("(", $this->basicConfig_childName);
            $nowDataBaseFeldName = $temp_array[0];
            $join_array = explode("|", $temp_array[1]);
            $joinDataBaseName = $join_array[0];
            $joinDataBaseId = $join_array[1];
            $joinFieldname = rtrim($join_array[2], ")");
        }

        //$this->basicConfig_childName  配置文件中定义的 子分类字段名称
        //$childParameter传递过来的参数名称
        //dump($basicConfig_childName);
        //dump($childParameter);
        if (stripos($this->basicConfig_childName, "(") === false) {
            //不包含多级表连接
            preg_match_all("#" . $this->basicConfig_childName . "=[0-9]*#", $childParameter, $matchs);//获取子分类的 值
        } else {
            //包含多级表连接
            preg_match_all("#" . $joinFieldname . "=[0-9]*#", $childParameter, $matchs);//获取子分类的 值
            // dump($matchs[0][0]);
        }

        if (isset($matchs[0][0])) {
            //如果获得了子分类的值,则获取所有的上级地址
            //主页面打开时这里判断
            $childParameter = $matchs[0][0];
            $childParameterVlaue = str_replace($this->basicConfig_childName . "=", "", $childParameter);//获取参数的值
        } else {
            //动作页面连接判断
            //如果当前的参数没有子分类的值,则判断是否传过来数据的ID值,从数据中读取子分类的值
            preg_match_all("#" . $this->basicConfig_idName . "=[0-9]*#", $this->url_parameter, $matchs1);
            if (isset($matchs1[0][0])) {
                $idParameter = $matchs1[0][0];
                $parameterValue = str_replace($this->basicConfig_idName . "=", "", $idParameter);//获取参数的值
                //dump ($idParameter ) ;
                // dump ( $parameterValue) ;
                $selectfieldName_temp = "";//要获取值的 子段名称
                if (stripos($this->basicConfig_childName, "(") === false) {
                    //如果当前地址有了分类的选项,获取所有父分类的连接
                    $selectfieldName_temp = $this->basicConfig_childName;
                    $sql = "SELECT {$selectfieldName_temp},'1' as noroleordernumb FROM #@__$this->basicConfig_dataBaseName WHERE $this->basicConfig_idName='$parameterValue'";//默认配置中的子分类字段名称,不带关联表的查询方式
                    $depInfo = $this->dsql->getone($sql);
                    //dump("role662" . $sql);//这里要能跨接别的表,在参数配置中设定
                    if ($depInfo != "") {
                        $childParameterVlaue = $depInfo[$this->basicConfig_childName];
                        $childParameter = $this->basicConfig_childName . "=" . $childParameterVlaue;
                    }
                } else {
                    //有关联表的查询方式
                    //如果包含(,则使用第二种方式判断
                    $selectfieldName_temp = $joinFieldname;
                    //170525权限这里的 在DWTSQL.CLASS.PHP中不再检查权限

                    $sql = "SELECT $selectfieldName_temp,'1' as noroleordernumb
                                FROM #@__$nowDataBaseName $nowDataBaseName
                                INNER JOIN #@__$joinDataBaseName $joinDataBaseName on $joinDataBaseName.$joinDataBaseId=$nowDataBaseName.$nowDataBaseFeldName
                                WHERE $nowDataBaseName.$nowDataBaseFeldName=(SELECT $nowDataBaseFeldName FROM #@__$nowDataBaseName WHERE $this->basicConfig_idName='$parameterValue')";
                }
                $depInfo = $this->dsql->getone($sql);
                //dump("role662".$sql);//这里要能跨接别的表,在参数配置中设定
                if ($depInfo != "") {
                    $childParameterVlaue = $depInfo[$selectfieldName_temp];
                    $childParameter = $selectfieldName_temp . "=" . $childParameterVlaue;
                }
            }
        }
        // dump ($this->basicConfig_childName) ;
        // dump ( $childParameterVlaue ) ;

        if ($childParameter != "" && $childParameterVlaue != "") {
            //如果获得了子分类的值,则获取所有的上级地址

            //如果有子分类,则将地址加上子分类参数
            $this->parentUrlArray = array();//所有当前地址的上级地址
            $this->parentUrlArray[0] = $forCheckRole_dirAndFileName . "?" . $childParameter;
            if (UrlAddFileExists($this->url_dirName . "/catalog.inc.class.php"))   // urladdfileexists只用相对地址
            {
                //dump ($childParameter) ;
                //dump ($childParameterVlaue);
                require_once(DWTPATH . "/" . $this->url_dirName . "/catalog.inc.class.php");
                $classname = $this->url_dirName . "CatalogInc";
                $newClassName = $this->url_dirName . "ClI";
                $$newClassName = new $classname();
                $reidArray = $$newClassName->GetAllParentUrlToRole($childParameterVlaue);
                //获取所有的父分类地址
                if (is_array($reidArray)) {
                    for ($reidi = 0; $reidi < count($reidArray); $reidi++) {
                        $nowcid = $reidArray[$reidi];
                        if (stripos($this->basicConfig_childName, "(") === false) {
                            //没有多表关联
                            //替换掉网址中的CID
                            $this->parentUrlArray[] = preg_replace("#" . $this->basicConfig_childName . "=[0-9]*#", $this->basicConfig_childName . "=" . $nowcid, $this->parentUrlArray[0]);
                        } else {
                            $this->parentUrlArray[] = preg_replace("#" . $nowDataBaseFeldName . "=[0-9]*#", $nowDataBaseFeldName . "=" . $nowcid, $this->parentUrlArray[0]);
                        }
                    }
                }
            }
            //170313添加 给所有子分类地址加上 不带子分类的,管理员权限没有带分类地址 用这个来判断
            $this->parentUrlArray[] = preg_replace("#\?" . $this->basicConfig_childName . "=[0-9]*#", "", $this->parentUrlArray[0]);

        }
    }


    /**
     * 获取当前功能的名称
     *
     */
    function getFunName()
    {
        //?????160316此处有问题,如果分类的带参数地址 ,要获取当前页面的分类参数,主页面直接获取,动作页面要判断后获取
        $dirAndFileName = $this->url_dirName . "/" . $this->url_fileName;
        $sql = "SELECT title FROM #@__sys_function WHERE urladd='$dirAndFileName'";
        $sysFunInfo = $this->dsql->getone($sql);
        if ($sysFunInfo == "") {
            if ($this->basicConfig_Name != "") {
                $this->funName = $this->basicConfig_Name;  //配置文件中的功能名称
            } else {
                $this->funName = "跳转页面或未设置标题";
            }
        } else {
            $this->funName = $sysFunInfo['title'];
        }
    }


    /**
     *获取当前登录用户的权限组信息
     */
    function getUserWebRole()
    {
        //--------------------------获取用户权限
        $this->dep_role_more_array = array();
        $this->web_role_more_array = array();
        $web_role = "";//用户的权限值  从数据库中获取
        $usertype_array = explode(',', $GLOBALS['CUSERLOGIN']->getUserType());
        foreach ($usertype_array as $usertype) {
            //直接从数据 库获取 权限内容
            $sql = "SELECT web_role,department_role FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='$usertype'";
            $groupSet = $this->dsql->GetOne($sql);
            if (is_array($groupSet)) {
                $web_role .= $groupSet['web_role'] . "|";
                array_push($this->dep_role_more_array, $groupSet['department_role']);
                array_push($this->web_role_more_array, $groupSet['web_role']);
            }
        }
        // $web_role="dep_DepAll|";
        $this->web_role = rtrim($web_role, "|");
        $this->web_role_array = explode('|', $web_role);


        //160422如果是部门管理员,则获取当前部门的所有可用功能文件地址
        if ($GLOBALS['GLOBAMOREDEP'] && preg_match('/dep_DepAll/i', $this->web_role)) {//如果包含多部门 并且当前登录的不是超级管理员 则只获取当前公司的功能
            //$query = "SELECT group_concat(urladd) as urladds FROM `#@__sys_function` f  WHERE 1=1    and  FIND_IN_SET(f.id,(SELECT p.functionids FROM `#@__emp_dep_plus` p  WHERE  p.depid='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "'))  and topid!=0     ORDER BY   	groups asc,topid asc,disorder ASC";
            //160606修改，多部门功能文件菜单获取
            //$query = "SELECT p.functionNames as urladds FROM `#@__emp_dep_plus` p  WHERE  p.depid='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "'";
            //160821修改 从订单中获取是否有使用权限
            $query = " SELECT GROUP_CONCAT(urladd) as urladds FROM `#@__sys_goods_orderdetails` f  where depid='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "'  and endDate>unix_timestamp()";
            //dump($query);
            $funInfo = $this->dsql->getone($query);
            if ($funInfo != "" && $funInfo["urladds"] != null) {
                $file_url_array = explode(",", $funInfo['urladds']);//获取所有的可以操作的主功能文件
                $action_array = array();
                foreach ($file_url_array as $url) {
                    $url_array = explode("/", $url);
                    $dirname = $url_array[0];   //160429此句在5.2下会出错
                    $filename = $url_array[1]; //160429此句在5.2下会出错
                    //根据主功能文件,获取他的所有动作文件,
                    if (!empty($GLOBALS['baseConfigFunArray'][$dirname][$filename]) && is_array($GLOBALS['baseConfigFunArray'][$dirname][$filename])) {
                        foreach ($GLOBALS['baseConfigFunArray'][$dirname][$filename] as $actionInfo) {
                            $actionInfo_array = explode(",", $actionInfo);
                            $actionUrl = $actionInfo_array[0]; //160429此句在5.2下会出错
                            $action_array[] = $dirname . "/" . $actionUrl;
                        }
                    }
                }

                $newurlArray = array_merge($action_array, $file_url_array);//将动作文件和功能文件合并
                $this->web_role_more_array[0] = implode("|", $newurlArray);//分隔后给了页面权限数组

                //dump(SearchOneArray($GLOBALS['baseConfigFunArray'],,));
                $web_numb = count($newurlArray);


                //获取所有的子部门字符串,并生成与功能文件对应个数的字符串,存入数组待用
                $dep_role_more = GetDepSonIds($GLOBALS['NOWLOGINUSERTOPDEPID']);//获取当前部门下的所有子部门,包含当前
                $dep_role_more_s = "";
                for ($temi = 0; $temi < $web_numb; $temi++) {
                    $dep_role_more_s .= $dep_role_more_s == "" ? $dep_role_more : "|" . $dep_role_more;
                }
                $this->dep_role_more_array[0] = $dep_role_more_s;
                $this->web_role_array = $newurlArray;
            }
        }
        //dump($this->web_role);
        //dump($this->web_role_more_array);
        //dump($this->dep_role_more_array);
    }

    /**
     *
     *
     * 这个要移出类,使用 160318
     *  生成当前登录用户的 部门ID查询SQL
     *
     * @access    public
     *
     * @param     string $FieldName 生成的查询语句的字段名称(如果为空则只输出以逗号分隔的depid,不为空则输出sql)
     *
     * @return    string
     */
    function getDepRole($FieldName = "")
    {

        //?????160316此处有问题,如果分类的带参数地址 ,要获取当前页面的分类参数,主页面直接获取,动作页面要判断后获取
        $funAllName = $this->url_dirName . "/" . $this->url_fileName;
        $return_str = "";
        $dep_role_str = "";


        //无继承权限的,如员工管理,直接获取当前功能页面对应的部门权限的数据

        // dump($this->web_role_more_array);
        //dump($this->dep_role_more_array);
        //dump("745行");
        if (isset($this->web_role_more_array)) {
            foreach ($this->web_role_more_array as $key => $web_role) {
                $web_roles = explode('|', $web_role);
                $funFileNameKey = array_search($funAllName, $web_roles);  //得到索引KEY
                if ($funFileNameKey !== false)     //当用 === 或 !== 进行比较时则不进行类型转换，因为此时类型和数值都要比对(因为key值有可能是0,如果用!=比较的话0也是false)
                {
                    $dep_roles = explode('|', $this->dep_role_more_array[$key]);
                    $dep_role_str .= $dep_roles[$funFileNameKey] . ",";
                }
            }
        }

        //如果有CID,则循环所有CID的上级部门
        //如果上级栏目有,管理权限,则代表当前栏目也有管理权限
        //这一步是为了--当添加的功能包含子栏目,但子栏目的webrole不在权限表中,而菜单列出了子栏目的地址,当用户点击子栏目地址后,这里要循环判断一下
        // dump($this->parentUrlArray);
        if (is_array($this->parentUrlArray)) {
            foreach ($this->parentUrlArray as $parentUrl) {
                foreach ($this->web_role_more_array as $key => $web_role) {
                    $web_roles = explode('|', $web_role);
                    $funFileNameKey = array_search($parentUrl, $web_roles);  //得到索引KEY
                    if ($funFileNameKey !== false)     //当用 === 或 !== 进行比较时则不进行类型转换，因为此时类型和数值都要比对(因为key值有可能是0,如果用!=比较的话0也是false)
                    {
                        // dump($funFileNameKey."=========");
                        $dep_roles = explode('|', $this->dep_role_more_array[$key]);
                        $dep_role_str .= $dep_roles[$funFileNameKey] . ",";
                    }
                }
            }
        }

        // dump($dep_role_str);

        $dep_role_str = rtrim($dep_role_str, ",");//删除右侧多余的逗号
        $dep_role_str = implode(",", array_unique(explode(",", $dep_role_str)));//删除重复的值
        /*160308 "&& $dep_role_str != "0""这一句是为了兼容旧的权限数据,结果是如果权限值是0,则可以显示所有的数据.
        (因为原来的文件如果在旧的配置中设定为不区分"部门数据",则所有的权限值为0.
        新修改的权限判断,如果配置文件有部门字段,则统一要判断部门权限)
        */
        if ($dep_role_str != "") {
            if ($FieldName != "") {
               /*
                * 优化查询速度 好像没有效果
                *  $dep_role_str_array=explode(",",$dep_role_str);
                $temp_str=$or_str="";
                foreach ($dep_role_str_array as $dep_role_str){
                    if(!$temp_str=="")$or_str=" OR ";
                    $temp_str .= " $or_str $FieldName='$dep_role_str' ";
                }
                $return_str = " AND ( $temp_str ) ";
*/


                $return_str = " AND {$FieldName} IN (" . $dep_role_str . ") ";



            } else {
                $return_str = $dep_role_str;
            }
        }
        //dump($return_str."----");
        return $return_str;
    }

}


