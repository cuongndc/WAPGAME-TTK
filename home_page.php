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

G_is_login();

?>

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
<br>
<div class="h5ui-page spacing-cell">
<?php

$player_info = $player->get_player_info();

$dis_info = $dis->dis_get('dis_index');

//var_dump($dis_info);
if ($dis_info->dis_prohibit == 1) {
    $out_html = '<p>' . $dis->dis_decode(json_decode($dis_info->dis_string), $player_info) . '</p>';
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("</div><br/>", "</div>", $out_html);
    echo $out_html;
} else {
    echo <<<html
<div class='h5ui-form'><a href="game.php" class="h5ui-btn h5ui-btn_primary btn-outlined">进入游戏>>></a></div>
html;

    if (G_is_god($player_info->token)) {
        echo "<div class='h5ui-form'><a href='admin/admin.php' class='h5ui-btn h5ui-btn_primary btn-outlined'>进入游戏设计模式>>></a></div>";
    } 
} 

?>
</div>
<!-- jQuery -->
<script src="./js/jquery.min.js"></script>
<!-- H5UI JS -->
<script src="./js/h5ui.min.js"></script>
</body>
</html>
