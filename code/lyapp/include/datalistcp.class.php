<?php if (!defined('DWTINC')) exit('Request Error!');
/**
 * 动态分页类
 * 说明:数据量不大的数据分页,使得数据分页处理变得更加简单化
 * 使用方法:
 *     $dl = new DataListCP();  //初始化动态列表类
 *     $dl->pageSize = 25;      //设定每页显示记录数（默认25条）
 *     $dl->SetParameter($key,$value);  //设定get字符串的变量
 *     //这两句的顺序不能更换
 *     $dl->SetTemplate($tplfile);      //载入模板
 *     $dl->SetSource($sql);            //设定查询SQL
 *     $dl->Display();                  //显示
 *
 * @version        $Id: datalistcp.class.php 3 17:02 9日
 * @package
 * @copyright
 * @license
 * @link
 */

require_once(DWTINC . '/dwttemplate.class.php');
$lang_pre_page = '上页';
$lang_next_page = '下页';
$lang_index_page = '首页';
$lang_end_page = '末页';
$lang_record_number = '条记录';
$lang_page = '页';
$lang_total = '共';

/**
 * DataListCP
 *
 * @package
 */
class DataListCP
{
    var $dsql;
    var $tpl;
    var $pageNO;
    var $totalPage;  //总页数
    var $totalResult;
    var $pageSize;
    var $getValues;
    var $sourceSql;
    var $isQuery;
    var $queryTime;
    var $nextPageUrl;/*160722下一页连接,用于前台AJAX获取更多内容时使用*/

    /**
     *  兼容PHP4版本
     *
     * @access    private
     *
     * @param     string $tplfile 模板文件
     *
     * @return    void
     */
    function DataListCP($tplfile = '')
    {
        $this->__construct($tplfile);
    }

    /**
     *  用指定的文档ID进行初始化
     *
     * @access    public
     *
     * @param     string $tplfile 模板文件
     *
     */
    function __construct($tplfile = '')
    {
//        if ($GLOBALS['cfg_mysql_type'] == 'mysqli' && function_exists("mysqli_init"))
//        {
//            $dsql = $GLOBALS['dsqli'];
//        } else {
        $dsql = $GLOBALS['dsql'];
//        }
        $this->sourceSql = '';
        $this->pageSize = 5;
        $this->queryTime = 0;
        $this->getValues = Array();
        $this->isQuery = false;
        $this->totalResult = 0;

        $this->totalPage = 0;
        $this->pageNO = 0;
        $this->dsql = $dsql;
        $this->SetVar('ParseEnv', 'datalist');
        $this->tpl = new DwtTemplate();
        if ($tplfile != '') {
            $this->tpl->LoadTemplate($tplfile);
        }


    }

    //设置SQL语句

    function SetVar($k, $v)
    {
        global $_vars;
        if (!isset($_vars[$k])) {
            $_vars[$k] = $v;
        }
    }
    //设置模板
    //如果想要使用模板中指定的pagesize，必须在调用模板后才调用 SetSource($sql)

    function SetSource($sql)
    {
        $this->sourceSql = $sql;
        //dump($sql);
    }
    //设置模板
    //如果想要使用模板中指定的pagesize，必须在调用模板后才调用 SetSource($sql)
    function SetTemplate($tplfile)
    {
        $this->tpl->LoadTemplate($tplfile);
    }

    function SetTemplet($tplfile)
    {
        $this->tpl->LoadTemplate($tplfile);
    }

    //设置网址的Get参数键值

    function SetParameter($key, $value)
    {
        $this->getValues[$key] = $value;
    }

    //设置/获取文档相关的各种变量

    function GetVar($k)
    {
        global $_vars;
        return isset($_vars[$k]) ? $_vars[$k] : '';
    }

    //获取当前页数据列表
    function GetArcList($atts, $refObj = '', $fields = array())
    {
        $rsArray = array();
        $t1 = Exectime();
        if (!$this->isQuery) $this->dsql->Execute('dlist', $this->sourceSql);

        $i = 0;
        while ($arr = $this->dsql->GetArray('dlist')) {
            $i++;
            //dump($this->pageNO . "页");
            //dump(">每页" . $this->pageSize . "条");
            $arr['autoindex'] = $i + (($this->pageNO - 1) * $this->pageSize);  //151229增加 页面获取自动自增长编号  引用代码:{dwt:field.autoindex/}  这个所有页递增

            $rsArray[$i] = $arr;
            //dump ($arr);
            if ($i >= $this->pageSize) {
                break;
            }
        }
        $this->dsql->FreeResult('dlist');
        $this->queryTime = (Exectime() - $t1);
        return $rsArray;
    }

