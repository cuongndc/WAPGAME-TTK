<?php
require_once "user_rights.php";

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$dis_name = $_SESSION['dis'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
$_SESSION['dis'] = $dis_name;
$_SESSION['event'] = "task";

$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);

if(isset($value['basic'])){
	switch($value['type']){
		case "Selection":
			$arry = add_challenge_people($value['key'],$value['clas']);
		break;
		case "search":
			$arry =  $searchBox->search($value);
		break;
		case "add_field":
			$arry = add_renwu_field($value);
		break;
		case "editing_step":
			$arry = editing_step($value);
		break;
		case 'Reset':
			$arry =  $searchBox->reloading('task',$value['obj'],$value['id']);
			$arry = array('body'=>$arry);
		break;
		case 'page':
		$task = $task->get_task_all($value['page'],$value['recPerPage']);
		if(is_object($task)){
foreach($task->data as $obj){
	$type = '';
	switch($obj->rwtype){
		case 'Killing':
		$type = '杀人';
		break;
		case 'Work':
		$type = '办事';
		break;
		case 'Seek':
		$type = '寻物';
		break;
		}
	$html .=  <<<html
	<tr>
		<td>{$obj->id}</td>
		<td>{$obj->name}</td>
		<td>{$type}</td>
		<td><p class="p1">{$obj->rwUnfinished}</p></td>
		<td><a class='btn btn-primary' href="task.php?type=edit&id={$obj->id}">编辑</a>
		<button class='btn btn-danger' type='button' data-position="100px" data-toggle="modal" data-target="#ajax-alert"  onclick='del_task("{$obj->id}")'>删除</button></td>
	</tr>
html;
		}
		}
		$arry = array('html'=>$html,'recTotal'=>$task->num);
		break;
		case "new":
			$arry = new_renwu($value);
		break;
		case "edit":
			$arry = edit_renwu($value);
		break;
		case "del":
		$task_info = $task->get_task_info($value['task_id']);
		if($value['confirm'] == 'true' ){
			$bool = $task->del_task_id($task_info->id);
			if($bool){
				$arry = array( 'title'=>"任务删除成功！"  , 'body'=>"删除当前选中任务：<h3>{$task_info->name}({$task_info->id})</h3>删除成功！" ,'repage' =>true );
			}else{
				$arry = array( 'title'=>"任务删除失败！"  , 'body'=>"删除当前选中任务：<h3>{$task_info->name}({$task_info->id})</h3>删除删除失败！" ,'repage' =>true );
			}
		}else{
			$arry = array( 
				'title'	=>"确认删除当前选中任务？",
				'body'	=>"确认删除当前选中任务：<h3>{$task_info->name}({$task_info->id})</h3>这个任务吗？" ,
				'btn'	=>"<button class='btn btn-danger' type='button' {$alert} onclick='del_task(\"{$task_info->id}\",\"true\")'>删除</button>",
				'exbtn'	=>true 
				);
		}
	break;
	case "save":
		$arry = save_renwu($value);
	break;
  }
	ajax_alert($arry);
}

function add_challenge_people($key,$data){//编辑步骤挑战人物
	global $searchBox;
	if($data=='goods'){
		$title = "编辑任务寻找物品";
		$com = $searchBox->create_search("输入物品名","task-gl.php","task","goods","物品名",$key);
	}else{
		$title = "编辑任务击杀人物";
		$com = $searchBox->create_search("输入电脑人物名","task-gl.php","task","npc","电脑人物名",$key);
	}
	return array('title'=>$title,'body'=>$com);
}

function add_renwu_field($value){//编辑任务字段
	global $task;
	$data = $value['data'];
	$key = $value['key'];
	$type = $value['clas'];
	switch($type){
		case 'npc':	
			$type = "rwKilling";
			$title = "编辑任务添加击杀人物成功！";
			$Tips = '击杀人物 ';
		break;
		case 'goods':	
		$type = "rwseek";
		$title = "编辑任务添加寻找物品成功！";
		$Tips = '寻找物品 ';
		break;
	}
	if($data!=""){
		 $obj = $task->get_task_info($key);
		 $val = add_branch_Packing_data($obj->$type,$data);
		 if($task->set_task_field($key,$type,$val)){
			$body = "编辑步骤{$Tips}{$data['name']} 添加成功！";
			return array('title'=>$title ,'body'=>$body ,'reloading'=>true);
		 }else{
			 
		 }
	 }
}

function analysis($id){//重新加载npc列表
	global $game;
	$obj=$game->get_npc_info($id);
	$new_obj = "npc";
	$json= $obj->npc_task;
	$list=json_decode($json);
$html .='<table class="table table-condensed table-bordered">
  <thead>
    <tr>
      <th>任务ID</th>
      <th>任务名</th>
      <th>任务类型</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>';
	if($list){
	foreach($list as $obj ){
		$task=$game->get_task_info($obj->task_id);
		if($task->rwid !=""){
		switch($task->rwtype){
			case "Work":
			$task_type="办事任务";
			break;
			case "Killing":
			$task_type="杀人任务";
			break;
			case "Seek":
			$task_type="寻物任务";
			break;
			case "":
			$task_type="未定义";
			break;
		}
		$html .=<<<html
		<tr>
      <td>$task->rwid </td>
      <td>$task->rwname</td>
      <td>$task_type</td>
      <td>
	  <button class="btn btn-primary" type="button" onclick="edit_task('$new_obj','$id','$task->rwid')" >编辑</button>
	  <button class="btn btn-danger" type="button" onclick="del('$new_obj','$id','$task->rwid')" >删除</button>
	  </td>
		</tr>
html;
		}
	}
	}
$html .= '</tbody></table>';
	return $html;
}

