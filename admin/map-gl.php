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
			$arry = reload_area($value['name'],$value['clas'],$value['area'],$value['page'],$value['recPerPage']);
		break;
		case "add_field":
			$arry = add_renwu_field($value);
		break;
		case "search":
			$arry =  $searchBox->search($value);
		break;
		case "sear":
			$arry = sear($value);
		break;
		case "qygl":
			$arry = qygl($value);
		break;
		case "Birth":
			$arry = birth($value);
		break;
		case "addobj":
			$arry = addobj($value);
		break;
		case "dtgl":
			$arry = dtgl($value);
		break;
		case "map":
			$arry = edit_map($value);
		break;
		case 'Reset':
			$arry =  $searchBox->reloading('map',$value['obj'],$value['id']);
			$arry = array('body'=>$arry);
		break;
		case "editing_step":
			$arry = editing_step($value);
		break;

	}
	ajax_alert($arry);
}

function sear($val){//搜索出口信息
	global $map;
	switch($val['clas']){
		case 'qy':
		case 'area':
			$obj = $map->get_name_qy($val['val']);
			//var_dump($obj);
			if(is_object($obj)){
				$body = "<hr>";
				foreach($obj->data as $obj_val){
					$i++;
				$body .= <<<html
<div class="radio">
  <label>
    <input type="radio" name="qyid" value="{$obj_val->qyid}">{$obj_val->qyname}({$obj_val->qyid})
  </label>
</div>
html;
				}
				$body .= <<<html
<hr>
<button type="button" class="btn btn-primary" onClick='ajax_load_qy()'>选取</button>
html;
				;
			}
		break;
		case 'map':
			$obj = $map->get_name_mid($val['val']);
			//var_dump($obj);
			if(is_object($obj)){
				$body = "<hr>";
				foreach($obj->data as $obj_val){
					$i++;
					$qyinfo = $map->get_qy_info($obj_val->qy);
				$body .= <<<html
<div class="radio">
  <label>
    <input type="hidden" name="qyid{$obj_val->id}" value="{$obj_val->qy}">
    <input type="radio" name="dtid" value="{$obj_val->id}">{$qyinfo->qyname}({$qyinfo->qyid})/{$obj_val->name}({$obj_val->id})
  </label>
</div>
html;
				}
				$body .= <<<html
<hr>
<button type="button" class="btn btn-primary" onClick='ajax_load_dt()'>选取</button>
html;
				;
			}
		break;
	}
	return array('body'=>$body );
}

function map_seaerh($val){//搜索地图数据
	global $map;
	$Obj_game_qy = $map->get_name_qymid(0,G_trimall($val['name']));
	$body = "<hr>";
	foreach($Obj_game_qy->data as $obj){
		$i++;
		$body .= <<<html
<div class="radio">
  <label>
    <input type="radio" name="mapid" value="{$obj->id}">{$obj->name}({$obj->id})
  </label>
</div>
html;
	}
	$body .= "<hr>";
	return array('body'=>$body );
}

