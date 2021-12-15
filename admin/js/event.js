
function edit_event(dis_name,id,name,type,step,clas){//加载事件编辑器
  if(!step){step=1;} 
  
  if(type=="edit"){
	  $.post('event-gl.php',{basic:"open",type:type,disname:dis_name,id:id,name:name,step:step,clas:clas},function(data) {$("#dis").html(data)})
	  return ;
	  }
	  
  if(!type){type="edit"; }

  if(type!="step"){
  $('#ajax_test').modal('hide');
  $('#ajax_test').on('hidden.zui.modal', function () {
	$.post('event-gl.php',{basic:"open",type:type,disname:dis_name,id:id,name:name,step:step,clas:clas},function(data) {
		$("#ajax_test").off("hidden.zui.modal");
		$("#dis").html(data)
	})
  })
  }else{
	$.post('event-gl.php',{basic:"open",type:type,disname:dis_name,id:id,name:name,step:step,clas:clas},function(data) {$("#dis").html(data)})  
  }
}

function del_event(){//删除事件引用及事件
  $('#ajax_test').modal('hide');
  $('#ajax_test').on('hidden.zui.modal', function () {
	$.post('event-gl.php',{basic:"open",type:"save"},function(data) {
		$("#ajax_test").off("hidden.zui.modal");
		$("#dis").html(data)
	})
  })
}
