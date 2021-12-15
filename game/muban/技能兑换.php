<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/11
 * Time: 16:14
 */
$game = new \main\game();
$jnlistcmd = $game->create_url($a,$c,"cmd=npc&nid=$nid&canshu=jnlist");
$jnlist='<br/>========兑换列表========<br/>';
$gnhtml = <<<HTML
<br/><a href="$jnlistcmd">符箓兑换</a><br/>
HTML;
$suoyin = 0;
if (isset($canshu)){
    switch ($canshu){
        case 'jnlist':
            $retjn = \player\getjineng_all($dblj);
            foreach ($retjn as $jn){
                $suoyin += 1;
                $jnid = $jn['jnid'];
                $jnname= $jn['jnname'];
                $jncmd = $game->create_url($a,$c,"cmd=jninfo&jnid=$jnid");
                $jnlist .= "[$suoyin]<a href='$jncmd'>$jnname</a><br/>";
            }
            $gnhtml = $jnlist;
            break;
    }

}