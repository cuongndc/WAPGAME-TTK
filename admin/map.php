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
$mid_id = $_GET['mid'];
$map_info = $map->get_mid_info($mid_id);

if($map_info->qy == 0 ){
	$area_name = "未分区地图";
}else{
	$area_name = $map->get_qy_name($map_info->qy);
}
?>
<style>
li a{
	background:lightgray;
}
.table {
	margin-bottom: 5px;
}
ul {
	margin-top: 5px;
    margin-bottom: 10px;
}
.edit-mid{
	margin-bottom: 10px;
}
td>h3{
	margin-top: 2px;
	margin-bottom: 5px;
}

</style>
<h2>地图设计界面</h2>

<table  class="table"> 
<input type="hidden" id="area_id"  value="<?php echo $area_id;?>">
<tr><td><b><?php echo $area_name;?></b>-<?php echo "{$map_info->name}({$map_info->id})";?></td><td style="text-align:right">
</td></tr>
</table>
<div id="map_edit">
	<button type="button" id="attr" onClick="edit_type('attr','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">定义属性</button>
	<button type="button" onClick="edit_type('operation','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">定义操作</button>
	<button type="button" onClick="edit_type('event','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">定义事件</button>
	<button type="button" onClick="edit_type('exit','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">定义出口</button>
	<button type="button" onClick="edit_type('task','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">任务设定</button>
	<button type="button" onClick="edit_type('display','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">显示设定</button>
	<button type="button" onClick="edit_type('addnpc','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">放置电脑人物</button>
	<button type="button" onClick="edit_type('addgoods','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">放置物品</button>
	<button type="button" onClick="edit_type('copy','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">复制场景</button>
	<button type="button" onClick="edit_type('import','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">导入定义数据</button>
	<button type="button" onClick="edit_type('export','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">查看定义数据</button>
	<button type="button" onClick="edit_type('update','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">更新场景</button>
	<button type="button" onClick="edit_type('entry','<? echo $map_info->id; ?>')" class="btn btn-primary edit-mid">进入场景</button>
</div>
<hr>
<div id="con"></div>
<div id="edit_body"></div>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>

<script type="text/javascript">
function save_map(){ //新建或修改地图信息信息
	var d = {};
    var t = $('#add').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var idata=JSON.stringify(d);
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"save_map",data:idata},function(data) { 
		$("#con").html(data.title);
		ajax_alert(data);
    }) 	
}

function map_display(mid){//保存地图显示设定
	var d = {};
    var t = $('#display').serializeArray();
    $.each(t, function() {
      d[this.name] = this.value;
    });
    var idata=JSON.stringify(d);
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"display",id:mid,data:idata},function(data) {
		ajax_alert(data);
	})
}

function ajax_load_qy(){//选中区域搜索结果
	var qyid = $("input[name='qyid']:checked").val();
	$("#edit_sear").html("");
	$("#quyu_xs_edit").val(qyid );
	quyugl('edit',qyid)
}

function ajax_load_dt(){//选中区域搜索结果
	var dtid = $("input[name='dtid']:checked").val();
	var qyid = $("input[name='qyid"+dtid+"']").val();
	console.log( dtid ,qyid);
	$("#edit_sear").html("");
	$("#quyu_xs_edit").val(qyid);
	quyugl('edit',qyid,dtid);
}

function edit_type(type,id){//编辑地图数据
	if(type=="attr"){
		$('.edit-mid').removeClass("btn-success").addClass("btn-primary");
		$('#'+type).removeClass("btn-primary").addClass("btn-success");
	};
	var area_id = $('#area_id').val();
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"edit",com:type,id:id},function(data) { 
		$('#edit_body').html(data.body);
		if(!data.area){
			if(data.href){ $(location).prop('href', data.href)};
		}
    }) 
}

function map_exit_edit(exit,id,mid,brea='false'){ //修改地图出口
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"exit",exit:exit,id:id,mid:mid,brea:brea},function(data) { 
			ajax_alert(data); 
        }) 	
}

function exit_edit(type){//编辑或新建出口
		var ntype=type;
		var edit = $("#edit_open").val();
		if(edit=="true"){
		var nqyid = $("#quyu_xs_edit").val();
		var nmapid = $("#ditu_xs_edit").val();
		var nexit = $("#edit_exit").val();
		var yexid = $("#edit_exid").val();
		var mapid = $("#edit_mid").val();
		$.post('map-gl.php',{basic:"open",type:"map",clas:"exit",newqy:nqyid,newmid:nmapid,exitype:ntype,nexit:nexit,yexid:yexid,mapid:mapid},function(data) { 
			$(".con").html(data.body); 
			ajax_alert(data);
			if(data.reload){edit_type('exit',mapid)}
        }) 
		}
}

function cut_off(mid,type,id,exit){//地图出口断开
	$('#ajax_test').modal('hide');
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"cutoff",exit:exit,id:id,mid:mid,brea:type},function(data) { 
		$(".con").html(data.title); 
		$("#ditu_xs").html(data.html);
		mapgl($("#ditu_xs").val());
         }) 	
}

$(document).ready(function(){ 
  $(document).on("click", ".edit-mid", function() {
	$('.edit-mid').removeClass("btn-success").addClass("btn-primary");
	$(this).removeClass("btn-primary").addClass("btn-success");
  });
  
})

function quyugl(edit,qyid,dtid){ //新建或修改地图信息信息
	$.post('map-gl.php',{basic:"open",type:"qygl",clas:"qydt",edit:edit,qyid:qyid},function(data) { 
		$("#ditu_xs_edit").html(data); 
		if(dtid){$("#ditu_xs_edit").val(dtid);}
    }) 	
}

function qy_sear(edit){//搜索区域列表
	var sear_qy =$('#sear_qy').val();
	$.post('map-gl.php',{basic:"open",type:"sear",clas:"qy",edit:edit,val:sear_qy},function(data) { 
		$("#edit_sear").html(data.body); 
    }) 	
}

function dt_sear(edit){//搜索区域列表
	var sear_qy =$('#sear_dt').val();
	$.post('map-gl.php',{basic:"open",type:"sear",clas:"map",edit:edit,val:sear_qy},function(data) { 
		$("#edit_sear").html(data.body); 
    }) 	
}

<?php
 $post_path = "map-gl.php";
 require_once "js/edit-data.php";

 require_once "js/operation-data.php"; 
?>
</script> 

<?php
require_once "html/footer.php";
?>