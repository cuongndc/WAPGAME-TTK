<?php
$selfym = $_SERVER['PHP_SELF'];
$html = <<<HTML
    <form action=$selfym method="get">
        角色名称：
        <input type="hidden" name="cmd" value="cjplayer">
        <input type="hidden" name="token" value='$token'>
        <p><input type="text" name="username" maxlength="7"></p>
        <p><label>男：<input type="radio" name="sex" value="1" checked></label>
            <label>女：<input type="radio" name="sex" value="2"></label>
        </p>
        <input type="submit" value="创建">
    </form>

HTML;
echo $html;

?>




