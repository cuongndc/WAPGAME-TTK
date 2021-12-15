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

$npc_id = $_GET['id'];
$npc_type = $_GET['type'];

require_once "html/header.php";
$npc_info = $npc->get_npc_info($npc_id);
switch($npc_type){
	case 'goods':
		$list = $searchBox->reloading('npc',"drop_items",$npc_id);
		$title = "修改NPC掉落物品";
		$Tips = "添加一个物品";
		$btn = "选择物品";
		$clas = "goods";
	break;
	case 'equip':
		$list = $searchBox->reloading('npc',"drop_equip",$npc_id);
		$title = "修改NPC掉落装备";
		$Tips = "添加一个装备";
		$btn = "选择装备";
		$clas = "drop_equip";
	break;
}
?>

<h2><?php echo $title ;?></h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b><?php echo $Tips ;?></b></td><td style="text-align:right">
<button type="button" onclick="Choicenpc('<?php echo $npc_id ;?>','<?php echo $clas ;?>')" <?php echo alert_open ;?> class="btn btn-primary"><?php echo $btn ;?></button>
</td></tr>
</table>

<div id="window"><?php echo $list;?></div>

<hr>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
<script type="text/javascript"> 
function Choicenpc(id,clas){//加载NPC选取窗口
	$.post('npc-gl.php',{basic:"open",type:"Selection",clas:clas,key:id},function(data) {
		ajax_alert(data);
	});
}
<?php
 $post_path = "npc-gl.php";
 require_once "js/edit-data.php";
?>
</script> 

<?php
require_once "html/footer.php";
?>