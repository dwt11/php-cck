!function (F, E, D) {
    !function () {
        D("#datalist").bootstrapTable({
            showExport: true,//显示导出按钮
            exportDataType: "basic",//导出类型
            striped: !0,
            showToggle: 0,
            showColumns: 0,
            iconSize: "outline",
            toolbar: "#Toolbar,#Toolbar2,#Toolbar3",
            sortable: true,                     //是否启用排序
            sortOrder: "asc",                  //排序方式
            icons: {refresh: "glyphicon-repeat", toggle: "glyphicon-list-alt", columns: "glyphicon-list"}
        });
        /* setTimeout(function () {
             D("#datalist").bootstrapTable('resetView');
         }, 200);*/
    }()
}(document, window, jQuery);
/*先加栽表格,页面加载完成后,刷新高度*/
$(document).ready(function () {
    $("#datalist").bootstrapTable('resetView', {
        height: getHeight()
    });
});
/*当屏幕变化时 自适应高度*/
$(window).resize(function () {
    $("#datalist").bootstrapTable('resetView', {
        height: getHeight()
    });
});


/*170305用于表格高度自适应 屏幕高度-标题高度-分页高度*/
function getHeight() {
    var windows_h = $(window).height();//页面显示屏幕高
    var title_h = $('.ibox-title').outerHeight(true);//页面头高
    var footer_h = $('.pagination-detail').outerHeight(true);//页面脚高,不要外层盒 的


    // console.clear();
    var bootstrap_table = $('.table-responsive').outerHeight(true);//工具框高
    /*    var fixed_table_toolbar = $('#Toolbar').outerHeight(true);//工具框高
        var fixed_table_toolbar2 = $('#Toolbar2').outerHeight(true);//工具框高
        var fixed_table_toolbar3 = $('#Toolbar3').outerHeight(true);//工具框高
        var toolbar = 0;
        if (fixed_table_toolbar > 0) toolbar = fixed_table_toolbar;
        if (fixed_table_toolbar2 > 0) toolbar = fixed_table_toolbar2;
        if (fixed_table_toolbar3 > 0) toolbar = fixed_table_toolbar3;
        console.log("toolbar" + toolbar);*/


    var ibox_content = $('.ibox-content').outerHeight(true);//工具框高
    /* var xxxx = ibox_content - bootstrap_table - toolbar;
     if (!(xxxx > 0)) */
    var xxxx = ibox_content - bootstrap_table;//如果页面加载后变动大小 bootstrap_table会包含toolbar的高,不用再减toolbar了 (ibox_content和bootstrap_table页面加栽时,是全页面;加载后变化 的话,是实际显示的大小)
    //xxxx=xxxx*1.4;
    console.log("ibox_content" + ibox_content);
    console.log("bootstrap_table" + bootstrap_table);
    console.log("xxxx" + xxxx);
    console.log("页面" + windows_h);
    console.log("u头" + title_h);
    /* console.log("fixed_table_toolbar"+fixed_table_toolbar);
     console.log("fixed_table_toolbar2"+fixed_table_toolbar2);
     console.log("fixed_table_toolbar3"+fixed_table_toolbar3);
    */

    console.log("footer_h" + footer_h);


    //表格的显示高度(滚动调度)=屏幕页面高-title标题高-表格工具栏高-翻页框的高度
    var height = windows_h - title_h * 1.05 - xxxx - footer_h;
    console.log("结果" + height);
    return height;
}
