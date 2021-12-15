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

$event_type = $_GET['type'];

$event_path = $_GET['path'];
$event_clas = $_GET['clas'];
$event_paid = $_GET['key'];

$event_enid = $_GET['enid'];

$title = "";
if (isset($event_path)) {
    switch ($event_path) {
        case 'operation':
            $title = "定义操作元素触发事件：";
            $operation_info = $operation->get_operation_info($event_paid);
            $event_info = $event->get_event_info($operation_info->event);
            break;
        case 'map':
            load_map_edit($event_clas, $event_paid);
            break;
        case 'npc':
            load_npc_edit($event_clas, $event_paid);
            break;
        case "task":
            load_task_edit($event_clas, $event_paid);
            break;
        case "skill":
            load_skill_edit($event_clas, $event_paid);
            break;
        case "goods":
            load_daoju_edit($event_clas, $event_paid);
            break;
        case "equip":
            load_equip_edit($event_clas, $event_paid);
            break;
    } 
} 

function load_npc_edit($event_clas, $event_paid) { // 加载NPC事件信息
    global $event;
    global $npc;
    global $title;
    global $event_info;
    global $event_id;
    $npc_info = $npc->get_npc_info($event_paid);
    switch ($event_clas) {
        case 'create':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的创建事件：";
            $event_info = $event->get_event_info($npc_info->event_create);
            break;
        case 'watch':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的查看事件：";
            $event_info = $event->get_event_info($npc_info->event_watch);
            break;
        case 'attack':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的出招事件：";
            $event_info = $event->get_event_info($npc_info->event_attack);
            break;
        case 'defense':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的被攻击事件：";
            $event_info = $event->get_event_info($npc_info->event_defense);
            break;
        case 'win':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的战胜事件：";
            $event_info = $event->get_event_info($npc_info->event_win);
            break;
        case 'fail':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的战败事件：";
            $event_info = $event->get_event_info($npc_info->event_fail);
            break;
        case 'adopted':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的被收养事件：";
            $event_info = $event->get_event_info($npc_info->event_adopted);
            break;
        case 'trade':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的交易事件：";
            $event_info = $event->get_event_info($npc_info->event_trade);
            break;
        case 'upgrade':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的升级事件：";
            $event_info = $event->get_event_info($npc_info->event_upgrade);
            break;
        case 'heartbeat':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的心跳事件：";
            $event_info = $event->get_event_info($npc_info->event_heartbeat);
            break;
        case 'timing':
            $title = "定义NPC\"{$npc_info->name}({$npc_info->id})\"的分钟定时事件：";
            $event_info = $event->get_event_info($npc_info->event_timing);
            break;
    } 
} 

function load_daoju_edit($event_clas, $event_paid) { // 加载道具事件信息
    global $event;
    global $goods;
    global $title;
    global $event_info;
    global $event_id;
    $goods_info = $goods->get_goods_info($event_paid);
    switch ($event_clas) {
        case 'create':
            $title = "定义物品\"{$goods_info->name}({$goods_info->id})\"的创建事件：";
            $event_info = $event->get_event_info($goods_info->event_create);
            break;
        case 'watch':
            $title = "定义物品\"{$goods_info->name}({$goods_info->id})\"的查看事件：";
            $event_info = $event->get_event_info($goods_info->event_watch);
            break;
        case 'use':
            $title = "定义物品\"{$goods_info->name}({$goods_info->id})\"的使用事件：";
            $event_info = $event->get_event_info($goods_info->event_use);
            break;
        case 'save':
            $title = "定义物品\"{$goods_info->name}({$goods_info->id})\"的存储数据事件";
            $event_info = $event->get_event_info($goods_info->event_save);
            break;
        case 'backups':
            $title = "定义物品\"{$goods_info->name}({$goods_info->id})\"的导出数据事件：";
            $event_info = $event->get_event_info($goods_info->event_backups);
            break;
        case 'timing':
            $title = "定义物品\"{$npc_info->name}({$npc_info->id})\"的分钟定时事件：";
            $event_info = $event->get_event_info($npc_info->event_timing);
            break;
    } 
} 

