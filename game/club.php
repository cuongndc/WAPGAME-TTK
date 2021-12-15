<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/26 0026
 * Time: 18:37
 */
$clubplayer = $game->clubplayer_get_player();
$player = $game->player_get_uinfo();
$gonowmid = $game->create_url_nowmid();
$clubhtml = '';
$clubmenu = '';
$renzhihtml = '';
$playerlist = '';
if (isset($canshu)){
    switch ($canshu){
        case "joinclub":
            if ($clubplayer){
                echo "你已经有门派了<br/>";
                break;
            }
            $row = $game->club_join($clubid);
            $clubplayer = $game->player_get_uinfo();
            echo "恭喜你成功加入<br/>";
            break;
        case "outclub":
            if ($clubplayer){
                $game->club_out();
                $clubplayer = $game->player_get_uinfo();
            }
            break;
        case "deleteclub":
            if ($clubplayer){
                if ($clubplayer->uclv == 1){
                    $sql="delete from club WHERE clubid='$clubplayer->clubid'";
                    $row = $dblj->exec($sql);
                    $sql="delete from clubplayer WHERE clubid='$clubplayer->clubid'";
                    $row = $dblj->exec($sql);
                    echo "门派解散成功<br/>";
                }
            }
            break;
        case "renzhi":
            if ($clubplayer){
                if (isset($zhiwei)){
                    $sql="select uid from clubplayer WHERE clubid=$clubplayer->clubid AND uclv > $clubplayer->uclv";
                    $ret = $dblj->query($sql);
                    $retuid = $ret->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($retuid as $uiditem){
                        $uid = $uiditem['uid'];
                        if ($uid==$player->uid){
                            continue;
                        }
                        $otherplayer = \player\getplayer1($uid,$dblj);
                        $ucmd = $encode->encode("cmd=club&canshu=zhiwei&zhiwei=$zhiwei&uid=$uid&sid=$sid");
                        $playerlist .= "<a href='?cmd=$ucmd'>{$otherplayer->uname}</a><br/>";

                    }
                   $renzhihtml = "
                    =========选择任职人员=========<br/>
                    $playerlist<br/>
                    <a href='$gonowmid'>返回游戏</a>";
                    exit($renzhihtml);
                }

                if ($clubplayer->uclv == 1){
                    $no2cmd = $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=2");
                    $no3cmd = $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=3");
                    $no4cmd = $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=4");
                    $no5cmd = $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=5");
                    $no6cmd = $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=6");
                    $renzhihtml = "<a href='$no2cmd'>任职副掌门</a><br/><a href='$no3cmd'>任职长老</a><br/><a href='$no4cmd'>任职执事</a><br/><a href='$no5cmd'>任职精英</a><br/><a href='?cmd=$no6cmd'>任职弟子</a><br/>";
                }
                if ($clubplayer->uclv == 2){
                    $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=3&sid=$sid");
                    $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=4&sid=$sid");
                    $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=5&sid=$sid");
                    $gpcl->create_url("cmd=club&canshu=renzhi&zhiwei=6&sid=$sid");
                    $renzhihtml = "<a href='$no3cmd'>任职长老</a><br/><a href='$no4cmd'>任职执事</a><br/><a href='$no5cmd'>任职精英</a><br/><a href='$no6cmd'>任职弟子</a><br/>";
                }
            }
            break;
        case "zhiwei":
            $sql="update clubplayer set uclv = $zhiwei WHERE uid=$uid AND clubid = $clubplayer->clubid";
            $dblj->exec($sql);

    }
}

if (isset($clubid) || $clubplayer){
    if ($clubplayer){
        if (isset($clubid)){
            if ($clubplayer->clubid != $clubid){
                goto noclub;
            }
        }else{
            $clubid = $clubplayer->clubid;
        }
        $outclubcmd = $gpcl->create_url("cmd=club&canshu=outclub");
        $clubmenu = "<a href='?cmd=$outclubcmd'>判出</a>";
        if ($clubplayer->uclv==1){
            $outclubcmd = $gpcl->create_url("cmd=club&canshu=deleteclub");
            $renzhicmd = $gpcl->create_url("cmd=club&canshu=renzhi");
            $clubmenu = "<a href='$renzhicmd'>任职</a> <a href='$outclubcmd'>解散</a>";
        }
    }else{
        $joincmd = $gpcl->create_url("cmd=club&clubid=$clubid&canshu=joinclub");
        $clubmenu = "<a href='?cmd=$joincmd'>申请加入</a>";
    }
    noclub:
    $club = $game->club_get_info($clubid);
    $cboss = $game->player_get_uinfo_uid($club->clubno1);
    $cbosscmd =$gpcl->create_url("cmd=getplayerinfo&uid=$club->clubno1");
    $clublist = $gpcl->create_url("cmd=clublist");
    $retuid = $game->clubplayer_get_all($clubid);

    foreach ($retuid as $uiditem){
        $uid = $uiditem->uid;
        $uclv = $uiditem->uclv;
        $chenhao = "[弟子]";
        switch ($uclv){
            case 1:
                $chenhao = "[掌门]";
                break;
            case 2:
                $chenhao = "[副掌门]";
                break;
            case 3:
                $chenhao = "[长老]";
                break;
            case 4:
                $chenhao = "[执事]";
                break;
            case 5:
                $chenhao = "[精英]";
                break;
        }
        $otherplayer = $game->player_get_uinfo_uid($uid);
        $ucmd = $gpcl->create_url("cmd=otherzhuangtai&uid=$uid");
        $playerlist .= "<a href='$ucmd'>{$chenhao}{$otherplayer->uname}</a><br/>";
    }

    $clubhtml =<<<HTML
门派:$club->clubname<br/>
创建者:<a href="?cmd=$cbosscmd" >$cboss->uname</a><br/>
门派资金:灵石[$club->clubyxb] 极品灵石[$club->clubczb]<br/>
建设度:$club->clubexp<br/>
门派介绍:<br/>$club->clubinfo<br/>
$clubmenu
<a href="?cmd=$clublist">门派列表</a><br/>
$renzhihtml
门派成员：<br/>
$playerlist
<br/>
<a href="?cmd=$gonowmid">返回游戏</a>
HTML;
    exit($clubhtml);

}

if (!$clubplayer){
    $clublist = $gpcl->create_url("cmd=clublist");
    $clubhtml =<<<HTML
你现在还没有门派呢！<br/>
<a href="?cmd=$clublist">加入门派</a>
<br/>
<br/>
<a href="?cmd=$gonowmid">返回游戏</a> 
HTML;

}
echo $clubhtml;