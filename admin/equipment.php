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


$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);
if(isset($value['basic'])){
  switch ($value['type']){
	case "new":
	echo <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">新建子类别</h4>
      </div>
      <div class="modal-body">
	  选择上级类目：
<select class="form-control" id="clas">
  <option value="weapon">兵器</option>
  <option value="equip">防具</option>
</select>
	  请输入新建子类别名：
		<input type="text" class="form-control" id="name" placeholder="新子类名">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" onclick="save_equip()">保存</button>
      </div>
    </div>
html;
	break;
	case "del":
		if(isset($value['del']) && $value['del']=="true"){
			$val=del_class($value['clas'],$value['id']);
	  switch($value['clas']){
		case "weapon":
			$clas = "兵器";
		break;
		case "equip":
			$clas = "防具";
		break;
	  }
		if($val){
			$list=equip_class_load($value['clas']);
			$html=<<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">删除子类别成功！</h4>
      </div>
      <div class="modal-body">
		<h3><small>删除</small>{$clas}<small>子类别名：</small>{$value['name']}<small>成功！</small></h3>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
html;
		}else{
			$html="";
			$error=true;			
		}
		$arr=array("list"=>$list ,"html"=> $html ,"error"=> $error ,"clas"=>$value['clas']);
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($arr);
		}else{
		$obj=read_class($value['clas'],$value['id']);
	  switch($value['clas']){
		case "weapon":
			$clas = "兵器";
		break;
		case "equip":
			$clas = "防具";
		break;
	  }		
		echo <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">删除子类别</h4>
      </div>
      <div class="modal-body">
		<h3><small>确认删除</small>{$clas}<small>子类别名：</small>$obj->name<small>的子类别？</small></h3>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-danger" onclick="del_equip('{$value['clas']}','$obj->id','true')">确认删除</button>
      </div>
    </div>
html;
		}
	break;
	case "edit":
		$obj=read_class($value['clas'],$value['id']);
	  switch($value['clas']){
		case "weapon":
			$clas = "兵器";
		break;
		case "equip":
			$clas = "防具";
		break;
	  }		
		echo <<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">编辑子类别</h4>
      </div>
      <div class="modal-body">
	  <input type="hidden" class="form-control" id='clas' value="{$value['clas']}" >
	  上级类目：<input type="text" class="form-control" value="$clas" readonly>
	  请输入新子类别名：
		<input type="text" class="form-control" id="name" value="$obj->name" placeholder="$obj->name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" onclick="save_equip('$obj->id')">保存</button>
      </div>
    </div>
html;
	break;
	case "save":
	  switch($value['clas']){
		case "weapon":
			$clas = "兵器";
		break;
		case "equip":
			$clas = "防具";
		break;
	  }
		$val = edit_class($value['clas'],$value['name'],$value['id']);
		if(isset($value['id'])){$tit="编辑";}else{$tit="修改";}
		if($val){
			$list=equip_class_load($value['clas']);
			$html=<<<html
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">{$tit}子类别成功！</h4>
      </div>
      <div class="modal-body">
		<h3><small>{$tit}</small>{$clas}<small>子类别名：</small>{$value['name']}<small>成功！</small></h3>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
html;
		}else{
			$html="";
			$error=true;			
		}
		$arr=array("list"=>$list ,"html"=> $html ,"error"=> $error );
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($arr);
	break;
	case "reload":
		echo equip_class_load($value["clas"]);
	break;
}
exit;
}
function equip_class_load($type){//加载装备类型
	global $sys;
	switch($type){
		case "weapon":
		  $equip=$sys->get_system_config("system","weapon_class");
		break;
		case "equip":
		  $equip=$sys->get_system_config("system","equip_class");
		break;
	}

	  $equip=json_decode($equip);
	  if(is_object($equip)){
		  foreach($equip as $key => $value){
			  if(is_object($value)){
				  ++$i;
$html .=<<<html
	<tr>
      <td>$i</td>
      <td>$value->name</td>
      <td>
	  <button class="btn btn-primary " type="button" onclick="edit_equip('$type','$key')">修改</button>
	  <button class="btn btn-danger " type="button" onclick="del_equip('$type','$key')">删除</button>
	  </td>
    </tr>
html;
			  }

		  }
	  }
	  return $html;
}

