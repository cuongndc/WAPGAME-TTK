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
<div class="container">
<h2>技能管理</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一个技能</b></td><td style="text-align:right">
<a type="button" href="skill-default.php" class="btn btn-info "> 技能默认值设置</a>
<a type="button" href="skill.php" class="btn btn-primary"> 新建技能</a>
</td></tr>
</table>
<input type="hidden"  id="type" value="battle">
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#battle" onclick="reload('battle')">战斗技能</a></li>
  <li><a data-tab href="#auxiliary" onclick="reload('auxiliary')">非战斗技能</a></li>
</ul>
<div class="tab-content table-condensed">
  <div class="tab-pane active" id="battle">
  <table class="table">
  <thead>
    <tr>
      <th>序号</th>
      <th>技能名(id)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="battle-list"></tbody>
</table>
  </div>
  <div class="tab-pane" id="auxiliary">
  <table class="table">
  <thead>
    <tr>
      <th>序号</th>
      <th>技能名(id)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="auxiliary-list"></tbody>
</table>
  </div>
</div>

<ul class="pager" id="pager" style="margin-bottom: 0px;margin-top: 0px;"></ul>
<hr>
<a href="admin.php"  class="btn btn-block">返回上级</a>
</div>
<br><br>

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

function reload(type){
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	if(!type){type = $('#type').val(); };
	if(type !=  $('#type').val()){myPager.set({page: 1});}

	$('#type').val(type);
	var idata = {basic:'open',type:'page',clas:type,page:pager.page,recPerPage:pager.recPerPage};
	$.post('skill.php',idata,function(data){
		$("#"+type+"-list").html(data.html);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
	})
}

function del_skill(type,key,allow){//删除技能信息
	$.post('skill.php',{basic:"open",type:"del",key:key,"allow":allow,"clas":type},function(data) {
		ajax_alert(data);if(data.reload){reload(type);};
	});
}

</script>

<?php
require_once "html/footer.php";
?>