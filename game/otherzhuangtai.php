<?php

/** @var string $sid */
$player_other = $player->player_get_uinfo_uid( $arr_data->uid );
$immenu='';
if ($player_info->sid == $player_other->sid){
	require_once "zhuangtai.php";
	return ;
}

//var_dump($arr_data->uid,$player_other);
$攻击 = $sys->create_url("cmd=pvp&cmd2=into_pve&uid=$uid","攻击");
//$clubplayer = $game->clubplayer_get_player_uid($uid);
if (isset($canshu)){
    if ($canshu == "addim"){
        $game->im_add($uid);
    }
}
/*
if ($clubplayer){
    $club = $game->club_get_info($clubplayer->clubid);
    $clubname = $sys->create_url("cmd=club&clubid=$club->clubid",$club->clubname);
}else{
    $clubname = "无门无派";
}
*/
$Operands = $sys->create_url("cmd=otherzhuangtai&cmd2=otherzhuangtai&canshu=addim&uid=$uid","加为好友");

if ($player_info->sid != $player_other->sid){
    $好友菜单 = $攻击;
    //$ret = $game->im_is($uid);
    if (!$ret){
        $加为好友 = $sys->create_url("cmd=otherzhuangtai&cmd2=otherzhuangtai&canshu=addim&uid=$uid","加为好友");
        $好友菜单 .= $加为好友;
    }else{
        $chat=  $sys->create_url("cmd=otherzhuangtai&canshu=addim&uid=$uid");
        $删除好友 =  $sys->create_url("cmd=im&canshu=deim&uid=$uid","删除好友");
        $immenu.= "$删除好友
                    <form>
                    <input type='hidden' name='cmd' value='sendliaotian'>
                    <input type='hidden' name='ltlx' value='im'>
                    <input type='hidden' name='sid' value='$sid'>
                    <input type='hidden' name='imuid' value='$uid'>
                    <input name='ltmsg'>
                    <input type='submit' value='发送私聊'>
                    </form>";
    }
    $immenu .= "<br/>";
}

$tool = array();
$player1 = (array) $player1;
for ($i = 1 ; $i <= 6 ; $i++){
    if ($player1["tool$i"]){

        $zhuangbei = $game->zb_get($player1["tool$i"]);
        if ($zhuangbei->qianghua > 0 ){
            $qhs = '+'.$zhuangbei->qianghua;
        }else{
            $qhs = '';
        }

        $tool[$i] = $sys->create_url( "cmd=zbinfo&cmd2=zbinfo&zbnowid={$player1["tool$i"]}" , "{$zhuangbei->zbname}{$qhs}");
    }else{
        $tool[$i] = '';
    }
}

$player1 = (object) $player1;
$game_zb1 = $sys->get_system_config("游戏","装备1");
$game_zb2 = $sys->get_system_config("游戏","装备2");
$game_zb3 = $sys->get_system_config("游戏","装备3");
$game_zb4 = $sys->get_system_config("游戏","装备4");
$game_zb5 = $sys->get_system_config("游戏","装备5");
$game_zb6 = $sys->get_system_config("游戏","装备6");

$dis_mid = $dis->dis_get('dis_others');
if(!is_object($dis_mid) || intval($dis_mid->dis_prohibit) == 0){
	$dis_mid = $dis->dis_get('排版_查看玩家');
	eval("\$out_html = \"$dis_mid->dis_string\";");
}else{
	$out_html = '<p>'.$dis->dis_decode(json_decode($dis_mid->dis_string),$player_info,$player_other).'</p>';
}

$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);
echo $out_html;

?>