function edit_class($type,$name,$id=null){//编辑和新建装备类型
	global $sys;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$sys->get_system_config("system",$clas);
	$equip=json_decode($equip);
	if(isset($id)){
		if(is_object($equip)){
			if(is_object($equip->$id)){
				$equip->$id->name = $name;
			}
		}
	}else{
		if(is_object($equip)){
			$i=$equip->count;
		}else{
			$i=0;
			$equip = new stdclass();
		}
		++$i;
		if(!is_object($equip->$i)){
			$equip->$i = new stdclass();
		}
		$equip->count = $i;
		$equip->$i->id = $i;
		$equip->$i->name = $name;
	}
	$m_value=json_encode($equip);
	$val=$sys->set_system_config("system",$clas,$m_value);
	return $val;
}

function read_class($type,$id){//读取装备类型类型
	global $sys;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$sys->get_system_config("system",$clas);
	$equip=json_decode($equip);	
	return $equip->$id;
}

function del_class($type,$id){//删除装备子类型
	global $sys;
		switch($type){
		case "weapon":
			$clas = "weapon_class";
		break;
		case "equip":
			$clas = "equip_class";
		break;
	}
	$equip=$sys->get_system_config("system",$clas);
	$equip=json_decode($equip);
	if(isset($id)){
		if(is_object($equip)){
			if(is_object($equip->$id)){
				unset ($equip->$id);
			}
		}
	$m_value=json_encode($equip);
	$val=$sys->set_system_config("system",$clas,$m_value);	
	}
	return $val;
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
<style>
li a{
	background:lightgray;
}
</style>
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
<h2>定义装备类别</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一个类别</b></td><td style="text-align:right">
<button type="button" onclick="new_equip()" class="btn btn-primary"> 新建类别</button>
</td></tr>
</table>
<ul class="nav nav-tabs">
  <li class="active"><a data-tab href="#weapon" onclick="reload_equip('weapon')">兵器类别</a></li>
  <li><a data-tab href="#equip" onclick="reload_equip('equip')">防具类别</a></li>
</ul>
<div class="tab-content table-condensed">
  <div class="tab-pane active" id="weapon">
  <table class="table">
  <thead>
    <tr>
      <th>兵器子类ID</th>
      <th>兵器子类名</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="weapon-list">
<?php echo equip_class_load("weapon");?>
  </tbody>
</table>
  </div>
  <div class="tab-pane" id="equip">
  <table class="table">
  <thead>
    <tr>
      <th>防具子类ID</th>
      <th>防具子类名</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="equip-list">
  </tbody>
</table>
  </div>
</div>


<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
</div>
<br><br>
<div class="modal fade" id="ajax_test">
  <div class="modal-dialog" id="ajax_te">
  </div>
</div>

<!-- ZUI Javascript 依赖 jQuery -->
<script src="../zui/1.8.1/lib/jquery/jquery.js"></script>
<script type="text/javascript"> 
function reload_equip(type){//重新加载子类型数据
	$.post('equipment.php',{basic:"open",type:"reload",clas:type},function(data) { 
		$("#"+type+"-list").html(data);

    }) 
 }

function new_equip(){ //加载类型新建菜单
	$.post('equipment.php',{basic:"open",type:"new"},function(data) { 
		$("#ajax_te").html(data);
		$("#ajax_test").modal("show")
    }) 
}
 
function save_equip(id){//保存类型新建和修改
	var name=$("#name").val();
	var clas=$("#clas").val();
	$.post('equipment.php',{basic:"open",type:"save","name":name,"clas":clas,"id":id},function(data) { 
	if(!data.error){
		$("#ajax_te").html(data.html);
		$("#"+clas+"-list").html(data.list);
		$("#ajax_test").modal("show")
	}else{
		(new $.zui.ModalTrigger({title: '提示',custom:data.html})).show();
	}
	
    }) 
}

function edit_equip(type,id){//加载子类型编辑菜单
	$.post('equipment.php',{basic:"open",type:"edit",clas:type,id:id},function(data) { 
		$("#ajax_te").html(data);
		$("#ajax_test").modal("show")
    }) 
}

function del_equip(type,id,del=false){//删除一个子分类
	$.post('equipment.php',{basic:"open",type:"del",clas:type,id:id,del:del},function(data) {
		if(del){
	if(!data.error){
		$("#ajax_te").html(data.html);
		$("#"+data.clas+"-list").html(data.list);
		$("#ajax_test").modal("show")
	}else{
		(new $.zui.ModalTrigger({title: '提示',custom:data.html})).show();
		}
		}else{
			$("#ajax_te").html(data);
			$("#ajax_test").modal("show")
		}
	
    }) 
	
}
</script>
 
<!-- ZUI 标准版压缩后的 JavaScript 文件 -->
<script src="../zui/1.8.1/js/zui.min.js"></script>
</body>
</html>