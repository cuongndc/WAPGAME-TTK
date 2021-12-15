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
$_SESSION['dis_name'] = $dis_name;

$test = file_get_contents("php://input");
parse_str($test, $value);
// print_r($value);
if (isset($value['basic'])) {
    switch ($value['type']) {
        case "Reset":
            echo ParsingPage($value['name']);
            break;
        case "save":
            $arry = save_elements($value);
            break;
        case "seaech":
            echo seaech_assembly($value);
            break;
        case "del":
            $arry = del_element($value);
            break;
        case "add_to":
            $arry = add_to($value);
            break;
        case "move":
            $arry = move_elements($value);
            break;
        case "edit":
            $arry = dis_edit($value);
            break;
        case "menu":
            $arry = new_menu();
            break;
        case "new":
            $arry = new_elements($value["clas"]);
            break;
    } 
    ajax_alert($arry);
    exit;
} 


function seaech_assembly($value) { // 搜索功能点
    global $sys;
    $Obj_seaech = $sys->get_assembly_seaech(trimall($value["name"]));
    $html = '<div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">区域查询结果</h4>
      </div> <div class="modal-body">';
    if ($Obj_seaech) {
        foreach($Obj_seaech as $obj) {
            $html .= '<div class="radio"><label><input type="radio" name="qy_rad"  value="' . $obj->value . '"> ' . $obj->nickname . '(' . $obj->value . ') </label></div>';
        } 
    } 
    $html .= '</div><div class="modal-footer"><button type="button" class="btn btn-default" onclick="$(\'#edit_sear\').html(\'\')">清空</button>';
    if (count($Obj_seaech) > 0) {
        $html .= '<button type="button" id="qy_xq" class="btn btn-primary">选取</button>';
    } 
    $html .= '</div></div>';
    return $html;
} 

function del_element($value) { // 删除页面元素
    global $dis;
    $id = $value["id"];
    $name = $value["name"];
    $layout = $dis->dis_get($name, "text");
    $odj = json_decode ($layout);

    $dis_string = $odj->$id->dis_string;

    if ($value["del"] != "true") {
        $button = <<<html
		<button type="button" class="btn btn-primary" onclick="del_element('$id','true')">确认删除</button>
html;
        return array('title' => '删除元素信息',
            'body' => "<h4>确认删除当前选中的[ {$dis_string} ]元素？ </h4>",
            'btn' => $button,
            'exbtn' => true,
            'error' => 'pop'
            );
    } elseif ($value["del"]) {
        $dis_string = $odj->$id->dis_string;
        unset($odj->$id);

        $dis_info = json_encode($odj);
        $button = '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';

        if ($dis->set_dis($name, $dis_info)) {
            return array('title' => '元素删除成功！' , 'btn' => $button , 'body' => "<h4>当前选中的[ {$dis_string} ]元素删除成功！ </h4>", 'error' => '');
        } else {
            return array('title' => '元素删除失败！', 'btn' => $button , 'body' => "<h4>当前选中的[ {$dis_string} ]元素删除失败！ </h4>", 'error' => 'true');
        } 
    } 
} 

function move_elements($value) { // 移动元素
    global $dis;
    $id = $value["id"];
    $number = $value["number"] == "" ? 1 : $value["number"] ;
    $layout = $dis->dis_get($value['name'], "text");
    $odj = json_decode ($layout, true);
    $test = array($id => $odj[$id]);
    unset($odj[$id]);
    array_splice($odj, $number, 0, $test);
    $layout = json_encode($odj);

    if ($dis->set_dis($value['name'], $layout)) {
        return array('title' => '移动元素成功！' , 'error' => '');
    } else {
        return array('title' => '移动元素失败！' , 'error' => 'true');
    } 
} 

