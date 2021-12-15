<?php 
// 系统公共配置操作定义
namespace game_system {
    // 系统配置操作定义
    class task {
        // 任务系统管理部分
        public $dblj;

        function __construct() {
            global $dblj;
            $this->dblj = $dblj;
        } 

        function get_task_info($taskid) { // 根据ID获取一条任务信息
            $sql = "select * from renwu WHERE id = ? ";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($taskid));
            $task = $stmt->fetch(\PDO::FETCH_OBJ);
            return $task;
        } 

        function get_task_name($name, $m = 0, $n = 20) { // 根据任务名获取一条任务信息
            $m = intval($m);
            $n = intval($n);
            $sql = "select * from renwu WHERE name like ? limit $m,$n;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array('%' . $name . '%'));
            $task = $stmt->fetchALL(\PDO::FETCH_OBJ);
            return $task;
        } 

        function get_task_all($m = 0, $n = 20) { // 获取全部任务列表信息
            $m = intval($m);
            $n = intval($n);
            $m = ($m-1) * $n;
            $data = json_decode('{}');
            $sql = "select count(*) as num from renwu";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute();
            $task = $stmt->fetch(\PDO::FETCH_OBJ);
            $data->num = intval($task->num);
            $sql = "select * from renwu limit $m,$n;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute();
            $data->data = $stmt->fetchALL(\PDO::FETCH_OBJ);
            return $data;
        } 

        function set_task_add($data) { // 新建一条任务
            $obj = json_decode($data);
            $sql = "INSERT INTO `renwu`( `name`, `rwtype`, `save`, `rwrandom`, `rwdiscard`, `rwtrigger`, `rwaccept`, `rwunacceptable`, `rwUnfinished`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($obj->task_name, $obj->task_rwtype, 1, $obj->task_rwrandom, $obj->task_rwdiscard, $obj->task_rwtrigger, $obj->task_rwaccept, $obj->task_rwunacceptable, $obj->task_rwUnfinished));
            $task = $stmt->fetch(\PDO::FETCH_OBJ);
            $id = $this->dblj->lastInsertId();
            return $id;
        } 

        function set_task_edit($task_id, $task_odj) { // 维护一条任务
            $sql = "UPDATE `renwu` SET `name` = ?, `rwtype` = ?, `save` = ?, `rwrandom` = ?, `rwdiscard` = ?, `rwtrigger` = ?, `rwaccept` = ?, `rwunacceptable` = ?, `rwUnfinished` = ? ,rwwork = ?  WHERE `id` = ? ;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($task_odj->task_name , $task_odj->task_rwtype , 1 , $task_odj->task_rwrandom , $task_odj->task_rwdiscard , $task_odj->task_rwtrigger , $task_odj->task_rwaccept , $task_odj->task_rwunacceptable , $task_odj->task_rwUnfinished , $task_odj->task_rwwork , $task_id));
            return $ret;
        } 

        function set_task_field($taskid, $type, $valve = null) { // 更新一个任务字段
            $alert_open = alert_open;
            switch ($type) {
                case "rwKilling":
                    $field = "rwKilling";
                    break;
                case "rwseek":
                    $field = "rwseek";
                    break;
                case "accept":
                case "rwevent_accept":
                    $field = "rwevent_accept";
                    break;
                case "discard":
                case "rwevent_discard":
                    $field = "rwevent_discard";
                    break;
                case "complete":
                case "rwevent_complete":
                    $field = "rwevent_complete";
                    break;
            } 
            if ($valve == null) {
                $sql = "UPDATE `renwu` SET $field = null WHERE `id` = ?;";
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute(array($taskid));
            } else {
                $sql = "UPDATE `renwu` SET $field = ? WHERE `id` = ?;";
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute(array($valve, $taskid));
            } 
            return $ret;
        } 

