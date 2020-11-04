<?php
$GLOBALS['baseConfigFunArray'] = array();
/* $GLOBALS['baseConfigFunArray']['目录名称']           
第1行    目录名称* $GLOBALS['baseConfigFunArray']['目录名称'][]='deviceKnowledge.php,设备知识库_管理,device_img,id,did(device|id|depid),userid,,1';           
第2行    0主文件地址，1文件功能说明标题，2数据表名称 ,3ID编号名称,4A部门数据字段(B上级关联数据表名称|与A关联的上级数据表的ID字段|最终取出的部门字段名称),5用户数据字段,6子分类字段,7是否部门数据
* $GLOBALS['baseConfigFunArray']['目录名称'][主文件名称]
第3-X行   0动作文件地址 ,1文件功能说明标题,2是否部门数据
* 如果部门数据字段或用户数据字段有值 and 是否部门数据为1 则权限不显示部门选择框
* 如果部门数据字段或用户数据字段有值 and 是否部门数据为0或空 则权限显示部门选择框
*/
$GLOBALS['baseConfigFunArray']['archives'][]='文档管理';
$GLOBALS['baseConfigFunArray']['archives'][]='archives.php,文档_管理,archives,id,,userid,typeid,';
$GLOBALS['baseConfigFunArray']['archives']['archives.php'][]='archives_add.php,文档_添加,1';
$GLOBALS['baseConfigFunArray']['archives']['archives.php'][]='archives_del.php,文档_删除,';
$GLOBALS['baseConfigFunArray']['archives']['archives.php'][]='archives_edit.php,文档_编辑,';

$GLOBALS['baseConfigFunArray']['archives'][]='catalog.php,文档栏目_管理,archives_type,id,,,,';
$GLOBALS['baseConfigFunArray']['archives']['catalog.php'][]='catalog_add.php,文档栏目_添加,';
$GLOBALS['baseConfigFunArray']['archives']['catalog.php'][]='catalog_del.php,文档栏目_删除,';
$GLOBALS['baseConfigFunArray']['archives']['catalog.php'][]='catalog_edit.php,文档栏目_编辑,';

$GLOBALS['baseConfigFunArray']['archives'][]='channel.php,文档模型_管理,archives_channeltype,id,,,,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_add.php,文档模型_添加,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_del.php,文档模型_删除,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_edit.php,文档模型_编辑,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_field.php,文档模型_字段管理,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_field_add.php,文档模型_字段添加,';
$GLOBALS['baseConfigFunArray']['archives']['channel.php'][]='channel_field_edit.php,文档模型_字段编辑,';

$GLOBALS['baseConfigFunArray']['archives'][]='feedback.php,评论反馈_管理,archives_feedback,id,,,,';

$GLOBALS['baseConfigFunArray']['archives'][]='recycling.php,文档回收站,archives,id,,,,';






$GLOBALS['baseConfigFunArray']['emp'][]='员工管理';
$GLOBALS['baseConfigFunArray']['emp'][]='emp.php,员工_管理,emp,emp_id,emp_dep,,,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_add.php,员工_添加,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_del.php,员工_删除,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_edit.php,员工_编辑,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_updateClient.php,员工_前台会员微信绑定,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_user_add.php,员工_登录信息添加,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_user_del.php,员工_登录信息删除,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_user_edit.php,员工_登录信息编辑,';
$GLOBALS['baseConfigFunArray']['emp']['emp.php'][]='emp_user_role_view.php,员工_权限信息查看,';

$GLOBALS['baseConfigFunArray']['emp'][]='dep.php,部门_管理,emp_dep,dep_id,dep_topid,,,';
$GLOBALS['baseConfigFunArray']['emp']['dep.php'][]='dep_add.php,部门_添加,';
$GLOBALS['baseConfigFunArray']['emp']['dep.php'][]='dep_del.php,部门_删除,';
$GLOBALS['baseConfigFunArray']['emp']['dep.php'][]='dep_edit.php,部门_编辑,';
$GLOBALS['baseConfigFunArray']['emp']['dep.php'][]='dep_move.php,部门_移动,';