function save_elements($value) { // 保存对元素的修改
    global $dis;
    global $operation;
    $layout = $dis->dis_get($value['name'], "text");
    $odj = json_decode ($layout);
	$id =  $value['id'];
    $type = $odj->$id->dis_type;
	if($type == 'open'){
		$operation->save_operation(array('name'=>$value["text"],'appear'=>$value["condition"],'key'=>$odj->$id->dis_open));
		$dis_open = $odj->$id->dis_open;
	}
    $odj->$id->dis_type = $type ;
	$odj->$id->dis_string = $value['text'];
	$odj->$id->dis_condition = $value['condition'];
	$odj->$id->dis_open = $dis_open;
	$odj->$id->dis_link = $value['link'];
	$odj->$id->dis_HotKey = $value['HotKey'] ;
	$odj->$id->dis_exit = $value['exit'];
    $layout = json_encode($odj);
    if ($dis->set_dis($value['name'], $layout)) {
        return array('title' => '保存元素数据成功！' , 'error' => '', 'id' => $test_id);
    } else {
        return array('title' => '保存元素数据失败！' , 'error' => 'true');
    } 
} 

function add_to($val) { // 新建元素
    global $dis;
    global $sys;
    global $operation;
    $layout = $dis->dis_get($val['name'], "text");
    $odj = json_decode ($layout, true);
    $i = intval($odj["count"]);
    $test_id = $i++;
	if($val["clas"]=='open'){
		$operation_id = $operation->new_operation();
		$operation->save_operation(array('name'=>$val["text"],'appear'=>$val["condition"],'key'=>$operation_id));
	}
    $odj[$test_id] = array("dis_type" => $val["clas"], "dis_string" => $val["text"], "dis_condition" => $val["condition"], "dis_open" => "{$operation_id}", "dis_link" => $val["link"], "dis_HotKey" => $val["HotKey"] , "dis_exit" => $val["exit"]);
    $odj["count"] = $i;
    $layout = json_encode($odj);
    if ($dis->set_dis($val['name'], $layout)) {
        return array('title' => '新建元素成功！' , 'error' => '', 'add_edit' => $val["edit"], 'id' => $test_id);
    } else {
        return array('title' => '新建元素失败！' , 'error' => 'true');
    } 
} 

function new_elements($clas) { // 新建元素选取完毕
    global $sys;
    switch ($clas) {
        case "text":
            $title = "添加文本元素";
            break;
        case "link":
            $title = "添加功能元素";
            break;
        case "open":
            $title = "添加操作元素";
            break;
        case "exit":
            $title = "添加链接元素";
            break;
    } 
    $html = <<<html
		元素名称：<textarea class="form-control" rows="3" id="text"></textarea>
		显示条件：<textarea class="form-control" rows="3" id="condition"></textarea>
html;

    if ($clas == "link") {
        $html .= '选择功能：<br>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择功能</span>
			<select class="form-control" id="assembly_xs"> 
				<option value="0">请选择一项需要的功能</option>';
        $Obj_assembly = $sys->get_assembly_all(0, 0, "all");
        foreach($Obj_assembly[1] as $obj) {
            $html .= "<option value='$obj->value'";
            if ($dis_link == $obj->value) {
                $html .= "selected";
            } 
            $html .= ">$obj->nickname($obj->value)</option>";
        } 
        $html .= '</select>
		</div>
	  </div><div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索功能</span>
			<input type="search" id="assembly_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="assembly_sear()" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div id="edit_sear"></div>
		<br>';
    } 
    if ($clas != "text") {
        $html .= '快捷键：<input  class="form-control" id="HotKey" value="">';
    } 
    if ($clas == "exit") {
        $html .= '链接地址：<input  class="form-control" id="exit" value="">';
    } 

    $btn = <<<html
	<button type="button" class="btn btn-primary" onclick="add_to('{$clas}','true')">保存</button>
html;
    return array('title' => $title, 'body' => $html, 'btn' => $btn, 'exbtn' => true);
} 

