<?php
/**
 * 订单编辑
 *
 * @version        $Id: sales_edit.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once("sales.functions.php");
require_once DWTINC.'/enums.func.php';  //获取数据字典对应的值


if(empty($dopost)) $dopost = '';
// var_dump($salesnumb1);exit;
/*--------------------------------
function __save(){   }
-------------------------------*/
if($dopost=='save')
{
	  if(empty($mobilephone)) $mobilephone = '';
	  if(empty($installaddress)) $installaddress = '';
	  if(empty($tag)) $tag = '';
	  if(empty($realname)) $realname = '';
	
	
	
	$salesid=$id;
	
	if($mobilephone==""||$installaddress==""||$realname==""||$tag=="")	
        {
		ShowMsg("客户的姓名、联系电话、小区名称和安装地址不能为空！",-1);
		exit(); 
	}

	//$returnmsg="";//订单保存后的提示信息
    



	$plantotalmoney=$money;
	$senddate=GetMkTime($senddate);
	$pubdate=$senddate;
	$plancompletedate=GetMkTime($plancompletedate);
	$description=trim($description);
	$status=1;//新的订单 


        /*客户信息*/
	  $mobilephone=trim($mobilephone);
	  $installaddress=trim($installaddress);
	  $tag=trim($tag);
	  $realname=trim($realname);
	
	
	//检查用户是否存在  如果同一个客户输入了两个不同的小区 或手机,则添加多条客户信息
	$questr="SELECT id FROM `x_people_client` where  realname='$realname' or mobilephone='$mobilephone'";
	$rowarc = $dsql->GetOne($questr);
	// var_dump($rowarc);exit;
	if(!is_array($rowarc))
        {
		//如果未找到,则添加为新客户
		$sqladdclient="INSERT INTO `x_people_client` (`clienttype`,realname, `mobilephone`,`address`,`tag`, `senddate`, `pubdate`, `description`, `readgoodsid`, `userid`)
		 VALUES ('1', '$realname', '$mobilephone', '$installaddress', '$tag', '$senddate', '$senddate', '$description', '', '$userid')";
		$dsql->ExecuteNoneQuery($sqladdclient);
		$clientid=$dsql->GetLastID();
	}else{
		
		
		//如果有旧的信息,则更新类型 日期 和安装地址
		$clientid=$rowarc['id']; //已经存在此客户,则使用他的ID
		//更新客户的pubdate和客户类型
		$query = "UPDATE x_people_client SET address='$installaddress',clienttype='1',pubdate='$senddate'  WHERE id='$clientid'; ";
		if(!$dsql->ExecuteNoneQuery($query))
		{
			ShowMsg('更新数据表时出错，请检查',-1);
			exit();
		}
	}

   
   
   
   
   
    //更新订单数据库的SQL语句

    $query = "UPDATE `x_sales` SET 
	`clientid`='$clientid',
	`plantotalmoney`='$plantotalmoney', 
	`senddate`='$senddate', 
	`pubdate`='$pubdate', 
	`plancompletedate`='$plancompletedate', 
	`completedate`='NULL', 
	`installaddress`='$installaddress', 
	`description`='$description', 
	`status`='1'
	 WHERE (`id`='$salesid'); ";
    if(!$dsql->ExecuteNoneQuery($query))
    {
        ShowMsg('更新订单数据表时出错，请检查',-1);
        exit();
    }
	
    
    //删除订单的商品信息
	$query = "delete from `x_sales_goods` WHERE (`salesid`='$id'); ";
    if(!$dsql->ExecuteNoneQuery($query))
    {
        ShowMsg('删除订单商品时出错，请检查',-1);
        exit();
    }
	
	//循环15个商品,如果有商品编号,则添加到销售商品表这里的
	//同时添加到销售记录表
	for($goodsi=1;$goodsi<16;$goodsi++)
	{
		  $goodscode=$money=$files=$desc_temp=$goodsdesc="";
		  $ename_goodscode="goodscode".$goodsi;
		  if(!empty($$ename_goodscode))
		  {
				
				$goodscode=$$ename_goodscode;
				$questr="SELECT id FROM `x_goods` where  `goodscode` ='$goodscode' ";
				$rowarc = $dsql->GetOne($questr);
				if(is_array($rowarc))
				{
					$goodsid=$rowarc['id'];
				}else{
					$goodsid=0;
					//$desc_temp="无此商品 订单提交商品编号为$goodscode";
				}
				
				//$ename_desc="goodsdescription".$goodsi;
				//$goodsdesc=$$ename_desc.$desc_temp;
				$ename_price="salesprice".$goodsi;
				$ename_numb="salesnumb".$goodsi;
				
				
				$salesnumb=GetGoodsPianToData($goodscode, $$ename_numb);//计算片数,存入数据库
				//dump($salesnumb);
				
				$sqladdsalsesgoods="INSERT INTO `x_sales_goods` ( `salesid`, `goodsid`, `salesnumb`, `salesprice`,  `senddate`)
				VALUES ('$salesid', '$goodsid', '".$salesnumb."','".$$ename_price."','$senddate')";

				
				$dsql->ExecuteNoneQuery($sqladdsalsesgoods);
	            //dump($sqladdsalsesgoods);
				
				
		  }
	}
    
	
	
				
				
				
	
	PutCookie('lastsalesmoreid', $salesid, 3600*24, "/");//最后浏览的订单号
	
    ShowMsg('订单修改成功','sales.php');
	exit;
}elseif($dopost== '')
{
	//获取订单信息
	$questr="SELECT sa.*,cl.mobilephone,cl.realname,cl.tag
	 FROM `x_sales` sa
	LEFT JOIN x_people_client cl on cl.id=sa.clientid
	 where  sa.id=".$id;
	//dump($questr);
	$rowsales = $dsql->GetOne($questr);
	if(!is_array($rowsales))
        {
		echo ("获取订单信息失败！请刷新");
		exit(); 
	}
	//获取采购编号
	$salescode="DD".$rowsales['salescode'];
	
	//采购单包含的商品数量 用于JS生成自动的商品表单
	$goodsnumb=0;

	$questr="SELECT count(*) as dd  FROM `x_sales_goods` where  salesid='$id'";
	$rowgoodsnumb = $dsql->GetOne($questr);
	if($rowgoodsnumb['dd']>0)
       {
		$goodsnumb=$rowgoodsnumb['dd'];
	}

}




