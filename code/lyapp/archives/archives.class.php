<?php
if (!defined('DWTINC')) exit("Request Error!");
/**
 * 文档类
 *
 * @version        $Id: arc.archives.class.php 4 15:13 7日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../../archives/catalogLinkOption.class.php");
require_once("archives.channelunit.class.php");
require_once(DWTINC . "/downmix.inc.php");

@set_time_limit(0);

/**
 * 主文档类(Archives类)
 *
 * @package          TypeLink
 * @subpackage
 * @link
 */
class Archives
{
    var $TypeLink;
    var $ChannelUnit;
    var $dsql;
    var $Fields;
    var $dtp;
    var $ArcID;
    var $SplitPageField;
    var $SplitFields;
    var $NowPage;
    var $TotalPage;
    var $NameFirst;
    var $ShortName;
    var $FixedValues;
    var $TempSource;
    var $IsError;
    var $SplitTitles;
    var $PreNext;
    var $addTableRow;

    /**
     *  php5构造函数
     *
     * @access    public
     *
     * @param     int $aid 文档ID
     *
     * @return    string
     */
    function __construct($aid)
    {
        global $dsql, $ftp;
        $this->IsError = FALSE;
        $this->ArcID = $aid;
        $this->PreNext = array();

        $this->dsql = $dsql;
        $query = "SELECT channelid,typeid FROM `#@__archives_arctiny` WHERE id='$aid' ";
        $arr = $this->dsql->GetOne($query);
        if (!is_array($arr)) {
            $this->IsError = TRUE;
        } else {

            if ($arr['channelid'] == 0) $arr['channelid'] = 1;
            $this->ChannelUnit = new ChannelUnit($arr['channelid'], $aid);
            $this->TypeLink = new TypeLink($arr['typeid']);

            $query = "SELECT arc.*,tp.reid,ch.addtable
			          FROM `#@__archives` arc
					 LEFT JOIN #@__archives_type tp on tp.id=arc.typeid
					  LEFT JOIN #@__archives_channeltype as ch on arc.channelid = ch.id
					  WHERE arc.id='$aid' ";
            $this->Fields = $this->dsql->GetOne($query);
            $this->dtp = new DwtTagParse();
            $this->dtp->SetRefObj($this);
            $this->SplitPageField = $this->ChannelUnit->SplitPageField;
            $this->SplitFields = '';
            $this->TotalPage = 1;
            $this->NameFirst = '';
            $this->ShortName = 'html';
            $this->FixedValues = '';
            $this->TempSource = '';
            if (empty($GLOBALS['pageno'])) {
                $this->NowPage = 1;
            } else {
                $this->NowPage = $GLOBALS['pageno'];
            }

            //特殊的字段数据处理
            $this->Fields['aid'] = $aid;
            $this->Fields['id'] = $aid;
            $this->Fields['position'] = $this->TypeLink->GetArchivePositionLink(TRUE);
            $this->Fields['typeid'] = $arr['typeid'];
            //dump($this->Fields['position']);

            //设置一些全局参数的值
            foreach ($GLOBALS['PubFields'] as $k => $v) {
                $this->Fields[$k] = $v;
            }


            //为了减少重复查询，这里直接把附加表查询记录放在 $this->addTableRow 中，在 ParAddTable() 不再查询
            if ($this->ChannelUnit->ChannelInfos['addtable'] != '') {
                $query = "SELECT * FROM `{$this->ChannelUnit->ChannelInfos['addtable']}` WHERE `archivesid` = '$aid'";
                $this->addTableRow = $this->dsql->GetOne($query);
            }

        }//!error
    }

    //php4构造函数
    function Archives($aid)
    {
        $this->__construct($aid);
    }

