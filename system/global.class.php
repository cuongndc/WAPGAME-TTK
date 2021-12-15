<?php
date_default_timezone_set("Asia/Shanghai");
const alert_open = ' data-position="100px" data-toggle="modal" data-target="#ajax-alert" '; //注册打开消息框
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once Game_path . 'system/ajax.class.php'; //Tải bộ điều khiển hộp tìm kiếm AJAX
require_once Game_path . 'system/pdosess.class.php'; //加载数据库连接 和 SESSION配置
require_once Game_path . 'system/system.class.php'; //加载系统核心配置
require_once Game_path . 'system/webmud.php'; //加载系统核心配置
require_once Game_path . 'system/task.class.php'; //加载任务系统控制配置
require_once Game_path . 'system/map.class.php'; //加载地图系统控制配置
require_once Game_path . 'system/formula.class.php'; //加载表达式系统控制配置
require_once Game_path . 'system/skill.class.php'; //加载表达式系统控制配置
require_once Game_path . 'system/dis.class.php'; //加载页面布局系统控制配置
require_once Game_path . 'system/player.class.php'; //加载表达式系统控制配置
require_once Game_path . 'system/event.class.php'; //加载事件系统控制配置
require_once Game_path . 'system/goods.class.php'; //加载事件系统控制配置
require_once Game_path . 'system/equip.class.php'; //加载事件系统控制配置
require_once Game_path . 'system/npc.class.php'; //加载事件系统控制配置
require_once Game_path . 'system/user_message.class.php'; //加载表达式系统控制配置
require_once Game_path . 'system/operation.class.php'; //加载操作系统类控制配置

require_once Game_path . 'system/game.php';
// ==============================================
require_once Game_path . 'system/user.class.php'; //加载用户控制类

// 实例化数据库连接
$pdo_ = new \sys\pdo_();
$dblj = $pdo_->dblj();
// 启动SESSION机制
$sess = new \sys\sess();
$sess->startSession();
// 实例化系统核心配置
$sys = new game_system\sys();
// 实例化属性编辑器
$attribute = new \sys\attribute();
// 实例化玩家角色控制
$player = new game_system\player();
// 实例化任务控制
$task = new game_system\task();
// 实例化地图控制
$map = new game_system\mid();
// 实例化表达式控制
$formula = new game_system\formula();
// 实例化技能控制
$skill = new game_system\skill();
// 实例化物品控制
$goods = new game_system\goods();
// 实例化装备控制
$equip = new game_system\equip();
// 实例化NPC控制
$npc = new game_system\npc();
// 实例化AJAX搜索控制
$searchBox = new game_system\searchBox();
// 实例化操作系统类控制配置
$operation = new game_system\operation();
// 实例化公式解析引擎
$webm = new game_system\WebMud();
// 实例化页面布局系统控制
$dis = new game_system\dis();
// 实例化事件控制
$event = new game_system\event();
// 实例化游戏控制原始类
$game = new \main\game();
// 实例化聊天控制
$user_message = new game_system\user_message();
// 远程请求（不获取内容）函数
_sock(Game_path . '1.php');
// 实例化用户控制数据
$user = new \user\user();

$user_info = $user->login_game_token($_SESSION['token']);

$user_info = $user_info->obj;

$game_name = $sys->get_system_config("系统", "游戏名称");
$game_desc = $sys->get_system_config("系统", "游戏简介");
$game_novice = $sys->get_system_config("游戏", "出生点");
if (!$game_name) {
    $game_name = "无名游戏";
} 

$变量_系统 = (object)[
"游戏名称" => $game_name,
];

function G_is_login() { // 判断用户登录状态是否有效
    if (!isset($_SESSION['sid'])) {
        header("refresh:1;url=../index.php");
        exit("长时间未操作，请重新登录");
    } 
} 

function G_is_god($token) { // 判断用户登录状态是否有效
    global $user;
    $user_power = $user->get_user_type($token);
    if ($user_power == 'god') {
        return true;
    } else {
        return false;
    } 
} 

function G_trimall($str) { // 删除全部空格换行
    $qian = array(" ", "　", "\t", "\n", "\r");
    return str_replace($qian, '', $str);
} 

function G_isPermit($str) { // 测试输入值是否为字段，字段名需要大于3位
    $isMatched = preg_match('/^[_0-9a-z]{2,25}$/', $str, $matches);
    if ($isMatched == 1) {
        return true;
    } else {
        return false;
    } 
} 

