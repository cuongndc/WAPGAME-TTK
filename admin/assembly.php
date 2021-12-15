<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

define("Game_path", dirname(__DIR__ , 1) . "\\"); //注册全居运行路径

require_once Game_path . '/system/global.class.php';

G_is_login();

if (!G_is_god($user_info->token)) {
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

function loa_data() {
    global $sys;
    global $formula;
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $test = $sys->get_assembly_all();
    $all_count = $test[0];
    if ($all_count > 10) {
        $test = $sys->get_assembly_all(0, 10);
    } else {
        $test = $sys->get_assembly_all(0, 10);
    } 
    foreach($test[1] as $odj) {
        $html .= "<tr>
			<td>$odj->nickname</td><td>$odj->value</td><td>";
        if ($odj->style != "") {
            $html .= "<button class='btn btn-info' type='button' onclick='edit(\"$odj->value\",\"style\")'>修改</button>";
        } else {
            $html .= "<button class='btn btn-info' type='button' onclick='edit(\"$odj->value\",\"style\")'>设置</button>";
        } 
        $html .= "</td><td>";
        if ($odj->clas != "") {
            $html .= "<button class='btn btn-success' type='button' onclick='edit(\"$odj->value\",\"clas\")'>修改</button>";
        } else {
            $html .= "<button class='btn btn-success' type='button' onclick='edit(\"$odj->value\",\"clas\")'>设置</button>";
        } 
        $html .= "</td><td><button class='btn btn-primary' type='button' onclick='edit(\"$odj->value\",\"nick\")'>修改</button></td>
			</tr>";
    } 
    return array($html, $page_html);
} 

$basic = $_POST["basic"];
if (isset($basic)) {
    $assembly = $sys->get_assembly($_POST["name"]);
    switch ($_POST["type"]) {
        case "nick": 
            // var_dump($assembly);
            if ($assembly->nickname != "") {
                $title = "修改显示名";
            } else {
                $title = "设置显示名";
            } 
            $html = <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">$title</h4>
      </div>
      <div class="modal-body">
		显示名：<textarea class="form-control" id="save" rows="3">$assembly->nickname</textarea>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		<button type="button" class="btn btn-primary" onclick="save('nick','$assembly->value')">保存修改</button>
      </div>
    </div>
html;
            echo $html;
            break;
        case "style":
            if ($assembly->style != "") {
                $title = "修改行内样式表";
            } else {
                $title = "设置行内样式表";
            } 
            $html = <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">$title</h4>
      </div>
      <div class="modal-body">
		行内元素：<textarea class="form-control" id="save" rows="3">$assembly->style</textarea>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		<button type="button" class="btn btn-primary" onclick="save('style','$assembly->value')">保存修改</button>
      </div>
    </div>
html;
            echo $html;
            break;
        case "clas":
            if ($assembly->clas != "") {
                $title = "修改全局样式引用";
            } else {
                $title = "设置全局样式引用";
            } 
            $html = <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">$title</h4>
      </div>
      <div class="modal-body">
		全局样式：<textarea class="form-control" id="save" rows="3">$assembly->clas</textarea>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		<button type="button" class="btn btn-primary" onclick="save('clas','$assembly->value')">保存修改</button>
      </div>
    </div>
html;
            echo $html;
            break;
        case "save":
            if ($sys->set_assembly($_POST['val'], $_POST['name'], $_POST['clas'])) {
                $data = loa_data();
                $arr = array('title' => "数据修改成功" , 'html' => $data[0], 'page' => $data[1]);
            } else {
                $arr = array('title' => "数据修改失败" , 'error' => 'true');
            } 
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode($arr);
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
<link rel="stylesheet" href="../zui/1.8.1/css/zui.min.css?_=<?php echo time();
?>">
    <title><?php echo $变量_系统->游戏名称 . "-" ?>设计后台</title>
</head>
<body>
<div class="container">
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="admin.php"><?php echo $变量_系统->游戏名称 . "-" ?>游戏设计后台</a>
    </div>
  </div>
</nav>
<h2>修改功能点名字</h2>

<table class="table table-condensed">
  <thead>
    <tr>
      <th>显示名</th>
      <th>功能名</th>
	  <th>样式文本</th>
	  <th>全局样式类</th>
	  <th>修改名称</th>
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
function edit(name,type){
	$.post('assembly.php',{basic:"open",name:name,type:type},function(data) { 
		$("#ajax_te").html(data); 
		$('#ajax_test').modal('show','fit');
    })
}

function save(type,name){
  var val = $("#save").val();
  $('#ajax_test').modal('hide');
  $('#ajax_test').on('hidden.zui.modal', function () {
	$.post('assembly.php',{basic:"open",type:"save",clas:type,name:name,val:val},function(data) { 
	$("#ajax_test").off("hidden.zui.modal");
	if(data.error!="true"){
		$("#math_list").html(data.html);
		$("#page").html(data.page);
		}else{
		(new $.zui.ModalTrigger({title: '提示',custom:data.title})).show();
		}
    }) 
  })
}

</script> 
<!-- ZUI 标准版压缩后的 JavaScript 文件 -->
<script src="../zui/1.8.1/js/zui.min.js"></script>
</body>
</html>