<?php 
// 地图配置操作定义
namespace game_system;

class mid {
    public $dblj;
    public $sys;
    function __construct() {
        global $dblj;
        global $sys;
        $this->dblj = $dblj;
        $this->sys = $sys;
    } 

    function mid_get_player_online($mid) { // 获取当前地图玩家
        $sql = "select * from game1 where nowmid = ? AND sfzx = 1";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $ret;
    } 

    function mid_delete_guaiwu_player() { // 删除地图该玩家已经被攻击怪物
        $sql = "delete from midguaiwu where sid = ?";
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($this->sid));
        return $ret;
    } 

    function get_mid_info($id) { // 获取地图信息
        $sql = "select * from mid where id = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($id));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function get_name_mid($name, $page = 1, $count = 20) { // 根据地图名获取地图列表
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $obj = json_decode('{}');
        $sql = "select * from mid where name like ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array("%" . $name . "%"));
        $obj->num = $stmt->rowCount();
        if ($count != 0) {
            $sql .= "limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array("%" . $name . "%"));
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_mid_qyid($mid) { // 获取地图所在区域id
        $sql = "SELECT qy FROM `mid` WHERE id= ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret->qy;
    } 

    function get_name_qymid($qyid , $mname, $page = 1, $count = 20) { // 获取区域id下地图名相关地图信息
        $qyid = intval($qyid);
        if ($mname == "") {
            return $this->get_qy_mid($qyid , $page, $count);
        } ;
        if ($mname == "" && $qyid == 0) {
            return $this->get_qy_mid($qyid , $page, $count);
        } ;
        $page = intval($page);
        $count = intval($count);
        $page = ($page - 1) * $count;
        $obj = json_decode('{}');
        if ($qyid != 0) {
            $sql = "select * from mid where qy = ? and name like ? ";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($qyid , "%" . $mname . "%"));
            $obj->num = $stmt->rowCount();
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($qyid , "%" . $mname . "%"));
        } else {
            $sql = "select * from mid where name like ? ";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array("%" . $mname . "%"));
            $obj->num = $stmt->rowCount();
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array("%" . $mname . "%"));
        } 
        $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $obj;
    } 

    function get_qy_mid($qyid , $page = 1, $count = 20) { // 获取区域下地图列表
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $obj = (object)[];
        if (intval($qyid) != 0) {
            $sql = "select * from mid where qy = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($qyid));
        } else {
            $sql = "select * from mid where qy is null";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute();
        } 
        $obj->num = $stmt->rowCount();
        if ($count != 0) {
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            if (intval($qyid) != 0) {
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute(array($qyid));
            } else {
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute();
            } 
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_qy_name($qyid) { // 获取区域名与id组合
        $sql = "select qyname,qyid from `qy` WHERE qyid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($qyid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret->qyname . "(" . $ret->qyid . ")" ;
    } 

    function get_qy_info($qyid) { // 读取指定区域
        $sql = "select * from `qy` WHERE qyid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($qyid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function del_mid_id($id) { // 删除一个已经创建的地图
        global $operation;
        global $event;
        $map_info = $this->get_mid_info($id);
        try {
            $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dblj->beginTransaction();
            $sql = "UPDATE `mid` SET `mup` = null WHERE `mup` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($map_info->id));
            $sql = "UPDATE `mid` SET `mdown` = null WHERE `mdown` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($map_info->id));
            $sql = "UPDATE `mid` SET `mleft` = null WHERE `mleft` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($map_info->id));
            $sql = "UPDATE `mid` SET `mright` = null WHERE `mright` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($map_info->id));
            if ($obj_info->operation) {
                $arry = explode(',', $data);
                if (is_array($arry)) {
                    foreach ($arry as $val) {
                        $operation->del_operation($val);
                    } 
                } 
            } 
            if ($map_info->event_create) {
                $event->del_event($map_info->event_create);
            } 
            if ($map_info->event_watch) {
                $event->del_event($map_info->event_watch);
            } 
            if ($map_info->event_enter) {
                $event->del_event($map_info->event_enter);
            } 
            if ($map_info->event_leave) {
                $event->del_event($map_info->event_leave);
            } 
            if ($map_info->event_timing) {
                $event->del_event($map_info->event_timing);
            } 
            $stmt = $this->dblj->prepare('DELETE FROM mid WHERE id = ?;');
            $stmt->execute(array($map_info->id));
            if ($stmt->rowCount() == 1) {
                $bool = $this->dblj->commit();
            } 
        } 
        catch(Exception $e) {
            $this->dblj->rollback();
            $bool = false;
        } 
        return $bool;
    } 

    function del_qy_id($id) { // 删除一个已定义的区域信息
        try {
            $this->dblj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dblj->beginTransaction();
            $sql = "UPDATE `mid` SET  `qy` =  null where qy = ? ;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($id));
            $sql = "UPDATE `npc` SET  `qy` =  null where qy = ? ;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($id));
            $sql = "DELETE FROM qy WHERE qyid = ?;";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($id));
            $bool = $this->dblj->commit();
        } 
        catch(Exception $e) {
            $this->dblj->rollback();
            $bool = false;
        } 
        return $bool;
    } 

    function get_name_qy($qyname, $page = 1, $count = 20) { // 根据区域名查询区域列表
        if ($qyname == "") {
            return $this->get_qy_all($page, $count);
        } ;
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $obj = json_decode('{}');
        $sql = "select * from qy WHERE qyname like ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array("%" . $qyname . "%"));
        $obj->num = $stmt->rowCount();
        if ($count != 0) {
            $sql .= " limit {$page},{$count};";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array("%" . $qyname . "%"));
            $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } 
        return $obj;
    } 

    function get_qy_all($page = 1, $count = 20) { // 获取所有区域
        $page = intval($page);
        $count = intval($count);
        $page = ($page-1) * $count;
        $obj = json_decode('{}');
        $sql = "select * from `qy` ";
        $cxjg = $this->dblj->query($sql);
        $obj->num = $cxjg->rowCount();
        if ($count != 0) {
            $sql .= " limit {$page},{$count};";
            $cxjg = $this->dblj->query($sql);
        } 
        $obj->data = $cxjg->fetchAll(\PDO::FETCH_OBJ);
        return $obj;
    } 

    function set_mid_exit_on($exitype, $nexit, $newqy, $newmid, $yexid, $mapid) { // 修改地图出口链接
        switch ($exitype) {
            case "single":// 单向出口
                switch ($nexit) {
                    case "mup":
                        $sql = "UPDATE `mid` SET `mup` = ? WHERE `id` = ?;";
                        break;
                    case "mdown":
                        $sql = "UPDATE `mid` SET `mdown` = ? WHERE `id` = ?;";
                        break;
                    case "mleft":
                        $sql = "UPDATE `mid` SET `mleft` = ? WHERE `id` = ?;";
                        break;
                    case "mright":
                        $sql = "UPDATE `mid` SET `mright` = ?  WHERE `id` = ?;";
                        break;
                } 
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute(array($newmid, $mapid));
                return $ret;
                break;
            case "double":// 双向出口
                switch ($nexit) {
                    case "mup":
                        $sqla = "UPDATE `mid` SET `mup` = ? WHERE `id` = ?;";
                        $sqlb = "UPDATE `mid` SET `mdown` = ? WHERE `id` = ?;";
                        break;
                    case "mdown":
                        $sqla = "UPDATE `mid` SET `mdown` = ? WHERE `id` = ?;";
                        $sqlb = "UPDATE `mid` SET `mup` = ? WHERE `id` = ?;";
                        break;
                    case "mleft":
                        $sqla = "UPDATE `mid` SET `mleft` = ? WHERE `id` = ?;";
                        $sqlb = "UPDATE `mid` SET `mright` = ? WHERE `id` = ?;";
                        break;
                    case "mright":
                        $sqla = "UPDATE `mid` SET `mright` = ?  WHERE `id` = ?;";
                        $sqlb = "UPDATE `mid` SET `mleft` = ? WHERE `id` = ?;";
                        break;
                } 
                $stmt = $this->dblj->prepare($sqla);
                $reta = $stmt->execute(array($newmid, $mapid));
                $stmt = $this->dblj->prepare($sqlb);
                $retb = $stmt->execute(array($mapid, $newmid));
                return $reta == $retb;
                break;
        } 
    } 

    function set_mid_exit_off($mid, $exit, $id, $brea) {
        switch ($brea) {
            case "single":// 单向出口
                switch ($exit) {
                    case "mup":
                        $sql = "UPDATE `mid` SET `mup` = null WHERE `mid` = ?;";
                        break;
                    case "mdown":
                        $sql = "UPDATE `mid` SET `mdown` = null WHERE `mid` = ?;";
                        break;
                    case "mleft":
                        $sql = "UPDATE `mid` SET `mleft` = null WHERE `mid` = ?;";
                        break;
                    case "mright":
                        $sql = "UPDATE `mid` SET `mright` = null  WHERE `mid` = ?;";
                        break;
                } 
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute(array($mid));
                return $ret;
                break;
            case "double":// 双向出口
                switch ($exit) {
                    case "mup":
                        $sqla = "UPDATE `mid` SET `mup` = null WHERE `mid` = ?;";
                        $sqlb = "UPDATE `mid` SET `mdown` = null WHERE `mid` = ?;";
                        break;
                    case "mdown":
                        $sqla = "UPDATE `mid` SET `mdown` = null WHERE `mid` = ?;";
                        $sqlb = "UPDATE `mid` SET `mup` = null WHERE `mid` = ?;";
                        break;
                    case "mleft":
                        $sqla = "UPDATE `mid` SET `mleft` = null WHERE `mid` = ?;";
                        $sqlb = "UPDATE `mid` SET `mright` = null WHERE `mid` = ?;";
                        break;
                    case "mright":
                        $sqla = "UPDATE `mid` SET `mright` = null  WHERE `mid` = ?;";
                        $sqlb = "UPDATE `mid` SET `mleft` = null WHERE `mid` = ?;";
                        break;
                } 
                $stmt = $this->dblj->prepare($sqla);
                $reta = $stmt->execute(array($mid));
                $stmt = $this->dblj->prepare($sqlb);
                $retb = $stmt->execute(array($id));
                return $reta == $retb;
                break;
        } 
    } 

    function load_event_list($id) { // 加载地图事件列表
        $map_info = $this->get_mid_info($id);
        $path = "map";
        $id = $map_info->id;
        $alert_open = alert_open;
        $link = "path={$path}&key={$id}";
        $html = <<<html
			<h3>编辑场景"{$map_info->name}"的事件：</h3>
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
        if ($map_info->event_create) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=create\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','create','{$map_info->id}','{$map_info->event_create}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=create\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>2 .查看事件</td><td>
";
        if ($map_info->event_watch) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&path=map&key={$map_info->id}&clas=watch\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','watch','{$map_info->id}','{$map_info->event_watch}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=watch\">设置事件</a>";
        } 

        $html .= "</td></tr>
