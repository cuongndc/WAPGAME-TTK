<?php
require_once "user_rights.php";

$table = $_SESSION['table'] ;
$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;
$_SESSION['table'] = $table;
// 以下代码用以重新定向数据库表访问路径
switch ($table) {
    case "map":
        $table = "mid";
        break;
    case "goods":
        $table = "daoju";
        break;
    case "skill":
        $table = "jineng";
        break;
} 
$test = file_get_contents("php://input");
parse_str($test, $value);
// print_r($value);
if (isset($value['basic'])) {
    switch ($value['type']) {
        case "Establish":
            $arry = new_menu($value['table']);
            break;
        case "Edit_read":
            $arry = Edit_read($table, $value);
            break;
        case "delete":
            $arry = del_attribute($table, $value);
            break;
        case "map_an":
            $arry = add_attribute($table, $value);
            break;
        case "Edit_write":
            $arry = edit_attribute($table, $value);
            break;
    } 
    ajax_alert($arry);
} 

function edit_attribute($table, $value) { // 编辑一条属性的设置
    global $attribute;
    $isadd = $attribute->set_attribute_gl("Edit", $table, $value["name"], 1, $value["sh"], 1, $value["mtype"], $value["desc"], $value["xh"], $value["max"], $value["yname"]);
    if ($isadd[0]) {
        return array('title' => '属性修改成功！', 'body' => '修改属性【' . $value['name'] . '】的操作成功！', 'button' => '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>', 'html' => $attribute->get_attribute_list($table));
    } else {
        return array('title' => $isadd[1]);
    } 
} 

function add_attribute($table, $value) { // 向数据库添加一条属性
    global $attribute;
    $isadd = $attribute->set_attribute_gl("new", $table, $value["name"], 1, $value["sh"], 1, $_POST["mtype"], $value["desc"], $value["xh"]);
    if ($isadd[0]) {
        return array('title' => '新建属性成功！', 'body' => '新的属性【' . $value["name"] . '】添加成功！', 'button' => '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>', 'html' => $attribute->get_attribute_list($table));
    } else {
        return array('title' => $isadd[1]);
    } 
} 

function new_menu($table) { // 加载新建属性菜单
    if ($table == "game1") {
        $content = '<p>消耗品可用：<p>
	<select id="axh" class="form-control">
		<option value="true">是</option>
		<option value="false">否</option>
	</select>';
    } 
    return array('title' => true , 'title' => '添加一条属性', 'body' => '
	<p>属性名:<p><input type="text" id="aname" class="form-control" placeholder="属性名">
	<p>属性类型：<p>
	<select id="atype" class="form-control">
		<option value="0">属性类型</option>
		<option value="10">文本型</option>
		<option value="3">整数型</option>
		<option value="7">逻辑型</option>
		<option value="0">字符串</option>
		<option value="8">日期时间</option>
	</select>'
         . $content . '<p>可视：<p>
	<select id="ash" class="form-control">
		<option value="true">真</option>
		<option value="false">假</option>
	</select>
	<p>属性备注：<p><input type="text" id="adesc" class="form-control" placeholder="属性备注">',
        'button' => '
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" onclick="map_an()" class="btn btn-primary">确认创建</button>');
} 

function Edit_read($table, $value) { // 加载编辑属性配置
    global $attribute;
    $cfg = $attribute->get_attribute_cfg($table, $value["name"]);
    $info = json_decode($cfg->column_comment);
    if ($table == "game1") {
        $content = '<p>消耗品可用：<p>
	<select id="axh" class="form-control"';
        if ($info->type == "s") {
            $content .= 'disabled="disabled"';
        } 
        $content .= '>
		<option value="true" ';
        if ($info->consume == "t") {
            $content .= ' selected = "selected"';
        } 
        $content .= ' >是</option>
		<option value="false"';
        if ($info->consume == "f") {
            $content .= ' selected = "selected"';
        } 
        $content .= ' >否</option>
	</select>';
    } 
    $html = '
		<p>属性名:<p><input type="text" id="aname" class="form-control" placeholder="属性名" value="' . $cfg->column_name . '"';
    if ($info->type == "s") {
        $html .= "readonly";
    } 
    $html .= '><input type="hidden" id="yname" value="' . $cfg->column_name . '"><p>属性类型：<p><select id="atype" class="form-control" ';
    if ($info->type == "s") {
        $html .= 'disabled="disabled"';
    } 
    $html .= '>	<option value="0">属性类型</option>';
    switch ($cfg->column_type) {
        case "int(4)":
            $typ = 7;
            break;
        case "datetime":
            $typ = 8;
            break;
        case "text":
            $typ = 10;
            break;
        case substr ($cfg->data_type, 0, 7) == "varchar":
            $typ = 0;
            break;
        case "int(11)":
            $typ = 3;
            break;
    } 
    $html .= '<option value="10"';
    if ($typ == 10) {
        $html .= ' selected = "selected"';
    } 
    $html .= '>文本型</option><option value="3"';
    if ($typ == 3) {
        $html .= ' selected = "selected"';
    } 
    $html .= '>整数型</option><option value="7"';
    if ($typ == 7) {
        $html .= ' selected = "selected"';
    } 
    $html .= '>逻辑型</option><option value="0"';
    if ($typ == 0) {
        $html .= ' selected = "selected"';
    } 
    $html .= '>字符串</option><option value="8"';
    if ($typ == 8) {
        $html .= ' selected = "selected"';
    } 
    $html .= '>日期时间</option>
	</select>';
    $html .=  $content . '<p>可视：<p>
	<select id="ash" class="form-control">
		<option value="true" ';
    if ($info->watch == "s") {
        $html .= ' selected = "selected"';
    } 
    $html .= '>真</option><option value="false"';
    if ($info->watch == "h") {
        $html .= ' selected = "selected"';
    } 
    $html .= '>假</option>
	</select>';
	if ($typ == 3) {
        $html .= '<p>是否有最大值：<p>
	<select id="max" class="form-control">
		<option value="true" ';
        if ($info->max == "t") {
            $html .= ' selected = "selected"';
        } 
        $html .= ' >是</option>
		<option value="false"';
        if ($info->max == "f") {
            $html .= ' selected = "selected"';
        } 
        $html .= ' >否</option>
	</select>';
    } 
	$html .='<p>属性备注：<p><input type="text" id="adesc" class="form-control" placeholder="属性备注" value="' . urldecode($info->Notes) . '">
      </div>';

    $button = '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
			 <button type="button" id="map_an" class="btn btn-primary" onclick="Edit_write()">保存修改</button>';

    return array('title' => true , 'title' => '修改一条属性', 'body' => $html, 'button' => $button);
} 

function del_attribute($table, $value) { // 删除一条属性配置
    global $attribute;
    $isadd = $attribute->set_attribute_gl("delete", $table, $value["name"], 1, $value["sh"], 1, $value["mtype"], $value["desc"], $value["xh"], $value["max"], $value["yname"]);
    if ($isadd[0]) {
        return array('title' => '属性删除成功！', 'body' => '删除属性【' . $value["name"] . '】添加成功！', 'button' => '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>', 'html' => $attribute->get_attribute_list($table));
    } else {
        return array('title' => $isadd[1]);
    } 
} 

?>