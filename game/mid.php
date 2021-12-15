<?php
/** @var string $sid */
if ( !isset($arr_data->cmd2) ){$cmd2 = 'nowmid';}else {$cmd2 = $arr_data->cmd2 ;}

if ($player_info->uhp <= 0) { $tishi = "<div class='tishi' >重伤，请治疗<br/></div>";}else{$tishi = '';}

switch ($cmd2){
    case 'gomid':
        if ( !isset($arr_data->gomid) ){
			$gomid = $player_info->nowmid;
			} else {
			$gomid = $arr_data->gomid;
		}
        if ($player_info->hp <= 0) {
            $gomid = $player->player_go_re();//移动到复活点
            $map_info = $map->get_mid_info($gomid);//获取地图信息
            break;
        }
        $player->player_relocation_mid($gomid);//移动
        $map_info = $map->get_mid_info($gomid);//获取地图信息

        break;
    case 'gonowmid':
        $map_info =  $map->get_mid_info($player->nowmid);//获取地图信息
        break;
    case 'goremid':
        $gomid = $player->player_go_re();//移动到复活点
        $map_info = $map->get_mid_info($gomid);//获取地图信息
        break;
}

$player_info = $player->get_player_info();//获取玩家信息

if (!$player_info->nowmid){//判断角色是否出现在非法地图
    $出生地 = $sys->get_system_config("游戏","出生地");
	if(isset($出生地)){
	$player->player_update_game1('nowmid', $出生地);
    $player_info->nowmid = $出生地;
	}else{
	echo "<b>糟糕，我们好像在这个世界迷失了，而且当前世界尚未设置人物出生地！</b>";
	exit();
	}
}

$map->load_map_data($player_info->nowmid);

$map_info = $map->get_mid_info($player_info->nowmid);//获取地图信息

if ($map_info){
    if (!$map_info->midinfo)
    $map_info->midinfo = $map_info->mname;
}

if ( $player->ispvp!=0 ){
    $pvper = $player->get_player_info($player->ispvp);
    $pvpcmd = $sys->create_url("cmd=pvp&cmd2=into_pvp&uid=$pvper->uid","还击");
    $pvpcmd = "<a href='$pvpcmd'>还击</a>";
    $tishi .= "$pvper->uname 正在攻击你：$pvpcmd<br/>";
}

if ($player->player_is_tupo() && $player->uexp >= $player->umaxexp){
    $tupocmd = $sys->create_url("cmd=tupo&cmd2=tupo","突破");
    $tishi .=  "你即将需要突破,否则将不能获得经验:$tupocmd";
}

if(!empty($map_info->operation)){
	$Operands = $dis->load_operation_list($map_info->operation);
}

//以下内容 - 加载并尝试解析界面模板定义

$dis_mid = $dis->dis_get('dis_map');

if(!is_object($dis_mid)){
	$dis_mid = $dis->dis_get('排版_地图');
	eval("\$out_html = \"$dis_mid->dis_string\";");
}else{
	$out_html = '<p>'.$dis->dis_decode(json_decode($dis_mid->dis_string),$player_info
	).'</p>';
}

$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);

echo $out_html;
?>