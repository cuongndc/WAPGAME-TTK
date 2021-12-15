<?php
/** @var string $sid */
/** @var string $cmd2 */


//$getbagzbcmd = $game->create_url( "cmd=bagzb&cmd2=bagzb");

//$clubplayer = \player\getclubplayer_once($sid,$dblj);
//if ($clubplayer){
//    $club = \player\getclub($clubplayer->clubid,$dblj);
//    $clubcmd = $encode->encode("cmd=club&sid=$sid");
//    $clubname ="<a href='?cmd=$clubcmd'>$club->clubname</a>";
//}else{
//    $clubname = "无门无派";
//}


switch ($cmd2){
    case 'xxzb':
        if (isset($xxzb)){
            $player->player_tool_xzzb($xxzb);
            $player_info = $player->get_player_info();
        }
        break;
    case 'setzb':
        $player->player_tool_setzb( $zbid , $tool);
        break;
}

$player_info = $player->get_player_info();
$tool = array();

$player_info = (array) $player_info;
for ($i = 1 ; $i <= 6 ; $i++){
    if ($player_info["tool$i"]){

        $zhuangbei = $player->zb_get($player_info["tool$i"]);
        if ($zhuangbei->qianghua > 0 ){
            $qhs = '+'.$zhuangbei->qianghua;
        }else{
            $qhs = '';
        }

        $downzb = $player->create_url( "cmd=zhuangtai&cmd2=xxzb&xxzb=1","卸下");
        $zbinfo = $player->create_url( "cmd=zbinfo&cmd2=zbinfo&zbnowid={$player_info["tool$i"]}","{$zhuangbei->zbname}{$qhs}");

        $xxzb = $downzb;
        $tool[$i] = "{$zbinfo}{$xxzb}";

    }else{
        $tool[$i] = '';
    }
}
$player_info = (object) $player_info;

$变量_状态 = (object)array(
    "装备1" => $tool[1],
    "装备2" => $tool[2],
    "装备3" => $tool[3],
    "装备4" => $tool[4],
    "装备5" => $tool[5],
    "装备6" => $tool[6]
);

$dis_mid = $dis->dis_get('dis_player');
if(!is_object($dis_mid)){
	$dis_mid = $dis->dis_get('排版_当前玩家状态');
	eval("\$out_html = \"$dis_mid->dis_string\";");
}else{
	$out_html = '<p>'.$dis->dis_decode(json_decode($dis_mid->dis_string),$player_info).'</p>';
}
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);
echo $out_html;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/10
 * Time: 17:34
 */?>