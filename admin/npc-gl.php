<?php
require_once "user_rights.php";

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;


$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);
if(isset($value['basic'])){
  switch ($value['type']){
	case "Selection":
		$arry = add_challenge_people($value['key'],$value['clas']);
	break;
	case "add_field":
		$arry = add_renwu_field($value);
	break;
	case "addobj":
		$arry = addobj($value);
	break;
	case "search":
		$arry =  $searchBox->search($value);
	break;
	case 'save_npc':
		$arry = $attribute->edit_record('npc',$value['data']);
	break;
	case 'save_drop':
		$arry = $npc->edit_drop($value['data']);
	break;
	case "editing_step":
		$arry = editing_step($value);
	break;
	case 'page':
		$test= $map->get_qy_all($value['page'],$value['recPerPage']);
		foreach($test->data as $obj){
	$html .= "<tr>
	  <td>{$obj->qyname}</td>
      <td>{$obj->qyid}</td><td>";
	$arry = $npc->get_qy_npc($obj->qyid);
	if($arry){$html .= $arry->num;}
	$html .=  "</td><td><a class='btn btn-primary' href='npc-list.php?qyid={$obj->qyid}&page={$page}'>查看区域NPC</a></td>
	</tr>
	";
	}
		$arry = array('html'=>$html,'recTotal'=>$test->num);
	break;
	case "edit":
		$arry = edit_npc($value['com'],$value['npcid']);
	break;
	case "Selection":
		echo <<<html
		<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">选取一个电脑人物</h4>
      </div>
      <div class="modal-body">
	  <div class="container"><!--[选择区域]-->
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索NPC</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" id="quyu_xs" onchange="qy_sear('edit')"> 
				<option value="0">请选择一级区域</option>
html;
				$Obj_game_qy =$map->get_qy_all();
				foreach($Obj_game_qy[1] as $obj){
				echo "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
				}
		  echo '</select>
		</div>
	  </div>
	  <div id="edit_sear"></div>
	</div>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
	</div>';
		break;
		case "new":
			if(isset($value['data'])){
				$id = $attribute->add_record('npc',$value['data']);
				if($id>0){
					$arry = array('title'=>'新建NPC：','body'=>'新的NPC已成功建立！','repage' =>true );
				}else{
					$arry = array('title'=>'新建NPC：','body'=>'新建NPC的操作失败了！','repage' =>true );
				}
			}else{
				$default = array('qy'=>$value['area']);
				$html .=  $attribute->get_attribute_new("npc",array(),$default);
				$btn = '<button type="button" onClick="new_npc(\''.$value['area'].'\',\'true\')" class="btn btn-primary" >确认创建</button>';
				$arry = array('title'=>'编辑新建NPC的属性：','body'=>$html,'btn'=>$btn,'exbtn'=>true);
			}
		break;
		case 'del':
			$npc_info = $npc->get_npc_info($value['id']);
			if(isset($value['confirm'])){
				$val = $npc->del_npc_id($npc_info->id);
				if($val){
					$arry = array('title'=>'删除NPC：','body'=>'NPC已成功删除！','repage' =>true );
				}else{
					$arry = array('title'=>'删除NPC：','body'=>'NPC删除失败了！','repage' =>true );
				}
			}else{
				$html .= "<h4>NPC名：<span class='text-danger'>{$npc_info->name}({$npc_info->id})</span></h4>";
				$html .= "<h4>NPC等级：<span class='text-danger'>{$npc_info->lvl}</span></h4>";
				$html .= "<h4>NPC描述：<span class='text-danger'>{$npc_info->desc}</span></h4>";
				$btn = '<button type="button" onClick="del_npc(\''.$value['id'].'\',\'true\')" class="btn btn-danger" >确认删除</button>';
				$arry = array('title'=>'删除NPC：','body'=>$html,'btn'=>$btn,'exbtn'=>true);
			}
		break;
		case 'Reset':
			$arry =  $searchBox->reloading('npc',$value['obj'],$value['id']);
			$arry = array('body'=>$arry);
		break;
		case "new_clas":
			$arry = new_npc_clas($value); 
		break;
	}
	ajax_alert($arry);
	}

function add_challenge_people($key,$data){//编辑步骤挑战人物
	global $searchBox;
	if($data=='goods'){
		$title = "编辑NPC掉落物品";
		$com = $searchBox->create_search("输入物品名","npc-gl.php","npc","goods","物品名",$key);
	}else{
		$title = "编辑NPC掉落装备";
		$com = $searchBox->create_search("输入装备名","npc-gl.php","npc","drop_equip","装备名",$key);
	}
	return array('title'=>$title,'body'=>$com);
}
	
