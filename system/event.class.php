<?php 
// 游戏脚本管理类
namespace game_system;

class event {
    public $dblj;
    public $sid;
    public $uid;
    public $gpcl;
    public $sys;
    public $goods;
    public $token;
    public $map;
    public $dis;
    public $skill;
    public $searchBox;
    public $task;
    public $npc;
    public $equip;
    public $player;

    function __construct() {
        global $dblj;
        global $map;
        global $sys;
        global $dis;
        global $goods;
        global $skill;
        global $searchBox;
        global $task;
        global $npc;
        global $equip;
        global $player;
        $this->dblj = $dblj;
        $this->goods = $goods;
        $this->dis = $dis;
        $this->map = $map;
        $this->sys = $sys;
        $this->skill = $skill;
        $this->searchBox = $searchBox;
        $this->task = $task;
        $this->npc = $npc;
        $this->equip = $equip;
        $this->player = $player;

        if (!isset($_SESSION['sid'])) {
            return;
        } 
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
    } 

    function get_event_info($id) { // 根据ID获取事件数据
        $sql = "SELECT * FROM `event` WHERE id = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $event = $stmt->fetch(\PDO::FETCH_OBJ);
        return $event;
    } 

    function get_event_branch($event) { // 根据事件id查询步骤到事件编辑器
        $branch = json_decode($event->branch);
        if (is_object($branch)) {
            foreach($branch as $obj) {
                if (is_object($obj)) {
                    $i++;
                    if ($i > 1) {
                        $up = <<<html
	<button type="button" class="btn btn-success" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onclick="edit_branch('up','{$obj->id}')">上移</button></td>
html;
                    } 
                    $html .= <<<html
			<tr>
			<td><span class="lead">步骤{$i}:</span> </td>
			<td><a class="btn btn-primary" href="branch-edit.php?type=edit&key={$obj->id}">修改</button></td>
			<td><button class="btn btn-danger" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onclick="edit_branch('del','{$obj->id}')">删除</button></td>
			<td>{$up}</td>
			</tr>
html;
                } 
            } 
        } 
        return $html;
    } 

	function load_play_event(&$branch_html){//加载事件步骤到玩家步骤缓存数据集
		$player_info = $this->player->get_player_info();
		$event_obj = json_decode($player_info->event_id ,true);
		if(count($event_obj) <= 0 ){
			return ;
		}
		if (is_array($event_obj)) {
			foreach($event_obj as $event_val) {
				if(empty($player_info->branch_id)){
					$event_info = $this->get_event_info($event_val);
					$branch_obj = json_decode($event_info->branch);
					if(count($event_obj) > 1){
						$this->player->set_player_field('event_id', json_encode(array_shift($event_obj)) , $player_info->sid);
					}else{
						$this->player->set_player_field('event_id', null , $player_info->sid);
					}
					$this->player->set_player_field('branch_id',$event_info->branch, $player_info->sid);
					$this->load_play_branch($branch_html);
					break; 
				}else{
					$this->load_play_branch($branch_html);
					break; 
				}
			}
		}else{
			return ;
		}
	}

	function load_play_branch(&$branch_html) {
		global $url_data;
		$player_info = $this->player->get_player_info();
		$branch_obj = json_decode($player_info->branch_id);
		$branch_count = $branch_obj->count;
		unset($branch_obj->count);
		if(count((array)$branch_obj) == 0){
			$this->player->set_player_field('branch_id', null, $player_info->sid);
			$this->load_play_event($branch_html);
			return ;
		}
		if(is_object($branch_obj)){
			foreach($branch_obj as $id => $branch){
				if(is_object($branch)){
					$branch_end = false; 	//当前为最后一个步骤
					$event_exit = false;  	//允许立即返回模式关闭
					$return_game = false;		//返回游戏连接
					$Result = false;		//步骤触发结果
					$input = false;			//是否等待用户输入
					$branch = $branch->id;
					$Result = $this->branch_decode($branch ,$player_info , $branch_html, $branch_end, $event_exit , $return_game,$input,$branch_count);
					//var_dump("最后一个步骤:",$branch_end, "立即返回:" ,$event_exit ,"触发结果:",$Result ,"返回游戏:" ,$return_game ,"<br>");
					unset($branch_obj->$id);
					//var_dump("剩余{$branch_count}个步骤",'<br>');
					if($branch_count < 1){
						$this->player->set_player_field('branch_id', null, $player_info->sid);
					}else{
						$branch_obj->count = $branch_count;
						$this->player->set_player_field('branch_id', json_encode($branch_obj), $player_info->sid);
					}
					if ( $Result ){ //步骤成功触发，并成功执行完毕
						if ($event_exit) {//步骤执行完毕立即返回
							$this->player->set_player_field('branch_id', null, $player_info->sid);
							break; 
						} 
						if (!empty($branch_html)){ 
							if($branch_end){//非最后一个步骤
								$branch_html .=  $this->sys->create_url($url_data, '继续') . '<br>';
							}
							if($return_game){//是否需要显示返回游戏连接
								$branch_html .= $this->sys->create_url_nowmid() . '<br>';
							}
							break; 
						}
					}else{
						if ($branch_count >= 1) {
							$this->load_play_branch($branch_html);
						}else{
							return;
						}
					}
				}
			}
		}
	} 
	
    function branch_decode($branch , $player_info , &$branch_html, &$branch_end , &$event_exit , &$return_game ,&$input,&$branch_num) { // 解析步骤集  返回真继续执行之后步骤，返回假抛弃之后步骤
        $sid = $player_info->sid;
		$branch_num = $branch_num - 1;
        $branch = $this->get_branch_info($branch); //读取步骤数据并准备解析
        if (isset($branch->trigger) && !empty($branch->trigger)) { // 检查步骤触发条件
            $trigger = $this->dis->dis_condition_decode($branch->trigger, $player_info);
            if ($trigger == "真") {
                $trigger = true;
                $branch_html = $this->dis->dis_text_decode($branch->trigger_prompt, $player_info) . '<br>';
            } else {
                $trigger = false;
            } 
        } else {
            $trigger = true;
            $branch_html = $this->dis->dis_text_decode($branch->trigger_prompt, $player_info) . '<br>';
        } 
        if ($trigger) { // 条件校验通过，进入步骤处理模型
            if (isset($branch->run) && !empty($branch->run)) { // 检查步骤执行条件
                $run = $this->dis->dis_condition_decode($branch->run, $player_info);
                if ($run == "真") {
                    $run = true;
                    $branch_html = $this->dis->dis_text_decode($branch->trigger_prompt, $player_info) . '<br>';
                } else {
                    $run = false;
                } 
            } else {
                $run = true;
            }
            if ($run) { // 步骤执行条件验证通过
                if (intval($branch->change_uo) == 1) {
                } ;

                if ($branch->set_up) { // 设置属性
                    $set_up = json_decode($branch->set_up); 
                    // var_dump($set_up);
                    foreach($set_up as $obj) {
                        if (is_object($obj)) {
                            $this->set_attr_field($sid, $obj->name, $obj->val);
                        } 
                    } 
                } ;

                if ($branch->change_genus) { // 变更属性
                    $change_genus = json_decode($branch->change_genus);
                    foreach($change_genus as $obj) {
                        if (is_object($obj)) {
                            $this->alter_attr_field($sid, $obj->name, $obj->val);
                        } 
                    } 
                } ;

                /**
                 * if($branch->change_items){
                 * 
                 * };
                 * if($branch->get_equipment){
                 * 
                 * };
                 * if($branch->lose_equipment){
                 * 
                 * };
                 */

                if ($branch->learning_skills) { // 学会技能
                    $learning_skills = json_decode($branch->learning_skills);
                    foreach($learning_skills as $obj) {
                        if (is_object($obj)) {
                            $this->skill->add_player_skill($obj->id , $sid);
                        } 
                    } 
                } ;

                if ($branch->abolish_skills) { // 废除技能
                    $abolish_skills = json_decode($branch->abolish_skills);
                    foreach($abolish_skills as $obj) {
                        if (is_object($obj)) {
                            $this->skill->del_player_skill_initial($obj->id , $sid);
                        } 
                    } 
                } ;

                if ($branch->trigger_task) { // 触发任务
                    $trigger_task = json_decode($branch->trigger_task);
                    foreach($trigger_task as $obj) {
                        if (is_object($obj)) {
                            $this->task->insert_player_task($obj->id, $branch->trigger_prompt);
                        } 
                    } 
                } ;

                if ($branch->del_task) { // 删除任务
                    $del_task = json_decode($branch->del_task);
                    foreach($del_task as $obj) {
                        if (is_object($obj)) {
                            $this->task->del_player_task($player_info->sid, $obj->id);
                        } 
                    } 
                } ;

                if ($branch->del_task_ok) { // 删除已完成任务
                    $del_task_ok = json_decode($branch->del_task_ok);
                    foreach($del_task_ok as $obj) {
                        if (is_object($obj)) {
                            $this->task->del_player_task($player_info->sid, $obj->id , 1);
                        } 
                    } 
                } ;

                if ($branch->del_task_give_up) { // 删除已放弃任务
                    $del_task_give_up = json_decode($branch->del_task_give_up);
                    foreach($del_task_give_up as $obj) {
                        if (is_object($obj)) {
                            $this->task->del_player_task($player_info->sid, $obj->id , 0);
                        } 
                    } 
                } ;

                /**
                 * if($branch->challenge_people){
                 * $challenge_people =json_decode($branch->challenge_people);
                 * foreach($challenge_people as $obj){
                 * if(is_object($obj)){
                 * $task->rw_player_delete($obj->id , 0);
                 * }
                 * }
                 * };
                 * if($branch->adoptive_pets){
                 * $adoptive_pets =json_decode($branch->adoptive_pets);
                 * foreach($adoptive_pets as $obj){
                 * if(is_object($obj)){
                 * $task->rw_player_delete($obj->id , 0);
                 * }
                 * }
                 * };
                 * if($branch->del_pets){
                 * $del_pets = json_decode($branch->del_pets);
                 * foreach($del_pets as $obj){
                 * if(is_object($obj)){
                 * $task->rw_player_delete($obj->id , 0);
                 * }
                 * }
                 * };
                 * 
                 * if($branch->view_player){
                 * $view_player =json_decode($branch->view_player);
                 * foreach($view_player as $obj){
                 * if(is_object($obj)){
                 * $branch_html .= $task->rw_player_delete($obj->id , 0);
                 * }
                 * }
                 * };
                 * if($branch->display_page){
                 * require_once $ym;
                 * };
                 * if($branch->refresh_npc){
                 * 
                 * };
                 * if($branch->refresh_items){
                 * 
                 * };
                 */

                if ($branch->mall_members) { // 设置商城特权
                } ;

                if ($branch->moving_target) { // 移动玩家到指定地图
                    $user_input = json_decode($branch->moving_target);
                    foreach($user_input as $obj) {
                        if (is_object($obj)) {
                            $mid_id = $this->dis->dis_text_decode($obj->id, $player_info);
                        } 
                    } 
                    if (isset($mid_id)) {
                        $this->player->player_relocation_mid(intval($mid_id));
                    } 
                } ;

                if (intval($branch->branch_back) == 1) { // 步骤执行完毕立即返回游戏
                    $this->player->set_player_field('event_id', null, $player_info->sid);
					$event_exit = true;
                } 

                if ($branch_num > 1 ) { // 判断是否需要显示继续按钮
                    $branch_end = true ;
                }
				
                if ( !empty($branch->user_input)) { // 判断是否需要显示继续按钮
                    $branch_end = true ;
                } 

                if (intval($branch->back) == 1) { // 步骤执行完毕显示返回游戏
                    $return_game = true;
                } ;
				$bool = true;
				if ($branch->user_input) { // 接收用户输入字段
                    $user_input = json_decode($branch->user_input);
                    $branch_html .= "<form name='userinput'  method='post'>
					<input type='hidden' name='form_userinput' value='1'/>";
                    foreach($user_input as $obj) {
                        if (is_object($obj)) {
                            $branch_html .= "{$obj->name}:<input type='text' name='{$obj->mark}' />";
                        } 
                    } 
                    $branch_html .= "<br>&nbsp;<br><input type='submit' value='提交' /></form><br>";
					$branch_end = false ;
					$return_game = false;
					$input = true;
                } ;
				
			} else {
				
                $branch_html = $this->dis->dis_text_decode($branch->run_fail, $player_info) . '<br>';
				$bool = false;
				
            } 
			
        } else {
			
            $bool = false;
			
        }
		$this->player->set_player_ut($player_info->sid, 'input');
        return $bool;
		
    } 

