<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 20:30
 */
$game = new \main\game();
$gmcmd = $game->create_url("cmd=npc&cmd2=gogoumai&nid=$nid");

$gnhtml = "<br/><a href='$gmcmd'>购买药品</a><br/>";

    switch ($cmd2){
        case 'goumai':
            if (isset($ypcount) && isset($ypid)){
                $yaopin = $game->yp_get_info_sys($ypid);
                $ypjg = $yaopin->ypjg;
                $ypname = $yaopin->ypname;
                $ret = $game->yxb_change( 2, $ypjg * $ypcount);
                if ($ret){
                    $game->yp_add($ypid,$ypcount);
                    $gnhtml .= "购买".$ypcount.$ypname."成功<br/>";
                }else{
                    $gnhtml .= "灵石数量不足<br/>";
                }
            }
            break;
        case 'gogoumai':
            $gnhtml='';

            $yaopin = $game->yp_get_info_all();
            foreach ($yaopin as $oneyaopin){
                $ypname = $oneyaopin->ypname;
                $ypid = $oneyaopin->ypid;
                $ypjg = $oneyaopin->ypjg;
                $ypcmd = $game->create_url("cmd=ypinfo&ypid=$ypid");
                $gm1yp = $game->create_url("cmd=npc&nid=$nid&cmd2=goumai&ypcount=1&ypid=$ypid");
                $gm5yp = $game->create_url("cmd=npc&nid=$nid&cmd2=goumai&ypcount=5&ypid=$ypid");
                $gm10yp = $game->create_url("cmd=npc&nid=$nid&cmd2=goumai&ypcount=10&ypid=$ypid");
                $gm1yp = "<a href='$gm1yp'>购买1</a>";
                $gm5yp = "<a href='$gm5yp'>购买5</a>";
                $gm10yp = "<a href='$gm10yp'>购买10</a>";
                $gnhtml .= "<br/><a href='$ypcmd'>$ypname--$ypjg 灵石</a>$gm1yp$gm5yp$gm10yp";
            }
            $gnhtml .="<br/>";
            break;
}






