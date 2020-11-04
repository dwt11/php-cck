<?php
/**
 * 重复购买次数  0不限制
 * 只 使用
 * @version        $Id: .php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
if (empty($dopost)) $dopost = '';
if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
if ($dopost == 'save') {

/*    $lineids = $id;
    $lineid_array = explode(",", $lineids);

    foreach ($lineid_array as $id) {*/
        $inQuery = "UPDATE `#@__goods` SET 
                `onlynumb`='$onlynumb'
               where id ='$id'
                ";

        if (!$dsql->ExecuteNoneQuery($inQuery)) {
            ShowMsg("更新数据时出错，请检查原因！", "-1");
            exit();
        }
  /*  }*/
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("更新信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {



    $lineids = $id;
    $lineid_array = explode(",", $lineids);
    $lineid=$lineid_array[0];//默认取第一个,

    //读取归档信息
    $arcQuery = "SELECT onlynumb  FROM #@__goods  WHERE id='$id' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        ShowMsg("读取信息出错!", "-1");
        exit();
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
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">

    <form id="zhishuadd" name="form1" action="" method="post" class="form-horizontal" target="_parent">

        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>


        <div class="form-group">
            <label   style="float: left">每个乘车卡,每天只可预约一次。<br>此数量限制乘车卡非当日情况下可重复预约次数(0次不限制)</label>


        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">重复购买次数:</label>
            <div class=" col-sm-1">
                <?php
                $onlynumb=0;
                if(isset($arcRow['onlynumb'])&&$arcRow['onlynumb']>0){
                    $onlynumb=$arcRow['onlynumb'];
                }
                ?>
                <input type="number" class="form-control" name="onlynumb" id="onlynumb" value="<?php echo $onlynumb; ?>">
            </div>

        </div>


        <div class="hr-line-dashed"></div>

        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>

    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/validate/messages_zh.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {


    });
</script>
</body>
</html>
