<?php
/**
 * 微信参数编辑
 *
 * @version        $Id: sysGroup_edit.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */

require_once("../config.php");
require_once(DWTINC . "/request.class.php");


if (empty($dopost)) $dopost = '';


if (!isset($id)) {
    ShowMsg("参数出错!", "-1");
    exit();
}


/**
 * 判断二级菜单是否有内容
 *
 * @param $oneI  主菜单的元素ID
 */
function isson($oneI)
{
    $retrun_bool = false;
    for ($twoI = 0; $twoI < 5; $twoI++) {
        $name_var_two_t = "name_2_" . $oneI . "_" . $twoI;
        $name_two_t = $$name_var_two_t;
        $url_var_two_t = "url_2_" . $oneI . "_" . $twoI;
        $url_two_t = $$url_var_two_t;

        if ($name_two_t != "" && $url_two_t != "") {
            $retrun_bool = true;
            break;
        }
    }
    return $retrun_bool;
}

/*保存菜单*/
if ($dopost == 'save') {
    /*    if ($name == "") {
            ShowMsg("请填写菜单显示名称", "-1");
            exit();
        }
        $typeValue = "";
        if ($isParameter && $type == "view" && $url == "") {
            ShowMsg("请填写链接URL", "-1");
            exit();
        }
        if ($isParameter && $type == "view") $typeValue = $url;

        if ($isParameter && $type == "click" && $key == "") {
            ShowMsg("请填写关键字", "-1");
            exit();
        }
        if ($isParameter && $type == "click") $typeValue = $key;

        $typeName = "";
        if ($isParameter) $typeName = $type;*/

    $err_str = "";


    //清空原菜单
    $sql = "DELETE FROM `#@__interface_weixin_menu` WHERE wid='$id' ;";
    $dsql->ExecuteNoneQuery($sql);


    for ($oneI = 0; $oneI < 3; $oneI++) {
        $type = "view";//无子菜单 则主菜单有连接
        $isson = false;//是否有主菜单
        //判断是否有子菜单
        for ($twoI = 0; $twoI < 5; $twoI++) {
            $name_var_two_t = "name_2_" . $oneI . "_" . $twoI;
            $name_two_t = $$name_var_two_t;
            $url_var_two_t = "url_2_" . $oneI . "_" . $twoI;
            $url_two_t = $$url_var_two_t;
            if ($name_two_t != "" && $url_two_t != "") {
                $type = "click";//有子菜单则 主菜单无连接
                $isson = true;
                break;
            }
        }

        $name_var = "name_1_" . $oneI;
        $name = $$name_var;
        $url_var = "url_1_" . $oneI;
        $url = $$url_var;
        $disorder_var = "disorder_1_" . $oneI;
        $disorder = $$disorder_var;


        //如果有子菜单 则执行下面的
        //dump($isson);
        if ($isson) {
            if ($name != "") {
                $url = "";
                $one_sql = "INSERT INTO `#@__interface_weixin_menu` ( `wid`, `name`, `reid`, `disorder`, `type`, `typeValue`)
              VALUES ('$id', '$name', '0', '$disorder', '$type', '$url');";
                $dsql->ExecuteNoneQuery($one_sql);
                $oneid = $dsql->GetLastID();
                // dump("ONE " . $one_sql);
                for ($twoI = 0; $twoI < 5; $twoI++) {
                    $name_var_two = "name_2_" . $oneI . "_" . $twoI;
                    $name_two = $$name_var_two;
                    $url_var_two = "url_2_" . $oneI . "_" . $twoI;
                    $url_two = $$url_var_two;
                    $disorder_var_two = "disorder_2_" . $oneI . "_" . $twoI;
                    $disorder_two = $$disorder_var_two;
                    $type_two = "view";//连接

                    if (($name_two != "" && $url_two == "") || ($name_two == "" && $url_two != "")) {
                        $err_str .= "第" . ($oneI + 1) . "-" . ($twoI + 1) . " 子菜单，名称和连接必须同时输入<br>";
                    } else if ($name_two != "" && $url_two != "") {
                        $two_sql = "INSERT INTO `#@__interface_weixin_menu` ( `wid`, `name`, `reid`, `disorder`, `type`, `typeValue`)
                      VALUES ('$id', '$name_two', '$oneid', '$disorder_two', '$type_two', '$url_two');";
                        $dsql->ExecuteNoneQuery($two_sql);
                        //    dump("two " . $two_sql);
                    }
                }
            } else {
                $err_str .= "第" . ($oneI + 1) . "主菜单，必须输入名称<br>";
            }

        }
        if (!$isson) {
            //如果 没有子菜单
            if ($url != "" && $name != "") {
                $one_sql = "INSERT INTO `#@__interface_weixin_menu` ( `wid`, `name`, `reid`, `disorder`, `type`, `typeValue`)
              VALUES ('$id', '$name', '0', '$disorder', '$type', '$url');";
                $dsql->ExecuteNoneQuery($one_sql);
                //dump("ONE " . $one_sql);
            } else if ($url != "" && $name == "") {
                $err_str .= "第" . ($oneI + 1) . "主菜单，没有子菜单，必须输入名称和连接地址<br>";
            } else if ($url == "" && $name != "") {
                $err_str .= "第" . ($oneI + 1) . "主菜单，没有子菜单，必须输入名称和连接地址<br>";
            }
        }
    }


    //dump($err_str);
    /* $inQuery = "UPDATE `x_interface_weixin_menu` SET `name`='$name',  `type`='$typeName', `typeValue`='$typeValue' WHERE (`id`='$id')";
     if (!$dsql->ExecuteNoneQuery($inQuery)) {
         ShowMsg("更新数据时出错，请检查原因！", "-1");
         exit();
     }
*/
    if ($err_str == "") {
        $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
        ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    } else {
        ShowMsg("修改信息有错误，请检查！<br>" . $err_str, -1);
    }
    exit();
}