$GLOBALS['baseConfigFunArray']['sys'][]='系统功能';
$GLOBALS['baseConfigFunArray']['sys'][]='channel.php,模型_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['channel.php'][]='channel_add.php,模型_添加,';
$GLOBALS['baseConfigFunArray']['sys']['channel.php'][]='channel_del.php,模型_删除,';
$GLOBALS['baseConfigFunArray']['sys']['channel.php'][]='channel_edit.php,模型_编辑,';

$GLOBALS['baseConfigFunArray']['sys'][]='field.php,模型字段_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['field.php'][]='field_add.php,模型字段_添加,';
$GLOBALS['baseConfigFunArray']['sys']['field.php'][]='field_del.php,模型字段_删除,';
$GLOBALS['baseConfigFunArray']['sys']['field.php'][]='field_edit.php,模型字段_编辑,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysGroup.php,用户权限组_管理,sys_admintype,rank,depid,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysGroup.php'][]='sysGroup_add.php,用户权限组_添加,';
$GLOBALS['baseConfigFunArray']['sys']['sysGroup.php'][]='sysGroup_del.php,用户权限组_删除,';
$GLOBALS['baseConfigFunArray']['sys']['sysGroup.php'][]='sysGroup_edit.php,用户权限组_权限设定,';
$GLOBALS['baseConfigFunArray']['sys']['sysGroup.php'][]='sysGroup_view.php,用户组_查看,';

$GLOBALS['baseConfigFunArray']['sys'][]='log.php,系统日志_管理,sys_log,lid,,adminid,,';
$GLOBALS['baseConfigFunArray']['sys']['log.php'][]='log_del.php,系统日志_删除,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysCacheUp.php,缓存清空,,,,,,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysData.php,数据库备份,,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysData.php'][]='sysData_revert.php,数据库还原,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysFunction.php,系统菜单_管理,sys_function,id,depid,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysFunction.php'][]='sysFunction_add.php,系统菜单_添加,';
$GLOBALS['baseConfigFunArray']['sys']['sysFunction.php'][]='sysFunction_edit.php,系统菜单_修改,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysInfo.php,系统其他参数配置,sys_sysOtherConfig,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysInfo.php'][]='sysInfo_add.php,系统其他参数配置_添加,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysStepSelect.php,数据字典,sys_stepselect,id,depid,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysStepSelect.php'][]='sysStepSelect_del.php,数据字典_删除,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysFeedback.php,用户建议管理,sys_feedback,id,,userid,,';
$GLOBALS['baseConfigFunArray']['sys']['sysFeedback.php'][]='sysFeedback_add.php,用户建议_添加,';
$GLOBALS['baseConfigFunArray']['sys']['sysFeedback.php'][]='sysFeedback_del.php,用户建议_删除,';
$GLOBALS['baseConfigFunArray']['sys']['sysFeedback.php'][]='sysFeedback_reply.php,用户建议_反馈,';

$GLOBALS['baseConfigFunArray']['sys'][]='weixin.php,微信参数配置,interface_weixin,id,depid,,,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_Msg_Template.php,微信参数配置_模板,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_Msg_Template_add.php,微信参数配置_模板添加,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_Msg_Template_del.php,微信参数配置_模板删除,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_Msg_Template_edit.php,微信参数配置_模板编辑,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_Msg_Template_test.php,微信_模板消息测试,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_add.php,微信参数配置_添加,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_autoreturn.php,微信参数配置_自动回复,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_del.php,微信参数配置_删除,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_edit.php,微信参数配置_编辑,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_menuEdit.php,微信参数配置_菜单编辑,';
$GLOBALS['baseConfigFunArray']['sys']['weixin.php'][]='weixin_menuUpload.php,微信参数配置_菜单微信同步,';

