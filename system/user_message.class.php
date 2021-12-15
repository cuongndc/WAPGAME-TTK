<?php
//地图配置操作定义

namespace game_system;

class user_message{
    public $dblj;
    public $sys;
    function __construct(){
		global $dblj;
		global $sys;
        $this->dblj = $dblj;
        $this->sys = $sys;
	}
	
	function liaotian_get_all($num ){//获取公共聊天纪录
        $sql = "SELECT * FROM ggliaotian ORDER BY id DESC LIMIT ?";//聊天列表获取
		$stmt = $this->dblj->prepare($sql);
		$stmt->execute(array($num));
        $retObj = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $retObj;
    }

    function liaotian_send_all($type ,$msg ,$uname , $uid){//发送公共聊天
        $nowdate = date('Y-m-d H:i:s');
        $ltmsg = htmlspecialchars($msg);
        $sql = "insert into ggliaotian(type,name,date,msg,uid) values(?,?,?,?,?)";
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute(array($type,$uname,$nowdate,$ltmsg,$uid));
        return $exeres;

    }


 
}
?>