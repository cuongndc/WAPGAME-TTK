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
</style>
<div id="event-page">
<h2>定义公共事件</h2>
<span class="con"></span> 
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#event-user" onclick="reload_equip('user')">玩家事件</a></li>
  <li><a data-tab href="#event-npc" onclick="reload_equip('npc')">电脑人物事件</a></li>
  <li><a data-tab href="#event-goods" onclick="reload_equip('goods')">物品事件</a></li>
  <li><a data-tab href="#event-map" onclick="reload_equip('map')">场景事件</a></li>
  <li><a data-tab href="#event-sys" onclick="reload_equip('sys')">系统事件</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane  active"  id="event-user">
  <table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-user-list">
  <?php  echo $event->event_type_load("user");?>
  </tbody>
</table>
  </div> 
  <div class="tab-pane" id="event-npc">
  <table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-npc-list">
  </tbody>
</table>
  </div>
    <div class="tab-pane" id="event-goods">
  <table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-goods-list">
  </tbody>
</table>
  </div> 
  <div class="tab-pane" id="event-map">
  <table class="table table-condensed" >
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-map-list">
  </tbody>
</table>
  </div>
    <div class="tab-pane" id="event-sys">
  <table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-sys-list">
  </tbody>
</table>
  </div> 
</div>


<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>



<script type="text/javascript"> 

function reload_equip(type){//重新加载子类型数据
	$.post('event-gl.php',{basic:"open",type:"reload",clas:type},function(data) { 
		$("#event-"+type+"-list").html(data);
    }) 
 }

function del_event(type,key,confirm){//准备删除事件
	if(!confirm){confirm = false;};
	$.post('event-gl.php',{basic:"open",type:"del_event",key:key,confirm:confirm},function(data) { 
		ajax_alert(data);
		if(data.reload){reload_equip(type);}
    }) 
}
</script> 
<?php
require_once "html/footer.php";
?>