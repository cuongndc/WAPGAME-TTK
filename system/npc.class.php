<?php 
// npc管理类
namespace game_system;

class npc {
    public $dblj;
    public $sid;
    public $uid;
    public $sys;
    public $task;
    public $map;
    public $equip;

    function __construct() {
        global $dblj;
        global $sys;
        global $map;
        global $task;
        global $equip;
        $this->dblj = $dblj;
        $this->sys = $sys;
        $this->map = $map;
        $this->task = $task;
        $this->equip = $equip;
        if (!isset($_SESSION['sid'])) {
            return;
        } 
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
    } 

    function mid_get_npc($mnpc) { // 获取地图npc
        $sql = "select * from npc where npc_id in ($mnpc)";
        $cxjg = $this->dblj->query($sql);
        $cxnpcall = $cxjg->fetchAll(\PDO::FETCH_OBJ);
        return $cxnpcall;
    } 

    function get_npc_seaech($npc_name, $qyid = 0, $page = 1, $count = 0) { // 按条件搜索所有npc
        if ($page < 0) {
            $page = 1;
        } 
        if ($count < 0) {
            $count = 0;
        } 
        if ($npc_name == "" && $qyid == 0) {
            return ;
        } 
        $sql = "select * from npc where";
        if ($npc_name != "") {
            $sql .= " name like ? ";
        } 
        if ($qyid != 0 && $npc_name != "") {
            $sql .= " and qy = ?";
        } elseif ($qyid != 0) {
            $sql .= " qy = ?";
        } 
        $stmt = $this->dblj->prepare($sql);
        if ($qyid == 0 && $npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%"));
        } elseif ($qyid != 0 && $npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%", $qyid));
        } elseif ($qyid != 0) {
            $stmt->execute(array($qyid));
        } elseif ($npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%"));
        } 
        $rows = $stmt->rowCount();
        if ($count != 0) {
            $sql = "select * from npc where name like ? ";
            if ($qyid != 0 && $npc_name != "") {
                $sql .= " and qy = ?";
            } elseif ($qyid != 0) {
                $sql .= " qy = ?";
            } 
            $sql .= " limit " . (intval($page)-1) * intval($count) . "," . intval($count) . ";";
        } 
        $stmt = $this->dblj->prepare($sql);
        if ($qyid == 0 && $npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%"));
        } elseif ($qyid != 0 && $npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%", $qyid));
        } elseif ($qyid != 0) {
            $stmt->execute(array($qyid));
        } elseif ($npc_name != "") {
            $stmt->execute(array("%" . $npc_name . "%"));
        } 
        $qynpcall = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return array($rows , $qynpcall);
    } 