function new_menu() { // 加载元素管理菜单
    $html = <<<html
		<button class="btn" onclick="new_add('text')">添加文本元素</button>
		<button class="btn" onclick="new_add('link')">添加功能元素</button>
		<button class="btn" onclick="new_add('open')">添加操作元素</button>
		<button class="btn" onclick="new_add('exit')">添加链接元素</button>
		<hr>
		<button class="btn">查看定义数据</button>
		<button class="btn btn-warning">禁用查看场景页面模板</button>
		<button class="btn btn-danger">清空所有元素</button>
html;
    $button = '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
    return array('title' => '元素管理菜单', 'body' => $html, 'button' => $button,);
} 

function dis_edit($value) { // 编辑模板元素信息
    global $sys;
    global $dis;
    global $task;
	global $operation;
	$dis_name = $value['name'];
    $odj = json_decode ($dis->dis_get($dis_name, "text"));
    $id = $value['id'];
    $dis_string = $odj->$id->dis_string;
    $dis_condition = $odj->$id->dis_condition;
    $dis_link = $odj->$id->dis_link;
    $dis_type = $odj->$id->dis_type;
    switch ($dis_type) {
        case "text": 
            // $title="添加文本元素";
            break;
        case "link": 
            // $title="添加功能元素";
            $html .= '选择功能：<br>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择功能</span>
			<select class="form-control" id="assembly_xs"> 
				<option value="0">请选择一项需要的功能</option>';
            $Obj_assembly = $sys->get_assembly_all(0, 0, "all");
            foreach($Obj_assembly[1] as $obj) {
                $html .= "<option value='$obj->value'";
                if ($dis_link == $obj->value) {
                    $html .= "selected";
                } 
                $html .= ">$obj->nickname($obj->value)</option>";
            } 
            $html .= '</select>
		</div>
	  </div><div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索功能</span>
			<input type="search" id="assembly_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="assembly_sear()" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div id="edit_sear"></div>
		<br>';
            break;
        case "open": 
            // $title="添加操作元素";
			$operation_info = $operation->get_operation_info($odj->$id->dis_open);
			$dis_string = $operation_info->name;
			$dis_condition = $operation_info->appear;
			$event_html = "无事件定义";
			if (intval($operation_info->event) != 0) {
				$event_html = "事件ID：({$operation_info->event})";
			}
			$task_html = "无任务定义";
            if (intval($operation_info->task) != 0) {
				$task_info = $task->get_task_info($operation_info->task);
				$task_html = "任务名：{$task_info->name}({$task_info->id})";
			} 
            $html .= <<<html
			<div class="container"><p></p>
				<div class="row">
					<div class="col-xs-3">定义事件：</div>
					<div class="col-xs-5">{$event_html}</div>
				</div>
			</div>
			<div class="container"><p></p>
				<div class="row">
					<div class="col-xs-3">触发任务：</div>
					<div class="col-xs-5">{$task_html}</div>
				</div>
			</div>
			<div class="container"><p></p>
				<div class="row">
					<div class="col-xs-3"><h4>修改操作：</h4></div>
					<div class="col-xs-5">
						<a class="btn btn-info" href="operation.php?path=dis&id={$operation_info->id}">编辑操作定义</a>
					</div>
				</div>
			</div>
html;
            break;
        case "exit": 
            // $title="添加链接元素";
            $html .= "链接地址：<input  class='form-control' id='exit' value='" . $odj->$id->dis_exit . "'>";
            break;
    } 

    if ($dis_type != "text") {
        $html .= '快捷键：<input  class="form-control" id="HotKey" value="' . $odj->$id->dis_HotKey . '">';
    } 
    $html .= '<div class="container"><p></p>
				<div class="row">
				  <div class="col-xs-4"><h4>移动元素：</h4></div>
				  <div class="col-xs-4"><input class="form-control" id="move" ></div>
				  <div class="col-xs-4"><button type="button" class="btn btn-primary btn-block" onclick="move(' . "'$dis_name','$id'" . ')" data-dismiss="modal">确认移动</button></div>    
				</div>
			  </div>
			<div class="container"><p></p>
				<div class="row">
				  <div class="col-xs-4"><h4>删除元素：</h4></div>
				  <div class="col-xs-6"><button type="button" class="btn btn-danger" onclick="del_element(\'' . $id . '\')">删除本元素</button></div>
				</div>
			  </div>';
    $button = <<<html
		<button type="button" class="btn btn-primary" onclick="save('{$id}')" data-dismiss="modal">保存</button>
html;
    $html ='元素名称：<textarea class="form-control" rows="3" id="text">' . $dis_string . '</textarea>
					显示条件：<textarea class="form-control" rows="3" id="condition">' . $dis_condition . '</textarea>'.$html;

    if ($load != "true") {
        return array('title' => '编辑元素信息', 'body' => $html, 'btn' => $button, 'exbtn' => true ,'dis' => "");
    } else {
        return array('html' => $html, 'dis' => $dis);
    } 
} 

