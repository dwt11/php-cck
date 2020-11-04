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
        url: "appt.list.sendweixin.php",
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


//选择车辆
function carInfo_select(car_group_i) {
    var appttime = $('#appttime').val();
    if (appttime == "") {
        layer.alert('未获得线路时间,请重新打开此页面', {icon: 6});
        return;
    }
    layer.open({
        type: 2,
        title: '选择车辆',
        content: '../device/device.select.php?appttime=' + appttime + '&targetname=deviceid_' + car_group_i
    });
}
//选择司机
function driverid_select(car_group_i) {
    layer.open({
        type: 2,
        title: '选择司机',
        content: '../emp/emp.select.radio.php?targetname=driverid_' + car_group_i+'&emp_dep=30'
    });
}
//选择乘务
function guideid_select(car_group_i) {
    layer.open({
        type: 2,
        title: '选择乘务',
        content: '../emp/emp.select.radio.php?targetname=guideid_' + car_group_i+'&emp_dep=30'
    });
}

//清空车辆
function carInfo_clear(car_group_i) {
    $('#deviceid_' + car_group_i).val("")
}


//保存车辆信息
function carInfo_save(car_group_i, gobackurl) {

    var nid = getCheckboxItem('orderaddonids_' + car_group_i);
    var lineid = $('#lineid').val();
    if (lineid == "") {
        layer.alert('未获得线路数据,请重新打开此页面', {icon: 6});
        return;
    }
    var appttime = $('#appttime').val();
    if (appttime == "") {
        layer.alert('未获得线路时间,请重新打开此页面', {icon: 6});
        return;
    }
    var deviceid = $('#deviceid_' + car_group_i).val();
    //if (deviceid == "") {
    //    layer.alert('请选择车辆', {icon: 6});
    //    return;
    //}
    if (nid == "") {
        layer.alert('请选择要操作的数据', {icon: 6});
        return;
    }
    var driverid = $('#driverid_' + car_group_i).val();
    if (!driverid) driverid = "";
    var guideid = $('#guideid_' + car_group_i).val();
    if (!guideid) guideid = "";
    //$("#carInfo_save_" + car_group_i).hide();

    $.ajax({
        type: "post",
        url: "old.apptQuery.list.php",
        data: {
            deviceid: deviceid,
            ids: nid,
            lineid: lineid,
            appttime: appttime,
            driverid: driverid,
            guideid: guideid,
            dopost: "save"
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


$(function () {
    var vars_value = {};
    intervalName = setInterval(handle, 1000);//定时器句柄
    function handle() {
        // IE浏览器此处判断没什么意义，但为了统一，且提取公共代码而这样处理。
        //如果值不一样,则代表了改变

        //车辆值改变
        $("input[name^='deviceid_']").each(
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

                    $("#carInfo_save_" + car_group_i).show();
                    $.ajax({
                        type: "get",
                        url: "/device/device.do.php",
                        data: {
                            deviceid: nowvalue,
                            dopost: "GetOneDeviceInfo"
                        },
                        dataType: 'json',
                        success: function (result) {
                            $("#devicename_" + car_group_i).html(result.devicename);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#devicename_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
                        }
                    });
                }
            }
        )


        //司机值改变
        $("input[name^='driverid_']").each(
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

                    $("#carInfo_save_" + car_group_i).show();
                    $.ajax({
                        type: "get",
                        url: "/emp/emp.inc.do.php",
                        data: {
                            emp_id: nowvalue,
                            dopost: "GetOneEmpInfo"
                        },
                        dataType: 'json',
                        success: function (result) {
                            $("#drivername_" + car_group_i).html(result.emp_realname + " " + result.emp_mobilephone);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $("#drivername_" + car_group_i).html("");//如果清空了车辆或出错,则把车牌号清空
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

                    $("#carInfo_save_" + car_group_i).show();
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


    }
});



