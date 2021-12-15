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
    if ($value['type'] == 'save') {
        $data = json_decode($value['data']);
		if(is_object($data)){
			if($sys->set_system_config('system', 'skill_config',json_encode($data))){
				ajax_alert(array ('title'=>'技能默认值保存成功！','body'=>'定义技能默认值成功！'));
			}
		}
    } ;
	exit;
} 
require_once "html/header.php";
$skill_config = json_decode($sys->get_system_config('system', 'skill_config'));
$player_config = $attribute->get_table_config("game1");
if (!is_object($skill_config)) {
    $skill_config = (object)[];
} 

?>
<div class="container">
<h2>技能管理</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>修改技能默认值</b></td><td style="text-align:right"></td></tr>
</table>
<form class="form-horizontal" id="add">
	<div class='form-group'>
		<label class='col-sm-2'>伤害目标：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="deplete_attr"> 
<?php
$deplete_attr = $skill_config->deplete_attr;
foreach($player_config as $obj) {
    $typ = $obj->column_type;
    switch ($typ) {
        case "int(11)":
            $hs = json_decode($obj->column_comment);
            if ($hs->consume == "t") {
                echo "<option value='{$obj->column_name}'";
                if ($deplete_attr == $obj->column_name) {
                    echo "selected";
                } ;
                echo ">" . urldecode($hs->Notes) . "({$obj->column_name})</option>";
            } 
            break;
    } 
} 

?>
		</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>消耗目标：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="hurt_attr"> 
<?php
$hurt_attr = $skill_config->hurt_attr;
foreach($player_config as $obj) {
    $typ = $obj->column_type;
    switch ($typ) {
        case "int(11)":
            $hs = json_decode($obj->column_comment);
            if ($hs->consume == "t") {
                echo "<option value='{$obj->column_name}'";
                if ($hurt_attr == $obj->column_name) {
                    echo "selected";
                } ;
                echo ">" . urldecode($hs->Notes) . "({$obj->column_name})</option>";
            } 
            break;
    } 
} 

?>
		</select>
		</div>
	</div>
  <div class="form-group">
    <label for="hura_cost" class="col-sm-2">伤害值公式：</label>
    <div class="col-md-6 col-sm-10">
	<textarea class="form-control" rows="3" id="hura_cost" name="hura_cost" placeholder="伤害值公式"><?php echo $skill_config->hura_cost; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="deplete_cost" class="col-sm-2">消耗值公式：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="deplete_cost" name="deplete_cost" placeholder="消耗值公式"><?php echo $skill_config->deplete_cost; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="add_point_exp" class="col-sm-2">使用一次熟练度表达式：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="add_point_exp" name="add_point_exp" placeholder="使用一次熟练度表达式"><?php echo $skill_config->add_point_exp; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="get_cost" class="col-sm-2">学习条件：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="get_cost" name="get_cost" placeholder="学习条件"><?php echo $skill_config->get_cost; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="not_get_desc" class="col-sm-2">不满足学习条件时提示语：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="not_get_desc" name="not_get_desc" placeholder="不满足学习条件时提示语"><?php echo $skill_config->not_get_desc; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="effect_cmmt" class="col-sm-2">使用效果描述：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="effect_cmmt" name="effect_cmmt" placeholder="使用效果描述"><?php echo $skill_config->effect_cmmt; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="uplvl_math" class="col-sm-2">升级公式：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="uplvl_math" name="uplvl_math" placeholder="升级公式"><?php echo $skill_config->uplvl_math; ?></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="uplvl_cost" class="col-sm-2">升级条件：</label>
    <div class="col-md-6 col-sm-10">
    <textarea class="form-control" rows="3" id="uplvl_cost" name="uplvl_cost" placeholder="升级条件"><?php echo $skill_config->uplvl_cost; ?></textarea>
    </div>
  </div>
</form>
<div class="form-horizontal">
	<div class='form-group'>
		<label class='col-sm-2'>升级事件：</label>
		<div class='col-md-6 col-sm-10'>
			<div class='row'>
<?php
if ($obj->event_uplvl == "") {
    echo "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=skill&key={$obj->id}&clas=uplvl' >添加事件</a></div>";
} else {
    echo "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=skill&key={$obj->id}&clas=uplvl'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open}  onClick=\"del_event('skill','uplvl','{$obj->id}','{$obj->event_upgrade}')\">删除事件</button></div>";
} 

?>
			</div>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>使用事件：</label>
		<div class='col-md-6 col-sm-10'>
			<div class='row'>
<?php
if ($obj->event_use == "") {
    echo "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=skill&key={$obj->id}&clas=use' >添加事件</a></div>";
} else {
    echo "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=skill&key={$obj->id}&clas=use'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open} onClick=\"del_event('skill','use','{$obj->id}','{$obj->event_apply}')\">删除事件</button></div>";
} 

?>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary"  onclick="edit_skill()" <?php echo alert_open;?> >确定</button> 
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="button" class="btn btn-info">还原所有默认值</button>
		</div>
	</div>
</div>
<hr>
<a href="skill-list.php"  class="btn btn-block " >返回上级</a>
</div>
<br><br>

<script type="text/javascript"> 

function edit_skill(){//编辑技能信息
	var params = $("#add").serializeArray();
	var values = {};
	for( x in params ){
		values[params[x].name] = params[x].value;
	}
	  var idata = JSON.stringify(values)
	  var data = {basic:"open",type:"save","data":idata }
	  $.post('skill-default.php',data,function(data) {ajax_alert(data);});
}

<?php

$post_path = "skill-default.php";
require_once "js/edit-data.php";

?>
</script>

<?php
require_once "html/footer.php";

?>