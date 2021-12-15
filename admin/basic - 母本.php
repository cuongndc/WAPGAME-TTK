<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

define("Game_path",  dirname( __DIR__ , 1)."\\" );//注册全居运行路径

require_once Game_path .'/system/global.class.php';

is_login();

if(!is_god($user_info->token)){
	Header("Location:../start_game.php");
	exit;
}

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

<h2>标题需要修改</h2>

<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一个物品</b></td><td style="text-align:right">
<button type="button" onclick="new_goods()" class="btn btn-primary"> 新建物品</button>
</td></tr>
</table>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>

<script type="text/javascript"> 

</script> 

<?php
require_once "html/footer.php";
?>