function load_equip_edit($event_clas, $event_paid) { // 加载装备事件信息
    global $event;
    global $equip;
    global $title;
    global $event_info;
    global $event_id;
    $obj_info = $equip->get_equip_info($event_paid);
    if ($obj_info->clas == "equipinlay" || $obj_info->clas == "weaponinlay") {
        $name = "镶物";
        $wear_title = "镶入";
        $undress_title = "取下";
    } else {
        $name = "装备";
        $wear_title = "穿上";
        $undress_title = "卸下";
    } 
    switch ($event_clas) {
        case 'create':
            $title = "定义装备\"{$obj_info->name}({$obj_info->id})\"的创建事件：";
            $event_info = $event->get_event_info($obj_info->event_create);
            break;
        case 'watch':
            $title = "定义装备\"{$obj_info->name}({$obj_info->id})\"的查看事件：";
            $event_info = $event->get_event_info($obj_info->event_watch);
            break;
        case 'use':
            $title = "定义装备\"{$obj_info->name}({$obj_info->id})\"的使用事件：";
            $event_info = $event->get_event_info($obj_info->event_use);
            break;
        case 'wear':
            $title = "定义{$name}\"{$obj_info->name}({$obj_info->id})\"的{$wear_title}事件";
            $event_info = $event->get_event_info($obj_info->event_save);
            break;
        case 'undress':
            $title = "定义{$name}\"{$obj_info->name}({$obj_info->id})\"的{$undress_title}事件：";
            $event_info = $event->get_event_info($obj_info->event_backups);
            break;
        case 'save':
            $title = "定义装备\"{$obj_info->name}({$obj_info->id})\"的存储数据事件";
            $event_info = $event->get_event_info($obj_info->event_save);
            break;
        case 'backups':
            $title = "定义物品\"{$obj_info->name}({$obj_info->id})\"的导出数据事件：";
            $event_info = $event->get_event_info($obj_info->event_backups);
            break;
        case 'timing':
            $title = "定义装备\"{$obj_info->name}({$obj_info->id})\"的分钟定时事件：";
            $event_info = $event->get_event_info($obj_info->event_timing);
            break;
    } 
} 

function load_task_edit($event_clas, $event_paid) { // 加载任务事件信息
    global $event;
    global $task;
    global $title;
    global $event_info;
    global $event_type;
    global $event_id;
    $task_info = $task->get_task_info($event_paid);
    switch ($event_clas) {
        case "accept":
            $temp = "接受";
            $event_id = $task_info->rwevent_accept;
            break;
        case "discard":
            $temp = "放弃";
            $event_id = $task_info->rwevent_discard;
            break;
        case "complete":
            $temp = "完成";
            $event_id = $task_info->rwevent_complete;
            break;
    } 
    switch ($event_type) {
        case "add":
            $title = "定义任务 \"{$task_info->name}\" {$temp}事件";
            break;
        case "edit":
            $title = "定义任务 \"{$task_info->name}\" {$temp}事件";
            $event_info = $event->get_event_info($event_id);
            break;
    } 
} 

function load_skill_edit($event_clas, $event_paid) { // 加载技能事件信息
    global $event;
    global $skill;
    global $title;
    global $event_info;
    global $event_id;
    global $event_type;
    $obj_info = $skill->get_skill_info($event_paid);
    switch ($event_clas) {
        case "use":
            $temp = "使用";
            $event_id = $obj_info->event_use;
            break;
        case "uplvl":
            $temp = "升级";
            $event_id = $obj_info->event_uplvl;
            break;
    } 
    switch ($event_type) {
        case "add":
            $title = "定义技能 \"{$obj_info->name}\" {$temp}事件";
            break;
        case "edit":
            $title = "定义技能 \"{$obj_info->name}\" {$temp}事件";
            $event_info = $event->get_event_info($event_id);
            break;
    } 
} 

