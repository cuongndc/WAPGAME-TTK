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
require_once "html/header.php";

$nid = $_GET['nid'];
$area_id = $_GET['area'];

if(isset($nid)){
	$npc_info = $npc->get_npc_info($nid);
}
?>

<h2>设计电脑人物</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>编辑一个电脑人物</b></td></tr>
</table>
<div id="map_edit">
	<button type="button" onClick="edit_npc('attr','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">定义属性</button>
	<button type="button" onClick="edit_npc('operation','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">定义操作</button>
	<button type="button" onClick="edit_npc('event','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">定义事件</button>
	<button type="button" onClick="edit_npc('task','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">任务设定</button>
	<button type="button" onClick="edit_npc('skill','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">技能设定</button>
	<button type="button" onClick="edit_npc('equip','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">放置装备</button>
	<button type="button" onClick="edit_npc('drop','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">掉落定义</button>
	<button type="button" onClick="edit_npc('update','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">保存修改</button>
	<button type="button" onClick="edit_npc('copy','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">复制该人物</button>
	<button type="button" onClick="edit_npc('import','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">导入定义数据</button>
	<button type="button" onClick="edit_npc('export','<? echo $npc_info->id; ?>')" class="btn btn-primary edit-mid">查看定义数据</button>
</div>
<span class="con"></span> 
<div id="edit_body"></div>

<hr>
<a href="npc-list.php?qyid=<?php echo $area_id;?>" class="btn btn-block ">返回NPC列表</a>
<br>
<br>
<script type="text/javascript"> 
$(document).ready(function(){ 
  $(document).on("click", ".edit-mid", function() {
	$('.edit-mid').removeClass("btn-success").addClass("btn-primary");
	$(this).removeClass("btn-primary").addClass("btn-success");
  });
  
})

function save_npc(){ //新建或修改地图信息信息
	var d = {};
    var t = $('#add').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var idata=JSON.stringify(d);
	$.post('npc-gl.php',{basic:"open",type:"save_npc",data:idata},function(data) { 
		$("#con").html(data.title);
		ajax_alert(data);
    }) 	
}

function save_drop(){
	var d = {};
    var t = $('#drop').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var idata=JSON.stringify(d);
	$.post('npc-gl.php',{basic:"open",type:"save_drop",data:idata},function(data) { 
		$("#con").html(data.title);
		ajax_alert(data);
    }) 	
}

function edit_npc(com,npcid){//编辑npc基本资料
	$.post('npc-gl.php',{basic:"open",type:"edit",npcid:npcid,com:com},function(data) { 
		$('#edit_body').html(data.body);
	});
}

<?php
 $post_path = "npc-gl.php";
 require_once "js/edit-data.php";

 require_once "js/operation-data.php"; 
 require_once "js/equip-data.php"; 
 
 
?>
</script> 
<?php
require_once "html/footer.php";
?>