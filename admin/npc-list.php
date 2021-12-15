<?php
require_once "user_rights.php";

$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);

if(isset($value['basic'])){
  switch ($value['type']){
	case 'page':
		$test = $npc->get_qy_npc($value['qyid'],$value['page'],$value['recPerPage']);
foreach($test->data as $obj){
	$html .=  <<<html
	<tr>
		<td>{$obj->id}</td>
		<td>{$obj->name}({$obj->qy})</td>
		<td>{$obj->lvl}</td>
		<td><a class='btn btn-primary' href='npc.php?type=edit&nid={$obj->id}&area={$value['qyid']}'>查看</a>
		<button class='btn btn-danger' type='button' data-position="100px" data-toggle="modal" data-target="#ajax-alert"  onclick='del_npc("{$obj->id}")'>删除</button></td>
	</tr>
html;
}
		$arry = array('html'=>$html,'recTotal'=>$test->num);
	break;

	}
	ajax_alert($arry);
exit;
}


$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
require_once "html/header.php";
?>
<h2>设计电脑人物</h2>
<span class="con"></span> 
<div id="win">
<table  class="table"> 
<tr><td><b>新建一个电脑人物</b></td><td style="text-align:right">
<button type="button" onclick="new_npc('<?php echo $_GET['qyid'];?>')"  data-position="100px" data-toggle="modal" data-target="#ajax-alert" class="btn btn-primary"> 新建电脑人物</button>
</td></tr>
</table>
<table class="table table-bordered table-condensed">
  <thead id="list_thead">
	<tr>
      <th>NPC-ID</th>
      <th>NPC名</th>
	  <th>NPC等级</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="list_tbody"></tbody>
</table>
<ul class="pager" id="pager" style="margin-bottom: 0px;margin-top: 0px;"></ul>
</div>
<div id="edit"></div>

<hr>
<a href="npc-area.php" class="btn btn-block ">返回区域</a>

<script type="text/javascript"> 
$('#pager').pager({
    page: 1,
	recPerPage:20,
	elements:['first','prev','nav','next','last','goto','size_menu','total_text','page_of_total_text'],
});

$(document).ready(function () { 
$('#pager').on('onPageChange', function(e, state, oldState) {
	if (state.page !== oldState.page || state.recPerPage !== oldState.recPerPage) {reload();}
});
	reload();
}); 

function reload(){
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	var idata = {basic:'open',type:'page',qyid:<?php echo $_GET['qyid'];?>,page:pager.page,recPerPage:pager.recPerPage};
	$.post('npc-list.php',idata,function(data){
		$('#list_tbody').html(data.html);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
	})
}

function list_reload(){
		$('#win').show();
		$('#edit').hide();
		$('#edit').html("");
}

function edit_npc(npcid,com,confirm){//编辑npc基本资料
	$.post('npc-gl.php',{basic:"open",type:"edit",npcid:npcid,com:com,confirm:confirm},function(data) { 
	if(data.edit){
		$('#win').hide();
		$('#edit').show();
		$('#edit').html(data.body);
	}
		ajax_alert(data);
	});
}

function del_npc(npcid,confirm){//删除NPC
	$.post('npc-gl.php',{basic:"open",type:"del",id:npcid,confirm:confirm},function(data) { 
		ajax_alert(data);
	});
}

function new_npc(area_id,confirm){//加载npc新建菜单
	if(!confirm){
		var idata = {basic:"open",type:"new",area:area_id};
	}else{
		var area_id = $('#area_id').val();
		var params = $("#add").serializeArray();
		var values = {};
		for( x in params ){
			values[params[x].name] = params[x].value;
		}
		var idata = {basic:"open",type:"new",area:area_id,data:JSON.stringify(values)};
	}
	$.post('npc-gl.php',idata,function(data) { 
		ajax_alert(data);
		
	})
}

function new_npc_clas(clas,npcid,confirm){//加载npc新建菜单
	$.post('npc-gl.php',{basic:"open",type:"new_clas",clas:clas,id:npcid,confirm:confirm},function(data) {
		if(!confirm){
			ajax_alert(data);
		}else{
		//edit(data);
		}
	});
}

function load_add_search(val,type,clas,com,key){
	$.post("npc-gl.php",{basic:"open",type:type,clas:clas,key:val,operation:com,parent:key,only:true},function(data) {
		$("#search-value").html(data);
	})
 }
 

function add_branch(type,key,confirm){//添加一项步骤数据
  if(!confirm){var confirm=false;}
  if(confirm){
	 var id = $("#add-id").val();
	 var name = $("#add-name").val();
	 var val = $("#add-value").val();
	 var num = $("#add-num").val();
	 if(!isEmpty(id)){
		var iadd = {id:id,name:name,val:val,num:num};
	 }else{
		var iadd = {name:name,val:val,num:num};
	 }
	 var data = {basic:"open",type:"add_field",clas:type,key:key,confirm:confirm,data:iadd};
  }else{
	 var data = {basic:"open",type:"add_field",clas:type,key:key,confirm:confirm}
  }
	$.post('npc-gl.php',data,function(data) {
		ajax_alert(data);
		if(data.reloading){Reset(key,type);}
	})
}


function Reset(obj,id){
	$.post('npc-gl.php',{basic:"open",type:"Reset",obj:obj,id:id},function(data) {
	$("#window").html(data);
	$("#new_task").html("");
	})
}
function quyugl(){//区域管理选择区域加载地图
	var vs = $("#quyu_xs").val();
	var edit = "false";
	 $.post('npc-gl.php',{basic:"open",type:"qygl",clas:"qydt",chusheng:vs,edit:edit},function(data) {  
	 alert(data)
	 //$("#ditu_xs").html(data);
	 })
}

function qy_sear(type) {//区域搜索
    var name = $("#sear_qy").val();
	if(type=="edit"){var edit="true";}
    $.post('map-gl.php',{basic:"open",type:"search",clas:"qy",name:name,edit:edit},function(data) {
  if(type=="edit"){
	$("#edit_sear").html(data); 
  }else{
	$("#ajax_te").html(data); 
	$('#ajax_test').modal('show','fit');
    }
  })
}

function qydt_search() {//搜索区域所有地图
    var name = $("#sear_qydt").val(); 
    var val = $("#quyu_xs").val();
    $.post('npc-gl.php',{basic:"open",type:"search",clas:"qydt",name:name,qyval:val},function(data) { 
	$("#ajax_te").html(data); 
	$('#ajax_test').modal('show','fit');
  })
}

$(document).on("click", "#qy_xq", function() {//ajax选中一级区域
	$("#quyu_xs").val($("input[name='qy_rad']:checked").val());
	$("#edit_sear").html(""); 
});

$(document).on("click", "#qydt_xq", function() {//ajax选中区域下属地图
	var id = $("input[name='mid_rad']:checked").val();
	var name = $("input[name='mid_rad']:checked")[0].nextSibling.nodeValue;
		$("#quyu_xs").val($("#"+id).val());
		$('#ajax_test').modal('hide');
});


</script> 
<?php
require_once "html/footer.php";
?>