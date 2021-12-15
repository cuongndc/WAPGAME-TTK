<?php
$player = $game->player_get_uinfo();


$修炼时间 = 0;
$修炼经验 = 0;
$tishi = '';
$nowdate = date('Y-m-d H:i:s');
$修炼需要灵石 = 32 * $player->ulv;
$修炼需要极品灵石 = round(($player->ulv+1)/2);

$结束修炼_块 = "";
$结束修炼_链接 = "";
$结束修炼_按钮短 = "";
$结束修炼_按钮长 = "";

$使用灵石修炼 = "";
$使用极品灵石修炼 = "";

switch ($cmd2){
    case 'startxiulian':

        if ($player->sfxl == 1){
            $tishi = '你已经在 修炼中了<br/>';
        }else{
            if ($canshu == 1){
                $ret = $game->player_change_yxb( $修炼需要灵石 , 2);
            }else{
                $ret = $game->player_change_czb( $修炼需要极品灵石 , 2);
            }
            if ($ret){
                $game->player_change_xl_time($nowdate);
                $game->player_change_xl_buff(1);

                $tishi = '开始修炼...<br/>';
                $修炼时间 = 0;
                $player = $game->player_get_uinfo();

            }else{
                $tishi='灵石不足';
            }

        }
        break;

    case 'endxiulian':

        if ($player->sfxl == 1){

            $one = strtotime($nowdate) ;
            $tow = strtotime($player->xiuliantime);
            $修炼时间 = floor(($one-$tow)/60);
            if ($修炼时间 > 1440){
                $修炼时间 = 1440;
            }
            $修炼经验 = round($修炼时间 * $player->ulv*1.2);

            $game->player_add_exp($修炼经验 );
            $game->player_change_xl_buff(0);

            $tishi = "已经结束修炼...<br/>获得修为:{$修炼经验}<br/>";
            $player = $game->player_get_uinfo();

        }else{
            $tishi = '你还没有开始修炼...<br/>';
        }
        break;
}




if ($player->sfxl == 1){
    $one = strtotime($nowdate) ;
    $tow = strtotime($player->xiuliantime);
    $修炼时间 = floor(($one-$tow)/60);

    if ($修炼时间 > 1440){
        $修炼时间 = 1440;
    }
    $修炼经验 = round($修炼时间 * $player->ulv*1.2);
    $tishi = '修炼中<br/>';
    $结束修炼_块 = $game->create_url("cmd=xiulian&cmd2=endxiulian","结束修炼",3);
    $结束修炼_链接 = $game->create_url("cmd=xiulian&cmd2=endxiulian","结束修炼",2);
    $结束修炼_按钮短 = $game->create_url("cmd=xiulian&cmd2=endxiulian","结束修炼",1);
    $结束修炼_按钮长 = $game->create_url("cmd=xiulian&cmd2=endxiulian","结束修炼",4);
}else{
    $使用灵石修炼 = $game->create_url("cmd=xiulian&cmd2=startxiulian&canshu=1","使用灵石修炼");
    $使用极品灵石修炼 = $game->create_url("cmd=xiulian&cmd2=startxiulian&canshu=2","使用极品灵石修炼");
    $修炼时间 = 0;
    $修炼经验 = 0;

}

$变量_修炼 = (object)array(
    "提示信息" => $tishi,
    "修炼时间" => $修炼时间,
    "修炼经验" => $修炼经验,
    "修炼需要灵石" => $修炼需要灵石,
    "修炼需要极品灵石" => $修炼需要极品灵石,
    "链接_结束修炼_块" => "$结束修炼_块",
    "链接_结束修炼_链接" => "$结束修炼_链接",
    "链接_结束修炼_按钮短" => "$结束修炼_按钮短",
    "链接_结束修炼_按钮长" => "$结束修炼_按钮长",
    "链接_使用灵石修炼"=> $使用灵石修炼,
    "链接_使用极品灵石修炼"=>$使用极品灵石修炼,

);

$dis_mid = $game->dis_get('排版_修炼');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);
echo $out_html;

?>