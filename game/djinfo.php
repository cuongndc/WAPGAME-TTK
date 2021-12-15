<?php

$djid = $arr_data->djid;
$ydaoju = $goods->get_goods_info($djid);
$daoju = $goods->get_player_goods_info($djid, $player_info->sid);
// $chushou = $game->create_url("cmd=djinfo&cmd2=djinfo&djid=$djid");
$djhtml = '';
// var_dump($ydaoju,$daoju);
if ($daoju) {
    $self = $_SERVER['PHP_SELF'];
    $djhtml = <<<HTML
    <br/>
    <form action="$self">
    <input type="hidden" name="cmd" value="djinfo">
    <input type="hidden" name="canshu" value="chushou">
    <input type="hidden" name="djid" value="$djid">
    出售数量：<br/>
    <input type="number" name="djcount"><br/>
    出售单价：<br/>
    <input type="number" name="pay">
    <input type="submit" value="出售">
    </form>
HTML;
} 
if (!$daoju) {
    $daoju->number = 0;
} 

$outhtml = "道具名称：{$daoju->name}<br/>
道具数量:$daoju->djsum
道具价格：{$ydaoju->djyxb}灵石<br/>
道具说明：<br/>
{$daoju->desc}
<br/>
{$djhtml}
<br/>
{$变量_系统->链接_返回游戏_按钮长}<br/>";

echo $outhtml;