?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
	<title>订单编辑 订单号:<?php echo $salescode;?></title>

	<link href="../include/auto.complete.do/jquery.autocomplete.css" type=text/css rel=stylesheet>
	<link href="../ui/css/bootstrap.min.css" rel="stylesheet">
	<link href="../ui/css/font-awesome.min.css" rel="stylesheet">
	<link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
	<link href="../ui/css/animate.min.css" rel="stylesheet">
	<link href="../ui/css/style.min.css" rel="stylesheet">
	<!-- <link href="../ui/css/base.css" rel="stylesheet"> -->

	<script src="../ui/js/jquery.min.js"></script>
	<script src="../ui/js/bootstrap.min.js"></script>
	<script src="../ui/js/content.min.js"></script>
	<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
	<script type="text/javascript" src="../include/auto.complete.do/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="sales.js"></script>

	<script language="javascript">
	<!--
	    autocomplete('installaddress','x_people_client','address');//加载自动完成
	    autocomplete('tag','x_people_client','tag');//加载自动完成
	-->
	</script>
	<script type="text/javascript" >
	var i=<?php echo $goodsnumb?>;//设定默认的商品个数 在sales.js中调用 
	</script>
</head>
<body class="gray-bg" onload="InitPage()">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5>订单编辑 &nbsp;&nbsp;订单号:<?php echo $salescode;?></h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                	
                    <!--表格数据区  开始-->
					<form name="form1" id="form1" action="sales_edit.php" method="post" role="form" class="form-horizontal">
					<input type="hidden" name="dopost" value="save" />
					<input type="hidden" name="id" value="<?php echo $id?>" />
					<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" id="salesdata">
						<tr>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">订单建立日期</label>
								  <div class="col-sm-4">
								  	 <?php $nowtime = GetDateMk($rowsales['senddate']); ?>
									 <input type="text"  name="senddate" class="form-control Wdate" value="<?php echo GetDateMk(time());?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">计划安装日期</label>
								  <div class="col-sm-4">
								  	 <?php $nowtime =GetDateMk($rowsales['plancompletedate']); ?>
									 <input type="text" name="plancompletedate" class="form-control Wdate" value="<?php echo GetDateMk(time());?>"   onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd'})"/>
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
						</tr>
						
						<tr>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">姓名</label>
								  <div class="col-sm-4">
									 <input type="text" class="form-control" id="realname" name="realname" value="<?php echo $rowsales['realname'];?>">
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">联系方式</label>
								  <div class="col-sm-4">
									 <input type="text" class="form-control" id="mobilephone" name="mobilephone" value="<?php echo $rowsales['mobilephone'];?>">
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
						</tr>
						
						<tr>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">客户地址</label>
								  <div class="col-sm-4">
									 <input type="text" class="form-control" id="installaddress" name="installaddress" value="<?php echo $rowsales['installaddress'];?>">
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
							<td>
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">小区名称</label>
								  <div class="col-sm-4">
									 <input type="text" class="form-control" id="tag" name="tag" value="<?php echo $rowsales['tag'];?>">
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
						</tr>

						<tr>
							<td width="60%">
								<div class="form-group">
								  <label for="" class="col-sm-2 control-label">订单备注</label>
								  <div class="col-sm-4">
									 <textarea class="form-control" rows="3" name="description" cols="15"><?php echo $rowsales['description'];?></textarea>
								  </div>
								  <div class="col-sm-6"></div>
								</div>
							</td>
							<td></td>
						</tr>
					</table>
					<style type="text/css">
						#salesdata td .col-sm-2{
							width:20%;
						}
						textarea{
							resize:none;
						}
					</style>
					
					<div class="form-group">
					  <div class="col-sm-10">
						 <button type="button" class="btn btn-primary" name="bbb" onclick="AddGoodsTr();">增加商品选项</button>
					  </div>
				    </div>
					
					<div class="table-responsive">
					<table class="table table-bordered" id="goodslist">
						<thead>
							<tr>
								<th></th>
								<th class="text-center">序号</th>
								<th class="text-center">商品编号</th>
								<th class="text-center">商品名称</th>
								<th class="text-center">销售单价</th>
								<th class="text-center">销售数量</th>
								<th class="text-center">总价(元)</th>
								<th class="text-center">销售单位</th>
								<!-- <th></th> -->
							</tr>
							<?php
		  						$querygoods = "SELECT salesgoods.*,goods.typeid,goods.goodsname,goods.goodscode,goods.chargeunit as saleschargeunit,gtype.typename,gtype.channeltype 
					FROM x_sales_goods salesgoods 
					LEFT JOIN `x_goods` goods  on goods.id=salesgoods.goodsid
					LEFT JOIN x_goods_type gtype on goods.typeid=gtype.id
					where salesgoods.salesid=$id
					  ORDER BY   goods.id asc ";
				    //dump($query);
				    $dsql->SetQuery($querygoods);
				    $dsql->Execute();
				    $i=1;

				  	while($rowgoods = $dsql->GetArray())
				  	{
		  
					  $everychargeunit="";//每包多少片
					  $everychargeunitm2="";//每片多少平方,要根据这个来算一平方多少包
					  $salesnumb=GetGoodsM2ToInput($rowgoods['goodscode'],$rowgoods['salesnumb']);   //根据数据库的算数 计算包数
					  $salesprice=$rowgoods['salesprice']; //采购单价
					  $chargeunit=GetEnumsValue('chargeunit',$rowgoods['saleschargeunit']);
					  if($rowgoods['channeltype']==1)
					  {
							$chargeunit="平方米"; //如果是 则采购单位是固定的包
								  
							//获取计算单位
							  $query1="SELECT  everychargeunit,everychargeunitm2 
									 FROM `#@__goods_addon1`
									  where   gid = '".$rowgoods['goodsid']."'";
							  $row1 = $dsql->GetOne($query1);
							  if(is_array($row1))
							  {//如果有此商品扩展信息, 则返回数据
								  $everychargeunit=$row1['everychargeunit'];//每包多少片
								  $everychargeunitm2=$row1['everychargeunitm2'];//每片多少平方,要根据这个来算一平方多少包
							  }
					   
					   
					   
					   }
					  $singlegoodstotal=number_format($salesprice*$salesnumb,2);

					?>
						</thead>
						<tbody>
							<tr id='tr<?php echo $i;?>'>
								<td class="text-center col-xs-1">
								<!-- <a class="btn btn-white" onClick='removeGoodsTr(<?php echo $i;?>);' name="remove1">删除</a> -->
								</td>
								<td class="text-center col-xs-1"><?php echo $i;?></td>
								<td class="col-xs-2"><input type="text" name='goodscode<?php echo $i;?>' id='goodscode<?php echo $i;?>' value='<?php echo $rowgoods['goodscode']?>' class="form-control data_goodscode goodscode"></td>
								<td><span id='_goodsname<?php echo $i;?>'>(<?php echo $rowgoods['typename']?>)<?php echo $rowgoods['goodsname']?></span></strong> 
							    <span id='_goodsAlert<?php echo $i;?>' style='color:#999'></span></td>
								<td class="col-xs-1"><input name='salesprice<?php echo $i;?>' type='text' id='salesprice<?php echo $i;?>' value='<?php echo $salesprice;?>' class="form-control"></td>
								<td class="col-xs-1"><input name='salesnumb<?php echo $i;?>' type='text' id='salesnumb<?php echo $i;?>' value='<?php echo $salesnumb;?>' class="form-control" required></td>
								<td class="text-center col-xs-1"><span id='_singlegoodstotal<?php echo $i;?>'><?php echo $singlegoodstotal;?></span></td>
								<td class="text-center col-xs-1"><span id='_chargeunit<?php echo $i;?>'><?php echo $chargeunit;?></span></td>
								<!-- <td>
									<span id='_everychargeunitm2<?php echo $i;?>' style="display:none"><?php echo $everychargeunitm2;?></span>
							     <span id='_everychargeunit<?php echo $i;?>'  style="display:none"><?php echo $everychargeunit;?></span> 
							     <span id='_desc<?php echo $i;?>'></span>
						         <span id='_everyunit<?php echo $i;?>'>每片<?php echo $everychargeunitm2;?>平方米 每包<?php echo $everychargeunit;?>片</span>
								</td> -->
							</tr>
								<?php      
									$i++;
								}?>
						</tbody>
					</table>
					</div>
					
					<div class="form-group">
					  <label for="" class="col-sm-2 control-label">订单总价</label>
					  <div class="col-sm-2 input-group">
						 <input type="text" class="form-control" id="money" name="money" placeholder="" value="<?php echo $rowsales['plantotalmoney'];?>">
						 <span class="input-group-addon">元</span>
					  </div>
					</div>
					
					<div class="form-group">
					  <div class="col-sm-offset-2 col-sm-10">
						 <button type="submit" class="btn btn-primary">添加</button>
						 <a class="btn btn-white" onClick="location.reload();">重置</a>
					  </div>
				   </div>
						
					</form>
                    <!--表格数据区------------结束-->
                </div>

               
            </div>
        </div>

    </div>
</div>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script type="text/javascript">
    $().ready(function () {	
        $("#form1").validate({
            rules: {
                realname: {required: !0},
                mobilephone: {required: !0, minlength: 11, isMobile: !0},
                installaddress:{required: !0},
                tag:{required: !0},
                goodscode1:{required: !0},
                salesnumb1:{required: !0}
            },
            messages: {
                realname: {required: "请填写姓名"},
                mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "请正确填写您的手机号码"},
                installaddress: {required: "请填写地址"},
                tag: {required: "请填写小区名称"},
                goodscode1: {required: "请填写商品编号"},
                salesnumb1: {required: "请填写商品数量"}
            }
        })
    });
</script>
<script type="text/javascript">
	$('body').on('click','.goodscode',function(){
		var i = $(this).attr('id').substring(9,10);
		layer.open({type: 2,title: '商品信息', content: 'goods_message.php?id='+i,});
	})
</script>
</body>
</html>
