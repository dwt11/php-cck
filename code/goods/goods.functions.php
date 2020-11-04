<?php
/**
 * 商品操作相关函数
 *
 * @version        $Id: inc_goods_functions.php 1 9:56 2010年7月21日
 * @package
 * @license
 * @link
 */


/**
 *  检测是否频道
 *  如果是频道封面 则不可以添加
 *170112
 *
 * @access    public
 *
 * @param     int $typeid 分类ID
 *
 * @return    bool
 */
function CheckIsPart($typeid)
{
    global $dsql;
    if ($typeid == 0) return TRUE;

    $row = $dsql->GetOne("SELECT ispart,channeltype FROM `#@__goods_type` WHERE id='$typeid' ");
    if ($row['ispart'] == 1) return true;
    else return false;
}
//显示商品状态
function GetDisplayStatus($goodsid, $channeltypeid)
{
    global $dsql;
    $request_str = "";
    $row = $dsql->GetOne("SELECT status,isOnlyAdminDisplay FROM `#@__goods` WHERE id='$goodsid' ");
    if ($row['isOnlyAdminDisplay'] == 1) {
        $request_str .= "<b>只在后台显示</b>";
    } else {
        $request_str .= "前后台都显示";
    }

    $status=$row['status'];
    if ($status == 0) {
        $request_str .= " 正常商品";
    } else if ($status == 1){
        $request_str .= " <b>已经下架</b>";
    }


    return $request_str;
}



//获取商品编号
function getGoodsCode($goodstypename){
    global $dsql;
    $pinyin = strtoupper(GetPinyin($goodstypename, 1));
    $goodsCode = $pinyin . "001";
    $pinyin_lenth=strlen($pinyin)+1;
    $questr = "SELECT SUBSTRING(goodscode,$pinyin_lenth) AS goodscode FROM `#@__goods` WHERE  goodscode LIKE '$pinyin%'  ORDER BY SUBSTRING(goodscode,$pinyin_lenth)+0 DESC limit 0,1";
    //dump($questr);
    $rowarc = $dsql->GetOne($questr);
    if (isset($rowarc['goodscode'])&&$rowarc['goodscode'] != "") {
        $goodsCode = $rowarc['goodscode'];
        $goodsCode++;

        $goodsCode = $pinyin.GetIntAddZero($goodsCode);
    }
    return $goodsCode;
}