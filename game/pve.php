<?php

//该文件已经作废

$game = new \main\game();
$player = $game->player_get_uinfo();
$cxmid = $game->mid_get_info($player->nowmid);
$cxqy = $game->mid_get_qy($cxmid->mqy);

$gonowmid = $game->create_url_nowmid();

$rwts = '';
$cwhtml='';
$pgjcmd = $game->create_url("cmd=pve&cmd2=ptgj&gid=$gid");
$guaiwu = $game->gw_get_info($gid);
$yguaiwu = new \player\guaiwu();
if ($guaiwu->gyid){
    $yguaiwu = $game->gw_get_info_sys($guaiwu->gyid);
}


$useyp1 = $game->create_url("cmd=pve&cmd2=useyp&ypid=$player->yp1&gid=$gid&nowmid=$nowmid");
$useyp2 = $game->create_url("cmd=pve&cmd2=useyp&ypid=$player->yp2&gid=$gid&nowmid=$nowmid");
$useyp3 = $game->create_url("cmd=pve&cmd2=useyp&ypid=$player->yp3&gid=$gid&nowmid=$nowmid");

$usejn1 = $game->create_url("cmd=pve&cmd2=usejn&jnid=$player->jn1&gid=$gid&nowmid=$nowmid");
$usejn2 = $game->create_url("cmd=pve&cmd2=usejn&jnid=$player->jn2&gid=$gid&nowmid=$nowmid");
$usejn3 = $game->create_url("cmd=pve&cmd2=usejn&jnid=$player->jn3&gid=$gid&nowmid=$nowmid");

$ypname1 = '药品1';
$ypname2 = '药品2';
$ypname3 = '药品3';

$jnname1 = '符箓1';
$jnname2 = '符箓2';
$jnname3 = '符箓3';

$huode = '';
$cwhurt = '';
$tishihtml='';
$pvebj = '';
$pvexx = '';


$html1 = "
        请正常玩游戏！<br/>
        <br/>
        <a href='$gonowmid'>返回游戏</a>";

$html2 = "
        怪物已经被其他人攻击了！<br/>
        请少侠练习一下手速哦
        <br/>
        <a href='$gonowmid'>返回游戏</a>";

if ($nowmid != $player->nowmid){
    exit($html1);
}

if (($guaiwu->sid != $sid && $guaiwu->sid != '') || ($guaiwu->gid=='')){
        exit($html2);
}

