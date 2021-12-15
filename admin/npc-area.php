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
<h2>设计电脑人物</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>电脑人物区域列表</b></td><td>
</td></tr>
</table>
<table class="table table-bordered table-condensed">
  <thead id="list_thead">
    <tr>
      <th>区域名</th>
      <th>区域ID</th>
	  <th>区域NPC总数</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="list_tbody"></tbody>
</table>
<ul class="pager" id="pager" style="margin-bottom: 0px;margin-top: 0px;"></ul>
<hr>
<a href="admin.php"  class="btn btn-block " >返回上级</a>
<br>
<br>

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
	var idata = {basic:'open',type:'page',page:pager.page,recPerPage:pager.recPerPage};
	$.post('npc-gl.php',idata,function(data){
		$('#list_tbody').html(data.html);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
	})
}


</script> 
<?php
require_once "html/footer.php";
?>