    //获取当前页数据列表

    function GetPageList($atts, $refObj = '', $fields = array())
    {
        global $lang_pre_page, $lang_next_page, $lang_index_page, $lang_end_page, $lang_record_number, $lang_page, $lang_total;
        //dump($atts);


        //140425添中这一段   如果页数少于10页的话,得不到这些字符
        $lang_pre_page = '上页';
        $lang_next_page = '下页';
        $lang_index_page = '首页';
        $lang_end_page = '末页';
        $lang_record_number = '条记录';
        $lang_page = '页';
        $lang_total = '共';


        $prepage = $nextpage = $geturl = $hidenform = '';
        $purl = $this->GetCurUrl();
        $prepagenum = $this->pageNO - 1;
        $nextpagenum = $this->pageNO + 1;
        if (!isset($atts['listsize']) || preg_match("#[^0-9]#", $atts['listsize'])) {
            $atts['listsize'] = 5;
        }
        if (!isset($atts['listitem'])) {
            $atts['listitem'] = "info,index,end,pre,next,pageno,form";
        }

        $totalpage = ceil($this->totalResult / $this->pageSize);
        //无结果或只有一页的情况
        if ($totalpage <= 1 && $this->totalResult > 0) {
            //return "共1页\r\n";
            return "";
        }
        if ($this->totalResult == 0) {
            return " 暂无内容 ";
        }
        $infos = " 第" . $this->pageNO . "页  ";

        if ($this->totalResult != 0) {
            $this->getValues['totalresult'] = $this->totalResult;
        }
//dump($geturl);
        if (count($this->getValues) > 0) {
            foreach ($this->getValues as $key => $value) {
                $value = urlencode($value);
                $geturl .= "$key=$value" . "&";
                $hidenform .= "<input type='hidden' name='$key' value='$value' />\n";
            }
        }
        $purl .= "?" . $geturl;
        //获得上一页和下一页的链接
        if ($this->pageNO != 1) {
            $prepage .= "<a  href='" . $purl . "pageno=$prepagenum' class='btn btn-white  btn-sm'>上一页</a></li> \n";
            $indexpage = "<a  href='" . $purl . "pageno=1' class='btn btn-white  btn-sm'>首页</a> \n";
        } else {
            $indexpage = "";
        }
        if ($this->pageNO != $totalpage && $totalpage > 1) {
            $nextpage .= " <a href='" . $purl . "pageno=$nextpagenum' class='btn btn-white  btn-sm'>下一页</a></li> \n";
            $endpage = "<a  href='" . $purl . "pageno=$totalpage' class='btn btn-white  btn-sm'>尾页</a> \n";
        } else {

            $endpage = "";
        }


        $plist = " <div style='height:35px;line-height:35px;overflow:hidden;' >";
        if (preg_match("#info#i", $atts['listitem'])) {
            $plist .= $infos;
        }
        if (preg_match("#index#i", $atts['listitem'])) {
            $plist .= "<div class='btn-group pull-right'>" . $indexpage;
        }
        if (preg_match("#pre#i", $atts['listitem'])) {
            $plist .= $prepage;
        }

        if (preg_match("#next#i", $atts['listitem'])) {
            $plist .= $nextpage;
        }
        if (preg_match("#end#i", $atts['listitem'])) {
            $plist .= $endpage . "</div>";
        }

        $plist .= "</div>";
        return $plist;
    }