    function get_qy_npc($qyid , $page = 1, $count = 20) { // 获取区域所有npc
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $obj = json_decode('{}');
        $sql = "select * from npc where qy = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($qyid));
        $obj->num = $stmt->rowCount();
        if ($obj->num) {
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($qyid));
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_npc_info($nid) { // 获取npc
        // var_dump("NPCid:",$nid,'<br>');
        $sql = "select * from npc where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($nid));
        $npc = $stmt->fetch(\PDO::FETCH_OBJ);
        return $npc;
    } 

    function load_mip_npclist($map_info) { // 加载场景NPC列表信息
        global $map;
        global $sys;
        global $dis;
        global $player;
        global $player_info;
        $us = $player->get_player_us($player_info->sid);
        $udc_show_gw_ext_info = intval($sys->get_system_config("怪物", "1表示显示扩展信息"));
        if ($udc_show_gw_ext_info == 1) {
            $guaiwu_all = $map->mid_get_guaiwu_all_and_wounded($map_info->id); //获取怪物 含被受伤的(当前sid)
        } else {
            $guaiwu_all = $map->mid_get_guaiwu_all($map_info->id); //获取现有怪物
        } 
        $gwhtml = '';
		
        foreach($guaiwu_all as $npc) {
            $gid = $npc->id;
            $gyid = $npc->gid;
			$cmd = "cmd=npcinfo";
            $npc_info = $this->get_npc_info($gyid);
            if ($udc_show_gw_ext_info == 1) {
                // $tmpExt='(#'.$gid;
                $tmpExt = '';
                $ghp = intval($npc->hp) ;
                $gmaxhp = intval($npc->max_hp) ;
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

            if (intval($npc->attackable) == 1) {
                $attackable = "*";
            } else {
                $attackable = "";
            } ;
			$task_ts = "";
			$task_url = "";
            if (isset($npc_info->task)) {
                $task_list = json_decode($npc_info->task);
                foreach($task_list as $obj) {
                    if (is_object($obj)) {
                        $task_info = $this->task->get_task_info($obj->id);
                        if (isset($task_info->rwtrigger) && $task_info->rwtrigger != "") {
                            $val = $dis->dis_text_decode($task_info->rwtrigger, $guaiwu, $player_info);
							var_dump("任务触发条件：{$val}<br>");
                        } else {
                            $val = true;
                        } 
                    } 
                } 
                if ($val) {
					$play_task = $this->task->rw_player_get_info($player_info->sid, $task_info->id);
					if($play_task){
						$task_ts = "<img src='images/wen.gif'>";
					}else{
						$task_ts = "<img src='images/tan.gif'>";
					}
					$cmd = "cmd=taskinfo&cmd2=npcinfo";
					$task_url = "&task={$task_info->id}";
                } 
            } 

            if ($npc->gnum != 0) {
                $gnum = "({$npc->gnum})";
            } else {
                $gnum = "";
            } ;

            $gwcmd = $sys->create_url("{$cmd}&gid={$gid}&gyid={$gyid}&nowmid={$player_info->nowmid}{$task_url}", "{$task_ts}{$attackable}{$npc->name}{$tmpExt}{$gnum}");
            $gwhtml .= $gwcmd;
        } 
        return $gwhtml;
    } 

    function set_npc_add($qyid) { // 新建一个NPC
        $sql = "INSERT INTO `npc` (`name`,`qy`) VALUES ( ? , ? );";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array("未命名", $qyid));
        $task = $stmt->fetch(\PDO::FETCH_OBJ);
        $id = $this->dblj->lastInsertId();
        return $id;
    } 

    function set_npc_field($npcid, $type, $value = null) { // 更新一个NPC
        switch ($type) {
            case "task":
            case "edit_task":
                $field = "task";
                break;
            case "skills":
            case "edit_skills":
                $field = "skills";
                break;
            case "equip_class":
                $field = "equip_class";
                break;
            case "equip_val":
                $field = "equip_val";
                break;
            case "drop_items":
                $field = "drop_items";
                break;
            case "drop_equip":
                $field = "drop_equip";
                break;
            case "drop_exp":
                $field = "drop_exp";
                break;
            case "drop_money":
                $field = "drop_money";
                break;
            case "drop_equip_factor":
                $field = "drop_equip_factor";
                break;
            case "drop_money_factor":
                $field = "drop_money_factor";
                break;
            case "operation":
                $field = "operation";
                break;
            case 'event_create':
                $field = "event_create";
                break;
            case 'event_watch':
                $field = "event_watch";
                break;
            case 'event_attack':
                $field = "event_attack";
                break;
            case 'event_defense':
                $field = "event_defense";
                break;
            case 'event_win':
                $field = "event_win";
                break;
            case 'event_fail':
                $field = "event_fail";
                break;
            case 'event_adopted':
                $field = "event_adopted";
                break;
            case 'event_trade':
                $field = "event_trade";
                break;
            case 'event_upgrade':
                $field = "event_upgrade";
                break;
            case 'event_heartbeat':
                $field = "event_heartbeat";
                break;
            case 'event_timing':
                $field = "event_timing";
                break;
        } 
        if ($value == null) {
            $sql = "UPDATE `npc` SET {$field} = null WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($npcid));
        } else {
            $sql = "UPDATE `npc` SET {$field} = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($value, $npcid));
        } 
        return $ret;
    } 

    function edit_drop($data) { // 保存NPC掉落配置
        $obj = json_decode($data);
        $npcid = $obj->id;
        if (isset($obj->drop_exp) && G_trimall($obj->drop_exp) != "") {
            $this->set_npc_field($npcid, 'drop_exp', $obj->drop_exp);
        } ;
        if (isset($obj->drop_money) && G_trimall($obj->drop_money) != "") {
            $this->set_npc_field($npcid, 'drop_money', $obj->drop_money);
        } ;
        if (isset($obj->drop_equip_factor) && G_trimall($obj->drop_equip_factor) != "") {
            $this->set_npc_field($npcid, 'drop_equip_factor', $obj->drop_equip_factor);
        } ;
        if (isset($obj->drop_money_factor) && G_trimall($obj->drop_money_factor) != "") {
            $this->set_npc_field($npcid, 'drop_money_factor', $obj->drop_money_factor);
        } ;
        return array('title' => '掉落定义已保存！', 'body' => 'NPC掉落定义保存成功！');
    } 

    function del_npc_id($id) { // 删除一个已经创建的NPC
        global $operation;
        global $event;
        $obj_info = $this->get_npc_info($id);
        try {
            $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dblj->beginTransaction();
            if ($obj_info->operation) {
                $arry = explode(',', $data);
                if (is_array($arry)) {
                    foreach ($arry as $val) {
                        $operation->del_operation($val);
                    } 
                } 
            } 
            if ($obj_info->event_create) {
                $event->del_event($obj_info->event_create);
            } 
            if ($obj_info->event_watch) {
                $event->del_event($obj_info->event_watch);
            } 
            if ($obj_info->event_enter) {
                $event->del_event($obj_info->event_enter);
            } 
            if ($obj_info->event_leave) {
                $event->del_event($obj_info->event_leave);
            } 
            if ($obj_info->event_timing) {
                $event->del_event($obj_info->event_timing);
            } 
            $stmt = $this->dblj->prepare('DELETE FROM npc WHERE id = ?;');
            $stmt->execute(array($obj_info->id));
            if ($stmt->rowCount() == 1) {
                $bool = $this->dblj->commit();
            } 
        } 
        catch(Exception $e) {
            $this->dblj->rollback();
        } 
        return $bool;
    } 

    function load_event_list($id) { // 加载地图事件列表
        $npc_info = $this->get_npc_info($id);
        $path = "npc";
        $id = $npc_info->id;
        $alert_open = alert_open;
        $link = "path={$path}&key={$id}";
        $html = <<<html
			<h3>编辑NPC"{$npc_info->name}"的事件：</h3>
<table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-npc-list">
  <tr><td>1 .创建事件</td><td>
html;
        if ($npc_info->event_create) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=create\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','create','{$npc_info->id}','{$npc_info->event_create}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=create\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>2 .查看事件</td><td>
";
        if ($npc_info->event_watch) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=watch\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','watch','{$npc_info->id}','{$npc_info->event_watch}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=watch\">设置事件</a>";
        } 

        $html .= "</td></tr>
<tr><td>3 .出招事件</td><td>
";
        if ($npc_info->event_attack) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=attack\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','attack','{$npc_info->id}','{$npc_info->event_attack}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=attack\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>4 .被攻击事件</td><td>
";
        if ($npc_info->event_defense) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=defense\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','defense','{$npc_info->id}','{$npc_info->event_defense}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=defense\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>5 .战胜事件</td><td>
";
        if ($npc_info->event_win) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=win\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','win','{$npc_info->id}','{$npc_info->event_win}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=win\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>6 .战败事件</td><td>
";
        if ($npc_info->event_fail) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=fail\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','fail','{$npc_info->id}','{$npc_info->event_fail}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=fail\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>7 .被收养事件</td><td>
";
        if ($npc_info->event_adopted) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=adopted\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','adopted','{$npc_info->id}','{$npc_info->event_adopted}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=adopted\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>8 .交易事件</td><td>
";
        if ($npc_info->event_trade) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=trade\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','trade','{$npc_info->id}','{$npc_info->event_trade}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=trade\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>9 .升级事件</td><td>
";
        if ($npc_info->event_upgrade) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=upgrade\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','upgrade','{$npc_info->id}','{$npc_info->event_upgrade}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=upgrade\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>10 .心跳事件</td><td>
";
        if ($npc_info->event_heartbeat) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=heartbeat\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','heartbeat','{$npc_info->id}','{$npc_info->event_heartbeat}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=heartbeat\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>11 .分钟定时事件</td><td>
";
        if ($npc_info->event_timing) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=timing\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','timing','{$npc_info->id}','{$npc_info->event_timing}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=timing\">设置事件</a>";
        } 
        $html .= "</td></tr>
</tbody></table>";
        return $html;
    } 

    function load_equip_list($id) { // 加载系统装备类别到NPC装备填充页面
        $info = $this->get_npc_info($id);
        $value = json_decode('{}');
        $value->weapon = $this->equip->load_weapon_class();
        $value->equip = $this->equip->load_equip_class();
        $this->set_npc_field($id, 'equip_class', json_encode($value));
    } 

    function get_enemy_info($enemy = null) { // 加载玩家当前攻击的NPC数据到战斗页面
        if (!isset($enemy)) {
            return "";
        } 
        $arry = explode(',', $enemy); 
        // var_dump($arry );
        if (is_array($arry)) {
            foreach ($arry as $val) {
                $enemy_info = $this->get_npc_run($val);
                $ut = json_decode($enemy_info->ut_val);
                $html = <<<html
				【{$enemy_info->name}】<br>
				体力：{$enemy_info->hp}/{$enemy_info->max_hp}{$ut->cut_hp->val}<br>
				法力：{$enemy_info->mp}/{$enemy_info->max_mp}{$ut->cut_mp->val}<br>
html;
            } 
        } 
        return $html;
    } 

	function get_killtask_npc($sid,$npc_id){
		$sql = "SELECT * FROM `player_killnpc` WHERE sid = ? AND npc_id = ?;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid,$npc_id));
		return $stmt->fetch(\PDO::FETCH_OBJ);
	}
	
	function del_killtask_npc($sid,$id){
		$sql = "DELETE FROM `player_killnpc` WHERE sid = ? AND id = ?;";
        $stmt = $this->dblj->prepare($sql);
		return  $stmt->execute(array($sid,$id));
	}
	
	function set_killtask_npc_num($sid,$task_id,$npc_id,$num){
		global $task;
		$sql = "UPDATE `player_killnpc` SET `kill_number` = ifnull(`kill_number`,0) + ? WHERE `sid` = ? and `npc_id` = ? and `task_id` = ?;";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($num,$sid,$npc_id,$task_id));
		
		if($ret){
			$task_info = $task->get_task_info($task_id);
			$npc_info = $this->get_npc_info($npc_id);
			$kill_info = $this->get_killtask_npc($sid,$npc_id);
			echo "任务【{$task_info->name}】击杀目标：{$npc_info->name}({$kill_info->kill_number}/{$kill_info->number})<br>";
		}
		return $ret;
	}
	
	function insert_killtask_npc($sid,$npc_id,$task_id,$num){
		$kill_npc = $this->get_killtask_npc($sid,$npc_id);
		if(!$kill_npc){
			$npc_info = $this->get_npc_info($npc_id);
			$sql = "INSERT INTO `player_killnpc`( `sid`, `task_id`,`npc_id`, `npc_name`, `number`) VALUES ( ?, ?, ?, ?, ?);";
			$stmt = $this->dblj->prepare($sql);
			$row = $stmt->execute(array($sid,$task_id,$npc_info->id,$npc_info->name,$num));
		}else{
			$sql = "UPDATE `player_killnpc` SET `number` = ifnull(`number`,0) + ? WHERE `sid` = ? and `npc_id` = ? and `task_id` = ?;";
			$stmt = $this->dblj->prepare($sql);
			$row = $stmt->execute(array($num,$sid,$npc_id,$task_id));
		}
		return $row;
	}
	
    function get_npc_run($gid) {
        $sql = "select * from mid_npc where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($gid));
        $guaiwu = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$guaiwu) {
            return false;
        } 
        $yguaiwu = $this->get_npc_info($guaiwu->gid);

        $guaiwu->gsex = $yguaiwu->gsex;
        $guaiwu->ginfo = $yguaiwu->ginfo;

        $udc_gw_ranges = $this->sys->get_system_config("游戏", "怪物层次分级");
        if (empty($udc_gw_ranges)) {
            // default
            $ranges_lv = array(0, 30, 50, 70, 80, 90, 100, 110);
        } else {
            $ranges_lv = explode('|', $udc_gw_ranges);
            if (count($ranges_lv) < 8) {
                // 默认30级一个层次
                for ($i = count($ranges_lv); $i < 8; $i++) {
                    $ranges_lv[] = $ranges_lv[count($ranges_lv)-1] + 30;
                } 
            } 
        } 

        $udc_gw_jj = $this->sys->get_system_config("游戏", "怪物层次定义");
        if (!empty($udc_gw_jj)) {
            $tmpArr = explode('|', $udc_gw_jj);
            if (count($tmpArr) < 8) {
                for ($i = count($tmpArr); $i < 8; $i++) {
                    $tmpArr[] = '层次' . $i;
                } 
            } 
            $层次1 = $tmpArr[0];
            $层次2 = $tmpArr[1];
            $层次3 = $tmpArr[2];
            $层次4 = $tmpArr[3];
            $层次5 = $tmpArr[4];
            $层次6 = $tmpArr[5];
            $层次7 = $tmpArr[6];
            $层次8 = $tmpArr[7];
        } else {
            // default
            $层次1 = $this->sys->get_system_config("游戏", "层次1");
            $层次2 = $this->sys->get_system_config("游戏", "层次2");
            $层次3 = $this->sys->get_system_config("游戏", "层次3");
            $层次4 = $this->sys->get_system_config("游戏", "层次4");
            $层次5 = $this->sys->get_system_config("游戏", "层次5");
            $层次6 = $this->sys->get_system_config("游戏", "层次6");
            $层次7 = $this->sys->get_system_config("游戏", "层次7");
            $层次8 = $this->sys->get_system_config("游戏", "层次8");
        } 

        $ranges_jj = array($层次1 , $层次2 , $层次3 , $层次4 , $层次5 , $层次6 , $层次7 , $层次8);

        for ($i = 0 ; $i < count($ranges_lv) ; $i++) {
            $lv = $ranges_lv[$i];
            $lv1 = $ranges_lv[$i + 1];

            if ($guaiwu->glv >= $ranges_lv[$i] && $guaiwu->glv < $ranges_lv[$i + 1]) {
                $ranges_jd = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
                $djc = $guaiwu->glv - $lv;
                $jds = ($lv1 - $lv) / 10;
                $j = (int) floor($djc / $jds);

                $jd = $ranges_jd[$j];
                $guaiwu->jingjie = $ranges_jj[$i] . $jd . '层';

                break;
            } 
        } 
        return $guaiwu;
    } 

    function gw_set_sid($gid) {
        $sql = "update mid_npc set sid = ? WHERE id= ?";
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute(array($this->sid , $gid));
        return $exeres;
    } 

    function gw_change_hp($hp , $lx , $gid) {
        if ($lx == 1) {
            $sql = "update mid_npc set hp = hp + ? WHERE id = ?";
            $stmt = $this->dblj->prepare($sql);
            return $stmt->execute(array($hp , $gid));
        } else {
            $sql = "update mid_npc set hp = hp - ? WHERE id = ? AND hp > 0 ";
            $stmt = $this->dblj->prepare($sql);
            return $stmt->execute(array($hp , $gid));
        } 
    } 

    function create_pve_lose($npc_info ,$player_sid ,$变量_系统) {
        global $player;
        global $dis;
        $this->gw_delete($npc_info->id);
        $player->player_go_re();
        $player->set_player_field("enemy",null,$player_sid);
        $ynpc = $this->get_npc_info($npc_info->gid); 
        $dis_pve = $dis->dis_get('排版_pve_失败');
        $out_html = '';
        eval("\$out_html = \"$dis_pve->dis_string\";");
        $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
        $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
        $out_html = str_replace("</div><br/>", "</div>", $out_html);
        return $out_html;
    } 

    function create_pve_win($npc_info  ,$player_sid , $变量_系统) {
        global $player;
        global $dis;
        $this->gw_delete($npc_info->id);
		$player->set_player_field("enemy",null,$player_sid);
        $ynpc = $this->get_npc_info($npc_info->gid); 
		$player_info = $player->get_player_info();
		//更新玩家任务列表击杀目标数据
		$ret = $this->get_killtask_npc($player_info->sid,$ynpc->id);
        if ($ret) {
			$this->set_killtask_npc_num($player_info->sid,$ret->task_id,$ynpc->id,$npc_info->gnum);
        } 
		
        $yxb = round($npc->glv * 3.9) + 1;
		
        $player->player_add_exp($ynpc->drop_exp);
		
		
        $player->yxb_change(1 , $yxb);
        $sjjv = mt_rand(1, 120);
        $zb_all = $this->gw_get_zb_all($ynpc->gzb);
        $dl_zb = "" ;
        $dl_dj = "";
        $dl_yp = "";
        $rwts = '';
        if ($ynpc->dljv >= $sjjv && $zb_all) {
            $sjdl = mt_rand(0, count($zb_all)-1);
            $zb = $zb_all[$sjdl];
            $add_gj = 0;
            $add_fy = 0;
            $add_hp = 0;
            $add_sx_sj = mt_rand(0, 2);
            if (!$add_sx_sj && $zb->zbgj > 0) {
                $sxsj = mt_rand(1, 100);
                if ($sxsj <= 70) {
                    $add_gj = mt_rand(1, 20);
                    $add_gj = ceil($zb->zbgj * ($add_gj / 100));
                    $zb->zbname .= "[攻击低]";
                } elseif ($sxsj <= 95) {
                    $add_gj = mt_rand(15, 60);
                    $add_gj = ceil($zb->zbgj * ($add_gj / 100));
                    $zb->zbname .= "[攻击中]";
                } else {
                    $add_gj = mt_rand(30, 100);
                    $add_gj = ceil($zb->zbgj * ($add_gj / 100));
                    $zb->zbname .= "[攻击高]";
                } 
            } 

            $add_sx_sj = mt_rand(0, 2);
            if (!$add_sx_sj && $zb->zbfy > 0) {
                $sxsj = mt_rand(1, 100);
                if ($sxsj <= 70) {
                    $add_fy = mt_rand(1, 20);
                    $add_fy = ceil($zb->zbfy * ($add_fy / 100));
                    $zb->zbname .= "[防御低]";
                } elseif ($sxsj <= 95) {
                    $add_fy = mt_rand(15, 60);
                    $add_fy = ceil($zb->zbfy * ($add_fy / 100));
                    $zb->zbname .= "[防御中]";
                } else {
                    $add_fy = mt_rand(30, 100);
                    $add_fy = ceil($zb->zbfy * ($add_fy / 100));
                    $zb->zbname .= "[防御高]";
                } 
            } 

            $add_sx_sj = mt_rand(0, 2);
            if (!$add_sx_sj && $zb->zbhp > 0) {
                $sxsj = mt_rand(1, 100);
                if ($sxsj <= 70) {
                    $add_hp = mt_rand(1, 20);
                    $add_hp = ceil($zb->zbhp * ($add_hp / 100));
                    $zb->zbname .= "[气血低]";
                } elseif ($sxsj <= 95) {
                    $add_hp = mt_rand(15, 60);
                    $add_hp = ceil($zb->zbhp * ($add_hp / 100));
                    $zb->zbname .= "[气血中]";
                } else {
                    $add_hp = mt_rand(30, 100);
                    $add_hp = ceil($zb->zbhp * ($add_hp / 100));
                    $zb->zbname .= "[气血高]";
                } 
            } 

            $this->zb_add_zhuangbei_add($zb->zbid , $zb->zbname , $add_gj , $add_fy , 0 , 0 , $add_hp);
            $dl_zb = "获得:<div class='zbys'>$zb->zbname</></div>";
        } 

        $sjjv = mt_rand(1, 95);
        $dj_all = $this->gw_get_dj_all($ynpc->gdj);

        if ($ynpc->djjv >= $sjjv && $dj_all) {
            $s = mt_rand(0, count($dj_all) - 1);
            $dj = $dj_all[$s];
            $djname = $dj->djname;
            $djid = $dj->djid;

            $djsum = mt_rand(1, 2);

            $this->dj_add($djid , $djsum);
            $rw_dj_wwc_all = $this->rw_get_player_pve_wwc_dj($djid);
            $dl_dj = "获得:<div class='djys'>{$djname}x{$djsum}</div>";
            foreach ($rw_dj_wwc_all as $renwu) {
                $rwts .= "任务：$renwu->rwname($renwu->rwnowcount/$renwu->rwcount)<br/>";
            } 
        } 

        $sjjv = mt_rand(1, 95);
        $yp_all = $this->gw_get_yp_all($ynpc->gyp);
        if ($ynpc->ypjv >= $sjjv && $yp_all) {
            $s = mt_rand(0, count($yp_all) - 1);
            $yp = $yp_all[$s];
            $ypsum = mt_rand(1, 2);
            $this->yp_add($yp->ypid , $ypsum);
            $dl_yp = "获得:<div class='ypys'>{$yp->ypname}x{$ypsum}</div>";
        } 
        $player_info = $player->get_player_info();

        $变量_pve_胜利 = (object)[
        "掉落装备" => $dl_zb,
        "掉落道具" => $dl_dj,
        "掉落药品" => $dl_yp,
        "获得经验" => $ynpc->drop_exp,
        "获得游戏币" => $yxb,
        "任务进度" => $rwts
        ];

        $dis_pve = $dis->dis_get('排版_pve_胜利');
        $out_html = '';
        eval("\$out_html = \"$dis_pve->dis_string\";");
        $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
        $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
        $out_html = str_replace("</div><br/>", "</div>", $out_html);

        return $out_html;
    } 

    function gw_delete($gid) {;
        $sql = "delete from mid_npc where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $bool = $stmt->execute(array($gid));
        return $bool;
    } 

    function gw_get_zb_all($gzb) {
        if (!$gzb) {
            return false;
        } 
        $sql = "select * from zhuangbei WHERE zbid in ( $gzb )";
        $stmt = $this->dblj->query($sql);
        $zb_all = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $zb_all;
    } 

    function gw_get_yp_all($gyp) {
        if (!$gyp) {
            return false;
        } 
        $sql = "select * from yaopin WHERE ypid in ($gyp)";
        $cxdljg = $this->dblj->query($sql);
        $ret = $cxdljg->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function gw_get_dj_all($gdj) {
        if (!$gdj) {
            return false;
        } 
        $sql = "select * from daoju WHERE djid in ($gdj)";
        $query = $this->dblj->query($sql);
        $ret = $query->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function set_npc_ut($id, $field, $val = null, $way = 'add') { // 向玩家数据表写入临时属性
        $ut = $this->get_npc_ut($id);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
        if (!is_object($ut->$field)) {
            $ut->$field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        if (isset($val)) {
            switch ($way) {
                case "add":
                    $ut->$field->val = $val;
                    $ut->$field->type = $type;
                    break;
                case "plus":
                case "+":
                    if ($ut->$field->type == 'num' && $type == 'num') {
                        $ut->$field->val = $ut->$field->val + $val;
                    } else {
                        $ut->$field->val = $val;
                    } 
                    break;
                case "reduce":
                case "-":
                    if ($ut->$field->type == 'num' && $type == 'num') {
                        $ut->$field->val = $ut->$field->val + $val;
                    } else {
                        $ut->$field->val = $val;
                    } 
                    break;
                case "multiply":
                case "*":
                    if ($ut->$field->type == 'num' && $type == 'num') {
                        $ut->$field->val = $ut->$field->val * $val;
                    } else {
                        $ut->$field->val = $val;
                    } 
                    break;
                case "divide":
                case "/":
                    if ($ut->$field->type == 'num' && $type == 'num') {
                        $ut->$field->val = $ut->$field->val / $val;
                    } else {
                        $ut->$field->val = $val;
                    } 
                    break;
            } 
        } else {
            unset($ut->$field);
        } 
        $sql = "update mid_npc set ut_val = ? WHERE id = ? ";
        $data = array(json_encode($ut) , $id);
        $stmt = $this->dblj->prepare($sql);
        return $stmt->execute($data);
    } 

    function set_npc_us($id, $field, $val = null, $way = 'add') { // 向玩家数据表写入动态属性
        $ut = $this->get_npc_us($id);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
        if (!is_object($ut->$field)) {
            $ut->$field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        if (isset($val)) {
            if (!is_object($val)) {
                switch ($way) {
                    case "add":
                        $ut->$field->val = $val;
                        $ut->$field->type = $type;
                        break;
                    case "plus":
                    case "+":
                        if ($ut->$field->type == 'num' && $type == 'num') {
                            $ut->$field->val = $ut->$field->val + $val;
                        } else {
                            $ut->$field->val = $val;
                        } 
                        break;
                    case "reduce":
                    case "-":
                        if ($ut->$field->type == 'num' && $type == 'num') {
                            $ut->$field->val = $ut->$field->val + $val;
                        } else {
                            $ut->$field->val = $val;
                        } 
                        break;
                    case "multiply":
                    case "*":
                        if ($ut->$field->type == 'num' && $type == 'num') {
                            $ut->$field->val = $ut->$field->val * $val;
                        } else {
                            $ut->$field->val = $val;
                        } 
                        break;
                    case "divide":
                    case "/":
                        if ($ut->$field->type == 'num' && $type == 'num') {
                            $ut->$field->val = $ut->$field->val / $val;
                        } else {
                            $ut->$field->val = $val;
                        } 
                        break;
                } 
            } else {
                $ut->$field->val = $val;
                $ut->$field->type = 'object';
            } 
        } else {
            unset($ut->$field);
        } 
        $sql = "update mid_npc set us_val = ? WHERE id = ? ";
        $data = array(json_encode($ut) , $id);
        $stmt = $this->dblj->prepare($sql);

        return $stmt->execute($data);
    } 

    function set_npc_max($id, $field, $val, $way = 'add') { // 向玩家数据表写入属性MAX值
        $ut = $this->get_npc_max($id);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
        if (!is_object($ut->$field)) {
            $ut->$field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        switch ($way) {
            case "add":
                $ut->$field->val = $val;
                $ut->$field->type = $type;
                break;
            case "plus":
            case "+":
                if ($ut->$field->type == 'num' && $type == 'num') {
                    $ut->$field->val = $ut->$field->val + $val;
                } else {
                    $ut->$field->val = $val;
                } 
                break;
            case "reduce":
            case "-":
                if ($ut->$field->type == 'num' && $type == 'num') {
                    $ut->$field->val = $ut->$field->val + $val;
                } else {
                    $ut->$field->val = $val;
                } 
                break;
            case "multiply":
            case "*":
                if ($ut->$field->type == 'num' && $type == 'num') {
                    $ut->$field->val = $ut->$field->val * $val;
                } else {
                    $ut->$field->val = $val;
                } 
                break;
            case "divide":
            case "/":
                if ($ut->$field->type == 'num' && $type == 'num') {
                    $ut->$field->val = $ut->$field->val / $val;
                } else {
                    $ut->$field->val = $val;
                } 
                break;
        } 
        $sql = "update mid_npc set all_max = ? WHERE id = ? ";
        $data = array(json_encode($ut) , $id);
        $stmt = $this->dblj->prepare($sql);
        return $stmt->execute($data);
    } 

    function get_npc_ut($id) { // 获取NPC临时属性列表
        $sql = "select * from mid_npc where id= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->ut_val);
    } 

    function get_npc_us($id) { // 获取NPC临时属性列表
        $sql = "select * from mid_npc where id= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->us_val);
    } 

    function get_npc_max($id) { // 获取NPC临时属性列表
        $sql = "select * from mid_npc where id= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->all_max);
    } 
} 

?>