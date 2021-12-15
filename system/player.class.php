<?php 
// 玩家配置操作定义
namespace game_system;

class player {
    public $dblj;
    public $sid;
    public $sys;
    public $attribute;

    function __construct() {
        global $dblj;
        global $sys;
        global $attribute;
        $this->dblj = $dblj;
        $this->sys = $sys;
        $this->attribute = $attribute;

        $this->sid = $_SESSION['sid'];
    } 

    function player_relocation_mid($mid) { // 移动玩家到指定地图ID
        return $this->player_update_game1('nowmid' , $mid);
    } 

    function player_update_ypwz($ypwz , $ypid) {
        return $this->player_update_game1("yp{$ypwz}" , $ypid);
    } 

    function player_get_uinfo_uid($uid) {
        $sql = "select * from game1 where uid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($uid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        $player = $this->get_player_info($player->sid);
        return $player;
    } 

    function get_player_info($sid = null) { // 获取玩家信息
        if (!isset($sid)) {
            $sid = $this->sid ;
        } 
        $sql = "select * from game1 where sid= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        $sid = $player->sid;
        if (!isset($player->all_max)) {
            $table_config = $this->attribute->get_table_config('game1');
            foreach($table_config as $obj) {
                if (is_object($obj)) {
                    $no = json_decode($obj->column_comment) ;
                    if (is_object($no)) {
                        if ($no->max == "t") {
                            $max_name = "max_" . $obj->column_name;
                            $this->set_player_max($sid, $max_name);
                        } 
                    } 
                } 
            } 
        } 
        for ($i = 1;$i <= 6;$i++) {
            $player = (array)$player;
            if ($player["tool$i"]) {
                $zhuangbei = $this->zb_get($player["tool$i"]);
                $player = (object)$player;
                $player->ugj = $player->ugj + $zhuangbei->zbgj;
                $player->ufy = $player->ufy + $zhuangbei->zbfy;
                $player->ubj = $player->ubj + $zhuangbei->zbbj;
                $player->uxx = $player->uxx + $zhuangbei->zbxx;
                $player->umaxhp = $player->umaxhp + $zhuangbei->zbhp;
            } 
        } 
        
		$player = (object)$player;

        $ranges_lv = array(0, 30, 50, 70, 80, 90, 100, 110);
        $ranges_exp = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5);
        $player_lv2 = $player->ulv + 1;

        $层次1 = $this->sys->get_system_config("游戏", "层次1");
        $层次2 = $this->sys->get_system_config("游戏", "层次2");
        $层次3 = $this->sys->get_system_config("游戏", "层次3");
        $层次4 = $this->sys->get_system_config("游戏", "层次4");
        $层次5 = $this->sys->get_system_config("游戏", "层次5");
        $层次6 = $this->sys->get_system_config("游戏", "层次6");
        $层次7 = $this->sys->get_system_config("游戏", "层次7");
        $层次8 = $this->sys->get_system_config("游戏", "层次8");

        $ranges_jj = array($层次1 , $层次2 , $层次3 , $层次4 , $层次5 , $层次6 , $层次7 , $层次8);
        $ranges_jd = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');

