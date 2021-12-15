<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

define("Game_path",  __DIR__ ."\\" );//注册全居运行路径


require_once Game_path .'system/global.class.php';


$a = '';
    if (isset($_POST[ 'submit'])){
        $username = $_POST['username'];
        $userpass = $_POST['userpass'];
        $userpass2 = $_POST['userpass2'];
        $username = htmlspecialchars($username);
        $userpass = htmlspecialchars($userpass);
        $a = $user->reg($username , $userpass ,$userpass2 );
    }

?>
<html lang="en">
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title><?php echo $game_name ?></title>
    <link rel="stylesheet" href="css/gamecss.css?_=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/h5ui.min.css?_=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/example.min.css?_=<?php echo time(); ?>">
</head>
<body>
<div class="h5ui-page spacing-cell">

    <img class="lazyload" src="images/11.jpg" data-original="images/11.jpg" width="100%" alt="">
    <div class="h5ui-msg_content">
        <h3>
		<?php echo $game_desc ?>
        </h3>
    </div>
    <div class="h5ui-form ">

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

            <label for="username" class="h5ui-form-label">账号</label>
            <input type="text" name="username" class="h5ui-form-input" placeholder="请输入账号">

            <label for="password" class="h5ui-form-label">密码</label>
            <input type="password" name="userpass" class="h5ui-form-input" placeholder="请输入密码">

            <label for="password" class="h5ui-form-label">确认密码</label>
            <input type="password" name="userpass2" class="h5ui-form-input" placeholder="请确认密码">

            <div class="h5ui-msg_content" style="color: red"><?php if (isset($a)){ echo $a;} ?></div>

            <div align="center">
                <p>
                    <button class="h5ui-btn h5ui-btn_primary btn-outlined" type="submit" name="submit" >注册</button>
                </p>

                <p>
                    <a class="h5ui-btn h5ui-btn_primary btn-outlined" href="index.php" >登陆</a>
                </p>
            </div>
        </form>
    </div>
</div>
</body>
</html>