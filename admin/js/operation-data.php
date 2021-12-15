<?php
$post_path = "operation-gl.php";
echo <<<js
function new_operation(type,path,objid){//新建操作元素
	$.post('{$post_path}',{basic:"open",type:type,path:path,objid:objid},function(data) {
		if(data.href){ $(location).prop('href', data.href)};
	})
}

function add_operation_attr(type,id){//添加触发任务
	$.post('{$post_path}',{basic:"open",type:"addobj",clas:type,key:id},function(data) { 
		ajax_alert(data); 
    }) 	
}

function del_operation_attr(type,id,confirm){//删除触发任务
	if(!confirm){var confirm = false;}
	$.post('{$post_path}',{basic:"open",type:"del_attr",clas:type,key:id,confirm:confirm},function(data) { 
		ajax_alert(data); 
		if(data.reload){location.reload([data.reload])};
    }) 	
}

function save_operation(key){//保存操作元素修改
	var name = $('#name').val();
	var appear = $('#appear').val();
	$.post('{$post_path}',{basic:"open",type:"save_operation",name:name,appear:appear,key:key},function(data) {
		$("#con").html(data.title);
	})
}

function del_operation(path,objid,key,confirm){//删除操作元素
	if(!confirm){var confirm = false;}
	$.post('{$post_path}',{basic:"open",type:"del_operation",path:path,objid:objid,key:key,confirm:confirm},function(data) {
		$("#con").html(data.title);
		if(data.body){ajax_alert(data);}
		if(data.reloading){Reset_operation(path,'operation',objid);}
	})
}

function Reset_operation(path,obj,id){
	$.post('{$post_path}',{basic:"open",type:"Reset",path:path,obj:obj,id:id},function(data) {
	$("#window").html(data.body);
	})
}

js;
?>