function load_map_edit($event_clas, $event_paid) { // 加载地图事件信息
    global $event;
    global $map;
    global $title;
    global $event_info;
    global $event_id;
    $map_info = $map->get_mid_info($event_paid);
    switch ($event_clas) {
        case 'create':
            $title = "定义地图\"{$map_info->name}({$map_info->id})\"的创建事件：";
            $event_info = $event->get_event_info($map_info->event_create);
            break;
        case 'watch':
            $title = "定义地图\"{$map_info->name}({$map_info->id})\"的查看事件：";
            $event_info = $event->get_event_info($map_info->event_watch);
            break;
        case 'enter':
            $title = "定义地图\"{$map_info->name}({$map_info->id})\"的进入事件：";
            $event_info = $event->get_event_info($map_info->event_enter);
            break;
        case 'leave':
            $title = "定义地图\"{$map_info->name}({$map_info->id})\"的离开事件：";
            $event_info = $event->get_event_info($map_info->event_leave);
            break;
        case 'timing':
            $title = "定义地图\"{$map_info->name}({$map_info->id})\"的分钟定时事件：";
            $event_info = $event->get_event_info($map_info->event_timing);
            break;
    } 
} 

if (isset($event_info)) {
    $event_branch = $event->get_event_branch($event_info);
} ;

if (!isset($event_info) && $event_type != 'add') {
    $event_info = $event->get_event_info($event_enid);
    $event_branch = $event->get_event_branch($event_info);
    $type = $event->get_event_type($event_info->type);
    $title = $type[1] . $event_info->name;
} ;

require_once "html/header.php";

?>

<div id="dis">
<h2>事件编辑器</h2>
<h4><?php echo $title;

?></h4>
<div id="con"></div>
<input type="hidden" id="event_id" value="<?php echo $event_info->id ;
?>">
触发条件：
<textarea class="form-control" rows="3" id="trigger" ><?php echo $event_info->trigger;

?></textarea>
不满足条件提示语:
<textarea class="form-control" rows="3" id="trigger_fail" ><?php echo $event_info->trigger_fail;

?></textarea>
<br>
<!--步骤：-->
<table id="branch">
<?php echo $event_branch;

?>
</table>
<br>
	<button class="btn btn-primary" type="button" <?php echo alert_open;
?> onclick="event_save(<?php echo "'{$event_path}','{$event_paid}','{$event_clas}'";

?>)">保存修改</button>
	<p></p>
	<a class="btn btn-success"  href="branch-edit.php?type=add&path=<?php echo $event_path;

?>&key=<?php echo $event_paid;

?>&enid=<?php echo $event_info->id ;

?>">添加步骤</a>
	<hr>
	<div class="btn-group">
	<button class="btn btn-info " type="button">查看定义数据</button>
	<button class="btn btn-primary" type="button">导入定义数据</button>
	</div>
	<hr>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>

<script src="js/event.js"></script>

<script type="text/javascript"> 
function add_step(){//加载步骤编辑器界面
	$.post('event-gl.php',{basic:"open",type:"add_step"},function(data) {
		$("#dis").html(data);
	})
}

function event_save(path,key,clas){//保存一个事件的修改
	var enid = $('#event_id').val();
	var trigger = $('#trigger').val();
	var trigger_fail = $('#trigger_fail').val();
	$.post('event-gl.php',{basic:"open",type:"save_event",path:path,key:key,clas:clas,"trigger":trigger,"trigger_fail":trigger_fail,enid:enid},function(data) {
		ajax_alert(data);
	})
}

function edit_branch(type,key,confirm=false){//移动或删除一个步骤
	var enid = $('#event_id').val();
	$.post('event-gl.php',{basic:"open",type:"edit_branch","clas":type,enid:enid,"key":key},function(data) {
		ajax_alert(data);
		load_branch(enid);
	})
}

function load_branch(enid){//重新加载事件步骤
	$.post('event-gl.php',{basic:"open",type:"load_branch",enid:enid},function(data){
		$("#branch").html(data.body);
		$("#con").html(data.title);
	});
}

</script> 
<?php
require_once "html/footer.php";

?>