<?php
$player = $game->player_get_uinfo();
$zhuangbei = $game->zb_get_info_sys($zbid);

$html = "
装备名称:$zhuangbei->zbname<br/>
装备攻击:$zhuangbei->zbgj<br/>
装备防御:$zhuangbei->zbfy<br/>
增加气血:$zhuangbei->zbhp<br/>
装备暴击:$zhuangbei->zbbj%<br/>
装备吸血:$zhuangbei->zbxx%<br/>
装备信息:$zhuangbei->zbinfo<br/><br/>

<br/>
{$变量_系统->链接_返回游戏}
";

echo $html;
?>