/*从数据库获取微信菜单*/
$query = "SELECT * FROM `#@__interface_weixin_menu` WHERE wid=$id   ORDER BY   disorder aSC, id ASC";
$dsql->Execute('me', $query);
$oneMenu_array = $twoMenu_array  = array();
while ($row = $dsql->getarray()) {
    if ($row['reid'] == 0) {
        $oneMenu_array[] = $row;
    } else {
        $twoMenu_array[$row['reid']][] = $row;
    }
}
/*从数据库获取微信菜单*/


/*分隔菜单用于显示 */
$display_array = $oneMenu_array;


//dump($twoMenu_array);
$temp_i = 0;
foreach ($display_array as $key => $display) {
    //遍历主菜单
    $son = "";
    for ($twoI = 0; $twoI < 5; $twoI++) {
        $name = $url = $disorder = "";
        //遍历子菜单查找当前主菜单的子菜单
        $onekey=$display_array[$key]['id'];
        if (isset($twoMenu_array[$onekey][$twoI])) {
            $name = $twoMenu_array[$onekey][$twoI]['name'];
            $url = $twoMenu_array[$onekey][$twoI]['typeValue'];
            $disorder = $twoMenu_array[$onekey][$twoI]['disorder'];
        }

        $xuhao_one = $temp_i + 1;
        $xuhao_two = $twoI + 1;

        $son .= ' <tr>
                           <td>' . $xuhao_one . '-' . $xuhao_two . '
                                                                       </td>
                                                                                 <td>
                                            <div class="col-sm-4" style="padding-left: 40px">
                                                <input type="text" class="form-control" name="name_2_' . $temp_i . '_' . $twoI . '"  id="name_2_' . $temp_i . '_' . $twoI . '" value="' . $name . '">
                                            </div>
                                        </td>
                                        <td>
                                                <div id="view_1_' . $twoI . '" class="col-sm-10" style="padding-left: 40px">
                                                    <input type="text" class="form-control" name="url_2_' . $temp_i . '_' . $twoI . '"  id="url_2_' . $temp_i . '_' . $twoI . '" value="' . $url . '">
                                                </div>
                                        </td>
                                        <td >
                                        <div  style="padding-left: 40px">
                                                <input type="text" value="' . $disorder . '" name="disorder_2_' . $temp_i . '_' . $twoI . '"  id="disorder_2_' . $temp_i . '_' . $twoI . '" class="form-control" style="max-width: 50px"/>
                                                </div>
                                        </td>
                                        <td>
                                                                            <a href="javascript:clear(\'2_' . $temp_i . '_' . $twoI . '\')" ">清空</a>
                                        </td>
                        </tr>';
    }
    $temp_i++;
    $display_array[$key]['son'] = $son;
}
/*分隔菜单用于显示 */



