<?php if (!defined('DWTINC')) exit("Request Error!");
/**
 *
 *  商品订单的流程
 * 1、订单创建前获取 优惠信息
 * 2、             结算信息
 * 3、             支付金额
 */

/**
 *
 * function
 *
 * @package          DedeTag
 * @subpackage
 * @link
 */
class GoodsOrder
{

    var $benefitInfo_array = array();//优惠信息数组
    var $clientScroeName = "";//用户的成长值名称
    var $price = 0;//商品单价
    var $clientid = 0;//
    //优惠的数量 如果多个,默认使用第一个
    var $benefit_gmyh_numb = "";//折扣后的价格  默认为空 表示没有折扣,如果为0则不用付款
    var $benefit_ejfhjb_numb = 0;//二级返还 的数量
    var $benefit_ejfhjf_numb = 0;//二级返还 的数量
    var $benefit_sjfhjb_numb = 0;//三级返还的数量
    var $benefit_sjfhjf_numb = 0;//三级返还的数量
    var $benefit_zdsyjb_numb = 0;//最多使用的数量
    var $benefit_zdsyjf_numb = 0;//最多使用的数量
    var $benefit_createtime = 0;//优惠规则的MD5加密时间验证
    var $benefit_id = 0;//优惠规则的MD5加密ID验证


