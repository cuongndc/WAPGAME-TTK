<?php
//系统公共配置操作定义

namespace game_system {
	//系统配置操作定义
	
class sys{
	public $dblj;
    function __construct(){
		global $dblj;
        $this->dblj = $dblj;
	}
	
	function get_dis($dis_name,$type=""){//获取排版信息
      $sql = "select * from `dis` WHERE dis_name = ?";
      $stmt = $this->dblj->prepare($sql);
	  $stmt->execute(array($dis_name));
	  $dis = $stmt->fetch(\PDO::FETCH_OBJ);
		if ($type == "text"){
			return $dis->dis_string;
		}
		return $dis;
	}
	
	function get_event($event_id){//获取事件信息
      $sql = "select * from `event` WHERE id = ?";
      $stmt = $this->dblj->prepare($sql);
	  $stmt->execute(array($event_id));
	  $event = $stmt->fetch(\PDO::FETCH_OBJ);
	  if(is_object($event)){
		return $event;
	  }else{
		return ;
	  }
	}
	
    function get_system_config($m_name,$m_value){//读取一条系统设置
        $sql = "select * from `system_config` WHERE m_name = ? AND  m_value = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array( $m_name , $m_value));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$ret ){return ;}
        if (!$ret || !$ret->m_string ){return ;}
        return $ret->m_string;
    }
	
    function set_system_config($type,$m_name,$m_value){//更新一条系统设置
		$sql = "UPDATE `system_config` SET `m_string` = ? WHERE `m_name` = ? AND `m_value` = ?";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($m_value , $type ,$m_name ));
		return $ret;
    }

	function add_system_config($type,$m_name,$m_value){//新建一条系统设置
		$sql = "INSERT INTO `system_config`(`m_name`, `m_value`, `m_string` ) VALUES ( ? , ?, ? );";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($m_value , $type ,$m_name ));
		return $ret;
    }

    function get_system_config_all(){//读取全部系统设置
        $sql = "select * from `system_config`";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    }

	function del_system_config($m_name,$m_value){//删除一条系统设置
		$sql = "DELETE FROM `system_config` WHERE `m_name` = ? and `m_value` = ? AND (`m_god` <> 'god' or `m_god` is null);";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($m_name , $m_value ));
		return $ret;
    }

	function create_url($url,$url_name='',$type=1){//创建url
        if ( !isset($_SESSION['c']) || !isset($_SESSION['a']) ){
            $c = rand(1,100);
            $a = rand(1,100);
            $_SESSION['c'] = $c;
            $_SESSION['a'] = $a;
        }
        $a = $_SESSION['a'];
        $c = $_SESSION['c'];
        $c++;
        $_SESSION["a_{$a}_c_{$c}"] = $url;
        $returl = "?c=a_{$a}_c_{$c}";
        if ($c>5){
            $a++;
            $c = 0;
        }
        $_SESSION['a'] = $a;
        $_SESSION['c'] = $c;
		
        switch ($type){
            case 1:
                $class = "h5ui-btn_1 h5ui-btn_primary btn-outlined_1";
                break;
            case 2:
                $class = "";
                break;
            case 3:
                $class = "h5ui-grid_item";
                break;
            case 4:
                $class = "h5ui-btn h5ui-btn_primary btn-outlined";
			break;
			case 0:
				$class = null;
			break;
        }
		//var_dump($url,$url_name,$type,'=====',$returl,'<br>');
        
		if(isset($class)){
			$returl = "<a href='$returl' class='$class'>$url_name</a>";
			return $returl;
		}else{
			return $returl;
		}
    }

	function create_url_nowmid($type = 1){//回到玩家所在地图
        return $this->create_url("cmd=mid&cmd2=gonowmid","返回游戏",$type);
    }
	
    function create_url_goremid(){//回到复活点
        return $this->create_url("cmd=mid&cmd2=goremid","回到复活点");
    }

	function get_assembly_all($page=0,$page_count=0,$all=""){//读取功能点列表返回记录总数和分页列表
		$sql = "select count(*) from assembly";
		$stmt = $this->dblj->query($sql);
        $count = $stmt->fetchColumn();
		if($all!="all"){
		if(intval($page)==0 && intval($page_count)==0){
			return array($count,"无查询结果");
			}
        $sql = "select * from assembly limit ". intval($page) .",".intval($page_count);
		}else{
		$sql = "select * from assembly"; 
		}
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $assembly = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return array($count, $assembly);
    }

    function get_assembly($assembly_name){//获取功能点信息
        $sql = "select * from `assembly` WHERE value = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($assembly_name));
        $dis = $stmt->fetch(\PDO::FETCH_OBJ);
		if ($type=="text"){return $dis->dis_string;}
        return $dis;
    }

	function set_assembly($assembly_valu,$assembly_name,$type){//更新功能点信息
		switch($type){
			case  "nick":
			$sql = "UPDATE `assembly` SET `nickname` = ? WHERE `value` = ?;";
			break;
			case  "clas":
			$sql = "UPDATE `assembly` SET `clas` = ? WHERE `value` = ?;";
			break;
			case  "style":
			$sql = "UPDATE `assembly` SET `style` = ? WHERE `value` = ?;";
			break;
		}
		$stmt = $this->dblj->prepare($sql);
		$ret = $stmt->execute(array($assembly_valu,$assembly_name));
		return $ret;
	}

    function get_assembly_seaech($assembly_name){//搜索功能点信息
	    $sql = "select * from `assembly` WHERE nickname like ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array("%$assembly_name%"));
        $dis = $stmt->fetchALL(\PDO::FETCH_OBJ);
        return $dis;
	}


	
	}
}
?>