function _sock($url) { // 远程请求（不获取内容）函数，下面可以反复使用
    $host = parse_url($url, PHP_URL_HOST);
    $port = parse_url($url, PHP_URL_PORT);
    $port = $port ? $port : 80;
    $scheme = parse_url($url, PHP_URL_SCHEME);
    $path = parse_url($url, PHP_URL_PATH);
    $query = parse_url($url, PHP_URL_QUERY);
    if ($query) $path .= '?' . $query;
    if ($scheme == 'https') {
        $host = 'ssl://' . $host;
    } 

    $fp = fsockopen($host, $port, $error_code, $error_msg, 1);
    if (!$fp) {
        return array('error_code' => $error_code, 'error_msg' => $error_msg);
    } else {
        stream_set_blocking($fp, true); //开启了手册上说的非阻塞模式
        stream_set_timeout($fp, 1); //设置超时
        $header = "GET $path HTTP/1.1\r\n";
        $header .= "Host: $host\r\n";
        $header .= "Connection: close\r\n\r\n"; //长连接关闭
        fwrite($fp, $header);
        usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
        fclose($fp);
        return array('error_code' => 0);
    } 
} 

function timediff($begin_time, $end_time) { // 功能：计算两个时间戳之间相差的日时分秒
    $begin_time = strtotime($begin_time);
    $end_time = strtotime($end_time);
    if ($begin_time < $end_time) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    } 
    // 计算天数
    $timediff = $endtime - $starttime;
    $days = intval($timediff / 86400); 
    // 计算小时数
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600); 
    // 计算分钟数
    $remain = $remain % 3600;
    $mins = intval($remain / 60); 
    // 计算秒数
    $secs = $remain % 60;
    return array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
} 

function G_convertObjectClass($objectA, $objectB) { // 合并数据类为一个
    $new_object = (object)[]; 
    // 初始化类属性
	if(is_object($objectA)){
    foreach($objectA as $property => $value) {
        $new_object->$property = $value;
    } 
	}
	if(is_object($objectB)){
    foreach($objectB as $property => $value) {
        $new_object->$property = $value;
    } 
	}
    return $new_object;
} 

function rand_section ($area, $num, $total) { // 拆分数字到数组
    $average = round($total / $num);
    $sum = 0;
    $result = array_fill(1, $num, 0);

    for($i = 1; $i < $num; $i++) {
        // 根据已产生的随机数情况，调整新随机数范围，以保证各份间差值在指定范围内
        if ($sum > 0) {
            $max = 0;
            $min = 0 - round($area / 2);
        } elseif ($sum < 0) {
            $min = 0;
            $max = round($area / 2);
        } else {
            $max = round($area / 2);
            $min = 0 - round($area / 2);
        } 
        // 产生各份的份额
        $random = rand($min, $max);
        $sum += $random;
        $result[$i] = $average + $random;
    } 
    // 最后一份的份额由前面的结果决定，以保证各份的总和为指定值
    $result[$num] = $average - $sum; 
    // 结果呈现
    return $result;
} 

// 后台管理类全局方法
function ajax_alert($arry) { // 公用后台数据下行接口
    if (is_array($arry)) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($arry);
    } else {
        echo $arry;
    } 
} 

function add_branch_Packing_data($data, $new_data) { // 打包新建或修改后需要保存到步骤的数据
    $temp = json_decode($data);
    $key_id = $new_data['id'];
    $new_name = $new_data['name'];
    $new_val = $new_data['val'];
    $new_num = $new_data['num'];
    $new_mark = $new_data['mark'];
    $new_size = $new_data['size'];
    $new_category = $new_data['category'];
    if (is_object($temp)) { // 测试是否为原定义属性重新修改
        foreach($temp as $id => $obj) {
            if (isset($key_id)) {
                if ($obj->id == $key_id) {
                    $temp->$id->val = $new_val;
                    $temp->$id->num = $new_num ;
                    $curt = true;
                    break;
                } 
            } 
            if (isset($new_mark)) {
                if ($obj->mark == $new_mark) {
                    $temp->$id->name = $new_name;
                    $temp->$id->size = $new_size;
                    $temp->$id->category = $new_category;
                    $curt = true;
                    break;
                } 
            } 
            if ($obj->name == $new_name && $obj->id == $key_id && $obj->mark == $new_mark) {
                $temp->$id->val = $new_val;
                $temp->$id->num = $new_num ;
                $curt = true;
                break;
            } 
        } 
    } 
    if (!$curt) { // 非已定义属性修改，则分配新属性位置
        if (!is_object($temp)) {
            $temp = json_decode('{}');
            $id = 0;
        } 
        $id = $temp->id;
        $total = $temp->total;
        $id++;
        $total++;
        $temp->id = $id ;
        $temp->total = $total ;
        $temp->$id = json_decode('{}');
        if (isset($key_id)) {
            $temp->$id->id = $key_id;
        } 
        $temp->$id->name = $new_name;
        $temp->$id->val = $new_val;
        $temp->$id->num = $new_num;
        $temp->$id->mark = $new_mark;
        $temp->$id->size = $new_size;
        $temp->$id->category = $new_category;
    } 
    return json_encode($temp);
} 
// 维护或删除步骤已定义数据
function editing_step($val) { // 事件步骤维护主接口
    $id = $val['key'];
    $vid = $val['vid'];
    $com = $val['com'];
    $path = $val['path'];
    $confirm = $val['confirm'];
    switch ($val['clas']) {
        case 'del':
            $arry = $confirm == 'true' ? edit_branch_val_del_true($path, $com, $id, $vid) : edit_branch_val_del($path, $com, $id, $vid);
            break;
        case 'edit':
            $arry = edit_branch_val_edit($path, $com, $id, $vid);
            break;
    } 
    return $arry ;
} 

