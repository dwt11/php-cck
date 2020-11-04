<?php
/**
 * 添加
 *
 * @version        $Id: spec_add.php 1 16:22 20日
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
else if ($dopost == 'save') {
    //timeset tagname typeid normbody expbody
    //$tagname = trim($tagname);

    /*多公司时如何检测员工编号重复,要考虑一下151223????
    $questr="SELECT emp_code FROM `#@__emp` WHERE emp_isdel=0 and emp_code =".$emp_code;
                ////dump($questr);
                $rowarc = $dsql->GetOne($questr);
        if(is_array($rowarc))
        {
            ShowMsg("已经存在此员工编号！","-1");
            exit();
        }
    */        //ShowMsg($crm_id,"crm.php");
    ////dump($crm_id);


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
    if ($emp_code == "" || $emp_realname == "" || $emp_mobilephone=="") {
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
    $query = "
     INSERT INTO `#@__emp` (`emp_code`, `emp_realname`, `emp_sfz`, `emp_csdate`, `emp_mobilephone`, `emp_sex`, `emp_ste`, `emp_rzdate`, `emp_lzdate`, `emp_update`, `emp_csxl`, `emp_dqxl`, `emp_dep`, `emp_isdel`, `emp_photo`, `emp_hy`, `emp_add`)
	                  VALUES ('$emp_code', '$emp_realname', '$emp_sfz', '$emp_csdate', '$emp_mobilephone', '$emp_sex', '$emp_ste', '$emp_rzdate', null, '$emp_update', '$emp_csxl', '$emp_dqxl', '$emp_dep',   '0', '$emp_photo ','$emp_hy', '$emp_add');
    ";

    //dump( $query);


    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg("添加数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
    ShowMsg("添加员工信息成功！", $$ENV_GOBACK_URL);
    exit();
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
                    <form id="empadd" name="form1" action="" method="post" enctype="multipart/form-data" class="form-horizontal"  >
                        <input type="hidden" name="dopost" value="save"/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">员工编号:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_code" id="emp_code" value="<?php echo $emp_code; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">姓名:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_realname" id="emp_realname">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">部门:</label>

                            <div class="col-sm-2">
                                <select class="form-control m-b" name='emp_dep' id='emp_dep'>
                                    <option value='0'>请选择部门...</option>
                                    <?php
                                    $depOptions = GetDepOptionListRole();
                                    echo $depOptions;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">性别:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="男" id="RadioGroup1_0" name="emp_sex" checked="checked">男</label>
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="女" id="RadioGroup1_1" name="emp_sex">女</label>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">手机:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_mobilephone">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">住址:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_add">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">入职日期:</label>


                            <div class="col-sm-2" style="width: 130px">
                                <?php
                                $nowtime = GetDateMk(time());
                                ?>

                                <input type="text" name="emp_rzdate" class="form-control  Wdate " size="14" value="<?php echo $nowtime; ?>"  onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">出生日期:</label>


                            <div class="col-sm-2" style="width: 130px">
                                <?php
                                $nowtime = date("Y-m-d", time() - 630720000); ?>
                                <input type="text" name="emp_csdate" class="form-control Wdate " size="14" value="<?php echo $nowtime; ?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">状态:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="在职" id="RadioGroup1_0" name="emp_ste" checked="checked">在职</label>
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="离职" id="RadioGroup1_1" name="emp_ste">离职</label>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">身份证号:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="emp_sfz" size="18">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">初始学历:</label>

                            <div class="col-sm-6">
                                <?php echo GetEnumsForm('education', '大学专科', 'emp_csxl', $seltitle = '') ?>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">当前学历:</label>

                            <div class="col-sm-6">
                                <?php echo GetEnumsForm('education', '大学专科', 'emp_dqxl', $seltitle = '') ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">婚姻:</label>

                            <div class="col-sm-6">
                                <?php echo GetEnumsForm('marital', '未婚', 'emp_hy', $seltitle = '') ?>

                            </div>
                        </div>



                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2 ">
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








</body>
</html>
