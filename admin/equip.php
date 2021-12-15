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
		case 'edit':
			$arry = edit_equip($value);
		break;
		case "addobj":
			$arry = addobj($value);
		break;
		case "search":
			$arry =  $searchBox->search($value);
		break;
		case "add_field":
			$arry = add_renwu_field($value);
		break;
		case 'Reset':
			$arry =  $searchBox->reloading('daoju',$value['obj'],$value['id']);
			$arry = array('body'=>$arry);
		break;
		case "editing_step":
			$arry = editing_step($value);
		break;
	}
	ajax_alert($arry);
	exit();
}

function edit_equip($val){
	global $equip;
	$alert_open = alert_open;
	$equip_info = $equip->get_equip_info($val['id']);
	$tit = $equip->get_equip_type($equip_info->type);
	switch($val['com']){
		case 'attr':
			global $attribute;
			$html = $attribute->get_attribute_edit("daoju",$equip_info->id,array(),array(),new_equip($equip_info->clas,$equip_info)); 
			$html .= '<button type="button" '. alert_open .' onclick="save_equip()" class="btn btn-primary">保存修改</button>';
		break;
		case 'operation':
			global $searchBox;
			$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_array_list('daoju',$thead ,$equip_info->operation,$equip_info->id,"operation");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑{$tit['1']}"{$equip_info->name}"的操作：</h3></td><td style="text-align:right">
	<button class="btn btn-primary" onClick="new_operation('add','daoju','{$equip_info->id}')" type="button">添加操作</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
		break;
		case 'event':
			global $event;
			return $event->load_event_list('equip',$equip_info->id);
		break;
		case 'task':
			global $searchBox;
			$thead = "<tr><th>任务ID</th><th>任务名</th><th>触发条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('daoju',$thead ,$equip_info->task,$equip_info->id,"edit_task");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑{$tit['1']}"{$equip_info->name}"的任务设置：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('task','{$equip_info->id}')" {$alert_open} class="btn btn-primary">添加任务</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
		break;
		case 'copy':
			$html ="复制地图场景";
		break;
		case 'import':
			$html =<<<html
	<h3>[编辑{$tit['1']}<b>"{$equip_info->name}"</b> 导入物品设置]</h3>
	物品设置数据:<textarea class="form-control" rows="15"></textarea>	<br>
	<button type="button" onclick="map_edit()" class="btn btn-primary">确认导入</button>
	<hr>
html;
		break;
		case 'export':
			$html =<<<html
	<h3>[编辑{$tit['1']}<b>"{$equip_info->name}"</b> 导出物品设置]</h3>
	物品设置数据:<textarea class="form-control" rows="15"></textarea>
	<br>
html;
		break;
		case 'update':
			$html ="更新场景";
		break;
	}
	return array('body'=>$html);
}

require_once "html/header.php";
$com = $_GET['com'];
$type = $_GET['type'];
$goods_id = $_GET['id'];

if($com =='add'){
	$tit = $equip->get_equip_type($type);
	$title = "新建{$tit['1']}";
	$html = $attribute->get_attribute_new("daoju",array(),array(),new_equip($type)); 
	$html .= "<button type=\"button\" ". alert_open ." onclick=\"save_goods()\" class=\"btn btn-primary\">确认创建</button>";
}elseif($com =='edit'){
	$goods_info = $equip->get_equip_info($goods_id);
	$tit = $equip->get_equip_type($goods_info->clas);
	$title = "修改{$tit['1']}";
$html =<<<html
<table  class="table"> 
<tr><td><b>{$title }</b>-{$goods_info->name}({$goods_info->id})</td><td style="text-align:right">
</td></tr>
</table>
<div id="map_edit">
	<button type="button" id="attr" onClick="edit_type('attr','{$goods_info->id}')" class="btn btn-primary edit-mid">定义属性</button>
	<button type="button" onClick="edit_type('operation','{$goods_info->id}')" class="btn btn-primary edit-mid">定义操作</button>
	<button type="button" onClick="edit_type('event','{$goods_info->id}')" class="btn btn-primary edit-mid">定义事件</button>
	<button type="button" onClick="edit_type('task','{$goods_info->id}')" class="btn btn-primary edit-mid">任务设定</button>
	<button type="button" onClick="edit_type('update','{$goods_info->id}')" class="btn btn-primary edit-mid">更新物品</button>
	<button type="button" onClick="edit_type('copy','{$goods_info->id}')" class="btn btn-primary edit-mid">复制物品</button>
	<button type="button" onClick="edit_type('import','{$goods_info->id}')" class="btn btn-primary edit-mid">导入定义数据</button>
	<button type="button" onClick="edit_type('export','{$goods_info->id}')" class="btn btn-primary edit-mid">查看定义数据</button>
</div>
<hr>
<div id="con"></div>

<div id="edit_body"></div>
html;
}


