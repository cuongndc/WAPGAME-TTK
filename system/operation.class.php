<?php
//游戏操作管理类
namespace game_system;


class operation{
    public $dblj;
    public $sid;
    public $uid;
	public $sys;
    public $map;

    function __construct(){
		global $dblj;
		global $sys;
		global $map;
        $this->dblj =  $dblj;
		$this->sys = $sys;
		$this->map = $map;
        if (!isset($_SESSION['sid'] )) {
            return;
        }
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
	}

function get_operation_info($id){//读取一个操作的属性
	$sql = 'SELECT * FROM `operation` WHERE id = ?;';
	$stmt = $this->dblj->prepare($sql);
    $stmt->execute(array($id));
	return $stmt->fetch(\PDO::FETCH_OBJ);
}

function new_operation(){//新建一个操作，并返回操作的ID号
	$sql = "INSERT INTO `operation`(`name`) VALUES ( ? );";
    $stmt = $this->dblj->prepare($sql);
    $stmt->execute(array('未命名'));
	$idd = $this->dblj->lastInsertId();
    return $idd ;
}

function del_operation($id){//删除一个操作定义
	global $event;
	$operation_info = $this->get_operation_info($id);
	if($operation_info->event){
		$event->del_event($operation_info->event);
	}
	$sql = 'DELETE FROM `operation` WHERE id = ? ;';
	$stmt = $this->dblj->prepare($sql);
    return $stmt->execute(array($id));
}

function save_operation($val){//保存一个操作的修改
	$name = G_trimall($val['name']);
	$name = $name == "" ?'未命名': $name;
	$sql = "UPDATE `operation` SET `name` = ? , `appear` = ? WHERE `id` = ?;";
    $stmt = $this->dblj->prepare($sql);
    $ret = $stmt->execute(array($name,$val['appear'],$val['key']));
	if($ret){
		$arry = array('title' => "操作修改保存成功！");
	}else{
		$arry = array('title' => "操作修改保存失败！");	
	}
	return $arry;
}

function add_operation_field($value){//编辑操作字段值
	$data = $value['data'];
	$key = $value['key'];
	$type = $value['clas'];
	switch($type){
		case 'task':
			$title = "编辑操作添加触发任务成功！";
			$Tips = '触发任务';
			$field = 'task';
		break;
		case 'event':
			$title = "编辑地图添加任务成功！";
			$Tips = '编辑地图';
			$field = 'event';
		break;
	}
	if($data!=""){
		$sql = "UPDATE `operation` SET {$field} = ?  WHERE `id` = ?;";
		$stmt = $this->dblj->prepare($sql);
		$ret = $stmt->execute(array($data['id'],$key));
		 if($ret){
			$body = "编辑操作{$Tips}{$data['name']} 添加成功！";
			return array('title'=>$title ,'body'=>$body,'reload' =>true );
		 }else{
			 
		 }
	 }
}

function del_operation_field($value){//删除操作字段值
	global $task;
	$key = $value['key'];
	$type = $value['clas'];
	$operation_info = $this->get_operation_info($key);
	$task_info = $task->get_task_info($operation_info->task);
	$confirm = $value['confirm'];
	switch($type){
		case 'task':
			$title = "删除操作已添加触发任务成功！";
			$Tips = '删除触发任务';
			$field = 'task';
		break;
		case 'event':
			$title = "删除操作已添加触发事件成功！";
			$Tips = '删除触发事件';
			$field = 'event';
		break;
	}

	if($confirm!=""){
		$sql = "UPDATE `operation` SET {$field} = null  WHERE `id` = ?;";
		$stmt = $this->dblj->prepare($sql);
		$ret = $stmt->execute(array($key));
		 if($ret){
			$body = "删除操作{$Tips}{$task_info->name} 成功！";
			return array('title'=>$title ,'body'=>$body,'repage' =>true );
		 }else{
			
		 }
	 }
}


}
?>