<?php
$gid = isset($arr_data->gid)?$arr_data->gid:$player_info->enemy;
$cmd2 = isset($arr_data->cmd2)?$arr_data->cmd2:"intopve";
$Quick = $arr_data->Quick;
$guaiwu = $npc->get_npc_run($gid);
$player->set_player_ut($player_info->sid, 'cut_hp');
$player->set_player_ut($player_info->sid, 'cut_mp');
$npc->set_npc_ut($gid, 'cut_hp');
$npc->set_npc_ut($gid, 'cut_mp');

$skill_config = json_decode($sys->get_system_config('system', 'skill_config'));

if (!$guaiwu) {
    $html = "
        怪物已经被其他人攻击了！<br/>
        请少侠练习一下手速哦！！
        <br/>
        {$变量_系统->链接_返回游戏_按钮短}
		";
    exit($html);
} 

switch ($cmd2) {
    case 'intopve'://进入战斗页面
        if ($player_info->lvl < 10) {
            $player->player_re_hp();
            $player_info = $player->get_player_info();
        }
        if ($player_info->hp > 1) {
			$player->set_player_field("enemy", $gid , $player_info->sid, "up");
            $npc->gw_set_sid($gid);
            $html = create_pve_info($player_info , $guaiwu , 0 , 0 , 0 , 0 , "普通攻击" , $变量_系统);
        } else {
            $html = create_pve_cannot($变量_系统);
        } 
        break;
    case 'use':
        $angel_slick = (object)[];
        switch ($arr_data->type) {
            case 'skill':
                player_attack_event($Quick,  $player_info , $guaiwu, $Player_Attack_Tips, $Enemy_Attack_Tips); 
                // $angel_slick->xx_player = 5;
                $angel_slick->phurt = $phurt;
				//var_dump($guaiwu);
                npc_attack_event($angel_slick, $player_info, $guaiwu, $Player_Attack_Tips, $Enemy_Attack_Tips);
                var_dump($Player_Attack_Tips, $Enemy_Attack_Tips);
				if ($player_info->hp > 0 && $guaiwu->hp <= 0) {
                    $html = create_pve_info($player_info , $guaiwu ,  $变量_系统);
                } elseif ($player_info->hp <= 0) { // 先判断玩家是否死亡
                    $html = $npc->create_pve_lose($guaiwu, $player_info->sid , $变量_系统);
                } elseif ($guaiwu->hp <= 0) { // 玩家没死再次判断怪物是否死亡
                    $html = $npc->create_pve_win($guaiwu,  $player_info->sid , $变量_系统);
                } 
                break;
            case "consume":
                $ret = $goods->use_player_consume($player_info->sid , $Quick , 1);
                $player_info = $player->get_player_info($player_info->sid);
                $html = create_pve_info($player_info , $guaiwu ,  $变量_系统);
                break;
			default:
				require_once 'Quick_battle_set.php';
        } 
        break;
} 

if(!empty($html)){
	echo $html;
}

function player_attack_event($Quick, &$p_info , &$guaiwu , &$Player_Attack_Tips , &$Enemy_Attack_Tips) {//玩家出招事件
    global $player;
    global $skill;
    global $skill_config;
    global $npc;
    global $dis;
	
	$sid = $p_info->sid;
	
	$guaiwu = $npc->get_npc_run($guaiwu->id);
	$npc_info = $npc->get_npc_info($guaiwu->gid);
	$guaiwu = G_convertObjectClass($npc_info, $guaiwu); 
	
	$hura_cost = player_use_shill($sid , $Quick , $Player_Attack_Tips);
    $Player_Attack_Tips = $dis->dis_text_decode($Player_Attack_Tips, $p_info, $guaiwu);
    $g_phurt = $dis->dis_text_decode($hura_cost,   $guaiwu , $p_info );
	var_dump("<br>g_phurt运算结果:<br>{$hura_cost}<br>{$g_phurt} <br>");
    if ($g_phurt < 0) {
        $g_phurt = 0;
        $Enemy_Attack_Tips = "{$p_info->name}躲过了{$guaiwu->name}的攻击！";
    } 
    $player->set_player_ut($sid, 'cut_hp', - $g_phurt);
	$player->player_change_uhp_sid( $g_phurt ,2,  $sid);
    $player->set_player_ut($sid, 'cut_mp', $dis->dis_text_decode($obj_info->deplete_cost, $p_info));
    $p_info = $player->get_player_info();
} 

