<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/24 0024
 * Time: 12:59
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
        $fszb = $game->fangshi_get_zhuangbei($zbnowid);
        if (!$fszb){
            echo "装备已经被卖出了<br/>";
            break;
        }

        $pay = $fszb->pay;
        $game->yxb_change(2 , $pay);
        $ret = $game->fangshi_delete_zhuangbei($zbnowid);

        if(!$ret){
            echo "装备出货失败<br/>";
            break;
        }
        $ret = $game->yxb_change_uid(1,$pay ,$fszb->uid);
        if(!$ret && $pay>0){
            echo "挂出该装备的修士未收到灵石<br/>";
            break;
        }
        $game->zb_update_user($zbnowid);
        echo "交易成功！<br/>";
}

$fsdjall = $game->fangshi_get_zhuangbei_all();
foreach ($fsdjall as $fsdj){
    $zbnowid = $fsdj->zbnowid;
    $zbname = $fsdj->zbname;
    $zbqh = $fsdj->qianghua;
    $zbpay = $fsdj->pay;
    $payid = $fsdj->payid;
    if ($zbqh){
        $zbqh = '+'.$zbqh;
    }else{
        $zbqh='';
    }
    $goumaizb = $game->create_url("cmd=fangshi_zb&cmd2=buy&zbnowid=$zbnowid");
    $zbpaycmd = $game->create_url("cmd=zbinfo&zbnowid=$zbnowid");
    $payhtml .= "<a href='$zbpaycmd'>{$zbname}{$zbqh}</a>价格:$zbpay<a href='$goumaizb'>购买</a><br/>";
}
$fangshi = $game->create_url("cmd=fangshi_dj");
$payhtml="
【<a href='$fangshi'>道具</a>|装备】<br/>
$payhtml
<br/>
<a href='$gonowmid'>返回游戏</a><br/>";



echo $payhtml;