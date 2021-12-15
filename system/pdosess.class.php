<?php
namespace sys;

class sess{

    function sessionMysqlOpen($savePath, $sessionName) {
        return true;
    }

    function sessionMysqlClose() {
        return true;
    }

    function sessionMysqlRead($sessionId) {
        try {
            $dblj = $this->get_Connection();

            $time = time();
            $sql = 'SELECT count(*) AS `count` FROM `session` WHERE skey = ? and expire > ?';
            $stmt = $dblj->prepare($sql);;
            $stmt->execute(array($sessionId, $time));
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($data['count'] == 0) {
                return '';
            }

            $sql = 'SELECT `data` FROM `session` WHERE `skey` = ? and `expire` > ?';
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array($sessionId, $time));
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $data['data'];
        } catch (\Exception $e) {
            return '';
        }

    }

    function sessionMysqlWrite($sessionId, $data) {
        try {
            $dblj = $this->get_Connection();
            $session_maxlifetime = get_cfg_var('session.gc_maxlifetime');
			if($data== "god")
			{
				$expire = time() + 1440*3 ;}
			else{
				$expire = time() + $session_maxlifetime;
			};
            $sql = 'INSERT INTO `session` (`skey`, `data`, `expire`) values (?, ?, ?) ON DUPLICATE KEY UPDATE data = ?, expire = ?';
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array($sessionId, $data, $expire, $data, $expire));

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return true;
    }

    function sessionMysqlDestroy($sessionId) {
        try {
            $dbh = $this->get_Connection();
            $sql = 'DELETE FROM `session` where skey = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array($sessionId));
            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    function sessionMysqlGc($lifetime) {
        try {
            $dbh = $this->get_Connection();
            $sql = 'DELETE FROM `session` WHERE expire < ?';
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(time()));
            $dbh = NULL;
            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    function sessionMysqlId() {
        if (filter_input(INPUT_GET, session_name()) == '' and
            filter_input(INPUT_COOKIE, session_name()) == '') {
            try {
                $dbh = $this->get_Connection();
                $stmt = $dbh->query('SELECT uuid() AS uuid');
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
                $data = str_replace('-', '', $data['uuid']);
                session_id($data);
                return TRUE;
            } catch (\Exception $ex) {
                return FALSE;
            }
        }
        return FALSE;
    }

    function startSession() {
        session_set_save_handler(
            array($this , 'sessionMysqlOpen'),
            array($this , 'sessionMysqlClose'),
            array($this , 'sessionMysqlRead'),
            array($this , 'sessionMysqlWrite'),
            array($this , 'sessionMysqlDestroy'),
            array($this , 'sessionMysqlGc'));
        register_shutdown_function( 'session_write_close');

        $this->sessionMysqlId();
        session_start();
    }

    function get_Connection(){
        $pdo_ = new pdo_();
        $dblj = $pdo_->dblj();
        return $dblj;
    }
}

class pdo_{

    function dblj(){
        $sqlname = 'saga';
        $sqlpass = 'auth_string';
        $dbhost = 'localhost';
        $dbport = 3306;
        $dbname = 'ttk';
        $dsn="mysql:host=$dbhost;port=$dbport;dbname=$dbname;";

        $APP_ROOT = dirname( dirname( __FILE__ )  ) . DIRECTORY_SEPARATOR;
        if(file_exists($APP_ROOT . 'config' . DIRECTORY_SEPARATOR . 'db.config.php')){
            require_once( $APP_ROOT . 'config' . DIRECTORY_SEPARATOR . 'db.config.php' );   
        }        

        if(isset($GLOBALS['config']['db']['db_host'])){

            $sqlname = $GLOBALS['config']['db']['db_user'];
            $sqlpass = $GLOBALS['config']['db']['db_password'];
            $dbhost = $GLOBALS['config']['db']['db_host'];
            $dbport = $GLOBALS['config']['db']['db_port'];
            $dbname = $GLOBALS['config']['db']['db_name'];

            $dsn="mysql:host=$dbhost;port=$dbport;dbname=$dbname;";
        }


        $dblj = new \PDO( $dsn , $sqlname , $sqlpass , array(\PDO::ATTR_PERSISTENT=>true) );

        $dblj->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
        $dblj->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $dblj->query("SET NAMES utf8mb4");;
        return $dblj;
    }
	

}

class attribute{//数据表操作控制类
    public $dblj;
    public $map;
	
    function __construct(){
		global $map;
		if(!$map){
			// 实例化地图控制
			$map = new \game_system\mid();
		}
        $pdo_ =  new \sys\pdo_();
        $this->dblj = $pdo_->dblj();
        $this->map = $map;
	}

	function get_table_config($table){//获取数据库表配置信息
        $sql = "SELECT column_name,column_comment,column_type FROM INFORMATION_SCHEMA.Columns WHERE table_name='$table' AND table_schema='".$GLOBALS['config']['db']['db_name']."'";
        $cxjg = $this->dblj->query($sql);
        $ret = $cxjg->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    }
			
	function get_attribute_list($table){//提取属性列表
	  $Obj_map_cfg = $this->get_table_config($table);
		foreach($Obj_map_cfg as $obj){
		  $typ=$obj->column_type;
		  switch($typ){
			case "int(4)":
			$typ="逻辑型";
			break;
			case "datetime":
			$typ="日期时间";
			break;
			case substr ($typ,0, 4)=="text":
			$typ="文本型";
			break;
			case substr ($typ,0, 7)=="varchar":
			$typ="长字符串";
			break;
			case "int(11)":
			$typ="整数型";
			break;
		  }
	$info = json_decode($obj->column_comment);
	$ks = $info->watch =="h"?"假":"真" ;
	if(isset($info->type)){//符合当前系统的数据编辑格式的内容
	if($info->Hide_editing != "h"){
		$text .="<tr><td>$obj->column_name</td><td>$typ</td><td>$ks</td><td>".urldecode($info->Notes)."</td><td>";
		$text .= '<input type="button" value="修改" class="btn btn-primary" '. alert_open .' onclick="Edit(\'Edit_read\',\''.$obj->column_name .'\')"> ';
			
	if($info->type !="s"){
		$text .= '<input type="button" value="删除"  class="btn btn-danger" '. alert_open .' onclick="del(\''.urldecode($info->Notes) .'\',\''.$obj->column_name .'\')">';
	}
	$text .= "</td></tr>". PHP_EOL ."    ";
	}
	}else{//不符合当前系统的数据编辑格式的内容
	$alert_open = alert_open;
$text .= <<<html
	<tr><td>{$obj->column_name}</td><td>{$typ}</td><td>{$ks}</td><td>{$hs}</td><td>
	<input type="button" value="修改" class="btn btn-primary" {$alert_open} onclick="Edit('Edit_read','{$obj->column_name}')"> <input type="button" value="删除"  class="btn btn-danger" {$alert_open} onclick="del('{$hs}','{$obj->column_name}')"></td></tr>
html;
			}
		}
		return $text;
	}

	function get_hidden_attribute($table,$type){//取表默认隐藏字段名
	  if($type == "new"){
		switch($table){
			case "jineng":
				$hidden = array("id","event_use","event_uplvl");
			break;
			case "daoju":
				$hidden =  array("id");
			break;
			case "equip":
				$hidden =  array("id");
			break;
			case "mid":
				$hidden =  array("id");
			break;
			case "npc":
				$hidden =  array("id");
			break;
		}
	  }else{
		switch($table){
			case "jineng":
				$hidden =  array("event_use","event_uplvl");
			break;
			case "daoju":
				$hidden =  array("pr");
			break;
			case "equip":
				$hidden =  array("pr");
			break;
			case "mid":
				$hidden =  array("npc");
			break;
			case "npc":
				$hidden =  array("open");
			break;
		}
	  }
	return $hidden ;
	}

	function get_attribute_new($table,$read,$default,$attach=null){//新建表数据时的表单填充数据
		//var_dump(is_array($default),$default);
		$html .= '<form class="form-horizontal" id="add"> 
				<input type="hidden" name="add_record" value="true">';
		$Obj_table_cfg = $this->get_table_config($table);
		$hidden = $this->get_hidden_attribute($table,"new");
		foreach($Obj_table_cfg as $obj){
			$field_name = $obj->column_name ;
		  if(is_array($default)){
			if(array_key_exists($field_name,$default)){ $val = $default[$field_name];}else{$val ='';};
		  }
			$skip = in_array($field_name,$hidden);
			$Notes  = json_decode($obj->column_comment);
			$hide = $Notes->watch;
			$edit = $Notes->edit;
			$Hide_editing =  $Notes->Hide_editing;
			$name = urldecode($Notes->Notes);
			$type = $obj->column_type;
			if($Hide_editing =="h" ){
				$skip = true;
			}
		if(!$skip){
			switch($Notes->type){
				case 's':
				case 'm':
					switch($type){
						case 'int(4)':
							$list ="<select class='form-control' id='{$field_name}' name='{$field_name}'>
								<option value='1'>是</option><option value='0'>否</option></select>";
						break;
						case 'text':
							$list ="<textarea class='form-control' id='{$field_name}' name='{$field_name}'>{$val}</textarea>";
						break;
						case 'int(11)':
							$list =  "<input type='number' name='{$field_name}' id='{$field_name}' class='form-control' placeholder='{$field_name}' value='{$val}'>";
						break;
						default:
							$list =  "<input type='text' name='{$field_name}' id='{$field_name}' class='form-control' placeholder='{$field_name}' value='{$val}'>";
					}
					if(G_trimall($name)!=""){
							$html .= "
							<div class='form-group'>
							<label for='{$field_name}' class='col-sm-2'>{$name}</label>
							<div class='col-md-6 col-sm-10'>{$list}</div>
							</div>";
					}
				break;
				case 'e':
					if($field_name == "id"){$readonly = "readonly";$inputtypr = 'hidden'; }
						$html .=  "<input type='{$inputtypr}' name='{$field_name}' class='form-control' value='{$val}' {$readonly}>";
						$inputtypr = 'text';
						$readonly ="";
				break;
				case 'h':
					//var_dump($skip,$name,$see,$hide,$type);
					$readonly = "readonly";
					$inputtypr = 'hidden';
					$html .=  "<input type='{$inputtypr}' name='{$field_name}' class='form-control' value='{$val}' {$readonly}>";
					$inputtypr = 'text';
					$readonly ="";
				break;
			}
		}
		}
	$html .=  "{$attach}</form>";
	return $html;
	}
	
	function get_attribute_edit($table,$id,$read,$default,$attach=null){//编辑条目数据时的表单填充数据
		$record = $this->get_record($table,$id);
		$html .= '<form class="form-horizontal" id="add"> 
		<input type="hidden" name="edit_record" value="true">';
		$Obj_table_cfg = $this->get_table_config($table);
		$hidden = $this->get_hidden_attribute($table,"edit");
		foreach($Obj_table_cfg as $obj){
			$field_name = $obj->column_name ;
			if(in_array($field_name ,$default)){ continue;}
			$skip = in_array($field_name ,$hidden);
			$Notes  = json_decode($obj->column_comment);
			$hide = $Notes->watch;
			$name = urldecode($Notes->Notes);
			$Hide_editing =  $Notes->Hide_editing;
			$type = $obj->column_type;
			$edit = $Notes->edit;
			if($Hide_editing =="h" ){
				$skip = true;
			}
			if(!$skip ){
			if($field_name == "id"){
					$html .=  "<input type='hidden' name='{$field_name}' class='form-control' value='{$record->$field_name}' readonly>";
					continue;
				}
			if(G_trimall($field_name)=="qy" && ($table =="mid" || $table =="npc")){
				$list ="<select class='form-control' id='{$field_name}' name='{$field_name}'>";
				$Obj_game_qy = $this->map->get_qy_all(1,0);
				foreach($Obj_game_qy->data as $obj){
					$selected = "";
					if( $obj->qyid == $record->qy ){$selected = "selected";};
					$list .="<option value='{$obj->qyid}' {$selected}>{$obj->qyname}({$obj->qyid})</option>";
				}
				$list .="</select>";
				$html .= "
					<div class='form-group'>
					<label for='{$field_name}' class='col-sm-2'>{$name}</label>
					<div class='col-md-6 col-sm-10'>{$list}</div>
					</div>
					";
				continue;
			}
				switch($Notes->type){
					case 's':
					case 'm':
					switch($type){
						case 'int(4)':
							$list ="<select class='form-control' id='{$field_name}' name='{$field_name}'>";
								$list .="<option value='1'";
								if($record->$field_name == 1){
									$list .=" selected"; 
								}
								$list .=">是</option><option value='0'";
								if($record->$field_name==0){
									$list .=" selected";
								}
								$list .=">否</option></select>";
						break;
						case 'text':
							$list ="<textarea class='form-control' id='{$field_name}' name='{$field_name}'>{$record->$field_name}</textarea>";
						break;
						case 'int(11)':
							$list =  "<input type='number' name='{$field_name}' id='{$field_name}' class='form-control' placeholder='{$field_name}' value='{$record->$field_name}'>";
						break;
						default:
						$list =  "<input type='text' name='{$field_name}' id='{$field_name}' class='form-control' value='{$record->$field_name}' placeholder='{$field_name}'>";
					}
					
					if(G_trimall($name)!=""){
						$html .= "
						<div class='form-group'>
						<label for='{$field_name}' class='col-sm-2'>{$name}</label>
						<div class='col-md-6 col-sm-10'>{$list}</div>
						</div>
						";
					}
					break;
					case 'e':
						if($field_name == "id"){$readonly = "readonly";$inputtypr = 'hidden'; }
						$html .=  "<input type='{$inputtypr}' name='{$field_name}' class='form-control' value='{$record->$field_name}' {$readonly}>";
						$inputtypr = 'text';
						$readonly ="";
					break;
					
				}
				/*
				if(substr ($obj->column_comment,2,1)=="r"){
							if($field_name=="mgtime"){
							$html .= '<input type="hidden" name="'.$field_name .'" class="form-control" value="datetime"> ';
							}else{
							$html .=  '<input type="hidden" name="'.$field_name .'" class="form-control" value="'.$record->$name . '"> ';
							}
						}
				*/
				}
			  }
	$html .=  "{$attach}</form>";
		return $html;
	}
	
	function set_attribute_gl($type,$table,$name,$clas,$ks,$bj,$ftype,$Notes,$consume,$max,$yname=""){//维护或新建属性
		$db = $GLOBALS['config']['db']['db_name'];
			$sql="SELECT COLUMN_NAME,column_comment,data_type FROM INFORMATION_SCHEMA.Columns WHERE table_name=? AND table_schema='$db' and COLUMN_NAME=?";
			$stmt = $this->dblj->prepare($sql);
			$stmt->execute(array($table , $yname ));
			$res = $stmt->fetch(\PDO::FETCH_OBJ);
			$Notes_initial = json_decode($res->column_comment);
			if(!is_object($Notes_initial)){$Notes_initial = (object)[];};
		//检查是否为系统默认属性，系统默认属性不允许修改属性名，属性类型
			$name_initial = $res->COLUMN_NAME;
		//计算新的备注字段信息
		if($Notes_initial->type != "s" ){ $Notes_initial->type = "m";}
		if($ks != "true" ){ $Notes_initial->watch = "h";}else{$Notes_initial->watch = "s";}
		if($bj != 1 ){ $Notes_initial->edit = "e";}else{ $Notes_initial->edit = "r";}
		if($consume != "true" ){ $Notes_initial->consume = "f";}else{ $Notes_initial->consume = "t";}
		if($max != "true" ){ $Notes_initial->max = "f";}else{ $Notes_initial->max = "t";}
		$Notes_initial->Notes =urlencode($Notes);
		$Field_type = $ftype;
		$ftype_initial = $res->data_type;
		$Notes =json_encode( $Notes_initial);
		switch ($ftype){//将字段属性翻译为数据库字段类型
			case "3"://字段为整数型
			$ftype="int(11)";
			break;
			case "10"://字段为文本型
			$ftype="text";
			break;
			case "7"://字段为逻辑型
			$ftype="int(4)";
			break;
			case "0"://字段为字符串
			$ftype="varchar(255)";
			break;
			case "8"://日期时间型
			$ftype="varchar(255)";
			break;
		}
		switch ($type){
		  case "Edit"://编辑字段信息
			if(G_isPermit($name) && G_isPermit($yname)){
				//非系统默认字段修改字段名，字段类型
			  if( $Notes_initial->type=="m"){
				if($name_initial != $name || $ftype_initial != $ftype){
					$sql="ALTER TABLE  $table CHANGE `$name_initial` `$name` $ftype;";
					$this->dblj->query($sql);
				}
			  }
			  //修改备注信息字段
			 if($Field_type == "0" || $Field_type =="10"){
				$sql="alter table $table MODIFY COLUMN `$name` $ftype CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '$Notes'";
				$this->dblj->query($sql);
				return array(true,"");
			 }else{
				 if($name=='id'){
					 $sql="alter table $table MODIFY COLUMN `$name` $ftype auto_increment COMMENT '$Notes'";
				 }else{
					 $sql="alter table $table MODIFY COLUMN `$name` $ftype COMMENT '$Notes'";
				 }
				
				$this->dblj->query($sql);
				 return array(true,"");
			 }
			}else{return array(false,'修改属性出错：属性名只能是"字母"+"数字"+"_"组成的2-25位字符串');}
		  break;
		  case "new"://添加字段信息
			if (G_isPermit($name)){
			if($this->isadd($table,$name)){
			$sql="alter table $table add `$name` $ftype COMMENT '$Notes'";
			$this->dblj->query($sql);
			  if($this->isadd_true($table,$name)){return array(true,"");}else{return  array(false,"创建新的属性失败！");}
			  }else{return array(false,"创建的属性已存在！");}
			}else{return array(false,'添加属性出错：属性名只能是"字母"+"数字"+"_"组成的2-25位字符串');}
		  break;
		  case "delete"://删除字段信息
			if($Notes_initial->type == "m"){
				if (G_isPermit($name)){
					if($this->isadd_true($table,$name)){
				$sql="ALTER TABLE $table DROP COLUMN `$name`";
				$this->dblj->query($sql);
						if(!$this->isadd_true($table,$name)){return array(true,"");
						}else{return array(false,"属性删除失败！");}
					}else{return array(false,"提交删除的属性不存在！");}
				}else{return array(false,$name.'删除属性出错：属性名只能是"字母"+"数字"+"_"组成的2-25位字符串');}
			}else{return array(false,"系统默认属性不可删除！");}
		  break;			
		}
    }

	function get_record($table,$id){  //从指定数据表提取一条记录
		switch($table){
			case "jineng":
			  $table = "jineng";
			break;
			case "mid":
			  $table = "mid";
			break;
		}
		$sql = "SELECT * FROM {$table} WHERE id = ? ;";
		$stmt = $this->dblj->prepare($sql);
		$stmt->execute(array($id));
		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	function add_record($table,$data){//向指定数据表添加一条记录
		switch($table){
			case "jineng":
			  $table = "jineng";
			break;
			case "equip":
			  $table = "equip";
			break;
			case "mid":
			  $table = "mid";
			break;
		}
		$value =array();
		$arry = json_decode($data,true);
		//var_dump($arry);
		foreach($arry as $k=>$v){ 
			if(G_isPermit($k) && $k !="mid" && $k !="add_record" & $v !=""){
				$field .=",`".$k."`";
				$val .=",?";
				if($v=="datetime"){$v=date("Y-m-d H:i",time());}
				array_push($value,$v);
			}
		}
		$field = substr($field,1);
		$val = substr($val,1);
		//var_dump('<br>',$field,'<br>',$val,'<br>',$value);
		$sql = "INSERT INTO `{$table}` ({$field}) VALUES ({$val});";
		//var_dump($sql);
		$stmt = $this->dblj->prepare($sql);
		$stmt->execute($value);
		$num = $stmt->rowCount();
	  if( $num == 1 ){
		$id = $this->dblj->lastInsertId();
        return $id;
	  }
	  return -1;
	}

	function del_record($table,$id){  //从指定数据表删除一条记录
	  switch($table){
			case "jineng":
			  $table = "jineng";
			  $allow = true;
			break;
			case "daoju":
			  $table = "daoju";
			  $allow = true;
			break;
			case "equip":
			  $table = "equip";
			  $allow = true;
			break;
	  }
	  if($allow){
		$sql = "DELETE FROM `{$table}` WHERE id = ? ;";
		$stmt = $this->dblj->prepare($sql);
		$stmt->execute(array($id));
		$num = $stmt->rowCount();
	    if( $num == 1 ){ return true;} 
	  }
	 return false;
	}

	function edit_record($table,$data){//对指定数据表维护一条记录
		switch($table){
			case "jineng":
			  $table = "jineng";
			break;
			case "equip":
			  $table = "equip";
			break;
			case "map":
			  $table = "mid";
			  $title = "地图";
			break;
		}
		//var_dump($data);
		$value = array();
		$arry = json_decode($data,true);
		foreach($arry as $k => $v){ 
			if(G_isPermit($k) && $k !="edit_record"  && $k !="id" ){
				if($v != ""){
					$field .=",`".$k."` = ? ";
					if( $v == "datetime" ){$v = date("Y-m-d H:i",time());}
					array_push($value,$v);
				}else{
					$field .=",`".$k."` = null ";
				}
			}
		}
		$field = substr($field,1);
		array_push($value,$arry['id']);
		$sql = "UPDATE `{$table}` SET {$field} WHERE `id` = ?;";
		//var_dump($sql,$value);
		$stmt = $this->dblj->prepare($sql);
		$res = $stmt->execute($value);
		$num = $stmt->rowCount();
	  if( $num == 1 && $res){
        return array('title'=>"{$title}数据条目修改成功！");
	  }
	  if( $num == 0 && $res){
		return array('title'=>"{$title}数据条目修改失败！" , 'body' => "ERROR：新的内容与原内容相同!");
	  }
	  return array('title'=>"{$title}数据条目修改失败！" , 'body' => "ERROR：内部数据处理错误！");
	}

	function get_attribute_cfg($table,$name) {//获取字段单个字段备注
			$db = $GLOBALS['config']['db']['db_name'];
			$sql="SELECT column_comment,column_type,column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND table_schema = '".$db."' AND column_name = ?;";
			$stmt = $this->dblj->prepare($sql);
			$stmt->execute(array($table , $name ));
			$res = $stmt->fetch(\PDO::FETCH_OBJ);
			return $res;
	}	
	
	function isadd($table,$name) {
			$sql="describe `$table` `$name`";
			$tep=$this->dblj->query($sql);
			$temp=$tep->fetch(\PDO::FETCH_ASSOC);
			if(!$temp){
				return true;}
			return false;
	}
	
	function isadd_true($table,$name) {
			$sql="describe `$table` `$name`";
			$tep=$this->dblj->query($sql);
			$temp=$tep->fetch(\PDO::FETCH_ASSOC);
			if(is_array($temp)){
				return true;}
			return false;
	}
	
}

?>