    /**
     *  解析附加表的内容
     *
     * @access    public
     * @return    void
     */
    function ParAddTable()
    {
        //读取附加表信息，并把附加表的资料经过编译处理后导入到$this->Fields中，以方便在模板中用 {dwt:field name='fieldname' /} 标记统一调用
        if ($this->ChannelUnit->ChannelInfos['addtable'] != '') {
            $row = $this->addTableRow;
            if (is_array($row)) {
                foreach ($row as $k => $v) $row[strtolower($k)] = $v;
            }
            if (is_array($this->ChannelUnit->ChannelFields) && !empty($this->ChannelUnit->ChannelFields)) {
                foreach ($this->ChannelUnit->ChannelFields as $k => $arr) {
                    if (isset($row[$k])) {
                        if (!empty($arr['rename'])) {
                            $nk = $arr['rename'];
                        } else {
                            $nk = $k;
                        }
                        $cobj = $this->GetCurTag($k);
                        if (is_object($cobj)) {
                            foreach ($this->dtp->CTags as $ctag) {
                                if ($ctag->GetTagName() == 'field' && $ctag->GetAtt('name') == $k) {
//                                    //带标识的专题节点
//                                    if($ctag->GetAtt('noteid') != '') {
//                                        $this->Fields[$k.'_'.$ctag->GetAtt('noteid')] = $this->ChannelUnit->MakeField($k, $row[$k], $ctag);
//                                    }
                                    //带类型的字段节点
                                    if ($ctag->GetAtt('type') != '') {
                                        $this->Fields[$k . '_' . $ctag->GetAtt('type')] = $this->ChannelUnit->MakeField($k, $row[$k], $ctag);
                                    } //其它字段
                                    else {
                                        $this->Fields[$nk] = $this->ChannelUnit->MakeField($k, $row[$k], $ctag);
                                    }
                                }
                            }
                        } else {
                            $this->Fields[$nk] = $row[$k];
                        }
                        if ($arr['type'] == 'htmltext') {
                            // 为内容里的图片 加上自动大小 点击大图  151106注销掉 使用jquery控制大小
                            //dump($this->Fields[$nk]);
                            //preg_match_all("/\<img[^>]+src=[\'\"]([^\'\"]+)['\"][^>]*\>/i",$this->Fields[$nk],$img_array);   //得到<IMG ....>和地址 两个值

                            preg_match_all("/\<img[^>]*\>/i", $this->Fields[$nk], $img_array);  //只得到<IMG>的值
                            $img_array = array_unique($img_array[0]);
                            if (count($img_array) > 0) {
                                for ($imgi = 0; $imgi < count($img_array); $imgi++) {
                                    $targetImg = str_replace("img", "img style='cursor:pointer' onclick=\"javascript:window.open(this.src);\"", strtolower($img_array[$imgi]));
                                    //dump($targetImg);
                                    $this->Fields[$nk] = str_replace($img_array[$imgi], $targetImg, $this->Fields[$nk]);
                                }
                            }

                        }
                    }
                }//End foreach
            }
            //设置全局环境变量
            $this->Fields['typename'] = $this->TypeLink->TypeInfos['typename'];
            //dump($this->Fields['title']);
            @SetSysEnv($this->Fields['typeid'], $this->Fields['typename'], $this->Fields['id'], $this->Fields['title'], 'archives');
        }
        //完成附加表信息读取
        unset($row);

        //处理要分页显示的字段
        $this->SplitTitles = Array();
        if ($this->SplitPageField != '' && $GLOBALS['cfg_arcsptitle'] = 'Y'
                && isset($this->Fields[$this->SplitPageField])
        ) {
            $this->SplitFields = explode("#p#", $this->Fields[$this->SplitPageField]);
            $i = 1;
            foreach ($this->SplitFields as $k => $v) {
                $tmpv = cn_substr($v, 50);
                $pos = strpos($tmpv, '#e#');
                if ($pos > 0) {
                    $st = trim(cn_substr($tmpv, $pos));
                    if ($st == "" || $st == "副标题" || $st == "分页标题") {
                        $this->SplitFields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        continue;
                    } else {
                        $this->SplitFields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        $this->SplitTitles[$k] = $st;
                    }
                } else {
                    continue;
                }
                $i++;
            }
            $this->TotalPage = count($this->SplitFields);
            $this->Fields['totalpage'] = $this->TotalPage;
        }

        //处理默认缩略图等
        if (isset($this->Fields['litpic'])) {
            $this->Fields['picname'] = $this->Fields['litpic'];

            //模板里直接使用{dwt:field name='image'/}获取缩略图
            $this->Fields['image'] = (!preg_match('/jpg|gif|png/i', $this->Fields['picname']) ? '' : "<img src='{$this->Fields['picname']}'/>");
        }
    }

