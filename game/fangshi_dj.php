<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/8 0008
 * Time: 18:58
 */
$player = $game->player_get_uinfo();
$gonowmid = $game->create_url_nowmid();
$payhtml='';
$pdjcount = 0;
if (!isset($yeshu)){
    $yeshu = 0;
}
if (!isset( $cmd2) ){
    $cmd2 = "";
}
switch ($cmd2){
    case "buy":
        $fsdj = $game->fangshi_get_daoju($payid);
        if (!$fsdj){
            echo("道具已经卖光了<br/>");
        }
        if ( ( (int) $fsdj->djcount ) < ( (int) $buycount )){
            echo("道具数量不足<br/>");
        }

        $playerdj = $game->dj_get_player($fsdj->djid);
        if ($playerdj){
            $pdjcount = $playerdj->djsum;
        }

        $price = $buycount * $fsdj->pay;
        $ret = $game->yxb_change( 2 , $price );

        if(!$ret){
            echo("灵石不足<br/>");
            break;
        }


        $game->fangshi_update_daoju($payid , $buycount);
        $game->yxb_change_uid( 1 , $price , $fsdj->uid);
        $game->dj_add($fsdj->djid , $buycount);
        $game->fangshi_delete_daoju_all();

        echo "交易成功！<br/>";


}

$fsdjall = $game->fangshi_get_daoju_all();
foreach ($fsdjall as $fsdj){
    $djid = $fsdj->djid;
    $djname = $fsdj->djname;
    $djpay = $fsdj->pay;
    $djcount = $fsdj->djcount;
    $payid = $fsdj->payid;
    $goumaidj1 = $game->create_url("cmd=fangshi_dj&cmd2=buy&payid=$payid&buycount=1");
    $goumaidj5 = $game->create_url("cmd=fangshi_dj&cmd2=buy&payid=$payid&buycount=5");
    $goumaidj10 = $game->create_url("cmd=fangshi_dj&cmd2=buy&payid=$payid&buycount=10");
    $djpaycmd = $game->create_url("cmd=djinfo&djid=$djid");
    $payhtml .= "<a href='$djpaycmd'>{$djname}x$djcount</a>单价:$djpay<a href='$goumaidj1'>购买1</a><a href='$goumaidj5'>购买5</a> <a href='$goumaidj10'>购买10</a><br/>";
}

$zhuangbei = $game->create_url("cmd=fangshi_zb");
$payhtml="
【道具|<a href='$zhuangbei'>装备</a>】<br/>
$payhtml
<br/><a href='$gonowmid'>返回游戏</a><br/>";
echo $payhtml;