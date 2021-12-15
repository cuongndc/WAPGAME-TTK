<?php

$task_id = $arr_data->task?$arr_data->task:$task_id;

$task_info = $task->get_task_info($task_id);

$ptask = $task->rw_player_get_info($player_info->sid, $task_info->id);

if (!$ptask) {
    $task->insert_player_task($task_info->id);
    if (isset($task_info->rwaccept) && !empty($task_info->rwaccept)) {
        $rwaccept = $dis->dis_text_decode($task_info->rwaccept, $player_info, $task_info);
    } else {
        $rwaccept = true;
    } 

    if (!$rwaccept) {
        if (isset($task_info->rwunacceptable) && !empty($task_info->rwunacceptable)) {
            echo "{$task_info->rwunacceptable}<br>";
            $cmd2 = $arr_data->cmd2;
            switch ($cmd) {
                case 'npcinfo':
                    $obj = $npc->get_npc_run($arr_data->gid);
                    echo $sys->create_url("cmd={$cmd2}&gid={$obj->id}&gyid={$obj->gid}&nowmid={$player_info->nowmid}", "返回{$obj->name}");
                    break;
            } 
            echo "<br>{$变量_系统->链接_返回游戏_按钮短}";
            exit;
        } else {
            $arr_data->cmd = $arr_data->cmd2;
            if ($arr_data->cmd == "event") {
                $ym = "./game/{$arr_data->cmd}/{$arr_data->cmd2}.php";
            } else {
                $ym = "./game/{$arr_data->cmd}.php";
            } 
            require_once $ym;
            exit;
        } 
    } else {
        if (intval($task_info->rwevent_accept) != 0) {
            $branch = $event->event_decode($task_info->rwevent_accept);
        } 
    } 
} else{
	$task_info = G_convertObjectClass($task_info,$ptask);
}

if($task_info->rwdiscard ==1){
	$rwdiscard= $sys->create_url("cmd=player_task&type=discard&rwid={$ptask->task_id}",'放弃该任务');
}
/*
if(!$ptask){
	$jieshourw = $sys->create_url("cmd=player_task&type=jieshou&rwid={$task_info->id}",'接受任务');
}
*/
//var_dump($task_info->type);

switch ($task_info->type) {
    case 'Seek':// 收集
    case 'seek':// 收集
		$Seek =json_decode($task_info->rwseek);
		foreach($Seek as $obj){
			if(is_object($obj)){
				$rwyq = $goods->get_goods_info($obj->id);
				$rwgoods =$goods->get_player_goods_initial($obj->id,$player_info->sid);
				if($rwgoods){
					if(($rwgoods->number - $obj->num) < 0 ){
						$tijiaorw = false;
						break;
					}else{$tijiaorw = true;}
				}
			}
		}
        break;
    case 'Work':// 办事
    case 'work':// 办事
		$field = $task_info->rwwork;
		$us =  $player->get_player_us($player_info->sid);
		if(intval($us->$field->val) == 1){
			$tijiaorw = true;
		}else{
			$tijiaorw = false;
		}
        break;
    case 'Killing':// 打怪
    case 'killing':// 打怪
		if(!empty($task_info->rwKilling)){
			$Seek = json_decode($task_info->rwKilling);
		foreach($Seek as $obj){
			if(is_object($obj)){
				$rwyq = $npc->get_npc_info($obj->id);
				$rwgoods =$npc->get_killtask_npc($player_info->sid,$obj->id);
				if($rwgoods){
					if((intval($rwgoods->kill_number) - intval($obj->num)) < 0 ){
						$tijiaorw = false;
						break;
					}else{$tijiaorw = true;}
				}
			}
		}
		}
        break;
} 


if($tijiaorw){
	/*
	if (intval($task_info->rwevent_complete) != 0) {
		$branch = $event->event_decode($task_info->rwevent_complete);
	} 
	*/
	$tijiaorw = $sys->create_url("cmd=player_task&type=tijiao&rwid={$ptask->task_id}",'提交任务');
}

if (isset($player_info->event_id) && !empty($player_info->event_id)) {
	$event->event_decode($operation_info->event, $branch_html);
    //require_once 'event_info.php';
} 

$rwdjarr = explode(',', $task->rwdj);
$rwyparr = explode(',', $task->rwyp);
$rwjlhtml = '任务奖励：<br/>';
$jldjidarr = array();
$jldjslarr = array();
$jlypidarr = array();
$jlypslarr = array();
$jlzbslarr = array();

$gonowmid = $sys->create_url_nowmid();

$rwhtml = '';
$tishi = '';
if ($ptask) {
    if ($ptask->rwzt == 3) {
        echo "<a href='$gonowmid'>返回游戏</a>";
        exit();
    } 
} 

if ($task->rwdj != '') {
    for ($i = 0;$i < count($rwdjarr);$i++) {
        $djarr = explode('|', $rwdjarr[$i]);
        $djid = $djarr[0];
        $djcount = $djarr[1];
        array_push($jldjidarr, $djid);
        array_push($jldjslarr, $djcount);
        $rwdj = $game->dj_get_sys($djid);
        $djinfo = $game->create_url("cmd=djinfo&djid=$rwdj->djid&sid");
        $rwjlhtml .= "<div class='djys'><a href='$djinfo'>$rwdj->djname</a>x$djcount</div>";
    } 
} 

