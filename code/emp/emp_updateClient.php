<?php
/**
 * 员工编辑
 *
 * @version        $Id: spec_edit.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . '/enums.func.php');  //获取联动枚举表单
if (empty($dopost)) $dopost = '';

/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {

    $arcQuery = "SELECT *  FROM #@__emp_client  WHERE emp_id='$emp_id' ";
    //dump($arcQuery);
    $row = $dsql->GetOne($arcQuery);
    if (is_array($row)) {
        $inQuery = "UPDATE `#@__emp_client` SET                    clientid='$clientid'                     WHERE (`emp_id`='$emp_id')                    ";
    } else {
        $inQuery = "INSERT INTO `x_emp_client` (`emp_id`,clientid) VALUES ('$emp_id','$clientid')";

    }

    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("绑定前台会员信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {

    //require_once(DWTPATH . "/emp/worktype.inc.options.php");

    //读取归档信息
    $arcQuery = "SELECT *  FROM #@__emp  WHERE emp_id='$emp_id' ";
    //dump($arcQuery);
    $row = $dsql->GetOne($arcQuery);
    if (!is_array($row)) {
        ShowMsg("读取信息出错!", "-1");
        exit();
    }

    //读取会员归档信息
    $query = "SELECT  cl.realname,cl.mobilephone,          clw.nickname,clw.photo
          FROM #@__client cl 
          LEFT JOIN #@__client_weixin clw ON cl.id=clw.clientid
            LEFT JOIN #@__emp_client ON #@__emp_client.clientid=cl.id
            WHERE #@__emp_client.emp_id='$emp_id'";
    //dump($arcQuery);
    $rowclient = $dsql->GetOne($query);


}
?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>

                </div>
                <div class="ibox-content">
                    员工信息
                    <table data-toggle="table" data-striped="true">
                        <thead>
                        <tr>
                            <th data-halign="center" data-align="center">员工编号</th>
                            <th data-halign="center" data-align="center">姓名</th>
                            <th data-halign="center" data-align="center">性别</th>
                            <th data-halign="center" data-align="center">手机</th>
                        </tr>
                        </thead>
                        <tr>
                            <td><?php echo GetIntAddZero($row['emp_code'], 3) ?></td>
                            <td class='bline'><?php echo $row['emp_realname'] ?></td>
                            <td><?php echo $row['emp_sex'] ?></td>
                            <td><?php echo $row['emp_mobilephone'] ?></td>
                        </tr>
                    </table>
                    <br>
                    <?php
                    //dump($rowclient);
                    if (is_array($rowclient)) {
                        ?>
                        前台会员信息
                        <table data-toggle="table" data-striped="true">
                        <thead>
                        <tr>
                            <th data-halign="center" data-align="center">微信信息</th>
                            <th data-halign="center" data-align="center">会员姓名</th>
                            <th data-halign="center" data-align="center">会员电话</th>
                        </tr>
                        </thead>
                        <tr>
                            <td>
                                <?php echo $rowclient["nickname"];?>
                                <br>
                                <?php $photo = $rowclient["photo"];
                                if ($photo == "") $photo = "../images/zw.jpg";
                                ?>
                                <img data-original="<?php echo $photo; ?>" width="80" height="80"/>
                            </td>

                            <td>
                                <?php echo $rowclient["realname"] ?>
                            </td>

                            <td>
                                <?php echo $rowclient["mobilephone"] ?>
                            </td>
                        </tr>
                        </table>
                        <br>
                    <?php } ?>
                    <form id="empadd" name="form1" action="" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>"/>

                        <input type="hidden" name="clientid" id="clientid" value=""/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择会员:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">会员名称</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="realname"></span>
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>


<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<!--表格-->
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<SCRIPT src="../ui/js/jquery.lazyload.js" type=text/javascript></SCRIPT>
<SCRIPT src="../ui/js/jquery.lazyload.plus.js" type=text/javascript></SCRIPT>
<script>
    $(document).ready(function () {

        $("#form1").validate({
            rules: {
                clientid: {required: true}
            },
            messages: {
                clientid: {required: "选择会员"}

            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "emp_updateClient.php",
                    data: {
                        dopost: "save",
                        clientid: $("#clientid").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                window.location.href = "emp.php";
                            });
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
                            });
                        }
                    }
                });
            }
        });
    });

    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
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
                    url: "../client/client.do.php",
                    data: {
                        clientid: clientid,
                        dopost: "GetOneClientInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        console.log(result);
                        $("#realname").html(result.realname + " " + result.mobilephone);
                    }
                });
            }
        }
    });

</script>


</body>
</html>
