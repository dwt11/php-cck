<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($dopost)) $dopost = '';
CheckRank();


/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'save') {
    //对保存的内容进行处理
    $pwdistrue = GetClientPayPwdIsTrue($CLIENTID, $pwd);
    if (!$pwdistrue) {
        echo "支付密码错误,操作失败！";
        exit();
    }
    $ex_info_array = GetExtractionInfo($CLIENTID);
    $jbmax = ($ex_info_array["jbmax"]);
    if ($jbnum > $jbmax) {
        echo "金币输入有误";
        exit();
    } else {
        $createtime = time();
        $jbnum100 = $jbnum * 100;

        $query = "INSERT INTO #@__clientdata_extractionlog (clientid,jbnum,createtime)
                  VALUES ('$CLIENTID','$jbnum100','$createtime');";
        $dsql->ExecuteNoneQuery($query);


        Update_jb($CLIENTID, -$jbnum100, "会员提现申请");

        echo("等待审核");
        exit();
    }

}

/*

$paypwd = "";
$row_paypwd = "SELECT paypwd FROM #@__client_pw WHERE clientid='$CLIENTID'";
$row_paypwd = $dsql->GetOne($row_paypwd);
if (isset($row_paypwd["paypwd"]) && $row_paypwd["paypwd"] != "") $paypwd = $row_paypwd["paypwd"];*/


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>提现</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/animate.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="/lyapp/css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                金币提现
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                <input type="hidden" name="dopost" value="save"/>
                <?php

                $ex_info_array = GetExtractionInfo($CLIENTID);
                $jbmax = ($ex_info_array["jbmax"]);
                $jbye = ($ex_info_array["jbye"]);
                $clientinfo = ($ex_info_array["realname"]);
                ?>
                <div class="form-group">
                    <div class="col-xs-4 control-label">会员信息:</div>
                    <div class="col-xs-8 form-control-static">
                        <span class="text-danger"><?php echo $clientinfo ?></span>

                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-4 control-label">金币余额:</div>
                    <div class="col-xs-8 form-control-static">
                        <span class="text-danger"><?php echo $jbye ?></span>

                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-4 control-label">可提现数量:</div>
                    <div class="col-xs-8 form-control-static">
                        <span class="text-danger"><?php echo $jbmax ?></span>

                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-4 control-label">输入提现数量:</div>
                    <div class="col-xs-8">
                        <input type="number" class="form-control" max="<?php echo (int)($jbmax) ?>" name="jbnum" id="jbnum" value="<?php echo (int)($jbmax) ?>" <?php if ($jbmax <=0) echo "disabled"; ?>>

                    </div>
                </div>


                <div class="form-group">
                    <div class="col-xs-4 control-label">支付密码:</div>
                    <div class="col-xs-8">
                        <input type="password" name="paypwd" id="paypwd" class="form-control" placeholder="请填写支付密码">


                    </div>
                </div>


                <div class="form-group">
                    <div class="text-center">
                        <?php

                        if ($jbmax > 0) {
                            echo '<button class="btn btn-primary" type="submit">提现</button> ';
                        } else {
                            echo '<button class="btn btn-primary" type="submit" disabled>提现</button> ';
                        }


                        ?>

                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        1、金币不达<b>起提数量</b>,可在平台内无限制意消费；<br>
                        2、金币达到<b>起提数量</b>,除保留数量外的金币,可提现至微信钱包；<br>
                        3、金币到账时间三到五个工作日；<br>
                        会员有多个身份,则按以下规则执行<br>
                        "起提数量"：以多个身份中最低的进行起提限制<br>
                        "保留数量"：以多个身份中最低的进行保留限制
                    </div>
                </div>

                <?
                //所有的优惠类型
                $display_array = array(
                    "起提数量", "保留数量"
                );


                //所有的配置类型

                $clientType_array = GetSYSClientAllType();


                // dump($clientType_array);
                ?>

                <table width='100%' cellspacing='1' cellpadding='3'>
                    <thead>
                    <tr>
                        <th class="text-center"></th>
                        <?php
                        foreach ($clientType_array as $value_array) {

                            echo "<th class=\"text-center\">";
                            //echo $value_array["type"];
                            if (!is_numeric($value_array["typevalue"])) echo $value_array["typevalue"];//不是数字才显示名称
                            echo $value_array["info"];
                            echo "</th>";

                        } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $form_list = "";
                    foreach ($display_array as $value) {

                        echo "<tr  >";
                        echo " <td  class=\"text-center\">$value</td > ";
                        foreach ($clientType_array as $value_array) {


                            $form_str_jb = "";

                            //获取 已经存在的金币
                            $sqlrowTrue = "SELECT jbnum FROM `#@__clientdata_extraction_config`
                                    WHERE configType='$value' 
                                    AND clientType='{$value_array["type"]}' 
                                    AND clientTypeValue='{$value_array["typevalue"]}'  ";
                            $rowTrue = $dsql->GetOne($sqlrowTrue);
                            if (isset($rowTrue["jbnum"]) && $rowTrue["jbnum"] !="") {
                                $form_str_jb = $rowTrue["jbnum"] / 100;
                            }


                            $eanme = "{$value}-{$value_array["type"]}-{$value_array["typevalue"]}";//表单名称
                            $form_list .= $eanme . ",";
                            echo "<td  class=\"text-center\">";

                            echo $form_str_jb;

                            echo "</td>";

                        }
                        echo "</tr> ";


                    }
                    $form_list = rtrim($form_list, ",");


                    ?>

                    </tbody>
                </table>


            </form>
        </div>
    </div>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>

<!--验证用-->
<script src="/ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script>
    $().ready(function () {
        $("#form1").validate({
            rules: {
                jbnum: {number: true, required: true, max: <?php echo $jbmax ?>,isIntGtZero:true},
                paypwd: {required: !0}
            },
            messages: {
                jbnum: {number: "必须填写数字", required: "不能为空", max: "超出可提现金币余额",isIntGtZero:"提现数量必须大于0"},
                paypwd: {required: "请填写支付密码"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "extraction_ing.php",
                    data: {dopost: "save", jbnum: $("#jbnum").val(), pwd: $("#paypwd").val()},
                    dataType: 'html',
                    success: function (result) {
                        //var index = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
                        if (result == "等待审核") {
                            layer.msg('提现成功，等待管理员审核！', {
                                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                                time: 2000, //20s后自动关闭
                            }, function () {
                                window.location.href = 'extraction.php';
                            });
                        } else {
                            layer.msg(result, {
                                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                                time: 2000, //20s后自动关闭
                            }, function () {
                                window.location.href = 'extraction.php';
                            });
                        }
                    }
                });
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg("系统错误,请重试", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = 'extraction_ing.php';
                });
            }
        })
    });
</script>
</body>
</html>
