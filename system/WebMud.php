<?php
namespace game_system;

class WebMud { // 解析表达式与属性引用
    public $map;
    public $npc;
    public $player;
    public $goods;
    public $skill;
    public $formula;

    private $debug;
    private $u_info;
    private $o_info;
    private $target;

    function __construct() {
        global $map;
        global $npc;
        global $player;
        global $goods;
        global $skill;
        global $formula;
        global $player_info;
        $this->map = $map;
        $this->npc = $npc;
        $this->formula = $formula;
        $this->goods = $goods;
        $this->skill = $skill;
        $this->player = $player;
    } 

    private function is_valid_brackets($str) { // 判断括号是否完整配对
        $Symbol = array('(' => ')', '[' => ']', '{' => '}');
        $Stack = array(); //存放匹配到的括号
        foreach(str_split($str) as $key => $val) {
            if (in_array($val, array_keys($Symbol))) {
                array_push($Stack, $val); //压入数组
            } 
            if (in_array($val, array_values($Symbol))) {
                if ($val != @$Symbol[array_pop($Stack)]) {
                    return false;
                } 
            } 
        } 
        return empty($Stack)?true:false;
    } 

    private function value($expr) { // 从全局配置数据表引入变量实际内容
        if ($this->debug) {
             echo "<b>变量解析：</b>$expr <br>";
        } 
        $exp = $this->trimall($expr);
		if(is_array($exp)){
			echo "ERROR:语法错误！括号符不匹配的公式【{$expr}】";
			exit;
			};
        if (substr($exp, 0, 2) == "v(") { // 删除v()引用属性v()包裹
            preg_match_all('/(?<=\()(.*?)(?=\))/', $exp, $matches);
			return  $this->get_attribute($matches[0][0]);
        } elseif (substr($exp, 0, 2) == '{"') { // 如果值被{" "}直接引用则返回文本值{""}包裹
            preg_match_all('/(?<={")(.*?)(?="})/', $exp, $matches);
			return  $this->get_attribute($matches[0][0]);
        } elseif (substr($exp, 0, 2) == '{(') { // 如果值被{( )}直接引用则返回文本值{()}包裹
            preg_match_all('/(?<={\()(.*?)(?=\)})/', $exp, $matches);
			return  $this->get_attribute($matches[0][0]);
        } elseif (substr($exp, 0, 1) == "{") { // 删除{}直接引用属性{}包裹
            preg_match_all('/(?<={)(.*?)(?=})/', $exp, $matches);
			return  $this->get_attribute($matches[0][0]);
        } 
		return $expr;
    } 

    private function valuea($expr) { // 提取表达式所有V引用属性
        $strPattern = "/v\(.*?\).*?/";
        $coun = preg_match_all($strPattern, $expr, $arrMatches);
        if ($coun > 0) {
            if ($this->debug) {
                echo "<br>v属性提取：";
                var_dump($arrMatches);
                echo "<br>";
            } 
            $search = array();
            $exprl = array();
            for ($k = 0; $k < $coun; $k++) {
                array_push($search, $arrMatches[0][$k]);
                array_push($exprl, self::value($arrMatches[0][$k]));
            } 
            return str_replace($search, $exprl, $expr);
        } 
        return $expr;
    } 

    function trimall($str) { // 删除全部空格换行并检查括号组是否匹配
        $qian = array(" ", "　", "\t", "\n", "\r"); 
        // 检查语法
        if (self::is_valid_brackets($str)) {
            return str_replace($qian, '', $str);
        } else {
            return array("error", "括号不匹配！");
        } 
    } 

    private function sanmu($expr) { // 循环执行所有三元运算计算
        if ($this->debug) {
            echo "<br>进入三目原始公式：{$expr}<br>";
        } 
        if (strpos($expr, '?') == false) {
            // echo "<br>进入三目进程跳出：{$expr}<br>";
            return $expr;
        } 
        $strPattern = '/\([^()]*\)/';
        preg_match_all($strPattern, $expr, $arrMatches);
        if (count($arrMatches, 1) > 1) {
            if ($this->debug) {
                echo "<br>进入三目:" . $count;
                var_dump ($arrMatches);
            } 
            $sanmuend = self::sanmua($arrMatches[0][0]);
            if ($this->debug) {
                echo "<br>执行公式" . $arrMatches[0][0] . "<br>三目执行结果：" . $sanmuend . "<br>原始公式：{$expr}<br>";
            } 
            $temp = str_replace($arrMatches[0][0], $sanmuend, $expr);
            if ($this->debug) {
                echo $temp . "<br>";
            } 
            return self::sanmu($temp);
        } else {
            return $expr;
        } 
    } 