    //获得当前字段参数
    function GetCurTag($fieldname)
    {

        if (!isset($this->dtp->CTags)) {
            return '';
        }
        foreach ($this->dtp->CTags as $ctag) {
            if ($ctag->GetTagName() == 'field' && $ctag->GetAtt('name') == $fieldname) {
                return $ctag;
            } else {
                continue;
            }
        }
        return '';
    }


    /**
     *  获得指定键值的字段
     *
     * @access    public
     *
     * @param     string $fname 键名称
     * @param     string $ctag  标记
     *
     * @return    string
     */
    function GetField($fname, $ctag)
    {


        //所有Field数组 OR 普通Field
        if ($fname == 'array') {
            return $this->Fields;
        }
//        //指定了ID的节点
//        else if($ctag->GetAtt('noteid') != '')
//        {
//            if( isset($this->Fields[$fname.'_'.$ctag->GetAtt('noteid')]) )
//            {
//                return $this->Fields[$fname.'_'.$ctag->GetAtt('noteid')];
//            }
//        }
        //指定了type的节点
        else if ($ctag->GetAtt('type') != '') {
            if (isset($this->Fields[$fname . '_' . $ctag->GetAtt('type')])) {
                return $this->Fields[$fname . '_' . $ctag->GetAtt('type')];
            }
        } else if (isset($this->Fields[$fname])) {
            return $this->Fields[$fname];
        }
        return '';
    }

    /**
     *  获得模板文件位置
     *
     * @access    public
     * @return    string
     */
    function GetTempletFile()
    {

        $filetag =  $this->TypeLink->TypeInfos["temparticle"];

        $tmpfile = $filetag;
        if (!preg_match("#.htm$#", $tmpfile)) return FALSE;
        return $tmpfile;
    }

    /**
     *  动态输出结果
     *
     * @access    public
     * @return    void
     */
    function display()
    {
        if ($this->IsError) {
            return '';
        }
        if ($this->NowPage > 1) $this->Fields["title"] = $this->Fields["title"] . "({$this->NowPage})";
        //预编译
        $this->LoadTemplet();//载入模板
        $this->ParAddTable();//读取附加表

        $this->ParseTempletsFirst(); //解析模板，对固定的标记进行初始给值

        $pageCount = $this->NowPage;
        $this->ParseDMFields($pageCount, 0);
        $this->dtp->display();
    }

