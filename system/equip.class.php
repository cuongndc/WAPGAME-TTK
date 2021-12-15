<?php
//游戏道具管理类
namespace game_system;


class equip{

    public $dblj;
    public $sid;
    public $uid;
	public $sys;
    public $map;

    function __construct(){
		global $dblj;
		global $sys;
		global $map;
        $this->dblj =  $dblj;
		$this->sys = $sys;
		$this->map = $map;
        if (!isset($_SESSION['sid'] )) {
            return;
        }
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
	}

	function equip_type_load($type,$page=1,$recPerPage=20){//加载特定装备
		$clas = $this->get_equip_type($type);
		$alert = alert_open;
		$list = $this->get_equip_all($clas[0],0,"",$page,$recPerPage);
		if($clas[0] == "weapon" ){
			$weapon = json_decode($this->sys->get_system_config("system","weapon_class"));
		}else{
			$weapon = json_decode($this->sys->get_system_config("system","equip_class"));
		}
	if(is_object($list)){
	foreach($list->data as $obj){
		$qyname = $this->map->get_qy_name($obj->qy);
		if($qyname == "()"){$qyname = "" ;}
		$subclass = "";
		$tool_id = $obj->tool;
		if(intval($tool_id) != 0 ){
			$name = $weapon->$tool_id->name;
			$subclass = "{$name}({$tool_id})";
		};
	++$i;
$html .="
	<tr>
      <td>{$i} .{$obj->name}({$obj->id})</td>";
	  if($clas[0] == "weapon" || $clas[0] == "equip" ){
		$html .= "<td>{$subclass}</td>";
	  }
$html .=<<<html
      <td>{$qyname}</td>
      <td>
	  <a class="btn btn-primary" href="equip.php?com=edit&id={$obj->id}">修改</a>
	  <button class="btn btn-danger" type="button" {$alert} onclick="del_equip('{$obj->id}')">删除</button>
	  </td>
    </tr>
html;
	  }
	}
	return array('list'=>$html,'recPerPage'=>$recPerPage,'recTotal'=>$list->num);
}
		
