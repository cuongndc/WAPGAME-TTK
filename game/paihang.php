<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/15
 * Time: 20:36
 */
$player = $game->player_get_uinfo();
$phhtml='';
$phlshtml='';

$ret = $game->paihang_get();
if ($ret){
    for ($i=0;$i < count($ret);$i++){
        $uname = $ret[$i]->uname;
        $ulv = $ret[$i]->ulv;
        $uid = $ret[$i]->uid;
        $cxsid = $ret[$i]->sid;
//        $clubp = $game->clubplayer_get_player_sid($cxsid);
//        if ($clubp){
//            $club = $game->club_get_info($clubp->clubid);
//            $club->clubname ="[$club->clubname]";
//        }else{
//            $club = new \player\club();
//            $club->clubname ="";
//        }
        $ucmd = $game->create_url("cmd=otherzhuangtai&uid=$uid","$uname");
        $xuhao = $i+1;

        $phlshtml .="$xuhao.[等级:{$ulv}]{$ucmd}<br/>";//{$club->clubname}
    }

    $变量_排行榜 = (object)array(
        "排行列表"=>$phlshtml
    );

    $dis_mid = $game->dis_get('排版_排行榜');
    eval("\$out_html = \"$dis_mid->dis_string\";");
    $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
    $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
    $out_html = str_replace("</div><br/>", "</div>",$out_html);
    echo $out_html;

}
?>