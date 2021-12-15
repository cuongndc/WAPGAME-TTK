<?php 
// var_dump($arr_data->cmd2,$arr_data->Quick);
$type = $arr_data->cmd2;
$Quick_id = $arr_data->Quick;

if (isset($type)) {
    echo $Quick_id . '<br>';
    switch ($type) {
        case 'set':
            echo load_skill($type);
            $exit = true;
            break;
        case 'edit':
            echo load_skill($type);
            $exit = true;
            break;
        case 'skill':
            echo load_skill($type);
            $exit = true;
            break;
        case 'consume':
            echo load_consume($type);
            $exit = true;
            break;
        case 'equip':
            echo load_equip($type);
            $exit = true;
            break;
        case 'other':
            echo load_other($type);
            $exit = true;
            break;
        case 'add':
            echo save_Quick($arr_data->type, $arr_data->id, $arr_data->order, $player_info->sid);
            break;
    } 

    if ($exit) {
        echo $sys->create_url("cmd=Quick_battle_set", "返回游戏");
        exit;
    } 
} 

echo "【战斗快捷键设置】<br>";
echo load_Quick_battle_set('设置快捷键', $player_info);

echo $变量_系统->链接_返回游戏_按钮短;

function save_Quick($type, $id, $order, $sid) {
    global $skill;
    global $goods;
    global $player;
    global $Quick_id;
    global $player_info;
    switch ($type) {
        case 'skill':
            $obj = $skill->get_player_skill_info($id, $sid);
            break;
        case 'consume':
        case 'equip':
            $obj = $goods->get_player_goods_info($id , $sid);
            break;
        case 'other':

            break;
    } 
    $Quick_battle = (object)[];
    $Quick_battle->name = $obj->name;
    $Quick_battle->type = $type;
    $Quick_battle->val = $obj->id;
    $ret = $player->set_player_us($sid, 'Quick_battle_' . $Quick_id, $Quick_battle);
    if ($ret) {
        return "快捷键设置成功！<br>";
    } 
} 

function load_Quick_battle_set($name = "", $player_info) { // 加载快捷键设置信息
    global $player;
    global $sys;
    if (isset($player_info)) {
        $us = $player->get_player_us($player_info->sid);
    } ;
    $html .= '战斗快捷1：';	
    if (isset($us->Quick_battle_1)) {
        $Quick_battle = json_decode($us->Quick_battle_1)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=1", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=1", $name, 1);
    } ;
    $html .= '<br>战斗快捷2：';
    if (isset($us->Quick_battle_2)) {
        $Quick_battle = json_decode($us->Quick_battle_2)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=2", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=2", $name, 1);
    } ;
    $html .= '<br>战斗快捷3：';
    if (isset($us->Quick_battle_3)) {
        $Quick_battle = json_decode($us->Quick_battle_3)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=3", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=3", $name, 1);
    } ;
    $html .= '<br>战斗快捷4：';
    if (isset($us->Quick_battle_4)) {
        $Quick_battle =  json_decode($us->Quick_battle_4)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=4", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=4", $name, 1);
    } ;
    $html .= '<br>战斗快捷5：';
    if (isset($us->Quick_battle_5)) {
        $Quick_battle = json_decode($us->Quick_battle_5)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=5", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=5", $name, 1);
    } ;
    $html .= '<br>战斗快捷6：';
    if (isset($us->Quick_battle_6)) {
        $Quick_battle = json_decode($us->Quick_battle_6)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=6", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=6", $name, 1);
    } ;
    $html .= '<br>战斗快捷7：';
    if (isset($us->Quick_battle_7)) {
        $Quick_battle = json_decode($us->Quick_battle_7)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=7", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=7", $name, 1);
    } ;
    $html .= '<br>战斗快捷8：';
    if (isset($us->Quick_battle_8)) {
        $Quick_battle = json_decode($us->Quick_battle_8)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=8", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=8", $name, 1);
    } ;
    $html .= '<br>战斗快捷9：';
    if (isset($us->Quick_battle_9)) {
        $Quick_battle = json_decode($us->Quick_battle_9)->val;
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=edit&Quick=9", $Quick_battle->name, 1);
    } else {
        $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=set&Quick=9", $name, 1);
    } ;
    $html .= '<br>';
    return $html;
} 

function load_skill($type) {
    global $sys;
    global $skill;
    global $Quick_id;
    global $player_info;
    $html .= Load_page_header($type, $Quick_id);
    $jineng = $skill->get_player_skill($player_info->sid);
    $suoyin = 0;
    if (is_array($jineng)) {
        foreach ($jineng as $jn) {
            if (is_object($jn)) {
                $suoyin += 1;
                $jnid = $jn->id;
                $jnname = $jn->name;
                $jnlvl = intval($jn->lvl);
                $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=add&order={$type}&Quick={$Quick_id}&type=skill&id={$jnid}", "{$suoyin}.{$jnname} ({$jnlvl}级)") . '<br>';
            } 
        } 
    } 
    return $html . '<br>';
} 

function load_equip($type) {
    global $sys;
    global $goods;
    global $Quick_id;
    global $player_info;
    $html .= Load_page_header($type, $Quick_id);
    $retzb = $goods->get_player_goods($player_info->sid, 'equip');
    if ($retzb) {
        foreach ($retzb as $equip) {
            if (is_object($equip)) {
                $id = $equip->id;
                $hangshu = $hangshu + 1;
                $zbname = $equip->name;
                $zbqh = $equip->lvl;
                $qhhtml = '';
                if ($zbqh > 0) {
                    $qhhtml = "+" . $zbqh;
                } 
                $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=add&order={$type}&Quick={$Quick_id}&type=equip&id={$id}", "{$zbname}{$qhhtml}");
            } 
        } 
    } 
    return $html . '<br>';
} 

function load_consume($type) {
    global $sys;
    global $goods;
    global $Quick_id;
    global $player_info;
    $html .= Load_page_header($type, $Quick_id);
    $yaopin = $goods->get_player_goods($player_info->sid, 'consume');
    $suoyin = 0;
    if ($yaopin) {
        foreach ($yaopin as $yp) {
            if ($yp->number > 0) {
                $suoyin += 1;
                $html .= $sys->create_url("cmd=Quick_battle_set&cmd2=add&order={$type}&Quick={$Quick_id}&type=consume&id={$yp->id}", "{$yp->name}x{$yp->number}");
            } 
        } 
    } 
    return $html . '<br>';
} 

function load_other($type) {
    global $sys;
    global $goods;
    global $Quick_id;
    global $player_info;
    $html .= Load_page_header($type, $Quick_id);
    $yaopin = $goods->get_player_goods($player_info->sid, 'consume');
    $suoyin = 0;
    if ($yaopin) {
        foreach ($yaopin as $yp) {
            if ($yp->number > 0) {
                $suoyin += 1;
                $html .= $sys->create_url("cmd=ypinfo&cmd2=player&goodsid={$yp->id}&nowmid={$player_info->nowmid}", "{$yp->name}x{$yp->number}");
            } 
        } 
    } 
    return $html . '<br>';
} 

function Load_page_header($type, $Quick_id) {
	global $sys;
    $url = "cmd=Quick_battle_set&order={$type}&Quick={$Quick_id}&cmd2=";
    $html .= $sys->create_url($url . 'skill', "技能");
    $html .= $sys->create_url($url . 'consume', "药品");
    $html .= $sys->create_url($url . 'equip', "道具");
    $html .= $sys->create_url($url . 'other', "其他");
    $html .= '<br>';
    return $html;
} 

?>