        function edit_task_info($task_id) { // 编辑任务数据
            $alert_open = alert_open;
            $task_id = intval($task_id);
            if ($task_id == 0) {
                $title = "定义 \"" . $task->name . "\"的任务属性";
                $html .= "无效的任务编辑信息";
            } else {
                $task_info = $this->get_task_info($task_id);
            } ;
            $title = "定义 \"" . $task_info->name . "\"的任务属性";
            $html .= '
<form id="task">
<div class="row">
  <div class="col-xs-4"><h4>任务标识：</h4></div>
  <div class="col-xs-8"><p class="form-control">';
            if ($task_info->id) {
                $html .= $task_info->id;
            } else {
                $html .= $task_id;
            } 
            $html .= '</p></div>
  <input type="hidden" name="task_rwid" value="';
            if ($task_info->id) {
                $html .= $task_info->id;
            } else {
                $html .= $task_id;
            } 
            $html .= '">
</div>
<div class="row">
  <div class="col-xs-4"><h4>任务名称：</h4></div>
  <div class="col-xs-8"><input type="text" name="task_name" class="form-control" value="' . $task_info->name . '"></div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>任务类型：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwtype">
		<option value="Killing"';
            if ($task_info->rwtype == "Killing") {
                $html .= "selected";
            } 
            $html .= '>杀人任务</option>
		<option value="Work"';
            if ($task_info->rwtype == "Work") {
                $html .= "selected";
            } 
            $html .= '>办事任务</option>
		<option value="Seek"';
            if ($task_info->rwtype == "Seek") {
                $html .= "selected";
            } 
            $html .= '>寻物任务</option>
	</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>是否随机：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwrandom">
		<option value=1';
            if ($task_info->rwrandom == 1) {
                $html .= " selected";
            } 
            $html .= ' >是</option>
		<option value=0';
            if ($task_info->rwrandom == 0) {
                $html .= " selected";
            } 
            $html .= '>否</option>
	</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>是否可放弃：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwdiscard">
		<option value=1';
            if ($task_info->rwdiscard == 1) {
                $html .= " selected";
            } 
            $html .= '>是</option>
		<option value=0';
            if ($task_info->rwdiscard == 0) {
                $html .= " selected";
            } 
            $html .= '>否</option>
	</select>
  </div>
</div>
触发条件：	
<textarea class="form-control" rows="3" name="task_rwtrigger" >' . $task_info->rwtrigger . '</textarea>
接受条件：	
<textarea class="form-control" rows="3" name="task_rwaccept" >' . $task_info->rwaccept . '</textarea>
不能接受提示语:
<textarea class="form-control" rows="3" name="task_rwunacceptable" >' . $task_info->rwunacceptable . '</textarea>
未完成提示语：	
<textarea class="form-control" rows="3" name="task_rwUnfinished" >' . $task_info->rwUnfinished . '</textarea>
<div class="container"><p></p>';
            switch ($task_info->rwtype) {
                case "Killing":
                    $bt = "任务击杀列表：";
                    $va = '</form><a href="task-mold.php?id=' . $task_info->id . '&type=killing" class="btn btn-primary btn-block">';
                    $list = json_decode($task_info->rwKilling);
                    if (is_object($list)) {
                        foreach($list as $key => $value) {
                            if (is_object($value)) {
                                $i++;
                            } 
                        } 
                    } 
                    if ($i > 0) {
                        $va .= "修改($i)";
                    } else {
                        $va .= "增加";
                    } 
                    $va .= '</a>';
                    break;
                case "Work":
                    $bt = "办事任务标识：";
                    $va = '<input type="text" name="task_rwwork" class="form-control" value="' . $task_info->rwwork . '"></form>';
                    break;
                case "Seek":
                    $bt = "寻物物品列表：";
                    $va = '</form><a href="task-mold.php?id=' . $task_info->id . '&type=seek" class="btn btn-primary btn-block">';
                    $list = json_decode($task_info->rwseek);

                    if (is_object($list)) {
                        foreach($list as $key => $value) {
                            if (is_object($value)) {
                                $i++;
                            } 
                        } 
                    } 
                    if ($i > 0) {
                        $va .= "修改($i)";
                    } else {
                        $va .= "增加";
                    } 

                    $va .= '</a>';
                    break;
            } 
            $html .= <<<html
	<div class="row">
		<div class="col-xs-3"><h4>{$bt}</h4></div>
		<div class="col-xs-5">{$va}</div>
	</div>
	</div>
	<div class="container"><p></p>
	<div class="row">
		<div class="col-xs-3"><h4>接受事件：</h4></div>
html;
            if ($task_info->rwevent_accept == "") {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=task&key={$task_info->id}&clas=accept' >添加事件</a></div>";
            } else {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=task&key={$task_info->id}&clas=accept'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open}  onClick=\"del_event('task','accept','{$task_info->id}','{$task_info->rwevent_accept}')\">删除事件</button></div>";
            } 

            $html .= '</div></div><div class="container"><p></p>
		<div class="row">
		  <div class="col-xs-3"><h4>放弃事件：</h4></div>';

            if ($task_info->rwevent_discard == "") {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=task&key={$task_info->id}&clas=discard' >添加事件</a></div>";
            } else {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=task&key={$task_info->id}&clas=discard'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open} onClick=\"del_event('task','discard','{$task_info->id}','{$task_info->rwevent_discard}')\">删除事件</button></div>";
            } 

            $html .= '</div></div><div class="container"><p></p>
		<div class="row">
		  <div class="col-xs-3"><h4>完成事件：</h4></div>';
            if ($task_info->rwevent_complete == "") {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=task&key={$task_info->id}&clas=complete' >添加事件</a></div>";
            } else {
                $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=task&key={$task_info->id}&clas=complete'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open} onClick=\"del_event('task','complete','{$task_info->id}','{$task_info->rwevent_complete}')\">删除事件</button></div>";
            } 
            $html .= <<<html
	</div></div><br>
	<button class="btn btn-primary" type="button" {$alert_open} onclick="save_task('$task_id')">保存修改</button><p></p>
	<hr>
	<div class="btn-group">
	<button class="btn btn-info " type="button">查看定义数据</button>
	<button class="btn btn-primary" type="button">导入定义数据</button>
	</div>
html;
            $data = json_decode('{}');
            $data->title = $title;
            $data->body = $html;
            return $data;
        } 

