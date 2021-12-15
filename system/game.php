<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/5 0005
 * Time: 19:57
 */

namespace main;



class game{
    public $sid;
    public $uid;
	public $gpcl;
    public $token;

    function __construct(){

        if (!isset($_SESSION['sid'] )) {
            return;
        }
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
	}




//以下内容未看明白具体意义
//2019.01.10pm tian
	


    function club_get_info($clubid){
        $sql = "select * from `club` WHERE clubid = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute( array($clubid) );
        $club = $stmt->fetch(\PDO::FETCH_OBJ);
        return $club;
    }

    function club_join($clubid){
        return $this->club_join_sid($clubid , $this->sid);
    }

    function club_join_sid($clubid , $sid){
        $sql = "insert into clubplayer(clubid, sid, uid, uclv) VALUES (?,?,?,?)";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute( array($clubid , $sid , $this->uid , 6) );
        $club = $stmt->rowCount();
        return $club;
    }

    function club_out(){
        return $this->club_join_out_sid($this->sid);
    }

    function club_join_out_sid($sid){
        $sql="delete from clubplayer WHERE sid= ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute( array($sid) );
        $club = $stmt->rowCount();
        return $club;
    }


    function clubplayer_get_player(){
        return $this->clubplayer_get_player_sid($this->sid);
    }

    function clubplayer_get_player_sid($sid){
        $sql = "select * from `clubplayer` WHERE sid = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute( array($sid) );
        $club = $stmt->fetch(\PDO::FETCH_OBJ);
        return $club;
    }

    function clubplayer_get_all($clubid){
        $sql="select uid,uclv from clubplayer WHERE clubid = ? ORDER BY uclv ASC ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($clubid));
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


    function zb_get($zbnowid){//获取玩家装备信息

        $sql = "select * from playerzhuangbei where zbnowid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zbnowid));
        $zhuangbei = $stmt->fetch(\PDO::FETCH_OBJ);
        return $zhuangbei;
    }


    function zb_get_num(){
        $sql = "select count(*) from playerzhuangbei where sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($this->sid));
        return $stmt->fetchColumn();
    }

    function zb_delete($zbid ){
        $sql = "delete from playerzhuangbei where zbnowid = ? AND sid= ?";//删除装备
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($zbid , $this->sid));
        return $ret;
    }

    function zb_sell_yxb($zbid ,$yxb){
        $ret = $this->zb_delete($zbid );
        if ($ret){
            return $this->yxb_change(1 ,$yxb );
        }
        return false;
    }



    function yxb_change_uid($lx , $num , $uid){
        if (!$num){
            return false;
        }
        if ($lx == 1){
            $sql = 'update game1 set uyxb = uyxb + ? WHERE uid = ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute( array($num , $uid) );
            return $ret;
        }else{
            $sql = 'update game1 set uyxb = uyxb - ? WHERE uid = ? AND uyxb >= ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute( array($num , $uid , $num) );
            return $ret;
        }
    }


    function czb_change($lx , $num ){

        if (!$num){
            return false;
        }
        if ($lx == 1){
            $sql = 'update game1 set uczb = uczb + ? WHERE sid = ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute( array($num , $this->sid) );
            return $ret;
        }else{
            $sql = 'update game1 set uczb = uczb - ? WHERE sid = ? AND uczb >= ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute( array($num , $this->sid , $num) );
            return $ret;
        }
    }





    function yp_get_info_all_sys( ){//获取系统药品信息

        $sql = "select * from yaopin";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $exeres = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $exeres;
    }

    function player_change_ugj($ugj , $lx ){
        return $this->player_change_game1('ugj' , $lx , $ugj );
    }

    function player_change_ufy($ufy , $lx ){
        return $this->player_change_game1('ufy' , $lx , $ufy );
    }

    function player_change_ubj($ugj , $lx ){
        return $this->player_change_game1('ubj' , $lx , $ugj  );
    }

    function player_change_uxx($ugj , $lx ){
        return $this->player_change_game1('uxx' , $lx , $ugj );
    }



    function player_change_umaxhp($umaxhp , $lx ){
        return $this->player_change_game1('umaxhp' , $lx , $umaxhp );
    }

    function player_change_uhp_uid($hp , $lx , $uid){
        $player = $this->player_get_uinfo_uid($uid);
        return $this->player_change_uhp_sid($hp , $lx , $player->sid);
    }





	
    function player_clean_hp_uid($uid ){
        return $this->player_update_game1_uid('uhp' , 0 , $uid );
    }