$GLOBALS['baseConfigFunArray']['sys'][]='phoneMsg.php,短信参数配置,interface_phoneMsg,id,depid,,,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_Template.php,短信参数配置_模板,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_Template_add.php,短信参数配置_模板添加,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_Template_del.php,短信参数配置_模板删除,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_Template_edit.php,短信参数配置_模板编辑,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_Template_test.php,短信_测试,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_add.php,短信参数配置_添加,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_del.php,短信参数配置_删除,';
$GLOBALS['baseConfigFunArray']['sys']['phoneMsg.php'][]='phoneMsg_edit.php,短信参数配置_编辑,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysFileBaseConfig.php,系统功能文件基础配置,,,,,,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysBaseConfigInfo.php,系统基本参数配置,,,,,,';

$GLOBALS['baseConfigFunArray']['sys'][]='phoneMsgLog.php,短信发送记录,interface_phonemsg_log,id,clientid(client|id|depids),,,';

$GLOBALS['baseConfigFunArray']['sys'][]='sysgoods.php,系统商品_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['sysgoods.php'][]='sysgoods_add.php,系统商品_添加,';
$GLOBALS['baseConfigFunArray']['sys']['sysgoods.php'][]='sysgoods_del.php,系统商品_删除,';
$GLOBALS['baseConfigFunArray']['sys']['sysgoods.php'][]='sysgoods_edit.php,系统商品_编辑,';

$GLOBALS['baseConfigFunArray']['sys'][]='goodsOrder.php,系统商品订单_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['sys']['goodsOrder.php'][]='goodsOrder_add.php,系统商品订单_添加,';
$GLOBALS['baseConfigFunArray']['sys']['goodsOrder.php'][]='goodsOrder_del.php,系统商品订单_删除,';
$GLOBALS['baseConfigFunArray']['sys']['goodsOrder.php'][]='goodsOrder_edit.php,系统商品订单_编辑,';

$GLOBALS['baseConfigFunArray']['sys'][]='goodsOrderDetail.php,系统商品订单_明细,,,,,,';

$GLOBALS['baseConfigFunArray']['sys'][]='weixinMsgLog.php,微信模板消息发送记录,,,,,,';






$GLOBALS['baseConfigFunArray']['goods'][]='商品管理';
$GLOBALS['baseConfigFunArray']['goods'][]='catalog.php,分类_管理,goods_type,id,,,,';
$GLOBALS['baseConfigFunArray']['goods']['catalog.php'][]='catalog_add.php,分类_添加,';
$GLOBALS['baseConfigFunArray']['goods']['catalog.php'][]='catalog_del.php,分类_删除,';
$GLOBALS['baseConfigFunArray']['goods']['catalog.php'][]='catalog_edit.php,分类_编辑,';

$GLOBALS['baseConfigFunArray']['goods'][]='goods.php,商品_管理,goods,id,,,typeid,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_add.php,商品_添加,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_benefit.php,商品_优惠管理,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_benefitadd.php,商品_优惠添加,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_benefitdel.php,商品_优惠删除,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_del.php,商品_删除,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_edit.php,商品_编辑,';
$GLOBALS['baseConfigFunArray']['goods']['goods.php'][]='goods_tt.php,商品_头条,';

$GLOBALS['baseConfigFunArray']['goods'][]='goodsrecycling.php,商品回收站,goods,id,,,,';

$GLOBALS['baseConfigFunArray']['goods'][]='benefit.php,商品优惠_管理,goods_benefit,id,,,,';

$GLOBALS['baseConfigFunArray']['goods'][]='line.php,线路团期_管理,line,id,,,,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_add.php,线路团期_添加,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_beforHors.php,线路团期_截止时间调整,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_copy.php,线路团期_复制,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_del.php,线路团期_删除,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_edit.php,线路团期_编辑,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_seatsedit.php,线路团期_座位数量调整,';
$GLOBALS['baseConfigFunArray']['goods']['line.php'][]='line_stop.php,线路团期_停用,';

$GLOBALS['baseConfigFunArray']['goods'][]='lyht.php,旅游合同_管理,lyht,id,,,,';
$GLOBALS['baseConfigFunArray']['goods']['lyht.php'][]='lyht_add.php,旅游合同_添加,';
$GLOBALS['baseConfigFunArray']['goods']['lyht.php'][]='lyht_del.php,旅游合同_删除,';
$GLOBALS['baseConfigFunArray']['goods']['lyht.php'][]='lyht_edit.php,旅游合同_编辑,';