function ParsingPage($dis_name){//重新加载页面布局列表
	global $game;
	$layout=$game->dis_get($dis_name,"text");
	$obj=json_decode($layout);
	foreach( $obj as $key => $value) {
		if(is_object ($value )){
		$id=$key;
		$i++;
		foreach( $value  as $key => $value) {
		  if($key=="dis_string"){
		  $dis .= "<a onclick=\"edit('$id')\" class='link'>$i . $value<i class=\"icon icon-level-down icon-rotate-90\"></i></a><br>";}
			}
		}
	}
	return $dis;
}

function load_Killing($rwKilling,$rwid){//重新加载击杀列表
	global $game;
	$test=json_decode($rwKilling);
	if(isset($test)){
	foreach($test as $key => $value){
	if(is_object($value)){
		$npc = $game->get_npc_info($value->npcid);
	$list.= <<<html
	<tr>
	  <td>{$npc->npc_name}({$npc->npc_id})</td>
      <td>{$npc->npc_lvl}</td>
	  <td>{$value->number}</td>
	  <td>
	  <button class='btn btn-primary' onclick="operation('edit','{$rwid}','{$key}')">编辑</button>
	<button class="btn btn-danger"  onclick="operation('del','{$rwid}','{$key}')">删除</button>
	  </td>
	</tr>
html;
	}
	}
  }
return $list;
}

function load_seek($rwseek,$rwid){//重新加载寻物列表
	global $goods;
	$test=json_decode($rwseek);
	if(isset($test)){
	foreach($test as $key => $value){
	if(is_object($value)){
		$good = $goods->get_goods_id($value->goodsid);
		$goodtype = $goods->get_goods_type($good->djtype);
$list.= <<<html
  <tr>
	<td>{$good->name}({$good->id})</td>
    <td>{$goodtype['1']}</td>
	<td>$value->number</td>
	<td>
	<button class="btn btn-primary" onclick="operation('edit','{$rwid}','{$key}')">编辑</button>
	<button class="btn btn-danger"  onclick="operation('del','{$rwid}','{$key}')">删除</button>
	</td>
  </tr>
html;
	}
	}
  }
return $list;
}

function new_renwu($value){//新建任务数据
$title = "定义新的任务";
$html = <<<html
<form id="task">
<div class="row">
  <div class="col-xs-4"><h4>任务名称：</h4></div>
  <div class="col-xs-8"><input type="text" name="task_name" class="form-control"></div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>任务类型：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwtype">
		<option value="Killing">杀人任务</option>
		<option value="Work">办事任务</option>
		<option value="Seek">寻物任务</option>
	</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>是否随机：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwrandom">
		<option value=1>是</option>
		<option value=0>否</option>
	</select>
  </div>
</div>
<div class="row">
  <div class="col-xs-4"><h4>是否可放弃：</h4></div>
  <div class="col-xs-8">
	<select class="form-control" name="task_rwdiscard">
		<option value=1>是</option>
		<option value=0>否</option>
	</select>
  </div>
</div>
触发条件：	
<textarea class="form-control" rows="3" name="task_rwtrigger" ></textarea>
接受条件：	
<textarea class="form-control" rows="3" name="task_rwaccept" ></textarea>
不能接受提示语:
<textarea class="form-control" rows="3" name="task_rwunacceptable" ></textarea>
未完成提示语：	
<textarea class="form-control" rows="3" name="task_rwUnfinished" ></textarea></form>
html;
	$btn = '<button class="btn btn-primary" type="button" onclick="save_task()">确认新建</button>';
	return array('title'=>$title,'body'=>$html ,"btn"=>$btn,'exbtn'=>true);
}

function save_renwu($value){//保存任务
	global $task;
	$task_id = intval($value['task_id']);
	if($task_id == 0){ 
		$id = $task->set_task_add($value['data']);
		if($id != 0 ){
			return array('title'=>'任务新建成功！','body'=>'新建任务成功！','repage'=>true);
		}else{
			return array('title'=>'任务新建失败！','body'=>'新建任务请求失败了，请稍后重试！');
		}
	}
	$obj = json_decode($value['data']);
	if($obj->task_name == ""){$obj->task_name = "未命名";}
	if($task->set_task_edit($task_id ,$obj)){
		$name = $obj->task_name;
		$title = "任务数据已保存！";
		$html = "<p class='text-red'>定义 \"{$name}\" 的任务属性成功！</p>";
	}
	return array('title'=>$title,'body'=>$html);
}



?>