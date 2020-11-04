<?php
require_once(dirname(__FILE__) . "/../include/config.php");
if (empty($dopost)) $dopost = '';
CheckRank();
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'save') {
    if (empty($mobilephone)) $mobilephone = '';
    //对保存的内容进行处理
    $pubdate = time();
    $sql = "";

    $questr = "SELECT realname,mobilephone,mobilephone_check FROM `#@__client`  where  id='$CLIENTID'";
    $row = $dsql->GetOne($questr);

    if ((!isset($row['realname']) || $row['realname'] == "") && $realname != "") {
        $query = "UPDATE #@__client SET   realname='$realname'   WHERE id='$CLIENTID'; ";
        $dsql->ExecuteNoneQuery($query);

    }

    if ((!isset($row['mobilephone']) || $row['mobilephone'] == "" || $row['mobilephone'] != "1") && $mobilephone != "") {
        $query = "UPDATE #@__client SET   mobilephone='$mobilephone'  WHERE id='$CLIENTID'; ";
        $dsql->ExecuteNoneQuery($query);
    }


    //更新基本信息
    $query = "UPDATE #@__client SET  pubdate='$pubdate'   WHERE id='$CLIENTID'; ";
    $dsql->ExecuteNoneQuery($query);

    /*    if (!empty($userName)) {
            //更新用户名
            $query = "UPDATE #@__client_pw SET   userName='$userName'  WHERE clientid='$CLIENTID'; ";
            $dsql->ExecuteNoneQuery($query);
        }*/

    //获取客户信息
    $questr = "SELECT idcard  FROM `#@__client_addon`  where  clientid='$CLIENTID'";
    $rowClientLy = $dsql->GetOne($questr);

    if ((!isset($rowClientLy['idcard']) || $rowClientLy['idcard'] == "") && $idcard != "") {
        $query = "UPDATE #@__client_addon SET   idcard='$idcard'  WHERE clientid='$CLIENTID'; ";
        $dsql->ExecuteNoneQuery($query);
    }


    /* //获取客户信息
    *    $questr = "SELECT idpic  FROM `#@__client_addon`  where  clientid='$CLIENTID'";
         $rowClientLy = $dsql->GetOne($questr

     /*    if ((!isset($rowClientLy['idpic']) || $rowClientLy['idpic'] == "") && $idpic != "") {
             require_once('upload_weixin.php');
             $icpic_t = weixinUploadPic($idpic, $CLIENTID);

             if (!$icpic_t) {
                 echo "照片保存失败，请重新选择照片并上传";
                 exit;
             } else {
                 $query = "UPDATE #@__client_addon SET   idpic='$icpic_t'  WHERE clientid='$CLIENTID'; ";
                 $dsql->ExecuteNoneQuery($query);
             }
         }*/


    echo "保存成功";
    exit;
}


//获取客户信息
$questr = "SELECT *  FROM `#@__client`  where  id='$CLIENTID'";
$row = $dsql->GetOne($questr);

//获取客户信息
$questr = "SELECT userName  FROM `#@__client_pw`  where  clientid='$CLIENTID'";
$rowClientPw = $dsql->GetOne($questr);

//获取客户信息
$questr = "SELECT idcard,jfnum,jbnum,idpic  FROM `#@__client_addon`  where  clientid='$CLIENTID'";
$rowClientLy = $dsql->GetOne($questr);


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>我的信息</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext"  >
                我的信息
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                <input type="hidden" name="dopost" value="save"/>

                <div class="form-group">
                    <div class="col-xs-3 control-label">姓名:</div>
                    <?php
                    if ($row['realname'] == "") {
                        echo "<div class=\"col-xs-8 \">
                                        <input type='text' class='form-control' name='realname' id='realname' value='' >
                                  </div>
                               ";
                    } else {
                        echo "<div class=\"col-xs-8 form-control-static\">
                                        {$row['realname']}
                                  </div>
                               ";
                    }
                    ?>
                </div>
                <input type="hidden" name="userName" id="userName" value="<?php /*echo $rowClientPw['userName'] */ ?>">
                <?php if ($row["mobilephone_check"] == 1) { ?>
                    <div class="form-group">
                        <div class="col-xs-3 control-label">手机:</div>
                        <div class="col-xs-6  form-control-static">
                            <?php echo $row['mobilephone']; ?> 已验证
                        </div>
                        <div class="col-sm-3 form-control-static">
                            <a href="../phone_change.php" class="btn btn-primary btn-xs btn-rounded">更换</a>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="form-group">
                        <div class="col-xs-3 control-label">手机:</div>
                        <div class="col-xs-8">
                            <input type='text' class='form-control' name='mobilephone' id='mobilephone' value='<?php echo $row['mobilephone']; ?>'>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="form-group">
                    <div class="col-xs-3 control-label">身份证号:</div>
                    <?php
                    if ($rowClientLy['idcard'] == "") {
                        echo "<div class=\"col-xs-8 \">
                                       <input type=\"text\" class=\"form-control\" name=\"idcard\" id=\"idcard\" value=\"\" >
                                  </div>
                               ";
                    } else {
                        echo "<div class=\"col-xs-8 form-control-static\">
                                        {$rowClientLy['idcard']}
                                  </div>
                               ";
                    }
                    ?>
                </div>


                <div class="form-group">
                    <div class="col-xs-3 control-label">介绍人:</div>
                    <div class="col-xs-8 form-control-static">
                        <?php
                        $sponsorName = getOneCLientRealName($cfg_ml->fields["sponsorid"]);
                        if ($sponsorName == "") {
                            echo "无";
                        } else {
                            echo $sponsorName;
                        } ?>
                    </div>
                </div>


                <div class="form-group">
                    <div class="text-center">
                        <?php if ($row['realname'] == "" || $row["mobilephone_check"] == 0 || $rowClientLy['idcard'] == "" /*|| $photo == ""*/) { ?>
                            <button class="btn btn-primary" type="submit">保存内容</button>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<SCRIPT src="../../ui/js/jquery.lazyload.js" type=text/javascript></SCRIPT>
<SCRIPT src="../../ui/js/jquery.lazyload.plus.js" type=text/javascript></SCRIPT>


<script>
    $().ready(function () {
        $("#form1").validate({
            rules: {
                realname: {required: !0},
                idcard: {required: !0, isIdCardNo: !0}
            },
            messages: {
                realname: {required: "请填写姓名"},
                idcard: {required: "请填写身份证号", isIdCardNo: "请输入正确的18位身份证号"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "myinfo.php",
                    data: {
                        dopost: "save",
                        realname: $("#realname").val(),
                        userName: $("#userName").val(),
                        mobilephone: $("#mobilephone").val(),
                        idcard: $("#idcard").val(),
                        //idpic: $("#idpic").val()
                        idpic: ""
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == '保存成功') {
                            layer.msg('保存成功', {
                                shade: 0.5, //开启遮罩
                                time: 1000 //20s后自动关闭
                            }, function () {
                                window.location.href = 'myinfo.php';
                            });

                        } else {
                            layer.msg(result, {
                                shade: 0.5, //开启遮罩
                                time: 1000 //20s后自动关闭
                            });

                        }
                    }
                });
            }
        })
    });


</script>
</body>
</html>
