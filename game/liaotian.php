<?php

if ( isset($_POST['cmd2']) ){
    $cmd2 = $_POST['cmd2'];
    switch ($cmd2){
        case "send_all":
            $msg = $_POST['msg'];
            $user_message->liaotian_send_all('公共',$msg , $player_info->name , $player_info->uid );
        break;
    }
}

if ($ltlx == "all"){

    $obj_liaotian_all = $game->liaotian_get_all(10);
    $lthtml='';

    if ($obj_liaotian_all){
        $goliaotian = $game->create_url("cmd=liaotian&ltlx=all","刷新");
        $imliaotian = $game->create_url("cmd=liaotian&ltlx=im","私聊");
        $lthtml = "聊天频道$goliaotian<br/>【公共|{$imliaotian}】<br/>";

        for ($i=0;$i < count($obj_liaotian_all);$i++){
            $ltObj = $obj_liaotian_all[count($obj_liaotian_all) - $i-1];
            $uname = $ltObj->name;
            $umsg = $ltObj->msg;
            $uid = $ltObj->uid;
            $date = $ltObj->date;
            $date = date_create($date);
            $date = date_format($date,'H:i:s');
            $ucmd = $game->create_url("cmd=otherzhuangtai&cmd2=playerinfo&uid=$uid","{$uname}");

            if ($uid){
                $lthtml .="[公共][$date]{$ucmd}:$umsg<br/>";
            }else{
                $lthtml .="[公共][$date]<div class='hpys' style='display: inline'>$uname:</div>$umsg<br/>";
            }

        }

    }
}

$lthtml.=<<<HTML
<form method="post">
<input type="hidden" name="c" value="$cc">
<input type="hidden" name="cmd2" value="send_all">
<input type="text" name="msg">
<input type="submit" value="发送">
</form>
HTML;

$html = <<<HTML
$lthtml
<br/>
{$变量_系统->链接_返回游戏_按钮短}
HTML;
echo $html;
