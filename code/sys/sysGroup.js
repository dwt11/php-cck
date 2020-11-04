$(document).ready(function () {
    $(".i-checks").iCheck({
        checkboxClass: "icheckbox_square-green",
        radioClass: "iradio_square-green",
    })
    $('#adminRole').on('ifChecked', function(event){
        //选中管理员,隐藏其他的权限显示
        $('#group').hide();
    })
    $('#adminRole').on('ifUnchecked', function(event){
        //不选中管理员,显示其他的权限显示
        $('#group').show();
    })

    $("#form1").validate({
        rules: {
            depid: {isIntGtZero: !0}
        },
        messages: {
            depid: {isIntGtZero: "请选择公司"}
        }
    })

    $("#form2").validate({
        rules: {
            groupname: {required: !0}
        },
        messages: {
            groupname: {required: "请填写组名称"}
        }
    })
});


//行全选
function row_Sel($rowi)
{
	var deps = document.getElementsByName('dep'+$rowi+'[]');
	var oldstu=deps[0].checked;
    for(i=0; i < deps.length; i++)
    {
         deps[i].checked=oldstu;
    }
}

//列全选
//$coli  当前点击的列,
//$rowNumb  要全选的总行数
function col_Sel($coli,$rowNumb)
{
	var files = document.getElementById('file_'+$coli);  //列头,用于获取原始状态
	var oldstu=files.checked;

    //如果功能不包含部门数据  则行的总数是0  则只操作隐藏的一个checkbox的选中状态
	//如果功能包含部门数据 则行的总数是部门的总数 
    if($rowNumb==0)
	{
			 var files_checkbox = document.getElementById('file_'+$coli+"_-100"); 
			 files_checkbox.checked=oldstu;
	}else
	{
		for(i=0; i < $rowNumb; i++)
		{
			 var files_checkbox = document.getElementById('file_'+$coli+"_"+i); 
			 files_checkbox.checked=oldstu;
		}
	}
}
