<?php
require_once "user_rights.php";


$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$dis_name = $_SESSION['dis_name'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
$_SESSION['dis_name'] = $dis_name;


$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);

require_once "html/header.php";

?>

<div class="container">
<h2>定义页面模板</h2>

<div class="list-group"><b>
  <a href="page.php?dis=mid" class="list-group-item">修改查看场景页面</a>
  <a href="page.php?dis=npc" class="list-group-item">修改电脑人物页面</a>
  <a href="page.php?dis=pets" class="list-group-item">修改宠物页面</a>
  <a href="page.php?dis=good" class="list-group-item">修改物品页面</a>
  <a href="page.php?dis=others" class="list-group-item">修改查看玩家页面</a>
  <a href="page.php?dis=player" class="list-group-item">修改查看自己页面</a>
  <a href="page.php?dis=skill" class="list-group-item">修改技能页面</a>
  <a href="page.php?dis=assembly" class="list-group-item">修改功能页面</a>
  <a href="page.php?dis=battle" class="list-group-item">修改战斗页面</a>
  <a href="page.php?dis=index" class="list-group-item">修改首页页面</a>
  <a href="#" class="list-group-item"></a>
  <a href="assembly.php" class="list-group-item">修改功能点名称</a>
  <a href="page-list.php" class="list-group-item">自定义页面</a></b>
</div>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>

<?php
require_once "html/footer.php";
?>