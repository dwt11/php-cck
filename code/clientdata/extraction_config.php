<?php
/**
 * 提现保底数量

 * 配置
 */
require_once("../config.php");

$t1 = ExecTime();

if (!isset($dopost)) $dopost = '';


// 保存过程
if ($dopost == "save") {
    $return_str = "";
    $error_numb = 0;
    $createtime = time();
    $post_array = array();//保存传递过来的表单名称(有值的)

    $operatorid = $CUSERLOGIN->userID;
    if (!empty($form_list)) {
        //dump($form_list);
        $form_list_array = explode(',', $form_list);
        foreach ($form_list_array as $form_list_t) {
            //查询所有的表单,筛选出不为0 不为空的值
            //if (trim($$form_list_t) != "" || trim($$form_list_t) > 0) {
            if (trim($$form_list_t) != "") {
                //dump($form_list_t."------".$$form_list_t);
                $post_array[] = $form_list_t;//将表单名称存入数组 待用
            }
        }


        foreach ($post_array as &$str) {
            $form_list_t_array = explode('-', $str);

            //dump($form_list_array);
            //获取关键字段
            $configType = $form_list_t_array[0];//表单类型("起提数量",            "保留数量",    "单次最多提现数量")
            if (isset($form_list_t_array[1])) $clientType = $form_list_t_array[1];   //  rank or  scores
            if (isset($form_list_t_array[2])) $clientTypeValue = $form_list_t_array[2];   //会员类型的值


            $form_str_jb = "$configType-$clientType-$clientTypeValue";
            $form_value_jb = trim($$form_str_jb);
            $form_value_jb100 = $form_value_jb * 100;
            $sqlrowTrue = "SELECT id FROM `#@__clientdata_extraction_config`
                                    WHERE configType='$configType' 
                                    AND clientType='$clientType' 
                                    AND clientTypeValue='$clientTypeValue'  ";
            $rowTrue = $dsql->GetOne($sqlrowTrue);
            if (!$rowTrue) {
                $sql = "INSERT INTO `#@__clientdata_extraction_config` (  `clientType`, `clientTypeValue`, `configType`, `jbnum` , `createtime`,`operatorid`)
                                                 VALUES (   '$clientType', '$clientTypeValue', '$configType', '$form_value_jb100' , '$createtime','$operatorid');";
                //dump($sql);
                if (!$dsql->ExecuteNoneQuery($sql)) {

                }
            } else {
                $sql = "UPDATE `x_clientdata_extraction_config` SET  `jbnum`='$form_value_jb100', `createtime`='$createtime', `operatorid`='$operatorid' WHERE `clientType`='$clientType' AND `clientTypeValue`='$clientTypeValue' AND `configType`='$configType'";
                //dump($sql);
                if (!$dsql->ExecuteNoneQuery($sql)) {

                }

            }


        }
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    $return_str .= "更新信息成功";
    //if ($error_numb > 0) $return_str .= "保存数据出错,请检查";
     ShowMsg($return_str, $$ENV_GOBACK_URL, "", 5000);
    exit();
}


$title = $sysFunTitle;   //页面显示标题


//所有的配置类型
$display_array = array(
    "起提数量","保留数量"
);


$clientType_array = GetSYSClientAllType();

//dump($clientType_array);
?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg" style="min-width: 800px; background-color: #ffffff">

<div class="wrapper wrapper-content animated fadeInRight">


    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" value="save" name="dopost" id="dopost">


        <div class="panel-body">
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


                            $form_str_jb="";

                            //获取 已经存在的金币
                            $sqlrowTrue = "SELECT jbnum FROM `#@__clientdata_extraction_config`
                                    WHERE configType='$value' 
                                    AND clientType='{$value_array["type"]}' 
                                    AND clientTypeValue='{$value_array["typevalue"]}'  ";
                            $rowTrue = $dsql->GetOne($sqlrowTrue);
                            if (isset($rowTrue["jbnum"])&&$rowTrue["jbnum"]!="") {
                                $form_str_jb=$rowTrue["jbnum"]/100;
                            }




                            $eanme = "{$value}-{$value_array["type"]}-{$value_array["typevalue"]}";//表单名称
                            $form_list .= $eanme . ",";
                            echo "<td>";

                            echo " <input name='{$eanme}' id='{$eanme}' placeholder=''  type='number'  class='form-control' value='{$form_str_jb}' style='max-width: 90px'   min='0' />";

                            echo "</td>";

                        }
                        echo "</tr> ";


                    }
                    $form_list = rtrim($form_list, ",");


                    ?>

                    </tbody>
                </table>
            </div>
        </div>


        <div class="table - responsive"><br>
            <?php
            //$form_list = $bf->get_all_formname_str();
            echo " <input type = \"hidden\" value=\"$form_list\" name=\"form_list\" id=\"form_list\">";
            ?>
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存当前页内容</button>
            </div>

            提现算法:如果会员有多个身份,则按以下规则执行<br>
            "起提数量"：以多个身份中最低的进行起提限制<br>
            "保留数量"：以多个身份中最低的进行保留限制


        </div>
    </form>


</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--日期控件-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>

</body>
</html>