    function yp_add($ypid,$ypsum){
        return  $this->yp_change($ypid , 1 , $ypsum );
    }


    function gw_update($ziduan , $change , $gid ){
        $sql = "update midguaiwu set $ziduan = ? WHERE id = ?";
        $stmt = $this->dblj->prepare($sql);
        return $stmt->execute(array($ziduan , $change , $gid));
    }



    function gw_get_mid($gid  ){

        $sql = "select * from mid WHERE mgid LIKE ? ";

        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array("%$gid|%"));
        return $stmt->fetchAll(\PDO::FETCH_OBJ);

    }


    function create_pvp_info($player , $pvper , $phurt , $ghurt ,  $baoji_player , $xx_player , $tishi ){

        $pgjcmd = $this->create_url("cmd=pvp&cmd2=ptgj&uid=$pvper->uid","攻    击");

        $pbuff = "";
        $gbuff = "";
        $yp_html = "";


        if ($ghurt){
            $gbuff = "(-$ghurt)";
        }

        if ($baoji_player){
            $gbuff = "$gbuff(暴击)";
        }

        if ($xx_player){
            $pbuff = "$pbuff(+$xx_player)";
        }
        if ($phurt){
            $pbuff = "$pbuff(-$phurt)";
        }
        if($player->yp1){
            $useyp = $this->create_url("cmd=pve_new&cmd2=useyp&ypid=$player->yp1&gid=$pvper->uid");
            $yp1 = $this->yp_get_info($player->yp1  );
            $yp_html = "<a href='$useyp'>$yp1->ypname</a>";
        }

        if($player->yp2){
            $useyp = $this->create_url("cmd=pve_new&cmd2=useyp&ypid=$player->yp2&gid=$pvper->uid");
            $yp1 = $this->yp_get_info($player->yp2 );
            $yp_html .= "<a href='$useyp'>$yp1->ypname</a>";
        }


        if($player->yp3){
            $useyp1 = $this->create_url("cmd=pve_new&cmd2=useyp&ypid=$player->yp3&uid=$pvper->uid");
            $yp1 = $this->yp_get_info($player->yp3 );
            $yp_html .= "<a href='$useyp1'>$yp1->ypname</a>";
        }



        $html = "
        ==战斗==<br/>
        $pvper->uname [lv:$pvper->ulv]<br/>
        气血:(<div class='hpys' style='display: inline'>$pvper->uhp</div>/<div class='hpys' style='display: inline'>$pvper->umaxhp</div>)$gbuff<br/>
        攻击:($pvper->ugj)<br/>
        防御:($pvper->ufy)<br/>
        ===================<br/>
        $player->uname [lv:$player->ulv]<br/>
        气血:(<div class='hpys' style='display: inline'>$player->uhp</div>/<div class='hpys' style='display: inline'>$player->umaxhp</div>)$pbuff<br/>
        攻击:($player->ugj)<br/>
        防御:($player->ufy)<br/>
        $tishi
        <br/>
        <ul>
        <li><a href='$gonowmid'>逃跑</a></li><br/>
        <li><a href='$pgjcmd'>攻击</a></li>
        </ul>
        <br/>
        $yp_html
        <br/>";

        return $html;
    }

    function dj_add( $djid , $djsum ){
        return $this->dj_change_sid(1 , $djid , $djsum , $this->sid);
    }

    function dj_sub( $djid , $djsum ){
        return $this->dj_change_sid(2 , $djid , $djsum , $this->sid);
    }

    function dj_change_sid($lx , $djid , $count , $sid){
        $player = $this->player_get_uinfo_sid($sid);
        $dj = $this->dj_get_player_sid($djid , $sid);
        $ret = false;

        if ($dj){
            if ($lx == 1){
                $sql = "update playerdaoju set djsum = djsum + ? where sid = ? and djid = ?";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($count , $this->sid ,$djid));
                $ret = $stmt->rowCount();
            }else{
                $sql = "update playerdaoju set djsum = djsum - ? where sid = ? and djid = ? AND djsum >= ?";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($count , $this->sid ,$djid , $count));
                $ret = $stmt->rowCount();
            }


        }elseif($lx == 1){
            $dj = $this->dj_get_sys($djid);
            if ($dj){
                $sql = "insert into playerdaoju(djname,djinfo,djzl,djid,djsum,sid,uid) VALUES (?,?,?,?,?,?,?)";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($dj->djname , $dj->djinfo , $dj->djzl , $dj->djid , $count , $this->sid , $player->uid));
                $ret = $stmt->rowCount();
            }
        }
        $this->rw_update_dj($djid , $count);
        return $ret;

    }


    function dj_get_all_player(){
        return $this->dj_get_all_player_sid($this->sid);
    }


    function dj_delete($djid ,$num){
        $sql = "update playerdaoju set djsum = djsum - ? where sid = ? and djid = ? AND djsum >= ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($num , $this->sid , $djid , $num));
        $ret = $stmt->rowCount();
        return $ret;
    }






    function zb_add_info($zbname , $zbinfo , $zbgj , $zbfy , $zbbj , $zbxx , $zbid , $zbhp , $qianghua , $zblv , $zbtool ){
        $sql = "insert into playerzhuangbei(zbname, zbinfo, zbgj, zbfy, zbbj, zbxx, zbid, uid, sid, zbhp, qianghua, zblv, zbtool) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->dblj->prepare($sql);
        $data = array($zbname , $zbinfo , $zbgj , $zbfy , $zbbj , $zbxx ,$zbid , $this->uid , $this->sid , $zbhp , $qianghua , $zblv , $zbtool);
        return $stmt->execute($data);
    }

    function zb_add_zhuangbei($zbid ){
        $zb = $this->zb_get_info_sys($zbid);
        return $this->zb_add_info( $zb->zbname , $zb->zbinfo , $zb->zbgj , $zb->zbfy , $zb->zbbj , $zb->zbxx , $zbid , $zb->zbhp , 0 , $zb->zblv , $zb->zbtool  );
    }

    function zb_add_zhuangbei_add($zbid , $zbname , $zbgj, $zbfy , $zbbj , $zbxx , $zbhp ){
        $zb = $this->zb_get_info_sys($zbid );
        return $this->zb_add_info( $zbname , $zb->zbinfo , $zb->zbgj + $zbgj , $zb->zbfy + $zbfy , $zb->zbbj + $zbbj , $zb->zbxx + $zbxx , $zbid , $zb->zbhp + $zbhp , 0 , $zb->zblv , $zb->zbtool  );
    }

    function zb_sx_add($ziduan , $gaibian , $zbnowid){
        return $this->zb_sx_add_sid($ziduan , $gaibian , $zbnowid , $this->sid);
    }

    function zb_sx_add_sid($ziduan , $gaibian , $zbnowid , $sid){
        $sql = "update playerzhuangbei set $ziduan = $ziduan + ? WHERE zbnowid = ? AND sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($gaibian , $zbnowid , $sid));
        return $stmt->rowCount();
    }

    function zb_sx_up($zbnowid , $upsx){
        $zb = $this->zb_get_info_player($zbnowid );
        $djsum = $zb->qianghua * 3 + 1;
        $zbsx = '';
        $zbqh = '';

        $djid = $this->get_system_config("游戏","强化道具");

        $ret = $this->dj_sub( $djid , $djsum );

        if ($ret){
            $upint = round($zbsx*0.05);
            if ($upint<1){
                $upint = 1;
            }
            $sjs = mt_rand(1,35);
            if ($sjs <= $zbqh){
                return 0;//失败
            }
            $sjs = mt_rand(1,30);
            if ($zbqh <= $sjs){
                $this->zb_sx_add($upsx , $upint , $zbnowid);
                $this->zb_sx_add('qianghua' , 1 , $zbnowid);
                return 1;
            }else{
                return 0;//失败
            }
        }else{
            return -1;//不足
        }
    }

    function zb_get_info_sys($zbid ){
        $sql = "select * from zhuangbei WHERE zbid = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zbid));
        return $stmt->fetch(\PDO::FETCH_OBJ);

    }

    function zb_get_info_player($zbid){
        return $this->zb_get_info_player_sid($zbid , $this->sid);
    }

    function zb_get_info_player_sid($zbid , $sid){
        $sql = "select * from playerzhuangbei WHERE zbnowid = ? AND sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zbid , $sid));
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }


    function zb_get_info_rw_all($rwid ){
        $task = $this->rw_get_sys($rwid);
        $sql = "select * from zhuangbei where zbid IN (?)";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($task->rwzb));
        return $stmt->fetchAll(\PDO::FETCH_OBJ);

    }

    function zb_update_user($zbnowid){
        return $this->zb_update_user_sid($this->sid  , $this->uid , $zbnowid);
    }

    function zb_update_user_sid($sid , $uid , $zbnowid){
        $sql = "update `playerzhuangbei` set sid = ?,uid = ? WHERE zbnowid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid , $uid , $zbnowid));
        return $stmt->rowCount();
    }


    function player_tool_update($tool , $zbid){
        $ret = $this->player_update_game1("tool{$tool}" , $zbid);
        return $ret;
    }

    function player_tool_xzzb($tool){
        return $this->player_tool_update($tool , 0);
    }

    function player_tool_setzb($zbid , $tool){

        $player = $this->player_get_uinfo();
        $arr = array($player->tool1,$player->tool2,$player->tool3,$player->tool4,$player->tool5,$player->tool6);
        $ret = "已经装备过该装备<br/>";
        if (!in_array($zbid,$arr)){
            $nowzb = $this->zb_get($zbid);

            if ($nowzb->uid != $player->uid){
                $ret = "你没有该装备，无法装备<br/>";

            }elseif($nowzb->zblv - $player->ulv > 5){
                $ret = "装备大于玩家等级，无法装备<br/>";

            }elseif($nowzb->zbtool != $tool && $nowzb->zbtool){
                $ret =  "装备种类不符合,无法装备<br/>";

            }else{
                $ret = $this->player_update_game1("tool{$tool}" , $zbid);

                if ($ret){
                    $ret =  "装备成功<br/>";
                }else{
                    $ret =  "装备失败，未知原因<br/>";
                }
            }

        }
        return $ret;
    }

    function im_is($uid){
        $sql="select imuid from im WHERE imuid = ? AND sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($uid , $this->sid));
        $row = $stmt->rowCount();
        return $row;
    }

    function im_add($imuid){
        $sql = "insert into `im`(imuid, sid, uid) VALUES (?,?,?)";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($imuid ,$this->sid, $this->uid ));
        return $stmt->rowCount();
    }

    function dh_get_info($dhm){
        $sql = "select * from duihuan WHERE dhm = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($dhm));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret;
    }

    function dh_delete($dhm){
        $sql = "delete from duihuan WHERE dhm = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($dhm));
        $ret = $stmt->rowCount();
        return $ret;
    }

    function paihang_get(){
        $sql = 'SELECT * FROM game1 ORDER BY ulv DESC,uexp ASC LIMIT 10';//列表获取
        $stmt = $this->dblj->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    function fangshi_add_zhuangbei_sid($zbnowid , $sid){
        $zb = $this->zb_get_info_player($zbnowid);
        $sql = 'insert into fangshi_zb(zbnowid, zbname, qianghua, pay, payid, zbinfo, zbgj, zbfy, zbbj, zbxx, zbid, uid, sid, zbhp, zblv) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zb->zbnowid ,$zb->zbname , $zb->qianghua , $zb->payid ,  ));
    }

    function fangshi_get_daoju($payid){
        $sql = "select * from `fangshi_dj` WHERE payid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($payid));
        $dj = $stmt->fetch(\PDO::FETCH_OBJ);
        return $dj;
    }

    function fangshi_get_zhuangbei($zbnowid){

        $sql = "select * from `fangshi_zb` WHERE zbnowid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zbnowid));
        $zb = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $zb;
    }

    function fangshi_update_daoju( $payid , $buycount ){

        $sql = "update `fangshi_dj` set djcount = djcount - ? WHERE djcount >= ? and payid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($buycount , $buycount , $payid));
        $row = $stmt->rowCount();
        return $row;
    }

    function fangshi_get_daoju_all(){
        $sql = "select * from `fangshi_dj`";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $dj = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $dj;
    }

    function fangshi_get_zhuangbei_all(){
        $sql = "select * from `fangshi_zb`";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $zb = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $zb;
    }

    function fangshi_delete_daoju_all(){
        $sql="delete from `fangshi_dj` where djcount = 0";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    function fangshi_delete_zhuangbei($zbnowid){
        $sql = "delete from `fangshi_zb` WHERE zbnowid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($zbnowid));
        return $stmt->rowCount();
    }

    function liandan_get_all(){
        $sql = "select * from `炼丹`";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute();
        $dis = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $dis;
    }

    function liandan_get_id($id){
        $sql = "select * from `炼丹` where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute([$id]);
        $dis = $stmt->fetch(\PDO::FETCH_OBJ);
        return $dis;
    }
}

?>