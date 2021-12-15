<?php
$player = $game->player_get_uinfo();
$taskhtml = '';
$npc = $game->get_npc_info($arr_data->nid);
$rwztt='';
if ($npc->taskid!=''){
    $taskarr = explode(',',$npc->taskid);
    $taskhtml = '';
    for ($i=0;$i<count($taskarr);$i++){
        $task_player = $game->rw_player_get_info($taskarr[$i]); //是否有任务
        $task = $game->rw_get_sys($taskarr[$i]);
        $rwztt = '';

        if ($task_player){

            if($task_player->rwzt == 2){
                $reztarr = array('普通', '日常','主线');
                $rwzttext = $reztarr[ $task->rwlx - 1];
                $rwztt = '<img src="./images/tan.gif" />';
                $rwztt = $rwztt."[$rwzttext]";
            }

        }else{
            if ($task->chufa != $nid){
                continue;
            }

            if ($task->lastrwid != -1){
                $lastrw = $game->rw_player_get_info($task->lastrwid);
                if (!$lastrw){
                    continue;
                }elseif ($lastrw->rwzt !=3){
                    continue;
                }
            }

            $reztarr = array('普通', '日常','主线');
            $rwzttext = $reztarr[ $task->rwlx - 1];
            $rwztt = '<img src="./images/wen.gif" />';
            $rwztt = $rwztt . "[$rwzttext]";
        }

        if ($rwztt){
            $rwcmd = $game->create_url("cmd=task&nid=$nid&rwid=$taskarr[$i]","$task->rwname");
            $taskhtml .="$rwztt{$rwcmd}<br/>";
        }

    }
}
if ($taskhtml!=''){
    $taskhtml = '<br/>'.$taskhtml;
}
$gnhtml='';
if ($npc->muban != ''){
    $mubanitem =  explode(',',$npc->muban);
    foreach ($mubanitem as $muban){
        $muban = iconv('UTF-8','GBK',$muban);
        if (file_exists("./game/muban/$muban")){
            include "./game/muban/$muban";
        }
    }
}
$image = "";
if (file_exists("./game/images/npc/{$nid}.jpg")){
    $image = "<img src='./game/images/npc/{$nid}.jpg' width='200' height='300'><br/>";
}


$npchtml =
"昵称:$npc->npc_name<br/>
性别:$npc->npc_sex<br/>
信息:$npc->npc_info<br/>
$image
$taskhtml
$gnhtml<br/>
{$变量_系统->链接_返回游戏_按钮短}";
echo $npchtml
?>
