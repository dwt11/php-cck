<?php if (!defined('DWTINC')) exit('Request Error!');
/**
 * 文档列表类
 *
 * @version        $Id: arc.listview.class.php 2 15:15 7日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('archives.partview.class.php');

helper('cache');
@set_time_limit(0);

/**
 * 自由列表类
 *
 * @package          ListView
 * @subpackage
 * @link
 */
class ListView
{
    var $dsql;
    var $dtp;
    var $dtp2;
    var $TypeID;
    var $TypeLink;
    var $PageNo;
    var $TotalPage;
    var $TotalResult;
    var $PageSize;
    var $ChannelUnit;
    var $ListType;
    var $Fields;
    var $PartView;
    var $upPageType;
    var $addSql;
    var $IsError;
    var $IsReplace;
    var $nextPageUrl;/*160722下一页连接,用于前台AJAX获取更多内容时使用*/

    /**
     *  php5构造函数
     *
     * @access    public
     *
     * @param     int $typeid 栏目ID
     * @param     int $uppage 上一页
     */
    function __construct($typeid, $uppage = 1)
    {


        global $dsql;
        $this->TypeID = $typeid;
        $this->dsql = &$dsql;
        $this->IsReplace = false;
        $this->IsError = false;
        $this->dtp = new DwtTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("dwt", "{", "}");
        $this->dtp2 = new DwtTagParse();
        $this->dtp2->SetNameSpace("field", "[", "]");
        $this->TypeLink = new TypeLink($typeid);
        $this->upPageType = $uppage;
        $this->TotalResult = is_numeric($this->TotalResult) ? $this->TotalResult : "";

        if (!is_array($this->TypeLink->TypeInfos)) {
            $this->IsError = true;
        }
        if (!$this->IsError) {
            $this->ChannelUnit = new ChannelUnit($this->TypeLink->TypeInfos['channeltype']);
            $this->Fields = $this->TypeLink->TypeInfos;
            $this->Fields['id'] = $typeid;
            $this->Fields['position'] = $this->TypeLink->GetArchivePositionLink(true);
            $this->Fields['title'] = preg_replace("/[<>]/", " / ", $this->TypeLink->GetArchivePositionLink(false));


            //设置环境变量
            SetSysEnv($this->TypeID, $this->Fields['typename'], 0, '', 'arclist');
            $this->Fields['typeid'] = $this->TypeID;


        }//!error
    }

    //php4构造函数
    function ListView($typeid, $uppage = 0)
    {
        $this->__construct($typeid, $uppage);
    }

    //关闭相关资源
    function Close()
    {

    }

