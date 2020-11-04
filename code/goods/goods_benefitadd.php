<?php
/**
 * 商品优惠列表
 *
 *
 * 思路:
 *1、界面按createtime只取每个优惠信息的最新的内容，修改了新值后，按当前日期生成新的优惠条目
 *2、每个会员类型或成长值区间，同每个商品,每个日期，只有一条优惠信息，不可以重复
 *3、在同一个界面输入值,按生成的最新日期获取.  以前的旧值保留不显示
 *4、二级返还、三级返还，直接输入金币和积分的数量，用户购买后返还。（要判断购买人使用的实际金币和现金，大于返还的金币+积分数量 ，才返还给上级），
 *金币使用：金币和积分数量为上限。如果金币不够需要现金补齐，如果积分不够可以金币和现金补齐。
 *购买优惠：只使用JBNUM字段，输入0.XX为几折
 *5-------------------   数据表中的 buynumb暂时不用，暂时都是不限数量，后期再看怎么做，（是第二件，不使用；或从第几件开始享受优惠）
 *
 *
 *
 *
 *
 *
 *
 * 使用方法:
 * 1\线路 直通车会员免费坐的,要专门发布商品,然后直通车会员设定免费 .或新建一个  免费专线 分类
 * 2\分时折扣的,  暂未做,随后再做  已经有接口  直接增加rownumb就可以了(线路对应商品的价格)
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once("benefit.class.php");

$t1 = ExecTime();

if (!isset($keyword)) $keyword = '';
if (!isset($dopost)) $dopost = '';
if (!isset($typeid)) $typeid = '';


//除了金币使用的保存过程
if ($dopost == "save") {
    $return_str = "";
    $error_numb = 0;
    $createtime = time();
    $post_array = array();//保存传递过来的表单名称(有值的)

    $operatorid = $CUSERLOGIN->userID;
    $benefitType_array = array("金币使用", "二级返还", "三级返还", "购买优惠");
    if (!empty($form_list)) {
        //dump($form_list);
        $form_list_array = explode(',', $form_list);
        foreach ($form_list_array as $form_list_t) {
            //查询所有的表单,筛选出不为0 不为空的值
            //if (trim($$form_list_t) != "" || trim($$form_list_t) > 0) {
            if (trim($$form_list_t) != "") {
                //dump($form_list_t."------".$$form_list_t);
                $post_array[] = $form_list_t;//将表单名称存入数组 待用
            }
        }


        foreach ($post_array as &$str) {
            $form_list_t_array = explode('-', $str);

            //dump($form_list_array);
            //获取关键字段
            $post_name = $form_list_t_array[0];//表单类型
            if (isset($form_list_t_array[2])) $clientType = $form_list_t_array[2];   //  rank or  scores
            if (isset($form_list_t_array[3])) $clientTypeValue = $form_list_t_array[3];   //会员类型的值


            //dump($clientType ."   ". $clientTypeValue  ."   ".  $goodsid);
            //从不是日期 的表单里取值
            if ($post_name != "time_s" && $post_name != "time_e") {

                foreach ($benefitType_array as $benefitType) {
                    $form_value_jb = $form_value_jf = $time_s_value = $time_e_value = "";//此处$form_value_jb 初始值必须为空,因为金币使用\二三级返还  不能等于0  ,优惠可以为0
                    //获取相应关键字的值,获取后,从数组中删除掉
                    $form_str = "jf-$benefitType-$clientType-$clientTypeValue";
                    $key_jf = array_search($form_str, $post_array);
                    if ($key_jf !== false) {
                        $form_value_jf = trim($$form_str);
                        array_splice($post_array, $key_jf, 1);//如果获取到值,就删除数组中元素
                    } /*else {
                        $form_value_jf = 0;
                    }*/


                    $form_str_jb = "jb-$benefitType-$clientType-$clientTypeValue";
                    $key_jb = array_search($form_str_jb, $post_array);
                    if ($key_jb !== false) {
                        //dump("4444");
                        //dump($$time_s_str);
                        $form_value_jb = trim($$form_str_jb);
                        array_splice($post_array, $key_jb, 1);//如果获取到值,就删除数组中元素
                    } /*else {
                        $form_value_jb = 0;
                    }*/

                    //dump($form_str_jb . "---------");
                    //dump($form_value_jb . "---------");
                    //时间不能清除 数组中的值,否则多列的话,得不到值
                    $time_s_str = "time_s-$benefitType";
                    $time_s_value = "0";
                    if (isset($$time_s_str) && $$time_s_str !== "") $time_s_value = GetMkTime($$time_s_str . " 00:00:00");


                    $time_e_str = "time_e-$benefitType";
                    $time_e_value = "0";
                    if (isset($$time_e_str) && $$time_s_str !== "") $time_e_value = GetMkTime($$time_e_str . " 23:59:59");


                    if ($time_e_value == "0" || $time_s_value == "0") {
                        $time_e_value = 0;
                        $time_s_value = 0;
                    }
                    /*$key = array_search($time_e_str, $post_array);
                    if ($key !== false) {
                        //dump($$time_s_str);
                        $time_e_value = GetMkTime($$time_e_str);
                        array_splice($post_array, $key, 1);//如果获取到值,就删除数组中元素
                    } else {
                        $time_e_value = 0;
                    }*/


                    if (
                        (
                            (

                                $benefitType == "二级返还"
                                ||
                                $benefitType == "三级返还"
                            )
                            &&
                            (
                                $form_value_jb > 0
                                ||
                                $form_value_jf > 0
                            )
                        )
                        ||
                        (
                            ($benefitType == "购买优惠"
                                ||
                                $benefitType == "金币使用")
                            &&
                            (
                                $form_value_jb != ""
                                ||
                                $form_value_jb > 0
                            )
                        )
                    ) {
                        //这里不能检查是否重复,否则引起createtime时间不统一  同一行的值不能全部获取 170116


                        $wheresql = "
                                          AND 
                                          time_s>0  AND time_e>0
                                          AND 
                                          (
                                              (time_s<$time_e_value AND time_e>$time_e_value)/*结束时间在原始区间内*/
                                              OR (time_s<$time_s_value AND time_e>$time_s_value)/*开始时间在原始区间内*/
                                              OR (time_s>$time_s_value AND time_e<$time_e_value)/*新时间区间 包含 原始的时间*/
                                              OR (time_s<$time_s_value AND time_e>$time_e_value)/*  原始的时间 包含 新时间区间*/
                                          ) 

                        ";

                        if ($time_e_value == "0" && $time_s_value == "0") {
                            $wheresql = "   AND    (time_s='$time_s_value'  AND time_e='$time_e_value')/*时间相等*/  ";
                        }

                        $sqlrowTrue = "SELECT id FROM `#@__goods_benefit`
                                    WHERE goodsid='$goodsid' 
                                    $wheresql
                                    AND clientType='$clientType' 
                                    AND clientTypeValue='$clientTypeValue'
                                     AND benefitType='$benefitType' 
                                   AND isdel=0  ";
                        //dump($sqlrowTrue);
                        $rowTrue = $dsql->GetOne($sqlrowTrue);
                        //dump($rowTrue);
                        if (!$rowTrue) {

                            $form_value_jb100 = $form_value_jb * 100;
                            $form_value_jf100 = $form_value_jf * 100;
                            $sql = "INSERT INTO `#@__goods_benefit` ( `goodsid`, `time_s`, `time_e`,  `clientType`, `clientTypeValue`, `benefitType`, `jbnum`, `jfnum`, `createtime`,`operatorid`)
                                                 VALUES ('$goodsid', '$time_s_value', '$time_e_value',  '$clientType', '$clientTypeValue', '$benefitType', '$form_value_jb100', '$form_value_jf100', '$createtime','$operatorid');";
                            //dump($sql);
                            if (!$dsql->ExecuteNoneQuery($sql)) {
                                $error_numb++;
                            }
                        } else {
                            $error_numb++;
                            if ($clientTypeValue == "0") {
                                $clientTypeValue = "非会员";
                            }
                            $return_str .= "所选日期 [$clientTypeValue] 的 [$benefitType] 信息已经存在;<br>";
                        }
                    }
                }

            }


            // dump($post_array);

        }
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    $return_str .= "更新信息成功";
    if ($error_numb > 0) $return_str .= "保存数据出错,请检查";
    ShowMsg($return_str, $$ENV_GOBACK_URL, "", 5000);
    exit();
}


