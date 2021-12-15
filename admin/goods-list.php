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

<h2>设计游戏物品</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一个物品</b></td><td style="text-align:right">
<button type="button" onclick="new_googs()" class="btn btn-primary">新建物品</button>
</td></tr>
</table>
<input type="hidden"  id="type" value="consume">
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#consume" onclick="reload('consume')">消耗品</a></li>
  <li><a data-tab href="#book" onclick="reload('book')">书籍</a></li>
  <li><a data-tab href="#taskitems" onclick="reload('taskitems')">任务物品</a></li>
  <li><a data-tab href="#other" onclick="reload('other')">其他</a></li>
</ul>
<div class="tab-content table-condensed">
  <div class="tab-pane active" id="consume">
  <table class="table">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>区域</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="consume-list"></tbody>
</table>

  </div>
  <div class="tab-pane" id="book">
  <table class="table">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>区域</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="book-list">
  </tbody>
</table>
  </div>
  <div class="tab-pane" id="taskitems">
  <table class="table">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>区域</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="taskitems-list">
  </tbody>
</table>
  </div>
  <div class="tab-pane" id="other">
  <table class="table">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>区域</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="other-list">
  </tbody>
</table>
  </div>
  <ul class="pager" id="pager"></ul>
</div>

<a href="admin.php" class="btn btn-block">返回上级</a>
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
	if (state.page !== oldState.page || state.recPerPage !== oldState.recPerPage) {reload();}
});
	reload();
}); 

function reload(type){//重新加载子类型数据
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	if(!type){type = $('#type').val(); };
	if(type !=  $('#type').val()){myPager.set({page: 1});}
	$('#type').val(type);
	$.post('goods-gl.php',{basic:"open",type:"reload",clas:type,name:name,page:pager.page,recPerPage:pager.recPerPage},function(data) { 
		$("#"+type+"-list").html(data.list);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
    }) 
 }
//显示列表翻页控制-结束

function new_googs(){
	var type = $('#type').val(); 
	$(window).attr('location','goods.php?com=add&type=' +type );
}

function del_goods(key,allow){//删除一个物品
	$.post('goods-gl.php',{basic:"open",type:"del",key:key,"allow":allow},function(data) {
	  	ajax_alert(data);
		if(data.reload){reload(data.type);}
	});
}
</script>
<?php
require_once "html/footer.php";
?>