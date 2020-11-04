<?php
/**
 *
 * @version        $Id: file_manage_main.php 1 8:48 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once("sysFunction.class.php");

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

if (empty($dopost)) $dopost = '';

// 如果超级管理员则可以查询别的公司的菜单，子公司管理员 则只能查看自己公司的
if ($CUSERLOGIN->getUserType() == 10) {
    if ($GLOBAMOREDEP) {
        if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
    } else {
        if (empty($depid)) $depid = "0";
    }
} else if ($CUSERLOGIN->getUserType() == 9) {
    $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    ShowMsg("无效参数！", "-1");
    exit();
}
if ($dopost == 'del') {
    if ($id == "") {
        ShowMsg("无效参数！", "-1");
        exit();
    }


    //2检测是否有子功能
    $questr = "SELECT topid FROM `#@__sys_function` where  topid='$id'";
    //echo $query;
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        ShowMsg("删除失败,请先删除包含的子功能！", "-1");
        exit();
    }

    //1删除在admintype中webrole包含此功能名称的权限组
    $questr = "SELECT urladd FROM `#@__sys_function` where  id='$id'";
    //echo $query;
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        $dirFileName = $rowarc["urladd"];
        //1.1查找包含此功能名称的权限组
        $query = "SELECT rank FROM `#@__sys_admintype` WHERE web_role like '%$dirFileName%'";       //此处不能用户FIND_IN_SET  因为保存的字段里没有逗号分隔
        //dump($query);
        $db->SetQuery($query);
        $db->Execute(0);
        while ($row1 = $db->GetObject(0)) {

            //1.2获取 功能名称 在此权限组WEB_ROLE,并把它分隔为数组后,获取此功能名称在数组中的位置
            $groupSet = $dsql->GetOne("SELECT department_role,web_role FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='{$row1->rank}' ");
            $groupWebRanks = explode('|', $groupSet['web_role']);
            $groupDepRanks = explode('|', $groupSet['department_role']);
            $funFileNameKey = array_search($dirFileName, $groupWebRanks);   //获取 功能名称 所在的键值
            if ($funFileNameKey != false) {
                // dump($funFileNameKey);  //???这里要判断一下,发果没有,则不更新权限
                //1.3按1.2获取的键值 分别将对应的web_role和dep_role清空 然后将数组组合成字符串存入数据库
                unset($groupWebRanks[$funFileNameKey]);   //移除键值对应的数组
                unset($groupDepRanks[$funFileNameKey]);
                $All_webRole = implode("|", $groupWebRanks);  //将数组合并为字符串
                $All_depRole = implode("|", $groupDepRanks);
                //将新的权限值更新到数据库
                $sql = "UPDATE `#@__sys_admintype` SET web_role='$All_webRole',department_role='$All_depRole' WHERE CONCAT(`rank`)='{$row1->rank}'";
            }
        }
    }


    /*//此段不用了，系统功能菜单和权限 值分开160606
     * if ($GLOBAMOREDEP) { //将权限值从dep_plus中删除
        $questr = "SELECT depid FROM `#@__emp_dep_plus` WHERE FIND_IN_SET('" . $id . "',functionids) ";
        $dsql->Execute('n', $questr);
        while ($rowarc = $dsql->GetArray('n')) {
            $depid = $rowarc["depid"];

            $questr = "SELECT functionids FROM `#@__emp_dep_plus` where  `depid` = '$depid' ";
            $rowarc = $dsql->GetOne($questr);
            $functionid_array = explode(",", $rowarc["functionids"]);

            //dump(array_search(155,$functionid_array));

            //将删除的RANK从DEP_PLUS中移除
            if (array_search($id, $functionid_array) !== false) {
                //dump($functionid_array);
                unset($functionid_array[array_search($id, $functionid_array)]);//如果替换掉里面的 其他-1
                $functionids = join(',', $functionid_array);
                $dsql->ExecuteNoneQuery("UPDATE `#@__emp_dep_plus` SET functionids='$functionids' WHERE depid='$depid'");
            }

        }
    }*/

    //3 删除功能表中的数据
    $id = trim(preg_replace("#[^0-9]#", '', $id));
    $dsql->ExecuteNoneQuery("delete from  `#@__sys_function`  where id='$id';");
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    //dump($$ENV_GOBACK_URL);
    ShowMsg("删除成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == "") {
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

                    <!--标题栏           开始-->
                    <div class="ibox-title">
                        <h5><?php echo $sysFunTitle ?></h5>
                    </div>
                    <!--标题栏   结束-->
                    <div class="ibox-content">
                        <div class="alert alert-warning alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            1、修改此页内容有风险，请小心操作！<br>
                            2、功能地址,<span style="background-color: #EBCCCC">背景黄色</span>代表实际文件不存在,需要删除<br>
                            3、功能地址,<span style="background-color: #C4E3F3">背景蓝色</span>代表此文件未在data\sys_function_data.php文件中定义<br>
                            5、子功能如果是"文档信息"中的子栏目,则系统中显示的名称为栏目名称
                        </div>
                        <!--工具框   开始-->

                        <div class="btn-group" id="Toolbar">
                            <a onclick="layer.open({type: 2,title: '添加', content: 'sysFunction_add.php'});" href='javascript:' data-toggle='tooltip' data-placement='top' title='添加' class="btn btn-white">
                                <i class='glyphicon glyphicon-plus' aria-hidden='true'></i> </a>
                        </div>
                        <?php
                        if ($GLOBAMOREDEP && $GLOBALS['NOWLOGINUSERTOPDEPID'] == 0) {

                            echo "<div class=\"btn-group\" id=\"Toolbar2\" style=\"margin-left: 5px\">
                            <form name=\"form2\" method=\"get\" action=\"sysFunction.php\">
                                <div class=\"input-group\">
                                    <div class=\"pull-left\">
                                  ";

                            $depOptions = GetDepOnlyTopOptionList($depid);
                            //dump($emp_dep);
                            echo "<select  class='form-control' name='depid' id='depid'  >\r\n";
                            if ($GLOBALS['NOWLOGINUSERTOPDEPID'] == 0) echo "<option value='0'>超级管理员菜单</option>\r\n";//如果超级管理员登录  显示这个
                            echo $depOptions;
                            echo "</select>";


                            echo "      </div>
                                    <div class=\"pull-left \">
                                        <div class=\"input-group-btn\">
                                            <button type=\"submit\" class=\"btn btn-white\">
                                                搜索
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>";
                        } ?>
                        <!--工具框   结束-->


                        <div class='panel-group' id='function'>
                            <?php
                            $fun = new sys_function();
                            $funArray = $fun->getSysFunArray($depid);
                            $parenti = 0;
                            $tableIds = "";//子列表的所有ID值
                            $defaultFunId = "";//默认打开的面板
                            foreach ($funArray as $key => $menu) {
                                $parenti++;
                                $parentMenu = explode(',', $menu[0]);
                                $parentId = $parentMenu[0];
                                $parentTitle = $parentMenu[3];
                                $parentRemark = $parentMenu[5];//删除系统功能中不用的字段后，更新索引160421
                                $parentIsbasefuc = $parentMenu[6];//删除系统功能中不用的字段后，更新索引160421
                                //输出父功能
                                $parentStr = "";
                                $parentStr .= $parentTitle;
                                if ($parentRemark != "") $parentStr .= "  <small>($parentRemark) </small>";
                                $parentStr .= "<code class='pull-right'>
                                                       <a onclick=\"layer.open({type: 2,title: '编辑', content: 'sysFunction_edit.php?id={$parentId}'});\"  href='javascript:'  >编辑</a>
                                                       <a onclick=\"layer.open({type: 2,title: '添加子功能', content: 'sysFunction_add.php?topid={$parentId}&parentTitle={$parentTitle}'});\"  href='javascript:'  >添加子功能</a>
                                                       ";
                                //如果没有子功能，并且不是系统功能，显示删除
                                if (count($menu) == 1 && $parentIsbasefuc == 0) {
                                    $parentStr .= " <a href='javascript:isdel(\"?dopost=del&id={$parentId}\");'>删除</a>";
                                } else {
                                    $parentStr .= " <span style='color: #000;text-decoration:line-through'>删除</span>";
                                }
                                $parentStr .= "</code>";

                                //输出父功能
                                $iconName = "bars";//默认的图标
                                $icon = $parentMenu[7];
                                if ($icon != "") $iconName = $icon;
                                $childStr = "<div class='panel panel-default'>
                                                            <div class='panel-heading'>
                                                                <h5 class='panel-title'>
                                                                    <a data-toggle='collapse' data-parent='#function' href='#collapse{$parentId}'>
                                                                         <i class='fa fa-chevron-down' data-toggle='tooltip' data-placement='top' title='点击显示子功能'></i>
                                                                          <i class='fa  fa-$iconName'></i>
                                                                    $parentStr</a>
                                                                </h5>
                                                            </div>";

                                //if ($parenti == 1) $isin = " in"; else $isin = "";//判断是否第一组，第一组的话，默认展开显示
                                if ($parenti == 1) {
                                    $defaultFunId = $parentId;
                                } //160613添加默认展开的面板

                                //输出子功能
                                $childStr .= "<div id='collapse{$parentId}' class='panel-collapse collapse'>
                                                    <div class='panel-body'>
                                                        <div class='table-responsive'>";

                                if (count($menu) > 1) {
                                    $tableIds .= "{$parentId},";
                                    $childStr .= "
                                                                 <table id='datalist{$parentId}' data-toggle='table'  data-striped='true'>
                                                                  <thead>
                                                                  <tr>
                                                                    <th data-halign='center' data-align='left'>分组名称</th>
                                                                    <th data-halign='center' data-align='left'>显示名称</th>
                                                                    <th data-halign='center' data-align='left'>功能地址</th>
                                                                    <th data-halign='center' data-align='center'>备注</th>
                                                                          <th data-halign='center' data-align='center' data-field='status'>地址状态</th>
                                                              <th data-halign='center' data-align='center'>操作</th>
                                                                  </tr>
                                                                  </thead>";
                                    //将子功能菜单连接，按分组存入数组中
                                    for ($childi = 1; $childi < count($menu); $childi++) {

                                        $childMenu = explode(',', $menu[$childi]);  //获取子功能数组
                                        $childId = $childMenu[0];
                                        $childUrladd = $childMenu[1];
                                        $childGroup = $childMenu[2] == "" ? '默认功能' : $childMenu[2];
                                        $childTitle = $childMenu[3];
                                        $childRemark = $childMenu[5];
                                        $childIsbasefuc = $childMenu[6];

                                        $childStr .= "\n<tr >\r\n";


                                        $childStr .= "<td>$childGroup</td>\r\n";
                                        $icon = $childMenu[7];
                                        if ($icon != "") $icon = "<i class='fa  fa-$icon'></i>";
                                        $childStr .= " <td>$icon $childTitle</td>";
                                        $childStr .= "  <td >$childUrladd</td>\r\n";
                                        $childStr .= "  <td>$childRemark</td>";

                                        $status = "正常";
                                        //150810优化 ,如果地址为空 或不存在 则输出红背景
                                        //dump( $childUrladd);dump(!UrlAddFileExists("../".$childUrladd));
                                        if ($childUrladd == "" || !UrlAddFileExists($childUrladd)) {
                                            $status = "不能访问";  //如果实际文件不存在 则背景显示红色

                                        } else {//如果实际文件存在,则判断是否为跳转数据
                                            //150228优化  判断是否在data\sys_function_data.php文件中定义,上面141130的错误应该和这个是一样的, 这个不添加的话,也是出现引用的数据3中没有定义,随后再没问题的话,上面的可以删除
                                            $filenameArray = explode('/', $childUrladd);
                                            if ($filenameArray[1] != "") $filename = ClearUrlAddParameter($filenameArray[1]); //得到不包含目录的文件地址，并清除地址中包含的参数（？后的内容清除）
                                            require_once("sysFileBaseConfig.class.php");
                                            $fun = new sys_baseconfg();
                                            $funName = $fun->getOneBaseConfig($filename);  //供栏目选择
                                            if ($funName == "") $status = "未定义";  //未在data\sys_function_data.php文件中定义,则显示青色背景
                                        }
                                        $childStr .= "  <td>$status</td>";


                                        /*$depnumb = 0;
                                        $dep_view = "";
                                        if ($GLOBAMOREDEP) {
                                            $sql1 = "SELECT count(*) as dd  FROM `#@__emp_dep_plus` p LEFT JOIN  `#@__emp_dep` d on d.dep_id=p.depid WHERE  FIND_IN_SET('" . $childId . "',functionids)";
                                            $row = $dsql->GetOne($sql1);
                                            if (is_array($row)) {
                                                $depnumb = $row["dd"];
                                            }
                                            $dep_view = "<a onclick=\"layer.open({type: 2,title: '使用公司', content: 'sysFunction_view.php?id={$childId}'});\"  href='javascript:'  >使用公司(" . $depnumb . ")</a> ";
                                        }
                                        //dump($depname);*/
                                        $childStr .= "  <td>
                                                            <a onclick=\"layer.open({type: 2,title: '编辑', content: 'sysFunction_edit.php?id={$childId}'});\"  href='javascript:'  >编辑</a>";
                                        //如果不是系统功能，则可删除
                                        //if($childIsbasefuc==0)
                                        // {
                                        $childStr .= " <a href=\"javascript:isdel('?dopost=del&id={$childId}');\">删除</a>";
                                        // }
                                        $childStr .= "<input name='parentId_" . $parentId . "'  type='hidden' value='" . $parentId . "'></td></tr>  ";
                                    }


                                    $childStr .= "
                                                </table>
                                             ";
                                } else {
                                    $childStr .= "<div align=\"center\"><strong>无子功能</strong></div>";
                                }
                                $childStr .= "</div>
                                            </div>
                                        </div>
                                    </div>";
                                echo $childStr;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/toastr/toastr.min.js"></script>
    <link href="../ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <script src="../ui/js/jquery.cookie.js"></script>
    <SCRIPT LANGUAGE="JavaScript">
        var lastShowFunctionId = $.cookie('lastShowFunctionId');//获取COOK中最后展开的面板
        if (lastShowFunctionId) {
            //如果有COOK，则展开
            $('#collapse' + lastShowFunctionId).collapse('show');
        } else {
            //如果没有COOK，则默认打开第一个COOK
            <?php if ($defaultFunId != "") {
            echo "$('#collapse" . $defaultFunId . "').collapse('show');";
        }
            ?>
        }

        <?php



        if ($tableIds != "") {
            foreach (explode(",", $tableIds) as $tableid) {
                if ($tableid != "") {
                    echo "/*表格配置*/
                    !function (e, t, o) {
                        'use strict';
                        !function () {
                            o('#datalist{$tableid}').bootstrapTable({
                                rowStyle: function (row, index) {
                                //这里有5个取值代表5中颜色['active', 'success', 'info', 'warning', 'danger'];
                                var strclass = '';
                                if (row.status == '不能访问') {
                                    strclass = 'danger';//还有一个active
                                }
                                else if (row.status == '未定义') {
                                    strclass = 'warning';
                                }
                                else if (row.status == '跳转文件') {
                                    strclass = 'info';
                                }
                                else {
                                    return {};
                                    }
                                return { classes: strclass }
                                }
                            });
                    }()
                    }(document, window, jQuery);\r\n";

                    echo "$('#collapse$tableid').on('show.bs.collapse', function () {//当点击面板显示的时候  保存当前面板的ID到COOK
                            $.cookie('lastShowFunctionId', '$tableid', { expires: 7 }); // 存储一个带7天期限的 cookie
                        });";
                }
            }
        }?>

    </SCRIPT>

    </body>
    </html>
<?php } ?>
