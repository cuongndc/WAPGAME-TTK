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

$task_id = $_GET['id'];
$task_type = $_GET['type'];

require_once "html/header.php";

if($task_type =='killing'){
	$list = $searchBox->reloading('task',"rwKilling",$task_id);
	$title = "修改任务击杀目标";
	$Tips = "添加一个电脑人物";
	$btn = "选择电脑人物";
	$clas = "npc";
}else{
	$list = $searchBox->reloading('task',"rwseek",$task_id);
	$title = "修改任务寻找物品";
	$Tips = "添加一个物品";
	$btn = "选择物品";
	$clas = "goods";
}

?>

<h2><?php echo $title ;?></h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b><?php echo $Tips ;?></b></td><td style="text-align:right">
<button type="button" onclick="Choicenpc('<?php echo $task_id ;?>','<?php echo $clas ;?>')" <?php echo alert_open ;?> class="btn btn-primary"><?php echo $btn ;?></button>
</td></tr>
</table>

<div id="window"><?php echo $list;?></div>

<hr>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
<script type="text/javascript"> 
function Choicenpc(id,clas){//加载NPC选取窗口
	$.post('task-gl.php',{basic:"open",type:"Selection",clas:clas,key:id},function(data) {
		ajax_alert(data);
	});
}
<?php
 $post_path = "task-gl.php";
 require_once "js/edit-data.php";
?>
</script> 

<?php
require_once "html/footer.php";
?>