    private function sanmua($expr) { // 新的三元运算计算
        // 单级三目运算
        $k = preg_match_all('/(?<=\()(.*?)(?=\))/', $expr, $matches); 
		if($this->debug) {
         echo "<br>单级三目运算";
         var_dump($matches);
         echo $k;
		}
        $zhi = $matches[0][0]; 
        // echo "<br>判断条件：".$zhi."<br>";
        $jieguo = explode("?", $zhi); 
        // var_dump($jieguo);
        if (count($jieguo) == 2) {
            $fanhui = explode(":", $jieguo[1]);
        } 
        // echo "<br>";
        // var_dump($fanhui);
        // echo "<br>".$jieguo[0];
        $panduan = array("==", "!=", "<=", ">=", ">", "<");
        if (self::panduan($jieguo[0], $panduan) == 0) {
            if ($jieguo[0] == "真") {
                return $fanhui[0];
            } elseif ($jieguo[0] !== 0) {
                return $fanhui[0];
            } 
            return $fanhui[1];
        } elseif (strpos($jieguo[0], "==") != false) { // 两端相等判断
            $tiaojian = explode("==", $jieguo[0]); 
            // echo "条件两极：".$tiaojian[0]."======".$tiaojian[1]."<br>";
            if ($tiaojian[0] === $tiaojian[1]) {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } elseif (strpos($jieguo[0], "!=") != false) { // 两端不等判断
            $tiaojian = explode("!=", $jieguo[0]); 
            // echo "条件两极：".$tiaojian[0]."======".$tiaojian[1]."<br>";
            if ($tiaojian[0] != $tiaojian[1]) {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } elseif (strpos($jieguo[0], "<=") != false) { // 左小于等于右
            $tiaojian = explode("<=", $jieguo[0]); 
            // echo "条件两极：".$tiaojian[0]."======".$tiaojian[1]."<br>";
            if (luojipd($tiaojian[0], "<=", $tiaojian[1]) == "真") {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } elseif (strpos($jieguo[0], ">=") != false) { // 左小大于等于右
            $tiaojian = explode(">=", $jieguo[0]); 
            // echo "条件两极：".$tiaojian[0]."======".$tiaojian[1]."<br>";
            if (luojipd($tiaojian[0], ">=", $tiaojian[1]) == "真") {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } elseif (strpos($jieguo[0], "<") != false) { // 左小于右
            $tiaojian = explode("<", $jieguo[0]); 
            // echo "条件两极：".$tiaojian[0]."======".$tiaojian[1]."<br>";
            if (luojipd($tiaojian[0], "<", $tiaojian[1]) == "真") {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } elseif (strpos($jieguo[0], ">") != false) { // 左大于右
            $tiaojian = explode(">", $jieguo[0]); 
            // echo "<br>条件两极：".$tiaojian[0].">>>>".$tiaojian[1]."<br>";
            if ($this->luojipd($tiaojian[0], ">", $tiaojian[1]) == "真") {
                return $fanhui[0];
            } else {
                return $fanhui[1];
            } 
        } 
        return $expr;
    } 

	private function pretreatment($expr,$tit=""){//前置预处理器
		$expr = $this->trimall(str_replace(PHP_EOL, '', $expr));
        if ($this->debug) {
            var_dump($tit.'<br>', $expr, '<br>');
        } 
		if (is_array($expr)) {
            echo "错误:" . $expr[1] . "<br>";
            exit();
        } 
		return $expr;
	}
	
    function start($expr, $u_info, $o_info = null, $target = null, $debug = false) { // 开始执行解析
        $this->target = $target;
        $this->u_info = $u_info;
        $this->o_info = $o_info;
        $this->debug = $debug;
		$expr = self::pretreatment($expr,"收到数据解析");
		$shuxing = array("+", "-", "*", "/", "&&", "||", "==", "!=", "<=", ">=", ">", "<");
        if ($expr != "") {
            // echo "提取花括号代码数组";
            $strPattern = '/"{[^"{}"]*}"/';
            $arrMatches = [];
            $i = preg_match_all($strPattern, $expr, $ntr);
            $k = 0;
            while ($i > 0) {
                $i--;
                $strPattern = '/{.*?}.*?/';
                preg_match_all($strPattern, $expr, $ntrl);
                $test = $ntrl[0][0];
                $expr = str_replace($ntr[0][$k], $test, $expr) ; //开始解析代码
                $k++;
            } 

            $strPattern = "/{.*?}.*?/";
            $arrMatches = [];
            $i = preg_match_all($strPattern, $expr, $ntr);

            if (!is_array($ntr) || count($ntr[0]) == 0) {
                return $expr;
            } else {
                // var_dump($ntr);
                // echo "<br>";
            } 
            $k = 0; 
            // var_dump($i,$ntr);
            while ($i > 0) {
                $i--;
                if (strcmp(substr ($ntr[0][$k], 0, 6), '{eval(') == 0) {
                    $temp = self::valuea($ntr[0][$k]);
                    if ($this->debug) {
                    echo "<br>V属性提取前公式:" . $ntr[0][$k] ."<br>V属性提取结果:" . $temp .'<br>';
                    }
					if (strcmp(substr ($temp, 0, 6), '{eval(') == 0) {
                        if ($this->debug) {
                            var_dump('<br>', 'Is{eval(', '<br>');
                        } 
                        $temp = str_replace('{eval(', '{(',$temp);
                    } 
                    if (self::panduan($temp, $shuxing) != 0) {
						 $temp = self::Cover($temp);
                    } 
                    if (!is_array($temp)) {
                        $temp = self::readkh($temp);
                    } else {
                        $temp = $temp [1];
                    } 
                } else {
                    if ($this->debug) {
						var_dump("没有参与混合判断 ： ",$ntr[0][$k]);
					}
                    $temp = self::valuea($ntr[0][$k]);
                    $temp = self::value($temp);
					if (self::panduan($temp, $shuxing) != 0) {
						 $temp = self::Cover($temp);
                    }
                }
                $expr = str_replace($ntr[0][$k], $temp, $expr) ; //开始解析代码
                $k++;
            }
 			if ($this->debug) {
                var_dump('<br>', '最终判定准备：',$expr, '<br>');
            } 

            if (is_array($expr)) {
                $expr = array('type' => $expr[0], 'text' => str_replace("%2B", "+", $expr[1]));
            } else {
				$expr = str_replace("{", "", $expr);
				$expr = str_replace("}", "", $expr);
				$expr = str_replace("\"", "", $expr);
				$expr = array('type' => 10, 'text' => str_replace("%2B", "+", $expr));
            } 
            if ($expr['text'] == '""0' || $expr['text'] == '0') {
                $expr['text'] = "" ;
            } else {
                $expr['text'] = str_replace('""', "", $expr['text']);
            } 
			if ($this->debug) {
                var_dump('<br>', '公式解析结果：',$expr, '<br>');
            } 
            return $expr;
        } else {
            return ;
        } 
    } 

	function start_condition($expr, $u_info, $o_info = null, $target = null, $debug = false){//开始解析-单条件解析模型
		$this->target = $target;
        $this->u_info = $u_info;
        $this->o_info = $o_info;
        $this->debug = $debug;
        $expr = self::pretreatment($expr,'单条件解析模型-收到数据解析');
		$shuxing = array("+", "-", "*", "/", "&&", "||", "==","!=", "<=", ">=", ">", "<");
        if ($expr != "") {
            // echo "提取花括号代码数组";
            $strPattern = '/"{[^"{}"]*}"/';
            $arrMatches = [];
            $i = preg_match_all($strPattern, $expr, $ntr);
            $k = 0;
            while ($i > 0) {
                $i--;
                $strPattern = '/{.*?}.*?/';
                preg_match_all($strPattern, $expr, $ntrl);
                $test = $ntrl[0][0];
                $expr = str_replace($ntr[0][$k], $test, $expr) ; //开始解析代码
                $k++;
            } 

            $strPattern = "/{.*?}.*?/";
            $arrMatches = [];
            $i = preg_match_all($strPattern, $expr, $ntr);

            if (!is_array($ntr) || count($ntr[0]) == 0) {
                return $expr;
            } else {
                // var_dump($ntr);
                // echo "<br>";
            } 
            $k = 0; 
            // var_dump($i,$ntr);
            while ($i > 0) {
                $i--;
                if (strcmp(substr ($ntr[0][$k], 0, 6), '{eval(') == 0) {
                    $temp = self::valuea($ntr[0][$k]);
                    if ($this->debug) {
                    echo "<br>V属性提取前公式:" . $ntr[0][$k] ."<br>V属性提取结果:" . $temp .'<br>';
                    } 
					if (strcmp(substr ($temp, 0, 6), '{eval(') == 0) {
                        if ($this->debug) {
                            var_dump('<br>', 'Is{eval(', '<br>');
                        } 
                        $temp = str_replace('{eval(', '{(',$temp);
                    } 
                    if (self::panduan($temp, $shuxing) != 0) {
						 $temp = self::Cover($temp);
                    } 
                    if (!is_array($temp)) {
                        $temp = self::readkh($temp);
                    } else {
                        $temp = $temp [1];
                    } 
					
					
                } else {
                    if ($this->debug) { var_dump($ntr[0][$k]);}
                    $temp = self::valuea($ntr[0][$k]);
                    $temp = self::value($temp);
                } 
                $expr = str_replace($ntr[0][$k], $temp, $expr) ; //开始解析代码
                $k++;
            }
 			if ($this->debug) {
                var_dump('<br>', '最终判定准备：',$expr, '<br>');
            } 
			
			if (self::panduan($expr, $shuxing) != 0) {
				$expr = self::Cover($expr);
				if ($this->debug) {
					var_dump('<br>', '最终判定准备-1111111：',$expr, '<br>');
				}
            } 
			if ($this->debug) {
				var_dump('<br>', '最终判定准备-2222222：',$expr, '<br>');
			}
            if (is_array($expr)) {
                $expr = array('type' => $expr[0], 'text' => str_replace("%2B", "+", $expr[1]));
            } else {
				$expr = str_replace("{", "", $expr);
				$expr = str_replace("}", "", $expr);
				$expr = str_replace("\"", "", $expr);
				$expr = array('type' => 10, 'text' => str_replace("%2B", "+", $expr));
            } 
            if ($expr['text'] == '""0' || $expr['text'] == '0') {
                $expr['text'] = "" ;
            } else {
                $expr['text'] = str_replace('""', "", $expr['text']);
            } 
			if ($this->debug) {
                var_dump('<br>', '公式解析结果：',$expr, '<br>');
            } 
            return $expr;
        } else {
            return ;
        }
	}
	
    private function readkh($expr) {
        $strPattern = '/(?<=\()[^()]*(?=\))/';
        $coun = preg_match($strPattern, $expr, $arrMatches);
        if ($this->debug) {
            echo "括号数据提取公式：";
            var_dump($expr);
            echo'<br>';
            echo "括号数据提取结果：{$coun}===";
            var_dump($arrMatches);
            echo '<br>';
        }
        if ($coun > 0) {
			$exp = $arrMatches[0];
			if ($this->debug) {echo "括号结果：" . $exp . "<br>";}
			if(is_numeric($exp) || $exp == "真" || $exp == "假" || $exp == ""){
				if ($this->debug) {echo "括号结果返回：" . $exp . "<br>";}
				return $exp;
			}
			$yunsuan = array("+", "-", "*", "/");
			$luoji = array("==", "!=", ">=", "<=", ">", "<");
			$sanmu = array("?");
			if (self::panduan($exp, $yunsuan)) {
            // echo "四则运算";
				$linshi = self::shuzhi($exp);
			}elseif(self::panduan($exp, $luoji)){
				$linshi = self::luojibj($exp);
			}if(self::panduan($exp, $sanmu)){
				$linshi = self::sanmu("(" . $exp . ")");
			}
            if ($this->debug) {
                echo $exp  . "<br>";
                echo "括号临时解析";
                var_dump($linshi);
                echo "<br>";
            } 
            if (is_array($linshi)) {
                $linshi = $linshi[1];
            }
            $shiji = "(" . $exp  . ")";
            $temp = str_replace($shiji, $linshi, $expr);
            if ($this->debug) {
                echo "括号结果：" . $temp . "<br>";
				//exit;
            } 
            return self::readkh($temp);
        } else {
            if ($this->debug) {
                echo "括号结果返回：" . $expr . "<br>";
            } 
            return $expr;
        } 
    } 

    private function jieguo($expr) { // 输出正确的结果
		if ($this->debug) { echo "<b>类型判断：</b>". $expr ."<br><b>类型识别：</b>";}
        $guanxi = array("&&", "||");
        $text = array ('"');
        if (self::panduan($expr, $guanxi)) {
            // echo "关系判断";
            return array(7, self::guanxibj($expr));
        } 
		$count = preg_match('/.*?(\?).*?(:).*?/', $expr, $arrMatches) ;
		if ($this->debug) { echo $count;};
		if ($count > 0) {
            if ($this->debug) { echo "private function jieguo(\$expr) "; } 
            return array(10, self::sanmu("(" . $expr . ")"));
        } 
        $isMatched = preg_match("/[\x{4e00}-\x{9fa5}]{1,4}/u", $expr, $matches);
		$luoji = array("==", "!=", ">=", "<=", ">", "<");
        if ($isMatched > 0) {
            //echo "文本判断";
            return self::textpd($expr);
        }
		if (self::panduan($expr, $luoji)) {
            // echo "逻辑比较";
            return array(7, self::luojibj($expr));
        }
		if (self::panduan($expr, $yunsuan)) {
            // echo "四则运算";
            return self::shuzhi($expr);
        } 
        return $expr;
    } 

    private function textpd($expr) {
        $expr = str_replace("+", "", $expr);
        $expr = str_replace('"', "", $expr); 
        // echo "<br>文本单独处理：" .$expr ;
        return $expr;
    } 

    private function guanxibj($expr) {
        if (strpos($expr, "&&") != false) { // 并且关系判断
            $tiaojian = explode("&&", $expr);
            if (self::luojibj($tiaojian[0]) == self::luojibj($tiaojian[1])) {
                return 1;
            } else {
                return 0;
            } 
        } elseif (strpos($expr, "||") != false) { // 或者关系判断
            $tiaojian = explode("||", $expr);
            if (self::luojibj($tiaojian[0]) == "真" || self::luojibj($tiaojian[1]) == "真") {
                return 1;
            } else {
                return 0;
            } 
        } 
        return 0;
    } 

    private function luojibj($expr) {
        if ($this->debug) {echo "<br><b>转到逻辑：</b>$expr<br>";}
        $isMatched = preg_match("/[\x{4e00}-\x{9fa5}]{1,4}/u", $expr, $matches); 
        // var_dump($isMatched, $matches);
        if ($isMatched > 0) {
            // echo "<br>逻辑判断文本：<br>";
            if (strpos($expr, "==") != false) {
                $tiaojian = explode("==", $expr);
                if (strcmp($tiaojian[0], $tiaojian[1]) == 0) {
                    return "真";
                } else {
                    return "假";
                } 
            } elseif (strpos($expr, "!=") != false) {
                $tiaojian = explode("!=", $expr);
                if (strcmp($tiaojian[0], $tiaojian[1]) != 0) {
                    return "真";
                } else {
                    return "假";
                } 
            } 
        } else {
            // echo "<br>逻辑判断整数：<br>";
            if (strpos($expr, "==") != false) {
                $tiaojian = explode("==", $expr);
                return $this->luojipd($tiaojian[0], "==", $tiaojian[1]);
            } elseif (strpos($expr, "!=") != false) {
                $tiaojian = explode("!=", $expr);
                return $this->luojipd($tiaojian[0], "!=", $tiaojian[1]);
            } elseif (strpos($expr, ">=") != false) {
                $tiaojian = explode(">=", $expr);
                return $this->luojipd($tiaojian[0], ">=", $tiaojian[1]);
            } elseif (strpos($expr, "<=") != false) {
                $tiaojian = explode("<=", $expr);
                return $this->luojipd($tiaojian[0], "<=", $tiaojian[1]);
            } elseif (strpos($expr, ">") != false) {
                $tiaojian = explode(">", $expr);
                return $this->luojipd($tiaojian[0], ">", $tiaojian[1]);
            } elseif (strpos($expr, "<") != false) {
                $tiaojian = explode("<", $expr);
                return $this->luojipd($tiaojian[0], "<", $tiaojian[1]);
            } 
        } 
        return "假";
    } 

    function luojipd($a, $f, $b) { // 处理逻辑判断
        $a = floatval($a);
        $b = floatval($b);
        eval("\$jieguo= $a $f $b;");
        if ($jieguo) {
            return "真";
        } else {
            return "假";
        } 
    } 

    private function panduan($expr, $fh) {
        for($k = 0;$k <= count($fh);$k++) {
            if ($expr && $fh[$k] && stripos($expr, $fh[$k]) != false) {
                return 1;
            } 
        } 
        return 0;
    } 

    private function paduan($expr, $cun, $wei) { // 判断初始与结尾标志
        $weicon = strlen($wei) * -1;
        if (strcmp(substr($expr, 0, strlen($cun)), $cun) == 0 && strcmp(substr($expr, $weicon), $wei) == 0) {
            return 1;
        } else {
            return 0;
        } 
    } 

    private function Cover($expr) { // 处理花括号内代码
        if ($this->debug) {echo "<b>花括数据：</b>".$expr."<br>";}
		$jieguo = self::readkh($expr);
		$jieguo =  str_replace("{","",$jieguo );
		$jieguo =  str_replace("}","",$jieguo );
		if(is_numeric($jieguo)){
			return $jieguo;
			};
		if (strpos($jieguo, '?') == true) {
            $jieguo = self::sanmu($jieguo);
            if ($this->debug) {
               echo "<br>三目数据结果:" . $jieguo . "<br>";
            } 
        } 
        $strPattern = "/v\(.*?\).*?/";
        $exprl = array();
        $arrMatches = [];
        $coun = preg_match($strPattern, $expr, $arrMatches);
        if ($this->debug) {
            echo "花括号内变量组:";
            var_dump($arrMatches, $coun); //花括号内变量名
            echo "<br>";
        } 
        // 先行对属性值引用进行解析
        if ($coun > 0) {
            $search = array();
            for ($k = 0; $k < $coun; $k++) {
                array_push($search, $arrMatches[0][$k]);
                array_push($exprl, self::value($arrMatches[0][$k]));
            } 
            $jieguo = str_replace($search, $exprl, $expr);
            $jieguo = self::readkh($jieguo);
            if ($this->debug) {
                echo "<br>数据结果jieguo:" ;
                var_dump($jieguo);
                echo "<br>";
            } 
            if (is_array($jieguo)) {
                return $jieguo;
            } 
            if (substr($jieguo, 0, 1) == '{') { // 如果值被{}直接引用则返回文本值{}包裹
                preg_match_all('/(?<={)(.*?)(?=})/', $jieguo, $matches);
                return $matches[0][0];
            } 
            return $jieguo;
        } 

        $temp = str_replace($expr, self::value($jieguo), $expr);
        if ($this->debug) {echo "<br>花括处理：" . $temp;}
        if ($temp != "") {
            return  self::jieguo($jieguo);
        } else {
            $jieguo = self::jieguo($expr);
            $jieguo = self::sanmu($jieguo[1]);
            return $jieguo;
        } 
    } 

    private function get_between($input, $start, $end) { // 字符串截取
        echo $input;
        $str = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
        return $str;
    } 

    private function shuzhi($expr) {
        // echo "<br><b>进入运算：</b>$expr";
        // $expr = '1+(5-6*7)*9';
        // $expr = '2+45+6/2-45+(23-4)*5';
        // $expr = '2+45+6/2-20';
        // 运算符栈,在栈里插入最小优先级的运算符，避免进入不到去运算符比较的循环里
        $opArr = array('#'); 
        // 运算数栈;
        $oprandArr = array(); 
        // 运算符优先级
        $opLevelArr = array(')' => 2,
            '(' => 3,
            '+' => 4,
            '-' => 4,
            '*' => 5,
            '/' => 5,
            '#' => 1
            );
        $bolanExprArr = array();
        $exprLen = strlen($expr);
        $inop = false;
        $opNums = ""; 
        // 解析表达式
        for($i = 0;$i <= $exprLen;$i++) {
            $char = $expr[$i]; 
            // 获取当前字符的优先级
            $level = intval($opLevelArr[$char]); 
            // 如果大于0，表示是运算符，否则是运算数，直接输出
            if ($level > 0) {
                $inop = true; 
                // 如果碰到左大括号，直接入栈
                if ($level == 3) {
                    array_push($opArr, $char);
                    continue;
                } //与栈顶运算符比较，如果当前运算符优先级小于栈顶运算符，则栈顶运算符弹出，一直到当前运算符优先级不小于栈顶
                while ($op = array_pop($opArr)) {
                    if ($op) {
                        $currentLevel = intval($opLevelArr[$op]);
                        if ($currentLevel == 3 && $level == 2) {
                            break;
                        } elseif ($currentLevel >= $level && $currentLevel != 3) {
                            array_push($bolanExprArr, $op);
                        } else {
                            array_push($opArr, $op);
                            array_push($opArr, $char);
                            break;
                        } 
                    } 
                } 
            } else {
                // 多位数拼接成一位数
                $opNums .= $char;
                if ($opLevelArr[$expr[$i + 1]] > 0) {
                    array_push($bolanExprArr, $opNums);
                    $opNums = "";
                } 
            } 
        } 
        array_push($bolanExprArr, $opNums); 
        // 输出剩余运算符
        while ($leftOp = array_pop($opArr)) {
            if ($leftOp != '#') {
                array_push($bolanExprArr, $leftOp);
            } 
        } 
        // echo "<br>sdgsgsdgr<br><br>";
        // 计算逆波兰表达式。
        foreach($bolanExprArr as $v) {
            if (!isset($opLevelArr[$v])) {
                array_push($oprandArr, $v);
            } else {
                $op1 = array_pop($oprandArr);
                $op2 = array_pop($oprandArr);
                if (is_numeric ($op2) && is_numeric ($op1)) {
                    // echo "\$result = $op2 $v $op1;";
                    eval("\$result = $op2 $v $op1;");
                    array_push($oprandArr, $result);
                } else {
                    return array(10, $this->textpd($expr));
                } 
            } 
        } 
        return array(3, $result);
    } 

    function get_attribute($val) { // 读取引用属性值
        $arry = explode(".", $val);
        $arry_count = count($arry);
        if ($this->debug) {
            var_dump($val, $this->target, $arry_count, "<br>=============<br>");
        } 
        $sid = $this->u_info->sid;
        $ut = json_decode($this->u_info->ut_val); 
        // $o_obj = json_decode($ut->o->val);
        // $o_id = $o_obj->id;
        switch ($this->target) {
            case 'player':
                $u_info = $this->player->get_player_info($sid);
                $ut = $this->player->get_player_ut($u_info->sid);
                $o_obj = json_decode($ut->o->val);
                $o_type = $o_obj->type;
                break;
            case 'npc':
                $u_info = $this->npc->get_npc_run($o_id);
                $npc_info = $this->npc->get_npc_info($u_info->gid);
                $u_info = G_convertObjectClass($npc_info, $u_info);
                $ut = $this->npc->get_npc_ut($u_info->id);
                $o_info = $this->player->get_player_info($sid);
                break;
        } 
        switch ($o_type) {
            case "npc":
                $o_info = $this->npc->get_npc_run($o_obj->id);
                $npc_info = $this->npc->get_npc_info($o_info->gid);
                $o_info = G_convertObjectClass($npc_info, $o_info);
                break;
            case "pve_npc":
                $npc_id = $ut->npc->val;
                $obj_info = $this->npc->get_npc_info($npc_id);
                break;
            case "pve":
                $obj_info = $this->player->get_player_info($sid);
                break;
            case "map":
                $obj_info = $this->map->get_mid_info($this->player_info->nowmid);
                break;
            case "goods":
                $goods_id = $ut->goods->val;
                $obj_info = $this->goods->get_goods_run($goods_id);
                $obj_info = $this->goods->get_goods_info($obj_info->gid);
                break;
            case "daoju":
                $daoju_id = $ut->daoju->val;
                $obj_info = $this->goods->get_player_goods_info($daoju_id, $sid);
                break;
            default:
                $obj_info = $this->player->get_player_info($sid);
        } 
        // var_dump($this->u_info,"<br><br>",$this->o_info);
        if ($arry_count >= 2) {
            switch ($arry[0]) {
                case 'u':
                    $max = json_decode($this->u_info->all_max);
                    $us = json_decode($this->u_info->us_val);

                    if ($arry_count = 2) {
                        $attr = $arry[1];
                        $value = $this->u_info->$attr ;
                        if ($this->debug) {	
							//var_dump($this->u_info);
                            var_dump('玩家属性提取值：',$value , $attr,'<br>');
                        } 
                        if (!isset($value) && substr($attr, 0, 4) == 'max_') {
							$max = json_decode($max->$attr);
                            $value = $max->val ;
                        } elseif (!isset($value)) {
                            $value = json_decode($us->$attr)->val ;
                        } 
                    } 
                    if ($arry_count = 3) {
                        $attr = $arry[2];
                        switch ($arry[1]) {
                            case 'pm':
                                $u_info = $this->map->get_mid_info($this->u_info->nowmid);
                                $value = $u_info->$attr;
                                break;
							case 'input':
							    $u_info = $this->player->get_player_info($sid);
							    $u_input =json_decode(json_decode(json_decode($u_info->ut_val)->input)->val);
							    $value = $u_input->$attr->val;
								break;
                        } 
                    } 
                    break;
                case 'g':
                    break;
                case 'o':
                    if ($arry_count = 2) {
                        $attr = $arry[1];
                        $value = $this->o_info->$attr;
                    } 
                    if ($arry_count = 3) {
                        $attr = $arry[2];
                        switch ($arry[1]) {
                            case 'pm':
                                $o_info = $this->map->get_mid_info($this->player_info->nowmid);
                                $value = $o_info->$attr;
                                break;
                        } 
                    } 
                    break;
                case 'ut':
                    if ($arry_count = 2) {
                        $attr = $arry[1];
                        $value = $ut->$attr->val;
                    } 
                    if ($arry_count = 3) {
                        $attr = $arry[2];
                        switch ($arry[1]) {
                            case 'pm':
                                $obj_info = $this->map->get_mid_info($this->player_info->nowmid);
                                $value = $ut->$attr->val;
                                break;
                        } 
                    } 
                    break;
                case 'ot':
                    break;
                case 'm':
                    $Quick_obj = json_decode($ut->Quick->val);
                    if (is_object($Quick_obj)) {
                        $obj_info = $this->skill->get_skill_info($Quick_obj->id);
                        $obj_info->lvl = $Quick_obj->lvl;
                    } else {
                        $skill_id = $ut->Quick->val;
                        $shill_player = $this->skill->get_player_skill_info($skill_id , $sid);
                        $skill_obj = $this->skill->get_skill_info($shill_player->initial_id);
                        $obj_info = G_convertObjectClass($skill_obj, $shill_player);
                    } 

                    if ($arry_count = 2) {
                        $attr = $arry[1];
                        $value = $obj_info->$attr;
                    } 
                    if ($arry_count = 3) {
                        $attr = $arry[2];
                        switch ($arry[1]) {
                            case 'pm':
                                $obj_info = $this->map->get_mid_info($this->player_info->nowmid);
                                $value = $ut->$attr->val;
                                break;
                        } 
                    } 
                    break;
                case 'e':
                    if ($arry_count = 2) {
                        $attr = $arry[1];
                        $math = $this->formula->get_math_name_info($attr);
                        $math_string = str_replace("{", "v(", $math->math_string);
                        $math_string = str_replace("}", ")", $math_string);
                        //$this->debug = true;
						if ($this->debug) {
                            var_dump('{eval(' . $math_string . ')}');
                        } 
                        $value = $this->start('{eval(' . $math_string . ')}', $this->u_info , $this->o_info, $this->target, $this->debug);
                        if ($this->debug) {
                            echo "<br>====================<br>" . '{eval(' . $math_string . ')}' . "<br>====================<br>数据解析结果：<br>";
                            var_dump($value, $value['type'], $value['text']);
                            echo "<br>====================<br>";
                        } 
                        $value = $value['text'];
                    } 
                    break;
                case 'r':
                    if ($arry_count = 2) {
                        $attr = intval($arry[1]);
                        $value = mt_rand (0, $attr);
                    } 
                    if ($arry_count = 3) {
                        $attr = $arry[2];
                        $value = $this->start("{{$arry[1]}.{$arry[2]}}", $this->u_info , $this->o_info, $this->target, $this->debug);
                        $value = mt_rand (0, intval($value['text']));
                    } 
                    break;
            } 
        } 
        $value = isset($value)?$value:0;
        if ($this->debug) {
            var_dump($val, '=====', $value, "<br>=============<br>");
        } 
        return $value;
    } 
} 

?>