function new_equip($type,$eq_obj=null){//新建装备
	global $map;
	global $sys;
	if(!is_object($eq_obj)){
		$eq_obj = (object)[];
	}
	//var_dump($obj);
	$html .= <<<html
	<input type="hidden" name="type" id="list" class="form-control" value="equip"> 
	<div class='form-group'>
		<label class='col-sm-2'>类别</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="clas"> 
			<option value='weapon' 
html;
	if($type =="weapon"){$html .= "selected";};
	$html .= ">兵器</option>
			<option value='weaponinlay'";
	if($type =="weaponinlay"){$html .= "selected";};
	$html .= ">兵器镶物</option>
			<option value='equip'";
	if($type =="equip"){$html .= "selected";};
	$html .= ">装备</option>
			<option value='equipinlay'";
	if($type =="equipinlay"){$html .= "selected";};
$html .= "
>装备镶物</option>
</select>
		</div>
	</div>";
	
	if($type !="weaponinlay" && $type !="equipinlay" ){
		if($type=="weapon"){
			$equip_clas = json_decode($sys->get_system_config('system','weapon_class'));
		}else{
			$equip_clas = json_decode($sys->get_system_config('system','equip_class'));
		}
$html .= "
	<div class='form-group'>
		<label class='col-sm-2'>子类别</label>
		<div class='col-md-6 col-sm-10'>
		<select class='form-control' name='tool'> ";
		foreach($equip_clas as $equip_cl){
			if(is_object($equip_cl)){
				$html .= "<option value='{$equip_cl->id}' ";
				if($eq_obj->tool == $equip_cl->id){$html .= "selected";};
				$html .= ">{$equip_cl->name}</option>";
			}
		}
$html .= "</select>
		</div>
	</div>";}
$html .= "
	<div class='form-group'>
		<label class='col-sm-2'>选择区域：</label>
		<div class='col-md-6 col-sm-10'>
		<select class='form-control' name='qy' id='quyu'> 
			<option value='0'>请选择一级区域</option>";
		$Obj_game_qy = $map->get_qy_all(1,0);
		foreach($Obj_game_qy->data as $obj){
			$html .= "<option value='$obj->qyid'";
				if($eq_obj->qy == $obj->qyid){$html .= "selected";};
			$html .= ">$obj->qyname($obj->qyid)</option>";
		}
$html .= "</select>
		</div>
	</div>
	<div class='form-group'>
		<label for='sear_qy' class='col-sm-2'>搜索区域：</label>
	  <div class='col-md-6 col-sm-10'>
		<div class='input-group'>
			<input type='search' id='sear_qy' class='form-control'>
			<span class='input-group-btn'>
				<button class='btn btn-default' onclick='qy_sear(\"edit\")' type='button'>搜索</button>
			</span>
		</div>
	  </div>
	</div><div id='area_list'></div>";
	if($type =="weapon" || $type =="weaponinlay" ){
	$html .= "
	<div class='form-group'>
	  <label for='attack_value' class='col-sm-2'>攻击力：</label>
	  <div class='col-md-6 col-sm-10'>
		<input type='search' id='attack_value' name='attack_value' class='form-control' value='{$eq_obj->attack_value}'>
	  </div>
	</div>";}
	if($type =="equip" || $type =="equipinlay" ){
	$html .= "
	<div class='form-group'>
	  <label  for='resist_value' class='col-sm-2'>防御力：</label>
	  <div class='col-md-6 col-sm-10'>
		<input type='search' id='resist_value' name='resist_value' class='form-control' value='{$eq_obj->resist_value}'>
	  </div>
	</div>";}
	if($type !="equipinlay" && $type !="weaponinlay"){
	$html .= "
	<div class='form-group'>
	  <label for='embed_count' class='col-sm-2'>可镶宝物数：</label>
	  <div class='col-md-6 col-sm-10'>
		<input type='search' id='embed_count' name='embed_count' class='form-control' value='{$eq_obj->embed_count}'>
	  </div>
	</div>";}
	$html .= "
	<div class='form-group'>
	  <label for='use_require' class='col-sm-2'>装备条件表达式：</label>
	  <div class='col-md-6 col-sm-10'>
		<textarea class='form-control' id='use_require' name='use_require'>{$eq_obj->use_require}</textarea>
	  </div>
	</div>
	";
	return $html;
}


?>
<style>
li a{
	background:lightgray;
}
.table {
	margin-bottom: 10px;
}
ul {
	margin-top: 5px;
    margin-bottom: 10px;
}
</style>

<h2>设计游戏装备</h2>
<span class="con"></span> 
<?php
echo $html;
?>
<hr>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>

<script type="text/javascript"> 
function save_equip(){//保存类型新建和修改
    var d = {};
    var t = $('#add').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var data=JSON.stringify(d);
	var clas=$("#list").val();
	$.post('equip-gl.php',{basic:"open",type:"save",data:data},function(data) {		
		if(!data.error){$(".con").html(data.title);}
		ajax_alert(data);
    }) 
}


function edit_type(type,id){//编辑物品数据
	if(type=="attr"){
		$('.edit-mid').removeClass("btn-success").addClass("btn-primary");
		$('#'+type).removeClass("btn-primary").addClass("btn-success");
	};
	var area_id = $('#area_id').val();
	$.post('equip.php',{basic:"open",type:"edit",com:type,id:id},function(data) { 
		$('#edit_body').html(data.body);
		if(!data.area){
			if(data.href){ $(location).prop('href', data.href)};
		}
    }) 
}


function qy_sear(){//搜索地图区域数据
	var name = $('#sear_qy').val();
	$.post('map-gl.php',{basic:"open",type:"sear",clas:'area',val:name},function(data) { 
		$("#area_list").html(data.body);
	});
}

function ajax_load_qy(){
	var qyid = $("input[name='qyid']:checked").val();
	$("#quyu").val(qyid);
	$("#area_list").html("");
}

$(document).ready(function(){ 
  $(document).on("click", ".edit-mid", function() {
	$('.edit-mid').removeClass("btn-success").addClass("btn-primary");
	$(this).removeClass("btn-primary").addClass("btn-success");
  });
})

<?php
 $post_path = "goods.php";
 require_once "js/edit-data.php";
 require_once "js/operation-data.php"; 
?>
</script>
<?php
require_once "html/footer.php";
?>