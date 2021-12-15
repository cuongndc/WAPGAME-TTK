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
	case "reload":
		$arry = $equip->equip_type_load($value["clas"],$value["page"],$value["recPerPage"]);
	break;
	case 'deploy':
		$arry = deploy_equip($value);
	break;
	case "save":
		$arry = new_equip($value);
	break;
	case "edit":
		$arry = edit_equip($value['key'],$value['allow'],$value['clas'],$value[data]);
	break;
	case "del":
		$arry = del_equip($value['key'],$value['allow'],$value['clas']);
	break;
	case "search":
		$arry =  search_equip($value);
	break;
	case "Selection":
		echo load_Selection();
	break;
	case "add_field":
		$arry = add_renwu_field($value);
	break;
	case "del_field":
		$arry = del_renwu_field($value);
	break;
	case 'Reset':
		switch($value['path']){
			case 'npc':
				$npc->load_equip_list($value['id']);
				$npc_info= $npc->get_npc_info($value['id']);
				$arry = $equip->read_equip_list($npc_info,$npc_info->equip_class,$npc_info->equip_val);
			break;
			/*
			case '':
			break;
			*/
		}
	break;
	}
	ajax_alert($arry);
}

function add_renwu_field($value){//编辑任务字段
	global $equip;
	$path = $value['path'];
	$pathkey = $value['key'];
	$equip_type = $value['equip'];
	$equip_clas = $value['equipcl'];
	$data = $value['data'];
	switch($path){
		case 'npc':
			global $npc;
			$obj = $npc->get_npc_info($pathkey);
			$table = "npc";
			$title = "编辑NPC添加装备成功！";
			$field = "equip_val";
			$Tips = 'NPC装备';
			if($data!=""){
				$val = $equip->add_equip_Packing_data($equip_type,$equip_clas,$obj->$field ,$data);
				if($npc->set_npc_field($pathkey,$field ,$val)){
				$body = "编辑电脑人物{$Tips}{$data['name']} 添加成功！";
				$reloading = true;
				}
			}
			
		break;
		case 'skill':
			global $skill;
			$obj = $skill->get_skill_info($pathkey);
			$table = "jineng";
			$title = "编辑技能使用特定装备成功！";
			$field = "equip_id";
			$Tips = '使用特定装备';
			if($data!=""){
				$val = $equip->get_equip_info($data['id']);
				if($skill->set_skill_field($pathkey,$field ,$val->id)){
				$body = "编辑技能{$Tips}{$data['name']} 添加成功！";
				$repage = true ;
				}
			}
		break;
		case 'weapon':
			$type = "skills";
			$title = "编辑NPC技能添加成功！";
			$Tips = 'NPC技能';
		break;
		case 'equipinlay':
			$type = "task";
			$title = "编辑NPC任务添加成功！";
			$Tips = '触发任务';
		break;
		case 'weaponinlay':
			$type = "skills";
			$title = "编辑NPC技能添加成功！";
			$Tips = 'NPC技能';
		break;
	}
	return array('title'=>$title ,'body'=>$body ,'reloading'=>$reloading, 'repage'=>$repage);
}

