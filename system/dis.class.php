<?php 
// 用户界面排版控制信息
namespace game_system;

class dis {
    public $dblj;
    public $attribute;
    public $webm;
    public $player;

    function __construct() {
        global $dblj;
        global $attribute;
        global $webm;
        global $player;
		
        $this->dblj = $dblj;
        $this->attribute = $attribute;
        $this->webm = $webm;
        $this->player = $player;
    } 

    function dis_get($dis_name, $type = "") { // 获取排版信息
        $sql = "select * from `dis` WHERE dis_name = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($dis_name));
        $dis = $stmt->fetch(\PDO::FETCH_OBJ);
        if ($type == "text") {
            return $dis->dis_string;
        } 
        return $dis;
    } 

    function set_dis($dis_name, $dis) { // 更新排版信息
        $sql = "UPDATE `dis` SET `dis_string` = ? WHERE `dis_name` = ?;";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($dis, $dis_name));
        return $ret;
    } 

    function dis_get_all() { // 获取全部排版
        $sql = "select * from `dis`";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $dis = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $dis;
    } 

    function dis_decode($dis_info, $u_obj, $o_obj = null, $target = null, $debug = false) { // 解析布局控制文件内容
        global $sys;
        global $player_info;
        if ($dis_info != "" && is_object($dis_info)) {
            foreach($dis_info as $value) {
                if (is_object ($value)) {
                    $dis_string = str_replace(array("\r\n", "\r", "\n"), '<br>', $value->dis_string);
                    if ($dis_string != "") {
						//$debug = true;
                        $dis_string = $this->webm->start($dis_string, $u_obj, $o_obj, $target, $debug);
                        if (is_array($dis_string)) {
                            $dis_string = $dis_string['text'];
                        } 
                    }
					if(empty($value->dis_condition)){
						$bool = true;
					}else{
						$str =$value->dis_condition;
						$str = str_replace('{','v(' ,$str);
						$str = str_replace('}',')' ,$str);
						$bool = $this->dis_text_decode('{eval('.$str .')}',$u_obj,$o_obj,$target, $debug);
						if($bool == "假"){$bool = 0;}
						$bool = boolval($bool);
					}
					if($bool){					
                    switch ($value->dis_type) {
                        case 'text':// 文本元素
                            $html .= $dis_string;
                            break;
                        case 'link':// 功能元素
                            $html .= $this->link_command($value->dis_link, $value->dis_string, $player_info);
                            break;
                        case 'open':// 操作元素
							$html .= $this->load_operation_list($value->dis_open);
                            break;
                        case 'exit':// 连接元素
                            $html .= "<a href='{$value->dis_exit}'>{$dis_string}</a>";
                            break;
                        default: 
                            // var_dump( $value ,'<br>');
                    } 
					}
                } 
            } 
        } 
        return $html;
    } 

    function dis_text_decode($dis_info, $u_obj, $o_obj = null, $target = null, $debug = false) { // 解析布局控制文本内容
        global $sys;
        global $player_info;
        if ($dis_info != "") {
            $dis_string = $this->webm->trimall($dis_info);
            if ($dis_string != "" && !is_array($dis_string)) {
				//$debug = true;
                $dis_string = $this->webm->start($dis_string, $u_obj, $o_obj, $target, $debug);
				if ($this->debug) {var_dump($dis_string);}
                if (is_array($dis_string)) {
                    $dis_string = $dis_string['text'];
                } 
            } else {
                $dis_string = $dis_info;
            } 
        } 
        return $dis_string;
    } 
	
    function dis_condition_decode($dis_info, $u_obj, $o_obj = null, $target = null, $debug = false) { //解析条件控制文本内容
        global $sys;
        global $player_info;
        if ($dis_info != "") {
            $dis_string = $this->webm->trimall($dis_info);
            if ($dis_string != "" && !is_array($dis_string)) {
				//$debug = true;
                $dis_string = $this->webm->start_condition($dis_string, $u_obj, $o_obj, $target, $debug);
				if ($this->debug) {var_dump($dis_string);}
                if (is_array($dis_string)) {
                    $dis_string = $dis_string['text'];
                } 
            } else {
                $dis_string = $dis_info;
            } 
        } 
        return $dis_string;
    } 

    function link_command($val, $name = "", $player_info = null) { // 连接到游戏固定功能
        global $sys;
        global $map;
        global $npc;
        global $map_info;
        global $Operands;
        global $Player_Attack_Tips;
        global $Enemy_Attack_Tips;
        $assembly = $sys->get_assembly($val);
        if ($assembly->clas != "") {
            $clas = "class='{$assembly->clas}'";
        } 
        if ($assembly->type != "") {
            $type = "type='{$assembly->type}'";
        } 
        if ($name == "") {
            $name = $assembly->nickname;
        } 
        switch ($assembly->value) {
            case 'home':
                $html = "<a href='home_page.php' {$type} {$clas}>{$name}</a>";
                break;
            case 'pm_exit':
                $html = $map->mid_get_out($map_info);
                break;
            case 'back':
                $html = $sys->create_url_nowmid();
                break;
            case 'User_message':
                $html = $this->load_User_message(2);
                break;
            case 'Npc_list':
                $html = $npc->load_mip_npclist($map_info);
                break;
            case 'Goods_list':
                $goods_list = $map->mid_get_goods_all($map_info->id);
                $goods_count = count($goods_list);
                if ($goods_count == 0) {
                    $html = "";
                } else {
                    $html = $html = $this->load_mip_goodslist($name, $map_info, $goods_list);
                } 
                break;
            case 'my_status':
                $html = $sys->create_url("cmd=zhuangtai&cmd2=zhuangtai", $name);
                break;
            case 'my_trunk':
                $html = $sys->create_url('cmd=bagzb&cmd2=bagzb', $name);
                break;
            case 'my_skill':
                $html = $sys->create_url('cmd=bagjn&cmd2=bagjn', $name);
                break;
            case 'sys_chat':
                $html = $sys->create_url("cmd=liaotian&ltlx=all", $name);
                break;
            case 'my_friend':
                $html = $sys->create_url("cmd=im&cmd2=imlist", $name);
                break;
            case 'my_task':
                $html = $sys->create_url("cmd=player_task", $name);
                break;
            case 'my_equip':
                $html = $sys->create_url("cmd=equiplist", $name);
                break;
            case 'Operands':
                $html = $Operands;
                break;
            case 'enemy_info':
                $html = $npc->get_enemy_info($player_info->enemy);
                break;
            case 'Player_Attack_Tips':
                $html = $Player_Attack_Tips;
                break;
            case 'Enemy_Attack_Tips':
                $html = $Enemy_Attack_Tips;
                break;
            case 'player_list':
                $player_online = $map->mid_get_player_online($player_info->nowmid);
                $user_count = count($player_online);
                if ($user_count == 1) {
                    $html = "";
                } else {
                    $html = $this->load_mid_player($name, $player_info, $player_online);
                } 
                break;
            case 'Quick_battle_1':
            case 'Quick_battle_2':
            case 'Quick_battle_3':
            case 'Quick_battle_4':
            case 'Quick_battle_5':
            case 'Quick_battle_6':
            case 'Quick_battle_7':
            case 'Quick_battle_8':
            case 'Quick_battle_9':
                $html = $this->player->get_battle_Quick($val , $name);// 加载战斗信息
                break;
            case 'Quick_battle_set':
                $html = $sys->create_url("cmd=Quick_battle_set", $name);
                break;
        } 
        return $html;
    } 

    function load_operation_list($operation_arry) { // 加载自定义操作列表
        global $sys;
        global $operation;
        $arry = explode(',', $operation_arry);
        if (is_array($arry)) {
            foreach ($arry as $val) {
                $operation_info = $operation->get_operation_info($val);
                $html .= $sys->create_url("cmd=operation&id={$operation_info->id}", "{$operation_info->name}<br>");
            } 
        } else {
            $operation_info = $operation->get_operation_info($operation_arry);
            $html .= $sys->create_url("cmd=operation&id={$operation_info->id}", "{$operation_info->name}<br>");
        } 
        return $html;
    } 

    function load_User_message() { // 加载聊天信息到页面
        global $sys;
        global $user_message;
        $obj_liaotian_all = $user_message->liaotian_get_all(2);
        if ($obj_liaotian_all) {
            for ($i = 0;$i < count($obj_liaotian_all);$i++) {
                $ltObj = $obj_liaotian_all[count($obj_liaotian_all) - $i-1];
                $uname = $ltObj->name;
                $umsg = $ltObj->msg;
                $uid = $ltObj->uid;
                $date = $ltObj->date;
                $date = date_create($date);
                $date = date_format($date, 'H:i:s');
                $ucmd = $sys->create_url("cmd=otherzhuangtai&uid=$uid", $uname);
                if ($uid) {
                    $lthtml .= "[公共][$date]$ucmd:$umsg<br />";
                } else {
                    $lthtml .= "[公共][$date]<div class='hpys' style='display: inline'>$uname:</div>$umsg<br />";
                } 
            } 
        } 
        return $lthtml;
    } 

    function load_mip_goodslist($name = '', $map_info = null, $goods_list = null) { // 加载场景物品列表信息
        global $map;
        global $sys;
        global $player_info;
        $nowdate = date('Y-m-d H:i:s');
        $second = floor((strtotime($nowdate) - strtotime($map_info->mgtime)) % 86400); //获取刷新间隔
        if ($second > $map_info->ms && count($guaiwu_all) == 0 && $map_info->mgid) { // 刷新怪物
            $gw_arr = $map->mid_get_guaiwu_sys_all($map_info->mid);
            if (is_array($ge_arr)) {
                foreach ($gw_arr as $gid) {
                    $guaiwu = $map->gw_get_info_sys($gid);
                    $gw_count = $map->mid_get_guaiwu_sys_num($map_info->id , $gid);
                    for ($n = 0 ; $n < $gw_count ; $n++) {
                        $map->mid_add_gw($gid, $map_info->id);
                    } 
                } 
            } 
        } 

        $udc_show_gw_ext_info = intval($sys->get_system_config("怪物", "1表示显示扩展信息"));
        if ($udc_show_gw_ext_info == 1) {
            $guaiwu_all = $map->mid_get_guaiwu_all_and_wounded($map_info->id); //获取怪物 含被受伤的(当前sid)
        } else {
            $guaiwu_all = $map->mid_get_goods_all($map_info->id); //获取现有怪物
        } 

        $gwhtml = '';
        for ($i = 0;$i < count($guaiwu_all);$i++) {
            $gid = $guaiwu_all[$i]['id'];
            $gyid = $guaiwu_all[$i]['gid'];
            if ($udc_show_gw_ext_info == 1) {
                // $tmpExt='(#'.$gid;
                $tmpExt = '';
                $ghp = intval($guaiwu_all[$i]['ghp']) ;
                $gmaxhp = intval($guaiwu_all[$i]['gmaxhp']) ;
                if ($gmaxhp - $ghp >= $gmaxhp / 10) {
                    if ($gmaxhp - $ghp >= $gmaxhp / 3) {
                        $tmpExt .= '(重伤)';
                    } else {
                        $tmpExt .= '(伤)';
                    } 
                } else {
                    $tmpExt .= '';
                } 
                // $tmpExt.=')';
            } else {
                $tmpExt = '';
            } 
            if (intval($guaiwu_all[$i]['gnum']) != 0) {
                $gnum = "({$guaiwu_all[$i]['gnum']})";
            } else {
                $gnum = "";
            } ;
            $gwcmd = $sys->create_url("cmd=goodsinfo&cmd2=mid&goodsid={$gid}&goodsyid={$gyid}&nowmid={$player_info->nowmid}", "{$guaiwu_all[$i]['gname']}{$tmpExt}{$gnum}");
            $gwhtml .= $gwcmd;
        } 
        return $name . $gwhtml;
    } 

    function load_mid_player($name = '', $player_info = null, $player_online = null) { // 获取玩家所在地图的其他玩家列表
        global $sys;
        if ($player_online) {
            $nowdate = date('Y-m-d H:i:s');
            for ($i = 0; $i < count($player_online);$i++) {
                $player1 = (object)$player_online[$i];
                $cxtime = $player1->endtime;
                $cxuid = $player1->uid;
                $cxsid = $player1->sid;
                $cxuname = $player1->name;
                $second = floor((strtotime($nowdate) - strtotime($cxtime)) % 86400); //获取刷新间隔
                if ($second > 300) {
                    $this->player->player_update_sfzx_uid(0, $cxuid);
                } else {
                    $clubp = $this->player->clubplayer_get_player_uid($cxuid);
                    if ($clubp) {
                        $club = $this->player->club_get_info($clubp->clubid);
                        $club->clubname = "[$club->clubname]";
                    } else {
                        $club = (object)array('clubname' => '');
                    } 
                    if ($player_info->uid != $cxuid) {
                        $playerhtml .= $sys->create_url("cmd=otherzhuangtai&uid=$cxuid", "{$club->clubname}$cxuname");
                    } 
                } 
            } 
        } 
        return $name . $playerhtml;
    } 
} 

?>