<?php
$getbagzbcmd = $sys->create_url("cmd=bagzb");
$getbagdjcmd = $sys->create_url("cmd=bagdj");
$getbagypcmd = $sys->create_url("cmd=bagyp");

$链接_装备 = $sys->create_url("cmd=bagzb&cmd2=bagzb", "装备", 3);
$链接_道具 = $sys->create_url("cmd=bagdj&cmd2=bagdj", "道具", 3);
$链接_药品 = $sys->create_url("cmd=bagyp&cmd2=bagyp", "药品", 3);
$链接_技能 = $sys->create_url("cmd=bagjn&cmd2=bagjn", "技能", 3);

$jineng = $skill->get_player_skill($player_info->sid);

$alljn = '';
$suoyin = 0;
if (is_array($jineng)) {
    foreach ($jineng as $jn) {
        if (is_object($jn)) {
            $suoyin += 1;
            $jnid = $jn->id;
            $jnname = $jn->name;
            $jnlvl = intval($jn->lvl);
            $alljn .= $sys->create_url("cmd=jninfo&jnid=$jnid", "{$suoyin}.{$jnname} ({$jnlvl}级)").'<br>';
        } 
    } 
} 
$变量_技能列表 = (object)[
'技能列表' => $alljn
];

$dis_mid = $dis->dis_get('排版_技能列表');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("</div><br/>", "</div>", $out_html);
echo $out_html;

?>