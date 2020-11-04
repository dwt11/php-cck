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
require_once( DWTINC . '/enums.func.php');  //获取联动枚举表单
if (empty($dopost)) $dopost = '';

/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {


    $emp_code = isset($emp_code) ? trim($emp_code) : $emp_code = "";
    $emp_realname = isset($emp_realname) ? trim($emp_realname) : $emp_realname = "";
    $emp_csdate = isset($emp_csdate) ? trim($emp_csdate) : $emp_csdate = "";
    $emp_mobilephone = isset($emp_mobilephone) ? trim($emp_mobilephone) : $emp_mobilephone = "";
    $emp_add = isset($emp_add) ? trim($emp_add) : $emp_add = "";
    $emp_sex = isset($emp_sex) ? trim($emp_sex) : $emp_sex = "";
    $emp_ste = isset($emp_ste) ? trim($emp_ste) : $emp_ste = "";
    $emp_rzdate = isset($emp_rzdate) ? trim($emp_rzdate) : $emp_rzdate = "";
    //$emp_lzdate = isset($emp_lzdate) ? trim($emp_lzdate) : $emp_lzdate = "";
    $emp_update = date("Y-m-d", time());
    $emp_csxl = isset($emp_csxl) ? trim($emp_csxl) : $emp_csxl = "";
    $emp_dqxl = isset($emp_dqxl) ? trim($emp_dqxl) : $emp_dqxl = "";
    $emp_dep = isset($emp_dep) ? trim($emp_dep) : $emp_dep = "";
    if ($emp_code == "" || $emp_realname == "" || $emp_mobilephone == "") {
        ShowMsg("员工编号 姓名 手机不能为空 请检查！", "-1");
        exit;
    }
    //如果手机号修改了,判断是否重复
    if ($emp_mobilephone != $emp_old_mobilephone) {
        $questr = "SELECT emp_mobilephone FROM `#@__emp` WHERE emp_isdel=0 and emp_mobilephone ='$emp_mobilephone'";
        $rowarc = $dsql->GetOne($questr);
        if (is_array($rowarc)) {
            ShowMsg("填写的手机号重复,请检查！", "-1");
            exit();
        }
    }

    /*    if (file_exists("worktype.php")) {//有工种的文件功能,才显示
            $emp_worktype = isset($emp_worktype) ? trim($emp_worktype) : $emp_code = "";
        }else{
            $emp_worktype = "";
        }*/
    $emp_photo = isset($picname) ? trim($picname) : $emp_photo = "";
    $emp_hy = isset($emp_hy) ? trim($emp_hy) : $emp_hy = "";


    if ($emp_photo == "") {//如果照片为空则不更新照片
        //更新
        $inQuery = "UPDATE `#@__emp` SET
					emp_code='$emp_code',
					emp_realname='$emp_realname',
					emp_sfz='$emp_sfz',
					emp_csdate='$emp_csdate',
					emp_mobilephone='$emp_mobilephone',
					emp_sex='$emp_sex',
					emp_ste='$emp_ste',
					emp_rzdate='$emp_rzdate',
					emp_update='$emp_update',
					emp_csxl='$emp_csxl',
					emp_dqxl='$emp_dqxl',
					emp_dep='$emp_dep',
					emp_hy='$emp_hy',
					emp_add='$emp_add'   WHERE (`emp_id`='$emp_id')";
    } else {
        $inQuery = "UPDATE `#@__emp` SET
                    emp_code='$emp_code',
                    emp_realname='$emp_realname',
                    emp_sfz='$emp_sfz',
                    emp_csdate='$emp_csdate',
                    emp_mobilephone='$emp_mobilephone',
                    emp_sex='$emp_sex',
                    emp_ste='$emp_ste',
                    emp_rzdate='$emp_rzdate',
                    emp_update='$emp_update',
                    emp_csxl='$emp_csxl',
                    emp_dqxl='$emp_dqxl',
                    emp_dep='$emp_dep',
                    emp_photo='$emp_photo',
                    emp_hy='$emp_hy',
                    emp_add='$emp_add'  WHERE (`emp_id`='$emp_id')
                    ";

    }
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
    ShowMsg("修改员工信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {

    //require_once(DWTPATH . "/emp/worktype.inc.options.php");

    //读取归档信息
    $arcQuery = "SELECT *  FROM #@__emp  WHERE emp_id='$emp_id' ";
    //dump($arcQuery);
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

    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
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
                    <form id="empadd" name="form1" action="" method="post" enctype="multipart/form-data" class="form-horizontal" >
                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">员工编号:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_code" value="<?php echo GetIntAddZero($arcRow['emp_code'], 3); ?>"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">姓名:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_realname" value="<?php echo $arcRow['emp_realname']; ?>"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">部门:</label>

                            <div class="col-sm-2">
                                <select class="form-control m-b" name='emp_dep' id='emp_dep'>
                                    <option value='0'>请选择部门...</option>
                                    <?php
                                    $depOptions = GetDepOptionListRole($arcRow['emp_dep']);
                                    echo $depOptions;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">性别:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input name="emp_sex" type="radio" id="RadioGroup1_0" value="男" <?php if ($arcRow['emp_sex'] == '男') echo "checked='checked'"; ?> /> 男
                                </label>
                                <label class="checkbox-inline i-checks">
                                    <input name="emp_sex" type="radio" id="RadioGroup1_1" value="女" <?php if ($arcRow['emp_sex'] == '女') echo "checked='checked'"; ?> /> 女
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">手机:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_mobilephone" value="<?php echo $arcRow['emp_mobilephone']; ?>"/>
                                <input type="hidden"  name="emp_old_mobilephone" value="<?php echo $arcRow['emp_mobilephone']; ?>"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">住址:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_add" value="<?php echo $arcRow['emp_add']; ?>"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">入职日期:</label>


                            <div class="col-sm-2" style="width: 130px">
                                <?php
                                if ($arcRow['emp_rzdate'] == "") {
                                    $nowtime = date("Y-m-d", time());
                                } else {
                                    $nowtime = date("Y-m-d", strtotime($arcRow['emp_rzdate']));
                                }
                                ?>
                                <input type="text" name="emp_rzdate" class="form-control Wdate " size="14" value="<?php echo $nowtime; ?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">出生日期:</label>


                            <div class="col-sm-2" style="width: 130px">
                                <?php
                                if ($arcRow['emp_csdate'] == "") {
                                    $nowtime = GetDateMk(time());
                                } else {
                                    $nowtime = GetDateMk(strtotime($arcRow['emp_csdate']));
                                }
                                ?>
                                <input type="text" name="emp_csdate" class="form-control Wdate " size="14" value="<?php echo $nowtime; ?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">状态:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input name="emp_ste" type="radio" id="RadioGroup1_0" value="在职" <?php if ($arcRow['emp_ste'] == '在职') echo "checked='checked'"; ?> /> 在职
                                </label>
                                <label class="checkbox-inline i-checks">
                                    <input name="emp_ste" type="radio" id="RadioGroup1_1" value="离职" <?php if ($arcRow['emp_ste'] == '离职') echo "checked='checked'"; ?> /> 离职
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">身份证号:</label>

                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="emp_sfz" size="18" value="<?php echo $arcRow['emp_sfz']; ?>"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">初始学历:</label>

                            <div class="col-sm-6">
                                <?php echo GetEnumsForm('education', $arcRow['emp_csxl'], 'emp_csxl', $seltitle = '') ?>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">当前学历:</label>

                            <div class="col-sm-6">
                                <?php echo GetEnumsForm('education', $arcRow['emp_dqxl'], 'emp_dqxl', $seltitle = '') ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">婚姻:</label>

                            <div class="col-sm-6">
                                <?php
                                echo GetEnumsForm('marital', $arcRow['emp_hy'], 'emp_hy', $seltitle = '')
                                ?>
                            </div>
                        </div>
                        <!--<div class="form-group">
                            <label class="col-sm-2 control-label">(不修改请留空)照片:</label>

                            <div class="col-sm-2">
                                <input name="picname" type="text" id="picname" class="form-control " readonly/>
                                <iframe name='uplitpicfra' id='uplitpicfra' src='' style='display: none'></iframe>
								<span class="litpic_span"> 
								<input name="litpic" type="file" id="litpic" onChange="SeePicNew(this, 'divpicview', 'uplitpicfra', 165, 'emp.inc.do.php','uploadLitpic');" size="1"/>
                                      </span>

                                <div id='divpicview' class='divpre'>
                                    <?php
/*                                    if ($arcRow['emp_photo'] != "") echo "<img src='" . $arcRow['emp_photo'] . "'  title='点击查看大图' style='cursor:pointer' width='150'    height='120' onclick=\"javascript:window.open(this.src);\"/>";
                                    */?>
                                </div>
                            </div>
                        </div>-->


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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>

<!--照片上传-->
<script src="../js/dedeajax2.js"></script>
<script src="../js/seeUploadPic.js"></script>
<script>


    $().ready(function () {
        $("#empadd").validate({
            rules: {
                emp_code: {required: !0},
                emp_realname: {required: !0},
                emp_mobilephone: {required: !0, minlength: 11, isMobile: !0},
                emp_dep: {isIntGtZero: !0}
            },
            messages: {
                emp_code: {required: "请填写员工编号"},
                emp_realname: {required: "请填写员工姓名"},
                emp_mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "请正确填写您的手机号码"},
                emp_dep: {isIntGtZero: "请选择公司"}
            }
        })
    });
</script>


<!--照片上传-->


</body>
</html>