    function event_decode($enid, &$info) { // 解析事件定义
        global $player;
        global $player_info;
        $user_event = json_decode($player_info->event_id);
        if (!is_array($user_event)) {    $user_event = [];	}
        $event_info = $this->get_event_info($enid);
        if ($event_info->activated == 1) {
            if (isset($event_info->trigger) && !empty($event_info->trigger)) {
                $trigger = $this->dis->dis_condition_decode($event_info->trigger, $player_info);
                if ($trigger == "真") {
                    $trigger = true;
                } else {
                    $trigger = false;
                } 
            } else {
                $trigger = true;
            } 
            if ($trigger) {
				$sys_event = [];
                $branch = json_decode($event_info->branch);
                if ($branch && $branch->count > 0) {
                    array_push($user_event, $event_info->id);
                } 
				//var_dump($user_event);
                $branch = $player->set_player_field('event_id', json_encode($user_event), $player_info->sid);
                if ($branch) {
                    $player_info = $player->get_player_info();
                    return true;
                } else {
                    $info = '事件解析器出错！事件定义无法执行！';
                    return false;
                } 
            }else{
				$info = $event_info->trigger_fail;
				return false;
			}
        } else {
            $info = $event_info->trigger_fail;
            return false;
        } 
    } 

    function set_attr_field($sid, $field , $val = null) { // 解析事件步骤设置属性
        global $player_info;
        $arry = explode(".", $field);
        if (count($arry) >= 2) {
            $type = $arry[0];
            $name = $arry[1];
            $val = $this->dis->dis_text_decode($val, $player_info);
            if (substr($val, 0, 1) != '#') {
                $val = floatval($val);
            } else {
                $val = substr($val, 1);
            } 
            $this->player->set_attr_field($sid, $type, $name, $val);
        } 
    } 

    function alter_attr_field($sid, $field , $val = null) { // 解析事件步骤更改属性
        global $player_info;
        global $player_info;
        $arry = explode(".", $field);
        if (count($arry) >= 2) {
            $type = $arry[0];
            $name = $arry[1];
            $val = $this->dis->dis_text_decode($val, $player_info);
            if (substr($val, 0, 1) != '#') {
                $val = floatval($val);
            } else {
                $val = substr($val, 1);
            } 
            $this->player->alter_attr_field($sid, $type, $name, $val);
        } 
    } 

    function edit_branch($event_id, $key, $type) { // 编辑步骤数据
        // var_dump($event_id,$key,$type);
        switch ($type) {
            case 'edit':
                $val = $this->load_branch($key);
                break;
            case 'del':
                $val = $this->del_branch($event_id, $key);
                break;
            case 'up':
                $val = $this->move_branch($event_id, $key);
                break;
        } 
        return $val ;
    } 

    function load_event_list($path, $id) { // 加载事件列表到编辑器
        switch ($path) {
            case 'map':
                $html = $this->map->load_event_list($id);
                break;
            case 'npc':
                $html = $this->npc->load_event_list($id);
                break;
            case 'task':
                $obj = $this->task->edit_task_info($id);
                $html = $obj->body;
                break;
            case 'goods':
                $html = $this->goods->load_event_list($id);
                break;
            case 'equip':
                $html = $this->equip->load_event_list($id);
                break;
        } 
        return array('body' => $html);
    } 

    function del_branch($event_id, $branch) { // 删除事件的一个步骤
        // 设置异常模式
        $res = false;
        $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        try {
            // 开始事务
            $this->dblj->beginTransaction();
            $sql = "DELETE FROM `event_branch` WHERE id= ? ;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($branch));
            $event = $this->get_event_info($event_id);
            $branch_info = json_decode($event->branch);
            foreach ($branch_info as $id => $val) {
                if ($val->id == $branch) {
                    unset($branch_info->$id);
                    $branch_info->count = $branch_info->count - 1;
                    break;
                } 
            } 
            // var_dump('准备删除步骤ID',$branch_info);
            $this->save_event_val($event_id, 'branch', json_encode($branch_info));
            $this->dblj->commit(); //提交
            $res = true;
        } 
        catch (Exception $e) {
            // 抓住try里面出现的错误，并且处理
            // echo $e->getMessage(); //获取异常信息
            $this->dblj->rollBack(); //回滚
        } 
        return $res;
    } 

    function del_event_req($value) { // 删除事件请求
        $path = $value['path'];
        $key = $value['objid'];
        $event_info = $this->get_event_info($value['key']);
        $clas = $value['clas'];
        $confirm = $value['confirm'];
        switch ($value['path']) {
            case 'map':
                $arry = $this->del_event_req_map($path, $key, $clas, $confirm, $event_info);
                break;
            case 'task':
                $arry = $this->del_event_req_task($path, $key, $clas, $confirm, $event_info);
                break;
            case 'npc':
                $arry = $this->del_event_req_npc($path, $key, $clas, $confirm, $event_info);
                break;
            case 'skill':
                $arry = $this->del_event_req_skill($path, $key, $clas, $confirm, $event_info);
                break;
            case 'goods':
            case 'equip':
                $arry = $this->del_event_req_goods($path, $key, $clas, $confirm, $event_info);
                break;
            default:
                $arry = $this->del_event_req_sys($confirm, $event_info);
        } 

        return $arry;
    } 