//旅游线路 车辆租赁的金币使用保存过程
if ($dopost == "jbsy_save") {
    $return_str = "";
    $error_numb = 0;
    $createtime = time();
    $post_array = array();//保存传递过来的表单名称(有值的)

    $operatorid = $CUSERLOGIN->userID;
    if (!empty($form_list)) {
        //dump($form_list);
        $form_list_array = explode(',', $form_list);
        foreach ($form_list_array as $form_list_t) {
            //查询所有的表单,筛选  不为空的值
            //if (trim($$form_list_t) != "" || trim($$form_list_t) > 0) {
            if (trim($$form_list_t) != "") {
                //dump($form_list_t."------".$$form_list_t);
                $post_array[] = $form_list_t;//将表单名称存入数组 待用
            }
        }


        foreach ($post_array as &$str) {
            $form_list_t_array = explode('-', $str);

            //dump($form_list_array);
            //获取关键字段
            $post_name = $form_list_t_array[0];//表单类型
            if (isset($form_list_t_array[2])) $clientType = $form_list_t_array[2];   //  rank or  scores
            if (isset($form_list_t_array[3])) $clientTypeValue = $form_list_t_array[3];   //会员类型的值


            //dump($clientType ."   ". $clientTypeValue  ."   ".  $goodsid);
            //从不是日期 的表单里取值
            //dump($post_name);
            if ($post_name != "time_s" && $post_name != "time_e") {

                $benefitType = "金币使用";
                $form_value_jb = $form_value_jf = $time_s_value = $time_e_value = "";//此处$form_value_jb 初始值必须为空,因为金币使用\二三级返还  不能等于0  ,优惠可以为0
                //获取相应关键字的值,获取后,从数组中删除掉
                $form_str = "jf-$benefitType-$clientType-$clientTypeValue";
                $key_jf = array_search($form_str, $post_array);
                if ($key_jf !== false) {
                    $form_value_jf = trim($$form_str);
                    array_splice($post_array, $key_jf, 1);//如果获取到值,就删除数组中元素
                }

                $form_str_jb = "jb-$benefitType-$clientType-$clientTypeValue";
                $key_jb = array_search($form_str_jb, $post_array);
                if ($key_jb !== false) {
                    $form_value_jb = trim($$form_str_jb);
                    array_splice($post_array, $key_jb, 1);//如果获取到值,就删除数组中元素
                }


                //时间不能清除 数组中的值,否则多列的话,得不到值
                $time_s_str = "time_s-$benefitType";


                $time_s_value_array = array("0");
                if (isset($$time_s_str) && $$time_s_str !== "") {
                    //$time_s_value = GetMkTime($$time_s_str . " 00:00:00");
                    $time_s_value_array = explode(",", $$time_s_str);

                }


                //dump($time_s_value_array);

                foreach ($time_s_value_array as $datevalue) {
                    if ($datevalue != "0") {
                        //如果有日期
                        $time_s_value = GetMkTime($datevalue . " 00:00:00");
                        $time_e_value = GetMkTime($datevalue . "  23:59:59");
                    } else {
                        $time_s_value = "0";
                        $time_e_value = "0";

                    }

                    if ($form_value_jb != "" || $form_value_jb > 0) {

                        $wheresql = "
                                          AND 
                                          time_s>0  AND time_e>0
                                          AND 
                                          (
                                              (time_s<$time_e_value AND time_e>$time_e_value)/*结束时间在原始区间内*/
                                              OR (time_s<$time_s_value AND time_e>$time_s_value)/*开始时间在原始区间内*/
                                              OR (time_s>$time_s_value AND time_e<$time_e_value)/*新时间区间 包含 原始的时间*/
                                              OR (time_s<$time_s_value AND time_e>$time_e_value)/*  原始的时间 包含 新时间区间*/
                                          ) 
                                         

                        ";

                        //if ($time_e_value == "0" && $time_s_value == "0") {
                            $wheresql = "   AND    (time_s='$time_s_value'  AND time_e='$time_e_value')/*时间相等*/  ";
                        //}

                        //判断 日期是否重复
                        $sqlrowTrue = "SELECT id FROM `#@__goods_benefit`
                                    WHERE goodsid='$goodsid' 
                                    $wheresql
                                    AND clientType='$clientType' 
                                    AND clientTypeValue='$clientTypeValue'
                                     AND benefitType='$benefitType' 
                                   AND isdel=0  ";
                        //dump($sqlrowTrue);
                        $rowTrue = $dsql->GetOne($sqlrowTrue);
                        //dump($rowTrue);
                        if (!$rowTrue) {

                            $form_value_jb100 = $form_value_jb * 100;
                            $form_value_jf100 = $form_value_jf * 100;
                            $sql = "INSERT INTO `#@__goods_benefit` ( `goodsid`, `time_s`, `time_e`,  `clientType`, `clientTypeValue`, `benefitType`, `jbnum`, `jfnum`, `createtime`,`operatorid`)
                                                 VALUES ('$goodsid', '$time_s_value', '$time_e_value',  '$clientType', '$clientTypeValue', '$benefitType', '$form_value_jb100', '$form_value_jf100', '$createtime','$operatorid');";
                            //dump($sql);
                            if (!$dsql->ExecuteNoneQuery($sql)) {
                                $error_numb++;
                            }
                        } else {
                            $error_numb++;
                            if ($clientTypeValue == "0") {
                                $clientTypeValue = "非会员";
                            }
                            $return_str .= "所选日期 $datevalue [$clientTypeValue] 的 [$benefitType] 信息已经存在;<br>";
                        }
                    }


                }


            }


            // dump($post_array);

        }
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    $return_str .= "更新信息成功";
    if ($error_numb > 0) $return_str .= "保存数据出错,请检查";
    ShowMsg($return_str, $$ENV_GOBACK_URL, "", 5000);
    exit();
}


$title = $sysFunTitle;   //页面显示标题

//获取当前点开的分类信息
$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetGoodsTypeOptionS();  //搜索表单的分类值//GetOptionArray
$title .= " " . $positionname;


//界面显示的标题和提示信息
$benefitTypeName_array = array(
    "金币使用" => "金币和积分数量使用上限。如果金币不够需要现金补齐，如果积分不够可以金币和现金补齐。",
    "购买优惠" => "按百分数计算,例如9折,要输入90",
    "二级返还" => "",
    "三级返还" => ""
);//要显示的优惠规则


//所有的优惠类型
$display_array = array(
    "金币使用",
    "购买优惠",
    "二级返还",
    "三级返还"
);

//要显示 的用户类型,为空则全显示
$use_clientType_array = "";


$query = "SELECT gt.channeltype as channelid FROM `#@__goods` goods
    LEFT JOIN `#@__goods_type` gt ON gt.id=goods.typeid
 WHERE goods.id='$goodsid' ";
$goodRow = $dsql->GetOne($query);
if (!is_array($goodRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
$channelid = $goodRow['channelid'];


$s_temp_url = "";
//直通车
if ($channelid == 4) {
//只显示 的优惠类型
    $display_array = array(
        "购买优惠",
        "二级返还",
        "三级返还"
    );
    $use_clientType_array = array(
        "合伙人",
        "小额合伙人",
        "0"/*分值为0的注册会员*/
    );
}

//旅游产品
if ($channelid == 2) {
    //只显示 的优惠类型
    $display_array = array(
        "金币使用"
    );

    $use_clientType_array =GetGoodsZTCclientTYPE();
    /*$use_clientType_array = array(
        "直通车",
        "爱心卡"

    );*/
    // dump($use_clientType_array);
    $s_temp_url = "goods/goods_benefitaddDateMore.htm";//日期多选界面
}

//车辆租赁
if ($channelid == 3) {
    //只显示 的优惠类型
    $display_array = array(
        "金币使用"
    );
    $use_clientType_array = array(
        "合伙人",
        "直通车"
    );
    $s_temp_url = "goods/goods_benefitaddDateMore.htm";//日期多选界面
}

if ($s_temp_url == "") $s_temp_url = "goods/goods_benefitadd.htm";//默认的单日期添加页面
include DwtInclude($s_temp_url);
