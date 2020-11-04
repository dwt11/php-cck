<?php
set_time_limit(0);
require_once(dirname(__FILE__) . "/../include/config.php");

//用户头像处理
$tx_img_path = '../..' . getUploadFileAdd($DEPID) . 'qr/tx-' . $cfg_ml->M_ID . $cfg_ml->fields["senddate"] . '.png';
if (!file_exists($tx_img_path)) $tx_img_path = "../../images/logo.jpg"; //如果头像获取不到,默认的头像

$tx_info = getimagesize($tx_img_path);
if ($tx_info['mime'] == 'image/jpeg') {
    $txim = imagecreatefromjpeg($tx_img_path);
} elseif ($tx_info['mime'] == 'image/gif') {
    $txim = imagecreatefromgif($tx_img_path);
} elseif ($tx_info['mime'] == 'image/png') {
    $txim = imagecreatefrompng($tx_img_path);
}


$qrpng_name="../../images/ew.jpg";//默认公众号二维码
if($CLIENTID>0) {
//获取微信二维码图片,并保存到目录
    $qrpng_name_t = getUploadFileAdd($DEPID) . 'qr/' . $CLIENTID . $cfg_ml->fields["senddate"] . '.jpg';
    $qrpng_name = '../..' . $qrpng_name_t;
    if (!file_exists($qrpng_name)) {
        //从微信获取二维码参数
        $appId = GetWeixinAppId($DEP_TOP_ID);
        $appSecret = GetWeixinAppSecret($DEP_TOP_ID);
        $ACCESS_TOKEN = Get_access_token($appId, $appSecret);
        $template = array(
            'action_name' => "QR_LIMIT_STR_SCENE",//二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
            'action_info' => array('scene' => array('scene_str' => "$CLIENTID"))
            //'action_info' => array('scene' => array('scene_str' => "6130"))
        );
        $template = urldecode(json_encode($template));
        //dump($template);
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$ACCESS_TOKEN";
        $result = http_request_json($url, $template);
        //dump($result);
        if (isset($result["ticket"]) && $result["ticket"] != "") {
            $result = json_decode($result, true);


            //如果没有此文件 则从微信获取
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $qrpng_name_t;//实际地址
            $dirname = dirname($filePath);//目录名称
            $weixinImgUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result["ticket"];

            $img = downloadImageFromWeiXin($weixinImgUrl);
            //创建目录失败
            if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            } else if (!is_writeable($dirname)) {
            }

            //移动文件
            if (!(file_put_contents($filePath, $img["body"]) && file_exists($filePath))) { //移动失败
            } else { //移动成功
                // $imgUrl = $qrpng_name . ";";
            }
        }
    }
}



//$bg = imagecolorallocate($im, 0xFB, 0xF9, 0xFA);
// Create the image
$im = imagecreatetruecolor(400, 610);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$lanse = imagecolorallocate($im, 0x00, 0x4D, 0xD2);
$black = imagecolorallocate($im, 0, 0, 0);
$bg1 = imagecolorallocate($im, 0xFB, 0xF9, 0xFA);
$bg2 = imagecolorallocate($im, 0x99, 0x01, 0x00);

// 画一矩形并填充
imagefilledrectangle($im, 0, 0, 400, 280, $bg1);//上半部白色背景
//imagefilledrectangle($im, 0, 280, 400, 610, $bg2);//下半部背景纯色填充
// The text to draw

$t8 = $cfg_ml->fields['realname'];
if ($t8 == "") $t8 = $cfg_ml->fields['nickname'];
if ($t8 == "") $t8 = "中国人";//默认显示的姓名
$query = "SELECT * FROM `#@__tg_config` WHERE  id=1";
//dump($query);
$row = $dsql->GetOne($query);
$t1 = $row["t1"];
$t2 = $row["t2"];
$t3 = $row["t3"];
$t4 = $row["t4"];
$t5 = $row["t5"];
$backpic = $row["backpic"];



// Replace path by your own font path 微软雅黑字体
$font = '../../ui/fonts/msyh.ttc';

// Add the text
//array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
imagettftext($im, 14, 0, 180, 40, $black, $font, "我是");//我是
imagettftext($im, 16, 0, 230, 40, $bg2, $font, $t8);//姓名
//imagettftext($im, 14, 0, 275, 40, $black, $font, $t9);//已认证

imagettftext($im, 14, 0, 110, 80, $black, $font, $t1);//邀您使用
imagettftext($im, 20, 0, 25, 130, $lanse, $font, $t2);//蓝色系统名称


//$str = '       购买此会员卡，一年内可无限次乘坐景区直通车，扫码购买即送200消费积分（可抵现金使用，用于平台内其他旅游产品或商品的消费。）                               购买成功后点击推广，朋友购买还可领取推广奖励哟！';
//$str = '       2018年1月1日前推广三位会员，可获赠一年会员资格！';


$temp = array("color" => array(0, 0, 0), "fontsize" => 12, "width" => 400, "leftAright" => 10, "top" => 130, "hang_size" => 35);
//这里我只用它做测量高度，把参数false改为true就是绘制了。
$str_h = draw_txt_to($im, $temp, $t3, true);
//dump($str_h);

/*imagettftext($im, 12, 0, 20, 160, $black, $font, $t10);
imagettftext($im, 12, 0, 20, 190, $black, $font, $t11);
imagettftext($im, 12, 0, 20, 220, $black, $font, $t12);
*/


//imagettftext($im, 12, 0, 5, 260, $bg2, $font, $t13);


