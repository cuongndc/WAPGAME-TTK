<?php
$gonowmid = $sys->create_url_nowmid();

$链接_装备 = $sys->create_url( "cmd=bagzb&cmd2=bagzb","装备",3);
$链接_道具 = $sys->create_url( "cmd=bagdj&cmd2=bagdj","道具",3);
$链接_药品 = $sys->create_url( "cmd=bagyp&cmd2=bagyp","药品",3);
$链接_技能 = $sys->create_url( "cmd=bagjn&cmd2=bagjn","技能",3);


$yaopin = $goods->get_player_goods($player_info->sid,'consume');
$allyp= '';
$suoyin = 0;
if ($yaopin){
    foreach ($yaopin as $yp){
        if ($yp->number > 0){
            $suoyin += 1;
            $ypcmd = $sys->create_url("cmd=ypinfo&cmd2=player&goodsid={$yp->id}&nowmid={$player_info->nowmid}","{$yp->name}x{$yp->number}").'<br>';
            $allyp .= $ypcmd;
        }
    }
}


$变量_药品列表 = (object)[
    '药品列表' => $allyp
];


$dis_mid = $dis->dis_get('排版_药品列表');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);
echo $out_html;
?>