    //获得当前网址
    function GetCurUrl()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $nowurl = $_SERVER["REQUEST_URI"];
            $nowurls = explode("?", $nowurl);
            $nowurl = $nowurls[0];
        } else {
            $nowurl = $_SERVER["PHP_SELF"];
        }
        //dump($nowurl);
        return $nowurl;
    }

    //关闭
    function Close()
    {

    }

    //显示数据
    function Display()
    {
        $this->PreLoad();

        //在PHP4中，对象引用必须放在display之前，放在其它位置中无效
        $this->tpl->SetObject($this);
        $this->tpl->Display();
    }

    //显示数据

    /**
     *  对config参数及get参数等进行预处理
     *
     * @access    public
     * @return    void
     */
    function PreLoad()
    {
        global $totalresult, $pageno;
        if (empty($pageno) || preg_match("#[^0-9]#", $pageno)) {
            $pageno = 1;
        }
        if (empty($totalresult) || preg_match("#[^0-9]#", $totalresult)) {
            $totalresult = 0;
        }

        $this->pageNO = $pageno;
        $this->totalResult = $totalresult;
        if (isset($this->tpl->tpCfgs['pagesize'])) {
            $this->pageSize = $this->tpl->tpCfgs['pagesize'];
        }
        $this->totalPage = ceil($this->totalResult / $this->pageSize);
        //dump ($this->totalResult);
        if ($this->totalResult == 0) {


            /* $this->dsql->SetQuery($countQuery);
            $this->dsql->Execute();
            $dd = $this->dsql->GetTotalRow();
            //dump($countQuery);
            $this->totalResult = $dd>0 ? $dd : 0;//151209修复BUG,原为isset($dd)如果SQL查询错误,返回DD为-1则引起显示-0的结果
            $this->sourceSql .= " LIMIT 0," . $this->pageSize;
            //dump($this->totalResult."kkkk");*/


            //$this->dsql->SetQuery($this->sourceSql);//先去SQL文件中  经过权限处理,返回加了权限
            // $role_sql=$this->dsql->queryString;

            // dump($role_sql);
            //然后,不加权限的进行数量统计
            //171026只匹配并替换SQL中最左面出现的 SELECT和FROM中间的内容,其他的不替换
            //$countQuery ="SELECT COUNT(*) AS total_count ,'1' as noroleordernumb FROM ({$role_sql}) AS SQLBYROLE";//这个外加的COUNT速度慢

            //下面这个不能用,如果order by里有别名,或者SQL里有GROUP BY就不能用了
            //$countQuery = preg_replace("#SELECT[ \r\n\t](.*?)[ \r\n\t]FROM#is", "SELECT COUNT(*) AS total_count ,'1' as noroleordernumb FROM", $role_sql,1);
            // $countQuery = preg_replace("#ORDER[ \r\n\t]{1,}BY(.*)#is", '', $countQuery,1);//这个未做好,如果SQL中有两个order by 会把第一个orderby以后的所有清除掉171026


            $countQuery = $this->sourceSql;
            //dump($countQuery)."9999999";

            // dump($role_sql);
            // $countQuery=$role_sql;//默认的
            $group_index = stripos($countQuery, "group ");//查找从左边开始第一次出现位置 如果包含group by
            if ($group_index > 0) {
                //如果包含group by则用外加COUNT
                $countQuery = "SELECT COUNT(*) AS total_count ,'1' as noroleordernumb FROM ({$countQuery}) AS SQLBYROLE";//这个外加的COUNT速度慢
            } else {
                //如果不含GROUP by 则用内加
                //171102SQL语句替换
                $strlen = strlen($countQuery);
                $from_index = stripos($countQuery, "from");//查找从左边开始第一次出现位置   from必有空格 避免出现字段名称是from的
                //dump($from_index);
                //dump($strlen);
                if ($from_index > 0) $countQuery = " SELECT COUNT(*) AS total_count ,'1' as noroleordernumb  " . substr($countQuery, $from_index, $strlen);//将select 和from中的替换
                //dump($countQuery);
                $order_index = strripos($countQuery, "order by");//查找从右边开始第一次出现的位置
                //dump($order_index);
                if ($order_index > 0) $countQuery = substr($countQuery, 0, $order_index);//删除ORDER BY ORDER 必有空格 避免出现字段名称是ORDER 的
                //dump($countQuery);


                //dump($countQuery);
            }
            $row = $this->dsql->GetOne($countQuery);
            if (!is_array($row)) $row['total_count'] = 0;
            $this->totalResult = $row['total_count'] > 0 ? $row['total_count'] : 0;//151209修复BUG,原为isset($dd)如果SQL查询错误,返回DD为-1则引起显示-0的结果
            $this->sourceSql .= " LIMIT 0," . $this->pageSize;
            //dump($countQuery);

        } else {
            $this->sourceSql .= " LIMIT " . (($this->pageNO - 1) * $this->pageSize) . "," . $this->pageSize;
            //dump($this->sourceSql);
        }
        /*160722当前页面连接,用于前台AJAX获取更多内容时使用*/
        $totalpage = ceil($this->totalResult / $this->pageSize);
        $purl = $this->GetCurUrl();
        if ($this->pageNO != $totalpage && $totalpage > 1) {
            $geturl = "";
            if (count($this->getValues) > 0) {
                foreach ($this->getValues as $key => $value) {
                    $value = urlencode($value);
                    if ($key == "dopost") $value = "ajax";
                    $geturl .= "$key=$value" . "&";
                }
            }
            $purl .= "?" . $geturl;

            //$nextpagenum = $this->pageNO + 1;
            //$this->nextPageUrl = $purl . "pageno=$nextpagenum";
            $this->nextPageUrl = $purl . "totalresult=" . $this->totalResult;
        }
        /*160722当前页面连接,用于前台AJAX获取更多内容时使用*/

    }


}