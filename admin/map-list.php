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
$qy_obj =$map->get_qy_all();
	foreach($qy_obj ->data as $obj){
		$option .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
	}
require_once "html/header.php";

$area_id = $_GET['area'];
if($area_id=="0"){
	$area_name = "未分区地图";
}else{
	$area_name = $map->get_qy_name($area_id);
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
<tr><td><b><?php echo $area_name;?></b>-地图列表</td><td style="text-align:right">
<button type="button" onclick="map_new()" <?php echo alert_open;?> class="btn btn-primary"> 创建新地图</button>
</td></tr>
</table>

<span class="con"></span> 
<div class="container">
<div class="row">
  <div class="col-md-5 col-sm-8 col-xs-12">
  <div class="input-group">
	<span class="input-group-addon">搜索地图</span>
	<input type="search" id="sear_dt" class="form-control">
	<span class="input-group-btn">
	<button class="btn btn-default" onclick="reload_area()" type="button">搜索</button>
	</span>
</div>
  </div>
</div>
</div>
<br>
<table class="table table-bordered table-condensed">
  <thead id="list_thead">
    <tr>
      <th>地图名</th>
	  <th>地图描述</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="map_list"></tbody>
</table>
<ul class="pager" id="pager"></ul>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>

<br>
<br>

<script type="text/javascript">

//显示列表翻页控制-开始

$('#pager').pager({// 手动进行初始化
    page: 1,
	recPerPage:20,
	elements:['first', 'prev', 'pages', 'next', 'last', 'page_of_total_text', 'items_range_text', 'total_text','goto','size_menu'],
});


$(document).ready(function () { 
$('#pager').on('onPageChange', function(e, state, oldState) {
	if (state.page !== oldState.page || state.recPerPage !== oldState.recPerPage) {reload_area();}
});
	reload_area();
}); 

function reload_area(area_id){//重新加载子类型数据
	var name = $('#sear_dt').val();
	if(!area_id){area_id = $('#area_id').val();}
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	$.post('map-gl.php',{basic:"open",type:"reload",clas:"map",name:name,area:area_id,page:pager.page,recPerPage:pager.recPerPage},function(data) { 
		$("#map_list").html(data.list);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
    }) 
 }

//显示列表翻页控制-结束

function map_edit(mid){ //新建或修改地图信息信息
	$.post('map-gl.php',{basic:"open",type:"dtgl",clas:"newm",get_mid:mid},function(data) { 
		$("#map_edit").html(data.body); 
    }) 	
}

function map_new(confirm){//新建一个地图
	if(!confirm){
		var area_id = $('#area_id').val();
		var idata = {basic:"open",type:"map",clas:"new",area:area_id};
	}else{
		var area_id = $('#area_id').val();
		var params = $("#add").serializeArray();
		var values = {};
		for( x in params ){
			values[params[x].name] = params[x].value;
		}
		var idata = {basic:"open",type:"map",clas:"new",area:area_id,data:JSON.stringify(values)};
	}
	$.post('map-gl.php',idata,function(data) { 
		ajax_alert(data);
		
	})
}

function map_del(mid,confirm){
	if(!confirm){ confirm = false;};
	var idata = {basic:"open",type:"map",clas:"del",mid:mid,confirm:confirm};
	$.post('map-gl.php',idata,function(data) { 
		ajax_alert(data);
	})
};
</script> 

<?php
require_once "html/footer.php";
?>