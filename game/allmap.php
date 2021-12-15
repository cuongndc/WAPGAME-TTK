<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/17
 * Time: 10:18
 */
$mapcl = new \Map_Control\mid();
$player = $game->player_get_uinfo();
$map_all_1 = '';
$map_all_3 = '';

$cxallmap = $mapcl->get_qy_all();
$br = 0;
for ($i=0; $i<count($cxallmap); $i++){

    $qyame = $cxallmap[$i]->qyname;
    $mid = $cxallmap[$i]->mid;

    if ($mid>0){
        $map = $game->mid_get_info($mid);
        $mname = $map->mname;
        $br++;
        $gomid = $gpcl->create_url("cmd=mid&cmd2=gomid&gomid=$mid","[{$qyame}]{$mname}",1);
        $map_all_1 .= $gomid;

        $gomid = $gpcl->create_url("cmd=mid&cmd2=gomid&gomid=$mid","[{$qyame}]{$mname}",3);
        $map_all_3 .= $gomid;
    }
    if ($br >= 3){
        $br = 0;
        $map_all.="<br/>"  ;
    }
}

$变量_复活点列表 = (object)array(
    "复活点列表" => $map_all_1,
    "复活点列表_块" => $map_all_3,
);


$dis_mid = $game->dis_get('排版_复活点列表');
eval("\$out_html = \"$dis_mid->dis_string\";");
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("<br/><br/>", "<br/>",$out_html);
$out_html = str_replace("</div><br/>", "</div>",$out_html);
echo $out_html;
?>