<?php
require_once "user_rights.php";

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;

$type=$_GET['type'];
$id=$_GET['id'];

require_once "html/header.php";
?>
<style>
.p1{
overflow: hidden;
text-overflow: ellipsis;
display: -webkit-box;
-webkit-line-clamp: 1;
-webkit-box-orient: vertical;
}
</style>

<h2>任务设计</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一个任务</b></td><td style="text-align:right">
<button type="button" onclick="new_task()"  data-position="100px" data-toggle="modal" data-target="#ajax-alert" class="btn btn-primary"> 新建任务</button>
</td></tr>
</table>
<table class="table table-bordered table-condensed">
  <thead id="list_thead">
	<tr>
      <th>任务ID</th>
      <th>任务名</th>
      <th>任务类型</th>
	  <th>任务简单描述</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="list_tbody"></tbody>
</table>
<ul class="pager" id="pager" style="margin-bottom: 0px;margin-top: 0px;"></ul>
<hr>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
<script type="text/javascript"> 
$('#pager').pager({
    page: 1,
	recPerPage:20,
	elements:['first','prev','nav','next','last','goto','size_menu','total_text','page_of_total_text'],
});

$(document).ready(function () { 
$('#pager').on('onPageChange', function(e, state, oldState) {
	if (state.page !== oldState.page || state.recPerPage !== oldState.recPerPage) {reload();}
});
	reload();
}); 

function reload(){
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	var idata = {basic:'open',type:'page',page:pager.page,recPerPage:pager.recPerPage};
	$.post('task-gl.php',idata,function(data){
		$('#list_tbody').html(data.html);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
	})
}

function new_task(id){//新建任务
  $('#ajax_test').modal('hide');
	$.post('task-gl.php',{basic:"open",type:"new",id:id},function(data) {
		ajax_alert(data);
	})
}

function save_task(task_id){//保存任务数据
	if(!task_id){var task_id = 0;}
	var params = $("#task").serializeArray();
	var values = {};
	for( x in params ){
		values[params[x].name] = params[x].value;
		}
	var idata = JSON.stringify(values)
	$.post('task-gl.php',{basic:"open",type:"save",data:idata,task_id:task_id},function(data) {
		ajax_alert(data);
	})
	
}

function edit_task(task_id){//载入任务编辑
	$.post('task-gl.php',{basic:"open",type:"edit",task_id:task_id},function(data) {
		ajax_alert(data);
	})
}


function del_task(task_id,confirm){//删除一条任务
	if(!confirm){confirm = false;}
	$.post('task-gl.php',{basic:"open",type:"del",task_id:task_id,confirm:confirm},function(data) {
		ajax_alert(data);
	})
}

function kill_npc_task(obj,task_id,npc_id=0){//添加任务需要杀死的npc
	var win=$("#ajax_test").attr("aria-hidden");
	$.post('task-gl.php',{basic:"open",type:"add_npc",obj:obj,task_id:task_id,npc_id:npc_id},function(data) {
		$("#ajax_te").html(data); 
		if(win!=false){$('#ajax_test').modal('show','fit');alert("tc")}
	})
}




</script> 
<?php
require_once "html/footer.php";
?>