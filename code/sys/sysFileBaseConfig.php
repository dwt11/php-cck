<?php
/**
 *
 * @version        $Id: file_manage_main.php 1 8:48 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("sysFileBaseConfig.class.php");


/*一\显示:
 先显示数据库中已经保存的
 然后搜索数据库未保存的
 */

//更新实体文件功能的备注信息
if (empty($dopost)) $dopost = '';


//批量更新实体文件的信息 到文件中
if ($dopost == 'batupdate') {

    $phpArrayStr = "";
    for ($diri = 1; $diri <= $dirnumb; $diri++) {//目录
        $title_name_t = "title_" . $diri;
        $dir_name_t = "dir_" . $diri;

        $dir_name = $$dir_name_t;      //目录名称
        $dir_title = $$title_name_t;   //目录标题

        $phpArrayStr .= "\$GLOBALS['baseConfigFunArray']['$dir_name'][]='$dir_title';\r\n";
        $filenumb = "filenumb_" . $diri;
        for ($filei = 1; $filei <= $$filenumb; $filei++) {//主功能
            $fileTitle_t = "fileTitle_" . $diri . "_" . $filei;
            $fileName_t = "fileName_" . $diri . "_" . $filei;
            $fileDataBaseName_t = "fileDataBaseName_" . $diri . "_" . $filei;
            $fileDataIdName_t = "fileDataIdName_" . $diri . "_" . $filei;
            $fileDataDepName_t = "fileDataDepName_" . $diri . "_" . $filei;
            $fileDataUserName_t = "fileDataUserName_" . $diri . "_" . $filei;
            $fileDataChildName_t = "fileDataChildName_" . $diri . "_" . $filei;
            $fileIsDepCheck_t = "fileIsDepCheck_" . $diri . "_" . $filei;

            //dump($$fileTitle_t);
            // dump($$fileName_t );
            /* dump($$fileDataBaseName_t);
             dump($$fileDataIdName_t);
             dump($$fileDataDepName_t);
             dump($$fileDataUserName_t );
             dump($$fileDataChildName_t );*/
            if (isset($$fileName_t) && $$fileTitle_t != "") {//160603添加文件名和标题不为空才添加
                $fileIsDepCheck = "";
                if (isset($$fileIsDepCheck_t)) $fileIsDepCheck = 1;
                $phpArrayStr .= "\$GLOBALS['baseConfigFunArray']['$dir_name'][]='" . $$fileName_t . "," . $$fileTitle_t . "," . $$fileDataBaseName_t . "," . $$fileDataIdName_t . "," . $$fileDataDepName_t . "," . $$fileDataUserName_t . "," . $$fileDataChildName_t . "," . $fileIsDepCheck . "';\r\n";
                $actionnumb = "actionnumb_" . $diri . "_" . $filei;
                //dump($actionnumb);
                //dump($$actionnumb);
                for ($actioni = 1; $actioni <= $$actionnumb; $actioni++) {//动作文件
                    $childFileTitle_t = "childFileTitle_" . $diri . "_" . $filei . "_" . $actioni;
                    $childFileName_t = "childFileName_" . $diri . "_" . $filei . "_" . $actioni;
                    $childFileIsDepCheck_t = "childFileIsDepCheck_" . $diri . "_" . $filei . "_" . $actioni;
                    $childFileIsDepCheck = "";
                    if (isset($$childFileName_t) && $$childFileTitle_t != "") {//160603添加文件名和标题不为空才添加
                        if (isset($$childFileIsDepCheck_t)) $childFileIsDepCheck = 1;
                        $phpArrayStr .= "\$GLOBALS['baseConfigFunArray']['$dir_name']['" . $$fileName_t . "'][]='" . $$childFileName_t . "," . $$childFileTitle_t . "," . $childFileIsDepCheck . "';\r\n";
                        //if (isset($$childFileIsDepCheck_t)) dump($$childFileIsDepCheck_t);
                    }
                }
                $phpArrayStr .= "\r\n";
            }
        }
        $phpArrayStr .= "\r\n\r\n\r\n\r\n\r\n";
    }

    //dump($phpArrayStr);
    $cachefile = DEDEDATA . '/sys_function_data.php';
    $fp = fopen($cachefile, 'w');
    fwrite($fp, '<' . "?php\r\n\$GLOBALS['baseConfigFunArray'] = array();\r\n");
    fwrite($fp, "/* \$GLOBALS['baseConfigFunArray']['目录名称']           \r\n第1行    目录名称");
    fwrite($fp, "* \$GLOBALS['baseConfigFunArray']['目录名称'][]='deviceKnowledge.php,设备知识库_管理,device_img,id,did(device|id|depid),userid,,1';           \r\n第2行    0主文件地址，1文件功能说明标题，2数据表名称 ,3ID编号名称,4A部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称),5用户数据字段,6子分类字段,7是否部门数据\r\n");
    fwrite($fp, "* \$GLOBALS['baseConfigFunArray']['目录名称'][主文件名称]\r\n第3-X行   0动作文件地址 ,1文件功能说明标题,2是否部门数据\r\n");
    fwrite($fp, "* 如果部门数据字段或用户数据字段有值 and 是否部门数据为1 则权限不显示部门选择框\r\n");
    fwrite($fp, "* 如果部门数据字段或用户数据字段有值 and 是否部门数据为0或空 则权限显示部门选择框\r\n");
    fwrite($fp, "*/\r\n");
    fwrite($fp, $phpArrayStr);


    fclose($fp);


    ShowMsg('成功保存内容!', 'sysFileBaseConfig.php');
    exit();


}

