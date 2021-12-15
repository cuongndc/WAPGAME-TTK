<?php
//AJAX动态搜索类

namespace game_system;

class searchBox{
    public $dblj;
    public $sys;
	
    function __construct(){
		global $dblj;
		global $sys;
        $this->dblj = $dblj;
        $this->sys = $sys;
	}
	
//AJAX动态搜索类

function create_search($title,$address,$path,$type,$tips,$parent_key){//创建用以动态加载信息搜索的JS和HTMl代码段
		return <<<html
		{$title}：
		<input id="add-search" autofocus="autofocus" type="search" class="form-control search-input" placeholder="{$tips}">
<script>
$('#add-search').searchBox({
    escToClear: true, 
    onSearchChange: function(searchKey, isEmpty) {
	$.post('{$address}',{basic:"open",type:"search",path:"{$path}",key:searchKey,operation:"{$type}",parent:"{$parent_key}"},function(data) {
		$("#search-value").html(data);
	})
    }
});
</script>
		<div id="search-value"></div>
html;
	}

function search($val){//判断搜索发起方，并编织搜索界面返回给客户
	switch($val['operation']){
		case "npc":
			return $this->search_npc($val);
		break;
		case "skills":
			return $this->search_skills($val);
		break;
		case "edit_skills":
			return $this->search_skills_satisfy($val);
		break;
		case "goods":
			return $this->search_goods($val);
		break;
		case "equip":
		case "drop_equip":
			return $this->search_equip($val);
		break;
		case "mallgoods":
			return $this->search_mall_goods($val);
		break;
		case "mid":
			return $this->search_mid($val);
		break;
		case "pets":
			return $this->search_pets($val);
		break;
		case "task":
			if($val['path']=='map' || $val['path']=='daoju'){
				return $this->search_task_satisfy($val);
			}else{
				return $this->search_task($val);
			}
		break;
		default:
			return $this->map_search($value);
		break;
		}
	 
}
	
function search_npc($value){//查询步骤用户选取NPC信息
	global $npc;
	$error = '查询到的NPC信息：<textarea class="form-control" rows="3" id="add-value" placeholder="NPCID" readonly>未查询到与该信息相关的NPC存在!</textarea>';
	if($value['only'] == 'true'){
		$mid_info = $npc->get_npc_info( $value['key']);
		if(is_object($mid_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>NPC名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$mid_info->name}({$mid_info->id})</td><td><p class='text-ellipsis'>{$mid_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="NPCid" value="'.$mid_info->id .'"><input id="add-name" type="hidden" placeholder="NPC名" value="' . $mid_info->name .'">';
			$html .= '</tbody></table>填写数量表达式：
		<textarea id="add-num" type="text"  class="form-control"  placeholder="数量表达式"></textarea><br>'.$btn;
		}
	}else{
	  $mid_info = $npc->get_npc_seaech( $value['key']);
	  $mid_info = $mid_info[1];
	  $html = "<table class='table table-condensed'><thead><tr><th>NPC名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($mid_info)){
		foreach($mid_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_goods($value){//查询步骤用户选取物品信息
	global $goods;
		$error = '查询到的物品信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的物品存在!</textarea>';
	if($value['only'] == 'true'){
		$good_info = $goods->get_goods_info($value['key']);
		if(is_object($good_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>物品名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$good_info->name}({$good_info->id})</td><td><p class='text-ellipsis'>{$good_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="物品id" value="'.$good_info->id .'"><input id="add-name" type="hidden" placeholder="物品名" value="' . $good_info->name .'">';
			$html .= '</tbody></table>填写数量表达式：
		<textarea id="add-num" type="text"  class="form-control"  placeholder="数量表达式"></textarea><br>'.$btn;
		}
	}else{
	  $good_info = $goods->get_goods_name($value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>物品名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($good_info)){
		foreach($good_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_task_satisfy($value){//查询步骤用户选取任务信息
	global $task;
		$error = '查询到的任务信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的任务存在!</textarea>';
	if($value['only'] == 'true'){
		$obj_info = $task->get_task_info($value['key']);
		if(is_object($obj_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>任务名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$obj_info->name}({$obj_info->id})</td><td><p class='p1'>{$obj_info->rwUnfinished}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="任务id" value="'.$obj_info->id .'"><input id="add-name" type="hidden" placeholder="任务名" value="' . $obj_info->name .'">';
			$html .= '</tbody></table>任务触发条件：
		<textarea id="add-value" type="text"  class="form-control"  placeholder="任务触发条件"></textarea><br>'.$btn;
		}
	}else{
	  $obj_info =  $task->get_task_name($value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>任务名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($obj_info)){
		foreach($obj_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='p1'>{$val->rwUnfinished}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_skills_satisfy($value){//查询步骤用户选取技能信息
	global $skill;
		$error = '查询到的技能信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的技能存在!</textarea>';
	if($value['only'] == 'true'){
		$obj_info = $skill->get_skill_info($value['key']);
		if(is_object($obj_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>技能名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$obj_info->name}({$obj_info->id})</td><td><p class='p1'>{$obj_info->rwUnfinished}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="技能id" value="'.$obj_info->id .'"><input id="add-name" type="hidden" placeholder="技能名" value="' . $obj_info->name .'">';
			$html .= '</tbody></table>等级表达式：
		<textarea id="add-value" type="text"  class="form-control"  placeholder="等级表达式"></textarea><br>'.$btn;
		}
	}else{
	  $obj_info =  $skill->get_skill_name($value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>任务名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($obj_info)){
		foreach($obj_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='p1'>{$val->rwUnfinished}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_task($value){//查询步骤用户选取任务信息
	global $task;
		$error = '查询到的任务信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的任务存在!</textarea>';
	if($value['only'] == 'true'){
		$obj_info = $task->get_task_info($value['key']);
		if(is_object($obj_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>任务名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$obj_info->name}({$obj_info->id})</td><td><p class='p1'>{$obj_info->rwUnfinished}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="任务id" value="'.$obj_info->id .'"><input id="add-name" type="hidden" placeholder="任务名" value="' . $obj_info->name .'">';
			$html .= '</tbody></table><br>'.$btn;
		}
	}else{
	  $obj_info =  $task->get_task_name($value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>任务名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($obj_info)){
		foreach($obj_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='p1'>{$val->rwUnfinished}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_equip($value){//查询步骤用户选取装备信息
	global $equip;
		$error = '查询到的装备信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的装备存在存在!</textarea>';
	if($value['only'] == 'true'){
		$equip_info = $equip->get_equip_info($value['key']);
		if(is_object($equip_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>装备名(ID)</th><th>装备描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$equip_info->name}({$equip_info->id})</td><td><p class='text-ellipsis'>{$equip_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="物品id" value="'.$equip_info->id .'"><input id="add-name" type="hidden" placeholder="装备名" value="' . $equip_info->name .'">';
			$html .= '</tbody></table><br>'.$btn;
		}
	}else{
	  $equip_info = $equip->get_equip_name( $value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>装备(ID)</th><th>装备描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($equip_info)){
		foreach($equip_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_skills($value){//查询步骤用户选取技能信息
	global $skill;
		$error = '查询到的技能信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的技能存在!</textarea>';
	if($value['only'] == 'true'){
		$obj_info = $skill->get_skill_info($value['key']);
		if(is_object($obj_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>技能名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$obj_info->name}({$obj_info->id})</td><td><p class='text-ellipsis'>{$obj_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="技能id" value="'.$obj_info->id .'"><input id="add-name" type="hidden" placeholder="技能名" value="' . $obj_info->name .'">';
			$html .= '</tbody></table>'.$btn;
		}
	}else{
	  $obj_info = $skill->get_skill_name($value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>技能名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($obj_info)){
		foreach($obj_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_mall_goods($value){//查询步骤用户选取物品信息
	global $goods;
		$error = '查询到的物品信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的物品存在!</textarea>';
	if($value['only'] == 'true'){
		$good_info = $goods->get_goods_info($value['key']);
		if(is_object($good_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>物品名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$good_info->name}({$good_info->id})</td><td><p class='text-ellipsis'>{$good_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="物品id" value="'.$good_info->id .'"><input id="add-name" type="hidden" placeholder="物品名" value="' . $good_info->name .'">';
			$html .= '</tbody></table>'.$btn;
		}
	}else{
	  $good_info = $goods->get_goods_name( $value['key']);
	  $html = "<table class='table table-condensed'><thead><tr><th>物品名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($good_info)){
		foreach($good_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_mid($value){//查询步骤用户选取地图信息
	global $map;
	$error = '查询到的场景信息：<textarea class="form-control" rows="3"  readonly>未查询到与该信息相关的场景存在!</textarea>';
	if($value['only'] == 'true'){
		$mid_info = $map->get_mid_info($value['key']);
		if(is_object($mid_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>地图名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$mid_info->name}({$mid_info->id})</td><td>{$mid_info->desc}</td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="地图id" value="'.$mid_info->id .'"><input id="add-name" type="hidden" placeholder="物品名" value="' . $mid_info->name .'">';
			$html .= '</tbody></table>'.$btn;
		}
	}else{
	  $mid_info = $map->get_name_mid($value['key']);
	  //var_dump($mid_info);
	  $html = "<table class='table table-condensed'><thead><tr><th>地图名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_object($mid_info)){
		foreach($mid_info->data as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td>{$val->desc}</td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function search_pets($value){//查询步骤用户选取宠物信息
	global $npc;
	$error = '查询到的宠物信息：<textarea class="form-control" rows="3" id="add-value" placeholder="宠物ID" readonly>未查询到与该信息相关的宠物存在!</textarea>';
	if($value['only'] == 'true'){
		$mid_info = $npc->get_npc_info( $value['key']);
		if(is_object($mid_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>宠物名(ID)</th><th>简单描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_branch(\"{$value['operation']}\",\"{$value['parent']}\",\"true\",\"{$value['path']}\")'>确认添加</button>";
			$html .=  "<tr><td>{$mid_info->name}({$mid_info->id})</td><td><p class='text-ellipsis'>{$mid_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="宠物id" value="'.$mid_info->id .'"><input id="add-name" type="hidden" placeholder="宠物名" value="' . $mid_info->name .'">';
			$html .= '</tbody></table>填写数量表达式：
		<textarea id="add-num" type="text"  class="form-control"  placeholder="数量表达式"></textarea><br>'.$btn;
		}
	}else{
	  $mid_info = $npc->get_npc_seaech( $value['key']);
	  $mid_info = $mid_info[1];
	  $html = "<table class='table table-condensed'><thead><tr><th>宠物名(ID)</th><th>简单描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($mid_info)){
		foreach($mid_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_search(\"{$val->id}\",\"search\",\"{$value['path']}\",\"{$value['operation']}\",\"{$value['parent']}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function reloading($path,$type,$key){//加载步骤定义数据内容
	//var_dump($path,$type,$key);
	switch($path){
		case 'npc':
			global $npc;
			$branch = $npc->get_npc_info($key);
			switch($type){
			  case 'goods':
			  case 'drop_items':
				$thead = "<tr><th>物品名</th><th>数量表达式</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->drop_items,$key,'goods');
			  break;
			  case 'task':
			  case 'edit_task':
				$thead = "<tr><th>任务ID</th><th>任务名</th><th>操作</th></tr>";
				$html = $this->generate_list($path,$thead,$branch->task,$key,'task');
			  break;
			  case 'skills':
			  case 'edit_skills':
				$thead = "<tr><th>技能ID</th><th>技能名</th><th>等级表达式</th><th>操作</th></tr>";
				$html = $this->generate_list($path,$thead,$branch->skills,$key,'edit_skills');
			  break;
			  case 'equip':
				$thead = "<tr><th>装备ID</th><th>装备名</th><th>装备位置</th><th>数量表达式</th><th>操作</th></tr>";
				$html = $this->generate_list($path,$thead,$branch->equipment,$key,'equip');
			  break;
			  case 'drop_equip':
				$thead = "<tr><th>装备ID</th><th>装备名</th><th>装备位置</th><th>数量表达式</th><th>操作</th></tr>";
				$html = $this->generate_list($path,$thead,$branch->drop_equip,$key,'drop_equip');
			  break;
			  case 'operation':
				$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
				$html =  $this->generate_array_list($path,$thead ,$branch->operation,$key,"operation");
			  break;
			}
		break;
		case 'task':
			global $task;
			$branch = $task->get_task_info($key);
			switch($type){
			  case 'rwKilling':
			  case 'npc':
				$thead = "<tr><th>人物ID</th><th>人物名</th><th>NPC等级</th><th>数量表达式</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->rwKilling,$key,'rwKilling');
			  break;
			  case 'rwseek':
			  case 'goods':
				$thead = "<tr><th>物品名</th><th>数量表达式</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->rwseek,$key,'rwseek');
			  break;
			}
		break;
		case 'map':
			global $map;
			$branch = $map->get_mid_info($key);
			switch($type){
			  case 'npc':
				$thead = "<tr><th>人物名</th><th>数量表达式</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->npc,$key,'npc');
			  break;
			  case 'goods':
				$thead = "<tr><th>物品名</th><th>数量表达式</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->goods,$key,'goods');
			  break;
			  case 'edit_task':
				$thead = "<tr><th>任务ID</th><th>任务名</th><th>触发条件</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->task,$key,'edit_task');
			  break;
			  case 'operation':
				$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
				$html =  $this->generate_array_list($path,$thead ,$branch->operation,$key,"operation");
			  break;
			}
		break;
		case 'event':
			global $event;
			$branch = $event->get_branch_info($key);
			switch($type){
				case 'add_set_up':
				case 'set_up':
					$thead = "<tr><th>属性名</th><th>属性值</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->set_up,$key,'set_up');
				break;
				case 'add_change_genus':
				case 'change_genus':
					$thead = "<tr><th>属性名</th><th>属性值</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->change_genus,$key,'change_genus');
				break;
				case 'add_change_items':
				case 'change_items':
					$thead = "<tr><th>物品名</th><th>数量表达式</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->change_items,$key,'change_items');
				break;
				case 'add_learning_skills':
				case 'learning_skills':
					$thead = "<tr><th>技能ID</th><th>技能名</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->learning_skills,$key,'learning_skills');
				break;
				case 'add_abolish_skills':
				case 'abolish_skills':
					$thead = "<tr><th>技能ID</th><th>技能名</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->abolish_skills,$key,'abolish_skills');
				break;
				case 'add_trigger_task':
				case 'trigger_task':
					$thead = "<tr><th>任务ID</th><th>任务标识</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->trigger_task,$key,'trigger_task');
				break;
				case 'add_del_task':
				case 'del_task':
					$thead = "<tr><th>任务ID</th><th>任务标识</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->del_task,$key,'del_task');
				break;
				case 'add_del_task_ok':
				case 'del_task_ok':
					$thead = "<tr><th>任务ID</th><th>任务标识</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->del_task_ok,$key,'del_task_ok');
				break;
				case 'add_del_task_give_up':
				case 'del_task_give_up':
					$thead = "<tr><th>任务ID</th><th>任务标识</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->del_task_give_up,$key,'del_task_give_up');
				break;
				case 'add_challenge_people':
				case 'challenge_people':
					$thead = "<tr><th>人物ID</th><th>人物名</th><th>数量表达式</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->challenge_people,$key,'challenge_people');
				break;
				case 'add_adoptive_pets':
				case 'adoptive_pets':
					$thead = "<tr><th>宠物ID</th><th>宠物名</th><th>数量表达式</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->adoptive_pets,$key,'adoptive_pets');
				break;
				case 'add_del_pets':
				case 'del_pets':
					$thead = "<tr><th>宠物ID</th><th>宠物名</th><th>数量表达式</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->del_pets,$key,'del_pets');
				break;
				case 'add_moving_target':
				case 'moving_target':
					$thead = "<tr><th>地图ID</th><th>地图名</th><th>操作</th></tr>";
					//var_dump($path,$thead,$branch->moving_target,$key);
					$html = $this->generate_list($path,$thead,$branch->moving_target,$key,'moving_target');
				break;
				case 'add_user_input':
				case 'user_input':
					$thead = "<tr><th>字段标识</th><th>字段名称</th><th>字段尺寸</th><th>字段类型</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->user_input,$key,'user_input');
				break;
				case 'add_mall_members':
				case 'mall_members':
					$thead = "<tr><th>物品ID</th><th>物品名称</th><th>操作</th></tr>";
					$html = $this->generate_list($path,$thead,$branch->mall_members,$key,'mall_members');
				break;
			}
		break;
		case 'daoju':
			global $goods;
			$branch = $goods->get_goods_info($key);
			switch($type){
			  case 'task':
			  case 'edit_task':
				$thead = "<tr><th>任务ID</th><th>任务名</th><th>触发条件</th><th>操作</th></tr>";
				$html =  $this->generate_list($path,$thead,$branch->task,$key,'edit_task');
			  break;
			  case 'operation':
				$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
				$html =  $this->generate_array_list($path,$thead ,$branch->operation,$key,"operation");
			  break;
			}
		break;
	}
	return $html;
	}

function generate_list($path,$thead,$data,$key,$type){//生成数据列表_公用类型S
	global $npc;
	global $equip;
	global $sys;
	$data = json_decode($data);
		$html = <<< html
		<table  class="table table-condensed"> 
		  <thead>
		  {$thead}
		  </thead>
		<tbody>
html;
	if(is_object($data)){
		foreach($data as $id => $obj){
			if(is_object($obj)){
				if($obj->category == "text"){$category="文本型";}else{$category="数值型";}
				$vid = "";
				$val = "";
				$num = "";
				$name = "";
				$mark = "";
				$size = "";
			if(isset($obj->id)){$vid = $obj->id;};
			if(isset($obj->val)){$val = $obj->val;};
			if(isset($obj->num)){$num = $obj->num;};
			if(isset($obj->name)){$name = $obj->name;};
			if(isset($obj->mark)){$mark = $obj->mark;};
			if(isset($obj->size)){$size = $obj->size;};
				$html .='<tr>';
				switch($type){
					case 'rwKilling':
						$obj = $npc->get_npc_info($vid);
						$list = "<td>{$vid}</td><td>{$name}</td><td>{$obj->lvl}</td><td>{$num}</td>";
					break;
					case 'equip':
						$obj = $equip->get_equip_info($vid);
						switch($obj->type){
							case 'equip':
								$tit = "防具" ;
								$info = $sys->get_system_config('system','equip_class');
							break;
							case 'equipinlay':
								$tit = "防具镶物"; 
								$info = $sys->get_system_config('system','equip_class');
							break;
							case 'weapon':
								$tit = "兵器" ;
								$info = $sys->get_system_config('system','weapon_class');
							break;
							case 'weaponinlay':
								$tit = "兵器镶物" ;
								$info = $sys->get_system_config('system','weapon_class');
							break;
						}
						$info = json_decode($info);
						$clas = $obj->clas;
						$list = "<td>{$vid}</td><td>{$name}</td><td>{$tit}/{$info->$clas->name}</td><td>{$num}</td>";
					break;
					case 'drop_equip':
						$obj = $equip->get_equip_info($vid);
						switch($obj->type){
							case 'equip':
								$tit = "防具" ;
								$info = $sys->get_system_config('system','equip_class');
							break;
							case 'equipinlay':
								$tit = "防具镶物"; 
								$info = $sys->get_system_config('system','equip_class');
							break;
							case 'weapon':
								$tit = "兵器" ;
								$info = $sys->get_system_config('system','weapon_class');
							break;
							case 'weaponinlay':
								$tit = "兵器镶物" ;
								$info = $sys->get_system_config('system','weapon_class');
							break;
						}
						$info = json_decode($info);
						$clas = $obj->clas;
						$list = "<td>{$vid}</td><td>{$name}</td><td>{$tit}/{$info->$clas->name}</td><td>{$num}</td>";
					break;
					case 'change_items':
					case 'npc':
					case 'goods':
					case 'rwseek':
						$list = "<td>{$name}({$vid})</td><td>{$num}</td>";
					break;
					case 'learning_skills':
					case 'abolish_skills':
					case 'task':
					case 'trigger_task':
					case 'del_task':
					case 'del_task_ok':
					case 'del_task_give_up':
					case 'moving_target':
					case 'mall_members':

						$list = "<td>{$vid}</td><td>{$name}</td>";
						$no_edit = true;
					break;
					case 'skills':
						$list = "<td>{$vid}</td><td>{$name}</td><td>{$val}</td>";
					break;
					case 'operation':
						$weiyi = true;
					break;
					case 'edit_task':
					case 'edit_skills':
					$list = "<td>{$vid}</td><td>{$name}</td><td>{$val}</td>";
					break;
					case 'adoptive_pets':
					case 'del_pets':
					case 'challenge_people':
						$list = "<td>{$vid}</td><td>{$name}</td><td>{$num}</td>";
					break;
					case 'user_input':
						$list = "<td>{$mark}</td><td>{$name}</td><td>{$size}</td><td>{$category}</td>";
					break;
				}
				
			if(isset($list)){
				$html .= $list;
			}else{
				$html .="<td>{$obj->name}</td><td>{$val}</td>";
			}
		if($weiyi){
			$edit .='<button class="btn btn-info" type="button">上移</button><button class="btn btn-info" type="button">下移</button> ';
		}
		if(!$no_edit){
			$edit .=<<<html
	  <button class="btn btn-info" type="button" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onClick="editing_step('edit','{$path}','{$type}','{$key}','{$id}')">编辑</button>
html;
		}
			
		$html .=<<<html
      <td>{$edit}
	  <button class="btn btn-danger " type="button" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onClick="editing_step('del','{$path}','{$type}','{$key}','{$id}')">删除</button>
	  </td>
    </tr>
html;
$edit="";
			}
		}
	}
$html .= "</tbody></table>";
		
		return $html;
	}

function generate_array_list($path,$thead,$data,$key,$type){//从数组数据中生成列表
	global $operation;
	$arry = explode(',',$data);
	$html = <<< html
		<table  class="table table-condensed"> 
		  <thead>
		  {$thead}
		  </thead>
		<tbody>
html;

	if(is_array($arry)){
	foreach ($arry as $val){
		if(G_trimall($val)!=""){
		$operation_info = $operation->get_operation_info($val);
		$html .= "<td>{$operation_info->id}</td><td>{$operation_info->name}</td><td>{$operation_info->appear}</td>";
		if(!$no_edit){
			$edit =<<<html
	  <a class="btn btn-info" href="operation.php?path={$path}&id={$operation_info->id}">编辑</a>
html;
		}
			
		$html .=<<<html
      <td>{$edit}
	  <button class="btn btn-danger " type="button" type="button" data-position="100px" data-toggle="modal" data-target="#ajax-alert" onClick="del_operation('{$path}','{$key}','{$operation_info->id}')">删除</button>
	  </td>
    </tr>
html;
$edit ="";
	}
	}
	}
	$html .= "</tbody></table>";
		
	return $html;
}

}
?>