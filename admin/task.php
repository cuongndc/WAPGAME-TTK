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

$task_type = $_GET['type'];
$task_id = $_GET['id'];
switch($task_type){
	case 'edit':
		$body = $task->edit_task_info($task_id);
	break;
	case'':
	break;
}
require_once "html/header.php";
?>
<h2><?php echo $body->title;?></h2>
<span class="con"></span> 
<div id="edit_body">
<?php echo $body->body; ?>
</div>
<hr>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>


<script type="text/javascript"> 
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

function Reset(obj,id){
	$.post('task-list.php',{basic:"open",type:"Reset",obj:obj,id:id},function(data) {
	$("#window").html(data);
	$("#new_task").html("");
	})
}

<?php
 $post_path = "task-gl.php";
 require_once "js/edit-data.php";
?>

</script> 

<?php
require_once "html/footer.php";
?>