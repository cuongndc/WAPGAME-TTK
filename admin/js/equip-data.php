<?php
$post_path = "equip-gl.php";
echo <<<js

function deploy_equip(type,target,targetid,equip,equipcl,equipid,confirm){//对指定目标部署装备
	var idata = {basic:'open',type:'deploy',clas:type,target:target,targetid:targetid,equip:equip,equipcl:equipcl,equipid:equipid,confirm:confirm};
	$.post('{$post_path}',idata,function(data) {ajax_alert(data)});
}

function load_add_equip(val,com,path,clas,key,equip,equipcl){
	var idata = {basic:"open",type:com,path:path,key:val,operation:clas,parent:key,equip:equip,equipcl:equipcl,only:true};
	$.post("{$post_path}",idata,function(data) {
		$("#search-value").html(data);
	})
 }
 
function add_deploy_equip(com,key,confirm,path,equip,equipcl){//添加一项步骤数据
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
	 var data = {basic:"open",type:"add_field",clas:com,path:path,key:key,equip:equip,equipcl:equipcl,confirm:confirm,data:iadd};
  }else{
	 var data = {basic:"open",type:"add_field",clas:com,path:path,key:key,equip:equip,equipcl:equipcl,confirm:confirm}
  }
	$.post('{$post_path}',data,function(data) {
		ajax_alert(data);
		if(data.reloading){Reset_equip(path,'equip',key);}
		if(data.reload){location.reload([data.reload])};
	})
}

function del_deploy_equip(path,key,equip,equipcl,confirm){//删除一项步骤数据
  if(confirm){
	 var data = {basic:"open",type:"del_field",path:path,key:key,equip:equip,equipcl:equipcl,confirm:confirm};
	 $.post('{$post_path}',data,function(data) {
		ajax_alert(data);
		if(data.reloading){Reset_equip(path,'equip',key);}
		if(data.reload){location.reload([data.reload])};
	})
  }
}

function Reset_equip(path,obj,id){
	$.post('{$post_path}',{basic:"open",type:"Reset",path:path,obj:obj,id:id},function(data) {
	$("#edit_body").html(data.body);
	})
}

js;
?>