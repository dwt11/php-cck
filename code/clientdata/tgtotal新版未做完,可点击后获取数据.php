<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
$t1 = ExecTime();

if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
//获取提现相关的数据
if ($dopost == 'get') {
    echo "aa";
    exit();
}

$client_Array = array();
$sqlstr = "SELECT `#@__client_addon`.clientid
                ,`#@__client_addon`.jfnum,`#@__client_addon`.jbnum,`#@__client_addon`.sponsorid
                ,cl.realname,cl.mobilephone,clw.nickname FROM #@__client_depinfos 
                         INNER JOIN #@__client_addon   ON #@__client_addon.clientid=#@__client_depinfos.clientid
         
                  INNER JOIN `#@__client` cl ON cl.id=`#@__client_addon`.clientid
                  INNER JOIN #@__client_weixin clw ON cl.id=clw.clientid
                WHERE `#@__client_depinfos`.isdel=0 ";

//$sqlstr="SELECT clientid FROM `#@__client_addon` WHERE sponsorid=0";
// dump($sqlstr);
$dsql->SetQuery($sqlstr);
$dsql->Execute();
while ($row = $dsql->GetArray()) {
    $clientid = $row["clientid"];
    //$jfnum = $row["jfnum"];
    //$jbnum = $row["jbnum"];
    $sponsorid = $row["sponsorid"];
    $realname = $row["realname"];
    $mobilephone = $row["mobilephone"];
    $nickname = $row["nickname"];

    $client_Array[$sponsorid][$clientid] = array("realname" => $realname, "mobilephone" => $mobilephone, "nickname" => $nickname);
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
                        foreach ($client_Array[0] as $clientid => $clientinfo) {
                            // dump($clientinfo);
                            $name = "无姓名";
                            $realname = $clientinfo["realname"];
                            $nickname = $clientinfo["nickname"];
                            if ($realname != "") {
                                $name = $realname;
                            } elseif ($realname == "" && $nickname != "") {
                                $name = $nickname;
                            }

                            $numb = $money = "";
                            //$numb = getxjnumb($clientid, $client_yxj_Array);//人数
                            //$money = number_format(getxjmoney($clientid, $client_yxj_Array), 2);//金额
                            $style = "";
                            if ($numb > 0) {
                                $style = "style=' font-weight: bold'";
                                $client_xj_numb[$clientid] = $numb;
                            }
                            echo "<ol class='dd-list' >\r\n";
                            echo "    <li class='dd-item' data-id=\"$clientid\">\r\n";
                            echo "        <div class='dd-handle' $style><span class='label label-info'></span>[第1级]$name\r\n";
                            echo "ID" . $clientid;
                            if ($numb > 0) echo "            <small  class='text-muted'>(下级总人数:" . $numb . ")        </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);

                            echo "            <span class='pull-right'>\r\n";
                            if ($money > 0) echo "            <small  class='text-muted ' style='color: #d2322d'>(总消费:" . $money . ")        </small> \r\n";//. GetDepAndChildTotalEmpNumb($id);
                            echo "            </span></div>\r\n";
                            //getClinetHtml($clientid, 1, $client_yxj_Array);
                            echo "    </li>\r\n</ol>\r\n";
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


        $(document).on("click", ".dd-item", function () {
            //点击框
            alert($(this).attr("data-id"));
            getAjaxJsonData()
        })


    });


    function getAjaxJsonData() {


        //获取下级数据
        $.ajax({
            url: "tgtotal.php?clientid=44444&dopost=get",
            type: "GET",
            async: false,
            dataType: 'json',
            success: function (dataJSON) {
                fn(dataJSON.data, $("#htmll"));
            },
            error: function (msg) {
                error(msg);
            }
        })
        function fn(array, html) {
            for (var i = 0; i < array.length; i++) {
                var object = array[i];
                var data = "";
                var temp = '';
                if (object.subs) {
                    var e = $("<ol class='dd-list'></ol>");
                    var f = $("<li class='dd-item' data-id='" + object.id + "'> <div class='dd-handle' id='" + object.id + "' ondblClick='aa(this)'>" + object.name + "</div></li>");
                    f.append(e);
                    html.append(f);
                    fn(object.subs, e);
                } else {
                    html.append("<li id='" + object.id + "' data-id='" + object.id + "' class='dd-item' ondblClick='aa(this)'><div class='dd-handle'>" + object.name + "</div></li>");
                }
            }
        }
    }

</script>

</body>
</html>