    function del_event_req_skill($path, $key, $clas, $confirm, $event_info) {
        $obj_info = $this->skill->get_skill_info($key);
        switch ($clas) {
            case 'use':
                $name = "技能使用事件";
                $type = "event_use";
                break;
            case 'uplvl':
                $name = "技能升级事件";
                $type = "event_uplvl";
                break;
        } 
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $bool = $this->skill->set_skill_field($obj_info->id, $type);
            } ;
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $repage = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$path}','{$clas}','{$key}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('repage' => $repage , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event_req_sys($confirm, $event_info) { // 删除事件请求-任务事件
        $clas = $this->get_event_type($event_info->type);
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的{$clas['1']}事件：<br>事件名：<b>[{$event_info->name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $reloading = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的{$clas['1']}事件：<br>事件名：<b>[{$event_info->name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的{$clas['1']}事件：<br>事件名：<b>[{$event_info->name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$clas['0']}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('reload' => $reloading , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event_req_npc($path, $key, $clas, $confirm, $event_info) { // 删除事件请求-NPC事件
        $obj_info = $this->npc->get_npc_info($key);
        switch ($clas) {
            case 'create':
                $name = "创建事件";
                $type = "event_create";
                break;
            case 'watch':
                $name = "查看事件";
                $type = "event_watch";
                break;
            case 'attack':
                $name = "出招事件";
                $type = "event_attack";
                break;
            case 'defense':
                $name = "被攻击事件";
                $type = "event_defense";
                break;
            case 'win':
                $name = "战胜事件";
                $type = "event_win";
                break;
            case 'fail':
                $name = "战败事件";
                $type = "event_fail";
                break;
            case 'adopted':
                $name = "被收养事件";
                $type = "event_adopted";
                break;
            case 'trade':
                $name = "交易事件";
                $type = "event_trade";
                break;
            case 'upgrade':
                $name = "升级事件";
                $type = "event_upgrade";
                break;
            case 'heartbeat':
                $name = "心跳事件";
                $type = "event_heartbeat";
                break;
            case 'timing':
                $name = "分钟定时事件";
                $type = "event_timing";
                break;
        } 
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $bool = $this->npc->set_npc_field($obj_info->id, $type);
            } ;
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $reloading = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$path}','{$clas}','{$key}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('reloading' => $reloading , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event_req_map($path, $key, $clas, $confirm, $event_info) { // 删除事件请求-地图事件
        $map_info = $this->map->get_mid_info($key);
        switch ($clas) {
            case 'create':
                $name = "创建事件";
                $type = "event_create";
                break;
            case 'watch':
                $name = "查看事件";
                $type = "event_watch";
                break;
            case 'enter':
                $name = "进入事件";
                $type = "event_enter";
                break;
            case 'leave':
                $name = "离开事件";
                $type = "event_leave";
                break;
            case 'timing':
                $name = "分钟定时事件";
                $type = "event_timing";
                break;
        } 
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $bool = $this->map->set_mid_field($map_info->id, $type);
            } ;
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的事件：<br>事件名：<b>[{$map_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $reloading = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的事件：<br>事件名：<b>[{$map_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的事件：<br>事件名：<b>[{$map_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$path}','{$clas}','{$key}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('reloading' => $reloading , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event_req_task($path, $key, $clas, $confirm, $event_info) { // 删除事件请求-任务事件
        $obj_info = $this->task->get_task_info($key);
        switch ($clas) {
            case 'accept':
                $name = "接受事件";
                $type = "rwevent_accept";
                break;
            case 'discard':
                $name = "放弃事件";
                $type = "rwevent_discard";
                break;
            case 'complete':
                $name = "查看事件";
                $type = "rwevent_complete";
                break;
        } 
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $bool = $this->task->set_task_field($obj_info->id, $type);
            } ;
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $reloading = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$path}','{$clas}','{$key}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('reloading' => $reloading , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event_req_goods($path, $key, $clas, $confirm, $event_info) { // 删除事件请求-物品事件
        $obj_info = $this->goods->get_goods_info($key);
        if ($obj_info->clas == "equipinlay" || $obj_info->clas == "weaponinlay") {
            $name = "镶物";
            $wear_title = "镶入";
            $undress_title = "取下";
        } else {
            $name = "装备";
            $wear_title = "穿上";
            $undress_title = "卸下";
        } 
        switch ($clas) {
            case 'create':
                $name = "创建事件";
                $type = "event_create";
                break;
            case 'watch':
                $name = "查看事件";
                $type = "event_watch";
                break;
            case 'use':
                $name = "使用事件";
                $type = "event_use";
                break;
            case 'wear':
                $name = "{$name}{$wear_title}事件";
                $type = "event_wear";
                break;
            case 'undress':
                $name = "{$name}{$undress_title}事件";
                $type = "event_undress";
                break;
            case 'save':
                $name = "存储数据事件";
                $type = "event_save";
                break;
            case 'backups':
                $name = "导出数据事件";
                $type = "event_backups";
                break;
            case 'timing':
                $name = "分钟定时事件";
                $type = "event_timing";
                break;
        } 
        if ($confirm == "true") {
            $bool = $this->del_event($event_info->id);
            if ($bool) {
                $bool = $this->goods->set_goods_field($obj_info->id, $type);
            } ;
            if ($bool) {
                $title = '删除事件成功！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件已删除";
                $reloading = true;
            } else {
                $title = '删除事件失败！';
                $body = "已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的操作失败了！";
            } 
        } else {
            $title = '确认删除当前事件：';
            $body = "确认删除已经添加的事件：<br>事件名：<b>[{$obj_info->name}-{$name}]({$event_info->id})]</b><br>触发条件：<b>[{$event_info->trigger}]</b><br>这个事件的定义？";
            $exbtn = true;
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick="del_event('{$path}','{$clas}','{$key}','{$event_info->id}','true')">确认删除</button>
html;
        } 
        return array('reloading' => $reloading , 'title' => $title, 'body' => $body, 'btn' => $button, 'exbtn' => $exbtn);
    } 

    function del_event($event_id) { // 删除一个事件定义
        // 设置异常模式
        $event = $this->get_event_info($event_id);
        $branch_info = json_decode($event->branch);
        $res = false;
        $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        try {
            // 开始事务
            $this->dblj->beginTransaction();
            $sql = "DELETE FROM `event_branch` WHERE event_id= ? ;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($event_id));
            if ($event->name == "") {
                $sql = "DELETE FROM `event` WHERE id= ? ;";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($event_id));
            } else {
                $this->save_event_val($event_id, 'activated');
                $this->save_event_val($event_id, 'trigger');
                $this->save_event_val($event_id, 'trigger_fail');
                $this->save_event_val($event_id, 'branch');
            } 
            $this->dblj->commit(); //提交
            $res = true;
        } 
        catch (Exception $e) {
            $this->dblj->rollBack(); //回滚
        } 
        return $res;
    } 

    function move_branch($event_id, $branch) { // 向上移动一个步骤
        if (isset($branch)) {
            $event = $this->get_event_info($event_id);
            $branch_info = json_decode($event->branch);
            foreach ($branch_info as $id => $val) {
                if ($val->id == $branch) {
                    if (!isset($ri_val)) {
                        return false;
                    } 
                    $branch_info->$ri_id->id = $val->id;
                    $branch_info->$id->id = $ri_val;
                    break;
                } 
                $ri_id = $id;
                $ri_val = $val->id;
            } 
            return $this->save_event_val($event_id, 'branch', json_encode($branch_info));
        } 
        return false;
    } 

    function save_event_val($event_id, $type, $val = null) { // 保存对事件单项属性的修改
        switch ($type) {
            case 'activated':
                $field = 'activated';
                break;
            case 'trigger':
                $field = 'trigger';
                break;
            case 'trigger_fail':
                $field = 'trigger_fail';
                break;
            case 'branch':
                $field = 'branch';
                break;
        } 
        $sql = "UPDATE `event` SET `{$field}` = ? WHERE `id` = ?;";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($val, $event_id));
        return $ret;
    } 

    function event_type_load($type) { // 按类型加载公共事件表
        $clas = $this->get_event_type($type);
        $list = $this->get_event_all($clas[0]);
        $alert_open = alert_open;
        if (isset($list)) {
            foreach($list as $obj) {
                ++$i;
                $html .= "<tr><td>$i .$obj->name($obj->id)</td><td>";
                if ($obj->activated == 1) {
                    $html .= <<<html
				<a class="btn btn-primary" href="event.php?type=edit&enid={$obj->id}">修改</a>
				<button class="btn btn-danger" type="button" {$alert_open} onclick="del_event('{$clas['0']}','{$obj->id}')">删除</button>
html;
                } else {
                    $html .= <<<html
				<a class="btn btn-primary" href="event.php?type=edit&enid={$obj->id}">设置</button>
html;
                } 
                $html .= "</td></tr>";
            } 
        } 
        return $html;
    } 

    function get_event_type($val) { // 读取公共事件分类数据
        switch ($val) {
            case "user":
                $clas = array("user", "玩家");
                break;
            case "npc":
                $clas = array("npc", "电脑人物");
                break;
            case "map":
                $clas = array("map", "场景");
                break;
            case "sys":
                $clas = array("sys", "系统");
                break;
            case "goods":
                $clas = array("goods", "物品");
                break;
        } 
        return $clas;
    } 

    function get_event_all($type) { // 获取指定类型的所有事件
        $type = $this->get_event_type($type);
        $sql = "SELECT * FROM `event` WHERE `type` = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($type[0]));
        $list = $stmt->fetchALL(\PDO::FETCH_OBJ);
        return $list;
    } 

    function add_branch($event_id) { // 加载步骤新建菜单
        $ajax_open = alert_open;
        $html = <<<html
<form id="add_step">
触发条件：
<input type="hidden" name='event_id' value="{$event_id}">
<input type="hidden" name='branch_id' value="0">
<textarea class="form-control" rows="3" name="trigger"></textarea>
触发提示语：
<textarea class="form-control" rows="3" name="trigger_prompt"></textarea>
执行条件：
<textarea class="form-control" rows="3" name="run"></textarea>
不满足执行条件提示语:
<textarea class="form-control" rows="3" name="run_fail" ></textarea>
<div class="row">
  <div class="col-xs-6">
    <h4>返回游戏链接</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="back">
  <option value="1">是</option>
  <option value="0">否</option>
</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-6">
    <h4>执行此步骤后立刻返回</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="branch_back">
  <option value="1">是</option>
  <option value="0">否</option>
</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-6">
    <h4>更改物品区分主被动</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="change_uo">
  <option value="1">是</option>
  <option value="0">否</option>
</select>
  </div>
</div>
<h4>设置属性： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('set_up','0')">修改(0)</button></h4>
<h4>更改属性： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('change_genus','0')">修改(0)</button></h4>
<h4>更改物品： <button class="btn btn-info btn-sm" type="button"  onclick="edit_branch('change_items','0')">修改(0)</button></h4>
<h4>得到装备： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('get_equipment','0')">修改(0)</button></h4>
<h4>失去装备： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('lose_equipment','0')">修改(0)</button></h4>
<h4>学会技能： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('learning_skills','0')">修改(0)</button></h4>
<h4>废除技能： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('abolish_skills','0')">修改(0)</button></h4>
<h4>触发任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('trigger_task','0')">修改(0)</button></h4>
<h4>删除任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task','0')">修改(0)</button></h4>
<h4>删除已完成任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task_ok','0')">修改(0)</button></h4>
<h4>删除已放弃任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task_give_up','0')">修改(0)</button></h4>
<h4>挑战人物： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('challenge_people','0')">增加(0)</button></h4>
<h4>收养宠物对象： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('adoptive_pets','0')">添加(0)</button></h4>
<h4>删除宠物对象： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_pets','0')">添加(0)</button></h4>
<h4>移动目标： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('moving_target','0')">修改(0)</button></h4>
<h4>查看玩家的ID表达式:</h4>
<textarea class="form-control" rows="3"  name="view_player"></textarea><br>
<div class="row">
  <div class="col-xs-5">
    <h4>显示页面模板：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="页面模板名"  name="display_page"> 
  </div>
</div>
<div class="row">
  <div class="col-xs-5">
    <h4>刷新场景NPC：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="需要刷新NPC的ID" name="refresh_npc"> 
  </div>
</div>
<div class="row">
  <div class="col-xs-5">
    <h4>刷新场景物品：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="需要刷新物品的ID" name="refresh_items"> 
  </div>
</div>
<h4>用户输入： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('user_input','0')">修改(0)</button></h4>
<h4>商城VIP功能:  <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('mall_members','0')">设置</button></h4>
</form>
	<button class="btn btn-primary" type="button" {$ajax_open} onclick="save_branch()">保存修改</button><p></p>
	<hr>
html;
        return $html;
    } 

    function save_branch($data) { // 添加或保存一个步骤
        $data = json_decode($data);
        $branch_id = intval($data->id); 
        // var_dump($data);
        if ($branch_id == 0) {
            $sql = "INSERT INTO `event_branch`( `event_id`, `trigger`, `trigger_prompt`, `run`, `run_fail`, `back`, `branch_back`, `change_uo`, `view_player`, `display_page`, `refresh_npc`, `refresh_items` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($data->event_id , $data->trigger , $data->trigger_prompt , $data->run , $data->run_fail , $data->back , $data->branch_back , $data->change_uo , $data->view_player , $data->display_page , $data->refresh_npc , $data->refresh_items));
            return $this->dblj->lastInsertId();
        } else {
            $sql = "UPDATE `event_branch` SET `event_id` = ? , `trigger` = ?, `trigger_prompt` = ?, `run` = ?, `run_fail` = ?, `back` = ?, `branch_back` = ?, `change_uo` = ?, `view_player` = ?, `display_page` = ?, `refresh_npc` = ?, `refresh_items` = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($data->event_id , $data->trigger , $data->trigger_prompt , $data->run , $data->run_fail , $data->back , $data->branch_back , $data->change_uo , $data->view_player , $data->display_page , $data->refresh_npc , $data->refresh_items , $branch_id));
            return -1 ;
        } 
        return -2 ;
    } 

    function save_event($trigger, $trigger_fail, $event_id = 0) { // 添加或保存一个事件
        $event_id = intval($event_id);
        if ($event_id != 0) {
            $sql = "UPDATE `event` SET `activated` = 1 , `trigger` = ?, `trigger_fail` = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($trigger, $trigger_fail, $event_id));
        } 
        return $ret;
    } 

    function add_event($trigger = "" , $trigger_fail = "") { // 新建一个事件
        $sql = "INSERT INTO `event`(`activated`, `trigger`, `trigger_fail`) VALUES (  1, ?, ?);";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($trigger, $trigger_fail));
        $idd = $this->dblj->lastInsertId();
        return $idd ;
    } 

    function event_edit_branch($type, $key, $branch, $up = false) { // 编辑或添加一个事件的步骤信息
        $event = $this->get_event_info($key);
        if (!$up) {
            $branch_list = json_decode($event->branch);
            if (!is_object($branch_list)) {
                $branch_list = json_decode('{}');
                $branch_list->count = 0;
            } 
            $num = $branch_list->count + 1;
            $branch_list->count = $num;
            $branch_list->$num = json_decode('{}');
            $branch_list->$num->id = $branch;
            $branch_list = json_encode($branch_list); 
            // var_dump($branch_list);
            $sql = "UPDATE `event` SET `activated` = 1, `branch` = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($branch_list, $key));
            return $ret;
        } else {
        } 
    } 

    function load_branch($key) { // 解析步骤到编辑器
        $ajax_open = alert_open;
        $branch = $this->get_branch_info($key);
        if (is_object($branch)) {
            if ($branch->back == 1) {
                $back_true = "selected";
            } else {
                $back_false = "selected";
            } 
            if ($branch->branch_back == 1) {
                $branch_back_true = "selected";
            } else {
                $branch_back_false = "selected";
            } 
            if ($branch->change_uo == 1) {
                $change_uo_true = "selected";
            } else {
                $change_uo_false = "selected";
            } 
            $html .= <<<html
<form id="add_step">
触发条件：
<input type="hidden" name='event_id' value="{$branch->event_id}">
<input type="hidden" name='id' value="{$branch->id}">
<textarea class="form-control" rows="3" name="trigger">{$branch->trigger}</textarea>
触发提示语：
<textarea class="form-control" rows="3" name="trigger_prompt">{$branch->trigger_prompt}</textarea>
执行条件：
<textarea class="form-control" rows="3" name="run">{$branch->run}</textarea>
不满足执行条件提示语:
<textarea class="form-control" rows="3" name="run_fail">{$branch->run_fail}</textarea>
<div class="row">
  <div class="col-xs-6">
    <h4>返回游戏链接：</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="back">
  <option value="1" {$back_true}>是</option>
  <option value="0" {$back_false}>否</option>
</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-6">
    <h4>执行此步骤后立刻返回：</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="branch_back">
  <option value="1" {$branch_back_true}>是</option>
  <option value="0" {$branch_back_false}>否</option>
</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-6">
    <h4>更改物品区分主被动：</h4>
  </div>
  <div class="col-xs-6">
    <select class="form-control" name="change_uo">
  <option value="1" {$change_uo_true}>是</option>
  <option value="0" {$change_uo_false}>否</option>
</select>
  </div>
</div>
html;

            $set_up = json_decode($branch->set_up);
            if (!is_object($set_up)) {
                $set_up = json_decode('{"total":"0"}');
            } else {
                $set_up->total = isset($set_up->total) ? $set_up->total : 0 ;
            } 

            $change_genus = json_decode($branch->change_genus);
            if (!is_object($change_genus)) {
                $change_genus = json_decode('{"total":"0"}');
            } else {
                $change_genus->total = isset($change_genus->total) ? $change_genus->total : 0 ;
            } 

            $change_items = json_decode($branch->change_items);
            if (!is_object($change_items)) {
                $change_items = json_decode('{"total":"0"}');
            } else {
                $change_items->total = isset($change_items->total) ? $change_items->total : 0 ;
            } 

            $get_equipment = json_decode($branch->get_equipment);
            if (!is_object($get_equipment)) {
                $get_equipment = json_decode('{"total":"0"}');
            } else {
                $get_equipment->total = isset($get_equipment->total) ? $get_equipment->total : 0 ;
            } 

            $lose_equipment = json_decode($branch->lose_equipment);
            if (!is_object($lose_equipment)) {
                $lose_equipment = json_decode('{"total":"0"}');
            } else {
                $lose_equipment->total = isset($lose_equipment->total) ? $lose_equipment->total : 0 ;
            } 

            $learning_skills = json_decode($branch->learning_skills);
            if (!is_object($learning_skills)) {
                $learning_skills = json_decode('{"total":"0"}');
            } else {
                $learning_skills->total = isset($learning_skills->total) ? $learning_skills->total : 0 ;
            } 

            $abolish_skills = json_decode($branch->abolish_skills);
            if (!is_object($abolish_skills)) {
                $abolish_skills = json_decode('{"total":"0"}');
            } else {
                $abolish_skills->total = isset($abolish_skills->total) ? $abolish_skills->total : 0 ;
            } 

            $trigger_task = json_decode($branch->trigger_task);
            if (!is_object($trigger_task)) {
                $trigger_task = json_decode('{"total":"0"}');
            } else {
                $trigger_task->total = isset($trigger_task->total) ? $trigger_task->total : 0 ;
            } 

            $del_task = json_decode($branch->del_task);
            if (!is_object($del_task)) {
                $del_task = json_decode('{"total":"0"}');
            } else {
                $del_task->total = isset($del_task->total) ? $del_task->total : 0 ;
            } 

            $del_task_ok = json_decode($branch->del_task_ok);
            if (!is_object($del_task_ok)) {
                $del_task_ok = json_decode('{"total":"0"}');
            } else {
                $del_task_ok->total = isset($del_task_ok->total) ? $del_task_ok->total : 0 ;
            } 

            $del_task_give_up = json_decode($branch->del_task_give_up);
            if (!is_object($del_task_give_up)) {
                $del_task_give_up = json_decode('{"total":"0"}');
            } else {
                $del_task_give_up->total = isset($del_task_give_up->total) ? $del_task_give_up->total : 0 ;
            } 

            $challenge_people = json_decode($branch->challenge_people);
            if (!is_object($challenge_people)) {
                $challenge_people = json_decode('{"total":"0"}');
            } else {
                $challenge_people->total = isset($challenge_people->total) ? $challenge_people->total : 0 ;
            } 

            $adoptive_pets = json_decode($branch->adoptive_pets);
            if (!is_object($adoptive_pets)) {
                $adoptive_pets = json_decode('{"total":"0"}');
            } else {
                $adoptive_pets->total = isset($adoptive_pets->total) ? $adoptive_pets->total : 0 ;
            } 

            $del_pets = json_decode($branch->del_pets);
            if (!is_object($del_pets)) {
                $del_pets = json_decode('{"total":"0"}');
            } else {
                $del_pets->total = isset($del_pets->total) ? $del_pets->total : 0 ;
            } 

            $moving_target = json_decode($branch->moving_target);
            if (!is_object($moving_target)) {
                $moving_target = json_decode('{"total":"0"}');
            } else {
                $moving_target->total = isset($moving_target->total) ? $moving_target->total : 0 ;
            } 

            $user_input = json_decode($branch->user_input);
            if (!is_object($user_input)) {
                $user_input = json_decode('{"total":"0"}');
            } else {
                $user_input->total = isset($user_input->total) ? $user_input->total : 0 ;
            } 

            $html .= <<<html
<h4>设置属性： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('set_up','{$branch->id}')">修改({$set_up->total})</button></h4>
<h4>更改属性： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('change_genus','{$branch->id}')">修改({$change_genus->total})</button></h4>
<h4>更改物品： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('change_items','{$branch->id}')">修改({$change_items->total})</button></h4>
<h4>得到装备： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('get_equipment','{$branch->id}')">修改({$get_equipment->total})</button></h4>
<h4>失去装备： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('lose_equipment','{$branch->id}')">修改({$lose_equipment->total})</button></h4>
<h4>学会技能： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('learning_skills','{$branch->id}')">修改({$learning_skills->total})</button></h4>
<h4>废除技能： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('abolish_skills','{$branch->id}')">修改({$abolish_skills->total})</button></h4>
<h4>触发任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('trigger_task','{$branch->id}')">修改({$trigger_task->total})</button></h4>
<h4>删除任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task','{$branch->id}')">修改({$del_task->total})</button></h4>
<h4>删除已完成任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task_ok','{$branch->id}')">修改({$del_task_ok->total})</button></h4>
<h4>删除已放弃任务： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_task_give_up','{$branch->id}')">修改({$del_task_give_up->total})</button></h4>
<h4>挑战人物： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('challenge_people','{$branch->id}')">增加({$challenge_people->total})</button></h4>
<h4>收养宠物对象： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('adoptive_pets','{$branch->id}')">添加({$adoptive_pets->total})</button></h4>
<h4>删除宠物对象： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('del_pets','{$branch->id}')">添加({$del_pets->total})</button></h4>
<h4>移动目标： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('moving_target','{$branch->id}')">修改({$moving_target->total})</button></h4>
html;
            $html .= <<<html
<h4>查看玩家的ID表达式:</h4>
<textarea class="form-control" rows="3"  name="view_player">{$branch->view_player}</textarea>
<br>
<div class="row">
  <div class="col-xs-5">
    <h4>显示页面模板：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="页面模板名"  name="display_page" value="{$branch->display_page}" > 
  </div>
</div>
<div class="row">
  <div class="col-xs-5">
    <h4>刷新场景NPC：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="需要刷新NPC的ID" name="refresh_npc" value="{$branch->refresh_npc}"> 
  </div>
</div>
<div class="row">
  <div class="col-xs-5">
    <h4>刷新场景物品：</h4>
  </div>
  <div class="col-xs-7">
    <input type="text" class="form-control" placeholder="需要刷新物品的ID" name="refresh_items" value="{$branch->refresh_items}" > 
  </div>
</div>
<h4>用户输入： <button class="btn btn-info btn-sm" type="button" onclick="edit_branch('user_input','{$branch->id}')">修改({$user_input->total})</button></h4>

<h4>商城VIP功能: 
html;
            if ($branch->mall_members != "") {
                $html .= <<<html
	<button class="btn btn-info btn-sm" type="button" onclick="edit_branch('mall_members','{$branch->id}')">修改</button>
	<button class="btn btn-info btn-sm" type="button" onclick="edit_branch('mall_members','{$branch->id}')">取消 </button>
html;
            } else {
                $html .= <<<html
	<button class="btn btn-info btn-sm" type="button" onclick="edit_branch('mall_members','{$branch->id}')">设置</button>
html;
            } 

            $html .= <<<html
 </h4>
</form>
	<button class="btn btn-primary" type="button" {$ajax_open} onclick="save_branch('edit')">保存修改</button><p></p>
	<hr>
html;
            return $html;
        } 
    } 

    function get_branch_info($key) { // 从数据库加载一个步骤
        $sql = "SELECT * FROM `event_branch` WHERE id = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($key));
        return $stmt->fetch(\PDO::FETCH_OBJ);
    } 

    function edit_branch_type($type, $key = 0) { // 读取步骤数据集
        if ($key <= 0) {
            return '步骤编辑：传入一个错误的事件步骤！';
        } 
        $html = '<table  class="table"> <tr><td><b>';
        $ajax_alert = '</b></td><td style="text-align:right"><button type="button" ' . alert_open . ' onclick="addobj(\'';
        $ajax_end = "','{$key}')" . '" class="btn btn-primary">';
        $list = "<div id='window'>" . $this->searchBox->reloading('event', $type, $key) . "</div>";
        switch ($type) {
            case "set_up":// 设置属性
                $html .= "添加一个设置属性{$ajax_alert}add_set_up{$ajax_end}添加属性</button></td></tr></table>{$list}";
                break;
            case "change_genus":// 更改属性
                $html .= "添加一个更改属性{$ajax_alert}add_change_genus{$ajax_end}添加属性</button></td></tr></table>{$list}";
                break;
            case "change_items":// 更改物品
                $html .= "添加一个物品{$ajax_alert}add_change_items{$ajax_end}添加物品</button></td></tr></table>{$list}";
                break;
            case "get_equipment":// 得到装备
                $branch = $this->get_branch_info($key);
                $we = $this->load_weapon_class("主动者", "u", $branch->get_equipment);
                $we .= $this->load_equip_class("主动者", "u", $branch->get_equipment);
                $oe = $this->load_weapon_class("被动者", "o", $branch->get_equipment);
                $oe .= $this->load_equip_class("被动者", "o", $branch->get_equipment);
                $html = <<<html
<style>
	.form-group{margin-bottom: 5px;}
	.form-horizontal .form-group>label {text-align: left;}
</style>
<form class="form-horizontal" id="equipment">
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#tabContent1">得到主动者装备</a></li>
  <li><a data-tab href="#tabContent2">得到被动者装备</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active" id="tabContent1"><br>{$we}</div>
  <div class="tab-pane" id="tabContent2"><br>{$oe}</div></div>
 <button type="submit" class="btn btn-primary" data-position="100" data-toggle="modal" data-target="#ajax-alert"  onClick="save_form('get_equipment','{$key}');return false;">保存设置</button></form>
html;
                break;
            case "lose_equipment":// 失去装备
                $branch = $this->get_branch_info($key);
                $we = $this->load_weapon_class("主动者", "u", $branch->lose_equipment);
                $we .= $this->load_equip_class("主动者", "u", $branch->lose_equipment);
                $oe = $this->load_weapon_class("被动者", "o", $branch->lose_equipment);
                $oe .= $this->load_equip_class("被动者", "o", $branch->lose_equipment);
                $html = <<<html
<style>
	.form-group{margin-bottom: 5px;}
	.form-horizontal .form-group>label {text-align: left;}
</style>
<form class="form-horizontal" id="equipment">
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#tabContent1">失去主动者装备</a></li>
  <li><a data-tab href="#tabContent2">失去被动者装备</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active" id="tabContent1"><br>{$we}</div>
  <div class="tab-pane" id="tabContent2"><br>{$oe}</div>
</div>
 <button type="submit" class="btn btn-primary" data-position="100" data-toggle="modal" data-target="#ajax-alert"  onClick="save_form('lose_equipment','{$key}');return false;">保存设置</button></form>
html;
                break;
            case "learning_skills":// 学会技能
                $html .= "学会一个技能{$ajax_alert}add_learning_skills{$ajax_end}选择技能</button></td></tr></table>{$list}";
                break;
            case "abolish_skills":// 废除技能
                $html .= "选择一个废除技能{$ajax_alert}add_abolish_skills{$ajax_end}选择技能</button></td></tr></table>{$list}";
                break;
            case "trigger_task":// 触发任务
                $html .= "选择一个触发任务{$ajax_alert}add_trigger_task{$ajax_end}选择任务</button></td></tr></table>{$list}";
                break;
            case "del_task":// 删除任务
                $html .= "删除一个任务{$ajax_alert}add_del_task{$ajax_end}选择任务</button></td></tr></table>{$list}";
                break;
            case "del_task_ok":// 删除已完成任务
                $html .= "删除一个已完成任务{$ajax_alert}add_del_task_ok{$ajax_end}选择任务</button></td></tr></table>{$list}";
                break;
            case "del_task_give_up":// 删除已放弃任务
                $html .= "删除一个已放弃任务{$ajax_alert}add_del_task_give_up{$ajax_end}选择任务</button></td></tr></table>{$list}";
                break;
            case "challenge_people":// 挑战人物
                $html .= "添加一个挑战对象{$ajax_alert}add_challenge_people{$ajax_end}添加NPC</button></td></tr></table>{$list}";
                break;
            case "adoptive_pets":// 收养宠物
                $html .= "添加一个收养宠物{$ajax_alert}add_adoptive_pets{$ajax_end}添加宠物</button></td></tr></table>{$list}";
                break;
            case "del_pets":// 删除宠物
                $html .= "添加一个删除宠物{$ajax_alert}add_del_pets{$ajax_end}添加宠物</button></td></tr></table>{$list}";
                break;
            case "moving_target":// 移动目标
                $html .= "添加一个移动目标{$ajax_alert}add_moving_target{$ajax_end}添加地图</button></td></tr></table>{$list}";
                break;
            case "user_input":// 用户输入
                $html .= "添加一个用户输入{$ajax_alert}add_user_input{$ajax_end}添加输入</button></td></tr></table>{$list}";
                break;
            case "mall_members":// 商城VIP
                $html .= "添加一个物品{$ajax_alert}add_mall_members{$ajax_end}添加物品</button></td></tr></table>";
                $branch = $this->get_branch_info($key);
                $temp = json_decode($branch->mall_members);
                $html .= <<<html
		优惠折扣率（n%）：
		<form  class="form-horizontal" id="mall-members">
		<input type="text" class="form-control" name="discount" placeholder="优惠折扣率（n%）" value="{$temp->discount}">
		有效时长（分钟）：
		<input type="text" class="form-control" name="eftime" placeholder="有效时长（分钟）" value="{$temp->eftime}">
		</form>
		<button class="btn btn-primary" type="button" data-position="100" data-toggle="modal" data-target="#ajax-alert"  onClick="save_form('mall-members','{$key}');return false;">保存设置</button><br><br>
		<p>打折的物品：</p>{$list}
html;
                break;
        } 
        return $html . '<button type="button" class="btn btn-block" onClick="location.reload(\'true\')">返回步骤</button><br>';
    } 
    // 维护或删除步骤已定义数据
    function editing_step($val) { // 事件步骤维护主接口
        $id = $val['key'];
        $vid = $val['vid'];
        $info = $this->get_branch_info($id);
        switch ($val['clas']) {
            case 'del':
                $arry = $val['confirm'] == 'true' ? $this->edit_branch_val_del_true($id, $vid, $info, $val) : $this->edit_branch_val_del($id, $vid, $info, $val);
                break;
            case 'edit':
                $arry = $this->edit_branch_val_edit($id, $vid, $info, $val);
                break;
        } 
        return $arry ;
    } 

    function edit_branch_val_edit($id, $vid, $info, $val) { // 编辑步骤属性定义值
        switch ($val['com']) {
            case 'set_up':
                $branch_val = json_decode($info->set_up);
                $title = '编辑事件步骤的设置属性修改已设属性：';
                $btn = "add_set_up";
                $body = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名" value="{$branch_val->$vid->name}">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值">{$branch_val->$vid->val}</textarea>
html;

                break;
            case 'change_genus':
                $branch_val = json_decode($info->change_genus);
                $title = '编辑事件步骤的修改属性修改已设属性：';
                $btn = "add_change_genus";
                $body = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名" value="{$branch_val->$vid->name}">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值">{$branch_val->$vid->val}</textarea>
html;
                break;
            case 'change_items':
                $branch_val = json_decode($info->change_items);
                $title = '编辑事件步骤的更改物品已设物品：';
                $btn = "add_change_items";
                $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
                break;
            case 'challenge_people':
                $branch_val = json_decode($info->challenge_people);
                $title = '编辑事件步骤的挑战人物已设人物：';
                $btn = "add_challenge_people";
                $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="人物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
                break;
            case 'adoptive_pets':
                $branch_val = json_decode($info->adoptive_pets);
                $title = '编辑事件步骤的添加宠物已设宠物：';
                $btn = "add_adoptive_pets";
                $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="宠物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
                break;
            case 'del_pets':
                $branch_val = json_decode($info->del_pets);
                $title = '编辑事件步骤的删除宠物已设宠物：';
                $btn = "add_del_pets";
                $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="宠物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
                break;
            case 'user_input':
                $branch_val = json_decode($info->user_input);
                $title = "修改事件步骤添加用户输入";
                $btn = "add_user_input";
                if ($branch_val->$vid->category == "number") {
                    $category_nu = "selected";
                } 
                if ($branch_val->$vid->category == "text") {
                    $category_te = "selected";
                } 
                $body = <<<html
		字段标识：	
		<input type="text" class="form-control" id="add-mark" placeholder="字段标识" value="{$branch_val->$vid->mark}">
		字段名称：
		<input type="text" class="form-control" id="add-name" placeholder="字段名称" value="{$branch_val->$vid->name}">
		最大长度：
		<input type="number" class="form-control" id="add-size" placeholder="最大长度" value="{$branch_val->$vid->size}">
		字段值类型：
		<select class="form-control" id="add-category" >
		  <option value="text" {$category_te}>字符串</option>
		  <option value="number" {$category_nu}>数值</option>
		</select>
html;
                break;
        } 
        $button = <<<html
	<button class='btn btn-primary' type='button' onclick='add_branch("{$btn}","{$id}","true")'>保存修改</button>
html;

        return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
    } 

    function edit_branch_val_del($id, $vid, $info, $val) { // 预删除步骤属性定义值
        switch ($val['com']) {
            case 'set_up':
                $branch_val = json_decode($info->set_up);
                $title = '编辑事件步骤的设置属性删除已设属性：';
                $body = "确认删除已经添加的设置属性：<br>属性名：<b>[{$branch_val->$vid->name}]</b><br>属性值：<b>[{$branch_val->$vid->val}]</b><br>这个属性的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","set_up","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'change_genus':
                $branch_val = json_decode($info->change_genus);
                $title = '编辑事件步骤的修改属性删除已设属性：';
                $body = "确认删除已经添加的设置属性：<br>属性名：<b>[{$branch_val->$vid->name}]</b><br>属性值：<b>[{$branch_val->$vid->val}]</b><br>这个属性的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","change_genus","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'change_items':
                $branch_val = json_decode($info->change_items);
                $title = '编辑事件步骤的更改物品删除已设物品：';
                $body = "确认删除已经添加的更改物品：<br>物品名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个属性的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","change_items","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'learning_skills':
                $branch_val = json_decode($info->learning_skills);
                $title = '编辑事件步骤的学会技能：';
                $body = "确认删除已经添加的学会技能：<br>技能名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个技能的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","learning_skills","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'abolish_skills':
                $branch_val = json_decode($info->abolish_skills);
                $title = '编辑事件步骤的废除技能：';
                $body = "确认删除已经添加的废除技能：<br>技能名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个技能的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","abolish_skills","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'trigger_task':
                $branch_val = json_decode($info->trigger_task);
                $title = '编辑事件步骤的触发任务：';
                $body = "确认删除已经添加的触发任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","trigger_task","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'del_task':
                $branch_val = json_decode($info->del_task);
                $title = '编辑事件步骤的删除任务：';
                $body = "确认删除已经添加的删除任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","del_task","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'del_task_ok':
                $branch_val = json_decode($info->del_task_ok);
                $title = '编辑事件步骤的删除已完成任务：';
                $body = "确认删除已经添加的删除已完成任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","del_task_ok","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'del_task_give_up':
                $branch_val = json_decode($info->del_task_give_up);
                $title = '编辑事件步骤的删除已放弃任务：';
                $body = "确认删除已经添加的删除已放弃任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","del_task_give_up","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'challenge_people':
                $branch_val = json_decode($info->challenge_people);
                $title = '编辑事件步骤的挑战人物：';
                $body = "确认删除已经添加的挑战人物：<br>人物名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个人物的设置？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","challenge_people","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'adoptive_pets':
                $branch_val = json_decode($info->adoptive_pets);
                $title = '编辑事件步骤的添加宠物：';
                $body = "确认删除已经添加的添加宠物：<br>宠物名名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个宠物的设置？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","adoptive_pets","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'del_pets':
                $branch_val = json_decode($info->del_pets);
                $title = '编辑事件步骤的删除宠物：';
                $body = "确认删除已经添加的删除宠物：<br>宠物名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个宠物的设置？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","del_pets","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'moving_target':
                $branch_val = json_decode($info->moving_target);
                $title = '编辑事件步骤的移动用户目标：';
                $body = "确认删除已经添加的移动用户目标：<br>地图名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个位置的设置？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","moving_target","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'user_input':
                $branch_val = json_decode($info->user_input);
                $title = '编辑事件步骤的删除已设用户输入：';
                $body = "确认删除已经添加的删除已设用户输入：<br>字段标识名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->size})]</b><br>这个用户输入的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","user_input","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
            case 'mall_members':
                $branch_val = json_decode($info->mall_members);
                $title = '编辑事件步骤的删除商城物品：';
                $body = "确认删除已经添加的删除已设商城物品：<br>物品名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个物品的定义？";
                $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","event","mall_members","{$id}","{$vid}","true")'>确认删除</button>
html;
                break;
        } 
        return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
    } 

    function edit_branch_val_del_true($id, $vid, $info, $val) { // 删除步骤属性定义值
        // var_dump($id,$vid,$info,$val);
        $type = $val['com'];
        switch ($type) {
            case 'set_up':
                $branch_val = json_decode($info->set_up);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的设置属性删除已设属性：';
                    $body = "编辑事件步骤的设置属性删除已设属性 [{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'change_genus':
                $branch_val = json_decode($info->change_genus);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的修改属性删除已设属性：';
                    $body = "编辑事件步骤的修改属性删除已设属性 [{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'change_items':
                $branch_val = json_decode($info->change_items);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的更改物品删除已设物品：';
                    $body = "删除已经添加的更改物品：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'learning_skills':
                $branch_val = json_decode($info->learning_skills);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的学会技能删除已设技能：';
                    $body = "删除已经添加的学会技能：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'abolish_skills':
                $branch_val = json_decode($info->abolish_skills);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的废除技能删除已设技能：';
                    $body = "删除已经添加的废除技能：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'trigger_task':
                $branch_val = json_decode($info->trigger_task);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的触发任务删除已设技能：';
                    $body = "删除已经添加的触发任务：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'del_task':
                $branch_val = json_decode($info->del_task);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的删除任务删除已设技能：';
                    $body = "删除已经添加的删除任务：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'del_task_ok':
                $branch_val = json_decode($info->del_task_ok);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的删除已完成任务删除已设技能：';
                    $body = "删除已经添加的删除已完成任务：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'del_task_give_up':
                $branch_val = json_decode($info->del_task_give_up);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的删除已放弃任务删除已设技能：';
                    $body = "删除已经添加的删除已放弃任务：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'challenge_people':
                $branch_val = json_decode($info->challenge_people);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的挑战人物：';
                    $body = "删除已经添加的挑战人物：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'adoptive_pets':
                $branch_val = json_decode($info->adoptive_pets);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的收养宠物：';
                    $body = "删除已经添加的收养宠物：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'del_pets':
                $branch_val = json_decode($info->del_pets);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的删除宠物：';
                    $body = "删除已经添加的删除宠物：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'moving_target':
                $branch_val = json_decode($info->moving_target);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的移动用户目标：';
                    $body = "删除已经添加的移动用户目标到：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'user_input':
                $branch_val = json_decode($info->user_input);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的删除已设用户输入：';
                    $body = "删除已经添加的删除已设用户输入：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
            case 'mall_members':
                $branch_val = json_decode($info->mall_members);
                $name = $branch_val->$vid->name;
                $val = $this->del_branch_Packing_data($branch_val, $vid);
                if ($this->edit_branch_field($id, $type, $val)) {
                    $title = '编辑事件步骤的商城商品：';
                    $body = "删除已经添加的商城商品：[{$name}] 删除成功！";
                    return array('title' => $title , 'body' => $body , 'reloading' => true);
                } else {
                } 
                break;
        } 
        return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
    } 
    // 向步骤字段信息写入数值
    function edit_branch_field($key, $field, $val) { // 编辑步骤字段信息
        if ($key != 0) {
            switch ($field) {
                case "set_up":// 设置属性
                    $field = "set_up";
                    $adopt = true;
                    break;
                case "change_genus":// 更改属性
                    $field = "change_genus";
                    $adopt = true;
                    break;
                case "change_items":// 更改物品
                    $field = "change_items";
                    $adopt = true;
                    break;
                case "get_equipment":// 得到装备
                    $field = "get_equipment";
                    $adopt = true;
                    break;
                case "lose_equipment":// 失去装备
                    $field = "lose_equipment";
                    $adopt = true;
                    break;
                case "learning_skills":// 学会技能
                    $field = "learning_skills";
                    $adopt = true;
                    break;
                case "abolish_skills":// 废除技能
                    $field = "abolish_skills";
                    $adopt = true;
                    break;
                case "trigger_task":// 触发任务
                    $field = "trigger_task";
                    $adopt = true;
                    break;
                case "del_task":// 删除任务
                    $field = "del_task";
                    $adopt = true;
                    break;
                case "del_task_ok":// 删除已完成任务
                    $field = "del_task_ok";
                    $adopt = true;
                    break;
                case "del_task_give_up":// 删除已放弃任务
                    $field = "del_task_give_up";
                    $adopt = true;
                    break;
                case "challenge_people":// 添加挑战人物
                    $field = "challenge_people";
                    $adopt = true;
                    break;
                case "adoptive_pets":// 添加收养宠物
                    $field = "adoptive_pets";
                    $adopt = true;
                    break;
                case "del_pets":// 添加删除宠物
                    $field = "del_pets";
                    $adopt = true;
                    break;
                case "moving_target":// 移动目标
                    $field = "moving_target";
                    $adopt = true;
                    break;
                case "user_input":// 步骤用户输入
                    $field = "user_input";
                    $adopt = true;
                    break;
                case "mall_members":// 步骤商城VIP
                    $field = "mall_members";
                    $adopt = true;
                    break;
            } 
            if ($adopt) {
                $sql = "UPDATE `event_branch` SET {$field} = ? WHERE `id` = ?;";
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute(array($val, $key));
            } 
        } 
        return $ret;
    } 
    // 新建步骤数据集类别识别
    function add_branch_type($type, $confirm, $data, $key = 0) { // 新建步骤数据集
        // var_dump($type,$confirm,$data,$key);
        switch ($type) {
            case "add_set_up":// 设置属性
                return $this->add_set_up($key, $data);
                break;
            case "add_change_genus":// 更改属性
                return $this->add_change_genus($key, $data);
                break;
            case "add_change_items":// 更改物品
                return $this->add_change_items($key, $data);
                break;
            case "add_learning_skills":// 学会技能
                return $this->add_learning_skills($key, $data);
                break;
            case "add_abolish_skills":// 废除技能
                return $this->add_abolish_skills($key, $data);
                break;
            case "add_trigger_task":// 触发任务
                return $this->add_trigger_task($key, $data);
                break;
            case "add_del_task":// 删除任务
                return $this->add_del_task($key, $data);
                break;
            case "add_del_task_ok":// 删除已完成任务
                return $this->add_del_task_ok($key, $data);
                break;
            case "add_del_task_give_up":// 删除已放弃任务
                return $this->add_del_task_give_up($key, $data);
                break;
            case "add_challenge_people":// 挑战人物
                return $this->add_challenge_people($key, $data);
                break;
            case "add_adoptive_pets":// 收养宠物
                return $this->add_adoptive_pets($key, $data);
                break;
            case "add_del_pets":// 删除宠物
                return $this->add_del_pets($key, $data);
                break;
            case "add_moving_target":// 移动目标
                return $this->add_moving_target($key, $data);
                break;
            case "add_user_input":// 新建用户输入
                return $this->add_user_input($key, $data);
                break;
            case "add_mall_members":// 编辑步骤商城VIP
                return $this->add_mall_members($key, $data);
                break;
        } 
    } 
    // 打包数据准备写入数据库
    function del_branch_Packing_data($data, $vid) { // 打包删除后需要保存到步骤的数据
        if (isset($vid) && intval($vid) > 0) {
            $total = $data->total ;
            $total--;
            unset($data->$vid);
            if ($total < 0) {
                $total = 0;
            } 
            $data->total = $total;
        } 
        return json_encode($data);
    } 

    function add_branch_Packing_data($data, $new_data) { // 打包新建或修改后需要保存到步骤的数据
        $temp = json_decode($data);
        $key_id = $new_data['id'];
        $new_name = $new_data['name'];
        $new_val = $new_data['val'];
        $new_num = $new_data['num'];
        $new_mark = $new_data['mark'];
        $new_size = $new_data['size'];
        $new_category = $new_data['category'];

        if (is_object($temp)) { // 测试是否为原定义属性重新修改
            foreach($temp as $id => $obj) {
                if (isset($key_id)) {
                    if ($obj->id == $key_id) {
                        $temp->$id->val = $new_val;
                        $temp->$id->num = $new_num ;
                        $curt = true;
                        break;
                    } 
                } 
                if (isset($new_mark)) {
                    if ($obj->mark == $new_mark) {
                        $temp->$id->name = $new_name;
                        $temp->$id->size = $new_size;
                        $temp->$id->category = $new_category;
                        $curt = true;
                        break;
                    } 
                } 
                if ($obj->name == $new_name && $obj->id == $key_id && $obj->mark == $new_mark) {
                    $temp->$id->val = $new_val;
                    $temp->$id->num = $new_num ;
                    $curt = true;
                    break;
                } 
            } 
        } 
        if (!$curt) { // 非已定义属性修改，则分配新属性位置
            if (!is_object($temp)) {
                $temp = json_decode('{}');
                $id = 0;
            } 
            $id = $temp->id;
            $total = $temp->total;
            $id++;
            $total++;
            $temp->id = $id ;
            $temp->total = $total ;
            $temp->$id = json_decode('{}');
            if (isset($key_id)) {
                $temp->$id->id = $key_id;
            } 
            $temp->$id->name = $new_name;
            $temp->$id->val = $new_val;
            $temp->$id->num = $new_num;
            $temp->$id->mark = $new_mark;
            $temp->$id->size = $new_size;
            $temp->$id->category = $new_category;
        } 
        return json_encode($temp);
    } 
    // 步骤数据分步维护控制
    function add_set_up($key, $data = "") { // 编辑步骤新建添加属性
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->set_up, $data);
            if ($this->edit_branch_field($key, "set_up", $val)) {
                $title = "新建设置属性成功！";
                $body = "定义事件步骤设置属性 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加设置属性";
            $com = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值"></textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_set_up","{$key}","true")'>确认添加</button>
html;
            return array('title' => $title, 'body' => $com, "btn" => $button, 'exbtn' => true);
        } 
    } 

    function add_change_genus($key, $data = "") { // 编辑步骤更改属性
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->change_genus, $data);
            if ($this->edit_branch_field($key, "change_genus", $val)) {
                $title = "新建更改属性成功！";
                $body = "定义事件步骤更改属性 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加更改属性";
            $com = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值"></textarea>
html;
            $button = "<button class='btn btn-primary' type='button' onclick='add_branch(\"add_change_genus\",\"{$key}\",\"true\")'>确认添加</button>";
            return array('title' => $title, 'body' => $com, 'btn' => $button, 'exbtn' => true);
        } 
    } 

    function add_change_items($key, $data) { // 编辑步骤更改物品
        $thead = "<tr><th>物品名</th><th>物品ID</th><th>操作</th></tr>";
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->change_items, $data);
            if ($this->edit_branch_field($key, "change_items", $val)) {
                $title = "新建步骤更改物品成功！";
                $body = "定义事件步骤更改物品 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加更改物品";
            $com = $this->searchBox->create_search("输入物品标识", "branch-edit.php", "add_change_items", "goods", "物品名", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function edit_equipment($key, $data) { // 保存装备变动数据信息
        switch ($data['clas']) {
            case "get_equipment":
                $tit = "得到装备";
                $data = json_decode($data['data']);
                foreach($data as $name => $val) {
                    if ($val != "") {
                        $total ++;
                    } else {
                        unset($data->$name);
                    } 
                } 
                $data->total = $total;
                $ret = $this->edit_branch_field($key, "get_equipment", json_encode($data));
                break;
            case "lose_equipment":
                $tit = "失去装备";
                $data = json_decode($data['data']);
                foreach($data as $name => $val) {
                    if ($val != "") {
                        $total ++;
                    } else {
                        unset($data->$name);
                    } 
                } 
                $data->total = $total;
                $ret = $this->edit_branch_field($key, "lose_equipment", json_encode($data));
                break;
        } 
        if ($ret) {
            $title = "定义事件步骤 {$tit} 保存成功！";
            $body = "定义事件步骤 {$tit} 的数据保存成功！";
        } else {
            $title = " 定义事件步骤 {$tit} 保存失败！";
            $body = "定义事件步骤 {$tit} 的数据保存失败！";
        } 
        return array('title' => $title , 'body' => $body);
    } 

    function add_learning_skills($key, $data = "") { // 编辑步骤添加学会技能
        $thead = "<tr><th>技能名</th><th>技能ID</th><th>操作</th></tr>";
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->learning_skills, $data);
            if ($this->edit_branch_field($key, "learning_skills", $val)) {
                $title = "新建学会技能成功！";
                $body = "定义事件步骤学会技能 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加学会技能";
            $com = $this->searchBox->create_search("输入技能标识", "branch-edit.php", "add_learning_skills", "skills", "技能名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_abolish_skills($key, $data = "") { // 编辑步骤添加废除技能
        $thead = "<tr><th>技能名</th><th>技能ID</th><th>操作</th></tr>";
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->abolish_skills, $data);
            if ($this->edit_branch_field($key, "abolish_skills", $val)) {
                $title = "新建废除技能成功！";
                $body = "定义事件步骤废除技能 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加废除技能";
            $com = $this->searchBox->create_search("输入技能标识", "branch-edit.php", "add_abolish_skills", "skills", "技能名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_trigger_task($key, $data = "") { // 编辑步骤添加触发任务
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->trigger_task, $data);
            if ($this->edit_branch_field($key, "trigger_task", $val)) {
                $title = "新建触发任务成功！";
                $body = "定义事件步骤触发任务 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加触发任务";
            $com = $this->searchBox->create_search("输入任务标识", "branch-edit.php", "add_trigger_task", "task", "任务名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_del_task($key, $data = "") { // 编辑步骤添加删除任务
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->del_task, $data);
            if ($this->edit_branch_field($key, "del_task", $val)) {
                $title = "新建删除任务成功！";
                $body = "定义事件步骤删除任务 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤删除任务";
            $com = $this->searchBox->create_search("输入任务标识", "branch-edit.php", "add_del_task", "task", "任务名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_del_task_ok($key, $data = "") { // 编辑步骤添加删除已完成任务
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->del_task_ok, $data);
            if ($this->edit_branch_field($key, "del_task_ok", $val)) {
                $title = "新建删除已完成任务成功！";
                $body = "定义事件步骤删除已完成任务 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤删除已完成任务";
            $com = $this->searchBox->create_search("输入任务标识", "branch-edit.php", "add_del_task_ok", "task", "任务名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_del_task_give_up($key, $data = "") { // 编辑步骤添加删除已放弃任务
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->del_task_give_up, $data);
            if ($this->edit_branch_field($key, "del_task_give_up", $val)) {
                $title = "新建删除已放弃任务成功！";
                $body = "定义事件步骤删除已放弃任务 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤删除已放弃任务";
            $com = $this->searchBox->create_search("输入任务标识", "branch-edit.php", "add_del_task_give_up", "task", "任务名", $key);
            return array('title' => $title , 'body' => $com);
        } 
    } 

    function add_challenge_people($key, $data) { // 编辑步骤挑战人物
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->challenge_people, $data);
            if ($this->edit_branch_field($key, "challenge_people", $val)) {
                $title = "编辑步骤添加挑战人物成功！";
                $body = "编辑步骤挑战人物 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "编辑步骤添加挑战人物";
            $com = $this->searchBox->create_search("输入人物名", "branch-edit.php", "add_challenge_people", "npc", "NPC名称", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function add_adoptive_pets($key, $data) { // 编辑步骤收养宠物
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->adoptive_pets, $data);
            if ($this->edit_branch_field($key, "adoptive_pets", $val)) {
                $title = "编辑步骤添加收养宠物成功！";
                $body = "编辑步骤收养宠物 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "编辑步骤添加收养宠物";
            $com = $this->searchBox->create_search("输入宠物名", "branch-edit.php", "add_adoptive_pets", "pets", "宠物名", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function add_del_pets($key, $data) { // 编辑步骤删除宠物
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->del_pets, $data);
            if ($this->edit_branch_field($key, "del_pets", $val)) {
                $title = "编辑步骤添加删除宠物成功！";
                $body = "编辑步骤删除宠物 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "编辑步骤添加删除宠物";
            $com = $this->searchBox->create_search("输入宠物名", "branch-edit.php", "add_del_pets", "pets", "宠物名", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function add_moving_target($key, $data) { // 编辑步骤移动目标
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->moving_target, $data);
            if ($this->edit_branch_field($key, "moving_target", $val)) {
                $title = '新建移动用户角色成功！';
                $body = "定义事件移动用户角色到 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "选择需要移动玩家的位置";
            $com = $this->searchBox->create_search("搜索地图", "branch-edit.php", "add_moving_target", "mid", "地图名", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function add_user_input($key, $data = "") { // 添加用户输入
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->user_input, $data);
            if ($this->edit_branch_field($key, "user_input", $val)) {
                $title = '新建用户输入成功！';
                $body = "定义事件步骤用户输入 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤添加用户输入";
            $com = '
		字段标识：
		<input type="text" class="form-control" id="add-mark" placeholder="字段标识">
		字段名称：
		<input type="text" class="form-control" id="add-name" placeholder="字段名称">
		最大长度：
		<input type="number" class="form-control" id="add-size" placeholder="最大长度">
		字段值类型：
		<select class="form-control" id="add-category" >
		  <option value="text">字符串</option>
		  <option value="number">数值</option>
		</select>'; 
            // add_branch("task","58","true","add_del_task_ok")
            $btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"add_user_input\",\"{$key}\",\"true\",\"add_user_input\")'>确认添加</button>";
            return array('title' => $title, 'body' => $com . $js, 'btn' => $btn, 'exbtn' => true);
        } 
    } 

    function add_mall_members($key, $data = "") { // 编辑步骤商城VIP
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = $this->add_branch_Packing_data($branch->mall_members, $data);
            if ($this->edit_branch_field($key, "mall_members", $val)) {
                $title = '新建用户输入成功！';
                $body = "定义事件步骤用户输入 {$data['name']} 添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = "修改事件步骤商城vip功能";
            $com .= $this->searchBox->create_search("输入物品标识", "branch-edit.php", "add_mall_members", "mallgoods", "物品名", $key);
            return array('title' => $title, 'body' => $com);
        } 
    } 

    function mall_members_effect($key, $data = "") { // 编辑步骤商城效果时间VIP
        if ($data != "") {
            $branch = $this->get_branch_info($key);
            $val = json_decode($branch->mall_members);
            $temp = json_decode($data);
            $val->discount = $temp->discount;
            $val->eftime = $temp->eftime;
            if ($this->edit_branch_field($key, "mall_members", json_encode($val))) {
                $title = '商城折扣率与时长设置成功！';
                $body = "商城折扣率 {$temp->discount} 与时长 {$temp->eftime} 设置成功！添加成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
        } else {
            $title = '商城折扣率与时长设置失败！';
            $body = "对商城折扣率与时长的设置失败了！";
            return array('title' => $title, 'body' => $body);
        } 
    } 
    // 类公用方法
    function load_weapon_class($name, $type, $data) { // 读取武器列表
        $Default_data = json_decode($data);
        $weapon_class = $this->sys->get_system_config("system", "weapon_class");
        $data = json_decode($weapon_class);
        foreach ($data as $val) {
            if (is_object($val)) {
                $Default_value = "{$type}.we.{$val->id}";
                $value = $Default_data->$Default_value;
                $html .= <<<html
  <div class="form-group">
    <label for="{$type}we{$val->id}" class="col-sm-2">{$name}{$val->name}</label>
    <div class="col-md-6 col-sm-10">
			<textarea type="text" class="form-control" name="{$type}.we.{$val->id}" id="{$type}we{$val->id}" placeholder="{$name}{$val->name}表达式">{$value}</textarea>
    </div>
  </div>
html;
            } 
        } 
        return $html;
    } 

    function load_equip_class($name, $type, $data) { // 读取装备列表
        $Default_data = json_decode($data);
        $equip_class = $this->sys->get_system_config("system", "equip_class");
        $data = json_decode($equip_class);
        foreach ($data as $val) {
            if (is_object($val)) {
                $Default_value = "{$type}.eq.{$val->id}";
                $value = $Default_data->$Default_value;
                $html .= <<<html
  <div class="form-group">
    <label for="{$type}eq{$val->id}" class="col-sm-2">{$name}{$val->name}</label>
    <div class="col-md-6 col-sm-10">
      <textarea type="text" class="form-control" name="{$type}.eq.{$val->id}" id="{$type}eq{$val->id}" placeholder="{$name}{$val->name}表达式">{$value}</textarea>
    </div>
  </div>
html;
            } 
        } 
        return $html;
    } 
} 

?>