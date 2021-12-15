<?php
$p_task = $task->get_player_task_info($player_info->sid, $arr_data->rwid);
$task_info = $task->get_task_info($p_task->task_id);
$task_info = G_convertObjectClass($task_info, $p_task);

$gonowmid = $sys->create_url_nowmid();
$rwdjarr = explode(',', $task->rwdj);
$rwjlhtml = '任务奖励：<br/>';
$rwhtml = '';

if($task_info->rwdiscard ==1){
	$rwdiscard= $sys->create_url("cmd=player_task&type=discard&rwid={$p_task->task_id}",'放弃该任务');
}

if ($task->rwdj != '') {
    for ($i = 0; $i < count($rwdjarr); $i++) {
        $djarr = explode('|', $rwdjarr[$i]);
        $djid = $djarr[0];
        $djcount = $djarr[1];
        $rwdj = $game->dj_get_sys($djid);
        $djinfo = $game->create_url("cmd=djinfo&djid=$rwdj->djid");
        $rwjlhtml .= "<div class='djys'><a href='$djinfo'>$rwdj->djname</a>x$djcount</div>";
		
    } 
} 

if ($task->rwzb != '') {
    $ret = $game->zb_get_info_rw_all($rwid);
    for ($i = 0;$i < count($ret);$i++) {
        $zbname = $ret[$i]->zbname;
        $zbid = $ret[$i]->zbid;
        $zbcmd = $game->create_url("cmd=zbinfo_sys&cmd2=zbinfo_sys&zbid=$zbid");
        $rwjlhtml .= "<div class='zbys'><a href='$zbcmd'>$zbname</a></div>";
    } 
} 
if ($task->rwexp != '') {
    $rwjlhtml .= "经验：$task->rwexp<br/>";
} 
if ($task->rwyxb != '') {
    $rwjlhtml .= "灵石：$task->rwyxb<br/>";
} 


switch ($task_info->type) {
    case 'Seek':// 收集
    case 'seek':// 收集
		$Seek =json_decode( $task_info->rwseek);
		foreach($Seek as $obj){
			if(is_object($obj)){
				$rwyq = $goods->get_goods_info($obj->id);
				$rwhtml .= "收集：({$rwyq->name}) ×{$obj->num}<br>";
				$rwgoods =$goods->get_player_goods_initial($obj->id,$player_info->sid);
				if($rwgoods){
					$rwhtml .= "进度：{$rwgoods->number}/{$obj->num}<br>";
				}else{
					$rwhtml .= "进度：0/{$obj->num}<br>";
				}
			}
		}
        break;
    case 'Work':// 办事
    case 'work':// 办事	
		$field = $task_info->rwwork;
		$us =  $player->get_player_us($player_info->sid);
		if(intval($us->$field->val) == 1){
			$rwhtml .="进度：已达成条件<br>"; 
		}else{
			$rwhtml .="进度：未达成条件<br>"; 
		}
        break;
    case 'Killing':// 打怪
    case 'killing':// 打怪
		$Killing =json_decode( $task_info->rwKilling);
		foreach($Killing as $obj){
			if(is_object($obj)){
				$rwyq = $npc->get_npc_info($obj->id);
				$rwgoods =$npc->get_killtask_npc($player_info->sid,$obj->id);
				$rwhtml .= "击杀：({$rwyq->name}) ×{$obj->num}<br>";
				if($rwgoods){
					$number = intval($rwgoods->kill_number);
					$rwhtml .= "进度：{$number}/{$obj->num}<br>";
				}else{
					$rwhtml .= "进度：0/{$obj->num}<br>";
				}
			}
		}
        break;
} 
$rwUnfinished = $dis->dis_text_decode($task_info->rwUnfinished,$player_info,$task_info);
$taskinfo = "
{$task_info->name}:<br/>
{$rwUnfinished}<br/>
{$rwhtml}<br/><br/>
{$rwjlhtml}
{$rwdiscard}
<br/>
{$gonowmid}
";
echo $taskinfo;

?>