function ParsingPage($dis_name) { // 重新加载页面布局列表
    global $dis;
    global $sys;
    global $operation;
    $layout = $dis->dis_get($dis_name, "text");
    $obj = json_decode($layout);
    foreach($obj as $key => $value) {
        if (is_object ($value)) {
            $id = $key;
            $i++;
            $valuea = str_replace(array("\r\n", "\r", "\n"), '<i class="icon icon-level-down icon-rotate-90"></i><br>', $value->dis_string);
            if ($valuea == "") {
                if (isset($value->dis_link)) {
                    $assembly = $sys->get_assembly($value->dis_link);
                    $valuea = $assembly->nickname;
                } 
            } elseif($value->dis_type =='open') {
				$operation_info = $operation->get_operation_info($value->dis_open);
				$valuea = $operation_info->name;
			}
            $dis_text .= <<<html
	<a onclick="edit('{$id}')" data-position="100" data-toggle="modal" data-target="#ajax-alert"  class='link'>{$id} . {$valuea}</a>
html;
        } 
    } 
    return $dis_text;
} 

$dis_val = $_GET['dis'];

switch ($dis_val) {
    case 'mid':
        $dis_name = "dis_map";
        $subtitle = '定义场景页面模板';
        break;
    case 'npc':
        $dis_name = "dis_npc";
        $subtitle = '定义电脑人物页面模板';
        break;
    case 'pets':
        $dis_name = "dis_pets";
        $subtitle = '定义宠物页面模板';
        break;
    case 'good':
        $dis_name = "dis_good";
        $subtitle = '定义物品页面模板';
        break;
    case 'others':
        $dis_name = "dis_others";
        $subtitle = '定义玩家页面模板';
        break;
    case 'player':
        $dis_name = "dis_player";
        $subtitle = '定义自己页面模板';
        break;
    case 'skill':
        $dis_name = "dis_skill";
        $subtitle = '定义技能页面模板';
        break;
    case 'assembly':
        $dis_name = "dis_assembly";
        $subtitle = '定义功能页面模板';
        break;
    case 'battle':
        $dis_name = "dis_battle";
        $subtitle = '定义战斗页面模板';
        break;
    case 'index':
        $dis_name = "dis_index";
        $subtitle = '定义首页页面模板';
        break;
} 

$page_content = ParsingPage($dis_name);
require_once "html/header.php";

?>

<style>
a{ font-size:18px}
</style>

<div class="container">
<h2><?php echo $subtitle;
?></h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>添加新的元素</b></td><td style="text-align:right">
<button class="btn btn-primary" type="button" <?php echo alert_open ;
?> onclick="Template_menu()">管理菜单</button>
</td></tr>
</table>
<div id="new_task"></div>
<div id="window">
<?php echo $page_content; ?>
</div>
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>

<script src="js/event.js?_=<?php echo time();
?>"></script>
<script src="js/task.js?_=<?php echo time();
?>"></script>

<script type="text/javascript">
 
var dis_name = "<?php echo $dis_name;
?>";

function Template_menu(){//加载管理菜单
	$.post('page.php',{basic:"open",type:"menu",name:dis_name},function(data) { 
		ajax_alert(data);
    });
}