switch ($cmd2){
    case 'intopve':
        if ($player->ulv >= 10 && $player->uhp <=0){
            $zdjg = -1;
        }else{
            $game->gw_set_sid($sid  , $gid );
            $cw = \player\getchongwu($player->cw,$dblj);
            \player\changecwsx('cwhp',$cw->cwmaxhp,$player->cw,$dblj);
            if($player->ulv <= 10){
                \player\changeplayersx('uhp',$player->umaxhp,$sid,$dblj);
                $player =  $game->player_get_uinfo( $sid );
            }
        }

        break;


    case 'useyp'://使用药品
        $ret = $game->yp_use( $ypid , 1 , $sid );
        $player =  $game->player_get_uinfo($sid);
        break;
    case 'ptgj'://普通攻击

        $hurt = false;
        $ghurt = 0;
        $jineng = new \player\jineng();

        if (isset($canshu)){
            switch ($canshu){
                case 'usejn':
                    $ret = \player\delejnsum($jnid,1,$sid,$dblj);
                    if ($ret){
                        $jineng = \player\getplayerjineng($jnid,$sid,$dblj);
                        $tishihtml = "使用技能：$jineng->jnname<br/>";
                    }else{
                        $tishihtml = "技能数量不足<br/>";
                    }

                    break;
            }
        }

        $player->ugj += $jineng->jngj;
        $player->ufy += $jineng->jnfy;
        $player->ubj += $jineng->jnbj;
        $player->uxx += $jineng->jnxx;

        $lvc = $player->ulv - $guaiwu->glv;
        if ($lvc <= 0){
            $lvc = 0;
        }

        $phurt = 0 ;

        $phurt = round($guaiwu->ggj - ($player->ufy * 0.75),0);
        if ($phurt<$guaiwu->ggj*0.15){
            $phurt = round($guaiwu->ggj*0.15);
        }

        $ran = mt_rand(1,100);
        if ($player->ubj >= $ran){
            $player->ugj = round($player->ugj * 1.72);
            $pvebj = '暴击';
        }

        $gphurt = round($player->ugj - ($guaiwu->gfy * 0.75),0);
        if ($gphurt < $player->ugj*0.15){
            $gphurt = round( $player->ugj * 0.15);
        }
        $pvexx = ceil($gphurt * ($player->uxx/100) );

        if ($phurt <= 0){
            $hurt = true;
        }

        if ($phurt < $pvexx){
            $pvexx = $phurt - 1;

            if ($pvexx<0){
                $pvexx = 0;
            }
        }

        $sql = "update midguaiwu set ghp = ghp - {$gphurt} WHERE id='$gid'";
        $dblj->exec($sql);
        $guaiwu = $game->gw_get_info($gid);

        if ($guaiwu->ghp<=0){//怪物死亡
            $sql = "delete from midguaiwu where id = $gid AND sid='$sid'";
            $dblj->exec($sql);

            $yxb = round($guaiwu->glv/2.9) + 1;
            if ($hurt || $lvc >=5){
                $yxb = 0;
            }

            $ret = \player\changeyxb(1,$yxb,$sid,$dblj);
            if ($ret){
                $huode .= "获得灵石:$yxb<br/>";
            }
            $taskarr = \player\getplayerrenwu($sid,$dblj);
            \player\changerwyq1(2,$guaiwu->gyid,1,$sid,$dblj);
            for ($i=0;$i<count($taskarr);$i++){
                $rwyq = $taskarr[$i]['rwyq'];
                $rwid = $taskarr[$i]['rwid'];
                $rwzl = $taskarr[$i]['rwzl'];
                $rwzt = $taskarr[$i]['rwzt'];
                if ($rwyq==$guaiwu->gyid && $rwzl==2 && $rwzt!=3){
                    $rwnowcount = $taskarr[$i]['rwnowcount']+ 1;
                    $rwts = $taskarr[$i]['rwname'].'('.$rwnowcount."/".$taskarr[$i]['rwcount'].')<br/>';
                    break;
                }
            }

            $sjjv = mt_rand(1,120);
            if ($yguaiwu->dljv >=$sjjv && $yguaiwu->gzb != ''){
                $sql = "select * from zhuangbei WHERE zbid in ($yguaiwu->gzb)";
                $cxdljg = $dblj->query($sql);
                if ($cxdljg){
                    $retzb = $cxdljg->fetchAll(PDO::FETCH_ASSOC);
                    $sjdl = mt_rand(0,count($retzb)-1);
                    $zbname = $retzb[$sjdl]['zbname'];
                    $zbid = $retzb[$sjdl]['zbid'];
                    $game->zb_add_zhuangbei($zbid,$uid,$sid);
                    $huode .= "获得:<div class='zbys'>$zbname</div>";
                }
            }
            $sjjv = mt_rand(1,180);
            if ($yguaiwu->djjv >= $sjjv && $yguaiwu->gdj != ''){
                $sql = "select * from daoju WHERE djid in ($yguaiwu->gdj)";
                $cxdljg = $dblj->query($sql);
                if ($cxdljg){
                    $retdj = $cxdljg->fetchAll(PDO::FETCH_ASSOC);
                    $sjdj = mt_rand(0,count($retdj)-1);
                    $djname = $retdj[$sjdj]['djname'];
                    $djid = $retdj[$sjdj]['djid'];
                    if ($djid == 1 && $lvc == 0){
                        goto yp;
                    }
                    $djsum = mt_rand(1,2);
                    \player\adddj($sid,$djid,$djsum,$dblj);
                    $huode .= "获得:<div class='djys'>$djname x$djsum</div>";

                    for ($i=0;$i<count($taskarr);$i++){
                        $rwyq = $taskarr[$i]['rwyq'];
                        $rwid = $taskarr[$i]['rwid'];
                        $rwzl = $taskarr[$i]['rwzl'];
                        $rwzt = $taskarr[$i]['rwzt'];
                        if ($rwyq==$djid && $rwzl==1 && $rwzt!=3){
                            $rwnowcount = $taskarr[$i]['rwnowcount']+ $djsum;
                            $rwts = $taskarr[$i]['rwname'].'('.$rwnowcount."/".$taskarr[$i]['rwcount'].')<br/>';
                            break;
                        }
                    }

                }
            }
            yp:
            $sjjv = mt_rand(1,100);
            if ($yguaiwu->ypjv >= $sjjv && $yguaiwu->gyp != ''){

                $sql = "select * from yaopin WHERE ypid in ($yguaiwu->gyp)";
                $cxdljg = $dblj->query($sql);
                $retyp = $cxdljg->fetchAll(PDO::FETCH_ASSOC);
                $sjdj = mt_rand(0, count($retyp) - 1);
                $ypname = $retyp[$sjdj]['ypname'];
                $ypid = $retyp[$sjdj]['ypid'];
                $ypsum = mt_rand(1, 2);
                $game->yp_add($sid , $ypid , $ypsum , $dblj);
                $huode .= "获得:<div class='ypys'>$ypname x$ypsum</div>";
            }

            $guaiwu->gexp = round($guaiwu->gexp / ($lvc+1),0);//经验计算
            if($guaiwu->gexp < 3){
                $guaiwu->gexp = 3;
            }
            $zdjg = 1;
        }
        $pzssh = $phurt - $pvexx;

        $sql = "update game1 set uhp = uhp - $pzssh  WHERE sid = '$sid'";
        $dblj->exec($sql);
        $player =  player\getplayer($sid,$dblj);
        if ($player->uhp > $player->umaxhp){
            $sql = "update game1 set uhp = $player->umaxhp WHERE sid = '$sid'";
            $dblj->exec($sql);
            $player->uhp = $player->umaxhp;
        }
        if ($player->uhp <= 0){
            $zdjg = 0;
        }

        break;
}

