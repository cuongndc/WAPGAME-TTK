<?php
$mytaskinfo = '';
$taskhtml = '';
$rwzt = '';

if (isset($arr_data->type)) {
    switch ($arr_data->type) {
        case 'discard':
            $p_task = $task->get_player_task_info($player_info->sid, $arr_data->rwid);
            $task->del_player_task($player_info->sid, $p_task->task_id);
			$taskhtml .= "放弃{$p_task->name}这个任务成功！<br>";
			if (intval($task_info->rwevent_discard) != 0) {
				$branch = $event->event_decode($task_info->rwevent_discard);
			} 
            break;
		case 'tijiao':
            $p_task = $task->get_player_task_info($player_info->sid, $arr_data->rwid);
			$task_info = $task->get_task_info($p_task->task_id);
            $ret = $task->complete_player_task($player_info->sid, $p_task->task_id);
			if($ret){
				$taskhtml .= "提交{$p_task->name}这个任务成功！<br>";
				if (intval($task_info->rwevent_complete) != 0) {
					$event->event_decode($task_info->rwevent_complete);
					require_once './game/event_info.php';
				}
			}else{
				$taskhtml .= "提交{$p_task->name}这个任务失败！<br>";
			}
            break;
    } 
} 



$playertask = $task->rw_get_player_wwc($player_info->sid);

if (count($playertask) == 0) {
    $taskhtml .= "当前没有待完成的任务！<br>";
} ;

foreach($playertask as $task_info) {
    if (is_object($task_info)) {
        $rwid = $task_info->task_id;
        $rwname = $task_info->name;
        $rwlx = $task_info->type;

        if ($rwlx == 2 && $task_info->rwzt != 3) {
            $taskhtml .= '[每日]';
        } 
        if ($rwlx == 3 && $task_info->rwzt != 3) {
            $taskhtml .= '[主线]';
        } 
        if ($rwlx == 1 && $task_info->rwzt != 3) {
            $taskhtml .= '[普通]';
        } 
        switch ($task_info->state) {
            case 0:
                $taskhtml .= $sys->create_url("cmd=player_task_info&rwid=$rwid", '<img src="./images/wen.gif"/>' . $rwname);
                $taskhtml .= "<br>";
                break;
            case 2:
                $taskhtml .= $sys->create_url("cmd=player_task_info&rwid=$rwid", '<img src="./images/tan.gif"/>' . $rwname);
                $taskhtml .= "<br>";
                break;
            case 3:
                break;
        } 
    } 
} 

echo <<<HTML
========任务========
<br/>
{$taskhtml}
{$变量_系统->链接_返回游戏_按钮短}
HTML;

?>