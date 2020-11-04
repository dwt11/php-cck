<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
CheckRank();

if (empty($orderlistid)) $orderlistid = '';
if (empty($dopost)) $dopost = '';

if ($orderlistid == '') {
    ShowMsg("获取参数出错!", "-1");
    exit();
}
$query = "SELECT #@__order_addon_ztc.* FROM #@__order_addon_ztc
          LEFT JOIN #@__order ON #@__order.id=#@__order_addon_ztc.orderid
          WHERE   #@__order_addon_ztc.id='$orderlistid'  AND 
          (#@__order.clientid=$CLIENTID)";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}


if ($dopost == 'save') {
    //dump($idpic);
    //这里要调试
    $icpic_t = SaveWeixinPicUploadService_NEW($DEPID, $idpic, $CLIENTID);
    if (!$icpic_t) {
        echo "照片保存失败，请重新选择照片并上传";
        exit;
    } else {
        //更新乘车卡的照片
        $query = "UPDATE #@__order_addon_ztc SET idpic='$icpic_t' ,idpic_desc=''    WHERE id='$orderlistid'; ";
        $dsql->ExecuteNoneQuery($query);
    }
    echo "保存成功";
    exit;
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>上传乘车卡照片</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>

<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-12 text-left lefttext">
                上传乘车卡照片
            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content text-center">
            <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                <input id="id" name="id" type="hidden" value='<?php echo $orderlistid; ?>'>

                <A href="javascript:;" onclick="showpic()" class="btn btn-warning">照片示例</A>
                <br><br><br><br>
                请上传<b>清晰、正面</b>日常生活照片
                <br>


                <?php
                echo UploadWeixinPicForm($DEPID,$formIdName="idpic");
                ?>
            </form>

        </div>
    </div>
</div>

<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>


<script type="text/javascript" charset="utf-8">
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

</script>
<script>

    function showpic() {
        layer.open({
            type: 1,
            title: false, //不显示标题栏
            closeBtn: true,
            area: '260px',
            offset: '30px',
            shade: 0.8,
            id: 'LAY_layuipro', //设定一个id，防止重复弹出
            resize: false,
            btnAlign: 'c',
            moveType: 1, //拖拽模式，0或者1
            content: '<div style="padding: 4%; line-height: 22px; font-size: 14px; background-color: #393D49; color: #fff; " class="text-center"><div >拍摄或者选择一张<br>本人正面清晰照片<br>（不可有遮挡物或佩戴墨镜）</div><div style="margin: 2%;"> <img src="/images/bsz.jpg" style="max-width: 100%; "> </div></div>'
        });
    }


</script>
<script>
    $().ready(function () {
        $("#form1").validate({
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "ztcCard_uploadidpic.php",
                    data: {
                        dopost: "save",
                        idpic: $("#idpic").val(),
                        orderlistid: $("#id").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == '保存成功') {
                            layer.msg('保存成功', {
                                time: 1000 //20s后自动关闭
                            }, function () {
                                //这里要调试
                                 window.location.href = 'ztcCard.php';
                            });
                        } else {
                            layer.msg(result, {
                                time: 3000 //20s后自动关闭
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