//判断数据表 是否存在
if ($dopost == 'checkDataBaseName') {
    $chRow = $dsql->IsTable("#@__$name");
    //判断数据库是否存在
    if ($chRow) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}
//判断数据表的ID字段 是否存在
if ($dopost == 'checkDataIdName') {
    //这里有问题,如果对应的表没有数据的话,???????也会提示出错
    $arcRow = $dsql->GetOne( "SELECT $idName FROM #@__$dataName");
    //dump( "SELECT $idName from '#@__$dataName'");
    if (is_array($arcRow)) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}
//判断部门字段 是否存在
if ($dopost == 'checkDataDepName') {
    $sql="";
    //如果包含多级表连接,则分割
    if (stripos($depName, "(") !== false) {
        $temp_array = explode("(", $depName);
        $nowDataBaseFeldName = $temp_array[0];
        $join_array = explode("|", $temp_array[1]);
        $joinDataBaseName = $join_array[0];
        $joinDataBaseId = $join_array[1];
        $joinFieldname = rtrim($join_array[2], ")");
        $sql = "SELECT $joinFieldname
                                FROM #@__$dataName $dataName
                                LEFT JOIN #@__$joinDataBaseName $joinDataBaseName on $joinDataBaseName.$joinDataBaseId=$dataName.$nowDataBaseFeldName";
    }else{
        //这里有问题,如果对应的表没有数据的话,???????也会提示出错
        $sql= "SELECT $depName FROM #@__$dataName";
    }
    $arcRow = $dsql->GetOne( $sql);
    if (is_array($arcRow)) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}

//判断用户字段 是否存在
if ($dopost == 'checkDataUserName') {
    $sql="";
    //如果包含多级表连接,则分割
    if (stripos($userName, "(") !== false) {
        $temp_array = explode("(", $userName);
        $nowDataBaseFeldName = $temp_array[0];
        $join_array = explode("|", $temp_array[1]);
        $joinDataBaseName = $join_array[0];
        $joinDataBaseId = $join_array[1];
        $joinFieldname = rtrim($join_array[2], ")");
        $sql = "SELECT $joinFieldname
                                FROM #@__$dataName $dataName
                                LEFT JOIN #@__$joinDataBaseName $joinDataBaseName on $joinDataBaseName.$joinDataBaseId=$dataName.$nowDataBaseFeldName";
    }else{
        //这里有问题,如果对应的表没有数据的话,???????也会提示出错
        $sql= "SELECT $userName FROM #@__$dataName";
    }
    $arcRow = $dsql->GetOne( $sql);
    if (is_array($arcRow)) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}


//判断子分类字段 是否存在
if ($dopost == 'checkDataChildName') {
    $sql="";
    //如果包含多级表连接,则分割
    if (stripos($childName, "(") !== false) {
        $temp_array = explode("(", $childName);
        $nowDataBaseFeldName = $temp_array[0];
        $join_array = explode("|", $temp_array[1]);
        $joinDataBaseName = $join_array[0];
        $joinDataBaseId = $join_array[1];
        $joinFieldname = rtrim($join_array[2], ")");
        $sql = "SELECT $joinFieldname
                                FROM #@__$dataName $dataName
                                LEFT JOIN #@__$joinDataBaseName $joinDataBaseName on $joinDataBaseName.$joinDataBaseId=$dataName.$nowDataBaseFeldName";
    }else{
        //这里有问题,如果对应的表没有数据的话,???????也会提示出错
        $sql= "SELECT $childName FROM #@__$dataName";
    }
    $arcRow = $dsql->GetOne( $sql);
    if (is_array($arcRow)) {
        echo "true";
    } else {
        echo "false";
    }
    exit;
}


