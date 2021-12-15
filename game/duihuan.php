<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/20
 * Time: 18:39
 */

$player = $game->player_get_uinfo();
$tishi = '';
if (isset($_POST['dhm'])){
    $dhm = $_POST['dhm'];
    $duihuan = $game->dh_get_info($dhm);
    if ($duihuan){
        $ret = $game->dh_delete($dhm);
        //及时删除兑换码， 最好采用开启事务
        $tishi = "兑换{$duihuan->dhname}兑换码成功，获得:<br/>";
        $retallzb = explode(',',$duihuan->dhzb);
        foreach ($retallzb as $zb){
            if ($zb){
                $game->zb_add_zhuangbei($zb);
                $zhuangbei = $game->zb_get_info_sys($zb);
                $tishi .= "$zhuangbei->zbname<br/>";
            }
        }
        $djitem = explode(',',$duihuan->dhdj);
        foreach ($djitem as $djinfo){
            if ($djinfo){
                $dj = explode('|',$djinfo);
                $djid = $dj[0];
                $djcount = $dj[1];
                $game->dj_add($djid,$djcount);
                $daoju = $game->dj_get_sys($djid);
                $tishi .= "{$daoju->djname}x{$djcount}<br/>";
                $game->rw_update_dj($djid,$djcount);
            }
        }
        $ypitem = explode(',',$duihuan->dhyp);
        foreach ($ypitem as $ypinfo){
            if ($ypinfo){
                $yp = explode('|',$ypinfo);
                $ypid = $yp[0];
                $ypcount = $yp[1];
                $game->yp_add($ypid,$ypcount);
                $yaopin = $game->yp_get_info_sys($ypid);
                $tishi .= "{$yaopin->ypname}x{$ypcount}<br/>";
            }
        }
        if ($duihuan->dhyxb){
            $game->yxb_change(1,$duihuan->dhyxb);
            $tishi .= "灵石：$duihuan->dhyxb<br/>";
        }
        if ($duihuan->dhczb){
            $game->czb_change(1,$duihuan->dhczb);
            $tishi .= "极品灵石：$duihuan->dhczb<br/>";
        }
        if ($duihuan->dhexp){
            $game->player_add_exp($duihuan->dhexp);
            $tishi .= "经验：$duihuan->dhexp<br/>";
        }

    }else{
        $tishi =  '兑换失败<br/>';
    }

}



$dhhtml =<<<HTML
==========兑换页面==========

<form method="post">
<input type="hidden" name="c" value="$cc">
兑换码:<br/><input name="dhm">
<input type="submit" value="兑换"><br/><br/>
</form>

$tishi

{$变量_系统->链接_返回游戏_按钮长}

HTML;
echo $dhhtml;
?>

