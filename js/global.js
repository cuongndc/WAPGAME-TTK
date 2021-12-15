//公用js类
function ajax_alert(data){//加载信息框窗口
	var exbtn = '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
	if(!data.btn){
		data.btn = exbtn ;
	};
	if(data.exbtn){
		data.btn = exbtn + data.btn;
	};
	$("#alert-title").html(data.title); 
	$("#alert-body").html(data.body);
	$("#alert-button").html(data.btn);
	$('#ajax-alert').one('hidden.zui.modal', function() {
		if(data.repage){location.reload([data.repage])};
	})
}

function isEmpty(obj){//判断字符是否为空的方法
    if(typeof obj == "undefined" || obj == null || obj == ""){
        return true;
    }else{
        return false;
    }
}