if ($dopost == "") {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>系统功能文件设定信息保存到文件</title>
        <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
        <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
        <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
        <link href="../ui/css/animate.min.css" rel="stylesheet">
        <link href="../ui/css/style.min.css" rel="stylesheet">
        <script src="../ui/js/jquery.min.js"></script>
    </head>

    <body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>系统功能文件设定信息保存到文件
                            <small></small>
                        </h5>
                    </div>
                    <div class="ibox-content">
                        <div class="alert alert-warning alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            1、列表中序号"1"为功能文件夹,"1-X"为主功能文件,"1-X-X"为动作文件。<br>
                            2、动作文件的功能说明,用下划线分割便于用户组设定时友好显示(只显示下划线后的)<br>
                            3、"字段_部门数据"，这个用于填写当前功能的数据表中与部门（公司）数据表连接的字段名称。无括号为当前功能的数据表中的字段。有括号为两数据表联查，联接方法:did(device|id|depid)A当前功能的部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称)。<br>
                            4、"字段_子分类"，这个用于填写当前功能的"子分类"字段名称,填写后可以在"菜单设定"中为主功能添加访问参数(参数必须与此处填写的字段名称一致)。无括号为当前功能的数据表中的字段。有括号为两数据表联查，联接方法:did(device|id|depid)A当前功能的部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称)。
                        </div>

                        <form name='form1' id='form1' method='post'>
                            <div class='panel-group' id='function'>
                                <?php
                                $fun = new sys_baseconfg();
                                $fun->listDir();
                                ?>
                            </div>
                            <div class="row text-center">
                                <input type="hidden" name="dopost" value='batupdate'/>
                                <button class="btn btn-primary" type="button">保存内容</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/toastr/toastr.min.js"></script>
    <link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <script src="../ui/js/jquery.cookie.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>

    <SCRIPT LANGUAGE="JavaScript">
        <?php
        echo $fun->panelJScode;?>
    </script>

    <style>
        .error1111 {
            background-color:#a94442;
            color: #ffffff;
        }
    </style>
    <!--验证用-->
    <script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
    <script>
        $('button').click(function () {
            var index11 = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
            //遍历子记录字段名称,并验证字段名称是否正确
            //setTimeout用于显示loading动画,但这里有问题,显示出来的会有图片静止现象
            setTimeout(function () {


                //判断主功能的数据表是否存在
                var falseNameNumb = 0;  //错误的数据表个数
                $("input[name^='fileDataBaseName_']", document.forms[0]).each(function () {
                    var inputName = this.name;
                    var inputValue = $(this).val();
                    if (inputValue != "") {
                        $.ajax({
                            async: false,//=======同步
                            type: "post",
                            url: "sysFileBaseConfig.php",
                            data: "dopost=checkDataBaseName&name=" + inputValue,
                            dataType: 'html',
                            success: function (result) {
                               // console.log(result);
                                if (result == "true") {
                                } else {
                                    //数据表不存在的话,边框颜色改变
                                    falseNameNumb++;
                                    //console.log(inputName + "数据表不存在");
                                    $("#" + inputName).addClass("error1111");
                                }
                            }
                        });
                    }
                });




                //判断主功能的数据表 的  id字段名称  是否正确
                var falseDataIdNumb=0;//错误的ID字段个数
                $("input[name^='fileDataIdName_']", document.forms[0]).each(function () {
                    var inputName = this.name;//存放ID字段的input表单的名称
                    var inputValue = $(this).val();//当前ID字段的名称
                    if (inputValue != "") {
                        var str_arry=inputName.split("_");
                        var inputDataValue=$('#fileDataBaseName_'+str_arry[1]+"_"+str_arry[2]).val();//对应的数据表的名称
                       // console.log(inputDataValue);
                        $.ajax({
                            async: false,//=======同步
                            type: "post",
                            url: "sysFileBaseConfig.php",
                            data: "dopost=checkDataIdName&dataName="+inputDataValue+"&idName=" + inputValue,
                            dataType: 'html',
                            success: function (result) {
                              //  console.log(result);
                                if (result == "true") {
                                } else {
                                    //数据表不存在的话,边框颜色改变
                                    falseDataIdNumb++;
                                    $("#" + inputName).addClass("error1111");
                                }
                            }
                        });
                    }
                });

                //判断部门字段名称  是否正确
                var falseDataDepNumb=0;//错误的ID字段个数
                $("input[name^='fileDataDepName_']", document.forms[0]).each(function () {
                    var inputName = this.name;//存放ID字段的input表单的名称
                    var inputValue = $(this).val();//当前ID字段的名称
                    if (inputValue != "") {
                        var str_arry=inputName.split("_");
                        var inputDataValue=$('#fileDataBaseName_'+str_arry[1]+"_"+str_arry[2]).val();//对应的数据表的名称
                       // console.log(inputDataValue);
                        $.ajax({
                            async: false,//=======同步
                            type: "post",
                            url: "sysFileBaseConfig.php",
                            data: "dopost=checkDataDepName&dataName="+inputDataValue+"&depName=" + inputValue,
                            dataType: 'html',
                            success: function (result) {
                              //  console.log(result);
                                if (result == "true") {
                                } else {
                                    //数据表不存在的话,边框颜色改变
                                    falseDataDepNumb++;
                                    $("#" + inputName).addClass("error1111");
                                }
                            }
                        });
                    }
                });

                //判断用户字段名称  是否正确
                var falseDataUserNumb=0;//错误的ID字段个数
                $("input[name^='fileDataUserName_']", document.forms[0]).each(function () {
                    var inputName = this.name;//存放ID字段的input表单的名称
                    var inputValue = $(this).val();//当前ID字段的名称
                    if (inputValue != "") {
                        var str_arry=inputName.split("_");
                        var inputDataValue=$('#fileDataBaseName_'+str_arry[1]+"_"+str_arry[2]).val();//对应的数据表的名称
                      //  console.log(inputDataValue);
                        $.ajax({
                            async: false,//=======同步
                            type: "post",
                            url: "sysFileBaseConfig.php",
                            data: "dopost=checkDataUserName&dataName="+inputDataValue+"&userName=" + inputValue,
                            dataType: 'html',
                            success: function (result) {
                                //console.log(result);
                                if (result == "true") {
                                } else {
                                    //数据表不存在的话,边框颜色改变
                                    falseDataUserNumb++;
                                    $("#" + inputName).addClass("error1111");
                                }
                            }
                        });
                    }
                });


                //判断子分类字段名称  是否正确
                var falseDataChildNumb=0;//错误的ID字段个数
                $("input[name^='fileDataChildName_']", document.forms[0]).each(function () {
                    var inputName = this.name;//存放ID字段的input表单的名称
                    var inputValue = $(this).val();//当前ID字段的名称
                    if (inputValue != "") {
                        var str_arry=inputName.split("_");
                        var inputDataValue=$('#fileDataBaseName_'+str_arry[1]+"_"+str_arry[2]).val();//对应的数据表的名称
                       // console.log(inputDataValue);
                        $.ajax({
                            async: false,//=======同步
                            type: "post",
                            url: "sysFileBaseConfig.php",
                            data: "dopost=checkDataChildName&dataName="+inputDataValue+"&childName=" + inputValue,
                            dataType: 'html',
                            success: function (result) {
                                //console.log(result);
                                if (result == "true") {
                                } else {
                                    //数据表不存在的话,边框颜色改变
                                    falseDataChildNumb++;
                                    $("#" + inputName).addClass("error1111");
                                }
                            }
                        });
                    }
                });






                layer.close(index11);
                var tipStr = "";
                if (falseNameNumb > 0)tipStr = '有' + falseNameNumb + '个数据表不存在<br>';
                if (falseDataIdNumb > 0)tipStr += '有' + falseDataIdNumb + '个ID字段不存在<br>';
                if (falseDataDepNumb > 0)tipStr += '有' + falseDataDepNumb + '个部门字段不存在<br>';
                if (falseDataUserNumb > 0)tipStr += '有' + falseDataUserNumb + '个用户字段不存在<br>';
                if (falseDataChildNumb > 0)tipStr += '有' + falseDataChildNumb + '个子分类字段不存在<br>';
                if (tipStr != "") {
                    var index = layer.confirm(tipStr+'继续保存,还是查看错误信息？', {
                        btn: ['忽略提示,继续保存', '查看提示的错误信息'] //按钮
                    }, function () {
                        $("#form1").submit();                         //继续保存
                    }, function () {
                        layer.close(index);
                        return false;  //取消后修改
                    });
                }else{
                    $("#form1").submit();                         //继续保存

                }
            }, 110);
        });
    </script>
    </body>
    </html>

<?php } ?>

