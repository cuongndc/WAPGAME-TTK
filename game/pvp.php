<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/21
 * Time: 22:22
 */

$mid = $game->mid_get_info($player->nowmid );
$game->mid_get_qy($mid->mqy );

$player = $game->player_get_uinfo();
$pvper = $game->player_get_uinfo_uid($uid);
$gonowmid = $game->create_url_nowmid();

if ( !$mid->ispvp ){

    $game->player_update_game1( "ispvp" , 0);
    $tishihtml = "当前地图不允许PK<br/><br/><a href='$gonowmid'>返回游戏</a>";

}

if ( !$pvper->sfzx ){

    $game->player_update_game1( "ispvp" , 0 );
    $tishihtml = "该玩家没有在线<br/><br/><a href='$gonowmid'>返回游戏</a>";
}

if ($pvper->nowmid != $player->nowmid){

    $game->player_update_game1( "ispvp" , 0 );
    $tishihtml = "该玩家没在该地图<br/><br/><a href='$gonowmid'>返回游戏</a>";
}

if ($player->uhp <= 0){

    $game->player_update_game1( "ispvp" , 0 );
    $game->player_go_re();
    $tishihtml = "你是重伤之身,无法进行战斗<br/><br/><a href='$gonowmid'>返回游戏</a>";
}

if ($pvper->uhp <= 0){

    $game->player_update_game1( "ispvp" , 0 );
    $game->player_update_game1_uid( "ispvp" , 0 , $uid);
    $tishihtml = "该玩家已经死亡<br/><br/><a href='$gonowmid'>返回游戏</a>";
}
if(isset($tishihtml)){
    exit($tishihtml) ;
}

switch ($cmd2){
    case "into_pve":
        $game->player_update_ispvp($pvper->uid);
        $game->player_update_ispvp($player->uid);
        $html = $game->create_pvp_info($player , $pvper , 0 ,0 , 0 , 0 , "" );
        echo $html;
        break;

    case "ptgj":

        $game->player_update_ispvp($pvper->uid );
        $game->player_update_ispvp_uid($player->uid , $pvper->uid );

        $pvperhurt = '';
        $tishihtml = '';
        $pvpbj = '';

        $ran = mt_rand(1,100);
        if ($player->ubj >= $ran){
            $player->ugj = round($player->ugj * 1.82);
            $pvpbj = '暴击';
        }

        $pvperhurt = round($player->ugj - $pvper->ufy * 0.75,0);
        if ($pvperhurt < $player->ugj * 0.05){
            $pvperhurt = round($player->ugj*0.05);
        }

        $pvpxx = round($pvperhurt*($player->uxx/100));

        $game->player_change_uhp_sid($pvperhurt , 2 , $pvper->sid);
        $game->player_change_uhp($pvpxx , 1);

        $player =  $game->player_get_uinfo();
        $pvper = $game->player_get_uinfo_uid($uid);



        if ($player->uhp<=0){

            $cxmid = $game->mid_get_info( $player->nowmid);
            $cxqy = $game->mid_get_qy( $cxmid->mqy );
            $game->player_update_ispvp(0);
            $game->player_update_ispvp_sid(0 , $pvper->sid);

            $html = "
                战斗结果:<br/>
                你被$pvper->uname 打死了<br/>
                战斗失败！<br/>
                请少侠重来<br/>
                <br/>
                <a href='$gorehpmid'>返回游戏</a>";


        }elseif ($pvper->uhp<=0){

            $game->player_update_ispvp(0);
            $game->player_update_ispvp_sid(0 , $pvper->sid);

            $dieinfo = ["听说 $player->uname 打死了 $pvper->uname","$pvper->uname 被 $player->uname 打的落花流水"," $player->uname 把 $pvper->uname 打得生活不能自理"];
            $randdie = mt_rand(0,count($dieinfo)-1);
            $msg = $dieinfo[$randdie];
            $game->liaotian_send_all($msg , "百晓生" , 0);

            $html = "
                   战斗结果:<br/>
                   你打死了$pvper->uname<br/>
                   战斗胜利！<br/>
                   <a href='$gonowmid'>返回游戏</a>";
        }else{
            $html = $game->create_pvp_info($player , $pvper ,0 , $pvperhurt , $pvpbj , $pvpxx ,"" );
        }

        echo $html;

        break;
}