$GLOBALS['baseConfigFunArray']['goods'][]='couponConfigInfo.php,优惠券规则,,,,,,';






$GLOBALS['baseConfigFunArray']['client'][]='用户管理';
$GLOBALS['baseConfigFunArray']['client'][]='client.php,用户管理,client_depinfos,clientid,depid,,,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_WeixinClear.php,用户_微信解绑,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_add.php,用户_添加,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_del.php,用户_禁用,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_edit.php,用户_编辑,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_updateWeixin.php,用户_微信绑定,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_user_add.php,用户_登录添加,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_user_edit.php,用户_登录修改,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_view_jb.php,用户_金币详情,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_view_jf.php,用户_积分详情,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_view_user.php,用户_登录信息,';
$GLOBALS['baseConfigFunArray']['client']['client.php'][]='client_view_weixin.php,用户_微信详情,';

$GLOBALS['baseConfigFunArray']['client'][]='feedback.php,用户建议,,,,,,';
$GLOBALS['baseConfigFunArray']['client']['feedback.php'][]='feedback_del.php,用户建议_删除,';
$GLOBALS['baseConfigFunArray']['client']['feedback.php'][]='feedback_reply.php,用户建议_反馈,';

$GLOBALS['baseConfigFunArray']['client'][]='recycling.php,禁用用户_列表,client_depinfos,clientid,depid,,,';
$GLOBALS['baseConfigFunArray']['client']['recycling.php'][]='recycling_del.php,禁用用户_彻底删除,';
$GLOBALS['baseConfigFunArray']['client']['recycling.php'][]='recycling_rest.php,禁用用户_恢复,';

$GLOBALS['baseConfigFunArray']['client'][]='clientQrCode.php,用户推广二维码,,,,,,';

$GLOBALS['baseConfigFunArray']['client'][]='tgdesc.php,会员推广排行,,,,,,';

$GLOBALS['baseConfigFunArray']['client'][]='tgConfigInfo.php,推广界面信息配置,,,,,,';






$GLOBALS['baseConfigFunArray']['clientdata'][]='用户数据功能';
$GLOBALS['baseConfigFunArray']['clientdata'][]='extraction.php,提现管理,clientdata_extractionlog,id,,,,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_add.php,提现管理_添加,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_config.php,提现管理_规则配置,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_del.php,提现管理_删除,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_no.php,提现管理_审核不通过,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_ok.php,提现管理_审核通过,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_weixin.php,提现管理_微信付款,';
$GLOBALS['baseConfigFunArray']['clientdata']['extraction.php'][]='extraction_xianjin.php,提现管理_现金线下付款,';

$GLOBALS['baseConfigFunArray']['clientdata'][]='jb.php,会员金币明细,clientdata_jblog,id,clientid(client_depinfos|clientid|depid),operatorid,,';
$GLOBALS['baseConfigFunArray']['clientdata']['jb.php'][]='jb_add.php,会员金币明细_添加,';
$GLOBALS['baseConfigFunArray']['clientdata']['jb.php'][]='jb_rest.php,会员金币明细_撤消,';
$GLOBALS['baseConfigFunArray']['clientdata']['jb.php'][]='jb_sub.php,会员金币明细_扣除,';

$GLOBALS['baseConfigFunArray']['clientdata'][]='jf.php,会员积分明细,clientdata_jflog,id,clientid(client_depinfos|clientid|depid),operatorid,,';
$GLOBALS['baseConfigFunArray']['clientdata']['jf.php'][]='jf_add.php,会员积分明细_添加,';
$GLOBALS['baseConfigFunArray']['clientdata']['jf.php'][]='jf_rest.php,会员积分明细_撤消,';

$GLOBALS['baseConfigFunArray']['clientdata'][]='clientScoresLog.php,会员成长值明细,,,,,,';

$GLOBALS['baseConfigFunArray']['clientdata'][]='jbadd.php,会员金币充值卡充值,clientdata_jblog,id,clientid(client_depinfos|clientid|depid),operatorid,,';

$GLOBALS['baseConfigFunArray']['clientdata'][]='coupon.php,优惠券,clientdata_coupon,id,clientid(client_depinfos|clientid|depid),operatorid,,';






