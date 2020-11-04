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
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

if (!isset($keyword)) $keyword = '';
if (!isset($dopost)) $dopost = '';
if (!isset($typeid)) $typeid = '';

if ($dopost == "save") {
    $error_numb = 0;
    $createtime = time();
    $post_array = array();//保存传递过来的表单名称(有值的)


    $benefitType_array = array("金币使用", "二级返还", "三级返还", "购买优惠");
    if (!empty($form_list)) {
        //dump($form_list);
        $form_list_array = explode(',', $form_list);
        $goodsid_yz_array = array();//表单传递过来 有值的商品ID
        foreach ($form_list_array as $form_list_t) {
            //查询所有的表单,筛选出不为0 不为空的值
            if (trim($$form_list_t) != "" || trim($$form_list_t) > 0) {
                //dump($form_list_t."------".$$form_list_t);
                $post_array[] = $form_list_t;//将表单名称存入数组 待用
                $form_list_t_array = explode('-', $form_list_t);
                $goodsid_yz_array[] = $form_list_t_array[4];//商品ID
            }
        }
        $goodsid_yz_array = array_unique($goodsid_yz_array);//用户提交的表单,有值的商品ID
        //dump($goodsid_yz_array);
        $goodsid_yz_old_array = explode(',', $goodsid_yz_old);//旧值有值的商品ID
        //dump($goodsid_yz_old_array);

        //旧值里的多出来的商品ID,新值里没有的ID,代表用户清空了,则删除此ID的优惠信息
        //旧值有的,新值没有的
        $goodsid_del_array = array_diff($goodsid_yz_old_array, $goodsid_yz_array);
        //dump($goodsid_del_array);

        //exit();
        if (count($goodsid_del_array) > 0) {
            foreach ($goodsid_del_array as $goodsid) {
                //如果用户提交的表单都没有值,则删除此商品的优惠信息
                $sql11 = "UPDATE `x_goods_benefit` SET `isdel`='1' WHERE goodsid='$goodsid' ";
                // dump($sql);
                if (!$dsql->ExecuteNoneQuery($sql11)) {
                    $error_numb++;
                }
            }


        }
        foreach ($post_array as &$str) {
            $form_list_t_array = explode('-', $str);

            //获取关键字段
            $clientType = $form_list_t_array[2];   //  rank or  scores
            $clientTypeValue = $form_list_t_array[3];   //会员类型的值
            $goodsid = $form_list_t_array[4];//商品ID
            $rownumb = $form_list_t_array[5];//行数


            //dump($clientType ."   ". $clientTypeValue  ."   ".  $goodsid);
            //从不是日期 并表商品ID大于0的里面取值
            if ($clientType != "|" && $clientTypeValue != "|" && $goodsid > 0) {

                foreach ($benefitType_array as $benefitType) {
                    $form_value_jb = $form_value_jf = $time_s_value = $time_e_value = "";//此处$form_value_jb 初始值必须为空,因为金币使用\二三级返还  不能等于0  ,优惠可以为0
                    //获取相应关键字的值,获取后,从数组中删除掉
                    $form_str = "jf-$benefitType-$clientType-$clientTypeValue-$goodsid-$rownumb";
                    $key_jf = array_search($form_str, $post_array);
                    if ($key_jf !== false) {
                        $form_value_jf = trim($$form_str);
                        array_splice($post_array, $key_jf, 1);//如果获取到值,就删除数组中元素
                    } /*else {
                        $form_value_jf = 0;
                    }*/


                    $form_str_jb = "jb-$benefitType-$clientType-$clientTypeValue-$goodsid-$rownumb";
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
                    $time_s_str = "time_s-$benefitType-|-|-$goodsid-$rownumb";
                    if (isset($$time_s_str) && $$time_s_str !== "") $time_s_value = GetMkTime($$time_s_str);
                    /* $key = array_search($time_s_str, $post_array);
                     if ($key !== false) {
                         //dump($$time_s_str);
                         $time_s_value = GetMkTime($$time_s_str);
                         array_splice($post_array, $key, 1);//如果获取到值,就删除数组中元素
                     } else {
                         $time_s_value = 0;
                     }*/

                    $time_e_str = "time_e-$benefitType-|-|-$goodsid-$rownumb";
                    if (isset($$time_e_str) && $$time_s_str !== "") $time_e_value = GetMkTime($$time_e_str);

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
                                $benefitType == "金币使用"
                                ||
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
                            $benefitType == "购买优惠"
                            &&
                            (
                                $form_value_jb != ""
                                ||
                                $form_value_jb > 0
                            )
                        )
                    ) {
                        //这里不能检查是否重复,否则引起createtime时间不统一  同一行的值不能全部获取 170116
                        /*$sqlrowTrue = "SELECT id FROM `#@__goods_benefit`
                                    WHERE goodsid='$goodsid' 
                                    and time_s='$time_s_value'  
                                    and time_e='$time_e_value' 
                                    and clientType='$clientType' 
                                    and clientTypeValue='$clientTypeValue'
                                     and benefitType='$benefitType' 
                                     and jbnum='$form_value_jb' 
                                     and jfnum='$form_value_jf'";
                        //dump($sqlrowTrue);
                        $rowTrue = $dsql->GetOne($sqlrowTrue);
                        //dump($rowTrue);
                        if (!$rowTrue) {*/
                        $form_value_jb100 = $form_value_jb * 100;
                        $form_value_jf100 = $form_value_jf * 100;
                        $sql = "INSERT INTO `#@__goods_benefit` ( `goodsid`, `time_s`, `time_e`,  `clientType`, `clientTypeValue`, `benefitType`, `jbnum`, `jfnum`, `createtime`)
                                                 VALUES ('$goodsid', '$time_s_value', '$time_e_value',  '$clientType', '$clientTypeValue', '$benefitType', '$form_value_jb100', '$form_value_jf100', '$createtime');";
                        // dump($sql);
                        if (!$dsql->ExecuteNoneQuery($sql)) {
                            $error_numb++;
                        }

                    }
                }

            }


            // dump($post_array);

        }
    }


    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    $return_str = "更新信息成功";
    if ($error_numb > 0) $return_str = "有错误";
    ShowMsg($return_str, $$ENV_GOBACK_URL);
    exit();
}


$title = $sysFunTitle;   //页面显示标题

//获取当前点开的分类信息
$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetGoodsTypeOptionS();  //搜索表单的分类值//GetOptionArray
$title .= " " . $positionname;


//默认的搜索条件
$whereSql = " where `status`='0'";


if ($keyword != "") {
    $whereSql .= " and ( ";
    $whereSql .= "   `goodsname` like '%" . $keyword . "%' ";
    $whereSql .= " or `goodscode` like '%" . $keyword . "%' ";
    $whereSql .= " ) ";
}

if ($typeid > 0) {
    $whereSql .= " AND `typeid` IN (" . $tl->GetGoodsSonIds() . ")";    //搜索用的
}


$query = "SELECT  typeid,id,goodsname,goodscode,litpic,price FROM `#@__goods`            $whereSql            ORDER BY   id asc ";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 5;

//GET参数
$dlist->SetParameter('keyword', $keyword);//input的搜索参数
$dlist->SetParameter('typeid', $typeid);


//模板
if (empty($s_tmplets)) $s_tmplets = 'benefit.htm';
//$s_tmplets = 'goods.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
$dlist->Close();

$t2 = ExecTime();
//echo $t2 - $t1;