//改变为222/222
//$qrim = imagecreatefrompng($qrpng_name);
//dump($qrim);
//exit;

//拷贝图像的一部分 bool imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )

//imagefilledrectangle($im, 0, 280, 400, 610, $bg2);//下半部背景纯色填充

//背景2图片
$bg2_pic_name = '../..' . $backpic;
$bg2_pic_im = imagecreatefromjpeg($bg2_pic_name);//背景2图片
//               元素  图片          左起点    上起点
imagecopyresized($im, $bg2_pic_im, 0, 280, 0, 0, 400, 330, 400, 330);


imagettftext($im, 12, 0, 90, 570, $white, $font, $t4);
imagettftext($im, 12, 0, 120, 600, $white, $font, $t5);

//读取二维码图片
$qrim = imagecreatefromjpeg($qrpng_name);//原始大小430X430
//写入二维码
imagecopyresized($im, $qrim, 90, 320, 0, 0, 222, 222, 430, 430);
//头像
imagecopyresized($im, $txim, 20, 10, 0, 0, 80, 80, $tx_info[0], $tx_info[1]);

/*bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )

$dst_image：新建的图片
$src_image：需要载入的图片
$dst_x：设定需要载入的图片在新图中的x坐标
$dst_y：设定需要载入的图片在新图中的y坐标
$src_x：设定载入图片要载入的区域x坐标
$src_y：设定载入图片要载入的区域y坐标
$dst_w：设定载入的原图的宽度（在此设置缩放）
$dst_h：设定载入的原图的高度（在此设置缩放）

$src_w：原图要载入的宽度

$src_h：原图要载入的高度*/


//imagecopy( $im , $im_tt, 90 , 320 , 0 , 0, 222 , 222 );
//bool imagecopyresized ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )


ob_clean();//清空（擦掉）输出缓冲区
// Set the content-type
header('Content-Type: image/png');

imagepng($im);
//imagepng($im,$png_name);

imagedestroy($im);


/**
 * 文字自动换行算法
 *
 * @param $card     画板
 * @param $pos      数组，top距离画板顶端的距离，fontsize文字的大小，width宽度，leftAright左右边的距离，hang_size行高
 * @param $str      要写的字符串
 * @param $iswrite  是否输出，ture，  花出文字，false只计算占用的高度
 *
 * @return int 返回整个字符所占用的高度
 */

function draw_txt_to($card, $pos, $str, $iswrite)
{

    $_str_h = $pos["top"];
    $fontsize = $pos["fontsize"];
    $width = $pos["width"] - $pos["leftAright"];
    $margin_lift = $pos["leftAright"];
    $hang_size = $pos["hang_size"];
    $temp_string = "";
    $font_file = '../../ui/fonts/msyh.ttc';
    $tp = 0;

    $font_color = imagecolorallocate($card, $pos["color"][0], $pos["color"][1], $pos["color"][2]);
    for ($i = 0; $i < mb_strlen($str); $i++) {

        $box = imagettfbbox($fontsize, 0, $font_file, $temp_string);
        $_string_length = $box[2] - $box[0];
        $temptext = mb_substr($str, $i, 1, 'utf-8');

        $temp = imagettfbbox($fontsize, 0, $font_file, $temptext);

        if ($_string_length + $temp[2] - $temp[0] < $width) {//长度不够，字数不够，需要

            //继续拼接字符串。

            $temp_string .= mb_substr($str, $i, 1, 'utf-8');

            if ($i == mb_strlen($str, 'utf-8') - 1) {//是不是最后半行。不满一行的情况
                $_str_h += $hang_size;//计算整个文字换行后的高度。
                $tp++;//行数
                if ($iswrite) {//是否需要写入，核心绘制函数
                    imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, $temp_string);
                }

            }
        } else {//一行的字数够了，长度够了。

//            打印输出，对字符串零时字符串置null
            $texts = mb_substr($str, $i, 1, 'utf-8');//零时行的开头第一个字。

//            判断默认第一个字符是不是符号；
            $isfuhao = preg_match("/[\\\\pP]/u", $texts) ? true : false;//一行的开头这个字符，是不是标点符号
            if ($isfuhao) {//如果是标点符号，则添加在第一行的结尾
                $temp_string .= $texts;

//                判断如果是连续两个字符出现，并且两个丢失必须放在句末尾的，单独处理
                $f = mb_substr($str, $i + 1, 1, 'utf-8');
                $fh = preg_match("/[\\\\pP]/u", $f) ? true : false;
                if ($fh) {
                    $temp_string .= $f;
                    $i++;
                }

            } else {
                $i--;
            }

            $tmp_str_len = mb_strlen($temp_string);
            $s = mb_substr($temp_string, $tmp_str_len - 1, 1, 'utf-8');//取零时字符串最后一位字符

            if (is_firstfuhao($s)) {//判断零时字符串的最后一个字符是不是可以放在见面
                //讲最后一个字符用“_”代替。指针前移动一位。重新取被替换的字符。
                $temp_string = rtrim($temp_string, $s);
                $i--;
            }
//            }

//            计算行高，和行数。
            $_str_h += $hang_size;
            $tp++;
            if ($iswrite) {

                imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, $temp_string);
            }
//           写完了改行，置null该行的临时字符串。
            $temp_string = "";
        }
    }

    return $tp * $hang_size;

}


function is_firstfuhao($str)
{
    $fuhaos = array("\\", "“", "'", "<", "《",);
    return in_array($str, $fuhaos);
}
