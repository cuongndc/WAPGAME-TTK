<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

//Đăng ký toàn bộ đường chạy của nơi cư trú
const Game_path = __DIR__ . "/";
require_once Game_path .'system/global.class.php';

if ( isset($_POST[ 'submit']) ){
    $username = $_POST['username'];
    $userpass = $_POST['userpass'];
	
    $Obj_login_user = $user->login_user($username , $userpass);
    if (!$Obj_login_user){exit('error');}
    if ($Obj_login_user->status == 0){
        $msg = $Obj_login_user->msg;
    }else{
        $userinfo = $Obj_login_user->obj;
        $_SESSION['token'] = $userinfo->token;
        $Obj_login_game = $user->login_game();
        if (!$Obj_login_game){exit('error');}
        if ($Obj_login_game->status == 0 ){// Lỗi đăng nhập người dùng

            $msg = $Obj_login_user->msg;
		}
        if ($Obj_login_game->status == 2 ){ // Không có vai trò nào trong tài khoản người dùng, hãy chuyển đến trang đăng ký vai trò
            $_SESSION['user_event'] = "event_Createroles";
			$_SESSION['user_dis'] = "dis_land";
			Header("Location:chuangjian.php");
		}
        if ($Obj_login_game->status == 1 ){// Đăng nhập thành công, bắt đầu sự kiện đăng nhập của người dùng
            $_SESSION['user_dis'] = "dis_land";
			$user_info = $Obj_login_game->obj;
			$_SESSION['sid'] = $user_info->sid;
			$_SESSION['uid'] = $user_info->uid;
			$player->set_player_us($user_info->sid,"user_event","event_login");
			Header("Location:home_page.php");
            exit();
        }
    }
}
require_once "html/header.php";

echo <<<html
    <img class="lazyload" src="images/11.jpg" data-original="images/11.jpg" width="100%" alt="">
    <div class="h5ui-msg_content">
        <h3>
		{$game_desc}
        </h3>
    </div>
    <div class="h5ui-form ">
        <form action="index.php" method="post">
            <label for="username" class="h5ui-form-label">Tài khoản</label>
            <input type="text" name="username" class="h5ui-form-input" placeholder="Vui lòng nhập tài khoản">
            <label for="password" class="h5ui-form-label">Mật khẩu</label>
            <input type="password" name="userpass" class="h5ui-form-input" placeholder="Xin vui lòng nhập mật khẩu">
            <div class="h5ui-msg_content"  style="color: red">{$msg}</div>
            <div align="center">
                <p><button class="h5ui-btn h5ui-btn_primary btn-outlined" type="submit" name="submit" >Đăng nhập</button></p>
                <p><a class="h5ui-btn h5ui-btn_primary btn-outlined" href="reguser.php" >Đăng ký</a></p>
            </div>
        </form>
    </div>
<br/>
</div>
<div style='text-align:center'>
<!--<a target="_blank" href="http://www.beian.miit.gov.cn/">豫ICP备19031259号</a>-->
</div>
<br>
</body>
</html>
html;

?>