function del_renwu_field($value){//编辑任务字段
	global $equip;
	$path = $value['path'];
	$pathkey = $value['key'];
	$equip_type = $value['equip'];
	$equip_clas = $value['equipcl'];
	$confirm = $value['confirm'];
	switch($path){
		case 'npc':
			global $npc;
			$obj = $npc->get_npc_info($pathkey);
			$table = "npc";
			$title = "编辑NPC删除装备成功！";
			$field = "equip_val";
			$Tips = 'NPC装备';
			if($confirm){
				$val = $equip->del_equip_Packing_data($equip_type,$obj->$field,$equip_clas);
				if($npc->set_npc_field($pathkey,$field ,$val)){
				$body = "编辑电脑人物{$Tips}{$data['name']} 删除成功！";
				$reloading = true; 
				}
			}
		break;
		case 'skill':
			global $skill;
			$obj = $skill->get_skill_info($pathkey);
			$table = "jineng";
			$title = "编辑技能删除特定装备成功！";
			$field = "equip_id";
			$Tips = '特定装备';
			if($confirm){
				if($skill->set_skill_field($pathkey,$field)){
				$body = "编辑技能使用{$Tips}{$data['name']} 删除成功！";
				$repage = true ;
				}
			}
		break;
		case 'weapon':
			$type = "skills";
			$title = "编辑NPC技能添加成功！";
			$Tips = 'NPC技能';
		break;
		case 'equipinlay':
			$type = "task";
			$title = "编辑NPC任务添加成功！";
			$Tips = '触发任务';
		break;
		case 'weaponinlay':
			$type = "skills";
			$title = "编辑NPC技能添加成功！";
			$Tips = 'NPC技能';
		break;
	}
	return array('title'=>$title ,'body'=>$body ,'reloading'=>$reloading ,'repage'=>$repage);
}

function create_search($title,$address,$path,$type,$tips,$parent_key,$equip,$equipcl){//创建用以动态加载信息搜索的JS和HTMl代码段
		return <<<html
		{$title}：
		<input id="add-search" autofocus="autofocus" type="search" class="form-control search-input" placeholder="{$tips}">
<script>
$('#add-search').searchBox({
    escToClear: true, 
    onSearchChange: function(searchKey, isEmpty) {
	var idata = {basic:"open",type:"search",path:"{$path}",key:searchKey,operation:"{$type}",parent:"{$parent_key}",equip:"{$equip}",equipcl:"{$equipcl}"}
	$.post('{$address}',idata,function(data) {
		$("#search-value").html(data);
	})
    }
});
</script>
		<div id="search-value"></div>
html;
	}

function search_equip($value){//查询步骤用户选取装备信息
	global $equip;
	$operation = $value['operation'];
	$parent = $value['parent'];
	$path = $value['path'];
	$key = $value['key'];
	$equip_na = $value['equip'];
	$equipcl = $value['equipcl'];
		$error = '查询到的装备信息：<textarea class="form-control" rows="3" readonly>未查询到与该信息相关的装备存在存在!</textarea>';
	if($value['only'] == 'true'){
		$equip_info = $equip->get_equip_info($key);
		if(is_object($equip_info)){
			$html = "<table class='table table-condensed'><thead><tr><th>装备名(ID)</th><th>装备描述</th></tr></thead><tbody>";
			$btn = "<button class='btn btn-primary' type='button' onclick='add_deploy_equip(\"{$operation}\",\"{$parent}\",\"true\",\"{$path}\",\"{$equip_na}\",\"{$equipcl}\")'>确认添加</button>";
			$html .=  "<tr><td>{$equip_info->name}({$equip_info->id})</td><td><p class='text-ellipsis'>{$equip_info->desc}</p></td></tr>"; 
			$html .= '<input id="add-id" type="hidden" placeholder="物品id" value="'.$equip_info->id .'"><input id="add-name" type="hidden" placeholder="装备名" value="' . $equip_info->name .'">';
			$html .= '</tbody></table><br>'.$btn;
		}
	}else{
	  $equip_info = $equip->get_equip_name_tool($key,$equip_na,$equipcl);
	  $html = "<table class='table table-condensed'><thead><tr><th>装备(ID)</th><th>装备描述</th><th>操作</th></tr></thead><tbody>";
	  if(is_array($equip_info)){
		foreach($equip_info as $val ){
			$btn = "<button type='button' class='btn btn-info' onClick='load_add_equip(\"{$val->id}\",\"search\",\"{$path}\",\"{$operation}\",\"{$parent}\",\"{$equip_na}\",\"{$equipcl}\")'>选取</button>";
			$html .=  "<tr><td>{$val->name}({$val->id})</td><td><p class='text-ellipsis'>{$val->desc}</p></td><td>{$btn}</td></tr>"; 
		}
		$html .= '</tbody></table>';
	  }
	};
	if($html){ return $html;}else{ return $error; };
}

