<?php
$k= intval($_GET['k']);
$g= intval($_GET['g']);
$n= $_GET['n'];
echo open($n,$k,$g);
?>
<form action="grid_map.php" method="get">
<p>地图名</p><input type="text" name="n" >
<p>地图宽度</p><input type="text" name="k" >
<p>地图高度</p><input type="text" name="g" >
 <input type="submit" value="Submit" />
</form>
<br>
<br>
<?php
function open($n,$k,$g){
	$cou=$k*$g;
	$h=1;
	for($i=1;$i<=$cou;$i++){
		$left=$i-1;
		$right=$i+1;
		$up=$i+$k;
		$down=$i-$k;
		if($left < 0 || $left == $k*($h-1)){$left=0;}
		if($right > $k*$h){$right=0;}
		if($up > $cou){$up=0;}
		if($down < 0 ){$down=0;}
		$test++;
		//地图编号
		$number= "(". $h.",".$test.")";
		//地图连接区域
		$text=$text."格:".$i."[".$n.$number." ->左".$left. "->右".$right. "->上" .$up. "->下" .$down."]<br>";
		//以上内容用来创建地图
		if($test==$k){$test=0;$h++;}
	}
	return $text;
}
?>