    /**
     *  统计列表里的记录
     *
     * @access    public
     *
     * @param     string
     *
     * @return    string
     */
    function CountRecord()
    {

        //统计数据库记录
        $this->TotalResult = -1;
        if (isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if (isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;
        $this->addSql = " arc.issend > -1 ";


        //上级列表包含子类列表
        $sonids = GetArchiveSonIds($this->TypeID, $this->Fields['channeltype']);
        if (!preg_match("/,/", $sonids)) {
            $sonidsCon = " arc.typeid = '$sonids' ";
        } else {
            $sonidsCon = " arc.typeid IN($sonids) ";
        }
        $this->addSql .= " AND ( $sonidsCon ) ";


        if ($this->TotalResult == -1) {
            $cquery = "SELECT COUNT(*) AS dd FROM `#@__archives_arctiny` arc WHERE " . $this->addSql;
            $row = $this->dsql->GetOne($cquery);
            if (is_array($row)) {
                $this->TotalResult = $row['dd'];
            } else {
                $this->TotalResult = 0;
            }
        }

        //初始化列表模板，并统计页面总数
        $tempfile = "";
        if ($this->TypeLink->TypeInfos['templist'] != "") {
            //161217如果列表页面没有参数  则显示所有的文档，这里加了判断 ，然后下面给了默认的模板地址
            $tempfile = $this->TypeLink->TypeInfos['templist'];
            $tempfile = str_replace("{tid}", $this->TypeID, $tempfile);
            $tempfile = str_replace("{typeid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
        }
        if (!file_exists($tempfile) || !is_file($tempfile)) {
            //echo "模板文件不存在，无法解析文档！";
            //exit();
            $tempfile = "archives_list.htm";
        }

        //dump($GLOBALS['dopost']);
        if(isset($GLOBALS['dopost'])&&$GLOBALS['dopost']=="ajax"){
//如果是下拉的,则使用以下的模板
            $tempfile = str_replace(".htm","_ajax.htm",$tempfile);
        }
        $this->dtp->LoadTemplate($tempfile);
        $ctag = $this->dtp->GetTag("page");
        if (!is_object($ctag)) {
            $ctag = $this->dtp->GetTag("arclist");
        }
        if (!is_object($ctag)) {
            $this->PageSize = 10;
        } else {
            if ($ctag->GetAtt("pagesize") != "") {
                $this->PageSize = $ctag->GetAtt("pagesize");
            } else {
                $this->PageSize = 10;
            }
        }
//dump($this->PageSize);
        $this->TotalPage = ceil($this->TotalResult / $this->PageSize);

        /*170914当前页面连接,用于前台AJAX获取更多内容时使用*/
        $totalpage = $this->TotalPage;
        if ($this->PageSize != $totalpage && $totalpage > 1) {
            $geturl = "dopost=ajax";
            $purl = GetCurUrl();
            /* if (count($this->getValues) > 0) {
                 foreach ($this->getValues as $key => $value) {
                     $value = urlencode($value);
                     if ($key == "dopost") $value = "ajax";
                     $geturl .= "$key=$value" . "&";
                 }
             }*/
           // dump(  $purl);
            //dump(stripos( $purl,"?"));
            if (stripos( $purl,"?") > 0) {
                //如果网址中有参数
                $purl .= "&" . $geturl;
            } else {
                $purl .= "?" . $geturl;

            }

            //$nextpagenum = $this->pageNO + 1;
            //$this->nextPageUrl = $purl . "pageno=$nextpagenum";
            $this->nextPageUrl = $purl . "&totalresult=" . $this->TotalResult;
        }
        /*170914当前页面连接,用于前台AJAX获取更多内容时使用*/

    }


    /**
     *  显示列表
     *
     * @access    public
     * @return    void
     */
    function Display()
    {

        if ($this->TypeLink->TypeInfos['ispart'] > 0) {
            $this->DisplayPartTemplets();
            return;
        }
        $this->CountRecord();
        //dump($this->PageSize);
        if ((empty($this->PageNo) || $this->PageNo == 1)
            && $this->TypeLink->TypeInfos['ispart'] == 1) {
            $tmpdir = $GLOBALS['cfg_web_templets_dir'];
            $tempfile = str_replace("{tid}", $this->TypeID, $this->Fields['tempindex']);
            $tempfile = str_replace("{typeid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);


            $tempfile = $tmpdir . "/" . $tempfile;
            $this->dtp->LoadTemplate($tempfile);
        }
        $this->ParseTempletsFirst();
        $this->ParseDMFields($this->PageNo, 0);
        $this->dtp->Display();
    }


    /**
     *  显示单独模板页面
     *
     * @access    public
     *
     * @param     string
     *
     * @return    string
     */
    function DisplayPartTemplets()
    {
        $this->PartView = new PartView($this->TypeID, false);
        $this->PartView->SetTypeLink($this->TypeLink);

        if ($this->Fields['ispart'] == 1) {
            //封面模板
            $tempfile = str_replace("{tid}", $this->TypeID, $this->Fields['tempindex']);
            $tempfile = str_replace("{typeid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
            $tempfile = $tempfile;

            $this->PartView->SetTemplet($tempfile);
        }
        $this->PartView->Display();
    }


    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    public
     * @return    string
     */
    function ParseTempletsFirst()
    {
        if (isset($this->TypeLink->TypeInfos['reid'])) {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }
        $GLOBALS['envs']['typeid'] = $this->TypeID;
        $GLOBALS['envs']['topid'] = GetArchiveTopid($this->Fields['typeid']);

        MakeOneTag($this->dtp, $this);
    }

    /**
     *  解析模板，对内容里的变动进行赋值
     *
     * @access    public
     *
     * @param     int $PageNo 页数
     * @param     int $ismake 是否编译
     *
     * @return    string
     */
    function ParseDMFields($PageNo, $ismake = 1)
    {
        //替换第二页后的内容
        if (($PageNo > 1 || strlen($this->Fields['content']) < 10) && !$this->IsReplace) {
            $this->dtp->SourceString = str_replace('[cmsreplace]', 'display:none', $this->dtp->SourceString);
            $this->IsReplace = true;
        }
        foreach ($this->dtp->CTags as $tagid => $ctag) {
            if ($ctag->GetName() == "arclist") {
                $limitstart = ($this->PageNo - 1) * $this->PageSize;
                $row = $this->PageSize;
                if (trim($ctag->GetInnerText()) == "") {
                    $InnerText = GetSysTemplets("list_fulllist.htm");
                } else {
                    $InnerText = trim($ctag->GetInnerText());
                }
                $this->dtp->Assign($tagid,
                    $this->GetArcList(
                        $limitstart,
                        $row,
                        $ctag->GetAtt("col"),
                        $ctag->GetAtt("titlelen"),
                        $ctag->GetAtt("infolen"),
                        $ctag->GetAtt("imgwidth"),
                        $ctag->GetAtt("imgheight"),
                        $ctag->GetAtt("listtype"),
                        $ctag->GetAtt("orderby"),
                        $InnerText,
                        $ctag->GetAtt("tablewidth"),
                        $ismake,
                        $ctag->GetAtt("orderway")
                    )
                );
            } else if ($ctag->GetName() == "pagelist") {
                $list_len = trim($ctag->GetAtt("listsize"));
                $ctag->GetAtt("listitem") == "" ? $listitem = "index,pre,pageno,next,end,option" : $listitem = $ctag->GetAtt("listitem");
                if ($list_len == "") {
                    $list_len = 3;
                }
                if ($ismake == 0) {
                    $this->dtp->Assign($tagid, $this->GetPageListDM($list_len, $listitem));
                } else {
                    $this->dtp->Assign($tagid, $this->GetPageListST($list_len, $listitem));
                }
            } else if ($PageNo != 1 && $ctag->GetName() == 'field' && $ctag->GetAtt('display') != '') {
                $this->dtp->Assign($tagid, '');
            }
        }
    }


    /**
     *  获得一个单列的文档列表
     *
     * @access    public
     *
     * @param     int    $limitstart 限制开始
     * @param     int    $row        行数
     * @param     int    $col        列数
     * @param     int    $titlelen   标题长度
     * @param     int    $infolen    描述长度
     * @param     int    $imgwidth   图片宽度
     * @param     int    $imgheight  图片高度
     * @param     string $listtype   列表类型
     * @param     string $orderby    排列顺序
     * @param     string $innertext  底层模板
     * @param     string $tablewidth 表格宽度
     * @param     string $ismake     是否编译
     * @param     string $orderWay   排序方式
     *
     * @return    string
     */
    function GetArcList($limitstart = 0, $row = 10, $col = 1, $titlelen = 30, $infolen = 250,
                        $imgwidth = 120, $imgheight = 90, $listtype = "all", $orderby = "default", $innertext = "", $tablewidth = "100", $ismake = 1, $orderWay = 'desc')
    {
        global $cfg_list_son;

        $typeid = $this->TypeID;

        if ($row == '') $row = 10;
        if ($limitstart == '') $limitstart = 0;
        if ($titlelen == '') $titlelen = 100;
        if ($infolen == '') $infolen = 250;
        if ($imgwidth == '') $imgwidth = 120;
        if ($imgheight == '') $imgheight = 120;
        if ($listtype == '') $listtype = 'all';
        if ($orderWay == '') $orderWay = 'desc';

        if ($orderby == '') {
            $orderby = 'default';
        } else {
            $orderby = strtolower($orderby);
        }

        $tablewidth = str_replace('%', '', $tablewidth);
        if ($tablewidth == '') $tablewidth = 100;
        if ($col == '') $col = 1;
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth . '%';
        $colWidth = $colWidth . '%';

        $innertext = trim($innertext);
        if ($innertext == '') {
            $innertext = GetSysTemplets('list_fulllist.htm');
        }

        //排序方式
        $ordersql = '';
        if ($orderby == "senddate" || $orderby == "id") {
            $ordersql = "   ORDER BY   arc.id $orderWay";
        } else if ($orderby == "hot" || $orderby == "click") {
            $ordersql = "   ORDER BY   arc.click $orderWay";
        } else if ($orderby == "lastpost") {
            $ordersql = "    ORDER BY   arc.lastpost $orderWay";
        } else {
            $ordersql = "   ORDER BY   arc.sortrank $orderWay";
        }

        //获得附加表的相关信息
//        $addtable  = $this->ChannelUnit->ChannelInfos['addtable'];
//        if($addtable!="")
//        {
//            $addJoin = " LEFT JOIN `$addtable` ON arc.id = ".$addtable.'.aid ';
//            $addField = '';
//            $fields = explode(',',$this->ChannelUnit->ChannelInfos['listfields']);
//            foreach($fields as $k=>$v)
//            {
//                $nfields[$v] = $k;
//            }
//            if(is_array($this->ChannelUnit->ChannelFields) && !empty($this->ChannelUnit->ChannelFields))
//            {
//                foreach($this->ChannelUnit->ChannelFields as $k=>$arr)
//                {
//                    if(isset($nfields[$k]))
//                    {
//                        if(!empty($arr['rename'])) {
//                            $addField .= ','.$addtable.'.'.$k.' as '.$arr['rename'];
//                        }
//                        else {
//                            $addField .= ','.$addtable.'.'.$k;
//                        }
//                    }
//                }
//            }
//        }
//        else
//        {
//            $addField = '';
//            $addJoin = '';
//        }

        //如果不用默认的sortrank或id排序，使用联合查询（数据量大时非常缓慢）
        if (preg_match('/hot|click|lastpost/', $orderby)) {
            $query = "SELECT arc.*,tp.typename,tp.ispart 
           $addField
           FROM `#@__archives` arc
           LEFT JOIN `#@__archives_type` tp ON arc.typeid=tp.id
           $addJoin
           WHERE {$this->addSql} $ordersql LIMIT $limitstart,$row";
        } //普通情况先从arctiny表查出ID，然后按ID查询（速度非常快）
        else {
            $t1 = ExecTime();
            $ids = array();
            $query = "SELECT id FROM `#@__archives_arctiny` arc WHERE {$this->addSql} $ordersql LIMIT $limitstart,$row ";
            $this->dsql->SetQuery($query);
            $this->dsql->Execute();
            while ($arr = $this->dsql->GetArray()) {
                $ids[] = $arr['id'];
            }
            $idstr = join(',', $ids);
            if ($idstr == '') {
                return '';
            } else {
                $query = "SELECT arc.*,tp.typename,tp.ispart  
                       
                       FROM `#@__archives` arc LEFT JOIN `#@__archives_type` tp ON arc.typeid=tp.id
                      
                       WHERE arc.id in($idstr) $ordersql ";
            }
            $t2 = ExecTime();
            //echo $t2-$t1;

        }
        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $t2 = ExecTime();
//dump($query );
        //echo $t2-$t1;
        $artlist = '';
        $this->dtp2->LoadSource($innertext);
        $GLOBALS['autoindex'] = 0;
        for ($i = 0; $i < $row; $i++) {
            if ($col > 1) {
                $artlist .= "<div>\r\n";
            }
            for ($j = 0; $j < $col; $j++) {
                if ($row = $this->dsql->GetArray("al")) {
                    $GLOBALS['autoindex']++;
                    $row['autoindex'] = $GLOBALS['autoindex'];
                    $ids[$row['id']] = $row['id'];
                    //处理一些特殊字段


                    $row['displayDateNoYear'] = GetDateNoYearMk($row['senddate']);//160512增加显示日期
                    $row['arcurl'] = $GLOBALS['cfg_install_path'] . "/lyapp/archives/archives_view.php?aid=" . $row['id']; //列表中文章的地址
                    if ($row['litpic'] == '-' || $row['litpic'] == '') {
                        $row['litpic'] = $GLOBALS['cfg_install_path'] . '/images/arcNoPic.jpg';
                    }
                    $row['picname'] = $row['litpic'];
                    $row['stime'] = GetDateMK($row['senddate']);
                    $row['typeurl'] = $GLOBALS['cfg_install_path'] . "/lyapp/archives/archives_list.php?tid=" . $row['typeid'];
                    $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['typename'] . "</a>";
                    $row['image'] = "<img src='" . $row['picname'] . "' border='0' width='$imgwidth' height='$imgheight' alt='" . preg_replace("/['><]/", "", $row['title']) . "'>";
                    //$row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
                    $row['fulltitle'] = $row['title'];
                    $row['title'] = cn_substr($row['title'], $titlelen);
                    if ($row['color'] != '') {
                        $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
                    }
                    if (preg_match('/c/', $row['flag'])) {
                        $row['title'] = "<b>" . $row['title'] . "</b>";
                    }


                    //编译附加表里的数据
                    foreach ($row as $k => $v) {
                        $row[strtolower($k)] = $v;
                    }
                    if ($this->ChannelUnit && is_array($this->ChannelUnit->ChannelFields)) {
                        foreach ($this->ChannelUnit->ChannelFields as $k => $arr) {
                            if (isset($row[$k])) {
                                $row[$k] = $this->ChannelUnit->MakeField($k, $row[$k]);
                            }
                        }
                    }
                    if (is_array($this->dtp2->CTags)) {
                        foreach ($this->dtp2->CTags as $k => $ctag) {
                            if ($ctag->GetName() == 'array') {
                                //传递整个数组，在runphp模式中有特殊作用
                                $this->dtp2->Assign($k, $row);
                            } else {
                                if (isset($row[$ctag->GetName()])) {
                                    $this->dtp2->Assign($k, $row[$ctag->GetName()]);
                                } else {
                                    $this->dtp2->Assign($k, '');
                                }
                            }
                        }
                    }
                    $artlist .= $this->dtp2->GetResult();
                }//if hasRow

            }//Loop Col

            if ($col > 1) {
                $i += $col - 1;
                $artlist .= "    </div>\r\n";
            }
        }//Loop Line

        $t3 = ExecTime();

        //echo ($t3-$t2);
        $this->dsql->FreeResult('al');
        return $artlist;
    }

    /**
     *  获取动态的分页列表
     *
     * @access    public
     *
     * @param     string $list_len 列表宽度
     * @param     string $list_len 列表样式
     *
     * @return    string
     */
    function GetPageListDM($list_len, $listitem = "index,end,pre,next,pageno")
    {
        global $cfg_rewrite;
        $prepage = $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if ($list_len == '' || preg_match("/[^0-9]/", $list_len)) {
            $list_len = 3;
        }
        $totalpage = ceil($this->TotalResult / $this->PageSize);
        if ($totalpage <= 1 && $this->TotalResult > 0) {
            return "共1页\r\n";
        }
        if ($this->TotalResult == 0) {
            return " 暂无内容 ";
        }
        $maininfo = " 第" . $this->PageNo . "页 ";

        $purl = $this->GetCurUrl();


        $geturl = "tid=" . $this->TypeID . "&TotalResult=" . $this->TotalResult . "&";
        $purl .= '?' . $geturl;

        $optionlist = '';
        //$hidenform = "<input type='hidden' name='tid' value='".$this->TypeID."'>\r\n";
        //$hidenform .= "<input type='hidden' name='TotalResult' value='".$this->TotalResult."'>\r\n";

        //获得上一页和下一页的链接
        if ($this->PageNo != 1) {
            $prepage .= " <a href='" . $purl . "PageNo=$prepagenum'  class='btn btn-white  btn-sm'>上一页</a> \r\n";
            $indexpage = " <a href='" . $purl . "PageNo=1' class='btn btn-white  btn-sm'>首页</a> \r\n";
        } else {
            $indexpage = "";
        }
        if ($this->PageNo != $totalpage && $totalpage > 1) {
            $nextpage .= " <a href='" . $purl . "PageNo=$nextpagenum' class='btn btn-white  btn-sm'>下一页</a> \r\n";
            $endpage = " <a href='" . $purl . "PageNo=$totalpage' class='btn btn-white  btn-sm'>尾页</a> \r\n";
        } else {
            $endpage = "";
        }


        $plist = " <div style='height:35px;line-height:35px;overflow:hidden;' >";
        if (preg_match('/info/i', $listitem)) $plist .= $maininfo;
        if (preg_match('/index/i', $listitem)) $plist .= "<div class='btn-group pull-right'>" . $indexpage;
        if (preg_match('/pre/i', $listitem)) $plist .= $prepage;
        if (preg_match('/next/i', $listitem)) $plist .= $nextpage;
        if (preg_match('/end/i', $listitem)) $plist .= $endpage . "</div>";

        $plist .= "</div>";
        return $plist;
    }

    /**
     *  获得当前的页面文件的url
     *
     * @access    public
     * @return    string
     */
    function GetCurUrl()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $nowurl = $_SERVER['REQUEST_URI'];
            $nowurls = explode('?', $nowurl);
            $nowurl = $nowurls[0];
        } else {
            $nowurl = $_SERVER['PHP_SELF'];
        }
        return $nowurl;
    }
}//End Class