$GLOBALS['baseConfigFunArray']['order'][]='订单管理';
$GLOBALS['baseConfigFunArray']['order'][]='orderZtc.php,会员卡订单_管理,order,id,clientid(client_depinfos|clientid|depid),,,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_add.php,会员卡订单_添加,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_baofei.php,会员卡订单_报废,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_buka.php,会员卡订单_补办,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_cancel.php,会员卡订单_删除,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_cardcode.php,会员卡订单_实体卡绑定,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_guashi.php,会员卡订单_挂失,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_idpicSH.php,会员卡订单_照片审核,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_return.php,会员卡订单_退款,';
$GLOBALS['baseConfigFunArray']['order']['orderZtc.php'][]='orderZtc_returnBF.php,会员卡订单_部分退款,';

$GLOBALS['baseConfigFunArray']['order'][]='orderLine.php,旅游线路订单_管理,order,id,clientid(client_depinfos|clientid|depid),operatorid,,';
$GLOBALS['baseConfigFunArray']['order']['orderLine.php'][]='orderLine_add.php,旅游线路订单_添加,';
$GLOBALS['baseConfigFunArray']['order']['orderLine.php'][]='orderLine_cancel.php,旅游线路订单_删除,';
$GLOBALS['baseConfigFunArray']['order']['orderLine.php'][]='orderLine_return.php,旅游线路订单_退款,';

$GLOBALS['baseConfigFunArray']['order'][]='orderZtcCardCodeQr.php,实体卡乘车检票二维码,,,,,,';

$GLOBALS['baseConfigFunArray']['order'][]='orderHyk.php,合伙人订单_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['order']['orderHyk.php'][]='orderHyk_add.php,合伙人订单_添加,';
$GLOBALS['baseConfigFunArray']['order']['orderHyk.php'][]='orderHyk_cancel.php,合伙人订单_删除,';
$GLOBALS['baseConfigFunArray']['order']['orderHyk.php'][]='orderHyk_return.php,合伙人订单_退款,';

$GLOBALS['baseConfigFunArray']['order'][]='orderCancel.php,订单取消记录,,,,,,';

$GLOBALS['baseConfigFunArray']['order'][]='orderCar.php,车辆租赁订单_管理,order,id,clientid(client_depinfos|clientid|depid),,,';
$GLOBALS['baseConfigFunArray']['order']['orderCar.php'][]='orderCar_add.php,车辆租赁订单_添加,';
$GLOBALS['baseConfigFunArray']['order']['orderCar.php'][]='orderCar_cancel.php,车辆租赁订单_删除,';
$GLOBALS['baseConfigFunArray']['order']['orderCar.php'][]='orderCar_return.php,车辆租赁订单_退款,';
$GLOBALS['baseConfigFunArray']['order']['orderCar.php'][]='orderCar_returnBF.php,车辆租赁订单_部分退款,';

$GLOBALS['baseConfigFunArray']['order'][]='orderCzk.php,充值卡订单_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['order']['orderCzk.php'][]='orderCzk_add.php,充值卡订单_添加,';
$GLOBALS['baseConfigFunArray']['order']['orderCzk.php'][]='orderCzk_cancel.php,充值卡订单_删除,';
$GLOBALS['baseConfigFunArray']['order']['orderCzk.php'][]='orderCzk_return.php,充值卡订单_退款,';

$GLOBALS['baseConfigFunArray']['order'][]='order.php,订单明细,,,,,,';
$GLOBALS['baseConfigFunArray']['order']['order.php'][]='order_cancel.php,订单明细_删除,';
$GLOBALS['baseConfigFunArray']['order']['order.php'][]='order_return.php,订单明细_退款,';






$GLOBALS['baseConfigFunArray']['service'][]='业务管理';
$GLOBALS['baseConfigFunArray']['service'][]='appt.php,旅游线路预约记录明细,order_addon_lycp,id,,,,';

