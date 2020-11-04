<?php


//系统内置的 模板的参数
$smsParam = Array();
$smsParam["服务购买成功通知"] = "{{first.DATA}}
                                订单号：{{keyword1.DATA}}
                                金额：{{keyword2.DATA}}
                                商品名称：{{keyword3.DATA}}
                                购买日期：{{keyword4.DATA}}
                                {{remark.DATA}} ";
