<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
define("Game_path",  dirname( __DIR__ , 1)."\\" );//注册全居运行路径
require_once Game_path .'/system/global.class.php';

G_is_login();

if(!G_is_god($user_info->token)){
	Header("Location:../start_game.php");
	exit;
}

?>