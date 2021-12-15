<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/30 0030
 * Time: 19:32
 */

if (isset($canshu)){
    if ($canshu=="deim"){
        $sql="delete from im WHERE imuid = $uid AND sid='$sid'";
        $dblj->exec($sql);
    }
}

$sql="select * from im WHERE sid='$sid'";
$ret = $dblj->query($sql);
$imitem = $ret->fetchAll(PDO::FETCH_ASSOC);
$gonowmid = $sys->create_url("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$imlist = '';
foreach ($imitem as $im){
    $imuid = $im['imuid'];
    $implayer = \player\getplayer1($imuid,$dblj);
    $playercmd = $sys->create_url("cmd=getplayerinfo&uid=$imuid&sid=$sid");
    $imlist .="<a href='?cmd=$playercmd'>$implayer->uname</a><br/>";
}
$imhtml =<<<HTML
=======好友=======<br/>
$imlist
<br/>
{$变量_系统->链接_返回游戏_按钮短}
HTML;
echo $imhtml;