?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?></h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">

                    <div class="alert alert-warning alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        1、如果子菜单包含内容，则主菜单的连接将失效。<br>
                        2、不用的菜单请清空。
                    </div>


                    <!--表格数据区------------开始-->
                    <form name='form2' id='form2' action='' method='post' class="form-horizontal m-t">
                        <input type="hidden" name="dopost" value="save">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">

                        <div class="table-responsive">
                            <table id="datalist" data-toggle="table" data-classes="table table-hover table-condensed" data-striped="true" data-sort-order="desc" data-mobile-responsive="true">
                                <thead>
                                <tr>
                                    <th align="center" data-halign="center" data-align="center">序号</th>
                                    <th align="center" data-halign="center" data-align="left">菜单显示名称</th>
                                    <th align="center" data-halign="center" data-align="left">连接地址</th>
                                    <th align="center" data-halign="center" data-align="center">排序</th>
                                    <th align="center" data-halign="center" data-align="center">操作</th>
                                </tr>
                                </thead>
                                <?php

                                $JS_str = "";
                                // dump($display_array);
                                for ($oneI = 0; $oneI < 3; $oneI++) {
                                    $name = $url = $disorder = $son = "";
                                    if (isset($display_array[$oneI])) {
                                        $name = $display_array[$oneI]['name'];
                                        $url = $display_array[$oneI]['typeValue'];
                                        $disorder = $display_array[$oneI]['disorder'];
                                        $son = $display_array[$oneI]['son'];
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $oneI + 1; ?></td>
                                        <td>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" name="name_1_<?php echo $oneI; ?>" id="name_1_<?php echo $oneI; ?>" value="<?php echo $name ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div id="view_1_<?php echo $oneI; ?>" class="col-sm-10">
                                                <input type="text" class="form-control" name="url_1_<?php echo $oneI; ?>" id="url_1_<?php echo $oneI; ?>" value="<?php echo $url; ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" value="<?php echo $disorder; ?>" name="disorder_1_<?php echo $oneI; ?>" id="disorder_1_<?php echo $oneI; ?>" class="form-control" style="max-width: 50px"/>
                                        </td>
                                        <td>
                                            <a href="javascript:clear('1_<?php echo $oneI; ?>')" ">清空</a>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($son != "") {
                                        echo $son;
                                    } else {
                                        $son_str = "";
                                        for ($twoI = 0; $twoI < 5; $twoI++) {
                                            $name = $url = $disorder = "";
                                            $xuhao_one = $oneI + 1;
                                            $xuhao_two = $twoI + 1;

                                            $son_str .= ' <tr>
                                                                    <td>' . $xuhao_one . '-' . $xuhao_two . '
                                                                       </td>
                                                                    <td>
                                                                        <div class="col-sm-4" style="padding-left: 40px">
                                                                            <input type="text" class="form-control"   id="name_2_' . $oneI . '_' . $twoI . '"  name="name_2_' . $oneI . '_' . $twoI . '" value="' . $name . '">
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                            <div id="view_1_' . $twoI . '" class="col-sm-10" style="padding-left: 40px">
                                                                                <input type="text" class="form-control" name="url_2_' . $oneI . '_' . $twoI . '"  id="url_2_' . $oneI . '_' . $twoI . '" value="' . $url . '">
                                                                            </div>
                                                                    </td>
                                                                    <td >
                                                                    <div  style="padding-left: 40px">
                                                                            <input type="text" value="' . $disorder . '" name="disorder_2_' . $oneI . '_' . $twoI . '"  id="disorder_2_' . $oneI . '_' . $twoI . '" class="form-control" style="max-width: 50px"/>
                                                                            </div>
                                                                    </td>
                                                                    <td>
                                                                            <a href="javascript:clear(\'2_' . $twoI . '\')" ">清空</a>
                                                                    </td>
                                                    </tr>';
                                        }
                                        echo $son_str;
                                    }
                                }
                                ?>
                            </table>

                            <div style=" text-align: center; margin-top: 10px">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>
                    </form>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>
    </div>

</div>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--表格-->
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="../ui/js/bootstrap-table.js"></script>
<!--表格-->
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    $().ready(function () {
        $("#form2").validate({
            rules: {
                url_1_0: {url: !0},
                url_1_1: {url: !0},
                url_1_2: {url: !0},
                url_2_0_0: {url: !0},
                url_2_0_1: {url: !0},
                url_2_0_2: {url: !0},
                url_2_0_3: {url: !0},
                url_2_0_4: {url: !0},
                url_2_1_0: {url: !0},
                url_2_1_1: {url: !0},
                url_2_1_2: {url: !0},
                url_2_1_3: {url: !0},
                url_2_1_4: {url: !0},
                url_2_2_0: {url: !0},
                url_2_2_1: {url: !0},
                url_2_2_2: {url: !0},
                url_2_2_3: {url: !0},
                url_2_2_4: {url: !0}
            },
            messages: {
                url_1_0: {url: "网址格式不正确,应为:http://www.163.com"},
                url_1_1: {url: "网址格式不正确,应为:http://www.163.com"},
                url_1_2: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_0_0: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_0_1: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_0_2: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_0_3: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_0_4: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_1_0: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_1_1: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_1_2: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_1_3: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_1_4: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_2_0: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_2_1: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_2_2: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_2_3: {url: "网址格式不正确,应为:http://www.163.com"},
                url_2_2_4: {url: "网址格式不正确,应为:http://www.163.com"}
            }
        })
    });

    /*清除行内容*/
    function clear(id) {
        $("#name_" + id).val("");
        $("#url_" + id).val("");
        $("#disorder_" + id).val("");

    }


    //下载
    function download1(wid) {
        layer.confirm('确认下载吗？<br>下载后,会覆盖当前数据库中的信息!', {
            icon: 3,
            title: '提示'
        }, function (index) {
            location.href = "weixinMenu.php?dopost=down&wid=" + wid;
        });
    }

    //上传到微信
    function upload(wid) {
        layer.confirm('确认上传吗？<br>上传后,微信中的菜单将在24小时内对所有用户生效!', {
            icon: 3,
            title: '提示'
        }, function (index) {
            location.href = "weixinMenu.php?dopost=upload&wid=" + wid;
        });
    }

</script>
</body>
</html>