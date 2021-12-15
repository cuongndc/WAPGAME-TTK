<?php

namespace user;


class user
{
    public $dblj;
    public $token;
    function __construct(){
		global $dblj;
        $this->dblj = $dblj;
    }
	
    function login_user( $username , $userpass ){//用户登录控制
        $username = htmlspecialchars($username);
        $userpass = htmlspecialchars($userpass);
		if(!$username){
            $ret = (object) array(
				'cmd'=>"login_user",
                'status'=>0,
                'msg'=>"用户名不能为空"
			);
            return $ret;
		}
		if(!$userpass){
            $ret = (object) array(
				'cmd'=>"login_user",
                'status'=>0,
                'msg'=>"密码不能为空"
			);
            return $ret;
		}

        $sql = "select * from userinfo where username = ? and userpass = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($username,$userpass));
        $retobj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$retobj){
            $ret = (object) array(
                'cmd'=>"login_user",
                'status'=>0,
                'msg'=>"用户不存在或账号密码错误"
            );
            return $ret;
        }
		
        $_SESSION['token'] = $retobj->token;
        $this->token = $retobj->token;
        $ret = (object) array(
            'cmd'=>"login_user",
            'status'=>1,
            'obj'=>$retobj,
            'msg'=>"登录成功"
        );
        return $ret;
    }

	function User_read( $username , $userpass ,$field ){//读取用户字段数据指定字段
        $username = htmlspecialchars($username);
        $userpass = htmlspecialchars($userpass);
		$field = htmlspecialchars($userpass);
		if(!$username){
            $ret = (object) array(
				'cmd'=>"User_read",
                'status'=>0,
                'msg'=>"用户名不能为空"
			);
            return $ret;
		}
		if(!$userpass){
            $ret = (object) array(
				'cmd'=>"User_read",
                'status'=>0,
                'msg'=>"密码不能为空"
			);
            return $ret;
		}
        $sql = "select ".$field." from userinfo where username = ? and userpass = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($username,$userpass));
        $retobj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$retobj){
            $ret = (object) array(
                'cmd'=>"User_read",
                'status'=>0,
                'msg'=>"用户不存在或账号密码错误"
            );
            return $ret;
        }
        $_SESSION[$field] = $retobj->$field;
        $this->$field = $retobj->$field;
        $ret = (object) array(
            'cmd'=>"User_read",
            'status'=>1,
            'obj'=>$retobj,
            'msg'=>"读取成功"
        );
        return $ret;

    }

	function get_user_type($token){//读取用户权限类型；
        $sql = "select power from userinfo where token = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($token));
        $retobj = $stmt->fetch(\PDO::FETCH_OBJ);
		return $retobj->power;
	}
		
    function login_game(){//尝试用当前帐号登录游戏
        $ret = $this->login_game_token($this->token);
        return $ret;
    }

    function login_user_role($token){
        $sql = "select * from game1 WHERE token = ?;";
        $stmt = $this->dblj->prepare($sql);
        $data = array($token);
        $stmt->execute($data);
        $retobj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$retobj){
            $ret = (object) array(
                'cmd'=>"login_game",
                'status'=>2,
                'msg'=>"角色不存在"
            );
            return $ret;
        }

        $ret = (object) array(
            'cmd'=>"login_game",
            'status'=>1,
            'obj'=>$retobj,
            'msg'=>"登陆成功"

        );
        return $ret;
    }
	
    function login_game_token($token){
        $sql = "select * from game1 WHERE token = ?;";
        $stmt = $this->dblj->prepare($sql);
        $data = array($token);
        $stmt->execute($data);
        $retobj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$retobj){
            $ret = (object) array(
                'cmd'=>"login_game",
                'status'=>2,
                'msg'=>"角色不存在"
            );
            return $ret;
        }
        $ret = (object) array(
            'cmd'=>"login_game",
            'status'=>1,
            'obj'=>$retobj,
            'msg'=>"登陆成功"
        );
        return $ret;
    }

    function new_player( $uname , $usex ){//创建角色
		global $game_novice;
		global $player;
        $this->token = $_SESSION['token'];
        $uname = htmlspecialchars($uname);
        if(strlen($uname)<6 || strlen($uname)>12){
            $ret = (object) array(
                'cmd'=>"newplayer",
                'status'=>0,
                'msg'=>"用户名不能太短或者太长"
            );
            return $ret;
        }
		if(!isset($game_novice)){$firstmid = 0;}
        $sid = md5($uname.$this->token .'229');
        $nowdate = date('Y-m-d H:i:s');
        $sql = "insert into game1(token , sid , name , sex , nowmid , endtime) SELECT  ?,?,?,?,?,? FROM dual WHERE NOT exists(SELECT token FROM game1 WHERE token = ?);";
        $stmt = $this->dblj->prepare($sql);
        $data = array( $this->token , $sid , $uname , $usex , $firstmid , $nowdate ,$this->token);
        try {
		$exeres = $stmt->execute($data);
		$player->inital_data($sid);
        if (!$exeres){
            $ret = (object) array(
                'cmd'=>"newplayer",
                'status'=>0,
                'msg'=>"创建失败，未知原因"
            );
            return $ret;

        }
        $ret = (object) array(
            'cmd'=>"newplayer",
            'status'=>1,
            'msg'=>"创建成功"
        );
        return $ret;
		} catch (PDOException $e) {
     echo 'insert error: '.print_r($data,true) ."\n" .$e->getMessage();}
    }

    function reg($username , $userpass , $userpass2){
        $sql = "select * from userinfo where username = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($username));
        $stmt->bindColumn('username',$cxusername);
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        if($userpass2 != $userpass){
            $a = '两次输入密码不一致';
        }elseif (strlen($username) < 6 or strlen($userpass)< 6){
            $a = '账号或密码长度请大于或等于6位';
        }elseif ($ret){
            $a = '注册失败,账号'.$cxusername.'已经存在';
        }else{
            $token = md5("$username.$userpass".strtotime(date('Y-m-d H:i:s')));
            $sql = "insert into userinfo(username,userpass,token) values(?,?,?)";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($username,$userpass,$token));
            $a = '注册成功';
            header("refresh:1;url=index.php");
        }

        return $a;
    }

	function set_user($type,$name,$val){//向用户数据表写入临时数据
		
	}

}


?>