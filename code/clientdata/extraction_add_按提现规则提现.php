<?php
/**
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
//获取提现相关的数据
if ($dopost == 'GetInfo') {

    echo json_encode(GetExtractionInfo($clientid));
    exit();
}
if ($dopost == 'save') {

    if (empty($clientid)) $clientid = '';



    $ex_info_array=GetExtractionInfo($clientid);
    $jbmax=($ex_info_array["jbmax"]);
    if ($clientid == '') {
        ShowMsg("提现失败,请选择会员！", "-1");
        exit;
    } elseif($jb>$jbmax){
        ShowMsg("提现失败,提现金额错误！", "-1");
        exit;

    } else{


        $jbnum100 = $jb * 100;
        $jbnum100_t = -$jbnum100;

        $createtime = time();
        $istrue = Update_jb($clientid, $jbnum100_t, "管理员手工提现", 0, $CUSERLOGIN->userID);
        if ($istrue) {
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__clientdata_extractionlog` (`clientid`,`jbnum`,`createtime`,`passtime`,`operatorid`)
                                    VALUES ('$clientid','$jbnum100','$createtime','$createtime','$CUSERLOGIN->userID')");
            ShowMsg("保存成功！", "extraction.php");
        } else {
            ShowMsg("保存失败！", "extraction.php");
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会员提现</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg" style="background-color: #ffffff">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5>会员提现 </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择会员:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                                <input type="hidden" name="clientid" id="clientid" value=""/>
                                <span id="clientid_str"><span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">会员信息</label>
                            <div class="col-sm-4 form-control-static">
                                <span id="realname"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">金币余额</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="jbye" name="jbye"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">可提现数量</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="jb_max" name="jb_max"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">提现金币</label>
                            <div class="col-sm-2">
                                <input class="form-control" id="jb" name="jb" type="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary">保存</button>
                            </div>
                        </div>


                    </form>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>

    </div>


    <?
    //所有的优惠类型
    $display_array = array(
        "起提数量","保留数量"
    );


    //所有的配置类型

    $clientType_array = GetSYSClientAllType();


    // dump($clientType_array);
    ?>
    <div class="row">

        <div class="table-responsive">
            <table data-toggle="table" data-classes="table table-hover table-condensed"
                   data-striped="true" data-sort-order="desc"
                   data-mobile-responsive="true">
                <thead>
                <tr>
                    <th class="text-center"></th>
                    <?php
                    foreach ($clientType_array as $value_array) {

                        echo "<th class=\"text-center\">";
                        //echo $value_array["type"];
                        echo $value_array["typevalue"];
                        echo $value_array["info"];
                        echo "</th>";

                    } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                $form_list = "";
                foreach ($display_array as $value) {

                    echo "<tr>";
                    echo " <td>$value</td > ";
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
                        echo "<td>";

                        echo $form_str_jb;

                        echo "</td>";

                    }
                    echo "</tr> ";


                }
                $form_list = rtrim($form_list, ",");


                ?>

                </tbody>
            </table>
        </div>
        提现算法:如果会员有多个身份,则按以下规则执行<br>
        "起提数量"：以多个身份中最低的进行起提限制<br>
        "保留数量"：以多个身份中最低的进行保留限制

    </div>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {

        $("#form1").validate({
            rules: {
                clientid: {required: true},
                jb: {number: true, required: true,isIntGtZero:true},
            },
            messages: {
                clientid: {required: "请选择会员"},
                jb: {number: "请输入数字", required: "请输入提现数量",isIntGtZero:"提现数量必须大于0"}

            }
        });

    });

    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php?dopost=jbtx'});
    }
    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                $("#clientid_str").html("编号" + clientid);//保存改变后的值

                $.ajax({
                    type: "get",
                    url: "extraction_add.php",
                    data: {
                        clientid: clientid,
                        dopost: "GetInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        console.log(result);
                        $("#realname").html(result.realname);
                        $("#jbye").html(result.jbye);
                        $("#jb_max").html(result.jbmax);
                        $("#jb").val(parseInt(result.jbmax));
                        $("#jb").attr("max", parseInt(result.jbmax))
                    }
                });

            }
        }
    });

</script>
</body>
</html>