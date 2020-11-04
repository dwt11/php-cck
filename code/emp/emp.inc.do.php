<?php
/**
 * 文档处理
 *
 * @version        $Id: emp.inc.do.php 1 8:26 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = isset($id) ? preg_replace("#[^0-9]#", '', $id) : '';


/*--------------------------
 //员工详细信息浏览
 function empview(){ }
 ---------------------------*/
if ($dopost == "empview") {

    //获取主表信息
    $query = "SELECT *
           FROM `#@__emp`
           WHERE emp_id='$id' ";
    //		   //dump($query);
    $row = $dsql->GetOne($query);
    //AjaxHead();

    $empPhoto = "../images/defaultpic.gif";
    if (trim($row['emp_photo']) != "") $empPhoto = $row['emp_photo'];
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>员工详细信息</title>
        <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
        <link href="../ui/css/style.min.css" rel="stylesheet">
        <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
        <style>html {
                height: auto; /*160109 style.min.css将html设置为 {  height: 100%;} ,引起layer自动适应高度时错误 ,再这里将100%屏蔽掉*/
            }
        </style>
    </head>
    <body class="gray-bg" style="min-width: 500px">

    <div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
        <table>
            <tr>
                <td width="30%" class="text-center">
                    <img src="<?php echo $empPhoto ?>" width="180" height="260" align="center">
                </td>
                <td width="10%" class="text-center">
                </td>
                <td width="60%">
                    <div class="table-responsive">

                        <table data-toggle="table" data-striped="true" data-show-header="false">
                            <thead>
                            <tr>
                                <th data-halign="center" data-align="right"></th>
                                <th data-halign="center" data-align="left"></th>
                            </tr>
                            </thead>
                            <tr>
                                <td align="right"><b>编号</b>：</td>
                                <td><?php echo GetIntAddZero($row['emp_code'],3) ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>姓名</b>：</td>
                                <td class='bline'><?php echo $row['emp_realname'] ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>性别</b>：</td>
                                <td><?php echo $row['emp_sex'] ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>出生日期</b>：</td>
                                <td><?php echo date("Y-m-d", strtotime($row['emp_csdate'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right"><b>电话</b>：</td>
                                <td><?php echo $row['emp_mobilephone'] ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>入职日期</b>：</td>
                                <td><?php echo date("Y-m-d", strtotime($row['emp_rzdate'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right"><b>状态</b>：</td>
                                <td><?php echo $row['emp_ste'] ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>当前学历</b>：</td>
                                <td><?php echo $row['emp_dqxl'] ?></td>
                            </tr>

                            <tr>
                                <td align="right" height='26'><b>初始学历</b>：</td>
                                <td><?php echo $row['emp_csxl'] ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right"><b>身份证号</b>：</td>
                                <td><?php echo $row['emp_sfz'] ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>婚姻</b>：</td>
                                <td><?php echo $row['emp_hy'] ?></td>


                            <tr>
                                <td align="right"><b>住址</b>：</td>
                                <td><?php echo $row['emp_add'] ?></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>

    <!--表格-->
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <script>
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
    </script>
    </body>
    </html>
    <?php
} /*--------------------------
     //异步上传员工照片
     function uploadLitpic(){ }
     ---------------------------*/
else if ($dopost == "uploadLitpic") {
    $msg = AdminUpload_plus('litpic', 'imagelit', 0, false, "", "empphoto");
    echo $msg;
    exit();
}

//AJAX获取商品信息  /采购添加  意向客户添加
if($dopost=='GetOneEmpInfo')
{
    $retstr="";
    $query="SELECT emp_realname,emp_mobilephone FROM `#@__emp` WHERE   emp_id = '$emp_id'";
    $row = $dsql->GetOne($query);
    if(is_array($row)){
        $retstr=json_encode($row);
    }
    echo $retstr;
}