function reload_area($name,$type,$area,$page=0,$recPerPage = 20){//加载区域列表数据
	global $map;
	$alert = alert_open;
	$i = ($page-1)*$recPerPage;
	if($type == "area"){
	$Obj_game_qy = $map->get_name_qy(G_trimall($name),$page,$recPerPage);
	$Obj_count = $map->get_qy_mid(0,0,0);
	//var_dump($Obj_count);
$html .=<<<html
	<tr>
      <td>0 .未分区(0)</td>
      <td>所有未指定所属区域的地图</td>
      <td>{$Obj_count->num}</td>
      <td>
	  <a class="btn btn-info" href="map-list.php?area=0" >查看</a>
	  <button class="btn btn-primary disabled" type="button">修改</button>
	  <button class="btn btn-danger disabled" type="button">删除</button>
	  </td>
    </tr>
html;
	foreach($Obj_game_qy->data as $obj){
		$Obj_count = $map->get_qy_mid($obj->qyid,0,0);
			++$i;
$html .=<<<html
	<tr>
      <td>$i .$obj->qyname($obj->qyid)</td>
      <td>$obj->qydesc</td>
	  <td>{$Obj_count->num}</td>
      <td>
	  <a class="btn btn-info" href="map-list.php?area={$obj->qyid}" >查看</a>
	  <button class="btn btn-primary" type="button" {$alert} onclick="qy_new('{$obj->qyid}')">修改</button>
	  <button class="btn btn-danger" type="button" {$alert} onclick="qy_del('{$obj->qyid}')">删除</button>
	  </td>
    </tr>
html;
	}
	}
	if($type == "map"){
	$Obj_game_qy = $map->get_name_qymid(G_trimall($area),G_trimall($name),$page,$recPerPage );
	foreach($Obj_game_qy->data as $obj){
			++$i;
$html .=<<<html
	<tr>
      <td>$i .$obj->name($obj->id)</td>
      <td>$obj->desc</td>
      <td>
	  <a class="btn btn-info" href="map.php?mid={$obj->id}">编辑</a>
	  <button class="btn btn-danger" type="button" {$alert} onclick="map_del('$obj->id')">删除</button>
	  </td>
    </tr>
html;
	}
	}
	return array('list'=>$html,'recPerPage'=>$recPerPage,'recTotal'=>$Obj_game_qy->num);
}

function dtgl($val){
	global $map;
	global $attribute;
	$id = intval($val['id']);
	$mid = intval($val['mid']);
	$exit = $val['exit'];
	$brea = $val['brea'];
	switch ($val['clas']){
		case "cutoff":
			if($map->set_mid_exit_off($mid,$exit,$id,$brea)){
				$qyid=$map->get_mid_qyid($mid);
				$Obj_game_mid = $map->get_qy_mid($qyid);
				foreach($Obj_game_mid as $obj){
					$html .= "<option value='$obj->mid'";
					if($obj->mid==$mid){$html .="selected";} 
					$html .=">$obj->mname($obj->mid)</option>";
				}
				return array('title'=>"地图出口已成功断开！" ,'html'=>$html);
			  }else{
				$qyid=$map->get_mid_qyid($mid);
				$Obj_game_mid = $map->get_qy_mid($qyid);
				foreach($Obj_game_mid as $obj){
					$html .= "<option value='$obj->mid'";
					if($obj->mid==$mid){$html .="selected";} 
					$html .=">$obj->mname($obj->mid)</option>";
				}
				return array('title'=>"地图出口断开失败失败" ,'html'=>$html);
			  }
		break;
		case "exit":
			return edit_exit($val);
		break;
		case "display":
			return display_save($val);
		break;
		case 'save_map':
			$arry = $attribute->edit_record('map',$val['data']);
		break;
		case "newm":
			$qyid = $val["get_qyid"];
			$mapid = $val["get_mid"];
			   if ($mapid==""){	$title ='新建地图';}else{$title ='修改地图';   }
				$Obj= $map->get_mid_info($mapid);
				if(is_object($Obj)){$id = $Obj->id;}else{$id = 0;}
				$body .=<<<html
				<button type="button" id="attr" onClick="edit_type('attr','$id')" class="btn btn-primary edit-mid">定义属性</button>
				<button type="button" onClick="edit_type('operation','$id')" class="btn btn-primary edit-mid">定义操作</button>
				<button type="button" onClick="edit_type('event','$id')" class="btn btn-primary edit-mid">定义事件</button>
				<button type="button" onClick="edit_type('exit','$id')" class="btn btn-primary edit-mid">定义出口</button>
				<button type="button" onClick="edit_type('task','$id')" class="btn btn-primary edit-mid">任务设定</button>
				<button type="button" onClick="edit_type('display','$id')" class="btn btn-primary edit-mid">显示设定</button>
				<button type="button" onClick="edit_type('addnpc','$id')" class="btn btn-primary edit-mid">放置电脑人物</button>
				<button type="button" onClick="edit_type('addgoods','$id')" class="btn btn-primary edit-mid">放置物品</button>
				<button type="button" onClick="edit_type('copy','$id')" class="btn btn-primary edit-mid">复制场景</button>
				<button type="button" onClick="edit_type('import','$id')" class="btn btn-primary edit-mid">导入定义数据</button>
				<button type="button" onClick="edit_type('export','$id')" class="btn btn-primary edit-mid">查看定义数据</button>
				<button type="button" onClick="edit_type('update','$id')" class="btn btn-primary edit-mid">更新场景</button>
				<button type="button" onClick="edit_type('entry','$id')" class="btn btn-primary edit-mid">进入场景</button>
html;
				$arry = array('title'=> $title ,'body'=> $body ,'btn'=>$btn);
	break;
	case 'edit':
		$arry = edit_attr($val['com'],$val['id'],$val['confirm']);
	break;
	}
	return $arry;
}

