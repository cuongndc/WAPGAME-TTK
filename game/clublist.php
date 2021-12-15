<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/27 0027
 * Time: 11:49
 */
$clublist = '';
$allclub = \player\getclub_all($dblj);
$player = $game->player_get_uinfo();
$gonowmid = $game->create_url_nowmid();

if ($allclub){
    $i = 0;
    foreach ($allclub as $club){
        $i++;
        $clubcmd = $encode->encode("cmd=club&clubid={$club['clubid']}&sid=$sid");
        $clublist .= "[$i]<a href='?cmd=$clubcmd' >{$club['clubname']}</a><br/>";
    }
}

$clubhtml ="
===========门派天榜===========
      <br/>
      $clublist<br/>
      <a href=$gonowmid>返回游戏</a> ";



echo $clubhtml;