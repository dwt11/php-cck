/**
 * Created by Administrator on 2017-02-21.
 */

/*  window.onbeforeunload = function(event) {
 return "确定离开页面吗？";
 }*/
$(document).ready(function () {
    $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    //是否全选
    $("input[name^='selAllBut']").on('ifChecked', function (event) {
        var e_name = (event.target.name);
        var e_name_array = e_name.split("_");
        var car_group_i = e_name_array[1];
        console.log(car_group_i);

        var vvname = "orderaddonids_" + car_group_i;
        $("input[name='" + vvname + "']").iCheck('check');
    });
    $("input[name^='selAllBut']").on('ifUnchecked', function (event) {
        var e_name = (event.target.name);
        var e_name_array = e_name.split("_");
        var car_group_i = e_name_array[1];
        console.log(car_group_i);

        var vvname = "orderaddonids_" + car_group_i;
        $("input[name='" + vvname + "']").iCheck('uncheck');
    });
});


function del(orderAddonId, gobackurl) {
    layer.confirm('您确定要删除此人吗？', {icon: 3, title: '提示'}, function (index) {
        $.ajax({
            type: "post",
            url: 'apptQuery.list.del.php',
            data: {
                id: orderAddonId
            },
            dataType: 'html',
            success: function (result) {
                layer.closeAll('loading'); //关闭加载层
                console.log(result);
                layer.msg(result, {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = gobackurl;
                });
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.closeAll('loading'); //关闭加载层
                console.log(XMLHttpRequest);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    });

}

//批量确认
function allInfo(car_group_i) {
    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    if (nid == "") {
        layer.alert('请选择要确认的数据', {icon: 6});
        return;
    }
    layer.open({type: 2, title: '确认信息', content: 'apptQuery_info.php?ids=' + nid});
}
//紧急变更通知
function send_alert_info(car_group_i) {
    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    if (nid == "") {
        layer.alert('请选择要发送的人', {icon: 6});
        return;
    }
    layer.open({type: 2, title: '紧急变更通知', content: 'apptQuery.list.alert.sendweixin.php?ids=' + nid});
}

//发送微信信息
function sendWeixinInfo(car_group_i) {
    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    if (nid == "") {
        layer.alert('请选择要发送的人', {icon: 6});
        return;
    }

//进度条
    var index = layer.load(2, {
        shade: [0.1, '#fff'] //0.1透明度的白色背景
    });
    $.ajax({
        type: "post",
        url: "apptQuery.list.sendweixin.php",
        data: {
            ids: nid
        },
        dataType: 'html',
        success: function (result) {
            layer.closeAll('loading'); //关闭加载层
            console.log(result);
            layer.msg(result, {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                window.location.reload();
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.closeAll('loading'); //关闭加载层
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}


//选择车辆商品
function goodscarInfo_select(car_group_i) {
    /* var appttime = $('#appttime').val();
     if (appttime == "") {
     layer.alert('未获得线路时间,请重新打开此页面', {icon: 6});
     return;
     }*/
    layer.open({
        type: 2,
        title: '选择车型',
        content: '../goods/goods.select.php?typeid=5&targetname=goodsid_' + car_group_i
    });
}


//清空车辆
function carInfo_clear(car_group_i, gobackurl) {
    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    var lineid = $('#lineid').val();
    if (nid == "") {
        layer.alert('请选择要操作的数据', {icon: 6});
        return;
    }
    $.ajax({
        type: "post",
        url: "apptQuery_list.php",
        data: {
            lineid: lineid,
            ids: nid,
            dopost: "clear"
        },
        dataType: 'html',
        success: function (data) {
            layer.closeAll('loading'); //关闭加载层

            layer.msg("操作成功", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                window.location.href = gobackurl;
            });

        }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.closeAll('loading'); //关闭加载层
            layer.msg("清空失败 请核对 ", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                //这里要考虑一下,是否自动刷新 当前页面
            });
        }
    });

}

//保存车辆商品，生成订单
function ordercarInfo_save_(car_group_i, gobackurl) {

    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    var lineid = $('#lineid').val();
    if(!lineid)lineid="";
    if (lineid == "") {
        layer.alert('未获得线路数据,请重新打开此页面', {icon: 6});
        return;
    }
    var appttime = $('#appttime').val();
    if (appttime == "") {
        layer.alert('未获得线路时间,请重新打开此页面', {icon: 6});
        return;
    }
    var goodsid = $('#goodsid_' + car_group_i).val();
    if(!goodsid)goodsid="";
    if (goodsid == "") {
        layer.alert('请选择车型', {icon: 6});
        return;
    }
    var orderCarId = $('#orderCarId_' + car_group_i).val();
    if (!orderCarId) orderCarId = "";
    //if (deviceid == "") {
    //    layer.alert('请选择车辆', {icon: 6});
    //    return;
    //}
    if (nid == "") {
        layer.alert('请选择要操作的数据', {icon: 6});
        return;
    }

    $("#ordercarInfo_save_" + car_group_i).hide();

    $.ajax({
        type: "post",
        url: "apptQuery_list.php",
        data: {
/*
            orderCarId: orderCarId,
*/
            goodsid: goodsid,
            ids: nid,
            lineid: lineid,
            appttime: appttime,
            dopost: "save"
        },
        dataType: 'json',
        success: function (data) {
            layer.closeAll('loading'); //关闭加载层
            if (data.info == "添加成功") {
                layer.msg("操作成功,请等待车辆安排", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = gobackurl;
                });
            } else {
                layer.msg("创建失败 原因:" + data.info, {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    //这里出错后,不要跳转,还在当前页面 让用户选择操作if (!isdebug) window.location.href = backUrl;
                });

            }
        }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.closeAll('loading'); //关闭加载层
            layer.msg("未正常创建 请核对 ", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                //这里要考虑一下,是否自动刷新 当前页面
            });
        }
    });
}

//选择乘务
function guideid_select(car_group_i) {
    layer.open({
        type: 2,
        title: '选择乘务',
        content: '../emp/emp.select.radio.php?targetname=guideid_' + car_group_i+'&no_emp_dep=19'
    });
}
//保存乘务信息
function guideInfo_save(car_group_i, gobackurl) {

    var device_automobile_uselog_id = $('#device_automobile_uselog_id_'+ car_group_i).val();
    if (!device_automobile_uselog_id ) {
        layer.alert('未获得数据,请重新打开此页面', {icon: 6});
        return;
    }
    var guideid = $('#guideid_' + car_group_i).val();
    if (!guideid) guideid = "";
    //$("#carInfo_save_" + car_group_i).hide();

    $.ajax({
        type: "post",
        url: "apptQuery_list.php",
        data: {
            device_automobile_uselog_id: device_automobile_uselog_id,
            guideid: guideid,
            dopost: "guideInfo_save"
        },
        dataType: 'html',
        success: function (result) {
            console.log(result);
            layer.msg(result, {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                window.location.href = gobackurl;
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);

        }
    });
}


//调整到别的车辆
function carOrder_select(car_group_i) {
    var lineid = $('#lineid').val();
    var CarOrderid_old = $('#CarOrderid_'+car_group_i).val();
    if(!CarOrderid_old)CarOrderid_old="";
    if (lineid == "") {
        layer.alert('未获得线路数据,请重新打开此页面', {icon: 6});
        return;
    }
    var appttime = $('#appttime').val();
    if (appttime == "") {
        layer.alert('未获得线路时间,请重新打开此页面', {icon: 6});
        return;
    }
    layer.open({
        type: 2,
        title: '选择',
        content: 'apptQuery.carOrder.select.php?targetname=CarOrderid_' + car_group_i+'&lineid='+lineid+"&appttime="+appttime+"&CarOrderid_old="+CarOrderid_old
    });
}
//保存 调整
function carOrder_select_save(car_group_i, gobackurl) {

    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    var lineid = $('#lineid').val();
    if (nid == "") {
        layer.alert('请选择要操作的数据', {icon: 6});
        return;
    }
    var CarOrderid = $('#CarOrderid_'+car_group_i).val();
    if(!CarOrderid)CarOrderid="";
    if (CarOrderid == ""||CarOrderid==0) {
        layer.alert('车辆订单信息获取出错', {icon: 6});
        return;
    }
    $.ajax({
        type: "post",
        url: "apptQuery_list.php",
        data: {
            lineid: lineid,
            ids: nid,
            CarOrderid: CarOrderid,
            dopost: "carOrder_select_save"
        },
        dataType: 'html',
        success: function (data) {
            layer.closeAll('loading'); //关闭加载层

            layer.msg("操作成功", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                 window.location.href = gobackurl;
            });

        }, error: function (XMLHttpRequest, textStatus, errorThrown) {
            layer.closeAll('loading'); //关闭加载层
            layer.msg("清空失败 请核对 ", {
                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                time: 2000 //2秒关闭（如果不配置，默认是3秒）
            }, function () {
                //这里出错后,不要跳转,if (!isdebug)window.location.href = backUrl;
                //这里要考虑一下,是否自动刷新 当前页面
            });
        }
    });
}





$(function () {
    var vars_value = {};
    //$("input[name^='ordercarInfo_save_']").hide();
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        // IE浏览器此处判断没什么意义，但为了统一，且提取公共代码而这样处理。
        //如果值不一样,则代表了改变

        //车型值改变
        $("input[name^='goodsid_']").each(
            function () {
                var e_name = $(this).attr("id");//表单名称
                var tmpvalue = vars_value[e_name];//临时的值
                if (!tmpvalue) tmpvalue = "";
                //console.log("tmpvalue " + tmpvalue);

                var nowvalue = $(this).val();
                if (nowvalue != tmpvalue) {
                    vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                    //console.log(vars_value.e_name);
                    var e_name_array = e_name.split("_");
                    var car_group_i = e_name_array[1];

                    $("#ordercarInfo_save_" + car_group_i).show();
                    $.ajax({
                        type: "get",

                        url: "../goods/goods.do.php",
                        data: {
                            goodsid: nowvalue,
                            dopost: "GetOneGoodsInfo"
                        },
                        dataType: 'json',
                        success: function (result) {
                            $("#goodsname_" + car_group_i).html(result.goodsname);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#goodsname_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                        }
                    });
                }
            }
        )


        //乘务值改变
        $("input[name^='guideid_']").each(
            function () {
                var e_name = $(this).attr("id");//表单名称
                var tmpvalue = vars_value[e_name];//临时的值
                if (!tmpvalue) tmpvalue = "";
                //console.log("tmpvalue " + tmpvalue);
                var nowvalue = $(this).val();
                if (nowvalue != tmpvalue) {
                    vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                    //console.log(vars_value.e_name);
                    var e_name_array = e_name.split("_");
                    var car_group_i = e_name_array[1];

                   // $("#carInfo_save_" + car_group_i).show();
                    $.ajax({
                        type: "get",
                        url: "/emp/emp.inc.do.php",
                        data: {
                            emp_id: nowvalue,
                            dopost: "GetOneEmpInfo"
                        },
                        dataType: 'json',
                        success: function (result) {
                            $("#guidename_" + car_group_i).html(result.emp_realname + " " + result.emp_mobilephone);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#guidename_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                        }
                    });
                }
            }
        )


        //调整车辆时的 订单号改变
        $("input[name^='CarOrderid_']").each(
            function () {
                var e_name = $(this).attr("id");//表单名称
                var tmpvalue = vars_value[e_name];//临时的值
                if (!tmpvalue) tmpvalue = "";
                //console.log("tmpvalue " + tmpvalue);
                var nowvalue = $(this).val();
                if (nowvalue != tmpvalue) {
                    vars_value[e_name] = nowvalue;//利用表单名称 保存当前的设备ID
                    //console.log(vars_value.e_name);
                    var e_name_array = e_name.split("_");
                    var car_group_i = e_name_array[1];
                    $.ajax({
                        type: "get",
                        url: "apptQuery_list.php",
                        data: {
                            orderCarid: nowvalue,
                            dopost: "getOrderCode"
                        },
                        dataType: 'html',
                        success: function (result) {
                            $("#ordercode_" + car_group_i).html(result);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#ordercode_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                        }
                    });
                }
            }
        )


    }
});