function Reset(obj,id){
	$.post('page.php',{basic:"open",type:"Reset",name:dis_name,id:id},function(data) {
	$("#window").html(data);
	$("#new_task").html("");
	edit(id);
	})
}

function Reset_page(){
	$.post('page.php',{basic:"open",type:"Reset",name:dis_name},function(data) {
		$("#window").html(data);
	})
}

function edit(id){//编辑元素信息
	$.post('page.php',{basic:"open",type:"edit",id:id,name:dis_name},function(data){
		if(data.dis!=""){$("#dis").html(data.dis);}
		ajax_alert(data);
    }) 
}

function new_add(type){//请求新建元素
	$.post('page.php',{basic:"open",type:"new",clas:type,name:dis_name},function(data) { 
		ajax_alert(data);
	})
}

function add_to(type,add_edit){//执行新建元素
	if(!add_edit){add_edit=false;} 
	var text=$("#text").val();
	var condition=$("#condition").val();
	var HotKey=$("#HotKey").val();
	var exit=$("#exit").val();
	var link = $("#assembly_xs").val();
	$.post('page.php',{basic:"open",type:"add_to",clas:type,text:text,condition:condition,edit:add_edit,name:dis_name,HotKey:HotKey,exit:exit,"link":link},function(data) { 
		if(data.error==""){
		 Reset_page()
		 $(".con").html(data.title)
		if(data.add_edit=="true"){edit(name,data.id)}
		}
		if(data.error=="pop"){
		$("#ajax_te").html(data.html); 
		$('#ajax_test').modal('show','fit');
		}
		if(data.error=="true"){
		(new $.zui.ModalTrigger({title: '提示',custom:data.title})).show();
		}
	});
}

function move(dis_name,id){//移动元素位置
	var number=$("#move").val();
	$.post('page.php',{basic:"open",type:"move",name:dis_name,id:id,number:number},function(data) { 
		if(data.error==""){
			Reset_page()
		 $(".con").html(data.title);
		}
		if(data.error=="pop"){
		Reset_page()
		$('#ajax_test').modal('show','fit');
		}
		if(data.error=="true"){
		(new $.zui.ModalTrigger({title: '提示',custom:data.title})).show();
		}
	});	
}

function del_element(id,del){//删除一个元素
	if(!del){del=false;} 
	$.post('page.php',{basic:"open",type:"del",name:dis_name,id:id,del:del},function(data) { 
		if(data.error==""){
			Reset_page()
		 $(".con").html(data.title);
		ajax_alert(data);
		}
	  if(data.error=="pop"){
		ajax_alert(data);
		Reset_page()
	  }
		if(data.error=="true"){
		ajax_alert(data); 
		}
	});	
}

function assembly_sear(){//搜索功能模块
  var name = $("#assembly_qy").val();
    $.post('page.php',{basic:"open",type:"seaech",name:name},function(data) {
	$("#edit_sear").html(data); 
  })
}

function save(id){//保存元素修改
  var link = $("#assembly_xs").val();
  var open = $("#open").val();
  var exit = $("#exit").val();
  var text = $("#text").val();
  var task = $("#task").val();
  var HotKey = $("#HotKey").val();
  var condition = $("#condition").val();
  var data= {basic:"open",type:"save","id":id,name:dis_name,"link":link,"task":task,"open":open,"exit":exit,"text":text,"HotKey":HotKey,"condition":condition};
	$.post('page.php',data,function(data) { 
		$("#ajax_test").off("hidden.zui.modal");
		if(data.error==""){
		 Reset_page()
		 $(".con").html(data.title)
		}
		if(data.error=="pop"){
		$("#ajax_te").html(data.html); 
		$('#ajax_test').modal('show','fit');
		}
		if(data.error=="true"){
		(new $.zui.ModalTrigger({title: '提示',custom:data.title})).show();
		}
	});
}


$(document).on("click", "#qy_xq", function() {//ajax选中功能模组
	$("#assembly_xs").val($("input[name='qy_rad']:checked").val());
	$("#edit_sear").html(""); 
});

</script> 

<?php
require_once "html/footer.php";

?>