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
			$arry = edit_goods($value);
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

function add_renwu_field($value){//编辑任务字段
	global $goods;
	$data = $value['data'];
	$key = $value['key'];
	$type = $value['clas'];
	switch($type){
		case 'edit_task':
			$type = "task";
			$title = "编辑物品添加任务成功！";
		break;
		
	}
	if($data!=""){
		$obj = $goods->get_goods_info($key);
		$val = add_branch_Packing_data($obj->$type,$data);
		 if($goods->set_goods_field($key,$type,$val)){
			$body = "编辑物品 {$data['name']} 添加成功！";
			return array('title'=>$title ,'body'=>$body ,'reloading'=>true);
		 }else{
			 
		 }
	 }
}

function edit_goods($val){
	global $goods;
	$alert_open = alert_open;
	$goods_info = $goods->get_goods_info($val['id']);
	$tit = $goods->get_goods_type($goods_info->type);
	switch($val['com']){
		case 'attr':
			global $attribute;
			$html = $attribute->get_attribute_edit("daoju",$goods_info->id,array(),array(),new_goods($goods_info->type,$goods_info->qy,$goods_info->use_attr)); 
			$html .= "<button type=\"button\" ". alert_open ." onclick=\"save_goods()\" class=\"btn btn-primary\">确认创建</button>";
		break;
		case 'operation':
			global $searchBox;
			$thead = "<tr><th>操作ID</th><th>操作名</th><th>操作出现条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_array_list('daoju',$thead ,$goods_info->operation,$goods_info->id,"operation");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑{$tit['1']}"{$goods_info->name}"的操作：</h3></td><td style="text-align:right">
	<button class="btn btn-primary" onClick="new_operation('add','daoju','{$goods_info->id}')" type="button">添加操作</button>
</td></tr>
</table>
<div id="window">{$skill_list}<div>
html;
		break;
		case 'event':
			global $event;
			return $event->load_event_list('goods',$goods_info->id);
		break;
		case 'task':
			global $searchBox;
			$thead = "<tr><th>任务ID</th><th>任务名</th><th>触发条件</th><th>操作</th></tr>";
			$skill_list =  $searchBox->generate_list('daoju',$thead ,$goods_info->task,$goods_info->id,"edit_task");
			$html = <<<html
<table class="table table-condensed"> 
<tr><td><h3>编辑{$tit['1']}"{$goods_info->name}"的任务设置：</h3></td><td style="text-align:right">
	<button type="button" onclick="addobj('task','{$goods_info->id}')" {$alert_open} class="btn btn-primary">添加任务</button>
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
	<h3>[编辑{$tit['1']}<b>"{$goods_info->name}"</b> 导入物品设置]</h3>
	物品设置数据:<textarea class="form-control" rows="15"></textarea>	<br>
	<button type="button" onclick="map_edit()" class="btn btn-primary">确认导入</button>
	<hr>
html;
		break;
		case 'export':
			$html =<<<html
	<h3>[编辑{$tit['1']}<b>"{$goods_info->name}"</b> 导出物品设置]</h3>
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

function addobj($val){//添加地图相关附属属性
	global $searchBox;
	$key = $val['mid'];
	switch($val['clas']){
		case 'task':
			$title = "编辑物品添加任务：";
			$com = $searchBox->create_search("输入任务名","goods.php","daoju","task","任务名",$key);
			$arry = array('title'=>$title,'body'=>$com);
		break;
	}
	return $arry;
}

require_once "html/header.php";

$com = $_GET['com'];
$type = $_GET['type'];
$goods_id = $_GET['id'];

if($com =='add'){
	$tit = $goods->get_goods_type($type);
	$title = "新建{$tit['1']}";
	$html = $attribute->get_attribute_new("daoju",array(),array(),new_goods($type)); 
	$html .= "<button type=\"button\" ". alert_open ." onclick=\"save_goods()\" class=\"btn btn-primary\">确认创建</button>";
}elseif($com =='edit'){
	$goods_info = $goods->get_goods_info($goods_id);
	$tit = $goods->get_goods_type($goods_info->type);
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

function new_goods($type,$qy="",$consume=""){//新建物品
	global $map;
	global $attribute;
	$html .= <<<html
	<div class='form-group'>
		<label class='col-sm-2'>类别</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="type"> 
			<option value='consume' 
html;
	if($type=="consume"){$html .= "selected";};
	$html .= ">消耗品</option>
			<option value='book'";
	if($type=="book"){$html .= "selected";};
	$html .= ">书籍</option>
			<option value='taskitems'";
	if($type=="taskitems"){$html .= "selected";};
	$html .= ">任务物品</option>
			<option value='other'";
	if($type=="other"){$html .= "selected";};
$html .= <<<html
>其他</option>
</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>选择区域：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="qy" id="quyu"> 
			<option value="0">请选择一级区域</option>
html;
		$Obj_game_qy = $map->get_qy_all(1,0);
		foreach($Obj_game_qy->data as $obj){
			$html .= "<option value='$obj->qyid' ";
			if($qy == $obj->qyid){$html .= "selected";};
			$html .= ">$obj->qyname($obj->qyid)</option>";
		}
$html .= "</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>搜索区域：</label>
	  <div class='col-md-6 col-sm-10'>
		<div class='input-group'>
			<input type='search' id='sear_qy' class='form-control'>
			<span class='input-group-btn'>
				<button class='btn btn-default' onclick='qy_sear(\"edit\")' type='button'>搜索</button>
			</span>
		</div>
	  </div>
	</div><div id='area_list'></div>";
	
	if($type=="consume"){
	$html .= <<<html
	<div class='form-group'>
		<label class='col-sm-2'>使用目标：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="use_attr"> 
html;
		$Obj_cfg = $attribute->get_table_config("game1");
		foreach($Obj_cfg as $obj){
		  $typ = $obj->column_type;
		  switch($typ){
			case "int(11)":
				$hs = json_decode($obj->column_comment);
				if($hs->consume == "t"){
					$html .= "<option value='{$obj->column_name}'";
					if($consume == $obj->column_name){$html .= "selected";};
					$html .= ">".urldecode($hs->Notes)."({$obj->column_name})</option>";
				}
			break;
		  }
		}
$html .= "</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>使用效果值：</label>
	  <div class='col-md-6 col-sm-10'>
		<input type='search' name='use_value' class='form-control'>
	  </div>
	</div>";

	}
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
h3 {
	margin-top: 5px;
}
</style>

<h2>设计游戏物品-<?php echo $title;?></h2>
<span class="con"></span> 
<?php
echo $html;
?>
<hr>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>

<script type="text/javascript"> 
function save_goods(){//保存类型新建和修改
    var d = {};
    var t = $('#add').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var data=JSON.stringify(d);
	var clas=$("#list").val();
	$.post('goods-gl.php',{basic:"open",type:"save","data":data},function(data) {		
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
	$.post('goods.php',{basic:"open",type:"edit",com:type,id:id},function(data) { 
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