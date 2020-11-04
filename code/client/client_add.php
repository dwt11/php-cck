<?php
/**
 * 添加
 *
 * @version
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

$dopost = isset($dopost) ? $dopost : "";

/*--------------------------------
function __save(){ 保存  }
-------------------------------*/
if ($dopost == 'save') {

    //对保存的内容进行处理
    $description = isset($description) ? trim($description) : "";
    if ($depid == "" || $realname == "" || $mobilephone == "") {
        ShowMsg("部门、姓名或手机不能为空！", "-1");
        exit;
    }

    if($sponsorid=="")$sponsorid=0;

    $clientid = RegClient(
        $realname, $mobilephone, $mobilephone_check = "", $address, $tag, $description, $from = "手工添加",
        $idcard, $CUSERLOGIN->userID, $sponsorid,
        $pwd = "",
        $depid, $openid = "", $AppId = "",
        $nickname = "", $sex_temp = "", $city = "", $province = "", $country = "", $headimgurl = ""
    );


    //dump($clientid);

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功添加！", $$ENV_GOBACK_URL);
    exit;
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
</head>
<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
        <input type="hidden" name="dopost" value="save"/>
        <div class="form-group">
            <label class="col-sm-2 control-label">部门:</label>

            <div class="col-sm-2">
                <select class="form-control" name='depid' id='depid'>
                    <option value=''>请选择部门...</option>
                    <?php

                    $depOptions = GetDepOptionListRole();
                    echo $depOptions;
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">姓名:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="realname" id="realname">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">手机:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="mobilephone" id="mobilephone">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">身份证:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="idcard" id="idcard">
            </div>
        </div>

        <?php

        $usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
        //dump($usertypename);
        $isskd=strpos($usertypename, "售卡点子部门");//判断 是否售卡点
        // dump($isskd);
        if ( $isskd===false) {
            //售卡点不显示介绍人
            //22售卡点管理人员
            //24零售卡点
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">介绍人:</label>

                <div class="col-sm-6">
                    <button type="button" id="select" class="btn btn-primary" onclick="selectClient()">选择</button>
                    <input type="hidden" name="clientid" id="clientid"/>
                    <input type="hidden" name="sponsorid" id="sponsorid"/>
                    <span id="sponsoridid_str"></span>
                    <span id="sponsoridname"></span>
                </div>
            </div>

        <?php } ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">住址:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="address" id="address">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">工作单位:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="tag" id="tag">
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea class="form-control" name="description" cols="30" rows="5" id="description"></textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",});
        $("#form1").validate({
            rules: {
                depid: {required: !0},
                realname: {required: !0},
                mobilephone: {required: !0, minlength: 11, isMobile: !0}
            },
            messages: {
                depid: {required: "请选择部门"},
                realname: {required: "请填写姓名"},
                mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "请正确填写您的手机号码"}
            }
        });
    });
</script>
<script>
    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: 'client.select.php'});
    }
    function clearsponsorid() {
        $("#clientid").val("");
        $("#sponsorid").val("");
        $("#sponsoridname").html("");
        $("#sponsoridid_str").html("");
    }
    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                if (clientid != "") {
                    $("#sponsoridid_str").html("编号" + clientid);//保存改变后的值
                    $("#sponsorid").val(clientid);//保存改变后的值
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
                            $("#sponsoridname").html(result.realname + " " + result.mobilephone);
                        }
                    });
                }
            }
        }
    });


</script>

</body>
</html>
