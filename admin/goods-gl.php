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

$test = file_get_contents("php://input");
parse_str($test, $value);
// print_r($value);
if (isset($value['basic'])) {
    switch ($value['type']) {
        case "reload":
            $arry = $goods->goods_type_load($value["clas"], $value["page"], $value["recPerPage"]);
            break;
        case "save":
            $arry = new_goods($value);
            break;
        case "edit":
            $arry = edit_goods($value['key'], $value['allow'], $value['clas'], $value[data]);
            break;
        case "del":
            $arry = del_goods($value['key'], $value['allow'], $value['clas']);
            break;
        case "seaech":
            echo load_seaech_goods($value);
            break;
        case "Selection":
            echo load_Selection();
            break;
    } 
    ajax_alert($arry);
} 

function load_seaech_goods($value) { // 管理后台选中物品分类及区域加载具体物品
    global $goods;
    global $map; 
    $goods_type = $goods->get_goods_type($value['goodtype']);
    $Obj_game_wpall = $goods->get_goods_all($goods_type[0], $value['qyval']);
    echo '<div class="modal-content">
      <div class="modal-header">'; 
    echo '<hr><h4 class="modal-title">';
    $name = $map->get_qy_name(trimall($value['qyval']));
    if ($name == "()") {
        echo "(所有区域)";
    } else {
        echo "$name";
    } 
    echo ' >>' . $goods_type[1] . ' 物品查询结果</h4></div> <div class="modal-body">';
    if (is_array($Obj_game_wpall)) {
        foreach($Obj_game_wpall as $obj) {
            if (is_object($obj)) {
                echo '<div class="radio"><label><input type="radio" name="mid_rad" value="' . $obj->id . '">' . $map->get_qy_name($obj->djqy) . "/" . $obj->name . '(' . $obj->id . ') </label></div>';
            } 
        } 
    } 
    echo '</div><div class="modal-footer">';
    if ($value['edit'] != "true") {
        echo '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
    } else {
        echo '<button type="button" class="btn btn-default" onclick="$(\'#edit_sear\').html(\'\')" >清空</button>';
    } 
    echo '<button type="button" id="qydt_xq" class="btn btn-primary">选取</button>
      </div>
    </div>';
} 

function load_Selection() { // 管理后台加载物品区域和类别选取页面
    global $map; 
    // global $goods;
    $title = "选取一个物品";
    $content = <<<html
	  <div class="container"><!--[选择区域]-->
	<h4>选择物品种类：</h4>
		<select class="form-control" id="good-type"> 
				<option value="consume">消耗品</option>
				<option value="weapon">兵器</option>
				<option value="equip">防具</option>
				<option value="book">书籍</option>
				<option value="weaponinlay">兵器镶物</option>
				<option value="equipinlay">防具镶物</option>
				<option value="taskitems">任务物品</option>
				<option value="other">其他</option>
		</select>
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6"><p></p>
	    <div class="input-group">
		  <span class="input-group-addon">搜索物品</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6"><p></p>
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" id="quyu_xs" onchange="qy_sear('edit')"> 
				<option value="0">请选择一级区域</option>
html;
    $Obj_game_qy = $map->get_qy_all();
    foreach($Obj_game_qy[1] as $obj) {
        $content .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
    } 
    $content .= '</select>
		</div>
	  </div>
	  <div id="edit_sear"></div>
	</div>
</div>';
    return ajax_alert($title, $content, "0");
} 

function del_goods($key, $true, $clas) { // 删除物品
    global $attribute;
    global $goods;
    if ($key != 0) {
        if ($true == "true") {
            $obj = $goods->get_goods_info($key);
            $type = $goods->type;
            if ($attribute->del_record("daoju", $key)) {
                $content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品操作成功!</p>";
                return array('title' => "删除物品成功！", 'body' => $content, 'reload' => true , 'type' => $type);
            } else {
                $title = "删除物品失败！";
                $content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品的操作失败了!</p>";
            } 
        } else {
            $title = "删除一个物品";
            $obj = $goods->get_goods_info($key);
            $content = "<p>确认删除<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品？</p>";
            $button = '<button type="button" onclick="del_goods(\'' . $obj->id . '\',\'true\')" class="btn btn-danger">确认删除</button>';
        } 
    } 
    return array('title' => $title, 'body' => $content, 'btn' => $button, 'exbtn' => true);
} 

