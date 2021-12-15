<?php

$equip_id = $arr_data->id;

$player->set_player_ut($player_info->sid, 'daoju', $equip_id, 'add');
if (isset($_POST['canshu'])) {
    $canshu = $_POST['canshu'];
    if ($canshu == 'guashou') {
        // $game->zb_update_user_sid( $zbnowid , "");
    } 
} 

$equip_info = $goods->get_player_goods_info($equip_id, $player_info->sid);
$equip_sys = $goods->get_goods_info($equip_info->initial_id);
$equip_info = G_convertObjectClass($equip_sys, $equip_info);

$强化需要游戏币 = round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * (round($zhuangbei->qianghua / 4)) + 1;
$强化道具数量 = '';
$链接_穿戴装备 = '';
$链接_强化攻击 = '';
$链接_强化防御 = '';
$链接_强化气血 = '';
$链接_分解装备 = '';
$upxx = '';
$装备操作提示 = '';
$qhssum = '';

$game_zb1 = $sys->get_system_config("游戏", "装备1");
$game_zb2 = $sys->get_system_config("游戏", "装备2");
$game_zb3 = $sys->get_system_config("游戏", "装备3");
$game_zb4 = $sys->get_system_config("游戏", "装备4");
$game_zb5 = $sys->get_system_config("游戏", "装备5");
$game_zb6 = $sys->get_system_config("游戏", "装备6");
$qhdjid = $sys->get_system_config("游戏", "强化道具");
$qhdaoju = $goods->get_goods_info($qhdjid);

$cmd2 = $arr_data->cmd2;

if ($player->uid == $zhuangbei->uid) {
    switch ($cmd2) {
        case 'usezb':
            $bool = $goods->use_player_equip($player_info->sid , $arr_data->id,$arr_data->clas);
            if ($bool) {
                $usval = json_decode($player_info->us_val);
            } else {
                echo "你翻遍了包裹，却没有找到想要使用的这件装备！<br>";
            } 
            break;
        case 'nonuse':
            $bool = $goods->remove_player_equip($player_info->sid , $arr_data->id,$arr_data->clas);
            if ($bool) {
                $usval = json_decode($player_info->us_val);
            } 
            break;
        case 'upzb':
            if ($player->uyxb < $强化需要游戏币) {
                $装备操作提示 = "强化失败，灵石不足<br/>";
                break;
            } 

            $ret = $sys->zb_sx_up($zbnowid, $zbsx);
            if ($ret != -1) {
                $retyxb = $sys->yxb_change(2, $强化需要游戏币);
                if ($ret == 1) {
                    $装备操作提示 = "恭喜强化成功<br/>";
                } elseif ($ret == 0) {
                    $装备操作提示 = "强化失败，请攒积人品<br/>";
                } 

                $zhuangbei = $goods->get_player_goods_info($zbnowid, $player_info->sid);
            } else {
                $装备操作提示 = "强化失败，强化石不足<br/>";
            } 
            break;
    } 
	$equip_info = $goods->get_player_goods_info($equip_id, $player_info->sid);
	$equip_sys = $goods->get_goods_info($equip_info->initial_id);
	$equip_info = G_convertObjectClass($equip_sys, $equip_info);

    $链接_强化攻击 = $sys->create_url("cmd=zbinfo&cmd2=upzb&zbsx=zbgj&zbnowid=$zhuangbei->zbnowid", "强化攻击");
    $链接_强化防御 = $sys->create_url("cmd=zbinfo&cmd2=upzb&zbsx=zbfy&zbnowid=$zhuangbei->zbnowid", "强化防御");
    $链接_强化气血 = $sys->create_url("cmd=zbinfo&cmd2=upzb&zbsx=zbhp&zbnowid=$zhuangbei->zbnowid", "强化气血");

    $daoju = $goods->get_player_goods_info($zbnowid, $player_info->sid);
    $强化道具数量 = $daoju->djsum; 
    // $upbj ="<a href='$upbj'>强化暴击</a>";
    // $upxx ="<a href='$upxx'>强化吸血</a>";
} else {
    $uyxb = '';
} 