function npc_attack_event($angel_slick, &$p_info , &$guaiwu, &$Player_Attack_Tips , &$Enemy_Attack_Tips) {//NPC出招事件
    global $npc;
    global $dis;
    global $skill;
	global $skill_config;
    global $player;

    $hura_cost = npc_use_skill($gid,$Enemy_Attack_Tips);
	// var_dump($hura_cost, $guaiwu, $p_info);
    $p_ghurt = $dis->dis_text_decode($hura_cost, $p_info, $guaiwu, null);
    if ($p_ghurt <= 0) {
        $p_ghurt = 0;
        $Player_Attack_Tips = "{$guaiwu->name}躲过了{$p_info->name}的攻击！";
    }
	var_dump("<br>p_ghurt运算结果:{$hura_cost}<br>{$p_ghurt}<br>");
    $npc->set_npc_ut($run_id, 'cut_hp', - $p_ghurt);
    $npc->set_npc_ut($run_id, 'cut_mp', $dis->dis_text_decode($obj_info->deplete_cost, $guaiwu , $p_info));
    $npc->gw_change_hp($p_ghurt , 2 , $run_id);
    $player->player_change_uhp(intval($angel_slick->phurt) - floatval($angel_slick->xx_player) , 2);
    $p_info = $player->get_player_info();
    $guaiwu = $npc->get_npc_run($run_id);
} 

function create_pve_cannot($系统变量) {//玩家重伤，无法进入战斗
    global $player;
    $player->player_go_re();
    $html = "
       <div class='tishi'>重伤请治疗！</div><br/>
        侠士已经重伤
        <br/>
        {$系统变量->链接_返回游戏_按钮短}";
    return $html;
} 

function create_pve_info($player , $guaiwu , $变量_系统) {//创建战斗页面
    global $dis;
    global $sys;
    $pbuff = "";
    $gbuff = "";
    if ($baoji_player) {
        $gbuff = "$gbuff(暴击)";
    } 
    if ($xx_player) {
        $pbuff = "$pbuff(+$xx_player)";
    } 

    $变量_打怪界面 = (object)[

    "战斗提示" => $tishi,
    "玩家状态" => $pbuff,
    "怪物状态" => $gbuff
    ];
    $out_html = "";

    $dis_mid = $dis->dis_get('dis_battle');
    if (!is_object($dis_mid)) {
        $dis_mid = $dis->dis_get('排版_打怪界面');
        eval("\$out_html = \"$dis_mid->dis_string\";");
    } else {
        $out_html = '<p>' . $dis->dis_decode(json_decode($dis_mid->dis_string), $player,$guaiwu,'') . '</p>';
    } 
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("</div><br/>", "</div>", $out_html);
    $html = $out_html;
    return $html;
} 

function npc_use_skill($gid,&$Enemy_Attack_Tips){//npc使用技能选取
    global $npc;
	global $skill;
	global $dis;
	$npc_info = $npc->get_npc_info($gid);
	 if (isset($npc_info->skills)) {
        $skill_list = json_decode($npc_info->skills);
        $skill_is = mt_rand(0, $skill_list->total);
        foreach($skill_list as $obj) {
            if (is_object($obj)) {
                $i++;
                if ($i == $skill_is) {
                    $skill_obj = $skill->get_skill_info($obj->id);
                    $skill_obj->lvl = $obj->val;
                    $Quick = (object)[];
                    $Quick->id = $skill_obj->id;
                    $Quick->lvl = $skill_obj->lvl;
                    $npc->set_npc_ut($run_id, 'Quick', json_encode($Quick)); 
                    // break;
                } 
            } 
        } 
    }
	if (!isset($skill_obj->hura_cost)) {
        $hura_cost = $skill_config->hura_cost;
    } else {
        $hura_cost = $skill_obj->hura_cost;
    } 
	    if (empty($Enemy_Attack_Tips)) {//玩家是否已躲过NPC攻击
        if (!isset($skill_obj->effect_cmmt) || $skill_obj->effect_cmmt == "") {
            $Enemy_Attack_Tips = $skill_config->effect_cmmt;
        } else {
            $Enemy_Attack_Tips = $skill_obj->effect_cmmt;
        }
        // var_dump($guaiwu );
        $Enemy_Attack_Tips = $dis->dis_text_decode($Enemy_Attack_Tips, $guaiwu, $player_info);
    } 
	return $hura_cost;
}

function player_use_shill($sid ,$skill_id,&$Player_Attack_Tips){//玩家使用技能选取
	global $player;
	global $skill;
	global $skill_config;
	$player->set_player_ut($sid , 'Quick', $skill_id);
    $p_shill = $skill->get_player_skill_info($skill_id, $sid);
    $s_skill = $skill->get_skill_info($p_shill->initial_id);
    $n_skill = G_convertObjectClass($s_skill, $p_shill);
	if (!isset($n_skill->hura_cost)) {
        $hura_cost = $skill_config->hura_cost;
    } else {
        $hura_cost = $n_skill->hura_cost;
    } 
    if (!isset($n_skill->effect_cmmt)) {
        $Player_Attack_Tips = $skill_config->effect_cmmt;
    } else {
        $Player_Attack_Tips = $n_skill->effect_cmmt;
    } 
	return  $hura_cost;
}

?>