function edit_goods($key, $true, $clas, $data = "") { // 编辑物品
    global $attribute;
    global $goods;
    global $map;
    $clas = str_replace("#", "", $clas);
    $tit = $goods->get_goods_type($clas);
    if ($key != 0) {
        if ($true == "true") {
            if ($attribute->edit_record("daoju", $key, $data)) {
                $obj = $goods->get_goods_id($key);
                $content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品操作成功!</p>";
                return array('title' => '物品信息修改成功！' , 'body' => $content , 'refresh' => true);
            } else {
                $title = "物品修改信息失败！";
                $obj = $goods->get_goods_id($key);
                $content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" </span>这个物品的操作失败了!</p>";
                $button = "0";
            } 
        } else {
            $title = "编辑一个物品信息";
            $good = $goods->get_goods_id($key);
            $list = $attribute->get_attribute_edit("daoju", $key, array(), array());
            $content = <<<html
	  <form id="add" method="post">
	  <input type="hidden" name="djtype" id="list" class="form-control" value="{$clas}"> 
		<div class="container"><!--[选择区域]-->
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6">
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6">
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" name="djqy" id="quyu_xs"> 
				<option value="0">请选择一级区域</option>
html;
            $Obj_game_qy = $map->get_qy_all();
            foreach($Obj_game_qy->data as $obj) {
                $content .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
            } 
            $content .= '</select>
		</div>
	  </div>
	  <div id="edit_sear"> </div></div><hr>';
            $content .= $list;
            $button = '<button type="button" onclick="edit_goods(\'' . $clas . '\',\'' . $good->id . '\',\'true\')" class="btn btn-primary">保存修改</button>';
            $arry = array('title' => $title, 'body' => $content, 'btn' => $button, 'exbtn' => true);
        } 
    } 
    return $arry;
} 

function new_goods($value) { // 新建物品
    global $map;
    global $goods;
    global $attribute;
    $clas = str_replace("#", "", $value['clas']);
    $tit = $goods->get_goods_type($clas);
    if (isset($value['data'])) {
        $obj = json_decode($data, true);
        if ($obj->add_record) {
            if ($attribute->add_record("daoju", $value['data']) > 0) {
                $obj = json_decode($value['data']);
                $content = "新建<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" 	</span>这个物品操作成功!</p>";
                return array("title" => "新建物品【 {$tit['1']} 】成功！", "body" => $content , 'refresh' => true);
            } else {
                echo "物品新建失败！";
            } 
        } else {
            if ($attribute->edit_record("daoju", $value['data']) > 0) {
                $obj = json_decode($value['data']);
                $content = "修改<span style='font-size:16px;' class='text-red'> \"{$obj->name}\" 	</span>这个物品操作成功!</p>";
                return array("title" => "修改物品【 {$tit['1']} 】成功！", "body" => $content , 'refresh' => true);
            } else {
                echo "物品修改失败！";
            } 
        } 
    } else {
        $title = "新建{$tit['1']}";
        $content = <<<html
	  <form id="add" method="post">
	  <input type="hidden" name="djtype" id="list" class="form-control" value="{$clas}"> 
		<div class="container"><!--[选择区域]-->
	<h4>选择区域：</h4>
	<input type="hidden" id="edit_open" value="true"/> 
	<div class="row">
	  <div class="col-sm-6">
	    <div class="input-group">
		  <span class="input-group-addon">搜索区域</span>
			<input type="search" id="sear_qy" class="form-control">
			<span class="input-group-btn">
				<button class="btn btn-default" onclick="qy_sear('edit')" type="button">搜索</button>
			</span>
		</div>
	  </div>
	  <div class="col-sm-6">
		<div class="input-group">
	  	  <span class="input-group-addon">选择区域</span>
			<select class="form-control" name="djqy" id="quyu_xs"> 
				<option value="0">请选择一级区域</option>
html;
        $Obj_game_qy = $map->get_qy_all();
        foreach($Obj_game_qy->data as $obj) {
            $content .= "<option value='$obj->qyid'>$obj->qyname($obj->qyid)</option>";
        } 
        $content .= '</select>
		</div>
	  </div>
	  <div id="edit_sear"> </div></div><hr>';
        $content .= $attribute->get_attribute_new("daoju", array(), array());
        $content .= '</form>
      <div class="modal-footer">';
        $button = "<button type=\"button\" onclick=\"new_goods('true')\" class=\"btn btn-primary\">确认创建</button>";
        return array('title' => $title, 'body' => $content, 'btn' => $button, 'exbtn' => true);
    } 
} 

?>