function deploy_equip($val){//对指定目标部署装备
	global $searchBox;
	global $equip;
	global $sys;
	 $clas = $val["clas"];
	 $target = $val["target"];
	 $targetid = $val["targetid"];
	 $equip_type = $val["equip"];
	 $equip_clas = $val["equipcl"];
	 $equipid = $val["equipid"];
	 $confirm = $val["confirm"];
	switch($clas){
		case 'add':
			switch($target){
				case 'npc':
					$title = "编辑NPC添加装备：";
					$com = create_search("输入装备名","equip-gl.php","npc","equip","装备名",$targetid,$equip_type,$equip_clas);
				break;
				case 'skill':
					$title = "编辑技能使用装备：";
					$com = create_search("输入装备名","equip-gl.php","skill","equip","装备名",$targetid,$equip_type,$equip_clas);
				break;
			}
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'edit':
			switch($target){
				case 'npc':
					$title = "编辑NPC添加装备：";
					$equip_info = $equip->get_equip_info($equipid);
					$com = "当前装备位置装备信息：{$equip_info->name}({$equip_info->id})<br>";
					$com .= create_search("选取新的装备名","equip-gl.php","npc","equip","装备名",$targetid,$equip_type,$equip_clas);
				break;
				case 'skill':
					$title = "编辑技能使用装备：";
					$equip_info = $equip->get_equip_info($equipid);
					$com = "当前技能使用装备信息：{$equip_info->name}({$equip_info->id})<br>";
					$com .= create_search("选取新的装备名","equip-gl.php","skill","equip","装备名",$targetid,$equip_type,$equip_clas);
				break;
			}
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'del':
			$btn = "<button class='btn btn-danger' type='button' onclick='del_deploy_equip(\"{$target}\",\"{$targetid}\",\"{$equip_type}\",\"{$equip_clas}\",\"true\")'>确认删除</button>";
			switch($equip_type){
				case 'weapon':
					$tit = '武器';
					$info = $sys->get_system_config("system","weapon_class");
				break;
				case 'equip':
					$tit = '装备';
					$info = $sys->get_system_config("system","equip_class");
				break;
			}
			$data =json_decode($info);
			$equip_clas_name = $data->$equip_clas->name;
			switch($target){
				case 'npc':
					$title = "删除NPC已添加装备：";
					$equip_info = $equip->get_equip_info($equipid);
					$com = "<h3>确认删除当前{$tit}位置的{$equip_clas_name}装备：<span  class='text-red'>{$equip_info->name}({$equip_info->id})</span >？</h3><br>";
					$arry = array('title'=>$title,'body'=>$com,'btn'=>$btn,'exbtn'=>true);
				break;
				case 'skill':
					$title = "删除技能使用特定装备：";
					$equip_info = $equip->get_equip_info($equipid);
					$com = "<h3>确认删除当前技能使用{$tit}{$equip_clas_name}必须为：<span  class='text-red'>{$equip_info->name}({$equip_info->id})</span >的限制？</h3><br>";
					$arry = array('title'=>$title,'body'=>$com,'btn'=>$btn,'exbtn'=>true);
				break;
			}
		break;
	}
	return $arry;
}

