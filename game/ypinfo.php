<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/26
 * Time: 15:57
 */
 
$ypid = $arr_data->goodsid;

 
$yphp = '';
$ypgj = '';
$ypfy = '';
$ypbj = '';
$ypxx = '';

$gonowmid = $sys->create_url_nowmid();
$playeryp = $goods->get_player_goods_info($ypid,$player_info->sid);
$yaopin = $goods->get_goods_info($playeryp->initial_id);
$yaopin =json_decode($playeryp->content);
$yaopin = G_convertObjectClass($yaopin,$playeryp);
//var_dump($yaopin);
$setyp = '';
$tishi='';

//var_dump($playeryp,$yaopin);
switch ($arr_data->cmd2){
    case 'setyp':
        $game->player_update_ypwz($canshu , $ypid );
        $tishi = "设置药品{$canshu}成功<br/>";
    break;
    case 'useyp':
        $ret = $goods->use_player_consume($player_info->sid, $ypid , 1);
        if ($ret){
            $tishi = "使用药品成功<br/>";
        }else{
            $tishi = "使用药品失败<br/>";
        }
    break;
}

if ($playeryp){
    $setyp1 = $sys->create_url( "cmd=ypinfo&cmd2=setyp&canshu=1&goodsid=$ypid","装备药品1");
    $setyp2 = $sys->create_url("cmd=ypinfo&cmd2=setyp&canshu=2&goodsid=$ypid","装备药品2");
    $setyp3 = $sys->create_url("cmd=ypinfo&cmd2=setyp&canshu=3&goodsid=$ypid","装备药品3");
    $useyp = $sys->create_url("cmd=ypinfo&cmd2=useyp&goodsid=$ypid","使用");
    $setyp ="
    {$setyp1}
    {$setyp2}
    {$setyp3}
    {$useyp}";
}
if($yaopin->hp!=0){
    $yphp = "气血$yaopin->yphp<br/>";
}

if ($yaopin->gj!=0){
    $ypgj = "攻击$yaopin->ypgj<br/>";
}

if ($yaopin->fy!=0){
    $ypfy = "防御$yaopin->ypfy<br/>";
}

if ($yaopin->bj!=0){
    $ypbj = "暴击$yaopin->ypbj<br/>";
}

if ($yaopin->xx!=0){
    $ypxx = "吸血$yaopin->ypxx<br/>";
}

$ypsx = "<br/>".$yphp.$ypgj.$ypfy.$ypbj.$ypxx;
$ypinfo = <<<HTML
$tishi
[$yaopin->name]*{$playeryp->number}<br/>
$yaopin->desc
$ypsx
$setyp
<br/>
{$变量_系统->链接_返回游戏_按钮长}
HTML;
echo $ypinfo;