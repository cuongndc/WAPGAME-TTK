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

<h2>地图设计界面</h2>


<table  class="table"> 
<tr><td><b>区域管理</b></td><td style="text-align:right">
<button type="button" onclick="qy_new()" <?php echo alert_open;?> class="btn btn-primary"> 创建新区域</button>
</td></tr>
</table>

<div class="container">
<div class="row">
  <div class="col-md-5 col-sm-8 col-xs-12">
  <div class="input-group">
	<span class="input-group-addon">搜索区域</span>
	<input type="search" id="sear_qy" class="form-control">
	<span class="input-group-btn">
	<button class="btn btn-default" onclick="reload_area()" type="button">搜索</button>
	</span>
</div>
  </div>
</div>
</div>

<span class="con"></span> 

<br>

<table class="table table-bordered table-condensed">
  <thead id="list_thead">
    <tr>
      <th>区域名</th>
	  <th>区域描述</th>
	  <th>区域地图数</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="area_list"></tbody>
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

function reload_area(){//重新加载子类型数据
	var	type = "area";
	var name = $('#sear_qy').val();
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	$.post('map-gl.php',{basic:"open",type:"reload",clas:type,name:name,page:pager.page,recPerPage:pager.recPerPage},function(data) { 
		$("#area_list").html(data.list);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		}
    }) 
 }

//显示列表翻页控制-结束

function qy_new(vs){//加载区域新建信息
    if(!vs){vs = $("#quyu_xs").val();}
	var name = $("#quyu_xs").text();
	$.post('map-gl.php',{basic:"open",type:"qygl",clas:"qyxg",get_qy:vs,get_qyname:name},function(data) { 
		ajax_alert(data);
    }) 
}

function qy_del(areaid,confirm){//加载区域新建信息
    if(!confirm){confirm = false;}
	var name = $("#quyu_xs").text();
	$.post('map-gl.php',{basic:"open",type:"qygl",clas:"del",confirm:confirm,area:areaid},function(data) { 
		ajax_alert(data);
    }) 
}

$(document).on("click", "#newqy", function() {//新建区域
        var name = $("#qy_name").val(); 
        var pass = $("#qy_desc").val(); 
		var qyid = $("#qy_id").val(); 
      $.post('map-gl.php',{basic:"open",type:"qygl",clas:"newqy",qy_id:qyid,qy_name:name,qy_desc:pass},function(data) { 
			$(".con").html(data); 
			reload_area();
	 }) 
});

</script> 
<?php
require_once "html/footer.php";
?>