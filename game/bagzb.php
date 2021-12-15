<?php

$tishi = '';
$fanye = "";
$cmd2 = $arr_data->cmd2;

if (isset($canshu)) {
    if ($canshu == 'maichu') {
        // $mczb = $game->zb_get_info_player($zbnowid);
        $sxzz = $mczb->zbgj + $mczb->zbfy + $mczb->zbbj * 5 + $mczb->zbxx * 5 + $mczb->qianghua * 3;
        $mcls = round($sxzz);
        $mcret = $game->zb_sell_yxb($zbnowid , $mcls);
        var_dump($mcret);

        if ($mcret) {
            $ret = $game->yxb_change(1, $sxzz / 2);
            $tishi = "卖出$mczb->zbname 成功，获得灵石:$mcls<br/>";
        } 
    } 
} 
if (!isset($yeshu)) {
    $yeshu = 1;
} 

switch ($cmd2) {
	case 'usezb':
		$bool = $goods->use_player_equip($player_info->sid ,$arr_data->id,$arr_data->clas);
		if($bool){
			$usval = json_decode($player_info->us_val); 
        } else {
            echo  "你翻遍了包裹，却没有找到想要使用的这件装备！<br>";
        } 
		break;
    case 'delezb':
        $zhuangbei = $game->zb_get_info_player($zbnowid);
        $fjls = $zhuangbei->qianghua * 20 + 20;
        $ret = $game->yxb_change(2 , $fjls);
        if ($ret) {
            $game->zb_delete($zbnowid);
            $qhs = round($zhuangbei->qianghua * $zhuangbei->qianghua);
            $sjs = mt_rand(1, 100);
            if ($sjs <= 30) {
                $sjs = mt_rand(1, 100);
                if ($sjs > 90) {
                    $qhs = $qhs + 3;
                } elseif ($sjs > 80) {
                    $qhs = $qhs + 2;
                } elseif ($sjs > 70) {
                    $qhs = $qhs + 1;
                } 
            } 
            $game->dj_add(1, $qhs);
            $tishi = '分解成功!<br/>';
            if ($qhs > 0) {
                $tishi .= "获得强化石:" . $qhs . "!<br/>";
            } 
        } else {
            $tishi = "灵石不足!<br/>";
        } 
        break;
    case 'bagzb':

        break;
} 

// $zbcount = $sys->zb_get_num();//装备数量


$zbhtml = '';

if ($yeshu != 1) {
    $ye = $yeshu - 1;
    $shangyiye = $sys->create_url("cmd=bagzb&cmd2=bagzb&yeshu=$ye", "上一页");
    $fanye = $shangyiye;
} 

if ($yeshu * 10 < $zbcount) {
    $ye = $yeshu + 1;
    $xiayiye = $sys->create_url("cmd=bagzb&cmd2=bagzb&yeshu=$ye", "下一页");
    $fanye .= $xiayiye;
} 

if (isset($fanye)) {
    $fanye = "<br/>$fanye<br/>";
} else {
    $fanye = '';
} 
$hangshu = 0;

$retzb = $goods->get_player_goods($player_info->sid, 'equip');

if ($retzb) {
    foreach ($retzb as $equip) {
        if (is_object($equip)) {
            $id = $equip->id;
            $hangshu = $hangshu + 1;
            $zbname =  $equip->name;
            $zbqh = $equip->lvl;
            $qhhtml = '';
            if ($zbqh > 0) {
                $qhhtml = "+" . $zbqh;
            } 
            $chakanzb = $sys->create_url("cmd=zbinfo&id=$id", "{$zbname}{$qhhtml}");
			if($equip->in_use ==1){
                $zbhtml .= "[$hangshu].$chakanzb(已装备)<br/>";
            } else{
				$mczb = $sys->create_url("cmd=bagzb&cmd2=usezb&id={$id}&clas={$equip->clas}", "使用");
                $zbhtml .= "[$hangshu]{$chakanzb}{$mczb}{$delezb}<br/>";
			}
			
        } 
    } 
} 

$变量_装备列表 = (object)array("装备列表" => $zbhtml,
    "链接_翻页" => $fanye,
    "提示信息" => $tishi
    );

$dis_mid = $dis->dis_get('排版_装备列表');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("<br/><br/>", "<br/>", $out_html);
$out_html = str_replace("</div><br/>", "</div>", $out_html);
echo $out_html;

?>