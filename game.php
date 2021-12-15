<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

const Game_path = __DIR__ . "/"; //注册全居运行路径

require_once Game_path . '/system/global.class.php';

G_is_login();

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;

if (!isset($sid_sys)) {
    header("refresh:1;url=index.php");
}

if (G_is_god($user_info->token)) { // 超级管理员状态监控打印
    echo "
	<style>
		.div{ margin:0 auto; width:80%; height:auto; border:1px solid #F00}
	</style>";
} 

$get_url = $_GET['c'];

$arr_data = (object)[];

if (!isset($get_url)) { // 从首页进入即直接访问地图界面
    $arr_data->cmd = 'mid';
    $cmd2 = 'gonowmid';
} else {
    if (!isset($_SESSION[ $get_url ])) {//判断访问地址是否正确，不正确的话返回上次访问地址
        $url_data = $_SESSION[ 'past' ];
    } else {
        $url_data = $_SESSION[ $get_url ];
    } 
	
    if ($url_data) {
        parse_str($url_data, $arr_data); //
        $arr_data = (object)$arr_data;

        if (G_is_god($user_info->token)) { // 超级管理员状态监控打印
            echo "
			<div class='div'>=======================================================================<br>
			GOD状态显示行：
			<br>session: {$get_url}
			<br><b>访问页面->收到数据：</b>{$url_data}<br>
			"; 
            /*
			echo "<b>Session:</b>"
			var_dump($_SESSION);		
			echo '<br>';
			*/
			echo "
			<b>Session取地址:</b>{$get_url}<br>
			<b>URl取地址:</b>{$url_data}<br>
			<b>命令符对像：</b>\$arr_data; ";
            var_dump($arr_data);
            echo "
			<br>=======================================================================</div>";
        } 

		} else {
        $arr_data->cmd = 'mid';
        $cmd2 = 'gonowmid';
    } 
} 
$url_past = $_SESSION[ 'past' ];

$player_info = $player->get_player_info(); //获取玩家信息

if ($_POST['form_userinput'] == '1') {//获取玩家输入信息
    $input = (object)[];
    foreach($_POST as $arry => $val) {
        $input->$arry = (object)[];
        $input->$arry->name = $arry;
        $input->$arry->val = $val;
    }
	//var_dump($input);
    $player->set_player_ut($player_info->sid, 'input', json_encode($input));
} 

$us = json_decode($player_info->us_val);

$event_db = json_decode($us->user_event);
$event_name = $event_db->val; //读取需要处理的事件
// var_dump($us->user_event,$event_name);
if (isset($event_name)) {
    // echo '公共事件解析引擎 -->>> ' . $event_name . '<br>';
    $event_info = "";
    switch ($event_name) {
        case 'User_register':
            $player->set_player_us($player_info->sid, "user_event", "event_login");
            $branch = $event->event_decode(2, $event_info);
            break;
        case 'event_login':
            $event_id = 3;
            $player->set_player_us($player_info->sid, "user_event");
            $branch = $event->event_decode(3, $event_info);
            break;
    } 
    echo $event_info;
} 

$user_dis = $us->user_dis->val; //读取需要加载的模板名称数据

// var_dump($user_dis );
if (isset($user_dis)) {
    // echo '公共模板解析引擎 -->>> ' . $user_dis . '<br>';
    switch ($user_dis) {
        case '':
            $sys_dis = "dis_land";
            break;
        default:
            $sys_dis = $user_dis;
    } 
}

//var_dump( $url_data , $url_past , $_SESSION[ $get_url ] );

$_SESSION = array();

$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;

if (isset($url_data)) {
    $_SESSION['past'] = $url_data;
} 

if (isset($url_data) && isset($get_url)) {
    $_SESSION[ $get_url ] = $url_data;
} 

if (!isset($c) || !isset($a)) {
    $c = 0;
    $a = 0;
} 
require_once Game_path . '/system/var.class.php';

$player->player_update_sfzx(1);

require_once "html/header.php"; //创建MUD关键画布

$ym = "./game/{$arr_data->cmd}.php";

if (G_is_god($user_info->token)) { // 超级管理员状态监控打印
    echo "
	<div class='div'>
	=================================================<br>
	GOD 处理完毕 --状态显示行：<br>
	访问页面->收到数据：{$url_data}<br>
	状态输出：{$ym}<br>
	=================================================
	</div>";
	//$dis_info = "{u.input.nick_name}";
	//echo $dis->dis_text_decode($dis_info , $player_info,null,null,false);
} 

$branch_html = "";
if(empty($url_past)){$url_past="game.php";};
if (G_is_god($user_info->token)) {
    echo "<div class='div'>触发前地址:{$url_past}<BR>当 前 地 址:{$get_url}</div>";
} 
switch ($arr_data->cmd) {
    case "operation"://处理玩家的点击操作触发
		$_SESSION[ $get_url ] =  $url_past;//操作触发完毕后设置访问地址为触发操作之前的访问路径
		unset( $_SESSION[ 'past' ] );
        $operation_info = $operation->get_operation_info($arr_data->id); 
        if (!isset($operation_info->task) && !isset($operation_info->event)) {
            $branch_html = "操作未定义！";
        } 
        if (isset($operation_info->event)) {
            $bool = $event->event_decode($operation_info->event, $branch_html);
			//var_dump($bool,$branch_html);
			parse_str( $url_past, $arr_data);
			$ym = "./game/{$arr_data->cmd}.php";
        } 
        if (isset($operation_info->task)) {
            $task_id = $operation_info->task;
			$ym =  "./game/taskinfo.php";
        } 
        if (!$bool) {
           $branch_html .= '<br>' . $变量_系统->链接_返回游戏_按钮短;
        }

        break;	
} 
    if(!empty($player_info->branch_id)){//默认需要处理的事件
		$event->load_play_branch($branch_html);
	}elseif(!empty($player_info->event_id)){
		execute_event($player_info, $event,$branch_html );
	}
	
	$branch_html_v = str_replace('<br>' ,'',$branch_html);
	
	if(!empty($player_info->enemy) and $arr_data->cmd != "pve_new" ){//玩家处于战斗中
		unset($arr_data->cmd2);
		$ym = "./game/pve_new.php";
	}

	if(!empty($branch_html_v)){
		echo  $branch_html;
	}else{
		if ($arr_data->cmd == '') { // 抓取用户实际访问路径
			$ym = "./game/mid.php";
		}
		if (file_exists($ym)) {
			require_once $ym;
		} else {
			echo "<br><br> 访问页面不存在！收到数据：{$url_data}<br>状态输出：{$ym}<br><br>";
			echo $sys->create_url_nowmid();
		} 
	}



require_once "html/footer.php";//MUD画板创建完毕


function execute_event($player_info, $event,&$branch_html) { // 解析触发事件并执行
    if (isset($player_info->event_id) && count((array)$player_info->event_id) > 0) {
        //echo "事件处理准备中……<br>";
        $branch_html = "";
        $event->load_play_event($branch_html);
    } 
} 

?>
