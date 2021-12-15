<?php
$变量_系统 = (object)[
"游戏名称" => $game_name,
"颜色_红色" => '<span id="ys_red">' ,
"颜色_绿色" => '<span id="ys_green">',
"颜色_颜色完" => "</span>",
"界面_底部开始" => '<div class="fixedbottom"><footer class="h5ui-bar bar-fixed"><div class="h5ui-page spacing-cell">',
"界面_底部结束" => '</div></footer></div>',
"界面_块开始" => "<div class='h5ui-grid clearfix'>",
"界面_块结束" => "</div>",
"界面_框架开始" => "<div class='h5ui-form'>",
"界面_框架结束" => "</div>",
'界面_空行' => " ",
"链接_返回首页" => $sys->create_url('cmd=event&cmd2=event_land', '返回首页'),
"链接_返回游戏_按钮短" => $sys->create_url_nowmid(1),
"链接_返回游戏_链接" => $sys->create_url_nowmid(2),
"链接_返回游戏_块" => $sys->create_url_nowmid(3),
"链接_返回游戏_按钮长" => $sys->create_url_nowmid(4),
'链接_回复活点' => $sys->create_url_goremid(),
'链接_查看地图' => $sys->create_url("cmd=allmap&cmd2=allmap", "所有地图"),
'链接_符箓' => $sys->create_url("cmd=getbagjn", "技能"),
'链接_排行' => $sys->create_url("cmd=paihang", "排行"),
'链接_修炼' => $sys->create_url("cmd=xiulian&cmd2=xiulian", "修炼"),
'链接_坊市' => $sys->create_url("cmd=fangshi_zb", "坊市"),
'链接_门派' => $sys->create_url("cmd=club", "门派"),
'链接_兑换码' => $sys->create_url("cmd=duihuan", "兑换码"),
'链接_宠物' => $sys->create_url("cmd=chongwu", "宠物"),
'任务_数量' => count($task->rw_get_player_wwc($player_info->sid)),
'装备1位置' => $sys->get_system_config("游戏", "装备1"),
'装备2位置' => $sys->get_system_config("游戏", "装备2"),
'装备3位置' => $sys->get_system_config("游戏", "装备3"),
'装备4位置' => $sys->get_system_config("游戏", "装备4"),
'装备5位置' => $sys->get_system_config("游戏", "装备5"),
'装备6位置' => $sys->get_system_config("游戏", "装备6"),

"链接_装备_按钮短" => $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 1),
"链接_道具_按钮短" => $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 1),
"链接_药品_按钮短" => $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 1),
"链接_技能_按钮短" => $sys->create_url("cmd=bagjn&cmd2=bagyp", "技能", 1),

"链接_装备_按钮长" => $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 4),
"链接_道具_按钮长" => $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 4),
"链接_药品_按钮长" => $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 4),
"链接_技能_按钮长" => $sys->create_url("cmd=bagjn&cmd2=bagyp", "技能", 4),

"链接_装备_链接" => $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 2),
"链接_道具_链接" => $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 2),
"链接_药品_链接" => $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 2),
"链接_技能_链接" => $sys->create_url("cmd=bagjn&cmd2=bagyp", "技能", 2),

"链接_装备_块" => $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 3),
"链接_道具_块" => $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 3),
"链接_药品_块" => $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 3),
"链接_技能_块" => $sys->create_url("cmd=bagjn&cmd2=bagyp", "技能", 3),

];

$sys_val = $变量_系统;
?>