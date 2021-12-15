<?php
//error_reporting(0);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// echo "游戏正在重构中，请耐心等待<br/>";

//require_once 'class/player.php';
require_once  '../class/game.php';

$sess = new \sys\sess();
$sess->startSession();

$game = new \main\game();

//加载变量文件
require_once  '../variable/system.php';//系统变量

if ( !isset($_SESSION['sid']) ){
    header("refresh:1;url=index.php");
    exit("长时间未操作，请重新登录");
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
?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<!-- ZUI 标准版压缩后的 CSS 文件 -->
<link rel="stylesheet" href="../zui/1.8.1/css/zui.min.css?_=<?php echo time(); ?>">
    <title><?php echo $变量_系统->游戏名称."-" ?>设计后台</title>
</head>
<body>
<div class="container">
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#"><?php echo $变量_系统->游戏名称."-" ?>游戏设计后台</a>
    </div>
  </div>
</nav>
<h2>表达式定义</h2>
<hr>


<br>
<button class="btn btn-block btn-primary" type="button" data-toggle="modal" data-target="#myModal">增加表达式</button>
<hr>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
</div>


<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
           <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">新建表达式</h4>
      </div>
      <div class="modal-body">
		表达式标识：<input type="text" class="form-control" placeholder="表达式标识">
		表达式类型：
		<select class="form-control">
			<option value="apple">数值型</option>
			<option value="banana">条件型</option>
			<option value="orange">文本型</option>
		</select>
		表达式内容：
		<textarea class="form-control" rows="3" placeholder="表达式内容"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary">保存</button>
      </div>
	 
    </div>
  </div>
</div>

<!-- ZUI Javascript 依赖 jQuery -->
<script src="../zui/1.8.1/lib/jquery/jquery.js"></script>
<!-- ZUI 标准版压缩后的 JavaScript 文件 -->
<script src="../zui/1.8.1/js/zui.min.js"></script>
</body>
</html>