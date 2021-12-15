<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/22
 * Time: 22:30
 */

$player = $game->player_get_uinfo();
$tupocmd = $game->create_url("cmd=tupo&cmd2=up" , "突破");
$gonowmid = $game->create_url_nowmid();
$tupo = $game->player_is_tupo( );

$tplshtml="";
$tpls = 0;



if ($tupo){
    $upgj = 0;
    $upfy = 0;
    $uphp = 0;


    if ($tupo == 1 ){
        $tpls = $player->ulv * $player->ulv * $player->ulv * 6;
    }elseif($tupo == 2){
        $tpls = $player->ulv * ($player->ulv+1) * 4;
    }

    $tplshtml =  "突破需要灵石：{$tpls}/$player->uyxb{$tupocmd}<br/>";

    switch ($cmd2) {
        case"up":
            $ret = $game->yxb_change(2, $tpls);
            if ($ret) {
                $sjs = mt_rand(1, 10);
                if ($sjs <= 5) {
                    echo "突破失败<br/>";
                    break;
                }
                if ($tupo == 2) {
                    $uphp = 2 + round($player->uhp / 20);
                    $upgj = 1 + round($player->ugj / 12);
                    $upfy = 1 + round($player->ufy / 10);
                } elseif ($tupo == 1) {
                    if ($sjs < 8) {
                        echo "突破失败<br/>";
                        $player = $game->player_get_uinfo();
                        break;
                    }
                    $uphp = 4 + round($player->uhp / 16);
                    $upgj = 2 + round($player->ugj / 8);
                    $upfy = 3 + round($player->ufy / 6);
                }

                $game->player_lv_add();
                $game->player_change_ugj($upgj , 1);
                $game->player_change_ufy($upfy , 1);
                $game->player_change_umaxhp($uphp , 1);
                $player = $game->player_get_uinfo();
                $tplshtml = '';

                echo "突破成功,获得属性：<br/>攻击+$upgj<br/>防御+$upfy<br/>气血+$uphp<br/>";
            } else {
                echo "灵石不足<br/>突破需要灵石：$tpls<br/>";
            }
            break;
    }



}




$tupohtml = <<<HTML
======突破======<br/>
当前境界：$player->jingjie$player->cengci<br/>
$tplshtml
<br/>
{$系统变量->链接_返回游戏_按钮长}
HTML;
echo $tupohtml;
?>