function display_save($val){//保存地图显示设置
	global $dblj;
	$id = $val['id'];
	$data = json_decode($val['data']);
	var_dump($id);
	foreach ($data as $name => $val){
		if($val != "" ){
			switch($name){
				case 'play':
					$field = 'display_play';
				break;
				case 'playname':
					$field = 'display_playname';
				break;
				case 'npc':
					$field = 'display_npc';
				break;
				case 'npcname':
					$field = 'display_npcname';
				break;
				case 'goods':
					$field = 'display_goods';
				break;
				case 'goodsname':
					$field = 'display_goodsname';
				break;
			}
		if($field){
			$sql = "UPDATE `mid` SET {$field} = ? WHERE `id` = ?;";
			$stmt = $dblj->prepare($sql);
			$ret = $stmt->execute(array($val,$id));
		}
		}
	}
}

function edit_exit($val){//加载地图出口编辑器
	global $map;
	$id = intval($val['id']);
	$mid = intval($val['mid']);
	$exit = $val['exit'];
	$brea = $val['brea'];
	switch($exit){
		case "mup":
			$reverse='mdown';
		break;
		case "mdown":
			$reverse='mup';
		break;
		case "mleft":
			$reverse='mright';
		break;
		case "mright":
			$reverse='mleft';
		break;
	}

	$exitinfo = $map->get_mid_idname($reverse,$id);

if($brea == "true"){
	$title = '出口断开：';
	$body =  <<<html
   <p>确认需要断开当期出口的连接状态？</p>
   <div class="row">
   <div class="col-xs-4"><button class="btn btn-block btn-primary" style="height:55px" type="button" onclick="cut_off('{$mid}','single','{$id}','{$exit}')">
html;

  if($exitinfo->id == "0" || !$exitinfo->id ){//对向出口已关闭
	$midinf = $map->get_mid_info($mid);
	$qyid = $map->get_mid_qyid($mid);
	$qyname = $map->get_qy_name($qyid);
	
	$body .=  <<<html
	<div class='row'>
      <div class='col-xs-8'>{$qyname}<br>{$midinf->mname}({$midinf->mid})</div>
      <div class='col-xs-4'><i class='icon icon-chevron-right icon-2x'></i></div>      
    </div></button></div>
    <div class="col-xs-4">
	  <button class="btn btn-block btn-danger disabled" style="height:55px" type="button"><i class="icon icon-resize-h icon-2x"></i><br>双向断开</button></div>
    <div class="col-xs-4"><button class="btn btn-block btn-info disabled" style="height:55px" type="button" >
html;
	$exitinfo = $map->get_mid_info($id);
	$qyid = $map->get_mid_qyid($exitinfo->id);
	$qyname=$map->get_qy_name($qyid);
	
	$body .=  <<<html
	<div class='row'>
			<div class='col-xs-12'>{$qyname}<br>{$exitinfo->mname}({$exitinfo->mid})</div>      
	</div>
	</button></div> 
   </div>
html;
  }else{//双向出口可用
	$qyid = $map->get_mid_qyid($exitinfo->id);
	$qyname=$map->get_qy_name($qyid);
	
	$body .=  <<<html
	<div class='row'>
      <div class='col-xs-8'>{$qyname}<br>{$exitinfo->name}({$exitinfo->id})</div>
      <div class='col-xs-4'><i class='icon icon-chevron-right icon-2x'></i></div>      
   </div></button></div>
<div class="col-xs-4"><button class="btn btn-block btn-danger" style="height:55px" type="button" onclick="cut_off('{$mid}','double','{$id}','{$exit}')"><i class="icon icon-resize-h icon-2x"></i><br>双向断开</button></div>'
html;

	$exitinfo=$map->get_mid_idname($exit,$exitinfo->id);
	$qyid=$map->get_mid_qyid($exitinfo->id);
	$qyname=$map->get_qy_name($qyid);
	  
    $body .= <<<html
	<div class='col-xs-4'><button class='btn btn-block btn-info' style='height:55px' type='button'  onclick=\"cut_off('{$exitinfo->id}','single','{$mid}','{$reverse}')\">
html;
	$body .=  <<<html
	<div class='row'>
		<div class='col-xs-4'><i class="icon icon-chevron-left icon-2x"></i></div>
		<div class='col-xs-8'>{$qyname}<br>{$exitinfo->name}({$exitinfo->id})</div>      
	</div>
	</button></div> 
   </div>
</div>

html;
  }

return	array('title' => $title , 'body' => $body );

}else{
  if($id==0){
	$title = "添加出口：";
	$body = <<<html
	<h4>选取区域：</h4>
	<div class="row">
	  <div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" id="quyu_xs_edit" onchange="quyugl('edit',this.value)"> 
				<option value="0">请选择一级区域</option>
html;
		$Obj = $map->get_qy_all();
		foreach($Obj->data as $vobj){
		$body .="<option value='$vobj->qyid'>{$vobj->qyname}($vobj->qyid)</option>";
		}
		$body .= <<<html
		</select>
		</div>
	  </div>
	</div>
	<h4>选取地图：</h4>
	<div class="row">
      <div class="col-sm-6"><p></p>
	  	<div class="input-group">
		  <span class="input-group-addon">搜索地图</span>
			<input type="search" id="sear_dt" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onClick="dt_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
      <div class="col-sm-6"><p></p>
		<div class="input-group">
			<span class="input-group-addon">选择地图</span>
			<select class="form-control" id="ditu_xs_edit">
				<option value="0">请选择一级区域</option>
			</select>
		</div>
	  </div>
  	</div>
	<input type="hidden" id="edit_open" value="true"/> 
	<input type="hidden" id="edit_mid" value="$mid"/> 
	<input type="hidden" id="edit_exid" value="$id"/>
	<input type="hidden" id="edit_exit" value="$exit"/> 	
	<div id="edit_sear"> </div>
 </div>
</div>
html;
	$btn =<<<html
	<button type="button" class="btn btn-primary" onClick="exit_edit('single')">单向出口</button>
	<button type="button" class="btn btn-primary" onClick="exit_edit('double')">双向出口</button>
html;
		return array('title' => $title , 'body' => $body ,'btn'=> $btn ,'exbtn'=>true);
	}else{
				  $body = <<<html
	<h4>选取区域：</h4>
	<div class="row">
	  <div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" id="quyu_xs_edit" onchange="quyugl('edit',this.value)"> 
				<option value="0">请选择一级区域</option>
html;
		$Obj_game_qy =$map->get_qy_all();
		foreach($Obj_game_qy->data as $obj){
		$body .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
		}
	$body .= <<<html
		  </select>
		</div>
	  </div>
	</div>
	<h4>选取地图：</h4>
	<div class="row">
      <div class="col-sm-6"><p></p>
	  	<div class="input-group">
		  <span class="input-group-addon">搜索地图</span>
			<input type="search" id="sear_dt" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onClick="dt_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
      <div class="col-sm-6"><p></p>
		<div class="input-group">
			<span class="input-group-addon">选择地图</span>
			<select class="form-control" id="ditu_xs_edit">
				<option value="0">请选择一级区域</option>
			</select>
		</div>
	  </div>
  	</div>
	<input type="hidden" id="edit_open" value="true"/> 
	<input type="hidden" id="edit_mid" value="$mid"/> 
	<input type="hidden" id="edit_exid" value="$id"/>
	<input type="hidden" id="edit_exit" value="$exit"/> 
	<div id="edit_sear"> </div>
html;

	$button = <<<html
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="button" class="btn btn-primary" onClick="exit_edit('single')">单向出口</button>
	<button type="button" class="btn btn-primary" onClick="exit_edit('double')">双向出口</button>
html;
	
	return array('title'=>'修改出口：','body'=>$body ,'btn'=>$button);
	}
	
	}
}

