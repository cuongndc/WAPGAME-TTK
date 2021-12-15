<?php
//var_dump($player_info,$arr_data->nowmid);
$nowmid = $arr_data->nowmid;
$gid = $arr_data->gid;
$gyid = $arr_data->gyid;

$obj = (object)[];
$obj->id = $gid;
$obj->initial_id = $gyid;
$obj->type = 'npc';

$player->set_player_ut($player_info->sid,'o',json_encode($obj));
$ut = $player->get_player_ut($player_info->sid);

if ($nowmid != $player_info->nowmid){
    $out_html = "
     请正常玩游戏！<br/>
     <br/>
     {$变量_系统->链接_返回游戏_按钮短}";
}elseif (isset($gid)){
    $guaiwu = $npc->get_npc_run($gid);
    $yguaiwu = $npc->get_npc_info($gyid);
	//var_dump($yguaiwu,$gyid );
if(!$yguaiwu){
	    $out_html = "
     当前NPC不存在！<br/>
     <br/>
     {$变量_系统->链接_返回游戏_按钮短}";
	 ;
}else{

    if ($yguaiwu->info==''){
        $yguaiwu->info = '没有任何名气';
    }
    
    if (($guaiwu->sid !='' and $guaiwu->sid != $game->sid) or $guaiwu->name==''){

        $dis_mid = $dis->dis_get('排版_怪物不存在');
        eval("\$out_html = \"$dis_mid->dis_string\";");
        $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
        $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
        $html = str_replace("</div><br/>", "</div>",$out_html);

    }  else{
        $链接_进入战斗_块 = $sys->create_url("cmd=pve_new&cmd2=intopve&gid=$gid&nowmid=$nowmid","进入战斗",3);
        $链接_进入战斗_链接 = $sys->create_url("cmd=pve_new&cmd2=intopve&gid=$gid&nowmid=$nowmid","进入战斗",2);
        $链接_进入战斗_按钮短 = $sys->create_url("cmd=pve_new&cmd2=intopve&gid=$gid&nowmid=$nowmid","进入战斗",1);
        $链接_进入战斗_按钮长 = $sys->create_url("cmd=pve_new&cmd2=intopve&gid=$gid&nowmid=$nowmid","进入战斗",4);
        $dlhtml = '';
        $zbhtml = '';
        $djhtml = '';
        $yphtml = '';
        if ($yguaiwu->gzb!=''){
            $zbarr = explode(',',$yguaiwu->gzb);
            foreach($zbarr as $newstr){
                $zbkzb = $game->zb_get_info_sys($newstr);
                $zbcmd = $sys->create_url("cmd=zbinfo_sys&cmd2=zbinfo_sys&zbid=$zbkzb->zbid","{$zbkzb->zbname}",1);
                $zbhtml .= "<div class='zbys'>$zbcmd</div>";
            }
            $dlhtml .=$zbhtml;
        }
        if ($yguaiwu->gdj!=''){
            $djarr = explode(',',$yguaiwu->gdj);
            foreach($djarr as $newstr){
                $dj = $game->dj_get_sys($newstr);
                $djinfo = $sys->create_url("cmd=djinfo&cmd2=zbinfo_sys&djid=$dj->djid","{$dj->djname}",1);
                $djhtml .= "<div class='djys'>$djinfo</div>";
            }
            $dlhtml .=$djhtml;
        }
        if ($yguaiwu->gyp!=''){
            $yparr = explode(',',$yguaiwu->gyp);
            foreach($yparr as $newstr){
                $yp = $game->yp_get_info_sys($newstr);
                $ypinfo = $sys->create_url("cmd=ypinfo&cmd2=ypinfo&ypid=$yp->ypid","$yp->ypname",1);

                $yphtml .= "<div class='ypys'>$ypinfo</div>";
            }
            $dlhtml .=$yphtml;
        }

        if ($dlhtml == ''){
            $dlhtml = '该怪物没有物品掉落<br/>';
        }

        $变量_怪物信息 = (object)array(
            "掉落物品"=>$dlhtml,
            "链接_进入战斗_块"=>$链接_进入战斗_块,
            "链接_进入战斗_链接"=>$链接_进入战斗_链接,
            "链接_进入战斗_按钮短"=>$链接_进入战斗_按钮短,
            "链接_进入战斗_按钮长"=>$链接_进入战斗_按钮长,
        );


$npc_info = $npc->get_npc_run($gid);
if($npc_info->attackable == 1 ){
	$Operands = $sys->create_url("cmd=pve_new&cmd2=intopve&gid={$npc_info->id}&nowmid={$player_info->nowmid}","进入战斗",1);
}
		
$dis_mid = $dis->dis_get('dis_npc');
if(!is_object($dis_mid)){
	$dis_mid = $dis->dis_get('排版_怪物信息');
	eval("\$out_html = \"$dis_mid->dis_string\";");
}else{
	$out_html = '<p>'.$dis->dis_decode(json_decode($dis_mid->dis_string),$player_info,$yguaiwu).'</p>';
}
        $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
        $out_html = str_replace("<br/><br/>", "<br/>",$out_html);
        $out_html = str_replace("</div><br/>", "</div>",$out_html);
    }

	}
}
echo $out_html;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/11
 * Time: 10:08
 */
?>