    /**
     *  载入模板
     *
     * @access    public
     * @return    void
     */
    function LoadTemplet()
    {
        if ($this->TempSource == '') {
            $tempfile = $this->GetTempletFile();
            //dump($tempfile);
            if (!file_exists($tempfile) || !is_file($tempfile)) {
                echo "文档ID：{$this->Fields['id']} - {$this->TypeLink->TypeInfos['typename']} - {$this->Fields['title']}<br />";
                echo "{$tempfile}模板文件不存在，无法解析文档！";
                exit();
            }
            $this->dtp->LoadTemplate($tempfile);
            $this->TempSource = $this->dtp->SourceString;
        } else {
            $this->dtp->LoadSource($this->TempSource);
        }
    }

    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    public
     * @return    void
     */
    function ParseTempletsFirst()
    {

        if (empty($this->Fields['reid'])) {
            $this->Fields['reid'] = 0;
        }


        if (isset($this->TypeLink->TypeInfos['reid'])) {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }


        $GLOBALS['envs']['typeid'] = $this->Fields['typeid'];

        $GLOBALS['envs']['topid'] = GetArchiveTopid($this->Fields['typeid']);

        $GLOBALS['envs']['aid'] = $GLOBALS['envs']['id'] = $this->Fields['id'];

        $GLOBALS['envs']['userid'] = $GLOBALS['envs']['userid'] = isset($this->Fields['userid']) ? $this->Fields['userid'] : 1;
        $this->Fields['arc_empname'] = GetEmpNameByUserId($GLOBALS['envs']['userid']);   //获取发布人姓名
        //dump($GLOBALS['envs']['arc_empname']);

        $GLOBALS['envs']['channelid'] = $this->TypeLink->TypeInfos['channeltype'];

        if ($this->Fields['reid'] > 0) {
            $GLOBALS['envs']['typeid'] = $this->Fields['reid'];
        }
        MakeOneTag($this->dtp, $this, 'N');
    }

    /**
     *  解析模板，对内容里的变动进行赋值
     *
     * @access    public
     *
     * @param     string $pageNo 页码数
     *
     * @return    string
     */
    function ParseDMFields($pageNo)
    {
        $this->NowPage = $pageNo;
        $this->Fields['nowpage'] = $this->NowPage;

        if ($this->SplitPageField != '' && isset($this->Fields[$this->SplitPageField])) {
            $this->Fields[$this->SplitPageField] = $this->SplitFields[$pageNo - 1];
            //if($pageNo>1) $this->Fields['description'] = trim(preg_replace("/[\r\n\t]/", ' ', cn_substr(html2text($this->Fields[$this->SplitPageField]), 200)));
        }


        //解析模板
        if (is_array($this->dtp->CTags)) {
            foreach ($this->dtp->CTags as $i => $ctag) {
                if ($ctag->GetName() == 'field') {
                    $this->dtp->Assign($i, $this->GetField($ctag->GetAtt('name'), $ctag));
                } else if ($ctag->GetName() == 'pagebreak') {
                    $this->dtp->Assign($i, $this->GetPagebreakDM($this->TotalPage, $this->NowPage, $this->ArcID));
                } else if ($ctag->GetName() == 'pagetitle') {
                    $this->dtp->Assign($i, $this->GetPageTitlesDM($ctag->GetAtt("style"), $pageNo));
                } else if ($ctag->GetName() == 'prenext') {
                    $this->dtp->Assign($i, $this->GetPreNext($ctag->GetAtt('get')));
                } else if ($ctag->GetName() == 'fieldlist') {
                    $innertext = trim($ctag->GetInnerText());
                    if ($innertext == '') $innertext = GetSysTemplets('tag_fieldlist.htm');
                    $dtp2 = new DwtTagParse();
                    $dtp2->SetNameSpace('field', '[', ']');
                    $dtp2->LoadSource($innertext);
                    $oldSource = $dtp2->SourceString;
                    $oldCtags = $dtp2->CTags;
                    $res = '';
                    if (is_array($this->ChannelUnit->ChannelFields) && is_array($dtp2->CTags)) {
                        foreach ($this->ChannelUnit->ChannelFields as $k => $v) {
                            if (isset($v['autofield']) && empty($v['autofield'])) {
                                continue;
                            }
                            $dtp2->SourceString = $oldSource;
                            $dtp2->CTags = $oldCtags;
                            $fname = $v['itemname'];
                            foreach ($dtp2->CTags as $tid => $ctag2) {
                                if ($ctag2->GetName() == 'name') {
                                    $dtp2->Assign($tid, $fname);
                                } else if ($ctag2->GetName() == 'tagname') {
                                    $dtp2->Assign($tid, $k);
                                } else if ($ctag2->GetName() == 'value') {
                                    $this->Fields[$k] = $this->ChannelUnit->MakeField($k, $this->Fields[$k], $ctag2);
                                    @$dtp2->Assign($tid, $this->Fields[$k]);
                                }
                            }
                            $res .= $dtp2->GetResult();
                        }
                    }
                    $this->dtp->Assign($i, $res);
                }//end case

            }//结束模板循环

        }
    }