function add_renwu_field($value){//编辑任务字段
	global $map;
	$data = $value['data'];
	$key = $value['key'];
	$type = $value['clas'];
	switch($type){
		case 'rwKilling':
			$type = "rwKilling";
			$title = "编辑任务添加击杀人物成功！";
			$Tips = '击杀人物 ';
		break;
		case 'rwseek':
			$type = "rwseek";
			$title = "编辑任务添加寻找物品成功！";
			$Tips = '寻找物品 ';
		break;
		case 'skills':
			$type = "skills";
			$title = "编辑电脑人物添加技能成功！";
			$Tips = '添加技能';
		break;
		case 'npc':
			$type = "npc";
			$title = "编辑地图添加电脑人物成功！";
			$Tips = '添加电脑人物';
		break;
		case 'goods':
			$type = "goods";
			$title = "编辑地图添加物品成功！";
			$Tips = '添加地图物品';
		break;
		case 'edit_task':
			$type = "task";
			$title = "编辑地图添加任务成功！";
			$Tips = '编辑地图';
		break;
		
	}
	if($data!=""){
		$obj = $map->get_mid_info($key);
		$val = add_branch_Packing_data($obj->$type,$data);
		 if($map->set_mid_field($key,$type,$val)){
			$body = "编辑场景{$Tips}{$data['name']} 添加成功！";
			return array('title'=>$title ,'body'=>$body ,'reloading'=>true);
		 }else{
			 
		 }
	 }
}

