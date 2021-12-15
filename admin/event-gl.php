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


$test=file_get_contents("php://input"); 

parse_str($test,$value);

//print_r($value);

$event_id = $value['enid'];

if(isset($value['basic'])){
	switch($value['type']){
		case "reload":
			echo $event->event_type_load($value["clas"]);
		break;
		case "del":
			$event_content = $event->del_event($value['key']);
			if($event_content){
				$arry = array('title'=>"步骤列表已刷新",'body'=>$event->event_type_load($value['clas']));
			}else{
				$arry = array('title'=>"步骤列表已刷新",'body'=>"步骤删除失败！" ,'error'=>'true');
			};
		break;
		case "add_step":
			echo $event->add_branch();
		break;
		case "load_branch"://重新加载事件步骤
			$event_content = $event->get_event_info($event_id);
			$event_branch = $event->get_event_branch($event_content);
			$arry = array('title'=>"步骤列表已刷新",'body'=>$event_branch);
		break;
		case "edit_branch":
		switch($value['clas']){
			case "del":
				$body = $event->edit_branch($event_id,$value['key'],$value['clas']);
				if($body){$body = "步骤删除成功！";}else{$body = "步骤删除失败！";};
				$arry = array('title' => "删除步骤",'body' => $body);
			break;
			case "up":
				$body = $event->edit_branch($event_id,$value['key'],$value['clas']);
				if($body){$body = "步骤移动成功！";}else{$body = "步骤移动失败！";};
				$arry = array('title' => "上移步骤",'body' => $body);
			break;
		}
			
		break;
		case "save_event":
			if(intval($value['enid'])==0){
			  $eventid = $event->add_event($value['trigger'],$value['trigger_fail']);
			  if($eventid!=0){
				switch($value['path']){
					case 'operation':
						$operation->add_operation_field(array('clas'=>'event','key'=>$value['key'],'data'=>array('id'=>$eventid)));
					break;
					case 'skill':
					  switch($value['clas']){
						case 'use':
							$skill->set_skill_field($value['key'],'use',$eventid);
						break;
						case 'uplvl':
							$skill->set_skill_field($value['key'],'uplvl',$eventid);
						break;
					  }
					break;
					case 'map':
					  switch($value['clas']){
						case 'create':
							$map->set_mid_field($value['key'],'event_create',$eventid);
						break;
						case 'watch':
							$map->set_mid_field($value['key'],'event_watch',$eventid);
						break;
						case 'enter':
							$map->set_mid_field($value['key'],'event_enter',$eventid);
						break;
						case 'leave':
							$map->set_mid_field($value['key'],'event_leave',$eventid);
						break;
						case 'timing':
							$map->set_mid_field($value['key'],'event_timing',$eventid);
						break;
					  }
					break;
					case 'task':
					  switch($value['clas']){
						case 'accept':
							$task->set_task_field($value['key'],'accept',$eventid);
						break;
						case 'discard':
							$task->set_task_field($value['key'],'discard',$eventid);
						break;
						case 'complete':
							$task->set_task_field($value['key'],'complete',$eventid);
						break;
					  }
					break;
					case 'npc':
					  switch($value['clas']){
						case 'create':
							$npc->set_npc_field($value['key'],'event_create',$eventid);
						break;
						case 'watch':
							$npc->set_npc_field($value['key'],'event_watch',$eventid);
						break;
						case 'attack':
							$npc->set_npc_field($value['key'],'event_attack',$eventid);
						break;
						case 'defense':
							$npc->set_npc_field($value['key'],'event_defense',$eventid);
						break;
						case 'win':
							$npc->set_npc_field($value['key'],'event_win',$eventid);
						break;
						case 'fail':
							$npc->set_npc_field($value['key'],'event_fail',$eventid);
						break;
						case 'adopted':
							$npc->set_npc_field($value['key'],'event_adopted',$eventid);
						break;
						case 'trade':
							$npc->set_npc_field($value['key'],'event_trade',$eventid);
						break;
						case 'upgrade':
							$npc->set_npc_field($value['key'],'event_upgrade',$eventid);
						break;
						case 'heartbeat':
							$npc->set_npc_field($value['key'],'event_heartbeat',$eventid);
						break;
						case 'timing':
							$npc->set_npc_field($value['key'],'event_timing',$eventid);
						break;
					  }
					break;
					case 'goods':
					case 'equip':
					  switch($value['clas']){
						case 'create':
							$goods->set_goods_field($value['key'],'event_create',$eventid);
						break;
						case 'watch':
							$goods->set_goods_field($value['key'],'event_watch',$eventid);
						break;
						case 'use':
							$goods->set_goods_field($value['key'],'event_use',$eventid);
						break;
						case 'wear':
							$goods->set_goods_field($value['key'],'event_wear',$eventid);
						break;
						case 'undress':
							$goods->set_goods_field($value['key'],'event_undress',$eventid);
						break;
						case 'save':
							$goods->set_goods_field($value['key'],'event_save',$eventid);
						break;
						case 'backups':
							$goods->set_goods_field($value['key'],'event_backups',$eventid);
						break;
						case 'timing':
							$goods->set_goods_field($value['key'],'event_timing',$eventid);
						break;
					  }
					break;
				}
				$arry =  array('title'=>"操作成功！",'body'=>"事件新建成功！" ,'repage'=>true);
			  }
			}else{
			  if( $event->save_event($value['trigger'],$value['trigger_fail'],$event_id)){
				$arry =  array('title'=>"操作成功！",'body'=>"事件保存成功！");
			  }
			}
			

		break;
		case "save_branch":
			$branch_id = $event->save_branch($value['data']);
			if($branch_id >= 1){
				$data = json_decode($value['data']);
				$event_id = intval($data->event_id);
				if($event->event_edit_branch("add",$event_id,$branch_id)){
					$arry =  array('title'=>"保存步骤数据",'body'=>"保存步骤数据成功！");
				}
			}else{
				$arry =  array('title'=>"保存步骤数据",'body'=>"保存步骤数据成功！");
			}
		break;
		case "del_event":
			$arry = $event->del_event_req($value);
		break;
		case "Reset":
			$arry = $event->load_event_list($value['path'],$value['id']);
		break;
	}
	
	ajax_alert($arry);
exit;
}


?>