if ($task->rwyp != '') {
    for ($i = 0;$i < count($rwyparr);$i++) {
        $yparr = explode('|', $rwyparr[$i]);
        $ypid = $yparr[0];
        $ypcount = $yparr[1];
        array_push($jlypidarr, $ypid);
        array_push($jlypslarr, $ypcount);
        $rwyp = $game->yp_get_info_sys($ypid);
        $ypcmd = $game->create_url("cmd=ypinfo&ypid=$ypid");
        $rwjlhtml .= "<div class='ypys'><a href='$ypcmd'>$rwyp->ypname</a>x$ypcount</div>";
    } 
} 

if ($task->rwzb != '') {
    $ret = $game->zb_get_info_rw_all($task->rwid);

    for ($i = 0;$i < count($ret);$i++) {
        $zbid = $ret[$i]->zbid;
        $zbname = $ret[$i]->zbname;
        array_push($jlzbslarr, $zbid);
        $zbkzb = $game->zb_get_info_sys($zbid);
        $zbcmd = $game->create_url("cmd=zbinfo_sys&zbid=$zbkzb->zbid");
        $rwjlhtml .= "<div class='zbys'><a href='$zbcmd'>$zbname</a></div>";
    } 
} 
if ($task->rwexp != '') {
    $rwjlhtml .= "经验：$task->rwexp<br/>";
} 
if ($task->rwyxb != '') {
    $rwjlhtml .= "灵石：$task->rwyxb<br/>";
} 

if (isset($canshu)) {
    switch ($canshu) {
        case 'jieshou':
            if ($ptask) {
                $tishi = '请不要重复接取任务';
                break;
            } 
            $day = 0;
            if ($task->rwlx == 2) {
                $day = date('d');
            } 
            $ret = $game->rw_insert_player_rwid($rwid);
            if ($ret) {
                $tishi = '接受成功';
                if ($task->rwzl == 1) {
                    $daoju = $game->dj_get_player($task->rwyq);
                    var_dump($daoju);

                    if ($daoju) {
                        $game->rw_update_dj($task->rwyq , $daoju->djsum);
                    } 
                } 
            } 

            break;
        case 'tijiao':
            if ($ptask->rwzt == 2) {
                $bool = $game->rw_com($rwid);
                if ($bool) {
                    $game->player_add_exp($task->rwexp);
                    $game->yxb_change(1, $task->rwyxb);
                    for ($i = 0; $i < count($jldjidarr); $i++) {
                        $game->dj_add($jldjidarr[$i], $jldjslarr[$i]);
                    } 
                    for ($i = 0;$i < count($jlypidarr);$i++) {
                        $game->yp_add($jlypidarr[$i], $jlypslarr[$i]);
                    } 

                    foreach ($jlzbslarr as $jlzbid) {
                        $game->zb_add_zhuangbei($jlzbid);
                    } 
                    echo "任务完成,获得：<br/>$rwjlhtml<a href='$gonowmid'>返回游戏</a>";
                    exit();
                } 
            } 
            break;
    } 
} 

$task->rw_update_rwzt();

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
		if(!empty($task_info->rwKilling)){
		$Seek =json_decode( $task_info->rwKilling);
		foreach($Seek as $obj){
			if(is_object($obj)){
				$rwyq = $npc->get_npc_info($obj->id);
				$rwhtml .= "击杀：({$rwyq->name}) ×{$obj->num}<br>";
				$rwgoods =$npc->get_killtask_npc($player_info->sid,$obj->id);
				if($rwgoods){
					$number = intval($rwgoods->kill_number);
					$rwhtml .= "进度：{$number}/{$obj->num}<br>";
				}else{
					$rwhtml .= "进度：0/{$obj->num}<br>";
				}
			}
		}
		}
        break;
} 

$cfnpc = $npc->get_npc_info($task->chufa);
$tjnpc = $npc->get_npc_info($task->tijiao);

$ptask = $task->rw_player_get_info($player_info->sid,$rwid);

$rwzthtml = '';

if ($ptask) {
    if ($ptask->tijiao == $nid) {
        if ($ptask->rwzl != 3) {
            $rwzthtml = "进度：$ptask->rwnowcount/$ptask->rwcount<br/>";
            $rwzthtml .= "<a href='$tijiaorw'>提交</a>";
        } elseif ($ptask->tijiao == $nid) {
            $rwzthtml .= "<a href='$tijiaorw'>提交</a>";
        } 
    } 
} else {
    if ($task->chufa == $nid) {
        $rwzthtml = $jieshourw;
    } 
} 

$taskhtml = "
【{$task_info->name}】:<br/>
{$task_info->rwUnfinished}<br/>
{$rwhtml}<br/>
{$tishi}<br/>
{$rwjlhtml}<br/>
{$rwzthtml}<br/>
{$tijiaorw}<br>
{$rwdiscard}<br/>
{$变量_系统->链接_返回游戏_按钮短}
";
echo $taskhtml;

?>