	function get_equip_all($type,$qyid=0,$name="",$page=1,$recPerPage=20){//获取指定类型的所有物品
		$page = intval($page);
		$recPerPage =intval($recPerPage);
		$page =($page-1)*$recPerPage;
		$obj = (object)[]; 
		if($type == "all"){
			$sql="SELECT * FROM `daoju`";
			$stmt = $this->dblj->prepare($sql);
			$stmt->execute();
		}else{
			$type = $this->get_equip_type($type);
			if($qyid!=0){
				$sql= "SELECT * FROM `daoju` WHERE `type` = 'equip' AND clas = ? AND qy = ? ";
				$stmt = $this->dblj->prepare($sql);
				$stmt->execute(array($type[0],$qyid));
			}else{
				$sql="SELECT * FROM `daoju` WHERE `type` = 'equip' AND clas = ? ";
				$stmt = $this->dblj->prepare($sql);
				$stmt->execute(array($type[0]));
			}
		}
		$obj->num = $stmt->rowCount();
		if($recPerPage !=0 ){
		  $sql .= " limit {$page},{$recPerPage};";

          $stmt = $this->dblj->prepare($sql);
		  if(intval($qyid)!=0){
			$stmt->execute(array($type[0],$qyid));
		  }else{
			$stmt->execute(array($type[0]));
		  }
          $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);	
		}
        return $obj;
	}
	
	function get_equip_info($id){//根据ID获取一条物品数据
		$sql = "SELECT * FROM `daoju` WHERE id = ? and type = 'equip';";
		$stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
		$equip = $stmt->fetch(\PDO::FETCH_OBJ);
        return $equip;
	}
	
	function get_equip_name($name,$m=0,$n=20){//根据装备名模糊查找数据
	  $m = intval($m);
	  $n = intval($n);
		$sql = "SELECT * FROM `daoju` WHERE type = 'equip' and name like ? limit $m,$n;";
		$stmt = $this->dblj->prepare($sql);
        $stmt->execute(array('%'.$name .'%'));
		$equip = $stmt->fetchALL(\PDO::FETCH_OBJ);
        return $equip;
	}

	function get_equip_name_tool($name,$type, $tool = 0,$m = 0,$n = 20){//加载指定位置的装备
		$m = intval($m);
	    $n = intval($n);
		if($type == ""){return $this->get_equip_name($name,$m,$n);}
		switch($type){
			case 'weapon':
				$type_val = 'weapon';
			break;
			case 'equip':
				$type_val = 'equip';
			break;
			case 'weaponinlay':
				$type_val = 'weaponinlay';
			break;
			case 'equipinlay':
				$type_val = 'equipinlay';
			break;
		}

		if(intval($tool)==0){
			$sql = "SELECT * FROM `daoju` WHERE type = 'equip' and clas = ? and name like ? limit $m,$n;";
			$stmt = $this->dblj->prepare($sql);
			$stmt->execute(array($type_val,'%'.$name .'%'));
			$equip = $stmt->fetchALL(\PDO::FETCH_OBJ);
  		}else{
			$sql = "SELECT * FROM `daoju` WHERE type = 'equip' and clas = ? and tool = ? and name like ? limit $m,$n;";
			$stmt = $this->dblj->prepare($sql);
			$stmt->execute(array($type_val,$tool,'%'.$name .'%'));
			$equip = $stmt->fetchALL(\PDO::FETCH_OBJ);
		}
        return $equip;
	}
	
	function load_event_list($id){//加载物品事件列表
		$obj_info = $this->get_equip_info($id);
		if($obj_info->clas == "equipinlay"  || $obj_info->clas == "weaponinlay" ){
			$wear_title = "镶物镶入";
			$undress_title = "镶物取下";
		}else{
			$wear_title = "装备穿上";
			$undress_title = "装备卸下";
		}
		$path = "equip";
		$id = $obj_info->id;
		$alert_open =alert_open;
		$link = "path={$path}&key={$id}";
			$html = <<<html
			<h3>编辑装备"{$obj_info->name}"的事件：</h3>
<table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-npc-list">
  <tr><td>1 .创建事件</td><td>
html;
if($obj_info->event_create){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=create\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','create','{$obj_info->id}','{$obj_info->event_create}')">删除</button>
html;
	
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=create\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>2 .查看事件</td><td>
";
if($obj_info->event_watch){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&path=map&key={$obj_info->id}&clas=watch\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','watch','{$obj_info->id}','{$obj_info->event_watch}')">删除</button>
html;
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=watch\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>3 .使用事件</td><td>
";
if($obj_info->event_use){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=use\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','use','{$obj_info->id}','{$obj_info->event_create}')">删除</button>
html;
	
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=use\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>4 .{$wear_title}事件</td><td>
";
if($obj_info->event_wear){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=wear\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','wear','{$obj_info->id}','{$obj_info->event_create}')">删除</button>
html;
	
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=wear\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>5 .{$undress_title}事件</td><td>
";
if($obj_info->event_undress){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&path=map&key={$obj_info->id}&clas=undress\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','undress','{$obj_info->id}','{$obj_info->event_watch}')">删除</button>
html;
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=undress\">设置事件</a>";
}

$html .= "</td></tr>
<tr><td>6 .存储数据事件</td><td>
";
if($obj_info->event_save){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=save\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','save','{$obj_info->id}','{$obj_info->event_leave}')">删除</button>
html;
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=save\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>7.导出数据事件</td><td>
";
if($obj_info->event_backups){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=backups\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','backups','{$obj_info->id}','{$obj_info->event_leave}')">删除</button>
html;
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=backups\">设置事件</a>";
}
$html .= "</td></tr>
<tr><td>8 .分钟定时事件</td><td>
";
if($obj_info->event_timing){
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=timing\">修改事件</a> ";
	$html .=<<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','timing','{$obj_info->id}','{$obj_info->event_timing}')">删除</button>
html;
}else{
	$html .="<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=timing\">设置事件</a>";
}
$html .= "</td></tr></tbody>
</table>
";
return $html;
	}	
	
	function get_equip_type($val){//读取物品分类数据
	  switch($val){
		case "#weapon":
		case "weapon":
		  $clas = array("weapon","兵器");
		break;
		case "#equip":
		case "equip":
		  $clas = array("equip","防具");
		break;
		case "#weaponinlay":
		case "weaponinlay":
		  $clas = array("weaponinlay","兵器镶物");
		break;
		case "#equipinlay":
		case "equipinlay":
		  $clas = array("equipinlay","防具镶物");
		break;
	  }
	return $clas;	
	}

	function load_weapon_class(){//读取武器列表
		$weapon_class =$this->sys->get_system_config("system","weapon_class");
		$data =json_decode($weapon_class);
		$value = json_decode('{}');
		foreach ($data as $val){
			$i++;
			if(is_object($val)){
				$bo = json_decode('{}');
				$bo->id = $val->id;
				$bo->name = $val->name;
				$value->$i = $bo;
			}
		}
		return $value;
	}

	function load_equip_class(){//读取装备列表
		$equip_class =$this->sys->get_system_config("system","equip_class");
		$data =json_decode($equip_class);
		$value = json_decode('{}');
		foreach ($data as $val){
			$i++;
			if(is_object($val)){
				$bo = json_decode('{}');
				$bo->id = $val->id;
				$bo->name = $val->name;
				$value->$i = $bo;
			}
		}
		return $value;
	}

	function read_equip_list($obj_info,$equip_class,$equip_val){//加载装备列表
		$alert_open = alert_open;
		$equip = json_decode($equip_class);
		$weapon = $equip->weapon;
			$equip = $equip->equip;
			//var_dump($weapon,$equip);
			$equip_val = json_decode($equip_val);
			$weapon_val = $equip_val->weapon;
			$equip_val = $equip_val->equip;
			//var_dump($weapon_val,$equip_val);
			foreach($weapon as $val){
				if(is_object($val)){
					$val_id = $val->id;
					$weapon_id = $weapon_val->$val_id->id;
					//var_dump($val_id ."===".$weapon_id);
					$btn = <<<html
	<button type="button" onclick="deploy_equip('add','npc','{$obj_info->id}','weapon','{$val->id}')" {$alert_open} class="btn btn-primary"> 添加</button>
html;
					if($weapon_id){
						$btn = <<<html
	<button type="button" onclick="deploy_equip('edit','npc','{$obj_info->id}','weapon','{$val->id}','{$weapon_id}')" {$alert_open} class="btn btn-info"> 修改</button>
			<button type="button" onclick="deploy_equip('del','npc','{$obj_info->id}','weapon','{$val->id}','{$weapon_id}')" {$alert_open} class="btn  btn-danger"> 删除</button>
html;
					}
					$skill_list .="<tr><td>武器：{$val->name}</td><td>{$weapon_id}</td><td>{$weapon_val->$val_id->name}</td><td>{$btn}</td></tr>";
				}
			}
			foreach($equip as $val){
				if(is_object($val)){
					$val_id = $val->id;
					$equip_id = $equip_val->$val_id->id;
						$btn = <<<html
	<button type="button" onclick="deploy_equip('add','npc','{$obj_info->id}','equip','{$val->id}')" {$alert_open} class="btn btn-primary"> 添加</button>
html;
					if($equip_id){
						$btn = <<<html
			<button type="button" onclick="deploy_equip('edit','npc','{$obj_info->id}','equip','{$val->id}','{$equip_id}')" {$alert_open} class="btn btn-info"> 修改</button>

			<button type="button" onclick="deploy_equip('del','npc','{$obj_info->id}','equip','{$val->id}','{$equip_id}')" {$alert_open} class="btn  btn-danger"> 删除</button>
html;
					}
					$skill_list .="<tr><td>装备：{$val->name}</td><td>{$equip_val->$val_id->id}</td><td>{$equip_val->$val_id->name}</td><td>{$btn}</td></tr>";
				}
			}
			$thead = "<tr><th>类型(位置)</th><th>装备ID</th><th>装备名</th><th>操作</th></tr>";
			$body = <<<html
<table  class="table"> 
<tr><td><b>编辑NPC"{$obj_info->name}"的装备设置：</b></td><td style="text-align:right">
</td></tr>
</table>
<div id="window">
<table  class="table table-condensed"> 
	<thead>
		{$thead}
	</thead>
	<tbody>
{$skill_list}
	</tbody>
</table>
<div>
html;
	return array('body' =>$body);
	}
	
	function add_equip_Packing_data($type,$equip_clas,$data,$new_data){//打包新建或修改后需要保存到步骤的数据
	$temp = json_decode($data);
	if(!is_object($temp)){$temp = json_decode('{}');};
	$weapon = $temp->weapon;
	$equip = $temp->equip;
		$key_id = $new_data['id'];
		$new_name = $new_data['name'];
		$new_val = $new_data['val'];
		$new_num = $new_data['num'];
		$new_mark = $new_data['mark'];
		$new_size = $new_data['size'];
		$new_category = $new_data['category'];
	switch($type){
		case 'weapon':
		if(is_object($weapon)){//测试是否为原定义属性重新修改
		  foreach($weapon as $id => $obj){
			if(isset($equip_clas)){
			   if($obj->id == $equip_clas){
				$weapon->$id->id = $key_id ;
				$weapon->$id->val = $new_val;
				$weapon->$id->num = $new_num ;
				$curt = true;
				break;
			  }
			 }
			
			if(isset($new_mark)){
			   if($obj->mark == $new_mark ){
				$weapon->$id->name = $new_name;
				$weapon->$id->size = $new_size;
				$weapon->$id->category = $new_category;
				$curt = true;
				break;
			  }
			}
			if($obj->name == $new_name && $obj->id == $equip_clas && $obj->mark == $new_mark ){
				$weapon->$id->val = $new_val;
				$weapon->$id->num = $new_num;
				$weapon->$id->id = $key_id;
				$curt = true;
				break;
			}
		  }
		}

		if(!$curt){//非已定义属性修改，则分配新属性位置
		if(!is_object($weapon)){$weapon = json_decode('{}');}
		 $id =  $equip_clas;
		 $total = $weapon->total;
		 $total++;
		 $weapon->id = $id ;
		 $weapon->total = $total ;
		 $weapon->$id = json_decode('{}');
		 if(isset($key_id)){$weapon->$id->id = $key_id;}
		 $weapon->$id->name = $new_name;
		 $weapon->$id->val =$new_val;
		 $weapon->$id->clas = $equip_clas;
		 $weapon->$id->num =$new_num;
		 $weapon->$id->mark =$new_mark;
		 $weapon->$id->size = $new_size;
		 $weapon->$id->category = $new_category;
		}
		break;
		case 'equip':
		if(is_object($equip)){//测试是否为原定义属性重新修改
		  foreach($equip as $id => $obj){
			if(isset($equid_clas)){
			   if($obj->id == $equid_clas ){
				$equip->$id->id = $key_id;
				$equip->$id->val = $new_val;
				$equip->$id->clas = $equip_clas;
				$equip->$id->num = $new_num ;
				$curt = true;
				break;
			  }
			 }
			if(isset($new_mark)){
			   if($obj->mark == $new_mark ){
				$equip->$id->name = $new_name;
				$equip->$id->size = $new_size;
				$equip->$id->clas = $equip_clas;
				$equip->$id->category = $new_category;
				$curt = true;
				break;
			  }
			}
			if($obj->name == $new_name && $obj->id == $equid_clas && $obj->mark == $new_mark ){
				$equip->$id->val = $new_val;
				$equip->$id->num = $new_num ;
				$equip->$id->id = $key_id;
				$curt = true;
				break;
			}
		  }
		}

		if(!$curt){//非已定义属性修改，则分配新属性位置
		if(!is_object($equip)){$equip = json_decode('{}');}
		 $id = $equip_clas;
		 $total = $equip->total;
		 $total++;
		 $equip->id = $id;
		 $equip->total = $total ;
		 $equip->$id=json_decode('{}');
		 if(isset($key_id)){$equip->$id->id = $key_id;}
		 $equip->$id->name = $new_name;
		 $equip->$id->val =$new_val;
		 $equip->$id->num =$new_num;
		 $equip->$id->mark =$new_mark;
		 $equip->$id->clas = $equip_clas;
		 $equip->$id->size = $new_size;
		 $equip->$id->category = $new_category;
		}
		break;
	}
	$temp->weapon = $weapon;
	$temp->equip = $equip;
	return json_encode($temp);
	}

	function del_equip_Packing_data($type,$data,$vid){//打包删除后需要保存到步骤的数据
	$temp = json_decode($data);
	if(!is_object($temp)){$temp = json_decode('{}');};
	$weapon = $temp->weapon;
	$equip = $temp->equip;
	switch($type){
		case 'weapon':
			unset($weapon->$vid);
		break;
		case 'equip':
			unset($equip->$vid);
		break;
	}
	$temp->weapon = $weapon;
	$temp->equip = $equip;
	return json_encode($temp);
	}

	function get_player_equip_list($sid,$yeshu = 1,$num = 20){//获取玩家装备列表分页
        $sql = "select * from playerzhuangbei  WHERE sid = ? ORDER BY zbid DESC LIMIT ?,?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($this->sid ,($yeshu - 1) * $num , $num));
        $exeres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $exeres;
    }
	}
?>