$链接_分解装备 = $sys->create_url("cmd=zbinfo&cmd2=delezb&zbnowid=$zhuangbei->zbnowid", "分解装备");
$self = $_SERVER['PHP_SELF'];
$链接_穿戴装备 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool={$zhuangbei->zbtool}&zbid=$zhuangbei->zbnowid", "穿戴装备");

if ($zhuangbei->zbtool == 0) {
    $setzbwz1 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=1&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb1}】位置");
    $setzbwz2 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=2&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb2}】位置");
    $setzbwz3 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=3&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb3}】位置");
    $setzbwz4 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=4&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb4}】位置");
    $setzbwz5 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=5&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb5}】位置");
    $setzbwz6 = $sys->create_url("cmd=zhuangtai&cmd2=setzb&tool=6&zbid=$zhuangbei->zbnowid", "装备在【{$game_zb6}】位置");
    $setzbwz = "{$setzbwz1}{$setzbwz2}<br/>{$setzbwz3}{$setzbwz4}<br/>{$setzbwz5}{$setzbwz6}<br/>";
} 
// $game->create_url("cmd=bagzb&cmd=chushou");
// $setzbwz .="
// <br/>
// {$分解装备}
// <br/>
// <form method='post'>
// <input type='hidden' name='c' value='$cc'>
// <input type='hidden' name='canshu' value='guashou'>
// <input type='hidden' name='zbnowid' value='$zhuangbei->zbnowid'>
// 挂售单价：<br/>
// <input type='number' name='pay'>
// <input type='submit' value='挂售'>
// </form>";
$强化需要道具数量 = $zhuangbei->qianghua * 3 + 1;
$强化需要游戏币 = round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * (round($zhuangbei->qianghua / 4)) + 1;
$分解所需游戏币 = $zhuangbei->qianghua * 20 + 20;

$强化成功率 = round((30 - $zhuangbei->qianghua) / 30, 2) * 100;

$tools = array("不限定" , $game_zb1 , $game_zb2 , $game_zb3 , $game_zb4 , $game_zb5 , $game_zb6);
$装备位置 = $tools[$zhuangbei->zbtool];

$变量_装备信息 = (object)array("装备位置" => $装备位置,
    "链接_强化攻击" => $链接_强化攻击,
    "链接_强化防御" => $链接_强化防御,
    "链接_强化气血" => $链接_强化气血,
    "链接_穿戴装备" => $链接_穿戴装备,
    "链接_分解装备" => $链接_分解装备,
    "强化成功率" => $强化成功率,
    "装备操作提示" => $装备操作提示,
    "分解所需游戏币" => $分解所需游戏币,
    "强化需要游戏币" => $强化需要游戏币,
    "强化道具数量" => $强化道具数量,
    "强化需要道具数量" => $强化需要道具数量
    );

if ($equip_info->in_use == 1) {
    $Operands .= $sys->create_url("cmd=zbinfo&cmd2=nonuse&clas=weapon&tool=0&id={$equip_info->id}", "卸下");
} else {
    $Operands .= $sys->create_url("cmd=zbinfo&cmd2=usezb&id={$equip_info->id}&clas={$equip_info->clas}", "使用");
} 

$Operands .= $sys->create_url("cmd=bagzb&cmd2=bagzb&canshu=maichu&yeshu=$yeshu&id=$id", "丢弃");
$Operands .= $sys->create_url("cmd=bagzb&cmd2=bagzb&canshu=maichu&yeshu=$yeshu&id=$id", "丢弃全部");

$dis_mid = $dis->dis_get('dis_good');
if (!is_object($dis_mid)) {
    $dis_mid = $dis->dis_get('排版_装备信息');
    eval("\$out_html = \"$dis_mid->dis_string\";");
} else {
    $out_html = '<p>' . $dis->dis_decode(json_decode($dis_mid->dis_string), $player_info, $equip_info) . '</p>';
} 
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("</div><br/>", "</div>", $out_html);
echo $out_html;

?>