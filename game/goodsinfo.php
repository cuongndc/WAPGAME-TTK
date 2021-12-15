<?php 
// $player = $game->player_get_uinfo();
$nowmid = $arr_data->nowmid;

$gid = $arr_data->goodsid;

$Operands = "";

$player->set_player_ut($player_info->sid, 'goods', $gid, 'add');

if ($nowmid != $player_info->nowmid) {
    $out_html = "
     请正常玩游戏！<br/>
     <br/>
     {$变量_系统->链接_返回游戏_按钮短}";
} 

switch ($arr_data->cmd2) {
    case 'pickup':
        if (isset($gid)) {
            $obj_info = $goods->get_goods_run($gid);
        } 
        $out_html = pickup($obj_info, $变量_系统);
        break;
    case 'player':
        $obj_info = $goods->get_player_goods($player->sid, null, $gid);
        if (is_object($obj_info)) {
            $good_info = $goods->get_goods_info($obj_info->initial_id);
        } 
        $out_html = load_goods($obj_info, $good_info, $arr_data->cmd2);
        break;
    default:
        if (isset($gid)) {
            $obj_info = $goods->get_goods_run($gid);
        } 
        if (is_object($obj_info)) {
            $good_info = $goods->get_goods_info($obj_info->gid);
        } 
        $out_html = load_goods($obj_info, $good_info, $arr_data->cmd2);
} 
echo $out_html;

function pickup($obj_info, $变量_系统) { // 从地图捡起物品
    global $goods;
    global $player_info;
    if ($goods->goods_pickup_mid($player_info->sid, $obj_info->id)) {
        $html = "捡起{$obj_info->gname}这个物品成功！<br/><br/>
					{$变量_系统->链接_返回游戏_按钮短}";
    } else {
        $html = "{$obj_info->gname}这个物品已经被人捡走了！<br/><br/>{$变量_系统->链接_返回游戏_按钮短}";
    } 
    return $html ;
} 

function load_goods($obj_info, $good_info, $cmd2) { // 加载物品数据
    global $sys;
    global $dis;
    global $Operands;
    global $player_info;
    $gid = $obj_info->id;
    $nowmid = $player_info->$nowmid;
    if (!$good_info) {
        $out_html = "当前物品不存在！<br/><br/>{$变量_系统->链接_返回游戏_按钮短}";
    } 
    switch ($cmd2) {
        case "mid":
            if (($obj_info->sid != '' and $obj_info->sid != $game->sid) or $obj_info->gname == '') {
                $dis_mid = $dis->dis_get('排版_怪物不存在');
                eval("\$out_html = \"$dis_mid->dis_string\";");
                $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
                $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
                $html = str_replace("</div><br/>", "</div>", $out_html);
            } else {
                $Operands .= $sys->create_url("cmd=goodsinfo&cmd2=pickup&goodsid=$gid&nowmid=$nowmid", "捡起", 1);
            } 
            break;
        case "player":
            $Operands .= $sys->create_url("cmd=pve_new&cmd2=intopve&gid=$gid&nowmid=$nowmid", "丢弃", 1);
            break;
    } 

    $dis_mid = $dis->dis_get('dis_good');
    if (!is_object($dis_mid)) {
        $dis_mid = $dis->dis_get('排版_怪物信息');
        eval("\$out_html = \"$dis_mid->dis_string\";");
    } else {
        $out_html = '<p>' . $dis->dis_decode(json_decode($dis_mid->dis_string), $player_info,$good_info) . '</p>';
    } 
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("<br/><br/>", "<br/>", $out_html);
    $out_html = str_replace("</div><br/>", "</div>", $out_html);
    return $out_html;
} 

?>

