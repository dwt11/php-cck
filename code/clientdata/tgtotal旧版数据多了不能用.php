<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
$t1 = ExecTime();
if (!isset($keywordid)) $keywordid = '';
if ($keywordid == "") {
    $client_Array = "";//没上级 并且有下级的客户
    $sqlstr = "SELECT `#@__client_addon`.clientid
                ,`#@__client_addon`.jfnum,`#@__client_addon`.jbnum,`#@__client_addon`.sponsorid
                ,cl.realname,cl.mobilephone,clw.sex,clw.nickname,clw.photo FROM `#@__client_addon`
                 LEFT JOIN `#@__client` cl ON cl.id=`#@__client_addon`.clientid
                 LEFT JOIN #@__client_weixin clw ON cl.id=clw.clientid
                WHERE `#@__client_addon`.sponsorid=0 ";

//$sqlstr="SELECT clientid FROM `#@__client_addon` WHERE sponsorid=0";
// dump($sqlstr);
    $dsql->SetQuery($sqlstr);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $clientid = $row["clientid"];
        $arcRow = $dsql->GetOne('select clientid FROM #@__client_addon where sponsorid=' . $clientid);
        if (isset($arcRow['clientid']) && $arcRow['clientid'] > 0) {
            $client_Array[] = array("clientid" => $clientid, "realname" => $row['realname'], "mobilephone" => $row['mobilephone'], "nickname" => $row['nickname'],);
        }
        // $client_Array[]=$clientid;
    }
} else {
    $row_cc = $dsql->GetOne('select b.id,b.realname,b.mobilephone,bb.nickname FROM #@__client as b
                     LEFT JOIN #@__client_depinfos as bb on bb.clientid=b.id
                      where b.id=' . $keywordid);
    $client_Array[] = array("clientid" => $keywordid, "realname" => $row_cc['realname'], "mobilephone" => $row_cc['mobilephone'], "nickname" => $row_cc['nickname'],);

}


//dump($client_Array);


global $client_yxj_Array;
$client_yxj_Array = "";//有下级
foreach ($client_Array as $clientinfo) {
    $clientid = $clientinfo["clientid"];
    $sqlstr = "SELECT clientid FROM `#@__client_addon` WHERE sponsorid=$clientid ";
    //dump($sqlstr);
    $dsql->SetQuery($sqlstr);
    $dsql->Execute($clientid);
    while ($row = $dsql->GetArray($clientid)) {
        $client_yxj_Array[1][$clientid][] = $row['clientid'];
        giveme2($row['clientid'], 2);
    }
}

//递归下级
function giveme2($id, $gi)
{
    global $dsql, $client_yxj_Array;
    $returnArray = "";
    $sqlstr = "SELECT clientid FROM `#@__client_addon` WHERE sponsorid=$id ";
    //dump($gi."   ".$sqlstr);
    $dsql->SetQuery($sqlstr);
    $dsql->Execute($gi . $id);
    while ($row = $dsql->GetArray($gi . $id)) {
        //dump($row['clientid']);
        $client_yxj_Array[$gi][$id][] = $row['clientid']; //
        giveme2($row['clientid'], ($gi + 1));
    }
}


//格式
//$client_yxj_Array[级数][上级ID][下级ID]
/**
 * 递归获取下级数量
 *
 * @param     $clientid
 * @param     $client_yxj_Array
 * @param int $return_numb
 *
 * @return int
 */
function getxjnumb($clientid, $client_yxj_Array, $return_numb = 0)
{
    $numb = 0;
    //筛选所有级数的客户
    foreach ($client_yxj_Array as $jskey => $client_xjarry) {
        // if($clientid==227)dump("第".$jskey);
        //筛选到前级的客户
        foreach ($client_xjarry as $sjclientid => $xjclient_id) {
            //dump("上级客户ID".$sjclientid);
            //当前的上级ID，等于送进来的参数  则读数
            if ($sjclientid == $clientid) {
                //dump(count($client_id));
                $numb = count($xjclient_id);

                //筛选当前级ID的下级客户的数量
                foreach ($xjclient_id as $xxxxxjclientid) {
                    $numb = getxjnumb($xxxxxjclientid, $client_yxj_Array, $numb);
                }
            }
        }
    }
    $allnumb = $return_numb + $numb;
    return $allnumb;
}


//dump(number_format(getxjmoney(416,$client_yxj_Array),2));
function getxjmoney($clientid, $client_yxj_Array, $return_numb = 0)
{
    global $dsql;


    //计算当前用户的订单
    $sql = "SELECT sum(paynum) as dd from x_order where sta=1 and  clientid =$clientid;";
    $row_cc = $dsql->GetOne($sql);
    $numb_me = $row_cc['dd'] / 100;
    //筛选所有级数的客户
    foreach ($client_yxj_Array as $jskey => $client_xjarry) {
        foreach ($client_xjarry as $sjclientid => $xjclient_id) {
            //if($sjclientid==419) dump("上级客户ID".$sjclientid);
            //当前的上级ID，等于送进来的参数  则读数
            if ($sjclientid == $clientid) {
                foreach ($xjclient_id as $xxxxxjclientid) {
                    $numb_me = getxjmoney($xxxxxjclientid, $client_yxj_Array, $numb_me);
                }

            }
        }
    }
    $allnumb = $return_numb + $numb_me;//+ $numb_me;
    return $allnumb;
}

/**
 * 获取用户信息
 *
 * @param $clientid
 *
 * @return array
 */
function getClinetInfo($clientid)
{
    global $dsql;
    $info_arry = array();
    $row_cc = $dsql->GetOne('select b.id,b.realname,b.mobilephone,bb.nickname FROM #@__client as b
                     LEFT JOIN #@__client_weixin as bb on bb.clientid=b.id
                      where b.id=' . $clientid);
    $name = "无姓名";
    $realname = $row_cc["realname"];
    $nickname = $row_cc["nickname"];
    if ($realname != "") {
        $name = $realname;
    } elseif ($realname == "" && $nickname != "") {
        $name = $nickname;
    }
    $info_arry['name'] = $name;
    return $info_arry;
}

//
/**输出第二级用户表格
 *
 * @param $clientid
 * @param $gi
 * @param $client_yxj_Array
 */
function getClinetHtml($clientid, $gi, $client_yxj_Array)
{
    global $client_xj_numb;
    global $dsql;
    if (isset($client_yxj_Array[$gi][$clientid]) && $client_yxj_Array[$gi][$clientid] != "") {
        foreach ($client_yxj_Array[$gi][$clientid] as $jskey => $client_xjid) {

            $clientinfo = getClinetInfo($client_xjid);
            $name = $clientinfo['name'];

            $style = "";//如果有下级加粗
            $numb = getxjnumb($client_xjid, $client_yxj_Array);
            $money = number_format(getxjmoney($client_xjid, $client_yxj_Array), 2);//金额
            $zw = "";//如果没有下级  则名称前加空格占位
            if ($numb > 0) {
                $style = "style=' font-weight: bold'";
                $client_xj_numb[$client_xjid] = $numb;//每个客户的下级总数 备用
            } else {

                $zw = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            echo "<ol class='dd-list'>\r\n";
            echo "    <li class='dd-item'>\r\n";
            $ginumb = $gi + 1;
            echo "        <div class='dd-handle' $style>{$zw}[第{$ginumb}级]$name\r\n";
            echo "ID" . $client_xjid;
            if ($numb > 0) echo "            <small  class='text-muted'>(下级总人数:" . $numb . ")           </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);
            //if ($this->isSun($id)) echo ",不包含子部门：" . $this->GetOnlyTotalEmp($id);
            echo "            <span class='pull-right'>\r\n";
            if ($money > 0) echo "            <small  class='text-muted' style='color: #d2322d'>(总消费:" . $money . ")           </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);
            echo "            </span>\r\n</div>\r\n";
            getClinetHtml($client_xjid, $gi + 1, $client_yxj_Array);
            echo "    </li>\r\n";
            echo "</ol>\r\n";
        }

    }

}


?>


    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title><?php echo $sysFunTitle ?></title>
        <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
        <link href="../ui/css/animate.min.css" rel="stylesheet">
        <link href="../ui/css/style.min.css" rel="stylesheet">
    </head>
    <body class="gray-bg">

    <div class="wrapper wrapper-content animated fadeInRight">


        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">

                    <!--标题栏和 添加按钮            开始-->
                    <div class="ibox-title">
                        <h5><?php echo $sysFunTitle ?></h5>
                    </div>
                    <!--标题栏和 添加按钮   结束-->


                    <div class="ibox-content">
                        <!--搜索框   开始-->
                        <!--搜索框   结束-->
                        <!--表格数据区------------开始-->
                        <div class="dd" id="nestable2">
                            <?php
                            foreach ($client_Array as $clientinfo) {
                                // dump($clientinfo);
                                $name = "无姓名";
                                $realname = $clientinfo["realname"];
                                $nickname = $clientinfo["nickname"];
                                if ($realname != "") {
                                    $name = $realname;
                                } elseif ($realname == "" && $nickname != "") {
                                    $name = $nickname;
                                }
                                $clientid = $clientinfo["clientid"];
                                // $clientinfo = getClinetInfo($clientid);
                                //$name = $clientinfo['name'];

                                // $clientid=$clientinfo;
                                $numb = getxjnumb($clientid, $client_yxj_Array);//人数
                                $money = number_format(getxjmoney($clientid, $client_yxj_Array), 2);//金额
                                $style = "";
                                if ($numb > 0) {
                                    $style = "style=' font-weight: bold'";
                                    $client_xj_numb[$clientid] = $numb;
                                }
                                echo "<ol class='dd-list'>\r\n";
                                echo "    <li class='dd-item'>\r\n";
                                echo "        <div class='dd-handle' $style><span class='label label-info'></span>[第1级]$name\r\n";
                                echo "ID" . $clientid;
                                if ($numb > 0) echo "            <small  class='text-muted'>(下级总人数:" . $numb . ")        </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);

                                //if ($this->isSun($dep_id)) echo ",不包含子部门：" ; ///$this->GetOnlyTotalEmp($clientid);
                                echo "            <span class='pull-right'>\r\n";
                                if ($money > 0) echo "            <small  class='text-muted ' style='color: #d2322d'>(总消费:" . $money . ")        </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);
                                echo "            </span></div>\r\n";
                                getClinetHtml($clientid, 1, $client_yxj_Array);
                                echo "    </li>\r\n</ol>\r\n";
                            }
                            ?>


                        </div>


                        <!--表格数据区------------结束-->
                        <br>

                        <div class="content clearfix" style="line-height: 30px">
                            <h5>推荐TOP20排行 <a href="tgtotal.php">所有人</a></h5>
                            <?php
                            global $client_xj_numb;

                            arsort($client_xj_numb);//排序
                            //dump($client_xj_numb);

                            $i = 0;//只显示 前20名
                            foreach ($client_xj_numb as $client_1_id => $numb) {
                                $i++;
                                if ($i > 20) break;
                                $clientinfo_0 = getClinetInfo($client_1_id);
//dump($client_1_id);
                                $name = $clientinfo_0["name"];
                                echo ' <div class="col-md-2"><a href="?keywordid=' . $client_1_id . '"> ' . $name . '(' . $numb . '人)</a> </div>';
                            }
                            ?>
                        </div>

                    </div>
                </div>


            </div>

        </div>
    </div>


    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <!--表格-->
    <!--表格-->
    <script src="../ui/js/plugins/nestable/jquery.nestable.js"></script>
    <!--表格-->
    <script>
        $(document).ready(function () {

            $("#nestable2").nestable();  //初始化
            $(".dd").nestable("collapseAll");//收缩全部.这里有BUG,原来旧界面的ajax动态获取值实现不了,因为无法得到+号-号的当前状态. 现在是直接加载所有的数据,后期再改为AJAX的
            $("#expand").on("click", function (e) {
                var target = $(e.target), action = target.data("action");
                if (action === "expand-all") {
                    $(".dd").nestable("expandAll")
                }
            })
            $("#collapse").on("click", function (e) {
                var target = $(e.target), action = target.data("action");
                if (action === "collapse-all") {
                    $(".dd").nestable("collapseAll")
                }
            })

        });
    </script>

    </body>
    </html>

<?php


$t2 = ExecTime();
//echo $t2 - $t1;

