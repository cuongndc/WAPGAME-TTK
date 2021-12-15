<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

define("Game_path",  __DIR__ ."\\" );//注册全居运行路径


require_once Game_path .'system/global.class.php';

$user_message->liaotian_send_all('系统','定任务测试','系统',0);


?>