    /**
     *  关闭所占用的资源
     *
     * @access    public
     * @return    void
     */
    function Close()
    {
        $this->FixedValues = '';
        $this->Fields = '';
    }

    /**
     *  获取上一篇，下一篇链接
     *
     * @access    public
     *
     * @param     string $gtype 获取类型
     *                          pre:上一篇  preimg:上一篇图片  next:下一篇  nextimg:下一篇图片
     *
     * @return    string
     */
    function GetPreNext($gtype = '')
    {
        $rs = '';
        if (count($this->PreNext) < 2) {
            $aid = $this->ArcID;
            $preR = $this->dsql->GetOne("SELECT id FROM `#@__archives_arctiny` WHERE id<$aid And issend=0 And typeid='{$this->Fields['typeid']}' ORDER BY id desc");
            $nextR = $this->dsql->GetOne("SELECT id FROM `#@__archives_arctiny` WHERE id>$aid And issend=0 And typeid='{$this->Fields['typeid']}' ORDER BY id asc");
            $next = (is_array($nextR) ? " where arc.id={$nextR['id']} " : ' where 1>2 ');
            $pre = (is_array($preR) ? " where arc.id={$preR['id']} " : ' where 1>2 ');
            $query = "SELECT arc.id,arc.title,arc.typeid,arc.senddate,arc.issend,arc.litpic,
                        t.typename,t.ispart
                        FROM `#@__archives` arc LEFT JOIN #@__archives_type t on arc.typeid=t.id  ";
            $nextRow = $this->dsql->GetOne($query . $next);
            $preRow = $this->dsql->GetOne($query . $pre);
            if (is_array($preRow)) {
                $mlink = "/lyapp/archives/archives_view.php?aid=" . $preRow['id'];
                $this->PreNext['pre'] = "上一篇：<a href='$mlink'>{$preRow['title']}</a> ";
                $this->PreNext['preimg'] = "<a href='$mlink'><img src=\"{$preRow['litpic']}\" alt=\"{$preRow['title']}\"/></a> ";
            } else {
                $this->PreNext['pre'] = "上一篇：没有了 ";
                $this->PreNext['preimg'] = "<img src=\"/templets/default/images/nophoto.jpg\" alt=\"对不起，没有上一图集了！\"/>";
            }
            if (is_array($nextRow)) {
                $mlink = "/lyapp/archives/archives_view.php?aid=" . $nextRow['id'];
                $this->PreNext['next'] = "下一篇：<a href='$mlink'>{$nextRow['title']}</a> ";
                $this->PreNext['nextimg'] = "<a href='$mlink'><img src=\"{$nextRow['litpic']}\" alt=\"{$nextRow['title']}\"/></a> ";
            } else {
                $this->PreNext['next'] = "下一篇：没有了 ";
                $this->PreNext['nextimg'] = "<a href='javascript:void(0)' alt=\"\"><img src=\"/templets/default/images/nophoto.jpg\" alt=\"对不起，没有下一图集了！\"/></a>";
            }
        }
        if ($gtype == 'pre') {
            $rs = $this->PreNext['pre'];
        } else if ($gtype == 'preimg') {

            $rs = $this->PreNext['preimg'];
        } else if ($gtype == 'next') {
            $rs = $this->PreNext['next'];
        } else if ($gtype == 'nextimg') {

            $rs = $this->PreNext['nextimg'];
        } else {
            $rs = $this->PreNext['pre'] . " &nbsp; " . $this->PreNext['next'];
        }
        return $rs;
    }

