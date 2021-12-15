<?php
echo <<<js

function del_event(path,clas,objid,key,confirm){//删除事件元素
	if(!confirm){var confirm = false;}
$.post('event-gl.php',{basic:"open",type:"del_event",path:path,clas:clas,objid:objid,key:key,confirm:confirm},function(data) {
		if(confirm){
			$('#con').html(data.title);
		};
		if(data.body){ajax_alert(data);}
		if(data.reloading){Reset_event(path,'event',objid);}
	})
}

function Reset_event(path,obj,id){
	$.post('event-gl.php',{basic:"open",type:"Reset",path:path,obj:obj,id:id},function(data) {
	$("#edit_body").html(data.body);
	})
}

function addobj(type,id){//添加附属物属性
	$.post('{$post_path}',{basic:"open",type:"addobj",clas:type,mid:id},function(data) { 
		ajax_alert(data); 
    }) 	
}


function add_branch(com,key,confirm,path){//添加一项步骤数据
  if(!confirm){var confirm=false;}
  if(confirm){
	 var mark = $("#add-mark").val();
	 var size = $("#add-size").val();
	 var category = $("#add-category").val();
	 var id = $("#add-id").val();
	 var name = $("#add-name").val();
	 var val = $("#add-value").val();
	 var num = $("#add-num").val();
	 if(!isEmpty(id)){

		var iadd = {id:id,name:name,val:val,num:num,mark:mark,name:name,size:size,category:category};
	 }else{
		var iadd = {name:name,val:val,num:num,mark:mark,name:name,size:size,category:category};
	 }
	 var data = {basic:"open",type:"add_field",clas:com,path:path,key:key,confirm:confirm,data:iadd};
  }else{
	 var data = {basic:"open",type:"add_field",clas:com,path:path,key:key,confirm:confirm}
  }
	$.post('{$post_path}',data,function(data) {
		ajax_alert(data);
		if(data.reloading){Reset(com,key,path);}
		if(data.reload){location.reload([data.reload])};
	})
}

function load_add_search(val,com,path,clas,key){
	var idata = {basic:"open",type:com,path:path,key:val,operation:clas,parent:key,only:true};
	$.post("{$post_path}",idata,function(data) {
		$("#search-value").html(data);
	})
 }


function editing_step(type,path,com,key,vid,confirm){//编辑一项步骤数据
  if(!confirm){var confirm = false;}
  if(confirm){
	 var mark = $("#add-mark").val();
	 var size = $("#add-size").val();
	 var category = $("#add-category").val();
	 var name = $("#add-name").val()
	 var val = $("#add-value").val()
	 var num = $("#add-num").val()
	 var iadd = {name:name,val:val,num:num,mark:mark,name:name,size:size,category:category};
	 var data = {basic:"open",type:"editing_step",clas:type,path:path,com:com,key:key,vid:vid,confirm:confirm,data:iadd};
  }else{
	 var data = {basic:"open",type:"editing_step",clas:type,path:path,com:com,key:key,vid:vid,confirm:confirm}
  }
	$.post('{$post_path}',data,function(data) {
		ajax_alert(data);
		if(data.reloading){Reset(com,key,path);}
	})
}


function Reset(obj,id,path){
	$.post('{$post_path}',{basic:"open",type:"Reset",path:path,obj:obj,id:id},function(data) {
	$("#window").html(data.body);
	})
}

js;
?>