        function del_task_id($taskid) { // 删除一条任务
            global $event;
            $task_info = $this->get_task_info($taskid);
            $sql = "delete from renwu where id= ? ;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($task_info->id));
            if ($ret) {
                if ($task_info->rwevent_accept) {
                    $event->del_event($task_info->rwevent_accept);
                } 
                if ($task_info->rwevent_discard) {
                    $event->del_event($task_info->rwevent_discard);
                } 
                if ($task_info->rwevent_complete) {
                    $event->del_event($task_info->rwevent_complete);
                } 
            } 

            return $ret;
        } 

        function rw_player_get_all() {
            $sql = "select * from playerrenwu WHERE sid = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($this->sid));
            $task = $stmt->fetchAll(\PDO::FETCH_OBJ);
            return $task;
        } 

        function rw_player_date_get_all() {
            $sql = "select * from playerrenwu WHERE sid = ? AND rwlx = 2";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($this->sid));
            $task = $stmt->fetchAll(\PDO::FETCH_OBJ);
            return $task;
        } 

        function del_player_task($sid, $rwid , $type = null) { // 玩家放弃一条任务
            if (isset($type)) {
                $sql = "delete from playerrenwu WHERE sid = ? AND task_id = ? AND state= ?" ;
                $stmt = $this->dblj->prepare($sql);
                $bool = $stmt->execute(array($sid , $rwid , $type));
                return $bool;
            } 
            $sql = "delete from playerrenwu WHERE sid = ? AND task_id = ?";
            $stmt = $this->dblj->prepare($sql);
            $bool = $stmt->execute(array($sid , $rwid));
            return $bool;
        } 

        function complete_player_task($sid, $rwid , $type = null) { // 玩家完成一条任务
            global $npc;
            global $goods;
            global $player;
			
            $play_task_info = $this->get_player_task_info($sid, $rwid);
            $task_info = $this->get_task_info($play_task_info->task_id);
			$ret = true;
            switch ($play_task_info->type) {
                case 'Work':
                case 'work':
                    $obj = json_decode($task_info->rwwork);
						$player->set_player_us($sid, $task_info->rwwork);
                    break;
                case 'Seek':
                case 'seek':
                    $obj = json_decode($task_info->rwseek);
                    foreach($obj as $val) {
                        if (is_object($val)) {
                            $ret = $goods->reduce_player_goods($val->id , 2 , $val->num);
                        } 
						if (!$ret) {
                            break;
                        } 
                    } ;
                    break;
                case 'Killing':
                case 'killing':
                    $obj = json_decode($task_info->rwKilling);
                    foreach($obj as $val) {
                        if (is_object($val)) {
                            $kill_npc = $npc->get_killtask_npc($sid, $val->id);
                            if ($kill_npc) {
                                $ret = $npc->del_killtask_npc($sid, $kill_npc->id);
                                if (!$ret) {
                                    break;
                                } 
                            } 
                        } 
                    } 
                    break;
            } 
            if (!$ret) {
                return false;
            } 
            if (isset($type)) {
                $sql = "delete from playerrenwu WHERE sid = ? AND task_id = ? AND state= ?" ;
                $stmt = $this->dblj->prepare($sql);
                $bool = $stmt->execute(array($sid , $rwid , $type));
                return $bool;
            } 
            $sql = "delete from playerrenwu WHERE sid = ? AND task_id = ?";
            $stmt = $this->dblj->prepare($sql);
            $bool = $stmt->execute(array($sid , $rwid));
            return $bool;
        } 

        function rw_get_player_wwc($sid) {
            $sql = "select * from playerrenwu WHERE sid = ? AND state <> 3";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($sid));
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 

        function rw_get_player_pve_wwc_gid($gid) {
            $sql = "select * from playerrenwu WHERE sid = ? AND type = 2 AND rwzt != 3 AND rwyq = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($this->sid , $gid));
            $task = $stmt->fetchAll(\PDO::FETCH_OBJ);
            return $task;
        } 

        function rw_get_player_pve_wwc_dj($djid) {
            $sql = "select * from playerrenwu WHERE sid = ? AND rwzl = 1 AND rwzt != 3 AND rwyq = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($this->sid , $djid));
            $task = $stmt->fetchAll(\PDO::FETCH_OBJ);
            return $task;
        } 

        function rw_player_get_info($sid, $rwid) {
            $sql = "select * from playerrenwu WHERE sid = ? AND task_id = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($sid , $rwid));
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } 

        function get_player_task_info($sid, $task_id) {
            $sql = "select * from playerrenwu WHERE sid = ? AND task_id = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($sid , $task_id));
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } 

        function rw_update_pve($gid) {
            $bool = $this->rw_update_pve_sid($gid , $this->sid);
            return $bool;
        } 

        function rw_update_dj($djid , $count) {
            $bool = $this->rw_update_dj_sid($djid , $count , $this->sid);
            return $bool;
        } 

        function rw_update_rwzt() {
            $sql = "update playerrenwu set rwzt = 2 WHERE sid = ? AND rwnowcount >= rwcount AND rwzt = 1";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($this->sid));
            $bool = $stmt->rowCount();
            return $bool;
        } 

        function rw_update_rwzt_rwid($rwid , $rwzt) {
            $sql = "update playerrenwu set rwzt = ? WHERE sid = ? AND rwnowcount >= rwcount AND rwid = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($rwzt , $this->sid , $rwid));
            $bool = $stmt->rowCount();
            return $bool;
        } 

        function rw_insert_player($task_info) {
            global $npc;
            global $player_info;
            $obj = $task_info;
            $sql = "insert into playerrenwu( task_id ,name , type , rwdj , rwzb , rwexp , rwyxb , rwUnfinished , sid , rwzt ,  rwyq , rwcount ,rwnowcount , rwlx , rwyp ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ? , ? , ? )";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($obj->id, $obj->name, $obj->rwtype, $rwdj, $rwzb, $rwexp, $rwyxb, $obj->rwUnfinished , $player_info->sid, $rwzt, $rwyq, $rwcount , $rwnowcount , $rwlx , $rwyp));
            $ret = $stmt->rowCount();
			if($task_info->rwKilling){
            if ($obj->rwtype == 'Killing' || $obj->rwtype == 'killing') {
                $obj = json_decode($task_info->rwKilling);
                foreach($obj as $npc_kill) {
                    if (is_object($npc_kill)) {
                        $npc->insert_killtask_npc($player_info->sid, $npc_kill->id, $task_info->id, $npc_kill->num);
                    } 
                } 
            } 
			}
            if ($rwzl == 1) {
                $this->rw_update_dj($rwyp , $rwnowcount);
            } 
            return $ret;
        } 

        function insert_player_task($task_id,$task_desc = "") {
            $task_info = $this->get_task_info($task_id);
            $day = date('d');
            $task_info->rwnowcount = 0;
            $task_info->rwzt = 1;
            if ($task_info->rwzl == 3) {
                $task_info->rwnowcount = $task_info->rwcount;
                $task_info->rwzt = 2;
            } 
			if(!empty($task_desc)){
				$task_info->rwUnfinished = $task_desc;
			}
            $bool = $this->rw_insert_player($task_info);
            return $bool;
        } 

        function rw_com($rwid) {
            $bool = $this->rw_update_rwzt_rwid($rwid , 3);
            return $bool;
        } 
    } 
} 

?>