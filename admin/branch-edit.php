<?php
require_once "user_rights.php";

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$event_op = $_SESSION['event'];
$event_obj = $_SESSION['eventobj'];
$event_id = $_SESSION['event_id'];
$event_branch_id = $_SESSION['event_branch_id'];
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
$_SESSION['event'] = $event_op;
$_SESSION['eventobj'] = $event_obj;
$_SESSION['event_id'] = $event_id;
$_SESSION['event_branch_id'] = $event_branch_id ;

$test = file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);
//var_dump($event_id,$event_branch_id,$_GET['type'],$_GET['key'],$_GET['path']);

$event_path = $_GET['path'];
$event_type = $_GET['type'];
$event_paid = $_GET['key'];
$event_id = $_GET['enid'];
//var_dump(intval($event_id));
if(intval($event_id)==0){
	switch($event_path){
		case 'operation':
		$event_id = $event->add_event();
		var_dump("<br>",$event_id);
		if($event_id !=0 ){
			$operation->add_operation_field(array('clas'=>'event','key'=>$event_paid,'data'=>array('id'=>$event_id)));
		}
		break;
	}
}
if(isset($_GET['type'])){
  switch($_GET['type']){
	case "add":
		$content = $event->add_branch($event_id);
	break;
	case "edit":
		$content = $event->load_branch($_GET['key']);
	break;
  }
}

if(isset($value['basic'])){
	switch ($value['type']){
	  case "edit_step":
		echo $event->edit_branch_type($value['clas'],$value['key']);
	  break;
	  case "addobj":
		$arry = $event->add_branch_type($value['clas'],$value['confirm'],$value['data'],$value['mid']);
		if(!is_array($arry)){ echo $arry; exit;}
	  break;
	  case "add_field":
			$path = isset($value['path'])?$value['path']:$value['clas'];
			$arry = $event->add_branch_type($path,$value['confirm'],$value['data'],$value['key']);
	  break;
	  case 'Reset':
			if(isset($value['path']) && $value['path']!="event"){
				$type = $value['path'];
			}else{
				$type = $value['obj'];
			};
			$arry =  $searchBox->reloading('event',$type,$value['id']);
			$arry = array('body'=>$arry);
	  break;
	  case "editing_step":
		$arry = $event->editing_step($value);
		if(!is_array($arry)){ echo $arry; exit;}
	  break;
	  case "search":
			$arry = $searchBox->search($value);
	  break;
	  case "save_form": //保存由步骤编辑器上传的装备变更事件
		if($value['clas']== 'mall-members'){
			$arry = $event->mall_members_effect($value['key'],$value['data']);
		}else{
			$arry = $event->edit_equipment($value['key'],$value);
		}
	  break;
	  case 'edit_branch_val':
		$arry = $event->edit_branch_val($value);
	  break;
	}
	ajax_alert($arry);
	exit;
}


require_once "html/header.php";
?>

<style>
li a{
	background:lightgray;
}

.p1{
overflow: hidden;
text-overflow: ellipsis;
display: -webkit-box;
-webkit-line-clamp: 1;
-webkit-box-orient: vertical;
}
</style>

<h2>定义操作事件步骤:</h2>
<span class="con"></span> 
<div id="dis">
<?echo $content;?>
</div>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回事件</a>
<br><br>

<script type="text/javascript"> 
function save_branch(type){//保存一个步骤的修改
	var params = $("#add_step").serializeArray();
	var values = {};
	for( x in params ){
		values[params[x].name] = params[x].value;
		}
	var idata = JSON.stringify(values);
	$.post('event-gl.php',{basic:"open",type:"save_branch",clas:type,data:idata},function(data) {
		ajax_alert(data);
		$(".con").html(data.body);
		
	})
}

function edit_branch(type,key){//编辑步骤数据
	$.post('branch-edit.php',{basic:"open",type:"edit_step",clas:type,key:key},function(data) {
		$("#dis").html(data); 
	})
}

 function edit_val(type,clas,id,key){
	var idata = {basic:"open",type:"edit_branch_val",clasa:type,clasb:clas,id:id,key:key};
	$.post('branch-edit.php',idata,function(data) {ajax_alert(data); });
 }

 function reloading(key,type){
	var idata = {basic:"open",type:"reloading",clas:type,key:key};
	$.post('branch-edit.php',idata,function(data) {$("#list").html(data.body); });
 }
 
 <?php
 $post_path = "branch-edit.php";
 require_once "js/edit-data.php";

?>
</script>

<?php
require_once "html/footer.php";
?>