function load_search_goods($value){//管理后台选中物品分类及区域加载具体物品
	global $goods;
	global $map;
		$goods_type = $goods->get_goods_type($value['goodtype']);
		$Obj_game_wpall = $goods->get_goods_all($goods_type[0],$value['qyval']);
				echo '<div class="modal-content">
      <div class="modal-header">';
        echo '<hr><h4 class="modal-title">';
				$name=$map->get_qy_name(G_trimall($value['qyval']));
				if ($name == "()"){echo "(所有区域)";}else{echo "$name";}
				echo ' >>'.$goods_type[1].' 物品查询结果</h4></div> <div class="modal-body">';
				if(is_array($Obj_game_wpall)){
				 	foreach($Obj_game_wpall as $obj){
						if(is_object($obj)){
						echo '<div class="radio"><label><input type="radio" name="mid_rad" value="'.$obj->id .'">'.$map->get_qy_name($obj->djqy) ."/".$obj->name .'('.$obj->id .') </label></div>';}
				}}
				echo '</div><div class="modal-footer">';
         if($value['edit']!="true"){
			echo '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
		 }else{
			echo '<button type="button" class="btn btn-default" onclick="$(\'#edit_sear\').html(\'\')" >清空</button>';
		 }
        echo '<button type="button" id="qydt_xq" class="btn btn-primary">选取</button>
      </div>
    </div>';
}

function load_Selection(){//管理后台加载物品区域和类别选取页面
	global $map;
	//global $goods;
		$title = "选取一个物品";
		$content = <<<html
	  <div class="container"><!--[选择区域]-->
	<h4>选择物品种类：</h4>
		<select class="form-control" id="good-type"> 
				<option value="consume">消耗品</option>
				<option value="weapon">兵器</option>
				<option value="equip">防具</option>
				<option value="book">书籍</option>
				<option value="weaponinlay">兵器镶物</option>
				<option value="equipinlay">防具镶物</option>
				<option value="taskitems">任务物品</option>
				<option value="other">其他</option>
		</select>
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索物品</span>
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
				$content .=  "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
				}
		  $content .=  '</select>
		</div>
	  </div>
	  <div id="edit_sear"></div>
	</div>
</div>';
	return ajax_alert($title,$content,"0");
}

function del_equip($key,$true,$clas){//删除装备
	global $attribute;
	global $equip;
	if($key !=0 ){
		$obj = $equip->get_equip_info($key);
	if($true=="true"){
	  if($attribute->del_record("daoju",$key)){
		$content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品操作成功!</p>";
		return array("title" => "删除物品成功！" ,'body'=>$content , 'reload'=>true);
	  }else{
		$content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品的操作失败了!</p>";
		return array("title" =>  "删除物品失败！" ,'body'=>$content);
	  }
	}else{
		$content = "<p>确认删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品？</p>";
		$button = '<button type="button" onclick="del_equip(\''.$obj->id .'\',\'true\')" class="btn btn-danger">确认删除</button>';
		return array('title'=>"删除一个物品",'body'=>$content,'btn'=>$button,'exbtn'=>true);
	  }
	}

}

function edit_equip($key,$true,$clas,$data=""){//编辑装备
	global $attribute;
	global $equip;
	global $map;
	$clas = str_replace("#","",$clas);
	$tit = $equip->get_equip_type($clas);
	if($key !=0 ){
	if($true=="true"){
		$obj = $equip->get_equip_id($key);
	  if($attribute->edit_record("equip",$key,$data)){
		$content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品操作成功!</p>";
		return array("title" => "物品信息修改成功！", "body"=> $content  , 'refresh'=>true);
	  }else{
		$content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品的操作失败了!</p>";
		$button = "0";
		return array("title" =>"物品修改信息失败！", "body"=> $content );
	  }
	}else{
		$title = "编辑一个物品信息";
		$eq_obj = $equip->get_equip_id($key);
		$list = $attribute->get_attribute_edit("equip",$key,array(),array());
		$content = <<<html
	  <form id="add" method="post">
	  <input type="hidden" name="type" id="list" class="form-control" value="{$clas}"> 
		<div class="container"><!--[选择区域]-->
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6">
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6">
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" name="qy" id="quyu_xs"> 
				<option value="0">请选择一级区域</option>
html;
			$Obj_game_qy = $map->get_qy_all();
				foreach($Obj_game_qy->data as $obj){
				$content .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
				}
		  $content .= '</select>
		</div>
	  </div>
	  <div id="edit_sear"> </div></div><hr>';
		$content .=$list;
		 $button = '<button type="button" onclick="edit_equip(\''.$clas.'\',\''.$eq_obj->id .'\',\'true\')" class="btn btn-primary">保存修改</button>';
		$arry = array('title'=>$title,'body'=>$content,'btn'=>$button);
	  }
	}
	return $arry;

}