<tr><td>3 .进入事件</td><td>
";
        if ($map_info->event_enter) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=enter\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','enter','{$map_info->id}','{$map_info->event_enter}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=enter\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>4 .离开事件</td><td>
";
        if ($map_info->event_leave) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=leave\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','leave','{$map_info->id}','{$map_info->event_leave}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=leave\">设置事件</a>";
        } 
        $html .= "</td></tr>
<tr><td>5 .分钟定时事件</td><td>
";
        if ($map_info->event_timing) {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=edit&{$link}&clas=timing\">修改事件</a> ";
            $html .= <<<html
	<button class="btn btn-danger " type="button" {$alert_open} onClick="del_event('{$path}','timing','{$map_info->id}','{$map_info->event_timing}')">删除</button>
html;
        } else {
            $html .= "<a class=\"btn btn-primary\" href=\"event.php?type=add&{$link}&clas=timing\">设置事件</a>";
        } 
        $html .= "</td></tr></tbody>
</table>
";
        return $html;
    } 

    function set_mid_edit($type, $arry) { // 维护或新建地图数据
        $value = array();
        switch ($type) {
            case "edit":
                foreach($arry as $k => $v) {
                    if (G_isPermit($k)) {
                        if ($k == "mid") {
                            $id = $v;
                        } 
                        if ($v == "datetime") {
                            $v = date("Y-m-d H:i", time());
                        } 
                        $field .= ",`" . $k . "` = ? ";
                        array_push($value, $v);
                    } 
                } 
                $field = substr($field, 1);
                $sql = "UPDATE `mid` SET $field WHERE `mid` = ?;";
                array_push($value, $id);
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute($value);
                return $ret;
                break;
            case "new":
                foreach($arry as $k => $v) {
                    if (G_isPermit($k) && $k != "mid") {
                        $field .= ",`" . $k . "`";
                        $val .= ",?";
                        if ($v == "datetime") {
                            $v = date("Y-m-d H:i", time());
                        } 
                        array_push($value, $v);
                    } 
                } 
                $field = substr($field, 1);
                $val = substr($val, 1);
                $sql = "INSERT INTO `mid`($field) VALUES ($val);";
                $stmt = $this->dblj->prepare($sql);
                $ret = $stmt->execute($value); 
                // $idd=$stmt::insert_id();
                // echo $idd;
                // exit;
                return $ret;
                break;
        } 
    } 

    function set_qy_gl($qyid, $qyname, $qydesc, $mid) { // 维护或新建区域
        $sql = 'INSERT INTO `qy` (`qyid`,`qyname`,`qydesc`, `mid` ) values (?,?,?,?) ON DUPLICATE KEY UPDATE `qyname` = ?,`qydesc`=? ,`mid` = ?';
        $stmt = $this->dblj->prepare($sql);
        $ret = $stmt->execute(array($qyid , $qyname , $qydesc, $mid , $qyname , $qydesc, $mid));
        return $ret;
    } 

    function set_mid_field($mapid, $type, $val = null) { // 更新一个地图字段
        switch ($type) {
            case "npc":
                $field = "npc";
                break;
            case "skills":
                $field = "skills";
                break;
            case "goods":
                $field = "goods";
                break;
            case "task":
                $field = "task";
                break;
            case "operation":
                $field = "operation";
                break;
            case "event_create":
                $field = "event_create";
                break;
            case "event_watch":
                $field = "event_watch";
                break;
            case "event_enter":
                $field = "event_enter";
                break;
            case "event_leave":
                $field = "event_leave";
                break;
            case "event_timing":
                $field = "event_timing";
                break;
        } 
        if (!$field) {
            return false;
        } 
        if (isset($val)) {
            $sql = "UPDATE `mid` SET {$field} = ? WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($val, $mapid));
        } else {
            $sql = "UPDATE `mid` SET {$field} = null WHERE `id` = ?;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($mapid));
        } 
        return $ret;
    } 

    function get_mid_idname($Exit, $mapid) { // 获取出口地图名与id组合
        $map = $this->get_mid_info($mapid);
        $m_mid = $map->$Exit;
        if (isset($m_mid) && $m_mid != 0) {
            $map = $this->get_mid_info($m_mid);
            if ($map->id != 0) {
                $obj = json_decode('{}');
                $obj->id = $map->id;
                $obj->name = $map->name;
                return $obj;
            } else {
                return ;
            } 
        } 
        return ;
    } 

    function mid_get_out_mup($map) { // 获取出口链接 上
        $outhtml = "";
        if ($map->mup) {
            $m_mid = $map->mup;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}↑");
            $outhtml = $murl;
        } 
        return $outhtml;
    } 

    function mid_get_out_mdown($map) { // 获取出口链接 下
        $outhtml = "";
        if ($map->mdown) {
            $m_mid = $map->mdown;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}↓");
            $outhtml = $murl;
        } 
        return $outhtml;
    } 

    function mid_get_out_mleft($map) { // 获取出口链接 左
        $outhtml = "";
        if ($map->mleft) {
            $m_mid = $map->mleft;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}←");
            $outhtml = $murl;
        } 
        return $outhtml;
    } 

    function mid_get_out_mright($map) { // 获取出口链接 右
        $outhtml = "";
        if ($map->mright) {
            $m_mid = $map->mright;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}→");
            $outhtml = $murl;
        } 
        return $outhtml;
    } 

    function mid_get_out($map) { // 获取出口链接 集合
        $outhtml = '';
        if ($map->mup) {
            $m_mid = $map->mup;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}↑");
            $outhtml .= "$murl<br/>";
        } 

        if ($map->mleft) {
            $m_mid = $map->mleft;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}←");
            $outhtml .= "$murl<br/>";
        } 

        if ($map->mdown) {
            $m_mid = $map->mdown;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}↓");
            $outhtml .= "$murl<br/>";
        } 

        if ($map->mright) {
            $m_mid = $map->mright;
            $m_midinfo = $this->get_mid_info($m_mid);
            $murl = $this->sys->create_url("cmd=mid&cmd2=gomid&gomid=$m_mid", "{$m_midinfo->name}→");
            $outhtml .= "$murl<br/>";
        } 

        return $outhtml;
    } 

    function load_map_data($mid) { // 载入地图数据到内存数据表
        $map_info = $this->get_mid_info($mid);
        $sql = "SELECT * FROM `run_mid` WHERE id = ? and  ( TimeStampDiff(minute , mgtime, current_timestamp()) < refresh )";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetchAll(\PDO::FETCH_OBJ);
        if (count($ret) == 0) {
            $sql = "replace into `run_mid` (`id`, `name`, `mgtime`, `refresh`, `desc`, `midboss`, `mup`, `mdown`, `mleft`, `mright`, `qy`, `playerinfo`, `ispvp`, `npc`, `goods`, `task`, `operation`, `display_play`, `display_playname`, `display_npc`, `display_npcname`, `display_goods`, `display_goodsname`, `event_create`, `event_watch`, `event_enter`, `event_leave`, `event_timing`) VALUES ( :id, :name,  :mgtime,  :refresh, :desc, :midboss,  :mup,  :mdown,  :mleft,  :mright, :qy, :playerinfo,  :ispvp,  :npc, :goods, :task,  :operation,  :display_play,  :display_playname,  :display_npc, :display_npcname, :display_goods,  :display_goodsname,  :event_create, :event_watch,  :event_enter,  :event_leave,  :event_timing );";
            $stmt = $this->dblj->prepare($sql);
            $stmt->bindParam(':id', $map_info->id);
            $stmt->bindParam(':name', $map_info->name);
            $stmt->bindParam(':mgtime', date('Y-m-d H:i:s', time()));
            $stmt->bindParam(':refresh', $map_info->refresh);
            $stmt->bindParam(':desc', $map_info->desc);
            $stmt->bindParam(':midboss', $map_info->midboss);
            $stmt->bindParam(':mup', $map_info->mup);
            $stmt->bindParam(':mdown', $map_info->mdown);
            $stmt->bindParam(':mleft', $map_info->mleft);
            $stmt->bindParam(':mright', $map_info->mright);
            $stmt->bindParam(':qy', $map_info->qy);
            $stmt->bindParam(':playerinfo', $map_info->playerinfo);
            $stmt->bindParam(':ispvp', $map_info->ispvp);
            $stmt->bindParam(':npc', $map_info->npc);
            $stmt->bindParam(':goods', $map_info->goods);
            $stmt->bindParam(':task', $map_info->task);
            $stmt->bindParam(':operation', $map_info->operation);
            $stmt->bindParam(':display_play', $map_info->display_play);
            $stmt->bindParam(':display_playname', $map_info->display_playname);
            $stmt->bindParam(':display_npc', $map_info->display_npc);
            $stmt->bindParam(':display_npcname', $map_info->display_npcname);
            $stmt->bindParam(':display_goods', $map_info->display_goods);
            $stmt->bindParam(':display_goodsname', $map_info->display_goodsname);
            $stmt->bindParam(':event_create', $map_info->event_create);
            $stmt->bindParam(':event_watch', $map_info->event_watch);
            $stmt->bindParam(':event_enter', $map_info->event_enter);
            $stmt->bindParam(':event_leave', $map_info->event_leave);
            $stmt->bindParam(':event_timing', $map_info->event_timing);
            $stmt->execute();

            $npc_list = json_decode($map_info->npc);
            $sql = "DELETE from mid_npc WHERE gmid = ?;";
            $arr = array($mid);
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute($arr);

            if (is_object($npc_list) && count($npc_list) > 0) {
                foreach ($npc_list as $obj) {
                    if (is_object($obj)) {
                        if ($obj->num > 1) {
                            $npc_arry = rand_section( $obj->num / 3, $obj->num / 2, $obj->num);
                            foreach($npc_arry as $num) {
								if($num !=0){
                                $this->mid_add_npc($obj->id , $mid , $num , $i++);
								}
                            } 
                        } else {
                            $this->mid_add_npc($obj->id , $mid , $obj->num);
                        } 
                    } 
                } 
            } 

            $goods_list = json_decode($map_info->goods);
            $sql = "DELETE from mid_goods WHERE gmid = ?;";
            $arr = array($mid);
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute($arr);
            $result = $stmt->rowCount();
            if (is_object($goods_list) && count($goods_list) > 0) {
                foreach ($goods_list as $obj) {
                    if (is_object($obj)) {
                        $this->mid_add_goods($obj->id , $mid , $obj->num);
                    } 
                } 
            } 
        } ;
    } 

    function mid_add_goods($gid , $mid , $num) { // 向地图物品数据表写入物品数据
        global $goods;
        $sql = "SELECT * FROM `mid_goods` WHERE gid = ? and gmid = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($gid, $mid));
        $gw = $stmt->fetch(\PDO::FETCH_OBJ);
        if ($gw) {
            $interval = timediff($gw->gcreate_time, date('Y-m-d H:i:s', time())) ; 
            // var_dump($interval['min'], $interval, $gw->grefresh);
            if ($interval['min'] > $gw->grefresh) {
                $update = true;
            } else {
                $update = false;
            } ;
        } else {
            $update = true;
        } ;

        if (!$update) {
            $result = true; //echo "保持原数据不变<br>";
        } else {
            // echo "准备更新当前物品数据<br>";
            $gw = $goods->get_goods_info($gid);
            if (is_object($gw)) {
                $sql = "INSERT INTO mid_goods(`gid`, `gname`, `grefresh`,`gcreate_time`, `gmid`, `ghp`, `ggj`, `gfy`, `glv`, `gyid`, `gexp`, `sid`, `gmaxhp`, `gbj`, `gxx`, `gnum`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE `gname` = ?, `grefresh` = ?, `gcreate_time` = ?, `gmid` = ?, `ghp` = ?, `ggj` = ?, `gfy` = ?, `glv` = ?, `gyid` = ?, `gexp` = ?, `sid` = ?, `gmaxhp` = ?, `gbj` = ?, `gxx` = ?, `gnum` = ?";
                $sjexp = mt_rand(6, 8) + 0.5;
                $gexp = round($gw->glv * $sjexp, 0);
                $arr = array($gw->id , $gw->name , $gw->refresh , date('Y-m-d H:i:s', time()), $mid, $gw->hp , $gw->gj , $gw->fy , $gw->lv , $gw->qy , $gexp , $gw->hp , $gw->bj , $gw->xx, 0, $num, $gw->name , $gw->refresh , date('Y-m-d H:i:s', time()), $mid , $gw->hp , $gw->gj , $gw->fy , $gw->lv , $gw->qy , $gexp , $gw->hp , $gw->bj , $gw->xx, 0, $num);
            } else {
                $sql = "DELETE from mid_goods WHERE gid = ? and gmid = ?;";
                $arr = array($gid , $mid);
            } 
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute($arr);
            $result = $stmt->rowCount();
        } 
        return $result;
    } 

    function mid_get_goods_all($mid) { // 获取当前内存地图物品
        $sql = "select * from mid_goods where gmid = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $ret;
    } 

    function mid_add_npc($gid , $mid , $num , $numid = 1) { // 向地图NPC数据表写入NPC数据
        global $npc;
        $sql = "SELECT * FROM `mid_npc` WHERE gid = ? and gmid = ? and gnumid =?;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($gid, $mid, $numid));
        $gw = $stmt->fetch(\PDO::FETCH_OBJ);
        if ($gw) {
            $interval = timediff($gw->gcreate_time, date('Y-m-d H:i:s', time())) ;
            if ($interval['min'] > $gw->grefresh) {
                $update = true;
            } else {
                $update = false;
            } ;
        } else {
            $update = true;
        } ;

        if (!$update) {
            $result = true; //echo "保持原数据不变<br>";
        } else {
            // echo "准备更新当前NPC数据<br>";
            $gw = $npc->get_npc_info($gid);
            if (is_object($gw)) {
                $sql = "INSERT INTO mid_npc(`gid`, `name`, `grefresh`,`gcreate_time`, `gmid`, `hp`, `max_hp`, `mp`, `max_mp`,`ggj`, `gfy`, `glv`, `gyid`, `gexp`, `sid`, `gmaxhp`, `gbj`, `gxx`, `attackable` , `gnum` , `gnumid`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
                $sjexp = mt_rand(6, 8) + 0.5;
                $gexp = round($gw->glv * $sjexp, 0);
                $arr = array($gw->id , $gw->name , $gw->refresh , date('Y-m-d H:i:s', time()), $mid , $gw->hp , $gw->max_hp , $gw->mp , $gw->max_mp , $gw->gj , $gw->fy , $gw->lv , $gw->qy , $gexp , null , $gw->bj , $gw->xx, 0, $gw->attackable, $num, $numid);
            } else {
                $sql = "DELETE from mid_npc WHERE gid = ? and gmid = ?;";
                $arr = array($gid , $mid);
            } 
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute($arr);
            $result = $stmt->rowCount();
        } 
        return $result;
    } 

    function mid_get_guaiwu_all($mid) { // 获取当前内存地图怪物
        $sql = "select * from mid_npc where gmid = ? ;";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function mid_get_guaiwu_all_and_wounded($mid) { // 获取当前地图怪物，含受伤的//
        $sql = "select * from mid_npc where mid = ? AND ( sid = '' or sid = ?) ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid , $this->sid));
        $ret = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return $ret;
    } 

    function mid_get_guaiwu_sys_all($mid) { // 获取当前地图怪物
        $sql = "select mgid from mid where id = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);
        $gw_arr = [];
        if ($ret) {
            $i = 0;
            $arr1 = explode("," , $ret->mgid);
            foreach ($arr1 as $gstr) {
                $arr2 = explode("|", $gstr);
                if (count($arr2) == 2) {
                    $gw_arr[$i] = $arr2[0];
                } 
                $i++;
            } 
            if ($gw_arr) {
                return $gw_arr;
            } 
        } 
        return $ret;
    } 

    function mid_update_gw_time($mid) {
        $nowdate = $nowdate = date('Y-m-d H:i:s');
        $sql = "update mid set mgtime = ? WHERE mid = ?";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($nowdate , $mid));
        return $stmt->rowCount();
    } 

    function mid_get_guaiwu_sys_num($mid , $gid) { // 获取当前地图怪物
        $sql = "select mgid from mid where mid = ? ";
        $stmt = $this->dblj->prepare($sql);
        $stmt->execute(array($mid));
        $ret = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($ret) {
            $arr1 = explode("," , $ret->mgid);
            foreach ($arr1 as $gstr) {
                $arr2 = explode("|", $gstr);
                if (!(count($arr2) == 2)) {
                    return false;
                } 
                if ($arr2[0] == $gid) {
                    return $arr2[1];
                } 
            } 
        } 
        return $ret;
    } 
} 

?>