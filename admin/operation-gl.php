<?php
require_once "user_rights.php";

$table = $_SESSION['table'] ;
$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
$_SESSION['table'] = $table;


$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);

if(isset($value['basic'])){
	$key = $value['objid'];
	switch ($value['type']){
		case "add":
			$operation_id = $operation->new_operation();
			switch($value['path']){
				case 'map':
					$obj = $map->get_mid_info($key);
					$val = $obj->operation == ""?"{$operation_id}":"{$obj->operation},{$operation_id}";
					$map->set_mid_field($key,'operation',$val);
				break;
				case 'npc':
					$obj = $npc->get_npc_info($key);
					$val = $obj->operation == ""?"{$operation_id}":"{$obj->operation},{$operation_id}";
					$npc->set_npc_field($key,'operation',$val);
				break;
				case 'daoju':
					$obj = $goods->get_goods_info($key);
					$val = $obj->operation == ""?"{$operation_id}":"{$obj->operation},{$operation_id}";
					$goods->set_goods_field($key,'operation',$val);
				break;
			}
			$arry = array('href' => "operation.php?path={$value['path']}&id={$operation_id}");
		break;
		case "del_operation":
			$operation_info = $operation->get_operation_info($value['key']);
			switch($value['path']){
				case 'map':
					if($value['confirm']=="true"){
					$obj = $map->get_mid_info($key);
					$arry = explode(',',$obj->operation );
					$val = array_search($value['key'],$arry);
					array_splice($arry,$val,1);
					$val = implode(",", $arry);
					$map->set_mid_field($key,'operation',$val );
					$title = '删除操作元素成功！';
					$body ="已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作已删除";
					$reloading = true;
					
					}else{
						$title = '确认删除当前操作：';
						$body ="确认删除已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作的定义？";
				$exbtn = true;
		$button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_operation('{$value['path']}','{$key}','{$operation_info->id}','true')">确认删除</button>
html;
					}
				break;
				case 'npc':
					if($value['confirm']=="true"){
					$obj = $npc->get_npc_info($key);
					$arry = explode(',',$obj->operation );
					$val = array_search($value['key'],$arry);
					array_splice($arry,$val,1);
					$val = implode(",", $arry);
					$npc->set_npc_field($key,'operation',$val );
					$title = '删除操作元素成功！';
					$body ="已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作已删除";
					$reloading = true;
					
					}else{
						$title = '确认删除当前操作：';
						$body ="确认删除已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作的定义？";
				$exbtn = true;
		$button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_operation('{$value['path']}','{$key}','{$operation_info->id}','true')">确认删除</button>
html;
					}
				break;
				case 'daoju':
					if($value['confirm']=="true"){
					$obj = $goods->get_goods_info($key);
					$arry = explode(',',$obj->operation );
					$val = array_search($value['key'],$arry);
					array_splice($arry,$val,1);
					$val = implode(",", $arry);
					$goods->set_goods_field($key,'operation',$val );
					$title = '删除操作元素成功！';
					$body ="已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作已删除";
					$reloading = true;
					
					}else{
						$title = '确认删除当前操作：';
						$body ="确认删除已经添加的操作：<br>操作名：<b>[{$operation_info->name}]({$operation_info->id})]</b><br>显示条件：<b>[{$operation_info->appear}]</b><br>这个操作的定义？";
				$exbtn = true;
		$button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_operation('{$value['path']}','{$key}','{$operation_info->id}','true')">确认删除</button>
html;
					}
				break;
			}
			if($reloading){ $operation->del_operation($operation_info->id);}
			$arry = array('reloading' => $reloading ,'title' => $title,'body'=>$body,'btn'=>$button,'exbtn'=>$exbtn);
		break;
		case "addobj":
			$title = "编辑操作添加触发任务：";
			$com = $searchBox->create_search("输入任务名","operation-gl.php","operation","task","任务名",$value['key']);
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'del_attr':
			$operation_info = $operation->get_operation_info($value['key']);
			switch($value['clas']){
				case 'task':
					$task_info = $task->get_task_info($operation_info->task);
					if($value['confirm']=="true"){
						$arry = $operation->del_operation_field($value);
					}else{
						$title = '确认删除当前操作已定义触发任务的关联：';
						$body ="确认删除已添加的触发任务关联：<br>任务名：<b>[{$task_info->name}]({$task_info->id})]</b><br>任务描述：<b>[{$task_info->rwUnfinished}]</b><br>这个任务触发关联？";
						$exbtn = true;
						$button = "<button class='btn btn-danger ' type='button' onclick=\"del_operation_attr('task','{$value['key']}','true')\">确认删除</button>";
					}
				break;
				case 'event' :
					$event_info = $event->get_event_info($operation_info->event);
					if($value['confirm']=="true"){
						$res = $event->del_event($operation_info->event);
						if($res){
							$arry = $operation->del_operation_field($value);
						}else{
							$title = '删除当前操作定义触发事件操作失败！';
							$body ="删除已经添加的触发事件：<br>事件名：<b>[{$event_info->name}]({$event_info->id})]</b><br>事件触发条件：<b>[{$event_info->trigger}]</b><br>这个事件触发的操作失败了！";
							$exbtn = true;
						}
					}else{
						$title = '确认删除当前操作定义触发事件：';
						$body ="确认删除已经添加的触发事件：<br>事件名：<b>[{$event_info->name}]({$event_info->id})]</b><br>事件触发条件：<b>[{$event_info->trigger}]</b><br>这个事件触发？";
						$exbtn = true;
						$button = "<button class='btn btn-danger ' type='button' onclick=\"del_operation_attr('event','{$value['key']}','true')\">确认删除</button>";
					}
				break;
			}
			if(!is_array($arry)){
				$arry = array('reloading' => $reloading ,'title' => $title,'body'=>$body,'btn'=>$button,'exbtn'=>$exbtn);
			}
		break;
		case "search":
			$arry = $searchBox->search($value);
		break;
		case "save_operation":
			$arry = $operation->save_operation($value);
		break;
		case "add_field":
			$arry = $operation->add_operation_field($value);
		break;
		case 'Reset':
			switch($value['path']){
				case 'map':
					$arry =  $searchBox->reloading('map',$value['obj'],$value['id']);
					$arry = array('body'=>$arry);
				break;
				case 'npc':
					$arry =  $searchBox->reloading('npc',$value['obj'],$value['id']);
					$arry = array('body'=>$arry);
				break;
				case 'daoju':
					$arry =  $searchBox->reloading('daoju',$value['obj'],$value['id']);
					$arry = array('body'=>$arry);
				break;
			}
		break;
	}
	ajax_alert($arry);
}


?>