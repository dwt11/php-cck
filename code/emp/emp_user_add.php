<?php
/**
 * 添加系统管理员
 *
 * @version        $Id: sys_user_add.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once("../sys/sysGroup.class.php");


if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {
    if (preg_match("#[^0-9a-zA-Z_@!\.-]#", $userName)) {
        ShowMsg('用户名不合法，<br />请使用[0-9a-zA-Z_@!.-]内的字符！', '-1', 0, 3000);
        exit();
    }
    $pwd = trim($pwd);

    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg('密码不合法，<br />请使用[0-9a-zA-Z_@!.-]内的字符！', '-1', 0, 3000);
        exit();
    }


    //$safecodeok = substr(md5($cfg_cookie_encode . $randcode), 0, 24);
    if ($safecode != $randcode) {
        ShowMsg('请填写正确的安全验证串！', '-1', 0, 3000);
        exit();
    }
    $mpwd = md5($pwd);
    $pwd = substr(md5($pwd), 5, 20);

    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__sys_admin` WHERE userName LIKE '$userName' ");
    if ($row['dd'] > 0) {
        ShowMsg('用户登录名已存在！', '-1');
        exit();
    }


    if (isset($usertypes)) {
        $usertype = join(',', $usertypes);
    } else {
        $usertype = "";
        // ShowMsg('请选择用户组！','-1');
        // exit();
    }


//后台管理员
    $inquery = "INSERT INTO `#@__sys_admin`(usertype,userName,pwd,empid)
                                                    VALUES('$usertype','$userName','$pwd','$emp_id'); ";
//dump($inquery);
    $rs = $dsql->ExecuteNoneQuery($inquery);
    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");

    ShowMsg('成功添加用户登录信息！', $$ENV_GOBACK_URL);
    exit();
}
$randcode = mt_rand(10000, 99999);
$randcode = substr(md5($cfg_cookie_encode . $randcode), 0, 24);
//$typeOptions = '';


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
                    <form name="form1" id="form1" action="emp_user_add.php" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户登录名:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="userName">
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户密码:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="pwd" onfocus="this.type='password'" id="pwd">
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">确认密码:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="pwd1" onfocus="this.type='password'" id="pwd1" placeholder="请再次确认密码">
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户组:</label>

                            <div class="col-sm-6">
                                <select class="form-control" multiple="" size='5' name="usertypes[]" data-toggle='tooltip' data-placement='top' title='按键盘上的 Ctrl 键 可以进行多选'>
                                    <?php
                                    echo "<option value=''>无</option>\r\n";
                                    $wheresql = "";
                                    $usertype = $CUSERLOGIN->getUserType();//当前登录用户的权限
                                    if ($GLOBAMOREDEP && ($usertype == 9||$usertype == 10)) {//如果包含多级部门,并且当前登录的是部门管理员 则权限组选项只能在当前公司内
                                        $wheresql = "where  t.depid='$NOWLOGINUSERTOPDEPID'";
                                    }
                                    $sql = "SELECT * FROM #@__sys_admintype t $wheresql ORDER BY rank asc";
                                    $dsql->SetQuery($sql);
                                    //dump($sql);

                                    $dsql->Execute("ut");
                                    while ($myrow = $dsql->GetObject("ut")) {
                                        echo "<option value='" . $myrow->rank . "'>" . $myrow->rank . " " . $myrow->typename . "</option>\r\n";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">员工姓名:</label>

                            <div class="col-sm-2" style="width: 100px">
                                    <p  class='form-control-static'>
                                <?php
                                //搜索不在用户表里的员工
                                $myrow=$dsql->GetOne("SELECT emp_id,emp_realname FROM #@__emp emp where emp_id=" . $emp_id . " and emp_isdel=0 and (select count(1) as num FROM #@__sys_admin admin where admin.empid = emp.emp_id) = 0 ORDER BY convert(emp_realname using gbk) asc");


                                    echo $myrow["emp_realname"];

                                ?>
                                    </p>
                                <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">安全验证串:</label>

                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="safecode">
                                <input name="randcode" type="hidden" value="<?php echo $randcode; ?>"/>
				(复制本代码： <font color='red'><?php echo $randcode; ?></font>)


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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>
<script src="sysGroup.js"></script>
<script language='javascript'>

    $().ready(function () {
        $("#form1").validate({
            rules: {
                userName: {required: !0, minlength: 4},
                pwd: {required: !0, minlength: 6},
                pwd1: {required: !0, minlength: 6, equalTo: "#pwd"},
                safecode: {required: !0}
            },
            messages: {
                userName: {required: "请填写用户登录名", minlength: "用户登录名必须大于4个字符"},
                pwd: {required: "请填写密码", minlength: "密码必须6个字符以上"},
                pwd1: {required: "请再次确认新密码", minlength: "密码必须6个字符以上", equalTo: "两次填写的密码不一致"},
                safecode: {required: "请填写安全验证串"}
            }
        })
    });


</script>
</body>
</html>





