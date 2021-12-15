<?php
//新手村村长
$game = new \main\game();
$player = $game->player_get_uinfo();

$npchtml ="
游戏错误
{$变量_系统->链接_返回游戏_按钮短};";

$xiaohao = round($player->ulv*15.2);
if ($nid!=''){
    if (isset($canshu)){
        switch ($canshu){
            case 'rehp':
                if ($player->uhp<=0){
                    $game->yxb_change(2,$xiaohao);
                    $game->player_re_hp();
                    $player =$game->player_get_uinfo();
                    $gnhtml ="
                    <br/>$npc->nname:少侠，你的的气血已经恢复了！<br/>
                    生命：$player->uhp/$player->umaxhp<br/>";
                }else{
                    $gnhtml ="<br/>我这里只接待重伤人士<br/>";
                }
                break;
        }
    }else{
        $rehp = $game->create_url("cmd=npc&nid=$nid&canshu=rehp","生命恢复需要[$xiaohao]灵石(没有灵石不收费)");
        $gnhtml ="<br/>$rehp<br/>";
        }
}
?>