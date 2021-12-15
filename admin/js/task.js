
function new_task(obj,id){//新建任务
  $('#ajax_test').modal('hide');
	$.post('task-gl.php',{basic:"open",type:"new",obj:obj,id:id},function(data) {
	$("#window").html("");
	$("#new_task").html(data);
	})
}

function edit_task(obj,obj_id,task_id){//载入任务编辑
	$.post('task-gl.php',{basic:"open",type:"edit",obj:obj,obj_id:obj_id,task_id:task_id},function(data) {
		$("#ajax_te").html(data); 
		$('#ajax_test').modal('show','fit');
	})
}

function save_task(obj,obj_id,task_id=0){//保存任务数据
	var params = $("#task").serializeArray();
	var values = {};
	for( x in params ){
		values[params[x].name] = params[x].value;
		}
	var idata = JSON.stringify(values)
	$.post('task-gl.php',{basic:"open",type:"save",data:idata,obj:obj,obj_id:obj_id,task_id:task_id},function(data) {
		$("#ajax_te").html(data.html); 
		$("#window").html(data.list); 
	    $("#new_task").html("");
		$('#ajax_test').modal('show','fit');
	})
	
}

function del(obj,obj_id,task_id){//删除一条任务
	if(obj=="dis"){$('#ajax_test').modal('hide');}
	$.post('task-gl.php',{basic:"open",type:"del",obj:obj,task_id:task_id,obj_id:obj_id},function(data) {
		$("#ajax_te").html(data.title); 
		$("#window").html(data.list); 
		$('#ajax_test').modal('show','fit');
	})
}

function kill_npc_task(obj,task_id,npc_id=0){//添加任务需要杀死的npc
	var win=$("#ajax_test").attr("aria-hidden");
	$.post('task-gl.php',{basic:"open",type:"add_npc",obj:obj,task_id:task_id,npc_id:npc_id},function(data) {
		$("#ajax_te").html(data); 
		if(win!=false){$('#ajax_test').modal('show','fit');alert("tc")}
	})
}


function del_task(genus,genus_name,genus_id,name,step){//删除任务引用及任务
 if(!step){ alert("无效的任务删除命令！"); return;}
  $('#ajax_test').modal('hide');
  $('#ajax_test').on('hidden.zui.modal', function () {
	$.post('task-gl.php',{basic:"open",type:"del",genus:genus,genus_name:genus_name,genus_id:genus_id,step:step},function(data) {
		$("#ajax_test").off("hidden.zui.modal");
		if(data.add_edit=="true" && data.genus=="dis"){
			edit(data.name,data.id);
			return ;
			}
		if(data.error=="true"){
		(new $.zui.ModalTrigger({title: '提示',custom:data.title})).show();
		}
	})
 })
}





