<?php if (!defined('DWTINC')) exit('Request Error!');
/**
 * 分类单元,主要用户管理后台管理处
 *
 */


/**
 * 分类单元,主要用户管理后台管理处
 *
 * @package          TypeUnit
 * @subpackage       DedeCMS.Libraries
 * @link
 */
class benefitClass
{
    var $dsql;
    var $th_str;//列头
    var $all_formname_str = ""; //所有的表单名称
    var $clientType_array = array(); //会员类型和类型值 数组  成长值

    /**
     * php5构造函数
     *
     *
     */
    function __construct($use_clientType_array = "")
    {
        $this->dsql = $GLOBALS['dsql'];


        //检出所有的会员类型
        //在提现规则中有同样的代码
        if ($use_clientType_array == "") {
            $query3 = "SELECT rank FROM `x_clientdata_ranklog` group by rank";
            $this->dsql->SetQuery($query3);
            $this->dsql->Execute("000");
            while ($row1 = $this->dsql->GetArray("000")) {
                $rank = $row1["rank"];
               /* if ($use_clientType_array != "" && count($use_clientType_array) > 0) {
                    //如果限制了,显示的会员类型,则不显示其他 的会员类型
                    if (!in_array($rank, $use_clientType_array)) continue;
                }*/
                $this->th_str .= "<th class='text-center' >$rank</th>";
                $this->clientType_array[] = array("rank" => $rank);
            }

               $query3 = "SELECT titles,scores FROM `x_clientdata_scoresname` ";
              //在提现规则中有同样的代码
              $this->dsql->SetQuery($query3);
              $this->dsql->Execute("111");
              while ($row1 = $this->dsql->GetArray("111")) {
                  $titles = $row1["titles"];
                  $scores = $row1["scores"];
                  /*if ($use_clientType_array != "" && count($use_clientType_array) > 0) {
                      //如果限制了,显示的会员类型,则不显示其他 的会员类型
                      if (!in_array($scores, $use_clientType_array)) continue;
                  }*/
                  $info = "({$scores}分以上)";
                  $this->clientType_array[] = array("scores" => $scores);
                  if ($scores == 0) $info = "(非会员)";
                  $this->th_str .= "<th class='text-center'>{$titles}{$info}</th>";
              }
        }else {

            foreach ($use_clientType_array as $value) {
                $info ="";
                if ($value == "0") $info = "(非会员)";
                $this->th_str .= "<th class='text-center' >$value{$info}</th>";
                if ($value == "0"){
                    $this->clientType_array[] = array("scores" => $value);
                }else{
                    $this->clientType_array[] = array("rank" => $value);
                }

            }
        }
        // $this->clientType_array=$use_clientType_array;
        //dump($this->clientType_array);
        //检出所有的成长值



    }

    /**
     * @param string $use_clientType_array 要显示 的数组类型
     */
    function benefitClass($use_clientType_array = "")
    {
        $this->__construct($use_clientType_array);
    }

    //清理类
    function Close()
    {
    }


    /**
     * 输出列头
     */
    function get_th_str()
    {
        return $this->th_str;
    }

    /**
     * 输出表单名称
     */
    function get_all_formname_str()
    {
        return $this->all_formname_str;
    }


    function getFormToCol($benefitType)
    {
        $td_str = "";
        $col_numb = 0;//列计数  如果是会员类型第一列,则输出  金币和积分的提示文字


        $td_str .= $this->getDateTemp($benefitType);
        foreach ($this->clientType_array as $key => $value) {
            foreach ($value as $clientType => $clientTypeValue) {
                $col_numb++;
                $td_str .= $this->getJBTemp($benefitType, $clientType, $clientTypeValue, $col_numb);
            }
        }


        return $td_str;
    }

