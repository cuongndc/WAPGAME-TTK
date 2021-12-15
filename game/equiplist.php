<?php
$usval = json_decode($player_info->us_val);
$clas = $arr_data->clas;
$tool = $arr_data->tool;
$cmd2 = $arr_data->cmd2;

switch ($cmd2) {
    case 'select':
		$bool = $goods->use_player_equip($player_info->sid ,$arr_data->id,$clas);
		if($bool){
			$usval = json_decode($player_info->us_val); 
			$body = load_equip_list($usval);
		}
        break;
    case 'nonuse':
		$bool = $goods->remove_player_equip($player_info->sid ,$arr_data->id,$clas);
		if($bool){
			$usval = json_decode($player_info->us_val); 
			$body = load_equip_list($usval);
		}
        break;
    case 'inuse':
        $retzb = $goods->get_player_goods($player_info->sid, 'equip', $clas, $tool);
        if (count($retzb) == 0) {
            $title = "你身上没有这个类型的装备！<br>";
            $body = load_equip_list($usval);
        } else {
            $title = "请选择要使用的兵器：<br>";
            $body = load_bags_equip($retzb);
        } 
        break;
    default:
        $body = load_equip_list($usval);
} 

echo "
【我的装备】<br>
 {$title}
 {$body}
 {$变量_系统->链接_返回游戏_按钮短}
";

function load_bags_equip($retzb) {
    global $sys;
    global $player_info;
    foreach($retzb as $obj) {
        $id = $obj->id;
        $html .= $sys->create_url("cmd=equiplist&cmd2=select&type={$obj->type}&clas={$obj->clas}&tool={$obj->tool}&id={$obj->id}&uid=$player_info->uid", $obj->name) . '<br>';
    } 
    $html .= '=====================<br>' . $sys->create_url("cmd=equiplist", '返回上级') . '<br>';
    return $html;
} 

function load_equip_list($usval) {
    global $sys;
    global $goods;
    global $player_info;
    $html = '兵器：';
    $attack_value = 0;
    if (isset($usval->weapon)) {
        $weapon_info = $goods->get_player_goods_info($usval->weapon->val, $player_info->sid);
        if (isset($weapon_info)) {
            $attack_value = $attack_value + $weapon_info->attack_value;
            $id = $weapon_info->id;
            $Operands = $sys->create_url("cmd=zbinfo&cmd2=zbinfo&id=$id&uid=$player_info->uid", $weapon_info->name);
			$Operands .= $sys->create_url("cmd=equiplist&cmd2=nonuse&clas=weapon&tool=0&id={$id}", "卸下");
            $html .= $Operands;
        } else {
            $html .= $sys->create_url("cmd=equiplist&cmd2=inuse&clas=weapon&tool=0", "使用");
        } 
    } else {
        $html .= $sys->create_url("cmd=equiplist&cmd2=inuse&clas=weapon&tool=0", "使用");
    } 
    $html .= "<br>";
    $equip = json_decode($sys->get_system_config('system', 'equip_class'));
    $resist_value = 0;
    foreach ($equip as $obj) {
        if (is_object($obj)) {
            $equip_id = $obj->id;
            $html .= "{$obj->name}：";
            $val = "equip" . $equip_id;
            if (isset($usval->$val)) {
                $equip_info = $goods->get_player_goods_info($usval->$val->val, $player_info->sid);
                if (isset($equip_info)) {
                    $resist_value = $resist_value + $equip_info->resist_value;
                    $id = $equip_info->id;
                    $Operands = $sys->create_url("cmd=zbinfo&cmd2=zbinfo&id=$id&uid=$player_info->uid", $equip_info->name);
                    $Operands .= $sys->create_url("cmd=equiplist&cmd2=nonuse&clas=equip&tool={$obj->id}&id={$id}", "卸下");
                    $html .= $Operands;
                } else {
                    $html .= $sys->create_url("cmd=equiplist&cmd2=inuse&clas=equip&tool={$obj->id}", "使用");
                } 
            } else {
                $html .= $sys->create_url("cmd=equiplist&cmd2=inuse&clas=equip&tool={$obj->id}", "使用");
            } 
            $html .= "<br>";
        } 
    } 
    $html .= "----------<br>
兵器总攻击力:{$attack_value}<br>
防具总防御力:{$resist_value}<br>
----------<br>";
    $html .= $sys->create_url("cmd=zhuangtai&cmd2=zhuangtai", '我的状态');
    $html .= '<br>';
    $html .= $sys->create_url('cmd=bagzb&cmd2=bagzb', '我的物品');
    $html .= '<br><br>';

    return $html ;
} 

?>