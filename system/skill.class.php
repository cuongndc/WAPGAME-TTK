<?php 
// 游戏技能管理类
namespace game_system;

class skill {
    public $dblj;
    public $attribute;
    function __construct() {
        global $dblj;
        global $attribute;
        $this->dblj = $dblj;
        $this->attribute = $attribute;
    } 

    function del_skill($key, $true, $clas) { // 删除技能
        if ($key != 0) {
            $skil = $this->get_skill_info($key);
            if ($true == "true") {
                try {
                    $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $this->dblj->beginTransaction();
                    $stmt = $this->dblj->prepare('DELETE from `jineng` WHERE id =?;');
                    $stmt->execute(array($key));
                    if ($stmt->rowCount() == 1) {
                        $bool = $this->dblj->commit();
                    } 
                } 
                catch(Exception $e) {
                    $this->dblj->rollback();
                } 
                if ($bool) {
                    $reload = true;
                    $title = "技能删除成功！";
                    $content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$skil->name}\" </span>这个技能操作成功!</p>";
                } else {
                    $title = "技能删除失败！";
                    $content = "<p>删除<span style='font-size:16px;' class='text-red'> \"{$skil->name}\" </span>这个技能操作失败!</p>";
                } 
                return array("title" => $title, 'body' => $content , 'reload' => $reload);
            } else {
                $title = "删除一个技能";
                $skil = $this->get_skill_info($key);
                $content = "<p>确认删除<span style='font-size:16px;' class='text-red'> \"{$skil->name}\" </span>这个技能？</p>";

                $button = <<<html
	<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
	<button type="button" onclick="del_skill('{$clas}','{$skil->id}','true')" class="btn btn-danger">确认删除</button>
html;
            } 
        } 
        return array('title' => $title, 'body' => $content, 'btn' => $button);
    } 

    function edit_skill($key, $true = 'false', $data = '') { // 编辑技能
        $alert_open = alert_open;
        if ($key != 0) {
            if ($true == "true") {
                if ($this->attribute->edit_record("jineng", $data)) {
                    $title = "技能修改成功！";
                    $skil = $this->get_skill_info($key);
                    $content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$skil->name}\" </span>这个技能操作成功!</p>";
                    $arry = array('title' => $title, 'body' => $content);
                } else {
                    $title = "修改技能失败！";
                    $skil = $this->get_skill_info($key);
                    $content = "<p>修改<span style='font-size:16px;' class='text-red'> \"{$skil->name}\" </span>这个技能的操作失败了!</p>";
                    $button = "0";
                } 
            } else {
                $title = "编辑一个技能";
                $skil = $this->get_skill_info($key);
                $default = array('hurt_attr', 'deplete_attr');
                $content = $this->attribute->get_attribute_edit("jineng", $key, array(), $default, $this->load_skill_event($skil));
                $button = <<<html
	<button type="button" {$alert_open} onclick="edit_skill('{$skil->id }','true')" class="btn btn-primary">保存修改</button>
html;
                $arry = array('title' => $title, 'body' => $content, 'btn' => $button);
            } 
            return $arry;
        } 
    } 

    function new_skill($value = null) { // 新建技能
        $alert_open = alert_open;
        if (isset($value['data'])) {
            $id = $this->attribute->add_record("jineng", $value['data']);
            $skill = $this->get_skill_info($id);
            $title = "新建一个技能";
            $repage = true;
            $content = "新建技能<span style='font-size:16px;' class='text-red'>【{$skill->name}】</span>的操作已完成！";
        } else {
            $title = "新建一个技能";
            $content = $this->attribute->get_attribute_new("jineng", array(), array(), $this->load_skill_event());

            $button = <<<html
	<button type="button" {$alert_open} onclick="new_skill('true')" class="btn btn-primary">确认创建</button>
html;
        } 
        return array('title' => $title, 'body' => $content, 'btn' => $button, 'repage' => $repage);
    } 

    function load_skill_event($obj = null) { // 加载技能事件定义
        global $sys;
        global $equip;
        global $attribute;
        $alert_open = alert_open;
        $weapon = json_decode($sys->get_system_config("system", "weapon_class"));
        if (intval($obj->equip_id) > 0) {
            $equip_info = $equip->get_equip_info($obj->equip_id);
        } 
		
	
		$option .= "<option value='0'";
		if(intval($obj->equip_type)==0){
			$option .= "selected";
		}
		$option .= ">任意</option>";
        foreach ($weapon as $weobj) {
            if (is_object($weobj)) {
                if (intval($equip_info->tool) == intval($weobj->id)) {
                    $weapon_name = $weobj->name ;
                } 
                if (intval($obj->equip_type) == intval($weobj->id)) {
                    $option .= "<option value='{$weobj->id}' selected>{$weobj->name}</option>";
                } else {
                    $option .= "<option value='{$weobj->id}'>{$weobj->name}</option>";
                } 
            } 
        } 

        if (intval($obj->equip_id) > 0) {
            $equip_title = "<span class='text-red'>{$weapon_name}</span>/{$equip_info->name}({$equip_info->id})";
        } else {
            $equip_title = "无特定装备";
        } 
        $deplete_attr = $obj->deplete_attr;
        $html = <<<html
	<div class='form-group'>
		<label class='col-sm-2'>消耗目标：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="deplete_attr"> 
html;
        $Obj_cfg = $attribute->get_table_config("game1");
        foreach($Obj_cfg as $obj) {
            $typ = $obj->column_type;
            switch ($typ) {
                case "int(11)":
                    $hs = json_decode($obj->column_comment);
                    if ($hs->consume == "t") {
                        $html .= "<option value='{$obj->column_name}'";
                        if ($deplete_attr == $obj->column_name) {
                            $html .= "selected";
                        } ;
                        $html .= ">" . urldecode($hs->Notes) . "({$obj->column_name})</option>";
                    } 
                    break;
            } 
        } 
		 $hurt_attr = $obj->hurt_attr;
        $html .= <<<html
		</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>伤害目标：</label>
		<div class='col-md-6 col-sm-10'>
		<select class="form-control" name="hurt_attr"> 
html;
        $Obj_cfg = $attribute->get_table_config("game1");
        foreach($Obj_cfg as $obj) {
            $typ = $obj->column_type;
            switch ($typ) {
                case "int(11)":
                    $hs = json_decode($obj->column_comment);
                    if ($hs->consume == "t") {
                        $html .= "<option value='{$obj->column_name}'";
                        if ($hurt_attr == $obj->column_name) {
                            $html .= "selected";
                        } ;
                        $html .= ">" . urldecode($hs->Notes) . "({$obj->column_name})</option>";
                    } 
                    break;
            } 
        } 
        $html .= <<<html
		</select>
		</div>
	</div>
	<div class='form-group'>
		<label for='equip_type' class='col-sm-2'>使用兵器类型：</label>
		<div class='col-md-6 col-sm-10'>
			<select class='form-control' id='equip_type' name='equip_type'>{$option}</select>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>使用特定装备:</label>
		<div class='col-md-6 col-sm-10'>
			<div class="row">
			<div class='col-xs-5'><h4>{$equip_title}</h4></div>
			<div class='col-xs-5'>
html;
        if (intval($obj->equip_id) == 0) {
            $html .= "<button type=\"button\" onclick=\"deploy_equip('add','skill','{$obj->id}','weapon','')\" {$alert_open} class=\"btn btn-primary\"> 添加</button>";
        } else {
            $html .= "<button type=\"button\" onclick=\"deploy_equip('edit','skill','{$obj->id}','weapon','','{$obj->equip_id}')\" {$alert_open} class=\"btn btn-info\"> 修改</button>
		<button type=\"button\" onclick=\"deploy_equip('del','skill','{$obj->id}','weapon','','{$obj->equip_id}')\" {$alert_open} class=\"btn  btn-danger\"> 删除</button>";
        } 
        $html .= "</div>
		</div>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>升级事件：</label>
		<div class='col-md-6 col-sm-10'>
			<div class='row'>";

        if ($obj->event_uplvl == "") {
            $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=skill&key={$obj->id}&clas=uplvl' >添加事件</a></div>";
        } else {
            $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=skill&key={$obj->id}&clas=uplvl'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open}  onClick=\"del_event('skill','uplvl','{$obj->id}','{$obj->event_upgrade}')\">删除事件</button></div>";
        } 
        $html .= "
			</div>
		</div>
	</div>
	<div class='form-group'>
		<label class='col-sm-2'>使用事件：</label>
		<div class='col-md-6 col-sm-10'>
			<div class='row'>";
        if ($obj->event_use == "") {
            $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=add&path=skill&key={$obj->id}&clas=use' >添加事件</a></div>";
        } else {
            $html .= "<div class='col-xs-5'><a class='btn btn-primary btn-block' href='event.php?type=edit&path=skill&key={$obj->id}&clas=use'>修改事件</a></div>
	<div class='col-xs-4'><button type='button' class='btn btn-danger btn-block' {$alert_open} onClick=\"del_event('skill','use','{$obj->id}','{$obj->event_apply}')\">删除事件</button></div>";
        } 
        $html .= <<<html
			</div>
		</div>
	</div>
html;
        return $html;
    } 

    function skill_type_load($type, $m = 1, $n = 20) { // 加载特定类型技能
        $list = $this->get_skill_all($type, $m, $n);
        $i = $n * ($m-1);
        if (isset($list)) {
            foreach($list->data as $obj) {
                ++$i;
                $html .= <<<html
	<tr>
      <td>{$i} </td>
	  <td>{$obj->name}({$obj->id})</td>
      <td>
	  <a class="btn btn-primary" href="skill.php?id={$obj->id}">修改</a>
	  <button class="btn btn-danger " type="button" data-position="100" data-toggle="modal" data-target="#ajax-alert" onclick="del_skill('{$type}','{$obj->id}')">删除</button>
	  </td>
    </tr>
html;
            } 
        } 
        return array('html' => $html, 'recTotal' => $list->num, 'recPerPage' => $n);
    } 

    function get_skill_all($type, $page = 1, $count = 20) { // 获取指定类型的所有技能
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $type = $this->get_skill_type($type);
        $obj = (object)[];
        $sql = "SELECT * FROM `jineng` WHERE `occasion` = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($type[0]));
        $obj->num = $stmt->rowCount();
        if ($obj->num > 0) {
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($type[0]));
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_skill_info($id) { // 根据ID获取一条技能数据
        $sql = "SELECT * FROM `jineng` WHERE id = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $skill = $stmt->fetch(\PDO::FETCH_OBJ);
        return $skill;
    } 

    function get_player_skill_info($id, $sid) { // 根据ID获取一条玩家技能数据
        $sql = "SELECT * FROM `playerjineng` WHERE id = ? and sid =? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id, $sid));
        return $stmt->fetch(\PDO::FETCH_OBJ);
    } 
	
    function get_player_skill_initial($id, $sid) { // 根据系统技能ID获取一条玩家技能数据
        $sql = "SELECT * FROM `playerjineng` WHERE initial_id = ? and sid =? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id, $sid));
        $skill = $stmt->fetch(\PDO::FETCH_OBJ);
        return $skill;
    } 

    function get_skill_name($name, $m = 1, $n = 20) { // 根据名称获取一条技能数据
        $m = intval($m);
        $n = intval($n);
		$m = ($m-1) * $n;
        $sql = "SELECT * FROM `jineng` WHERE name like ? limit $m,$n;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array('%' . $name . '%'));
        $skill = $stmt->fetchALL(\PDO::FETCH_OBJ);
        return $skill;
    } 

    function get_skill_type($val) {
        switch ($val) {
            case "battle":
                $clas = array("1", "战斗");
                break;
            case "auxiliary":
                $clas = array("0", "辅助");
                break;
        } 
        return $clas;
    } 

    function set_skill_field($id, $type, $value = null) { // 更新技能单个字段
        switch ($type) {
            case 'use':
            case 'event_use':
                $field = "event_use";
                break;
            case 'uplvl':
            case 'event_uplvl':
                $field = "event_uplvl";
                break;
            case 'equip_id':
                $field = "equip_id";
                break;
        } 
        if ($value == null) {
            $sql = "UPDATE `jineng` SET {$field} = null WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($id));
        } else {
            $sql = "UPDATE `jineng` SET {$field} = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($value, $id));
        } 
        return $ret;
    } 

    function get_player_skill($sid) {
        $sql = "select * from playerjineng WHERE sid = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        return $stmt->fetchALL(\PDO::FETCH_OBJ);
    } 

    function add_player_skill($jnid, $sid) { //玩家学习技能
        $jineng = $this->get_skill_info($jnid);
        $ret = $this->get_player_skill_initial($jineng->id, $sid);
        if ($ret) {
			//echo "不能重复学习技能：{$jineng->name}！<br>";
			$ret = false;
        } else {
            $sql = "INSERT INTO `playerjineng`(`initial_id`, `lvl`, `name`, `sid`) VALUES ( ?, ?, ?, ?);";
			$stmt = $this->dblj->prepare($sql);
			$ret = $stmt->execute(array($jineng->id,1,$jineng->name,$sid));
			if($ret){echo "学习技能：{$jineng->name}成功！<br>";};
        } 
		return $ret;
    } 

    function del_player_skill_initial($jnid,$sid) { //系统废除玩家技能
        $ret = $this->get_player_skill_initial($jnid, $sid);
		$jineng = $this->get_skill_info($jnid);
        if ($ret) {
            if (!isset($ret->Grade)) {
				$sql = "DELETE from playerjineng WHERE id= ? and sid = ? and Grade is null;";
				$stmt = $this->dblj->prepare($sql);
				$ret = $stmt->execute(array($ret->id,$sid));
				if($ret){echo "废除技能：{$jineng->name}成功！<br>";};
               return true;
            } else {
                return false;
            } 
        } else {
            return false;
        } 
    } 
	
    function del_player_skill($jnid,$sid) { //玩家废除技能
        $ret = $this->get_player_skill_info($jnid, $sid);
		$skill_name = $ret->name;
        if ($ret) {
            if (!isset($ret->Grade)) {
				$sql = "DELETE from playerjineng WHERE id= ? and sid = ? ;";
				$stmt = $this->dblj->prepare($sql);
				$ret = $stmt->execute(array($ret->id,$sid));
				if($ret){echo "废除技能：{$skill_name}成功！<br>";};
               return true;
            } else {
                return false;
            } 
        } else {
            return false;
        } 
    } 

	function set_player_default($jnid,$sid){//玩家技能设置为默认
		$ret = $this->del_player_default($sid);
		if($ret){
			$sql = "UPDATE `playerjineng` SET `default` = 1  WHERE `id` = ? and `sid` = ?;";
			$stmt = $this->dblj->prepare($sql);
			$ret = $stmt->execute(array($jnid,$sid));
		}
		return $ret;
	}

	function del_player_default($sid){//玩家技能取消默认
		$sql = "UPDATE `playerjineng` SET `default` = NULL WHERE `sid` = ?;";
		$stmt = $this->dblj->prepare($sql);
		$ret = $stmt->execute(array($sid));
		return $ret;
	}
	} 

?>