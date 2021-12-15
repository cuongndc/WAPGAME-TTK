<?php
require_once "user_rights.php";
require_once "html/header.php";
?>

<div class="container">
  <div class="row">
	<a href="basic.php" >
      <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-info-sign icon-4x"></i><p>基本信息</p>
	  </div>
	</a>
    <a href="attribute.php"  >
    <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-info icon-4x"></i><p>定义属性</p>
	</div> 
	</a>
    <a href="formula.php"  >
    <div class="col col-xs-4 col-sm-2 btn ">
		<i class="icon icon-exchange icon-4x"></i><p>定义表达式</p>
	</div>  
	</a>
    <a href="equipment.php"  >
    <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-th-large icon-4x"></i><p>装备类别</p>
	</div>  	  	
	</a>
    <a href="skill-list.php">
	  <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-sitemap icon-4x"></i><p>定义技能</p>
	  </div>
	</a>
    <a href="global_event.php"  >
	  <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-list-ul icon-4x"></i><p>定义公共事件</p>
	  </div>
	</a>
  </div>
  <div class="row">
	<a href="map_area.php" >
      <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-table icon-4x"></i><p>设计地图</p>
	  </div>
	</a>
    <a href="goods-list.php">
    <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-cubes icon-4x"></i><p>设计物品</p>
	</div> 
	</a>
	<a href="equip-list.php">
    <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-shield icon-4x"></i><p>设计装备</p>
	</div> 
	</a>
    <a href="npc-area.php"  >
    <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-android icon-4x"></i><p>设计电脑人物</p>
	</div>  
	</a>
    <a href="task-list.php"  >
    <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-tags icon-4x"></i><p>设计任务</p>
	</div>  	  	
	</a>
    <a href="page-list.php">
	  <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-file-powerpoint icon-4x"></i><p>定义页面模板</p>
	  </div
	</a>
  </div>
  <div class="row">
    <a href=""  >
	  <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-bolt icon-4x"></i><p>PK设置</p>
	  </div>
	</a>
	<a href="" >
      <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-trophy icon-4x"></i><p>排行榜设置</p>
	  </div>
	</a>
    <a href=""  >
    <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-bug icon-4x"></i><p>内测人员设置</p>
	</div> 
	</a>
    <a href=""  >
    <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-group icon-4x"></i><p>查看在线玩家</p>
	</div>  
	</a>
    <a href="designer.php"  >
    <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-user icon-4x"></i><p>设计者管理</p>
	</div>  	  	
	</a>
    <a href=""  >
	  <div class="col col-xs-4 col-sm-2 btn">
		<i class="icon icon-wrench icon-4x"></i><p>其他设置</p>
	  </div>
	</a>
    <a href=""  >
	  <div class="col col-xs-4 col-sm-2 btn">
	  	<i class="icon icon-cog icon-4x"></i><p>游戏管理</p>
	  </div>
	</a>
  </div>
</div>
  <br>
<?php
 echo  "<a href='../home_page.php'  class='btn btn-block' >返回游戏</a>"
?>  
  <br>
  <br>
</div>
<?php
require_once "html/footer.php";
?>