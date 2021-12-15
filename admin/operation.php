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

$op_path = $_GET['path'];
$op_id = $_GET['id'];

$alert_open = alert_open;

if(isset($op_path)){
  switch($op_path){
	case "map":
		map_operation($op_path,$op_id);
	break;
  }
}

function map_operation($com,$id){//地图操作定义
	global $title;
	switch($com){
		case 'add':
			$title = '编辑场景添加操作：';
		break;
	}	
}

if(intval($op_id)!=0){
	$operation_info = $operation->get_operation_info($op_id);
	$op_id = $operation_info->id;
	$operation_name = $operation_info->name;
	$operation_name = $operation_name =="未命名"?"":$operation_name;
	$operation_appear = $operation_info->appear;
}

require_once "html/header.php";
?>

<div id="dis">
<h2>操作编辑器</h2>
<h4><?php echo $title; ?></h4>
<div id="con"></div>
操作提示：<input type="text" id="name" class="form-control" placeholder="操作提示" value="<?php echo $operation_name;?>">
出现条件：<textarea class="form-control" id="appear" name="play" rows="3" placeholder="操作出现条件"><?php echo $operation_appear;?></textarea>
<h3>触发事件：
<?php
			if(intval($operation_info->event)!=0){
				echo  "<a class='btn btn-primary' href='event.php?type=edit&path=operation&key={$op_id}'>修改事件</a>
						<button type=\"button\" onclick=\"del_operation_attr('event','{$op_id}')\" {$alert_open} class=\"btn btn-danger\">删除事件</button>";
			}else{
				echo "<a class='btn btn-primary' href='event.php?type=add&path=operation&key={$op_id}'>设置事件</a>";
			}
			echo '</h3>';
			echo '<h3>触发任务：';
			if(intval($operation_info->task)!=0){
				$task_info = $task->get_task_info($operation_info->task);
				echo "
				<button type=\"button\" onclick=\"add_operation_attr('task','{$op_id}')\" {$alert_open} class=\"btn btn-info\">{$task_info->name}({$task_info->id})</button>
				<button type=\"button\" onclick=\"del_operation_attr('task','{$op_id}')\" {$alert_open} class=\"btn btn-danger\">删除任务</button>";
			}else{
echo <<<html
	<button type="button" onclick="add_operation_attr('task','{$op_id}')" {$alert_open} class="btn btn-primary">添加任务</button>
html;
			}
echo <<<html
		</h3>
	<hr>
	<button type="button" onClick="save_operation('{$op_id}')" class="btn btn-primary"  data-dismiss="modal">保存修改</button>
html;
?>
<hr>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>

<script src="js/event.js"></script>

<script type="text/javascript"> 
<?php 
$post_path = "operation-gl.php"; 
require_once "js/edit-data.php"; 
require_once "js/operation-data.php"; 
?>
</script> 
<?php
require_once "html/footer.php";
?>