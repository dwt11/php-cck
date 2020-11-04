<?php

/**
 * 返回FLAG
 * @param $flag
 *
 * @return string
 */
function flag($flag)
{
	if($flag=='') return '';
	else return "[<span class='  text-warning' >$flag</span>]";
}



////获取订单的所有合计收入(不包含支出项,只算用户支付了的)
/*function getTotalMoney($id)
{
		global $dsql;
	
		  $totalMoney=0.00;
		  $dsql->SetQuery("SELECT saleslogtype,money FROM x_sales_log   where  salesid=$id  ");
		  $dsql->Execute($id);
		  while($row_z = $dsql->GetArray($id))
		  {
			  if($row_z['saleslogtype']=='3.1'||$row_z['saleslogtype']=='3.2')
			  {//支出减项
				  //$totalMoney-=$row_z['money'];
			  }else
			  {//收入增加
				  $totalMoney+=$row_z['money'];
			  
				}
			  
		  }
		 // dump($totalMoney);
		  return $totalMoney;

}*/

//获取订单的状态的颜色和说明
//$status   订单状态
//$money  未结算金额
/*function getStatusInfo($status,$money)
{
    //获取订单状态
      $statusColor="<span style='color:#ffffff;'>■</span> ";  //默认白色
      $statusinfo="完成";
      
      //(定做&&余款>0)||(定做&&状态不是发小样不是制作完)                  ||(成品&&余款>0)
      if(($money>0&&$status==4&&$salestype==1)||($money>0&&$salestype==2)){$statusColor="<span style='color:#ff0000'>■</span> ";$statusinfo="有余款未支付";}  //定做做完,未付余款||成品只支付部分.未付余款

      //(定做&&余款>0)||(定做&&状态不是发小样不是制作完)                  ||(成品&&余款>0)
      if($status<3&&$salestype==1){$statusColor="<span style='color:#F0F'>■</span> ";$statusinfo="订做未开工";}  //定做做完,未付余款||成品只支付部分.未付余款


     
     //(定做&&状态是制作完&余款结清)                                || (成品&&不是完成&&不是取消&余款结清)
	 //dump($status."---".$money."---".$salestype);
     if(($status==4&&$money<=0&&$salestype==1)||($status!=10&&$status!=-1&&$money<=0&&$salestype==2)){$statusColor="<span style='color:#009966'>■</span> ";$statusinfo="款项结清,未调试";}  //(成品+定做) 款项结清 未调试的
     
     //取消的订单
     if($status==-1){$statusColor="<span style='color:#cccccc'>■</span> ";$statusinfo="订单已取消";}  //订单取消
 
      //订单状态发小样
      if($status==3){$statusColor="<span style='color:#66CCCC;'>■</span> ";$statusinfo="已经发送小样";}  //已经发小样

	 return $statusColor.$statusinfo;
}
*/






