    /**
     *
     * 表单模板
     *
     * @param               $benefitType     优惠类型
     *
     * @return string
     */
    function getDateTemp($benefitType)
    {
        //dump($benefitType);
        $form_name_s = "time_s-$benefitType";
        $form_name_e = "time_e-$benefitType";
        $this->all_formname_str .= ($this->all_formname_str == "") ? $form_name_s : ",$form_name_s";
        $this->all_formname_str .= ($this->all_formname_str == "") ? $form_name_e : ",$form_name_e";

        if ($benefitType == "金币使用") {
            //金币使用  使用日期多选模式
            $td_str = "<td class='text-center'>
                <div class='form-group' >
                    <div class='col-sm-6'>
                   <input type=\"text\" id=\"$form_name_s\"  name=\"$form_name_s\" value=\"\" style=\"*zoom:1;\">

                       
                    </div>
                </div>
                
                
            </td>";
        } else {
            //非金币使用 ,使用日期区间设置
            $td_str = "<td class='text-center'>
                <div class='form-group' >
                    <div class='col-sm-6'>
                        <input type='text' value='' placeholder='开始' readonly name='$form_name_s' id='$form_name_s' class='form-control  Wdate'
                         onfocus=\"WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd',maxDate:'#F{\$dp.\$D(\'$form_name_e\')}'})\"
                         />
                    </div>
                </div>
                
                <div class='form-group' >
                    <div class='col-sm-6'>
                        <input type='text' value='' placeholder='结束' readonly name='$form_name_e' id='$form_name_e' class='form-control  Wdate' 
                        onfocus=\"WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd',minDate:'#F{\$dp.\$D(\'$form_name_s\')}'})\"
                        />
                    </div>
                </div>
                如果选择了日期范围 <br>开始和结束日期必须同时输入
            </td>";
        }
        //dump($this->all_formname_str);//加的|是为了表单统一
        return $td_str;
    }

    /**
     * @param     $benefitType     优惠类型
     * @param     $clientType      会员类型/成长值
     * @param     $clientTypeValue 会员对应的值
     * @param     $col_numb        第一列输出 说明:有效期 金币 积分
     *
     * @return string
     */
    function getJBTemp($benefitType, $clientType, $clientTypeValue, $col_numb)
    {
        $form_name_jb = "jb-$benefitType-$clientType-$clientTypeValue";
        $form_name_jf = "jf-$benefitType-$clientType-$clientTypeValue";
        $this->all_formname_str .= ($this->all_formname_str == "") ? $form_name_jb : ",$form_name_jb";
        if ($benefitType != "购买优惠") $this->all_formname_str .= ($this->all_formname_str == "") ? $form_name_jf : ",$form_name_jf";


        $td_str = "<td >
                                        <div class='form-group'  >";

        $tip_name = "金币";
        if ($benefitType == "购买优惠") {
            $tip_name = "折扣%";
            //$maxnumb=" max='1' ";//如果是购买优惠 则最大的值为1
        }

        //if ($col_numb == 1) $td_str .= "       <label class='col-sm-3  form-control-static'>$tip_name:</label>";
        $td_str .= "             <div class='col-sm-6'>";
        //if ($col_numb > 1)$td_str .= "             <div class='col-sm-9' style='margin-left: 20px'>";
        $td_str .= "                             <input name='$form_name_jb' placeholder='$tip_name'  type='number' id='$form_name_jb' class='form-control' value='' style='max-width: 90px'   min='0' />
                                            </div>
                                        </div>";


        if ($benefitType != "购买优惠") {

            $td_str .= "                                
                                        <div class='form-group'  >";
            // if ($col_numb == 1) $td_str .= "                   <label class='col-sm-3  form-control-static'>积分:</label>";
            $td_str .= "             <div class='col-sm-6'>";
            //if ($col_numb > 1)$td_str .= "             <div class='col-sm-9' style='margin-left: 20px'>";
            $td_str .= "                                       <input name='$form_name_jf' placeholder='积分' type='number' id='$form_name_jf' class='form-control' value='' style='max-width: 90px'   min='0' />
                                            </div>
                                        </div>
                                    </td>";
        }
        return $td_str;

    }
}//End Class