$GLOBALS['baseConfigFunArray']['service'][]='apptQuery.php,旅游线路预约_管理,order_addon_lycp,id,,,,';
$GLOBALS['baseConfigFunArray']['service']['apptQuery.php'][]='apptQuery_info.php,旅游线路预约_信息确认,';
$GLOBALS['baseConfigFunArray']['service']['apptQuery.php'][]='apptQuery_list.php,旅游线路预约_按日记录,';

$GLOBALS['baseConfigFunArray']['service'][]='ztcList.php,直通车卡_明细,,,,,,';

$GLOBALS['baseConfigFunArray']['service'][]='lease.php,车辆租赁预约_管理,order_addon_car,id,,,,';
$GLOBALS['baseConfigFunArray']['service']['lease.php'][]='lease_get.php,车辆租赁预约_取车,';
$GLOBALS['baseConfigFunArray']['service']['lease.php'][]='lease_return.php,车辆租赁预约_还车,';

$GLOBALS['baseConfigFunArray']['service'][]='leaseQuery.php,车辆租赁预约_查询,order_addon_car,id,,,,';

$GLOBALS['baseConfigFunArray']['service'][]='apptTempMoney.php,临时乘车明细,,,,,,';
$GLOBALS['baseConfigFunArray']['service']['apptTempMoney.php'][]='apptTempMoney_del.php,临时乘车明细_删除,';
$GLOBALS['baseConfigFunArray']['service']['apptTempMoney.php'][]='apptTempMoney_edit.php,临时乘车明细_编辑,';

$GLOBALS['baseConfigFunArray']['service'][]='ztcJihuoList.php,乘车卡_激活明细,ztc_jihuo,orderListId,dep_id,,,';

$GLOBALS['baseConfigFunArray']['service'][]='ztcDaoqiList.php,乘车卡_到期明细,,,,,,';






$GLOBALS['baseConfigFunArray']['device'][]='设备管理';
$GLOBALS['baseConfigFunArray']['device'][]='catalog.php,分类_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['device']['catalog.php'][]='catalog_add.php,分类_添加,';
$GLOBALS['baseConfigFunArray']['device']['catalog.php'][]='catalog_del.php,分类_删除,';
$GLOBALS['baseConfigFunArray']['device']['catalog.php'][]='catalog_edit.php,分类_编辑,';

$GLOBALS['baseConfigFunArray']['device'][]='device.php,设备_管理,,,,,,';
$GLOBALS['baseConfigFunArray']['device']['device.php'][]='device_add.php,设备_添加,';
$GLOBALS['baseConfigFunArray']['device']['device.php'][]='device_del.php,设备_删除,';
$GLOBALS['baseConfigFunArray']['device']['device.php'][]='device_edit.php,设备_编辑,';

$GLOBALS['baseConfigFunArray']['device'][]='devicerecycling.php,设备_回收站,,,,,,';

$GLOBALS['baseConfigFunArray']['device'][]='deviceQrCode.php,设备_二维码,,,,,,';

$GLOBALS['baseConfigFunArray']['device'][]='deviceState.php,车辆状态查询,,,,,,';

$GLOBALS['baseConfigFunArray']['device'][]='deviceUseLog.php,车辆使用记录明细,,,,,,';

$GLOBALS['baseConfigFunArray']['device'][]='deviceUseForGuide.php,乘务安排,,,,,,';






$GLOBALS['baseConfigFunArray']['count'][]='统计';
$GLOBALS['baseConfigFunArray']['count'][]='jbCountByMonth.php,金币按月汇总,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='jbCountTypeByMonth.php,金币类型按月汇总,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='jbCountByDay.php,金币按天汇总,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='jbCountTypeByDay.php,金币类型按天汇总,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='wxxjCountByMonth.php,支付到微信按月统计,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='xsxjCountByMonth.php,现金支付的定单按月统计,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='reportByDay.php,结算报表,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BclientByDay.php,用户分析,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BorderByDay.php,消费分析,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BtgClientByMonth.php,会员推广分析月报表,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BtgDepByMonth.php,售卡点销售分析月报表,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BjihuoDepByMonth.php,售卡点激活分析月报表,,,,,,';

$GLOBALS['baseConfigFunArray']['count'][]='BjihuoQuyuByMonth.php,区域激活分析月报表,,,,,,';






