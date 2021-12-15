<?php
require_once "user_rights.php";

$table = $_GET['clas'];

if(isset($table)){
switch($table){
	case "map":
		$Subheading="修改地图属性";
		$page="mid";
	break;
	case "npc":
		$Subheading="修改电脑人物属性";
		$page="npc";
	break;
	case "user":
		$Subheading="修改玩家人物属性";
		$page="game1";
	break;
	case "goods":
		$Subheading="修改物品属性";
		$page="daoju";
	break;
	case "skill":
		$Subheading="修改技能属性";
		$page="jineng";
	break;
	case "vuser":
		$Subheading="修改查看玩家显示属性";
		$page="vuser";
	break;
}
$_SESSION['table'] = $page;
require_once "attribute-map.php";
exit;
}
require_once "html/header.php";
?>
<div class="container">
<h2>游戏属性定义</h2>
<span class="con"></span> 
<table class="table table-condensed">
  <thead>
    <tr>
      <th>分类</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><p>玩家属性</p></td>
      <td><a href="attribute.php?clas=user" class="btn btn-primary">设 置</a></td>
    </tr>
    <tr>
      <td><p>查看玩家属性</p></td>
      <td><a href="attribute.php?clas=vuser" class="btn btn-primary">设 置</a></td>
    </tr>
	<tr>
      <td><p>电脑人物属性</p></td>
      <td><a href="attribute.php?clas=npc" class="btn btn-primary">设 置</a></td>
    </tr>
    <tr>
      <td><p>物品属性</p></td>
      <td><a href="attribute.php?clas=goods" class="btn btn-primary">设 置</a></td>
    </tr>
    <tr>
      <td><p>地图属性</p></td>
      <td><a href="attribute.php?clas=map" class="btn btn-primary">设 置</a></td>
    </tr>
    <tr>
      <td><p>技能属性</p></td>
      <td><a href="attribute.php?clas=skill" class="btn btn-primary">设 置</a></td>
    </tr>
  </tbody>
</table>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>
<?php
require_once "html/footer.php";
?>