if ($player->yp1!=0){
    $game->yp_get_info($player->yp1,$sid);
    $ypname1 = "$yaopin->ypname($yaopin->ypsum)";
}
if ($player->yp2!=0){
    $yaopin = $game->yp_get_info($player->yp2 , $sid );

    $ypname2 = "$yaopin->ypname($yaopin->ypsum)";
}
if ($player->yp3!=0){
    $yaopin = $game->yp_get_info($player->yp3 , $sid );
    $ypname3 = "$yaopin->ypname($yaopin->ypsum)";
}

if ($player->jn1!=0){
    $jineng = \player\getplayerjineng($player->jn1,$sid,$dblj);
    if ($jineng){
        $jnname1 = "$jineng->jnname($jineng->jncount)";
    }
}
if ($player->jn2!=0){
    $jineng = \player\getplayerjineng($player->jn2,$sid,$dblj);
    if ($jineng){
        $jnname2 = "$jineng->jnname($jineng->jncount)";
    }
}
if ($player->jn3!=0){
    $jineng = \player\getplayerjineng($player->jn3,$sid,$dblj);;
    if ($jineng){
        $jnname3 = "$jineng->jnname($jineng->jncount)";
    }
}

if (isset($zdjg)){
    switch ($zdjg){
        case 1:

            player\changeexp($sid,$dblj,$guaiwu->gexp);
            $huode.='获得修为:'.$guaiwu->gexp.'<br/>';


            break;
        case 0:

            break;
        case -1:
            $html = <<<HTML
            战斗结果:<br/>
            你已经重伤，无法再次进行战斗！<br/>
            请少侠恢复之后重来<br/>
            <br/>
            <a href="$gorehpmid">返回游戏</a>
HTML;
            break;
    }
}else{
    if (isset($gphurt) && $gphurt>0){
        $ghurt='-'.$gphurt;
    }else{
        $ghurt='';
    }
    if (isset($cwhurt) && $cwhurt>0){
        $cwhurt='-'.$cwhurt;
    }else{
        $cwhurt='';
    }
    if (isset($phurt) && $phurt>0){
        $phurt='-'.$phurt;
    }else{
        $phurt='';
    }

    if ($pvexx>0){
        $pvexx="(+".$pvexx.')';
    }else{
        $pvexx = '';
    }

//    if ($player->cw!=0){
//        $cw = \player\getchongwu($player->cw,$dblj);
//        if ($cwhurt!='' || $cw->cwhp>0){
//            $cwhtml=<<<HTML
//            ===============<br/>
//            宠物:$cw->cwname[lv:$cw->cwlv]<br/>
//            气血:(<div class="hpys" style="display: inline">$cw->cwhp</div>/<div class="hpys" style="display: inline">$cw->cwmaxhp</div>)$cwhurt<br/>
//            攻击:($cw->cwgj)<br/>
//            防御:($cw->cwfy)<br/>
//HTML;
//        }
//
//    }



$html = <<<HTML
==战斗==<br/>
$guaiwu->gname [lv:$guaiwu->glv]<br/>
气血:(<div class="hpys" style="display: inline">$guaiwu->ghp</div>/<div class="hpys" style="display: inline">$guaiwu->gmaxhp</div>)$pvebj$ghurt<br/>
攻击:($guaiwu->ggj)<br/>
防御:($guaiwu->gfy)<br/>
===================<br/>
$player->uname [lv:$player->ulv]<br/>
气血:(<div class="hpys" style="display: inline">$player->uhp</div>/<div class="hpys" style="display: inline">$player->umaxhp</div>)$phurt$pvexx<br/>
攻击:($player->ugj)<br/>
防御:($player->ufy)<br/>
$tishihtml
<br/>
<ul>
<li><a href="$gonowmid">逃跑</a></li><br/>
<li><a href="$pgjcmd">攻击</a></li>
</ul>
<a href="$usejn1">$jnname1</a>.<a href="$usejn2">$jnname2</a>.<a href="$usejn3">$jnname3</a><br/>
<a href="$useyp1">$ypname1</a>.<a href="$useyp2">$ypname2</a>.<a href="$useyp3">$ypname3</a><br/>
<br/>
HTML;


}
echo $html;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/11
 * Time: 12:09
 */
?>