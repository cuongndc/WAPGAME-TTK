<?php 
// 游戏道具管理类
namespace game_system;

class goods {
    public $dblj;
    public $sid;
    public $uid;
    public $sys;
    public $map;

    function __construct() {
        global $dblj;
        global $sys;
        global $map;
        $this->dblj = $dblj;
        $this->sys = $sys;
        $this->map = $map;
        if (!isset($_SESSION['sid'])) {
            return;
        } 
        $this->sid = $_SESSION['sid'];
        $this->uid = $_SESSION['uid'];
        $this->token = $_SESSION['token'];
    } 

    function goods_type_load($type, $page = 1, $recPerPage = 20) { // 加载特定物品
        $clas = $this->get_goods_type($type);
        $alert = alert_open;
        $list = $this->get_goods_all($clas[0], 0, "", $page, $recPerPage);
        if (is_object($list)) {
            foreach($list->data as $obj) {
                $qyname = $this->map->get_qy_name($obj->qy);
                ++$i;
                $html .= <<<html
	<tr>
      <td>{$i} .{$obj->name}({$obj->id})</td>
      <td>{$qyname}</td>
      <td>
	  <a class="btn btn-primary" href="goods.php?com=edit&id={$obj->id}">修改</a>
	  <button class="btn btn-danger" type="button" {$alert} onclick="del_goods('{$obj->id}')">删除</button>
	  </td>
    </tr>
html;
            } 
        } 
        return array('list' => $html, 'recPerPage' => $recPerPage, 'recTotal' => $list->num);
    } 

    function get_goods_all($type, $qyid = 0, $name = "", $page = 1, $recPerPage = 20) { // 获取指定类型的所有物品
        $page = intval($page);
        $recPerPage = intval($recPerPage);
        $page = ($page-1) * $recPerPage;
        $obj = (object)[];
        if ($type == "all") {
            $sql = "SELECT * FROM `daoju`";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute();
        } else {
            $type = $this->get_goods_type($type);
            if ($qyid != 0) {
                $sql = "SELECT * FROM `daoju` WHERE `type` = ? AND qy = ?";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($type[0], $qyid));
            } else {
                $sql = "SELECT * FROM `daoju` WHERE `type` = ? ";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($type[0]));
            } 
        } 
        $obj->num = $stmt->rowCount();

        if ($recPerPage != 0) {
            $sql .= " limit {$page},{$recPerPage};";
            $stmt = $this->dblj->prepare($sql);
            if (intval($qyid) != 0) {
                $stmt->execute(array($type[0], $qyid));
            } else {
                $stmt->execute(array($type[0]));
            } 
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_goods_info($id) { // 根据ID获取一条物品数据
        $sql = "SELECT * FROM `daoju` WHERE id = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $goods = $stmt->fetch(\PDO::FETCH_OBJ);
        return $goods;
    } 

    function get_goods_name($name, $m = 0, $n = 20) { // 根据物品名模糊查找获取一条物品数据
        $m = intval($m);
        $n = intval($n);
        $sql = "SELECT * FROM `daoju` WHERE name like ? limit $m,$n;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array('%' . $name . '%'));
        $goods = $stmt->fetchALL(\PDO::FETCH_OBJ);
        return $goods;
    } 

    function isPermit($str) {
        $isMatched = preg_match('/^[_0-9a-z]{3,16}$/', $str, $matches);
        if ($isMatched == 1) {
            return true;
        } else {
            return false;
        } 
    } 

    function get_goods_type($val) { // 读取物品分类数据
        switch ($val) {
            case "#consume":
            case "consume":
                $clas = array("consume", "消耗品");
                break;
            case "#book":
            case "book":
                $clas = array("book", "书籍");
                break;
            case "#taskitems":
            case "taskitems":
                $clas = array("taskitems", "任务物品");
                break;
            case "#other":
            case "other":
                $clas = array("other", "其他");
                break;
        } 
        return $clas;
    } 

    function load_event_list($id) { // 加载物品事件列表
        $obj_info = $this->get_goods_info($id);
        $path = "goods";
        $id = $obj_info->id;
        $alert_open = alert_open;
        $link = "path={$path}&key={$id}";
        $html = <<<html
			<h3>编辑物品"{$obj_info->name}"的事件：</h3>
<table class="table table-condensed">
  <thead>
    <tr>
      <th>名称(ID)</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody id="event-npc-list">
  <tr><td>1 .创建事件</td><td>
html;
        if ($obj_info->event_create) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=create\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','create','{$obj_info->id}','{$obj_info->event_create}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=create\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>2 .查看事件</td><td>
";
        if ($obj_info->event_watch) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&path=map&key={$obj_info->id}&clas=watch\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','watch','{$obj_info->id}','{$obj_info->event_watch}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=watch\">设置事件</a>";
        } 

        $html .= "</td></tr>
<tr><td>3 .使用事件</td><td>
";
        if ($obj_info->event_use) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=use\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','use','{$obj_info->id}','{$obj_info->event_enter}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=use\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>4 .存储数据事件</td><td>
";
        if ($obj_info->event_save) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=save\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','save','{$obj_info->id}','{$obj_info->event_leave}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=save\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>5.导出数据事件</td><td>
";
        if ($obj_info->event_backups) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=backups\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','backups','{$obj_info->id}','{$obj_info->event_leave}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=backups\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>6 .分钟定时事件</td><td>
";
        if ($obj_info->event_timing) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=timing\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','timing','{$obj_info->id}','{$obj_info->event_timing}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=timing\">设置事件</a>";
        } 
        $html .= "</td></tr></tbody>