        for ($i = 0;$i < count($ranges_lv);$i++) {
            $lv = $ranges_lv[$i];
            $lv2 = $ranges_lv[$i + 1];
            if ($player->ulv >= $lv && $player->ulv < $lv2) {
                $djc = $player->ulv - $ranges_lv[$i];
                $jds = ($lv2 - $lv) / 10;
                $num = (int) floor($djc / $jds);
                $jd = $ranges_jd[$num];

                $player->jingjie = $ranges_jj[$i];
                $player->cengci = $jd . '层';
                $player->umaxexp = $player_lv2 + round($player_lv2 / 2);
                $player->umaxexp = $player_lv2 * $player->umaxexp * 12 * $ranges_exp[$i] + $player_lv2;
                break;
            } 
        } 
        return $player;
    } 

    function player_update_sfzx($sfzx) { // 更新在线状态
        $nowdate = date('Y-m-d H:i:s');
        $sql = "update game1 set sfzx = ? , endtime = ? WHERE sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sfzx , $nowdate , $this->sid));
        $exeres = $stmt->rowCount();
        return $exeres;
    } 

    function player_update_sfzx_uid($sfzx, $uid) { // 更新在线状态
        $nowdate = $nowdate = date('Y-m-d H:i:s');
        $sql = "update game1 set sfzx = ? , endtime = ? WHERE uid = ?";
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute(array($sfzx , $nowdate , $uid));
        return $exeres;
    } 

    function player_update_endtime($endtime) { // 更新最后操作时间
        $sql = "update game1 set endtime = ? , sfzx = ? WHERE sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute(array($endtime , 1, $this->sid));
        return $exeres;
    } 

    function player_change_exp($exp , $lx) {
        $ret = $this->player_change_game1('exp' , $lx , $exp);
        if ($ret) {
            $ret = $this->plauer_lv_up();
        } 
        return $ret;
    } 

    function plauer_lv_up() {
        $ret = $this->player_is_tupo();
        if ($ret) {
            return false;
        } 
        return $this->player_lv_add();
    } 

    function player_lv_add() { // 强制升级 无视突破
        global $dis;
        $player = $this->get_player_info();
        $r_lv = $dis->dis_text_decode('{e.player_lvl_up}', 'lvl_up');
        if ($player->exp < $r_lv) {
            return false;
        } 
        $sql = "update game1 set exp = exp - ? where sid = ? AND exp >= ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($player->umaxexp , $this->sid , $player->umaxexp));

        $r_lv = array(1, 30, 50, 70, 80, 90, 100, 110);
        $r_gj = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5);
        $r_fy = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5);
        $r_hp = array(30, 50, 70, 90, 110, 130, 170);
        $playernextlv = $player->ulv + 1;

        for ($i = 0 ; $i < count($r_lv) - 1 ; $i++) {
            $lv1 = $r_lv[$i];
            $lv2 = $r_lv[$i + 1];

            if ($playernextlv >= $lv1 && $playernextlv < $lv2) {
                $sql = "update game1 set lvl = lvl + 1,
                                           ugj = ugj + $r_gj[$i],
                                           ufy = ufy + $r_fy[$i]
                                           where sid = ?";
                $stmt = $this->dblj->prepare($sql);
                return $stmt->execute(array($this->sid));
            } 
        } 
        return false;
    } 

    function player_is_tupo() {
        global $dis;
        $player = $this->get_player_info();
        $r_lv = $dis->dis_text_decode('{e.player_lvl_up}', 'lvl_up');
        if ($player->exp < $r_lv) {
            return 0;
        } 
        $playernextlv = $player->ulv + 1;
        $层次1 = $this->sys->get_system_config("游戏", "层次1");
        $层次2 = $this->sys->get_system_config("游戏", "层次2");
        $层次3 = $this->sys->get_system_config("游戏", "层次3");
        $层次4 = $this->sys->get_system_config("游戏", "层次4");
        $层次5 = $this->sys->get_system_config("游戏", "层次5");
        $层次6 = $this->sys->get_system_config("游戏", "层次6");
        $层次7 = $this->sys->get_system_config("游戏", "层次7");
        $层次8 = $this->sys->get_system_config("游戏", "层次8");

        $r_jj = array($层次1 , $层次2 , $层次3 , $层次4 , $层次5 , $层次6 , $层次7 , $层次8);

        for ($i = 0 ; $i < count($r_lv) ; $i++) {
            if ($playernextlv >= $r_lv[$i] && $playernextlv < $r_lv[$i + 1]) {
                if ($player->jingjie != $r_jj[$i]) {
                    return 1; //阶段突破
                } 

                $rangesjd = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
                $djc = $playernextlv - $r_lv[$i];
                $jds = ($r_lv[$i + 1] - $r_lv[$i]) / 10;
                $jieduan = (int)floor($djc / $jds);
                $jd = $rangesjd[$jieduan];

                if ($player->cengci != $jd . '层') {
                    return 2; //层次突破
                } 
                return 0;
            } 
        } 

        return 0;
    } 

    function player_add_exp($exp) {
        $ret = $this->alter_player_field('exp' , 1 , $exp , $this->sid);
        if ($ret) {
            $ret = $this->plauer_lv_up();
        } 
        return $ret;
    } 

    function yxb_change($lx , $num) {
        return $this->yxb_change_sid($lx , $num , $this->sid);
    } 

    function yxb_change_sid($lx , $num , $sid) {
        if (!$num) {
            return false;
        } 
        if ($lx == 1) {
            $sql = 'update game1 set money = money + ? WHERE sid = ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($num , $sid));
            return $ret;
        } else {
            $sql = 'update game1 set money = money - ? WHERE sid = ? AND money >= ? ';
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($num , $sid , $num));
            return $ret;
        } 
    } 

    function player_sub_exp($exp) {
        return $this->player_change_exp($exp , 2);
    } 

    function player_change_yxb($exp , $lx) {
        return $this->player_change_game1('uyxb' , $lx , $exp);
    } 

    function player_change_czb($exp , $lx) {
        return $this->player_change_game1('uczb' , $lx , $exp);
    } 

    function player_change_xl_time($time) {
        return $this->player_change_xl_time_sid($time , $this->sid);
    } 

    function player_change_xl_time_sid($time , $sid) {
        return $this->set_player_field('xiuliantime' , $time , $sid);
    } 

    function player_change_xl_buff($buff) {
        return $this->player_change_xl_buff_sid($buff , $this->sid);
    } 

    function player_change_xl_buff_sid($buff , $sid) {
        return $this->set_player_field('sfxl' , $buff , $sid);
    } 

    function set_player_field($field, $val = null, $sid, $type = "") { // 更新玩家字段值
        if (isset($val)) {
            $value = "?";
        } else {
            $value = "null";
        } 
        switch ($type) {
            case 'up':
                $sql = "update game1 set $field = {$value} WHERE sid = ?";
                break;
            default:
                $sql = "update game1 set $field = {$value} WHERE sid = ?";
        } 
        $stmt = $this->dblj->prepare($sql);
		//var_dump($sql,$val,$sid );
        if (isset($val)) {
            $bool = $stmt->execute(array($val , $sid));
        } else {
            $bool = $stmt->execute(array($sid));
        } 
        return $bool;
    } 

    function player_update_game1($ziduan , $gaibian) { // 更新玩家字段值
        $sql = "update game1 set $ziduan = ? WHERE sid = ?"; 
        // var_dump($sql,$gaibian , $this->sid);
        $stmt = $this->dblj->prepare($sql);
        $bool = $stmt->execute(array($gaibian , $this->sid));
        return $bool;
    } 

    function player_update_game1_uid($ziduan , $gaibian , $uid) { // 更新玩家字段值
        $sql = "update game1 set $ziduan = ? WHERE uid = ?";
        $stmt = $this->dblj->prepare($sql);
        $bool = $stmt->execute(array($gaibian , $uid));
        return $bool;
    } 

    function player_update_ispvp_uid($gaibian , $uid) { // 更新玩家字段值
        $sql = "update game1 set ispvp = ? WHERE uid = ?";
        $stmt = $this->dblj->prepare($sql);
        $bool = $stmt->execute(array($gaibian , $uid));
        return $bool;
    } 

    function player_update_ispvp($gaibian) { // 更新玩家字段值
        return $this->player_update_ispvp_sid($gaibian , $this->sid);
    } 

    function player_update_ispvp_sid($gaibian , $sid) { // 更新玩家字段值
        $sql = "update game1 set ispvp = ? WHERE sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $bool = $stmt->execute(array($gaibian , $sid));
        return $bool;
    } 

    function player_change_game1($ziduan , $lx , $gaibian) { // 改变玩家字段值
        return $this->alter_player_field($ziduan , $lx , $gaibian, $this->sid);
    } 

    function alter_player_field($ziduan , $lx = 1 , $gaibian , $sid) { // 改变玩家字段值
        global $attribute;
        $cfg = $attribute->get_attribute_cfg('game1', $ziduan);
        $cfg = json_decode($cfg->column_comment);
        $name = urldecode($cfg->Notes);
        switch ($lx) {
            case 1:
                $sql = "update game1 set $ziduan = $ziduan + ? WHERE sid = ? ";
                $data = array($gaibian , $sid);
                $mark = '+';
                break;
            case 2:
                $sql = "update game1 set $ziduan = $ziduan - ? WHERE sid = ? AND $ziduan >= ?";
                $data = array($gaibian , $sid , $gaibian);
                $mark = '-';
                break;
            case 3:
                if ($gaibian > 0) {
                    $mark = '+';
                    $sql = "update game1 set $ziduan = ifnull(`$ziduan`,0) + ? WHERE sid = ? AND ($ziduan >= 0 or $ziduan is null)";
                } else {
                    $mark = '-';
                    $sql = "update game1 set $ziduan = ifnull(`$ziduan`,0) - ? WHERE sid = ? AND ($ziduan >= 0 or $ziduan is null)";
                } 
                $data = array(abs($gaibian) , $sid);
                break;
        } 
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute($data);
        if ($exeres) {
            echo "{$name}{$mark}{$gaibian}<br>";
        } 
        return $exeres;
    } 

	function get_battle_Quick($val,$name = ""){//加载战斗页面快捷键信息
        $url = 'cmd=pve_new&cmd2=use&type=';
		$player_info= $this->get_player_info();
        $enemy = $player_info->enemy;
        if (isset($player_info)) {
            $us = $this->get_player_us($player_info->sid);
        } ;
        switch ($val) {
            case 'Quick_battle_1':
                if (isset($us->Quick_battle_1)) {
                    $Quick_battle = json_decode($us->Quick_battle_1)->val;
                    $name = $Quick_battle->name;
                    $html .= $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html .= $this->sys->create_url("{$url}{$us->Quick_battle_1->type}&Quick={$us->Quick_battle_1->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_2':
                if (isset($us->Quick_battle_2)) {
                    $Quick_battle = json_decode($us->Quick_battle_2)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_2->type}&Quick={$us->Quick_battle_2->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_3':
                if (isset($us->Quick_battle_3)) {
                    $Quick_battle = json_decode($us->Quick_battle_3)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_3->type}&Quick={$us->Quick_battle_3->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_4':
                if (isset($us->Quick_battle_4)) {
                    $Quick_battle = json_decode($us->Quick_battle_4)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_4->type}&Quick={$us->Quick_battle_4->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_5':
                if (isset($us->Quick_battle_5)) {
                    $Quick_battle = json_decode($us->Quick_battle_5)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_5->type}&Quick={$us->Quick_battle_5->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_6':
                if (isset($us->Quick_battle_6)) {
                    $Quick_battle = json_decode($us->Quick_battle_6)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_6->type}&Quick={$us->Quick_battle_6->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_7':
                if (isset($us->Quick_battle_7)) {
                    $Quick_battle = json_decode($us->Quick_battle_7)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_7->type}&Quick={$us->Quick_battle_7->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_8':
                if (isset($us->Quick_battle_8)) {
                    $Quick_battle = json_decode($us->Quick_battle_8)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_8->type}&Quick={$us->Quick_battle_8->val}&gid={$enemy}", $name, 1);
                } ;
                break;
            case 'Quick_battle_9':
                if (isset($us->Quick_battle_9)) {
                    $Quick_battle = json_decode($us->Quick_battle_9)->val;
                    $name = $Quick_battle->name;
                    $html = $this->sys->create_url("{$url}{$Quick_battle->type}&Quick={$Quick_battle->val}&gid={$enemy}", $name, 1);
                } else {
                    $html = $this->sys->create_url("{$url}{$us->Quick_battle_9->type}&Quick={$us->Quick_battle_9->val}&gid={$enemy}", $name, 1);
                } ;
                break;
        } 
        return $html;
	}
	
    function player_re_hp() {
        return $this->player_re_hp_sid($this->sid);
    } 

    function player_re_hp_sid($sid) {
        $player_info = $this->get_player_info();
        $sid = $player_info->sid;
        $table_config = $this->attribute->get_table_config('game1');
        foreach($table_config as $obj) {
            if (is_object($obj) && $obj->column_name == "hp") {
                $no = json_decode($obj->column_comment) ;
                if (is_object($no)) {
                    if ($no->max == "t") {
                        $max_hp = json_decode($this->get_player_max($sid)->max_hp);
                    } 
                } 
            } 
        } 
		var_dump($max_hp->val);
        return $this->set_player_field('hp' , $max_hp->val , $sid);
    } 

    function player_change_uhp($uhp , $lx) {
        return $this->player_change_uhp_sid($uhp , $lx , $this->sid);
    } 

    function player_change_uhp_sid($hp , $lx , $sid) {
        $player = $this->get_player_info();
        $hp = abs($hp);
        if ($lx == 1) {
            if ($player->hp + $hp > $player->umaxhp) {
                return $this->player_re_hp();
            } 
            $sql = "update game1 set hp = hp + ? WHERE sid = ? ";
            $data = array($hp , $sid);
        } elseif ($hp > 0) {
            if ($player->hp - $hp < 0) {
                return $this->player_clean_hp();
            } 
            $sql = "update game1 set hp = hp - ? WHERE sid = ? AND hp >= ? ";
            $data = array($hp , $sid , $hp);
        } else {
            return false;
        } 
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute($data);
        return $ret;
    } 

    function player_clean_hp() {
        return $this->player_clean_hp_sid($this->sid);
    } 

    function player_clean_hp_sid($sid) {
        return $this->player_update_game1('hp' , 0);
    } 

    function player_go_re() {
        global $map;
        $player = $this->get_player_info();
        $mid_info = $map->get_mid_info($player->nowmid);
        return $mid_info->id;
    } 

    function clubplayer_get_player_uid($uid) {
        $sql = "select * from `clubplayer` WHERE uid = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($uid));
        $club = $stmt->fetch(\PDO::FETCH_OBJ);
        return $club;
    } 

    function alter_attr_field($sid, $type, $name, $val) {
        switch ($type) {
            case 'ut':
                $this->set_player_ut($sid, $name, $val, "+");
                break;
            case 'u':
			case '':
                if (substr($name, 0, 4) == 'max_') {
                    $this->set_player_max($sid, $name, $val, "+");
                } else {
                    $sql = "SELECT COLUMN_NAME as name FROM information_schema.COLUMNS where TABLE_NAME='game1' order by COLUMN_NAME;";
                    $stmt = $this->dblj->prepare($sql);
                    $stmt->execute();
                    $list = $stmt->fetchALL(\PDO::FETCH_COLUMN, 0);
                    $field = array_search($name, $list);
                    if ($field) {
                        $this->alter_player_field($name, 3 , $val , $sid);
                    } else {
                        $this->set_player_us($sid, $name, $val, "+");
                    } 
                } 
                break;
        } 
    } 

    function set_attr_field($sid, $type, $name, $val) {
        switch ($type) {
            case 'ut':
                $this->set_player_ut($sid, $name, $val);
                break;
            case 'u':
			case '':
                if (substr($name, 0, 4) == 'max_') {
                    $this->set_player_max($sid, $name, $val);
                } else {
                    $sql = "SELECT COLUMN_NAME as name FROM information_schema.COLUMNS where TABLE_NAME='game1' order by COLUMN_NAME;";
                    $stmt = $this->dblj->prepare($sql);
                    $stmt->execute();
                    $list = $stmt->fetchALL(\PDO::FETCH_COLUMN, 0);
                    $field = array_search($name, $list);
                    if ($field) {
                        $this->set_player_field($name, $val , $sid);
                    } else {
                        $this->set_player_us($sid, $name, $val);
                    } 
                } 
                break;
        } 
    } 

    function set_player_ut($sid, $field_name, $val = null, $way = 'add') { // 向玩家数据表写入临时属性
        $ut = $this->get_player_ut($sid);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
		$field = $ut->$field;
		if (!is_object($field)) {
            $field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        if (isset($val)) {
            switch ($way) {
                case "add":
                    $field->val = $val;
                    $field->type = $type;
                    break;
                case "plus":
                case "+":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val + $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "reduce":
                case "-":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val + $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "multiply":
                case "*":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val * $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "divide":
                case "/":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val / $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
            }
			$sql = "UPDATE game1 SET ut_val = json_set(ut_val,?,?) WHERE sid = ? ;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",json_encode($field),$sid);
        } else {
			$sql = "UPDATE game1 SET ut_val = json_remove(ut_val,?) WHERE sid = ?;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",$sid);
        } 
		return $stmt->execute($data);
    } 

    function set_player_us($sid, $field_name , $val = null, $way = 'add') { // 向玩家数据表写入动态属性
		$ut = $this->get_player_us($sid);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
		$field = $ut->$field_name;
		if (!is_object($field)) {
            $field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        if (isset($val)) {
            if (!is_object($val)) {
                switch ($way) {
                    case "add":
                        $field->val = $val;
                        $field->type = $type;
                        break;
                    case "plus":
                    case "+":
                        if ($field->type == 'num' && $type == 'num') {
                            $field->val = $field->val + $val;
                        } else {
                            $field->val = $val;
                        } 
                        break;
                    case "reduce":
                    case "-":
                        if ($field->type == 'num' && $type == 'num') {
                            $field->val = $field->val + $val;
                        } else {
                            $field->val = $val;
                        } 
                        break;
                    case "multiply":
                    case "*":
                        if ($field->type == 'num' && $type == 'num') {
                            $field->val = $field->val * $val;
                        } else {
                            $field->val = $val;
                        } 
                        break;
                    case "divide":
                    case "/":
                        if ($field->type == 'num' && $type == 'num') {
                            $field->val = $field->val / $val;
                        } else {
                            $ut->$field->val = $val;
                        } 
                        break;
                } 
            } else {
                $field->val = $val;
                $field->type = 'object';
            } 
			$sql = "UPDATE game1 SET us_val = json_set(us_val,?,?) WHERE sid = ? ;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",json_encode($field),$sid);
        } else {
			$sql = "UPDATE game1 SET us_val = json_remove(us_val,?) WHERE sid = ?;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",$sid);
        } 
		return $stmt->execute($data);
    } 

    function set_player_max($sid, $field_name , $val = null, $way = 'add') { // 向玩家数据表写入属性MAX值
        $ut = $this->get_player_max($sid);
        if (!is_object($ut)) {
            $ut = (object)[];
        } 
		$field = $ut->$field_name;
        if (!is_object($field)) {
            $field = (object)[];
        } 
        if (is_float($val) || is_int($val)) {
            $type = 'num';
        } 
        if (isset($val)) {
            switch ($way) {
                case "add":
					$field->val = $val;
					$field->type = $type;
                    break;
                case "plus":
                case "+":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val + $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "reduce":
                case "-":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val + $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "multiply":
                case "*":
                    if ($field->type == 'num' && $type == 'num') {
                        $field->val = $field->val * $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
                case "divide":
                case "/":
                    if ($field->type == 'num' && $type == 'num') {
						$field->val = $field->val / $val;
                    } else {
                        $field->val = $val;
                    } 
                    break;
            } 
			$sql = "UPDATE game1 SET all_max = json_set(all_max,?,?) WHERE sid = ? ;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",json_encode($field),$sid);
        } else {
			$sql = "UPDATE game1 SET all_max = json_remove(all_max,?) WHERE sid = ?;";
			$stmt = $this->dblj->prepare($sql);
			$data = array("$.{$field_name}",$sid);
        } 
		return $stmt->execute($data);
    } 

    function get_player_ut($sid) { // 获取玩家临时属性列表
        $sql = "select ut_val from game1 where sid= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->ut_val);
    } 

    function get_player_us($sid) { // 获取玩家临时属性列表
        $sql = "select us_val from game1 where sid= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->us_val);
    } 

    function get_player_max($sid) { // 获取玩家属性最大值列表
        $sql = "select all_max from game1 where sid= ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid));
        $player = $stmt->fetch(\PDO::FETCH_OBJ);
        return json_decode($player->all_max);
    } 

	function inital_data($sid){//初始化用户临时数据存储字段
		$sql = "UPDATE `game1` SET `ut_val` = '{}', `us_val` = '{}', `all_max` = '{}' WHERE `sid` = ?;";
		$stmt = $this->dblj->prepare($sql);
		return $stmt->execute(array($sid));
	}
} 

?>