function addobj($val){//添加地图相关附属属性
	global $searchBox;
	$key = $val['mid'];
	switch($val['clas']){
		case 'npc':
			$title = "编辑场景添加电脑人物：";
			$com = $searchBox->create_search("输入电脑人物名","map-gl.php","map","npc","电脑人物名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'goods':
			$title = "编辑场景添加物品：";
			$com = $searchBox->create_search("输入物品名","map-gl.php","map","goods","物品名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
		case 'task':
			$title = "编辑场景添加任务：";
			$com = $searchBox->create_search("输入任务名","map-gl.php","map","task","任务名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
	}
	return $arry;
}

function edit_attr($type,$id,$confirm){//编辑地图具体信息
	global $attribute;
	global $map;
	global $event;
	global $player;
	global $searchBox;
	$alert_open = alert_open;
	$map_info =  $map->get_mid_info($id);//获取地图信息
	switch($type){
		case 'attr':
			$html = '<h4>编辑场景"'.$map_info->name .'"的属性：</h4>';
			$html .=  $attribute->get_attribute_edit("mid",$map_info->id,array(),array());
			$html .= "<button type='button' onClick='save_map()' class='btn btn-primary' {$alert_open}  >保存修改</button><hr>";
			return array('title'=>'修改地图：','body'=>$html,);
		break;
		case 'exit':
			if(isset($map_info) && is_object($map_info)){
				$html = "<h3>编辑场景\"{$map_info->name}\"的出口：</h3>
				<table class='table table-bordered table-condensed'>
				<tbody>
				<tr><td><b>地图名:</b></td><td>$map_info->name</td></tr>
				<tr><td><b>刷新:</b></td><td>$map_info->ms</td></tr>
				<tr><td><b>地图简介:</b></td><td>$map_info->desc</td></tr>
				</tbody></table>
				<table class='table table-fixed table-bordered'><tbody><tr><td></td><td>";
				$html .=  create_export($map_info->id,'mup');
				$html .=  "</td><td></td></tr><tr><td>";
				$html .=  create_export($map_info->id,'mleft');
$html .=  <<<html
	</td><td> 
	<div class="row">
	  <div class="col-xs-12 col-sm-9">
		<button class="btn btn-block btn-primary" onclick="edit_type('attr','{$map_info->id}')" >{$map_info->name}({$map_info->id})</button>
	  </div>
	  <div class="col-xs-12 col-sm-3">
		<button class="btn btn-block btn-danger  type="button">删除</button>
	  </div>
	</div>
	</td><td>
html;
				$html .=  create_export($map_info->id,'mright');
				$html .=  "</td></tr> <tr><td></td><td>";
				$html .=  create_export($map_info->id,'mdown');
				$html .=  "</td><td></td></tr></tbody></table>";
				
			}
			return array('body'=>$html,'area'=>$map_info->qy);
		break;
		case 'operation':
			$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_array_list('map',$thead ,$map_info->operation,$map_info->id,"operation");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑场景"{$map_info->name}"的操作：</h3></td><td style="text-align:right">
	<button class="btn btn-primary" onClick="new_operation('add','map','{$map_info->id}')" type="button">添加操作</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'event':
			return $event->load_event_list('map',$map_info->id);
		break;
		case 'task':
			$thead = "<tr><th>任务ID</th><th>任务名</th><th>触发条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('map',$thead ,$map_info->task,$map_info->id,"edit_task");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑场景"{$map_info->name}"的任务设置：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('task','{$map_info->id}')" {$alert_open} class="btn btn-primary">添加任务</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'display':
			$html =<<<html
	<h3>[编辑场景<b>"{$map_info->name}"</b>的显示设定]</h3>
	<form id="display">
	场景显示玩家条件表达式:<textarea class="form-control" name="play" rows="3">{$map_info->display_play}</textarea>
	场景显示玩家名称表达式:<textarea class="form-control" name="playname" rows="3">{$map_info->display_playname}</textarea>
	场景显示电脑人物条件表达式:<textarea class="form-control" name="npc" rows="3">{$map_info->display_npc}</textarea>
	场景显示电脑人物名称表达式:<textarea class="form-control" name="npcname" rows="3">{$map_info->display_npcname}</textarea>
	场景显示物品条件表达式:<textarea class="form-control" name="goods" rows="3">{$map_info->display_goods}</textarea>
	场景显示物品名称表达式:<textarea class="form-control" name="goodsname" rows="3">{$map_info->display_goodsname}</textarea>
	</form>
	<br>
	<button class="btn btn-primary btn-block" onClick="map_display('{$map_info->id}')" type="button">保存修改</button>

	<hr>
html;
			return array('body'=>$html);
		break;
		case 'addnpc':
			$thead = "<tr><th>电脑人物名</th><th>数量表达式</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('map',$thead ,$map_info->npc,$map_info->id,"npc");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑场景"{$map_info->name}"的电脑人物：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('npc','{$map_info->id}')" {$alert_open} class="btn btn-primary">添加电脑人物</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'addgoods':
			$thead = "<tr><th>物品名</th><th>数量表达式</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('map',$thead ,$map_info->goods,$map_info->id,"goods");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑场景"{$map_info->name}"的物品：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('goods','{$map_info->id}')"  {$alert_open} class="btn btn-primary">添加物品</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
			return array('body'=>$html);
		break;
		case 'copy':
			$html ="复制地图场景";
			return array('body'=>$html);
		break;
		case 'import':
			$html =<<<html
	<h3>[编辑场景<b>"{$map_info->name}"</b> 导入地图设置]</h3>
	地图设置数据:<textarea class="form-control" rows="15"></textarea>	<br>
	<button type="button" onclick="map_edit()" class="btn btn-primary">确认导入</button>
	<hr>
html;
			return array('body'=>$html);
		break;
		case 'export':
			$html =<<<html
	<h3>[编辑场景<b>"{$map_info->name}"</b> 导出地图设置]</h3>
	地图设置数据:<textarea class="form-control" rows="15"></textarea>
	<br>
html;
			return array('body'=>$html);
		break;
		case 'update':
			$html ="更新场景";
			return array('body'=>$html);
		break;
		case 'entry':
			$html ="进入场景";
			$player->player_relocation_mid($id);
			return array('href'=>'../game.php');
		break;
	}

}

function edit_map($val){
	global $map;
	global $attribute;
	switch ($val['clas']){
		case "new":
			if(isset($val['data'])){
				$id = $attribute->add_record('mid',$val['data']);
				if($id>0){
					$arry = array('title'=>'新建地图：','body'=>'新的地图已成功建立！','repage' =>true );
				}else{
					$arry = array('title'=>'新建地图：','body'=>'新建地图的操作失败了！','repage' =>true );
				}
			}else{
				$html = '<h4>编辑新建场景的属性：</h4>';
				$default = array('qy'=>$val['area']);
				$html .=  $attribute->get_attribute_new("mid",array(),$default);
				$btn = '<button type="button" onClick="map_new(\'true\')" class="btn btn-primary" >确认创建</button>';
				$arry = array('title'=>'新建地图：','body'=>$html,'btn'=>$btn,'exbtn'=>true);
			}
		break;
		case "edit":
			  $query_str = $_POST["data"];
			  parse_str($query_str,$query_arr);
			  if($map->set_mid_edit("edit",$query_arr)){
				$Obj_game_mid = $map->get_qy_mid($query_arr["mqy"]);
				foreach($Obj_game_mid as $obj){
				$html .= "<option value='$obj->mid'>$obj->mname($obj->mid)</option>";
				}
				$arry = array('title'=>"地图信息修改成功！" ,'html'=>$html,'val'=>$_POST["get_mid"]);
			  }
		break;
		case "exit":
			$bool = $map->set_mid_exit_on($val["exitype"], $val["nexit"],$val["newqy"],$val["newmid"],$val["yexid"],$val["mapid"]);
			if($bool){$arry = array( 'title'=>"地图出口编辑"  , 'body'=>"地图出口编辑成功！" ,'reload' =>true );}
		break;
		case "del":
			$map_info = $map->get_mid_info($val['mid']);
			if($val['confirm'] == 'true' ){
				$bool = $map->del_mid_id($map_info->id);
				if($bool){
					$arry = array( 'title'=>"地图删除成功！"  , 'body'=>"删除当前选中地图：<h3>{$map_info->name}({$map_info->id})</h3>删除成功！" ,'repage' =>true );
				}else{
					$arry = array( 'title'=>"地图删除失败！"  , 'body'=>"删除当前选中地图：<h3>{$map_info->name}({$map_info->id})</h3>删除删除失败！" ,'repage' =>true );
				}
			}else{
				$arry = array( 
					'title'	=>"确认删除当前选中地图？",
					'body'	=>"确认删除当前选中地图：<h3>{$map_info->name}({$map_info->id})</h3>这个地图吗？" ,
					'btn'	=>"<button class='btn btn-danger' type='button' {$alert} onclick='map_del(\"{$map_info->id}\",\"true\")'>删除</button>",
					'exbtn'	=>true 
					);
			}
		break;
	}
	return $arry;
}

function birth($val){//设置玩家出生地信息
	global $map;
	global $sys;
	switch ($val['clas']){
		case "write":
			$obj = $map->get_mid_info($val["set_chusheng"]);
			if(isset($obj) && is_object($obj)){
				$sys->set_system_config("游戏","出生地",$obj->id);
				$qyinfo = $map->get_qy_name($obj->qy);
				echo "{$qyinfo}--{$obj->name}({$obj->id})";
			}
		break;
		case "qydt":
			$qyid = $val["chusheng"];
			$Obj_game_mid = $map->get_qy_mid($qyid);
			foreach($Obj_game_mid->data as $obj){
				echo  "<option value='$obj->id'>$obj->name($obj->id)</option>";
			}
		break;
	}
}

function qygl($val){
	global $map;
	switch ($val['clas']){
		case "newqy":
			$qy_id = $val["qy_id"];
			if($qy_id==""){$qy_id=0;}
			$jieguo = $map->set_qy_gl($qy_id,G_trimall($val["qy_name"]),G_trimall($val["qy_desc"]),0);
			if($jieguo){if($qy_id==""){echo "新建区域成功！";}else{echo "维护区域成功！";};}else{if($qy_id==""){echo "新建区域失败！";}else{echo "维护区域失败！";}}
		break;
		case 'del':
			$qy_info = $map->get_qy_info($val['area']);
			if($val['confirm'] == 'true' ){
				$bool = $map->del_qy_id($qy_info->qyid);
				if($bool){
					$arry = array( 'title'=>"地图删除成功！"  , 'body'=>"删除当前选中地图：<h3>{$qy_info->qyname}({$qy_info->qyid})</h3>删除成功！" ,'repage' =>true );
				}else{
					$arry = array( 'title'=>"地图删除失败！"  , 'body'=>"删除当前选中地图：<h3>{$qy_info->qyname}({$qy_info->qyid})</h3>删除删除失败！" ,'repage' =>true );
				}
			}else{
				$arry = array( 
					'title'	=>"确认删除当前选中区域？",
					'body'	=>"确认删除当前选中区域：<h3>{$qy_info->qyname}({$qy_info->qyid})</h3>这个区域吗？" ,
					'btn'	=>"<button class='btn btn-danger' type='button' {$alert} onclick='qy_del(\"{$qy_info->qyid}\",\"true\")'>删除</button>",
					'exbtn'	=>true 
					);
			}
		
		break;
		case "qydt":
			$qy_id = $val["qyid"];
			$Obj_game_mid = $map->get_qy_mid($qy_id);
			if(!isset($val['edit'])){$arry .= '<option value="">新建地图</option>';}
			foreach($Obj_game_mid->data as $obj){
				$arry .= "<option value='$obj->id'";
				if($_POST["def"]==$obj->id){$arry .=  "selected" ;}
					$arry .= ">$obj->name($obj->id)</option>
					";
			}
		break;
		case "qyxg":
				$qy_id = $val["get_qy"];
				if(isset($qy_id)){$mqy = $map-> get_qy_info($qy_id);}
				$body = <<<EOF
				<h5>区域基本信息设置</h5><input type="hidden" name="basic" value="open" />
				<input type="hidden" id="qy_id" value="$mqy->qyid" />
				<p>区域名：<p><input type="text" class="form-control" id="qy_name" placeholder="区域名" value="$mqy->qyname">
				<p>区域简介：<p><textarea class="form-control" rows="3" id="qy_desc"  placeholder="区域简介">$mqy->qydesc</textarea>
EOF;
				if($qy_id !="" ){
				$btn = '<input type="submit" class="btn btn-primary" data-dismiss="modal" id="newqy" value="保存修改" >';
				}else {
				$btn = '<input type="submit" class="btn btn-primary" data-dismiss="modal" id="newqy" value="新建区域" >';
				}

			$arry = array('title'=> '创建区域' ,'body'=> $body ,'btn' =>$btn,'exbtn'=>true);
		break;
		case "Refreshqy":
				echo '<option value="">请选择一级区域</option>';
				$Obj_game_qy =$map->get_qy_all();
				foreach($Obj_game_qy->data as $obj){
				echo "<option value='".$obj->qyid ."'>".$obj->qyname ."(".$obj->qyid .")"."</option>";}
		break;
	}
	return $arry;
}

function create_export($mapid,$exit){//创建编辑器地图快捷编辑器出口连接
	global $map;
	$mid = $map->get_mid_idname($exit,$mapid);
	$alert = alert_open;
	if(isset($mid)){
return <<<html
  <div class="row">
	<div class="col-xs-12 col-sm-6">
		<button class="btn btn-block btn-primary" type="button" onclick="edit_type('exit','{$mid->id}')">{$mid->name}({$mid->id})</button>
	</div>
	<div class="col-xs-6 col-sm-3">
		<button class="btn btn-block" {$alert} onclick="map_exit_edit('{$exit}','{$mid->id}','{$mapid}')" >更换</button>
	</div>
	<div class="col-xs-6 col-sm-3">
		<button class="btn btn-block btn-warning" {$alert} onclick="map_exit_edit('{$exit}','{$mid->id}','{$mapid}','true')">断开</button>
	</div>
  </div>
html;
	}else{
	return  <<<html
	<button class="btn btn-block" {$alert} onclick="map_exit_edit('{$exit}','0','{$mapid}')">添加出口</button>
html;
	}
}

?>