function edit_branch_val_edit($path, $com, $id, $vid) { // 编辑步骤属性定义值
    switch ($path) {
        case 'map';
            $arry = edit_branch_val_map($path, $com, $id, $vid);
            break;
        case 'npc';
            $arry = edit_branch_val_npc($path, $com, $id, $vid);
            break;
        case 'task';
            $arry = edit_branch_val_task($path, $com, $id, $vid);
            break;
        case 'daoju';
            $arry = edit_branch_val_daoju($path, $com, $id, $vid);
            break;
    } 
    return $arry;

    global $task;
    switch ($com) {
        case 'rwKilling';
            $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwKilling);
            break;
        case 'rwseek';
            $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwseek);
            break;
    } 
    switch ($com) {
        case 'rwseek':
            $title = '修改任务寻物物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("rwseek","{$id}","true")'>保存修改</button>
html;
            break;
        case 'rwKilling':
            $title = '修改任务击杀目标：';
            $body = <<<html
		人物名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="人物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("rwKilling","{$id}","true")'>保存修改</button>
html;
            break;
        case 'set_up':
            $branch_val = json_decode($info->set_up);
            $title = '编辑事件步骤的设置属性修改已设属性：';
            $body = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名" value="{$branch_val->$vid->name}">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值">{$branch_val->$vid->val}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_set_up","{$id}","true")'>保存修改</button>
html;
            break;
        case 'change_genus':
            $branch_val = json_decode($info->change_genus);
            $title = '编辑事件步骤的修改属性修改已设属性：';
            $body = <<<html
		输入属性名：
		<input type="text" class="form-control" id="add-name" placeholder="属性名" value="{$branch_val->$vid->name}">
		输入属性值：
		<textarea class="form-control" rows="3" id="add-value" placeholder="属性值">{$branch_val->$vid->val}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_change_genus","{$id}","true")'>保存修改</button>
html;
            break;
        case 'change_items':
            $branch_val = json_decode($info->change_items);
            $title = '编辑事件步骤的更改物品已设物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_change_items","{$id}","true")'>保存修改</button>
html;
            break;
        case 'challenge_people':
            $branch_val = json_decode($info->challenge_people);
            $title = '编辑事件步骤的挑战人物已设人物：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="人物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_challenge_people","{$id}","true")'>保存修改</button>
html;
            break;
        case 'adoptive_pets':
            $branch_val = json_decode($info->adoptive_pets);
            $title = '编辑事件步骤的添加宠物已设宠物：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="宠物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_challenge_people","{$id}","true")'>保存修改</button>
html;
            break;
        case 'del_pets':
            $branch_val = json_decode($info->del_pets);
            $title = '编辑事件步骤的删除宠物已设宠物：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="宠物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            $button = <<<html
		<button class='btn btn-primary' type='button' onclick='add_branch("add_challenge_people","{$id}","true")'>保存修改</button>
html;
            break;
        case 'user_input':
            $branch_val = json_decode($info->user_input);
            $title = "修改事件步骤添加用户输入";
            if ($branch_val->$vid->category == "number") {
                $category_nu = "selected";
            } 
            if ($branch_val->$vid->category == "text") {
                $category_te = "selected";
            } 
            $body = <<<html
		字段标识：	
		<input type="text" class="form-control" id="add-mark" placeholder="字段标识" value="{$branch_val->$vid->mark}">
		字段名称：
		<input type="text" class="form-control" id="add-name" placeholder="字段名称" value="{$branch_val->$vid->name}">
		最大长度：
		<input type="text" class="form-control" id="add-size" placeholder="最大长度" value="{$branch_val->$vid->size}">
		字段值类型：
		<select class="form-control" id="add-category" >
		  <option value="text" {$category_te}>字符串</option>
		  <option value="number" {$category_nu}>数值</option>
		</select>
html;

            $button = "<button class='btn btn-primary' type='button' onclick='add_user_input()'>确认添加</button>";
            $body .= <<<js
<script>
function add_user_input(){
	 var mark = $("#add-mark").val();
	 var name = $("#add-name").val();
	 var size = $("#add-size").val();
	 var category = $("#add-category").val();
	 var iadd = {mark:mark,name:name,size:size,category:category};
	 var data = {basic:"open",type:"add_branch",clas:"add_user_input",key:"{$id}",data:iadd};
	$.post('branch-edit.php',data,function(data) {
		ajax_alert(data);
		if(data.reloading){reloading({$id},"add_user_input");}
	})
}
</script>
js;
            break;
    } 
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_map($path, $com, $id, $vid) { // 编辑步骤属性定义值-MAP类
    global $map;
    $info = $map->get_mid_info($id);
    switch ($com) {
        case 'npc';
            $branch_val = json_decode($info->npc);
            $title = '修改地图NPC：';
            $body = <<<html
	  人物名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="人物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
        case 'goods':
            $branch_val = json_decode($info->goods);
            $title = '修改地图物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
        case 'edit_task':
            $branch_val = json_decode($info->task);
            $title = '修改地图物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		任务触发条件表达式：
		<textarea class="form-control" rows="3" id="add-value" placeholder="数量表达式">{$branch_val->$vid->val}</textarea>
html;
            break;
    } 
    $button = <<<html
	<button class='btn btn-primary' type='button' onclick='add_branch("{$com}","{$id}","true","{$path}")'>保存修改</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_npc($path, $com, $id, $vid) { // 编辑步骤属性定义值-NPC类
    global $npc;
    $info = $npc->get_npc_info($id);
    switch ($com) {
        case 'edit_skills';
            $branch_val = json_decode($info->skills);
            $title = '修改NPC技能定义：';
            $body = <<<html
	  技能名：
		<input id="add-id" type="hidden" placeholder="技能id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="技能名" readonly>{$branch_val->$vid->name}</textarea>
		等级表达式：
		<textarea class="form-control" rows="3" id="add-value" placeholder="等级表达式">{$branch_val->$vid->val}</textarea>
html;
            break;
        case 'goods':
            $branch_val = json_decode($info->drop_items);
            $title = '修改NPC掉落物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
        case 'drop_equip':
            $branch_val = json_decode($info->drop_equip);
            $title = '修改NPC掉落装备：';
            $body = <<<html
		装备名：
		<input id="add-id" type="hidden" placeholder="装备id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="装备名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
        case 'edit_task':
            $branch_val = json_decode($info->task);
            $title = '修改NPC任务：';
            $body = <<<html
		任务名：
		<input id="add-id" type="hidden" placeholder="任务id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="任务名" readonly>{$branch_val->$vid->name}</textarea>
		任务触发条件表达式：
		<textarea class="form-control" rows="3" id="add-value" placeholder="任务触发条件">{$branch_val->$vid->val}</textarea>
html;
            break;
    } 
    $button = <<<html
	<button class='btn btn-primary' type='button' onclick='add_branch("{$com}","{$id}","true","{$path}")'>保存修改</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_task($path, $com, $id, $vid) { // 编辑步骤属性定义值-TASK类
    global $task;
    $info = $task->get_task_info($id);
    switch ($com) {
        case 'rwKilling';
            $branch_val = json_decode($info->rwKilling);
            $title = '修改任务击杀目标定义：';
            $body = <<<html
	  人物名：
		<input id="add-id" type="hidden" placeholder="人物id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="人物名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
        case 'rwseek':
            $branch_val = json_decode($info->rwseek);
            $title = '修改任务寻找物品：';
            $body = <<<html
		物品名：
		<input id="add-id" type="hidden" placeholder="物品id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="物品名" readonly>{$branch_val->$vid->name}</textarea>
		数量表达式：
		<textarea class="form-control" rows="3" id="add-num" placeholder="数量表达式">{$branch_val->$vid->num}</textarea>
html;
            break;
    } 
    $button = <<<html
	<button class='btn btn-primary' type='button' onclick='add_branch("{$com}","{$id}","true","{$path}")'>保存修改</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_daoju($path, $com, $id, $vid) { // 编辑步骤属性定义值-GOODS类
    global $goods;
    $info = $goods->get_goods_info($id);
    switch ($com) {
        case 'edit_task':
            $branch_val = json_decode($info->task);
            $title = '修改NPC任务：';
            $body = <<<html
		任务名：
		<input id="add-id" type="hidden" placeholder="任务id" value="{$branch_val->$vid->id}">
		<textarea class="form-control" rows="3" id="add-name" placeholder="任务名" readonly>{$branch_val->$vid->name}</textarea>
		任务触发条件表达式：
		<textarea class="form-control" rows="3" id="add-value" placeholder="任务触发条件">{$branch_val->$vid->val}</textarea>
html;
            break;
    } 
    $button = <<<html
	<button class='btn btn-primary' type='button' onclick='add_branch("{$com}","{$id}","true","{$path}")'>保存修改</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function del_branch_Packing_data($data, $vid) { // 打包删除后需要保存到步骤的数据
    if (isset($vid) && intval($vid) > 0) {
        $total = $data->total ;
        $total--;
        unset($data->$vid);
        if ($total < 0) {
            $total = 0;
        } 
        $data->total = $total;
    } 
    return json_encode($data);
} 

function edit_branch_val_del($path, $com, $id, $vid) { // 预删除步骤属性定义值
    switch ($path) {
        case 'map';
            $arry = edit_branch_val_del_map($path, $com, $id, $vid);
            break;
        case 'npc';
            $arry = edit_branch_val_del_npc($path, $com, $id, $vid);
            break;
        case 'task';
            $arry = edit_branch_val_del_task($path, $com, $id, $vid);
            break;
        case 'daoju';
            $arry = edit_branch_val_del_daoju($path, $com, $id, $vid);
            break;
    } 
    return $arry;

    global $task;
    switch ($com) {
        case 'rwKilling';
            $info = $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwKilling);
            break;
        case 'rwseek';
            $info = $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwseek);
            break;
    } 

    switch ($com) {
        case 'rwKilling':
            $title = '修改任务击杀目标：';
            $body = "确认删除已经添加的击杀目标：<br>人物名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个电脑人物的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","rwKilling","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'rwseek':
            $title = '修改任务寻找物品：';
            $body = "确认删除已经添加的寻找物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个物品的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","rwseek","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;

        case 'set_up':
            $info = $this->load_branch_data($id);
            $branch_val = json_decode($info->set_up);
            $title = '编辑事件步骤的设置属性删除已设属性：';
            $body = "确认删除已经添加的设置属性：<br>属性名：<b>[{$branch_val->$vid->name}]</b><br>属性值：<b>[{$branch_val->$vid->val}]</b><br>这个属性的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","set_up","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'change_genus':
            $branch_val = json_decode($info->change_genus);
            $title = '编辑事件步骤的修改属性删除已设属性：';
            $body = "确认删除已经添加的设置属性：<br>属性名：<b>[{$branch_val->$vid->name}]</b><br>属性值：<b>[{$branch_val->$vid->val}]</b><br>这个属性的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","change_genus","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'change_items':
            $branch_val = json_decode($info->change_items);
            $title = '编辑事件步骤的更改物品删除已设物品：';
            $body = "确认删除已经添加的更改物品：<br>物品名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个属性的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","change_items","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'learning_skills':
            $branch_val = json_decode($info->learning_skills);
            $title = '编辑事件步骤的学会技能：';
            $body = "确认删除已经添加的学会技能：<br>技能名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个技能的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","learning_skills","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'abolish_skills':
            $branch_val = json_decode($info->abolish_skills);
            $title = '编辑事件步骤的废除技能：';
            $body = "确认删除已经添加的废除技能：<br>技能名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个技能的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","abolish_skills","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'trigger_task':
            $branch_val = json_decode($info->trigger_task);
            $title = '编辑事件步骤的触发任务：';
            $body = "确认删除已经添加的触发任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","trigger_task","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'del_task':
            $branch_val = json_decode($info->del_task);
            $title = '编辑事件步骤的删除任务：';
            $body = "确认删除已经添加的删除任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","del_task","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'del_task_ok':
            $branch_val = json_decode($info->del_task_ok);
            $title = '编辑事件步骤的删除已完成任务：';
            $body = "确认删除已经添加的删除已完成任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","del_task_ok","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'del_task_give_up':
            $branch_val = json_decode($info->del_task_give_up);
            $title = '编辑事件步骤的删除已放弃任务：';
            $body = "确认删除已经添加的删除已放弃任务：<br>任务名：<b>[{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","del_task_give_up","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'challenge_people':
            $branch_val = json_decode($info->challenge_people);
            $title = '编辑事件步骤的挑战人物：';
            $body = "确认删除已经添加的挑战人物：<br>人物名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个人物的设置？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","challenge_people","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'adoptive_pets':
            $branch_val = json_decode($info->adoptive_pets);
            $title = '编辑事件步骤的添加宠物：';
            $body = "确认删除已经添加的添加宠物：<br>宠物名名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个宠物的设置？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","adoptive_pets","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'del_pets':
            $branch_val = json_decode($info->del_pets);
            $title = '编辑事件步骤的删除宠物：';
            $body = "确认删除已经添加的删除宠物：<br>宠物名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个宠物的设置？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","del_pets","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'moving_target':
            $branch_val = json_decode($info->moving_target);
            $title = '编辑事件步骤的移动用户目标：';
            $body = "确认删除已经添加的移动用户目标：<br>地图名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个位置的设置？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","moving_target","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'user_input':
            $branch_val = json_decode($info->user_input);
            $title = '编辑事件步骤的删除已设用户输入：';
            $body = "确认删除已经添加的删除已设用户输入：<br>字段标识名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->size})]</b><br>这个用户输入的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","user_input","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
        case 'mall_members':
            $branch_val = json_decode($info->mall_members);
            $title = '编辑事件步骤的删除商城物品：';
            $body = "确认删除已经添加的删除已设商城物品：<br>物品名：<b>[{$branch_val->$vid->mark}{$branch_val->$vid->name}({$branch_val->$vid->id})]</b><br>这个物品的定义？";
            $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","mall_members","{$id}","{$vid}","true")'>确认删除</button>
html;
            break;
    } 
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_map($path, $com, $id, $vid) { // 预删除步骤属性定义值-MAP类
    global $map;
    $info = $info = $map->get_mid_info($id);
    switch ($com) {
        case 'npc';
            $branch_val = json_decode($info->npc);
            $title = '修改任务寻找物品：';
            $body = "确认删除已经添加的寻找物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个物品的定义？";
            break;
        case 'goods';
            $branch_val = json_decode($info->goods);
            $title = '修改任务寻找物品：';
            $body = "确认删除已经添加的寻找物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个物品的定义？";
            break;
        case 'edit_task';
            $branch_val = json_decode($info->task);
            $title = '修改任务寻找物品：';
            $body = "确认删除已经添加的寻找物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>这个物品的定义？";
            break;
    } 
    $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","{$path}","{$com}","{$id}","{$vid}","true")'>确认删除</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_npc($path, $com, $id, $vid) { // 预删除步骤属性定义值-NPC类
    global $npc;
    $info = $npc->get_npc_info($id);
    switch ($com) {
        case 'edit_skills';
            $branch_val = json_decode($info->skills);
            $title = '修改NPC技能定义：';
            $body = "确认删除已经添加的NPC技能定义：<br>技能名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>等级表达式：<b>[{$branch_val->$vid->val}]</b><br>这个技能的定义？";
            break;
        case 'drop_equip';
            $branch_val = json_decode($info->drop_equip);
            $title = '修改NPC掉落装备：';
            $body = "确认删除已经添加的NPC掉落装备：<br>装备名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个装备的定义？";
            break;
        case 'goods';
            $branch_val = json_decode($info->drop_items);
            $title = '修改NPC掉落物品：';
            $body = "确认删除已经添加的NPC掉落物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个物品的定义？";
            break;
        case 'edit_task';
            $branch_val = json_decode($info->task);
            $title = '修改NPC定义任务：';
            $body = "确认删除已经添加的NPC定义任务：<br>任务名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            break;
    } 
    $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","{$path}","{$com}","{$id}","{$vid}","true")'>确认删除</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_task($path, $com, $id, $vid) { // 预删除步骤属性定义值-TASK类
    global $task;
    $info = $task->get_task_info($id);
    switch ($com) {
        case 'rwKilling';
            $branch_val = json_decode($info->rwKilling);
            $title = '修改任务击杀目标定义：';
            $body = "确认删除已经添加的任务击杀目标定义：<br>人物名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个击杀目标的定义？";
            break;
        case 'rwseek';
            $branch_val = json_decode($info->rwseek);
            $title = '修改任务寻找物品：';
            $body = "确认删除已经添加的寻找物品：<br>物品名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>数量表达式：<b>[{$branch_val->$vid->num}]</b><br>这个物品的定义？";
            break;
    } 
    $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","{$path}","{$com}","{$id}","{$vid}","true")'>确认删除</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_daoju($path, $com, $id, $vid) { // 预删除步骤属性定义值-GOODS类
    global $goods;
    $info = $goods->get_goods_info($id);
    switch ($com) {
        case 'edit_task';
            $branch_val = json_decode($info->task);
            $title = '修改物品定义任务：';
            $body = "确认删除已经添加的物品定义任务：<br>任务名：<b>[{$branch_val->$vid->name}]({$branch_val->$vid->id})]</b><br>这个任务的定义？";
            break;
    } 
    $button = <<<html
		<button class='btn btn-danger ' type='button' onclick='editing_step("del","{$path}","{$com}","{$id}","{$vid}","true")'>确认删除</button>
html;
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_true($path, $com, $id, $vid) { // 删除步骤属性定义值
    switch ($path) {
        case 'map';
            $arry = edit_branch_val_del_true_map($path, $com, $id, $vid);
            break;
        case 'npc';
            $arry = edit_branch_val_del_true_npc($path, $com, $id, $vid);
            break;
        case 'task';
            $arry = edit_branch_val_del_true_task($path, $com, $id, $vid);
            break;
        case 'daoju';
            $arry = edit_branch_val_del_true_daoju($path, $com, $id, $vid);
            break;
    } 
    return $arry;
    global $task;
    switch ($com) {
        case 'rwKilling';
            $info = $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwKilling);
            break;
        case 'rwseek';
            $info = $info = $task->get_task_id($id);
            $branch_val = json_decode($info->rwseek);
            break;
    } 
    switch ($com) {
        case 'rwKilling':
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'rwseek':
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $com, $val)) {
                $title = '删除已经添加的寻找物品：';
                $body = "删除已经添加的寻找物品 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'set_up':
            $info = $this->load_branch_data($id);
            $branch_val = json_decode($info->set_up);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的设置属性删除已设属性：';
                $body = "编辑事件步骤的设置属性删除已设属性 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'change_genus':
            $branch_val = json_decode($info->change_genus);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的修改属性删除已设属性：';
                $body = "编辑事件步骤的修改属性删除已设属性 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'change_items':
            $branch_val = json_decode($info->change_items);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的更改物品删除已设物品：';
                $body = "删除已经添加的更改物品：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'learning_skills':
            $branch_val = json_decode($info->learning_skills);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的学会技能删除已设技能：';
                $body = "删除已经添加的学会技能：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'abolish_skills':
            $branch_val = json_decode($info->abolish_skills);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的废除技能删除已设技能：';
                $body = "删除已经添加的废除技能：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'trigger_task':
            $branch_val = json_decode($info->trigger_task);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的触发任务删除已设技能：';
                $body = "删除已经添加的触发任务：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'del_task':
            $branch_val = json_decode($info->del_task);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的删除任务删除已设技能：';
                $body = "删除已经添加的删除任务：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'del_task_ok':
            $branch_val = json_decode($info->del_task_ok);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的删除已完成任务删除已设技能：';
                $body = "删除已经添加的删除已完成任务：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'del_task_give_up':
            $branch_val = json_decode($info->del_task_give_up);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的删除已放弃任务删除已设技能：';
                $body = "删除已经添加的删除已放弃任务：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'challenge_people':
            $branch_val = json_decode($info->challenge_people);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的挑战人物：';
                $body = "删除已经添加的挑战人物：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'adoptive_pets':
            $branch_val = json_decode($info->adoptive_pets);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的收养宠物：';
                $body = "删除已经添加的收养宠物：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'del_pets':
            $branch_val = json_decode($info->del_pets);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的删除宠物：';
                $body = "删除已经添加的删除宠物：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'moving_target':
            $branch_val = json_decode($info->moving_target);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的移动用户目标：';
                $body = "删除已经添加的移动用户目标到：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'user_input':
            $branch_val = json_decode($info->user_input);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的删除已设用户输入：';
                $body = "删除已经添加的删除已设用户输入：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'mall_members':
            $branch_val = json_decode($info->mall_members);
            $name = $branch_val->$vid->name;
            $val = $this->del_branch_Packing_data($branch_val, $vid);
            if ($this->edit_branch_field($id, $com, $val)) {
                $title = '编辑事件步骤的商城商品：';
                $body = "删除已经添加的商城商品：[{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
    } 
    return array('title' => $title, 'body' => $body, 'btn' => $button , 'exbtn' => true);
} 

function edit_branch_val_del_true_map($path, $com, $id, $vid) { // 删除步骤属性定义值-MAP类
    global $map;
    $info = $map->get_mid_info($id);
    switch ($com) {
        case 'npc';
            $branch_val = json_decode($info->npc);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'goods';
            $branch_val = json_decode($info->goods);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'edit_task';
            $com = 'task';
            $branch_val = json_decode($info->task);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
    } 
} 

function edit_branch_val_del_true_npc($path, $com, $id, $vid) { // 删除步骤属性定义值-NPC类
    global $npc;
    $info = $npc->get_npc_info($id);
    switch ($com) {
        case 'edit_skills';
            $branch_val = json_decode($info->skills);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的技能：';
                $body = "删除已经添加的技能 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'goods';
            $branch_val = json_decode($info->drop_items);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'drop_equip';
            $branch_val = json_decode($info->drop_equip);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀目标：';
                $body = "删除已经添加的击杀目标 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'edit_task';
            $com = 'task';
            $branch_val = json_decode($info->task);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的定义任务：';
                $body = "删除已经添加的定义任务 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
    } 
} 

function edit_branch_val_del_true_task($path, $com, $id, $vid) { // 删除步骤属性定义值-TASK类
    global $task;
    $info = $task->get_task_info($id);
    switch ($com) {
        case 'rwKilling';
            $branch_val = json_decode($info->rwKilling);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的击杀人物：';
                $body = "删除已经添加的击杀人物 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
        case 'rwseek';
            $branch_val = json_decode($info->rwseek);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的寻找物品：';
                $body = "删除已经添加的寻找物品 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
    } 
} 

function edit_branch_val_del_true_daoju($path, $com, $id, $vid) { // 删除步骤属性定义值-NPC类
    global $goods;
    $info = $goods->get_goods_info($id);
    switch ($com) {
        case 'edit_task';
            $com = 'task';
            $branch_val = json_decode($info->task);
            $name = $branch_val->$vid->name;
            $val = del_branch_Packing_data($branch_val, $vid);
            if (edit_branch_field($id, $path, $com, $val)) {
                $title = '删除已经添加的定义任务：';
                $body = "删除已经添加的定义任务 [{$name}] 删除成功！";
                return array('title' => $title , 'body' => $body , 'reloading' => true);
            } else {
            } 
            break;
    } 
} 

function edit_branch_field($key, $path, $field, $val) { // 编辑步骤字段信息
    global $dblj;
    if ($key != 0) {
        switch ($path) {
            case 'map':
                $table = 'mid';
                switch ($field) {
                    case 'npc':
                        $field = 'npc';
                        $adopt = true;
                        break;
                    case 'goods':
                        $field = 'goods';
                        $adopt = true;
                        break;
                    case 'task':
                        $field = 'task';
                        $adopt = true;
                        break;
                } 
                break;
            case 'npc':
                $table = 'npc';
                switch ($field) {
                    case 'edit_skills':
                        $field = 'skills';
                        $adopt = true;
                        break;
                    case 'goods':
                        $field = 'drop_items';
                        $adopt = true;
                        break;
                    case 'drop_equip':
                        $field = 'drop_equip';
                        $adopt = true;
                        break;
                    case 'task':
                    case 'edit_task':
                        $field = 'task';
                        $adopt = true;
                        break;
                } 
                break;
            case 'task':
                $table = 'renwu';
                switch ($field) {
                    case "rwKilling":
                        $field = "rwKilling";
                        $adopt = true;
                        break;
                    case "rwseek":
                        $field = "rwSeek";
                        $adopt = true;
                        break;
                } 
                break;
            case 'daoju':
                $table = 'daoju';
                switch ($field) {
                    case 'task':
                    case 'edit_task':
                        $field = 'task';
                        $adopt = true;
                        break;
                } 
                break;
        } 

        if (!$adopt) {
            return false;
        } 
        $sql = "UPDATE {$table} SET {$field} = ? WHERE `id` = ?;";
        $stmt = $dblj->prepare($sql);
        $ret = $stmt->execute(array($val, $key));
        return $ret;
    } 
} 

?>

