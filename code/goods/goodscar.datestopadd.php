<?php
/**
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');

$t1 = ExecTime();
if (!isset($dopost)) $dopost = '';

if ($dopost == "save") {
    $return_str = "";
    $createtime = time();

    $operatorid = $CUSERLOGIN->userID;

    $error_numb = 0;
    if($stopdate!="") {
        $stopdate_array = explode(",", $stopdate);
        // dump($stopdate_array);
        foreach ($stopdate_array as $stopdate) {


            //if($stoptime>$createtime)//大于当前时间才保存
            $stoptime = GetMkTime($stopdate . " 00:00:00");

            //判断 日期是否重复
            $sqlrowTrue = "SELECT id FROM `#@__car_stop`
                                    WHERE goodsid='$goodsid' 
                                    AND stoptime='$stoptime'  ";
            //dump($sqlrowTrue);
            $rowTrue = $dsql->GetOne($sqlrowTrue);
            //dump($rowTrue);
            if (!$rowTrue) {
                $sql = "INSERT INTO `#@__car_stop` ( `goodsid`, `stoptime`, `createtime`,`operatorid`)
                                                 VALUES ('$goodsid', '$stoptime', '$createtime','$operatorid');";
                //dump($sql);
                if (!$dsql->ExecuteNoneQuery($sql)) {
                    $error_numb++;
                }
            } else {
                $error_numb++;

                $return_str .= "所选日期 $stopdate  信息已经存在;<br>";
            }
        }

    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");


    $return_str .= "更新信息成功";
    if ($error_numb > 0) $return_str .= "保存数据出错,请检查";
     ShowMsg($return_str,"-1");
    exit();
}


$title = $sysFunTitle;   //页面显示标题


$s_temp_url = "goods/goodscar.datestopadd.htm";//默认的单日期添加页面
include DwtInclude($s_temp_url);
