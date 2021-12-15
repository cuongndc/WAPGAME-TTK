<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/11
 * Time: 18:54
 */
 
$jnid = $arr_data->jnid;


$duihuan = $sys->create_url("cmd=jninfo&type=duihuan&jnid=$jnid",'兑换');

$htmltishi = '';
$playerjn = $skill->get_player_skill_info($jnid,$player_info->sid);
$jineng = $skill->get_skill_info($playerjn->initial_id,$player_info->sid);

$jineng = G_convertObjectClass($jineng,$playerjn);
//var_dump($jineng);
if(isset($jineng->default)){
	$operation = $sys->create_url("cmd=jninfo&type=delmr&jnid=$jnid","取消默认");
}else{
	$operation = $sys->create_url("cmd=jninfo&type=setmr&jnid=$jnid","设为默认");
}
$operation .= $sys->create_url("cmd=jninfo&type=feichu&jnid=$jnid",'废除');
$gonowmid = $sys->create_url("cmd=gomid&newmid=$player->nowmid");


if (isset($arr_data->type)){
    switch ($arr_data->type){
        case 'feichu':
            $ret = $skill->get_player_skill_info($jnid ,$player_info->sid);
            if ($ret){
				$alert = true;
				if(!isset($arr_data->confirm)){
					echo  "确认废除当前【{$ret->name}】这个技能？<br><br>";
					echo  $sys->create_url("cmd=jninfo&type=feichu&confirm=true&jnid=$jnid","废除技能"),'<br><br>';
					echo  $sys->create_url("cmd=jninfo&jnid=$jnid",'返回游戏'),'<br>';
				}else{
					$skill->del_player_skill($jnid,$player_info->sid);
					echo  $sys->create_url("cmd=bagjn&cmd2=bagjn",'返回游戏'),'<br>';
				}
            }else{
                echo "你还没有掌握技能，无法废除！<br>";
            }
            break;
		case 'duihuan':
            $ret = \player\deledjsum($jineng->jndj,$jineng->djcount,$sid,$dblj);
            if ($ret){
                \player\addjineng($jnid,1,$sid,$dblj);
                $htmltishi = "兑换成功<br/>";
                $playerjn = \player\getplayerjineng($jnid,$sid,$dblj);
                $daoju = \player\getplayerdaoju($sid,$jineng->jndj,$dblj);
            }else{
                $htmltishi = "道具数量不足<br/>";
            }

            break;
        case 'setmr':
            $ret = $skill->get_player_skill_info($jnid ,$player_info->sid);
            if ($ret){
				echo  "当前技能【{$ret->name}】已设置为默认技能。<br><br>";
				$skill->set_player_default($jnid,$player_info->sid);
			}else{
                echo "你还没有掌握技能，无法设置！<br>";
            }
            break;
		case 'delmr':
            $ret = $skill->get_player_skill_info($jnid ,$player_info->sid);
            if ($ret){
				echo  "技能【{$ret->name}】的默认已取消。<br><br>";
				$skill->del_player_default($player_info->sid);
			}else{
                echo "你还没有掌握技能，无法取消！<br>";
            }
            break;
    }
	
$playerjn = $skill->get_player_skill_info($jnid,$player_info->sid);
$jineng = $skill->get_skill_info($playerjn->initial_id,$player_info->sid);

$jineng = G_convertObjectClass($jineng,$playerjn);
//var_dump($jineng);
if(isset($jineng->default)){
	$operation = $sys->create_url("cmd=jninfo&type=delmr&jnid=$jnid","取消默认");
}else{
	$operation = $sys->create_url("cmd=jninfo&type=setmr&jnid=$jnid","设为默认");
}
$operation .= $sys->create_url("cmd=jninfo&type=feichu&jnid=$jnid",'废除');
$gonowmid = $sys->create_url("cmd=gomid&newmid=$player->nowmid");

}

$dhhtml = "兑换需要：$dhdaoju->djname($daoju->djsum/$jineng->djcount){$duihuan}<br/><br/>";

$jineng->exp = intval($jineng->exp);
$jineng->max_exp = intval($jineng->max_exp);
$user_info = $sys->create_url("cmd=zhuangtai&cmd2=zhuangtai", "我的状态");
$scope = $jineng->group_attack==1?'群攻':'单攻';
$equip_type = intval($jineng->equip_type)==0?'任意':'';

$hurt_attr = $attribute->get_attribute_cfg('game1',$jineng->hurt_attr);
$hurt_attr = json_decode($hurt_attr->column_comment);
$hurt_attr = urldecode($hurt_attr->Notes);


$deplete_attr = $attribute->get_attribute_cfg('game1',$jineng->deplete_attr);
$deplete_attr = json_decode($deplete_attr->column_comment);
$deplete_attr = urldecode($deplete_attr->Notes);

if(empty($equip_type)){
$weapon = json_decode($sys->get_system_config("system", "weapon_class"));
$equip_type = intval($jineng->equip_type);
foreach($weapon as $obj){
	if(is_object($obj)){
		if(intval($obj->id) == $equip_type){
			$equip_type = $obj->name;
		}
	}
}
}
if(!isset($alert)){
echo <<<html
<br>
[我的技能]<br>
{$jineng->name}<br>
描述：{$jineng->desc}<br>
等级：{$jineng->lvl}({$jineng->exp}/{$jineng->max_exp})<br>

攻击范围：{$scope}<br>
伤害目标：{$hurt_attr}<br>
消耗目标：{$deplete_attr}<br>
兵器类型：{$equip_type}<br>
<br>
{$htmltishi}<br>
{$operation}<br>
{$dhhtml}<br>
<br>
{$user_info}<br>
{$变量_系统->链接_返回游戏_按钮短}
html;
}

?>