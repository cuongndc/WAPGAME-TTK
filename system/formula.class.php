<?php

namespace game_system {
    // 系统配置操作定义
    class formula {
        // 表达式信息定义
        public $dblj;

        function __construct() {
            global $dblj;
            $this->dblj = $dblj;
        } 

        function get_math_info($id) { // 读取表达式信息
            $sql = "select * from math WHERE id = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($id));
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } 
		
		function get_math_name_info($name) { // 读取表达式信息
            $sql = "select * from math WHERE math_name = ?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($name));
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } 

        function get_math_all($page = 0, $count = 20) { // 读取表达式列表返回记录总数和分页列表
            $page = intval($page);
            $count = intval($count);
            $sql = "select * from math";
            $stmt = $this->dblj->query($sql);
            $obj = json_decode('{}');
            $obj->num = $stmt->rowCount();
            if ($count != 0) {
                $sql .= " limit {$page},{$count}";
                $stmt = $this->dblj->prepare($sql);
                $stmt->execute();
                $obj->data = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } 
            return $obj;
        } 

        function del_math_id($id) { // 删除一条表达式
            $sql = "DELETE FROM math WHERE id = ? ;";
            $stmt = $this->dblj->prepare($sql);
            $ret = $stmt->execute(array($id));
            return $ret;
        } 

        function set_math($old_name, $math_name, $math_type, $math_string, $math_notes = "") { // 更新表达式信息
            $sql = "SELECT * FROM `math` WHERE math_name =?";
            $stmt = $this->dblj->prepare($sql);
            $stmt->execute(array($old_name));
            $math = $stmt->fetch(\PDO::FETCH_OBJ);
            if (!$math) {
                $type = "new";
            } else {
                $type = "edit";
            } 
            switch ($type) {
                case 'edit':
                    $sql = "UPDATE math SET `math_name` = ? ,`math_notes` = ? , `math_string` = ?, `math_type` = ? WHERE `math_name` = ?;";
                    $stmt = $this->dblj->prepare($sql);
                    $ret = $stmt->execute(array($math_name , $math_notes , $math_string , $math_type , $old_name));
                    break;
                case 'new':
                    $sql = "replace into math(math_name, math_notes,math_string,math_type) values(?,?,?,?);";
                    $stmt = $this->dblj->prepare($sql);
                    $ret = $stmt->execute(array($math_name , $math_notes , $math_string , $math_type));
                    break;
            } 
            if ($ret) {
                return $type;
            } else {
                return false;
            } 
        } 
    } 
} 

?>