</table>
";
        return $html;
    } 

    function set_goods_field($id, $type, $value = null) { // 更新一个物品
        switch ($type) {
            case "task":
            case "edit_task":
                $field = "task";
                break;
            case "operation":
                $field = "operation";
                break;
            case 'event_create':
                $field = "event_create";
                break;
            case 'event_watch':
                $field = "event_watch";
                break;
            case 'event_use':
                $field = "event_use";
                break;
            case 'event_wear':
                $field = "event_wear";
                break;
            case 'event_undress':
                $field = "event_undress";
                break;
            case 'event_save':
                $field = "event_save";
                break;
            case 'event_backups':
                $field = "event_backups";
                break;
            case 'event_timing':
                $field = "event_timing";
                break;
        } 
        if ($value == null) {
            $sql = "UPDATE `daoju` SET {$field} = null WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($id));
        } else {
            $sql = "UPDATE `daoju` SET {$field} = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($value, $id));
        } 
        return $ret;
    } 

    function get_goods_run($gid) {
        $sql = "select * from mid_goods where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($gid));
        $guaiwu = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$guaiwu) {
            return false;
        } 
        $yguaiwu = $this->get_goods_info($guaiwu->gyid);
        $guaiwu->gsex = $yguaiwu->gsex;
        $guaiwu->ginfo = $yguaiwu->ginfo;

        $udc_gw_ranges = $this->sys->get_system_config("游戏", "怪物层次分级");
        if (empty($udc_gw_ranges)) {
            // default
            $ranges_lv = array(0, 30, 50, 70, 80, 90, 100, 110);
        } else {
            $ranges_lv = explode('|', $udc_gw_ranges);
            if (count($ranges_lv) < 8) {
                // 默认30级一个层次
                for ($i = count($ranges_lv); $i < 8; $i++) {
                    $ranges_lv[] = $ranges_lv[count($ranges_lv)-1] + 30;
                } 
            } 
        } 

        $udc_gw_jj = $this->sys->get_system_config("游戏", "怪物层次定义");
        if (!empty($udc_gw_jj)) {
            $tmpArr = explode('|', $udc_gw_jj);
            if (count($tmpArr) < 8) {
                for ($i = count($tmpArr); $i < 8; $i++) {
                    $tmpArr[] = '层次' . $i;
                } 
            } 
            $层次1 = $tmpArr[0];
            $层次2 = $tmpArr[1];
            $层次3 = $tmpArr[2];
            $层次4 = $tmpArr[3];
            $层次5 = $tmpArr[4];
            $层次6 = $tmpArr[5];
            $层次7 = $tmpArr[6];
            $层次8 = $tmpArr[7];
        } else {
            // default
            $层次1 = $this->sys->get_system_config("游戏", "层次1");
            $层次2 = $this->sys->get_system_config("游戏", "层次2");
            $层次3 = $this->sys->get_system_config("游戏", "层次3");
            $层次4 = $this->sys->get_system_config("游戏", "层次4");
            $层次5 = $this->sys->get_system_config("游戏", "层次5");
            $层次6 = $this->sys->get_system_config("游戏", "层次6");
            $层次7 = $this->sys->get_system_config("游戏", "层次7");
            $层次8 = $this->sys->get_system_config("游戏", "层次8");
        } 

        $ranges_jj = array($层次1 , $层次2 , $层次3 , $层次4 , $层次5 , $层次6 , $层次7 , $层次8);

        for ($i = 0 ; $i < count($ranges_lv) ; $i++) {
            $lv = $ranges_lv[$i];
            $lv1 = $ranges_lv[$i + 1];

            if ($guaiwu->glv >= $ranges_lv[$i] && $guaiwu->glv < $ranges_lv[$i + 1]) {
                $ranges_jd = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
                $djc = $guaiwu->glv - $lv;
                $jds = ($lv1 - $lv) / 10;
                $j = (int) floor($djc / $jds);

                $jd = $ranges_jd[$j];
                $guaiwu->jingjie = $ranges_jj[$i] . $jd . '层';

                break;
            } 
        } 

        return $guaiwu;
    } 

    function get_player_goods($sid, $type, $clas = null, $tool = null) {
        switch ($type) {
            case 'goods':
                $sql = "select * from playerdaoju where sid = ? and (type = ? or type = ? or type =?)";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($sid, 'book', 'other', 'taskitems'));
                break;
            default:
                if (isset($clas) || (isset($tool) && $tool != 0)) {
                    if (isset($clas) && isset($tool) && $tool != 0) {
                        $sql = "select * from playerdaoju where sid = ? and type = ? and clas = ? and tool =?";
                        $stmt = $this->dblj->prepare($sql);
                        $stmt->execute(array($sid, $type, $clas, $tool));
                    } elseif (isset($clas)) {
                        $sql = "select * from playerdaoju where sid = ? and type = ? and clas = ?";
                        $stmt = $this->dblj->prepare($sql);
                        $stmt->execute(array($sid, $type, $clas));
                    } elseif (isset($tool) && $tool != 0) {
                        $sql = "select * from playerdaoju where sid = ? and type = ? and tool = ?";
                        $stmt = $this->dblj->prepare($sql);
                        $stmt->execute(array($sid, $type, $tool));
                    } 
                } else {
                    $sql = "select * from playerdaoju where sid = ? and type = ?";
                    $stmt = $this->dblj->prepare($sql);
                    $stmt->execute(array($sid, $type));
                } 
        } 
        return $stmt->fetchALL(\PDO::FETCH_OBJ);
    } 

    function get_player_goods_info($djid , $sid) { // 获取玩家物品信息
        $sql = "select * from playerdaoju where sid = ? and id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid , $djid));
        return $stmt->fetch(\PDO::FETCH_OBJ);
    } 

    function get_player_goods_initial($djid , $sid) { // 获取玩家物品系统ID信息
        $sql = "select * from playerdaoju where sid = ? and initial_id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($sid , $djid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function set_player_goods_field($id, $type, $value = null) { // 更新一个玩家物品
        switch ($type) {
            case "in_use":
                $field = "in_use";
                break;
        } 
        if ($value == null) {
            $sql = "UPDATE `playerdaoju` SET {$field} = null WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($id));
        } else {
            $sql = "UPDATE `playerdaoju` SET {$field} = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($value, $id));
        } 
        return $ret;
    } 

    function use_player_consume($sid, $ypid , $sum) {//玩家使用药品类物品
        global $player;
        $player_info = $player->get_player_info($sid);
        $user_yp = $this->get_player_goods_info($ypid , $sid);
        $sys_yp = $this->get_goods_info($user_yp->initial_id , $sid);
        $user_yp = G_convertObjectClass($sys_yp, $user_yp);
        $target = $user_yp->use_attr;
        $effect = $user_yp->use_value;
		if($user_yp->number <= 0){
			echo   '你的背包里已经已经没有'. $user_yp->name .'这个物品了！';
            return false;
		}
        if ($player_info->hp <= 0) {
            return false;
        } 
            $max = $player->get_player_max($sid);
            $max_target = "max_{$target}";
            if (isset($max->$max_target)) {
                $max = intval($max->$max_target->val);
                if ((intval($player_info->$target) + intval($effect)) < $max) {
					$player->alter_attr_field($sid,'u',$target,$effect);
					$use = true;
                } elseif(intval($player_info->$target) == $max) {
                    echo $user_yp->name .'使用失败！';
					return false;
                } else {
					$player->alter_attr_field($sid,'u',$target,$max - $player_info->hp);
					$use = true;
				}
            } elseif (true) {
            } else {
            } 
		if($use){
			$use = $this->reduce_player_goods($user_yp->initial_id , 2 , $sum , $sid);
		}
		return $use;
    } 

    function reduce_player_goods($sysid , $lx , $sum , $sid = null) { // 变更玩家物品数量
        if (!isset($sid)) {
            $sid = $this->sid;
        } ;
        $goods = $this->get_player_goods_initial($sysid, $sid);
        switch ($lx) {
            case 1:
                $mark = "+";
                $sql = "update playerdaoju set number = number + ? WHERE id = ? AND sid = ?";
                break;
            case 2:
                if ($goods) {
                    if ($goods->number < $sum) {
                        return false;
                    } 
                    $sql = "update playerdaoju set number = number - ? WHERE id = ? AND sid = ?";
                    $mark = "-";
                } else {
                    return false;
                } 
                break;
        } 
        $stmt = $this->dblj->prepare($sql);
        $exeres = $stmt->execute(array($sum , $goods->id , $sid));
        if ($exeres) {
            echo "{$goods->name}{$mark}{$sum}<br>";
        } 
        return $exeres;
    } 

    function goods_pickup_mid($sid, $run_id) { // 玩家从地图捡起物品
        $sql = "SELECT * FROM `mid_goods` where id = ?;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($run_id));
        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        $sql = "DELETE FROM mid_goods WHERE id = ?;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($obj->id));
        $sql = "SELECT * FROM `daoju` WHERE id = ?;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($obj->gid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ); 
        // 玩家插入语句
        $goods = $this->get_player_goods_initial($obj->gid, $sid);
        if (!$goods || isset($goods->Grade)) {
            $sql = "INSERT INTO `playerdaoju`( `initial_id`, `name`, `sid`, `type`, `clas`, `tool`, `attack_value`, `resist_value`, `embed_count`, `number` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $this->dblj->prepare($sql);
            $exeres = $stmt->execute(array($ret->id, $ret->name, $sid, $ret->type, $ret->clas, $ret->tool, $ret->attack_value, $ret->resist_value, $ret->embed_count, $obj->gnum));
        } else {
            $sql = "UPDATE `playerdaoju` SET `number` = ifnull(`number`,0) + ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $exeres = $stmt->execute(array($obj->gnum, $goods->id));
        } 
        if ($exeres) {
            echo "{$ret->name}+{$obj->gnum}<br>";
        } 
        return $exeres;
    } 

	function use_player_equip($sid ,$equip_id , $clas){ //玩家使用装备
		global $player;
		global $player_info;
		$bool = $this->remove_player_equip($sid ,$equip_id, $clas);
		if(!$bool){return false;};
		$obj_info = $this->get_player_goods_info($equip_id, $sid);
        if (isset($obj_info)) {
            echo  "装备使用成功<br>";
            if ($clas == 'weapon') {
                $player->set_player_us($sid, $clas, $obj_info->id);
                $this->set_player_goods_field($equip_id, 'in_use', 1);
                $player->set_player_us($sid, 'attack', $obj_info->attack_value);
            } else {
                $player->set_player_us($sid, "equip{$obj_info->tool}", $obj_info->id);
                $this->set_player_goods_field($equip_id, 'in_use', 1);
                $player->set_player_us($sid, 'resistance', intval($obj_info->resist_value), '+');
            } 
			$player_info = $player->get_player_info();
			return true;
        } else {
            echo  "你翻遍了包裹，却没有找到想要使用的这件装备！<br>";
			return false;
        } 
	}
	
	function remove_player_equip($sid ,$equip_id, $clas){ //玩家卸下装备
		global $player;
		global $player_info;
	    $obj_info = $this->get_player_goods_info($equip_id, $sid);
        if (isset($obj_info)) {
            echo  "装备卸下成功<br>";
            if ($clas == 'weapon') {
                $player->set_player_us($sid, $clas);
                $this->set_player_goods_field($equip_id, 'in_use', 0);
                $player->set_player_us($sid, 'attack', 0);
            } else {
                $player->set_player_us($sid, "equip{$obj_info->tool}");
                $this->set_player_goods_field($equip_id, 'in_use', 0);
                $player->set_player_us($sid, 'resistance', intval($obj_info->resist_value), '-');
            } 
            $player_info = $player->get_player_info();
			return true;
        } else {
            echo "你尝试了多种办法，废了九牛二虎之力，却依然没能卸下这件装备！<br>";
			return false;
        } 
	} 
	
}	
?>