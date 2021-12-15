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
function loa_data(){
	
}


if($_POST["basic"]=="open"){
	switch ($_POST["type"]){
	case "new" :
	echo <<<html
	    <div class="modal-content">
           <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">添加游戏设计者</h4>
      </div>
      <div class="modal-body">
	  	<input id="old_name" type="hidden" value="">
		表达式标识：<input type="text" id="name" class="form-control" placeholder="表达式标识">
		表达式备注：<input type="text" id="notes" class="form-control" placeholder="表达式备注">
		表达式类型：
		<select class="form-control" id="type">
			<option value="number">数值型</option>
			<option value="condition">条件型</option>
			<option value="text">文本型</option>
		</select>
		表达式内容：
		<textarea class="form-control" rows="3" id="string" placeholder="表达式内容"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" onclick="new_add()">保存</button>
      </div>
    </div>
html;
	
	break;
	case "edit" :
	echo "编辑测试";
	break;
	case "del" :
	echo "删除测试";
	break;
	}
exit;
}
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
      <a class="navbar-brand" href="admin.php"><?php echo $变量_系统->游戏名称."-" ?>游戏设计后台</a>
    </div>
  </div>
</nav>
<h2>设计者管理</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新增一个设计者</b></td><td style="text-align:right">
<button class="btn btn-primary" type="button" onclick="new_god()">增加设计者</button>
</td></tr>
</table>

<table class="table table-condensed">
  <thead>
    <tr>
      <th>帐号ID</th>
      <th>添加时间</th>
	  <th>权限等级</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="math_list">
<?php
$data = loa_data();
echo $data[0];
?>
  </tbody>
</table>
<div align="center" id="page">
<?php
echo $data[1];
?>
</div>



<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
</div>

<div class="modal fade" id="ajax_test">
  <div class="modal-dialog" id="ajax_te">
  </div>
</div>

<!-- ZUI Javascript 依赖 jQuery -->
<script src="../zui/1.8.1/lib/jquery/jquery.js"></script>
<script type="text/javascript"> 

function new_god(){//添加设计者
	$.post('designer.php',{basic:"open",type:"new"},function(data) { 
	$("#ajax_te").html(data); 
	$('#ajax_test').modal('show','fit'); 
    }) 
}

</script> 
<!-- ZUI 标准版压缩后的 JavaScript 文件 -->
<script src="../zui/1.8.1/js/zui.min.js"></script>
</body>
</html>