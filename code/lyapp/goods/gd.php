<?php
set_time_limit (0);
require_once(dirname(__FILE__) . "/../include/config.php");

//用户头像处理
$tx_img_path='../..'.getUploadFileAdd($DEPID).'qr/tx-'.$cfg_ml->M_ID.$cfg_ml->fields["senddate"].'.png';
//$tx_img_path=$cfg_ml->fields["photo"];
//dump($tx_img_path);
//if($tx_img_path=="")$tx_img_path='../../uploads/qr/tx.jpg';

if(!file_exists($tx_img_path))$tx_img_path="../../images/logo.jpg"; //如果头像获取不到,默认的头像

$tx_info = getimagesize($tx_img_path);
if($tx_info['mime'] == 'image/jpeg')
{
    $txim = imagecreatefromjpeg($tx_img_path);
}
elseif($tx_info['mime'] == 'image/gif')
{
    $txim = imagecreatefromgif($tx_img_path);
}
elseif($tx_info['mime'] == 'image/png')
{
    $txim = imagecreatefrompng($tx_img_path);
}


//生成二维码
require_once(DWTINC .'/qrcode.class.php');
$qrpng_name='../..'.getUploadFileAdd($DEPID).'qr/'.$CLIENTID.$cfg_ml->fields["senddate"].'.png';
$params=array();
//$params['data'] = 'http://www.baidu.com/?sid='.$cfg_ml->M_ID;//生成的连接
$params['data'] ="http://".$_SERVER['SERVER_NAME']."/lyapp/goods/goods_view.php?id=1&u=$CLIENTID&did=$DEPID";//生成的连接
$params['size'] = 6;
$params['savename'] = $qrpng_name;//二维码存储地址
$qrcode = new DwtQrcode;
$eeeee=$qrcode->generate($params);




//$bg = imagecolorallocate($im, 0xFB, 0xF9, 0xFA);
// Create the image
$im = imagecreatetruecolor(400, 610);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$lanse = imagecolorallocate($im, 0x00,0x4D, 0xD2);
$black = imagecolorallocate($im, 0, 0, 0);
$bg1 = imagecolorallocate($im, 0xFB, 0xF9, 0xFA);
$bg2 = imagecolorallocate($im, 0x99, 0x01, 0x00);

// 画一矩形并填充
imagefilledrectangle($im, 0, 0, 400, 280, $bg1);
imagefilledrectangle($im, 0, 280, 400, 610, $bg2);
// The text to draw
$t1 = '我是';
$t2 = '邀您使用';
$t3 = ' 旅游景区直通车会员卡';
//$t4 = 'xx 系统';

$t6 = '长按此图 识别图中二维码 搞定';
$t7 = '一码不扫 何以扫天下';
$t8 = $cfg_ml->fields['realname'];
if($t8=="")$t8=$cfg_ml->fields['nickname'];
$t9 = '已认证';

$t10 = '       购买此会员卡，一年内可无限次乘坐景区直通';
$t11 = '车，扫码购买即送200消费积分（可抵现金使用，用';
$t12 = '于平台内其他旅游产品或商品的消费。）';
$t13 = ' 购买成功后点击推广，朋友购买还可领取推广奖励哟！';


// Replace path by your own font path 微软雅黑字体
$font = '../../ui/fonts/msyh.ttc';

// Add the text
//array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
imagettftext($im, 14, 0, 160, 40, $black, $font, $t1);//我是
imagettftext($im, 14, 0, 210, 40, $bg2, $font, $t8);//姓名
imagettftext($im, 14, 0, 275, 40, $black, $font, $t9);//已认证

imagettftext($im, 14, 0, 200, 80, $black, $font, $t2);//邀您使用
imagettftext($im, 20, 0, 25, 130, $lanse, $font, $t3);//蓝色系统名称

imagettftext($im, 12, 0, 20, 160, $black, $font, $t10);
imagettftext($im, 12, 0, 20, 190, $black, $font, $t11);
imagettftext($im, 12, 0, 20, 220, $black, $font, $t12);

imagettftext($im, 12, 0, 5, 260, $bg2, $font, $t13);

imagettftext($im, 12, 0, 90, 570, $white, $font, $t6);
imagettftext($im, 12, 0, 120, 600, $white, $font, $t7);

//读取二维码图片
$qrim = imagecreatefrompng($qrpng_name);

//拷贝图像的一部分 bool imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )
imagecopy( $im , $qrim, 90 , 320 , 0 , 0, 222 , 222 );

//bool imagecopyresized ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
imagecopyresized( $im , $txim , 20 , 10, 0 , 0 , 80 , 80 , $tx_info[0], $tx_info[1] );


ob_clean();//清空（擦掉）输出缓冲区
// Set the content-type
header('Content-Type: image/png');

imagepng($im);
//imagepng($im,$png_name);

imagedestroy($im);