    /**获取优惠信息
     *
     * 成长值,获取用户所属档次,只获取该档次的优惠信息
     * 会员类型,获取用户所属类型的优惠,可以多个
     *
     * 用户选择使用哪个优惠类型
     *
     * @param        $clientid          用户ID
     * @param        $goodsid           商品ID
     * @param        $price             单价
     * @param string $no_benefit_type   不查询出来的优惠
     * @param string $only_benefit_type 只查询出来的优惠
     */
    function __construct($clientid, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "", $linetime = "")
    {
        global $dsql;
        $this->price = $price;//商品单价
        $this->clientid = $clientid;//商品单价

        //开始---------------------------------------------------------------------获取用户的成长值
        $scroeInfo = GetClientType("score", $clientid);
        $scroeInfo_array = explode(",", $scroeInfo);
        // $scoreNumb = $scroeInfo_array[0];//用户分值
        $this->clientScroeName = $scroeInfo_array[1];//用户成长值名称
        $scoreNumb_biaozun = $scroeInfo_array[2];//所在等级的标准值

        $nowtime = time();
        if ($linetime != "") $nowtime = $linetime;
        $whereSql = "";
        if ($no_benefit_type != "") $whereSql = " AND clientTypeValue!='$no_benefit_type'";
        if ($only_benefit_type != "") $whereSql = " AND clientTypeValue='$only_benefit_type'";
        $sql = "SELECT id,time_s,time_e,clientTypeValue,benefitType,jbnum,jfnum,createtime FROM `#@__goods_benefit` 
                WHERE goodsid='$goodsid'   AND isdel=0 
                $whereSql
                AND (
                      (time_s<$nowtime  AND time_e>$nowtime )
                       OR
                      ( time_s=0 AND time_e=0)
                    ) 
                AND clientType='scores' 
                AND (clientTypeValue+0)=$scoreNumb_biaozun/*只取当前等级 的优惠*/
                ";
        $dsql->SetQuery($sql);
        $dsql->Execute("score170118");
        // dump($sql);
        while ($row = $dsql->GetObject("score170118")) {
            $id = $row->id;
            $time_s = $row->time_s;
            $time_e = $row->time_e;
            $jbnum100 = $row->jbnum;
            $jbnum = $jbnum100 / 100;
            $jfnum100 = $row->jfnum;
            $jfnum = $jfnum100 / 100;

            /*如果有时间范围,则判断{
            //              当前时间是否在范围内容{
            //                     如果在,则获取规则
             //             }else如果不在则跳过
            //  }如果没有时间范围,则全部获取
            */
            if ($time_s > 0 && $time_e > 0) {
                if ($time_s < $nowtime && $time_e > $nowtime) {
                    $this->benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum, $jfnum);
                    $this->benefit_createtime = $row->createtime;
                } else {
                    continue;
                }
            } else {
                $this->benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum, $jfnum);
                $this->benefit_createtime = $row->createtime;
            }
            $this->benefitInfo_array[$row->clientTypeValue][$row->benefitType]["id"] = $id;

        }

        //dump($this->benefitInfo_array);

        //-----------------获取用户的类型
        $rankInfo = GetClientType("rank", $clientid);
        $sql = "SELECT id,time_s,time_e,clientTypeValue,benefitType,jbnum,jfnum,createtime FROM `#@__goods_benefit` 
                WHERE goodsid='$goodsid'  AND isdel=0 
                $whereSql
                AND (
                      (time_s<$nowtime  AND time_e>$nowtime )
                       OR
                      ( time_s=0 AND time_e=0)
                    ) 
                AND clientType='rank' 
                AND FIND_IN_SET(clientTypeValue,'$rankInfo')
                ";
        //dump($sql);
        $dsql->SetQuery($sql);
        $dsql->Execute("score170118");
        while ($row = $dsql->GetObject("score170118")) {
            $id = $row->id;
            $time_s = $row->time_s;
            $time_e = $row->time_e;
            $jbnum100 = $row->jbnum;
            $jbnum = $jbnum100 / 100;
            $jfnum100 = $row->jfnum;
            $jfnum = $jfnum100 / 100;
            //dump
            /*如果有时间范围,则判断{
            //              当前时间是否在范围内容{
            //                     如果在,则获取规则
             //             }else如果不在则跳过
            //  }如果没有时间范围,则全部获取
            */
            if ($time_s > 0 && $time_e > 0) {
                if ($time_s < $nowtime && $time_e > $nowtime) {
                    $this->benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum, $jfnum);
                    $this->benefit_createtime = $row->createtime;
                } else {
                    continue;
                }
            } else {
                $this->benefitInfo_array[$row->clientTypeValue][$row->benefitType] = array($jbnum, $jfnum);
                $this->benefit_createtime = $row->createtime;
            }
            //$this->benefitInfo_array[$row->clientTypeValue][$row->benefitType]["id"] = $id;////做了，id但暂时先不用170316？？？？
        }
        //dump($this->benefitInfo_array);
    }

    function GoodsOrder($clientid, $goodsid, $price)
    {
        $this->__construct($clientid, $goodsid, $price);
    }


    function Get_benefit_createtime()
    {
        return $this->benefit_createtime;
    }


    /**
     * 根据优惠数组,分隔显示,供用户多选选择
     *
     * 这个是多选,每个商品可选择选择会员对应的乘车卡
     *
     * 供旅游线路购买使用
     *
     * @param $only_benefit_type  要获取 的优惠类型
     *
     * @return string
     */
    function GetBenefitInfoHtmlToWeb_ZTC_CARD($only_benefit_type)
    {
        $return_str = "";
        //  dump($this->benefitInfo_array);
        // dump($only_benefit_type);
        //根据优惠数组,分隔显示,供用户选择
        if (!isset($this->benefitInfo_array[$only_benefit_type]) || !is_array($this->benefitInfo_array[$only_benefit_type])) return "";

        $clintTypeValue = $only_benefit_type;
        $benefit = $this->benefitInfo_array[$only_benefit_type];//此数组 显示会员类型名称
        if (!is_array($benefit)) return "";
        $row = "";
        $enname_global = GetPinyin($clintTypeValue, $ishead = 1);//在HTML页面中的标识名称
        foreach ($benefit as $benefitType => $jbjfnum) {//此数组 显示详细的优惠信息
            //$tmpName = "";
            $jbnum = $jbjfnum[0];//保存入数组的时候 已经处理成元了
            $jfnum = $jbjfnum[1];
            //$id= $jbjfnum["id"];//做了，id但暂时先不用170316？？？？
            if ($benefitType == "金币使用") {
                $tmpName = "每个卡可使用";
                //ID<span id='benefitID_$benefit_numb_i'>$id</span>//做了，id但暂时先不用170316？？？？
                $row .= "
                                        [$tmpName]
                                        <span class=\"pull-right\"  >
                                            金币<span id='zdsyjb_$enname_global'>$jbnum</span>
                                            积分<span id='zdsyjf_$enname_global'>$jfnum</span>
                                        </span>
                                        <br>
                                        ";
            }
        }

        /*$return_str .= "        <li class=\"list-group-item1 \">
                                     <div class=\" small text-muted \">
                                        $row
                                    </div>
                                </li>
                                ";

        if ($return_str != "") {
            $return_str = "
                        <ul class=\"list-group list-group-plus list-font-color-black\">
                           $return_str
                         </ul>
                         ";
        } else {
            $return_str = "";
        }*/

        if ($row != "") {
            $return_str = $row;
        } else {
            $return_str = "";
        }

        //dump($return_str);
        return $return_str;

    }


    /**
     * 根据优惠数组,分隔显示,供用户单独选择
     *
     * 这个是单选,每个商品只可使用一种优惠
     *
     * 供直通车\车辆租赁购买使用
     *
     * @return string
     *
     */
    function GetBenefitInfoHtmlToWeb()
    {
        $benefit_numb = 0;   //优惠的个数
        $benefit_numb_i = 0;   //HTML输出分隔线计数
        $return_str = "";
        //dump($this->benefitInfo_array);
        //根据优惠数组,分隔显示,供用户选择
        if (!is_array($this->benefitInfo_array)) return "";
        $benefit_numb = $benefit_numb_i = count($this->benefitInfo_array);
        foreach ($this->benefitInfo_array as $clintTypeValue => $benefit) {//此数组 显示会员类型名称
            $clintType = "";
            if (is_numeric($clintTypeValue)) {
                $clintType = "[{$this->clientScroeName}] 优惠";
            } else {
                $clintType = "[{$clintTypeValue}] 优惠";
            }
            // dump($benefit);

            if (!is_array($benefit)) return "";
            $row = "";
            foreach ($benefit as $benefitType => $jbjfnum) {//此数组 显示详细的优惠信息
                //$tmpName = "";
                $jbnum = $jbjfnum[0];//保存入数组的时候 已经处理成元了
                $jfnum = $jbjfnum[1];
                //$id= $jbjfnum["id"];//做了，id但暂时先不用170316？？？？
                if ($benefitType == "购买优惠") {
                    $tmpName = "会员价格";
                    $price_now = $this->price * ($jbnum) / 100;//这里单位是元,计算折扣率
                    //dump($this->price);
                    $price_now = number_format($price_now, 2);
                    $row .= "
                                       [$tmpName]
                                        <span class=\"pull-right\" >[{$this->price}×{$jbnum}%] =
                                            <span id='gmyh_$benefit_numb_i'>$price_now</span>
                                        </span>
                                        <br>
                                        ";

                    if ($benefit_numb == $benefit_numb_i) $this->benefit_gmyh_numb = $price_now;//购买优惠的数量 默认取第一个
                } else if ($benefitType == "金币使用") {
                    $tmpName = "每件商品可使用";
                    //ID<span id='benefitID_$benefit_numb_i'>$id</span>//做了，id但暂时先不用170316？？？？

                    $row .= "
                                        [$tmpName]
                                        <span class=\"pull-right\"  >
                                            金币<span id='zdsyjb_$benefit_numb_i'>$jbnum</span>
                                            积分<span id='zdsyjf_$benefit_numb_i'>$jfnum</span>
                                        </span>
                                        <br>
                                        ";
                    if ($benefit_numb == $benefit_numb_i) {
                        $this->benefit_zdsyjb_numb = $jbnum;
                        $this->benefit_zdsyjf_numb = $jfnum;
                    }//最多使用的数量 默认取第一个
                } else if ($benefitType == "二级返还") {
                    $tmpName = "给上级返利";
                    $row .= "
                                        [$tmpName]
                                        <span class=\"pull-right\" >
                                            金币<span id='ejfhjb_$benefit_numb_i'>$jbnum</span>
                                            积分<span id='ejfhjf_$benefit_numb_i'>$jfnum</span>
                                        </span>
                                        <br>
                                        ";
                    if ($benefit_numb == $benefit_numb_i) {
                        $this->benefit_ejfhjb_numb = $jbnum;
                        $this->benefit_ejfhjf_numb = $jfnum;
                    }//二级返还 的数量 默认取第一个
                } else if ($benefitType == "三级返还") {
                    $tmpName = "给上上级返利";
                    $row .= "
                                        [$tmpName]
                                        <span class=\"pull-right\" >
                                            金币<span id='sjfhjb_$benefit_numb_i'>$jbnum</span>
                                            积分<span id='sjfhjf_$benefit_numb_i'>$jfnum</span>
                                        </span>
                                       <br>
                                        ";
                    if ($benefit_numb == $benefit_numb_i) {
                        $this->benefit_sjfhjb_numb = $jbnum;
                        $this->benefit_sjfhjf_numb = $jfnum;
                    }//三级返还的数量 默认取第一个
                }


            }


            //dump($row);

            $border = "";
            if ($benefit_numb_i > 1) $border = " list-group-item-border ";
            $display = "";
            if ($benefit_numb == 1) $display = "display: none";//只有一个优惠 则不显示选择按钮
            $Checked = "";
            //if($benefit_numb == $benefit_numb_i)$Checked=" checked ";
            $return_str .= " 
                                <li class=\"list-group-item1 $border\">
                                              <span id='hykname_$benefit_numb_i'>$clintType</span>
                              
                                    <span class=\"pull-right   text-danger\" style=\"margin-right: 5px;{$display}\">
                                        <div class=\"checkbox i-checks\">
                                        <label>
                                            <input type=\"radio\" value=\"$benefit_numb_i\" name=\"benefitInfo\"  id=\"benefitInfo\" $Checked>
                                        </label>
                                        </div>

                                       </span>
                                    <div class=\"clearfix  \"></div>
                                    <div class=\" small text-muted \">
                                        $row
                                    </div>
                                </li>
                                ";
            $benefit_numb_i--;
        }
        if ($return_str != "") {
            $return_str = "
                        <ul class=\"list-group list-group-plus list-font-color-black\">
                           $return_str
                         </ul>
                         ";
        } else {
            $return_str = "";
        }
        //dump($return_str);
        return $return_str;

    }


    /* function GetJbjfdkHtmlToWeb()
   {


              * 可以使用的金币和积分算法
        * 1、如果 商品优惠信息中,最多使用金币不大于0 并且 最多使用积分不大于0  并且 折扣价格为空,则代表没有任何优惠信息  则不显示折扣信息 不显示使用金币和积分开关
        *
        * 先使用优惠后价格 与金币判断,判断后再用剩下的与积分判断 .这样计算出可以使用的金币和积分
        *
        * 再与用户的余额判断  算出 可以使用的 最终金币和积分
        *
        *

       //获取类中的值
       $benefit_zdsyjb_numb_t = $this->benefit_zdsyjb_numb;  //最多使用金币
       $benefit_zdsyjf_numb_t = $this->benefit_zdsyjf_numb;  //最多使用积分
       $benefit_gmyh_numb_t = $this->benefit_gmyh_numb;  //最多使用金币

       if (
           (
               !$benefit_zdsyjb_numb_t > 0
               &&
               !$benefit_zdsyjf_numb_t > 0
           )
           &&
           $benefit_gmyh_numb_t == ""
       ) {
           //如果 商品优惠信息中,最多使用金币不大于0 并且 最多使用积分不大于0  并且 折扣价格为空,则代表没有任何优惠信息  则不显示折扣信息 不显示使用金币和积分开关
           return "";
       }


       $jbjfdk_html = "        <ul class=\"list-group list-group-plus list-font-color-black\">";
       //打折后的价格
       if ($benefit_gmyh_numb_t != "") {
           $jbjfdk_html .= "               <li class=\"list-group-item1 \">
                                               会员价格
                                               <span class=\"small text-muted\">

                                               </span>
                                               <span class=\"pull-right  text-danger\" style=\"margin-right: 5px\">
                                                   ￥<span id='dk_jg'>$benefit_gmyh_numb_t</span>
                                               </span>
                                           </li>
               ";
       }


       //开始--------------------根据优惠后价格 计算可以使用的金币和积分
       $dk_jb = 0;
       $dk_jf = 0;
       $gmyh_dkjb = 0;//折扣后价格减去 金币折扣价格 剩下的供积分判断
       if (is_numeric($benefit_gmyh_numb_t) && $benefit_gmyh_numb_t == 0) {
           //如果后优惠价格为0 则金币和积分抵扣都为0
           $dk_jb = $dk_jf = 0;
       } elseif ($benefit_gmyh_numb_t > 0) {
           //如果优惠价格大于0
           if ($benefit_zdsyjb_numb_t > 0) {
               //如果最多使用金币大于0
               if ($benefit_gmyh_numb_t > $benefit_zdsyjb_numb_t) {
                   //如果折扣后价格 大于 金币抵扣价格 则折扣后价格减去 金币折扣价格 剩下的供积分判断
                   $dk_jb = $benefit_zdsyjb_numb_t;
                   $gmyh_dkjb = $benefit_gmyh_numb_t - $benefit_zdsyjb_numb_t;
               } else {
                   //如果折扣后价格 小于等金币价格  则金币抵扣价格等于折扣后价格
                   $dk_jb = $benefit_gmyh_numb_t;
               }
           }

           if ($benefit_zdsyjf_numb_t > 0) {
               //如果最多可用积分大于0
               if ($benefit_zdsyjb_numb_t > 0) {
                   //如果有最多可用金币
                   if ($gmyh_dkjb > $benefit_zdsyjf_numb_t) {
                       //如果 折扣后价格减去 金币折扣价格 剩下的   大于可以积分
                       $dk_jf = $benefit_zdsyjf_numb_t;
                   } else {
                       //小于等于可以积分
                       $dk_jf = $gmyh_dkjb;
                   } //
               } else {
                   //如果最多可用金币没有
                   if ($benefit_gmyh_numb_t > $benefit_zdsyjf_numb_t) {
                       //如果折扣后价格 大于 积分抵扣价格 则折扣后价格减去 金币折扣价格 剩下的供积分判断
                       $dk_jf = $benefit_zdsyjf_numb_t;
                   } else {
                       //如果折扣后价格 小于等金币价格  则金币抵扣价格等于折扣后价格
                       $dk_jf = $benefit_gmyh_numb_t;
                   } //
               }
           }
       } else {
           $dk_jb = $benefit_zdsyjb_numb_t;
           $dk_jf = $benefit_zdsyjf_numb_t;
       }
       //结束 --------------------根据优惠后价格 计算可以使用的金币和积分


       $ye_jb = 0;
       $ye_jf = 0;
       if ($dk_jb > 0) {
           $ye_jb = GetClientJBJFnumb('jb', $this->clientid);//金币余额
           if ($ye_jb < $dk_jb) {
               //如果用户余额小于可以使用金币 则可以使用金币等于余额
               $dk_jb = $ye_jb;
           }
       }
       if ($dk_jf > 0) {
           $ye_jf = GetClientJBJFnumb('jf', $this->clientid);//金币余额
           if ($ye_jf < $dk_jf) {
               //如果用户余额小于可以使用金币 则可以使用金币等于余额
               $dk_jf = $ye_jf;
           }
       }
       //结束 --------------------根据用户余额和折扣后价格 计算最终付款使用的金币 积分


       //可以使用金币大于0 并且 (没有折扣或折扣后大于0)
       if ($dk_jb > 0) {

           $border = " ";
           if ($dk_jf > 0) $border = "list-group-item-border ";//如果可以使用积分大于0  则显示分隔符
           $jbjfdk_html .= "               <li class=\"list-group-item1 $border\">
                                               金币
                                               <span class=\"small text-muted\">
                                                   余额<span id='ye_jb'>{$ye_jb}</span>
                                                   可用<span id='dk_jb'>{$dk_jb}</span>
                                               </span>
                                               <span class=\"pull-right   text-danger\" style=\"margin-right: 5px\">
                                                <div class=\"checkbox i-checks\">
                                               <label>
                                                   <input type=\"checkbox\" value=\"\"  name=\"dk_jb_bottom\"  id=\"dk_jb_bottom\">
                                               </label>
                                               </div>

                                               </span>
                                           </li>
                           ";
       }


       if ($dk_jf > 0) {

           //$checkboxType = "hidden";
           //if ($dk_jf > 0) $checkboxType = "checkbox";//如果抵扣金分大于0 显示开关按钮
           $checkboxType = "checkbox";
           $jbjfdk_html .= "               <li class=\"list-group-item1 \">
                                               积分
                                               <span class=\"small text-muted\">
                                                       余额<span id='ye_jf'>{$ye_jf}</span>
                                                       可用<span id='dk_jf'>{$dk_jf}</span>
                                               </span>
                                               <span class=\"pull-right  text-danger\" style=\"margin-right: 5px\">
                                                <div class=\"checkbox i-checks\">
                                               <label>
                                                   <input type=\"checkbox\" value=\"\"  name=\"dk_jf_bottom\"  id=\"dk_jf_bottom\">
                                               </label>
                                               </div>
                                               </span>
                                           </li>
               ";
       }


       $jbjfdk_html .= "
                                   </ul>
                           ";

       return $jbjfdk_html;
   }


   function GetJejsHtmlToWeb()
   {
       $jejs_html = "";
       /* // 这个暂时不用了

       if (count($this->benefitInfo_array)>0){
           $jejs_html.="
                          规则时间<input type='text' name='benefitCreatetime' id='benefitCreatetime' value='$this->benefit_createtime'>
                          <br>二级金币<input type='text' name='fh_ejjb' id='fh_ejjb' value='$this->benefit_ejfhjb_numb'>
                          <br>二级积分<input type='text' name='fh_ejjf' id='fh_ejjf' value='$this->benefit_ejfhjf_numb'>
                          <br>三级金币<input type='text' name='fh_sjjb' id='fh_sjjb' value='$this->benefit_sjfhjb_numb'>
                          <br>三级积分<input type='text' name='fh_sjjf' id='fh_sjjf' value='$this->benefit_sjfhjf_numb'>

                           ";
       }
       return $jejs_html;
          if (!$this->benefit_zdsyjb_numb > 0 && !$this->benefit_zdsyjf_numb > 0) return "";
          $jejs_html = "<ul class=\"list-group list-group-plus list-font-color-black\"  \">
                          <li class=\"list-group-item1  \">
                              商品金额
                              <span class=\"pull-right   text-danger\" style=\"margin-right: 5px\">
                                  ￥<span id=\"js_je_display\">{$this->price}</span>
                              </span>";
          if ($this->benefit_zdsyjb_numb > 0) {
              $jejs_html .= "
                              <br>
                              使用金币
                              <span class=\"pull-right   text-danger\" style=\"margin-right: 5px\">
                                  -￥<span id=\"js_jb_display\">$this->benefit_zdsyjb_numb</span>
                              </span>
                          ";
          }
          if ($this->benefit_zdsyjf_numb > 0) {
              $jejs_html .= "<br>
                          使用积分
                          <span class=\"pull-right   text-danger\" style=\"margin-right: 5px\">
                              -￥<span id=\"js_jf_display\">$this->benefit_zdsyjf_numb</span>
                          </span>

                      </li>
                  </ul>
                  ";
          }
          $jejs_html .= "         </li>
                              </ul>
                      ";

    }*/
}



    