function new_equip($value){//新建装备
	global $map;
	global $equip;
	global $attribute;
	$clas = str_replace("#","",$value['clas']);
	$tit = $equip->get_equip_type($clas);
	if(isset($value['data'])){
		$obj = json_decode($data,true);
		if($obj->add_record){
			if($attribute->add_record("daoju",$value['data'])>0){
				$obj = json_decode($value['data']);
				$content = "新建<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" 	</span>这个装备操作成功!</p>";
				return array("title"=>"新建装备【 {$tit['1']} 】成功！", "body"=>$content , 'refresh'=>true);
			}else{
				echo "物品新建失败！";
			}
		}else{
			if($attribute->edit_record("daoju",$value['data'])>0){
				$obj = json_decode($value['data']);
				$content = "修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" 	</span>这个装备操作成功!</p>";
				return array("title"=>"修改装备【 {$tit['1']} 】成功！", "body"=>$content , 'refresh'=>true);
			}else{
				echo "物品修改失败！";
			}
		}

	  }else{
	$title= "新建{$tit['1']}";
	$content = <<<html
	  <form id="add" method="post">
	  <input type="hidden" name="type" id="list" class="form-control" value="{$clas}"> 
		<div class="container"><!--[选择区域]-->
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6">
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6">
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" name="qy" id="quyu_xs"> 
				<option value="0">请选择一级区域</option>
html;
			$Obj_game_qy =$map->get_qy_all();
				foreach($Obj_game_qy->data as $obj){
				$content .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
				}
		  $content .= '</select>
		</div>
	  </div>
	  <div id="edit_sear"> </div></div><hr>';
	  $content .= $attribute->get_attribute_new("equip",array(),array());
	  $content .= '</form>
      <div class="modal-footer">';
	  $button = "<button type=\"button\" onclick=\"new_equip('true')\" class=\"btn btn-primary\">确认创建</button>";
	  return array('title' => $title,'body' => $content,'btn'=> $button,'exbtn'=>true);
	}
}

function edit_class($type,$name,$id=null){//编辑和新建装备类型
	global $game;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$game->get_system_config("system",$clas);
	$equip=json_decode($equip);
	if(isset($id)){
		if(is_object($equip)){
			if(is_object($equip->$id)){
				$equip->$id->name = $name;
			}
		}
	}else{
		if(is_object($equip)){
			$i=$equip->count;
		}else{
			$i=0;
			$equip = new stdclass();
		}
		++$i;
		if(!is_object($equip->$i)){
			$equip->$i = new stdclass();
		}
		$equip->count = $i;
		$equip->$i->id = $i;
		$equip->$i->name = $name;
	}
	$m_value=json_encode($equip);
	$val=$game->set_system_config("system",$clas,$m_value);
	return $val;
}

function read_class($type,$id){//读取装备类型类型
	global $game;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$game->get_system_config("system",$clas);
	$equip=json_decode($equip);	
	return $equip->$id;
}

function del_class($type,$id){//删除装备子类型
	global $game;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$game->get_system_config("system",$clas);
	$equip=json_decode($equip);
	if(isset($id)){
		if(is_object($equip)){
			if(is_object($equip->$id)){
				unset ($equip->$id);
			}
		}
	$m_value=json_encode($equip);
	$val=$game->set_system_config("system",$clas,$m_value);	
	}
	return $val;
}



?>