function addobj($val){//添加地图相关附属属性
	global $searchBox;
	$key = $val['mid'];
	switch($val['clas']){
		case 'edit_skills':
			$title = "编辑NPC添加人物技能：";
			$com = $searchBox->create_search("输入技能名","npc-gl.php","npc","edit_skills","技能名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'task':
			$title = "编辑NPC添加任务：";
			$com = $searchBox->create_search("输入任务名","npc-gl.php","npc","task","任务名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
	}
	return $arry;
}
	
function edit_npc($com,$npcid){//加载NPC编辑器分类菜单
	global $npc;
	global $attribute;
	global $searchBox;
	global $event;
	global $equip;
	$alert_open = alert_open;
	$npc_info = $npc->get_npc_info($npcid);
	switch($com){
		case 'attr':
			$html = '<h4>编辑NPC"'.$npc_info->name .'"的属性：</h4>';
			$html .=  $attribute->get_attribute_edit("npc",$npc_info->id,array(),array());
			$html .= "<button type='button' onClick='save_npc()' class='btn btn-primary' {$alert_open}  >保存修改</button>";
			return array('title'=>'编辑NPC信息：','body'=>$html,);
		break;
		case 'operation':
			$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_array_list('npc',$thead ,$npc_info->operation,$npc_info->id,"operation");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>定义电脑人物"{$npc_info->name}"的操作：</h3></td><td style="text-align:right">
	<button class="btn btn-primary" onClick="new_operation('add','npc','{$npc_info->id}')" type="button">添加操作</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'event':
			return $event->load_event_list('npc',$npc_info->id);
		break;
		case 'task':
			$thead = "<tr><th>任务ID</th><th>任务名</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('npc',$thead ,$npc_info->task,$npc_info->id,"task");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑NPC"{$npc_info->name}"的任务设置：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('task','{$npc_info->id}')" {$alert_open} class="btn btn-primary">添加任务</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'skill':
			$thead = "<tr><th>技能ID</th><th>技能名</th><th>等级表达式</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list("npc",$thead ,$npc_info->skills,$npc_info->id,"edit_skills");
			$body = <<<html
<table  class="table"> 
<tr><td><b>编辑NPC"{$npc_info->name}"的技能设置：</b></td><td style="text-align:right">
	<button type="button" onclick="addobj('edit_skills','{$npc_info->id}')" {$alert_open} class="btn btn-primary"> 添加技能</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
		break;
		case 'equip':
			$npc->load_equip_list($npc_info->id);
			$npc_info= $npc->get_npc_info($npc_info->id);
			return $equip->read_equip_list($npc_info,$npc_info->equip_class,$npc_info->equip_val);
		break;
		case 'drop':
			$goods = json_decode($npc_info->drop_items);
			if(!isset($goods)){ $goods_total = 0; }else{$goods_total= $goods->total;};
			$equip = json_decode($npc_info->drop_equip);
			if(!isset($equip)){ $equip_total = 0; }else{$equip_total= $equip->total;};
 			$title ='设计电脑人物死后掉落定义：';
			$body = <<<html
			<h3>设计电脑人物死后掉落定义：</h3>
<form id="drop">
	<input type="hidden" name="id" value="{$npc_info->id}">
  <div class="form-group">
    <label for="exp">掉落经验表达式</label>
    <textarea class="form-control" rows="3" name="drop_exp"  placeholder="掉落经验表达式">{$npc_info->drop_exp}</textarea>
  </div>
  <div class="form-group">
    <label for="money">掉钱表达式</label>
	<textarea class="form-control" rows="3" name="drop_money"  placeholder="掉钱表达式">{$npc_info->drop_money}</textarea>
  </div>
  <table id="branch">
	<tbody>
	<tr>
		<td><span class="lead">掉落物品:</span> </td>
		<td><a class="btn btn-primary" href="npc-mold.php?type=goods&id={$npc_info->id}">修改({$goods_total})</a></td>
	</tr>
	</tbody>	
  </table>
  <div class="form-group">
    <label for="exp">爆装条件表达式</label>
	<textarea class="form-control" rows="3" name="drop_equip_factor"  placeholder="爆装条件表达式">{$npc_info->drop_equip_factor}</textarea>
  </div>
  <div class="form-group">
    <label for="money">爆装掉钱表达式</label>
	<textarea class="form-control" rows="3" name="drop_money_factor"  placeholder="爆装掉钱表达式">{$npc_info->drop_money_factor}</textarea>
  </div>
   <table id="branch">
	<tbody>
	<tr>
		<td><span class="lead">爆装物品:</span> </td>
		<td><a class="btn btn-primary" href="npc-mold.php?type=equip&id={$npc_info->id}">修改({$equip_total})</a></td>
		</tr>
	</tbody>	
  </table>
  <button type="button" class="btn btn-primary" {$alert_open} onclick="save_drop();return false;">提交</button>
</form>
html;
		break;
		case 'copy':
			$html ="复制NPC";
			return array('body'=>$html);
		break;
		case 'import':
			$html =<<<html
	<h3>[编辑NPC<b>"{$npc_info->name}"</b> 导入NPC设置]</h3>
	NPC设置数据:<textarea class="form-control" rows="15"></textarea>	<br>
	<button type="button" onclick="map_edit()" class="btn btn-primary">确认导入</button>
	<hr>
html;
			return array('body'=>$html);
		break;
		case 'export':
			$html =<<<html
	<h3>[编辑NPC<b>"{$npc_info->name}"</b> 导出NPC设置]</h3>
	NPC设置数据:<textarea class="form-control" rows="15"></textarea>
	<br>
html;
			return array('body'=>$html);
		break;
		case 'update':
			$html ="更新NPC";
			return array('body'=>$html);
		break;
		
	}
	return array('title'=>$title,'body'=>$body);
}

function new_npc_clas($value){
	$key = $value['id'];
	switch($value['clas']){
		case 'open':
			$body = <<<html
<form>
  <div class="form-group">
    <label for="exampleInputAccount1">操作名称</label>
	<textarea class="form-control" rows="3" placeholder="可以输入多行文本"></textarea>
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">显示条件</label>
	<textarea class="form-control" rows="3" placeholder="可以输入多行文本"></textarea>
  </div>
  <div class="form-group">
    <div class="input-group">
      <span class="input-group-addon">快捷键值</span>
      <input type="number" class="form-control" id="exampleInputMoney1" placeholder="">
    </div>
  </div>
  <table id="branch">
			<tbody><tr>
			<td><span class="lead">触发事件:</span> </td>
			<td><a class="btn btn-primary" href="branch-edit.php?type=edit&amp;key=14">修改</a></td>
			<td><button class="btn btn-danger" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onclick="edit_branch('del','14')">删除</button></td>
			<td></td>
			</tr>			<tr>
			<td><span class="lead">触发任务:</span> </td>
			<td><a class="btn btn-primary" href="branch-edit.php?type=edit&amp;key=15">修改</a></td>
			<td><button class="btn btn-danger" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onclick="edit_branch('del','15')">删除</button></td>
			<td>	<button type="button" class="btn btn-success" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onclick="edit_branch('up','15')">上移</button></td>
			</tr></tbody></table>
  
  <button type="submit" class="btn btn-primary">确定</button>
</form>
  <button type="submit" class="btn btn-primary">删除该操作</button>
html;
		$arry=array('title'=>'新建操作','body'=>$body);
		break;
		case 'event':
		break;
		case 'skill':
		$title = "编辑电脑人物技能：";
		$com = create_search("输入技能名","npc-gl.php","skills","skills","技能名称",$key);
		$arry=array('title'=>'添加技能','body'=>$com);
		break;
		case 'equip':
		$title = "编辑电脑人物装备：";
		$com = create_search("输入装备名","npc-gl.php","equip","equip","装备名称",$key);
		$arry=array('title'=>'添加装备','body'=>$com);
		break;
		case 'drop':
		break;
	}
	return $arry;
}

function add_renwu_field($value){//编辑任务字段
	global $npc;
	$data = $value['data'];
	$key = $value['key'];
	$type = $value['clas'];
	switch($type){
		case 'task':
		case 'edit_task':
			$type = "task";
			$title = "编辑NPC任务添加成功！";
			$Tips = '触发任务';
		break;
		case 'edit_skills':
			$type = "skills";
			$title = "编辑NPC技能添加成功！";
			$Tips = 'NPC技能';
		break;
		case 'equip':
			$type = "equipment";
			$title = "编辑NPC装备添加成功！";
			$Tips = 'NPC装备';
		break;
		case 'drop_equip':
			$type = "drop_equip";
			$title = "编辑NPC掉落装备添加成功！";
			$Tips = 'NPC掉落装备';
		break;
		case 'goods':
			$type = "drop_items";
			$title = "编辑NPC掉落物品添加成功！";
			$Tips = 'NPC掉落物品';
		break;
	}
	if($data!=""){
		 $obj = $npc->get_npc_info($key);
		 $val = add_branch_Packing_data($obj->$type,$data);
		 if($npc->set_npc_field($key,$type,$val)){
			$body = "编辑电脑人物{$Tips}{$data['name']} 添加成功！";
			return array('title'=>$title ,'body'=>$body ,'reloading'=>true);
		 }else{
			 
		 }
	 }
}

//function 
?>