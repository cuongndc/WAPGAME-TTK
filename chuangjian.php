<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/5 0005
 * Time: 19:55
 */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

const Game_path = __DIR__ . "/"; //注册全居运行路径

require_once Game_path . 'system/global.class.php';

if (isset($_POST['uname']) && isset($_POST['sex'])) {
    $uname = $_POST['uname'] ;
    $sex = $_POST['sex'];
    $Obj_new_player = $user->new_player($uname , $sex , $token);
    if ($Obj_new_player->status == 0) {
        $msg = $Obj_new_player->msg;
    } 
    if ($Obj_new_player->status == 1) {
        $Obj_login_game = $user->login_game();
        if ($Obj_login_game->status == 1) {
            $userinfo = $Obj_login_game->obj;
            $_SESSION['sid'] = $userinfo->sid;
            $_SESSION['uid'] = $userinfo->uid;
			$player_info = $player->get_player_info($userinfo->sid);
			$player->set_player_us($player_info->sid,"user_event","User_register");
			Header("Location:game.php");
            exit;
        } else {
            $msg = $Obj_new_player->msg;
        } 
    } 
} 

?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title><?php echo $game_name ?></title>
    <link rel="stylesheet" href="css/gamecss.css?_=<?php echo time();
?>">
    <link rel="stylesheet" href="css/h5ui.min.css?_=<?php echo time();
?>">
    <link rel="stylesheet" href="css/example.min.css?_=<?php echo time();
?>">
</head>

<body >

<div class="h5ui-page spacing-cell">
    <div class="h5ui-form ">

        <form action='chuangjian.php' method="POST">

            <label for="username" class="h5ui-form-label">Tên nhân vật</label>
            <input type="text" name="uname" class="h5ui-form-input" placeholder="请输入角色名称">


            <div class="h5ui-radio" data-toggle="buttons">

                <label for="wechat" class="btn active">
                    <input type="radio" name="sex" checked="checked" value="1">
                    <span class="h5ui-radio_bd">Nam giới</span>
                    <span class="h5ui-radio_ft"></span>
                </label>

                <label for="alipay" class="btn">
                    <input type="radio" name="sex" value="2">
                    <span class="h5ui-radio_bd">Nữ giới</span>
                    <span class="h5ui-radio_ft"></span>
                </label>

            </div>
            <?php if (isset($msg)) {
    echo $msg;
} 
?>
            <br/>
            <button class="h5ui-btn h5ui-btn_primary btn-outlined" type="submit" name="submit" >Tạo</button>

        </form>
    </div>

</div>
<!-- jQuery -->
<script src="./js/jquery.min.js"></script>
<!-- H5UI JS -->
<script src="./js/h5ui.min.js"></script>
</body>
</html>


