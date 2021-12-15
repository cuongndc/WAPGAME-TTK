<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/17
 * Time: 16:01
 */
if (isset($cmd2)) {
    $getyxb = 0;

    switch ($cmd2) {
        case 'sell':
            $ret = $game->dj_sub($djid, $canshu);
            if ($ret) {
                $daoju = $game->dj_get_sys($djid);
                $game->yxb_change(1, $daoju->djyxb * $canshu);
                $getyxb = $daoju->djyxb * $canshu;
                echo "卖出成功，获得{$getyxb}灵石<br/>";
            } 

            break;
    } 
} 

$retdj = $goods->get_player_goods($player_info->sid, 'goods');

$道具列表 = '';
// var_dump($retdj);
if ($retdj) {
    $hangshu = 0;
    for ($i = 0;$i < count($retdj);$i++) {
        $djname = $retdj[$i]->name;
        $djid = $retdj[$i]->id;
        $djsum = $retdj[$i]->number;
        if ($djsum > 0) {
            $hangshu = $hangshu + 1;
            $chakandj = $sys->create_url("cmd=djinfo&cmd2=djinfo&djid=$djid", "{$djname}x{$djsum}");
            $maichu1 = $sys->create_url("cmd=bagdj&cmd2=sell&canshu=1&djid=$djid", "卖出*1");
            $maichu5 = $sys->create_url("cmd=bagdj&cmd2=sell&canshu=5&djid=$djid", "卖出*5");
            $maichu10 = $sys->create_url("cmd=bagdj&cmd2=sell&canshu=10&djid=$djid", "卖出*5");
            $道具列表 .= "{$chakandj}{$maichu1}{$maichu5}{$maichu10}<br/>";
        } 
    } 
} 

$变量_道具列表 = (object)array("道具列表" => $道具列表

    );

$链接_装备 = $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 3);
$链接_道具 = $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 3);
$链接_药品 = $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 3);
$链接_技能 = $sys->create_url("cmd=bagjn&cmd2=bagjn", "技能", 3);

$dis_mid = $dis->dis_get('排版_道具列表');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("</div><br/>", "</div>", $out_html);
echo $out_html;