    /**
     *  获得动态页面分页列表
     *
     * @access    public
     *
     * @param     int $totalPage 总页数
     * @param     int $nowPage   当前页数
     * @param     int $aid       文档id
     *
     * @return    string
     */
    function GetPagebreakDM($totalPage, $nowPage, $aid)
    {
        global $cfg_rewrite;
        if ($totalPage == 1) {
            return "";
        }
        $PageList = "<li><a>共" . $totalPage . "页: </a></li>";
        $nPage = $nowPage - 1;
        $lPage = $nowPage + 1;
        if ($nowPage == 1) {
            $PageList .= "<li><a href='#'>上一页</a></li>";
        } else {
            if ($nPage == 1) {
                $PageList .= "<li><a href='archives_view.php?aid=$aid'>上一页</a></li>";
            } else {
                $PageList .= "<li><a href='archives_view.php?aid=$aid&pageno=$nPage'>上一页</a></li>";
            }
        }
        for ($i = 1; $i <= $totalPage; $i++) {
            if ($i == 1) {
                if ($nowPage != 1) {
                    $PageList .= "<li><a href='archives_view.php?aid=$aid'>1</a></li>";
                } else {
                    $PageList .= "<li class=\"thisclass\"><a>1</a></li>";
                }
            } else {
                $n = $i;
                if ($nowPage != $i) {
                    $PageList .= "<li><a href='archives_view.php?aid=$aid&pageno=$i'>" . $n . "</a></li>";
                } else {
                    $PageList .= "<li class=\"thisclass\"><a href='#'>{$n}</a></li>";
                }
            }
        }
        if ($lPage <= $totalPage) {
            $PageList .= "<li><a href='archives_view.php?aid=$aid&pageno=$lPage'>下一页</a></li>";
        } else {
            $PageList .= "<li><a href='#'>下一页</a></li>";
        }
        return $PageList;
    }


    /**
     *  获得动态页面小标题
     *
     * @access    public
     *
     * @param     string $styleName 类型名称
     * @param     string $pageNo    页码数
     *
     * @return    string
     */
    function GetPageTitlesDM($styleName, $pageNo)
    {
        if ($this->TotalPage == 1) {
            return "";
        }
        if (count($this->SplitTitles) == 0) {
            return "";
        }
        $i = 1;
        $aid = $this->ArcID;
        if ($styleName == 'link') {
            $revalue = "";
            foreach ($this->SplitTitles as $k => $v) {
                if ($i == 1) {
                    $revalue .= "<a href='archives_view.php?aid=$aid&pageno=$i'>$v</a> \r\n";
                } else {
                    if ($pageNo == $i) {
                        $revalue .= " $v \r\n";
                    } else {
                        $revalue .= "<a href='archives_view.php?aid=$aid&pageno=$i'>$v</a> \r\n";
                    }
                }
                $i++;
            }
        } else {
            $revalue = "<select id='dedepagetitles' onchange='location.href=this.options[this.selectedIndex].value;'>\r\n";
            foreach ($this->SplitTitles as $k => $v) {
                if ($i == 1) {
                    $revalue .= "<option value='" . $this->Fields['phpurl'] . "/archives_view.php?aid=$aid&pageno=$i'>{$i}、{$v}</option>\r\n";
                } else {
                    if ($pageNo == $i) {
                        $revalue .= "<option value='" . $this->Fields['phpurl'] . "/archives_view.php?aid=$aid&pageno=$i' selected>{$i}、{$v}</option>\r\n";
                    } else {
                        $revalue .= "<option value='" . $this->Fields['phpurl'] . "/archives_view.php?aid=$aid&pageno=$i'>{$i}、{$v}</option>\r\n";
                    }
                }
                $i++;
            }
            $revalue .= "</select>\r\n";
        }
        return $revalue;
    }


}//End Archives

