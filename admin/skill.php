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
	case "page":
		$arry = $skill->skill_type_load($value["clas"],$value["page"],$value["recPerPage"]);
	break;
	case "edit":
		$arry = $skill->edit_skill($value['key'],$value['allow'],$value[data]);
	break;
	case "new":
		$arry = $skill->new_skill($value);
	break;
	case "del":
		$arry = $skill->del_skill($value['key'],$value['allow'],$value['clas']);
	break;
	}
	ajax_alert($arry);
exit;
}else{
$skill_id = $_GET['id'];
if(isset($skill_id)){
	$obj = $skill->edit_skill($skill_id);
}else{
	$obj = $skill->new_skill();
}
}


require_once "html/header.php";
?>
<div class="container">
<h2>技能管理</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b><?echo $obj["title"];?></b></td><td style="text-align:right"></td></tr>
</table>
<?echo $obj["body"];?>
<?echo $obj["btn"];?>
<hr>
<a href="skill-list.php"  class="btn btn-block " >返回上级</a>
</div>
<br><br>

<script type="text/javascript"> 

function edit_skill(key,allow){//编辑技能信息
	if(allow){
	  var params = $("#add").serializeArray();
	  var values = {};
	  for( x in params ){
		values[params[x].name] = params[x].value;
		}
	  var idata = JSON.stringify(values)
	  var data = {basic:"open",type:"edit",key:key,"allow":allow,"data":idata }
	  $.post('skill.php',data,function(data) {ajax_alert(data);});
	}
}

function new_skill(add){//加载技能新建菜单
if(add){
	var params = $("#add").serializeArray();
	var values = {};
	for( x in params ){
		values[params[x].name] = params[x].value;
		}
	var idata = JSON.stringify(values)
	$.post('skill.php',{basic:"open",type:"new",data:idata},function(data) {
		ajax_alert(data);
	});
  }else{
	$.post('skill.php',{basic:"open",type:"new"},function(data) {
		ajax_alert(data);
	});
  }
}

<?php
 require_once "js/equip-data.php"; 
  $post_path = "skill.php";
 require_once "js/edit-data.php";
?>

</script>

<?php
require_once "html/footer.php";
?>