<?php
require_once "user_rights.php";

if($_POST['basic']=="open"){
$game_name = G_trimall($_POST['game_name']);
$game_desc = G_trimall($_POST['game_desc']);
$game_lvl = G_trimall($_POST['game_lvl']);
$game_money = G_trimall($_POST['game_money']);
if(isset($game_name)){$sys->set_system_config("系统","游戏名称",$game_name);} 
if(isset($game_desc)){$sys->set_system_config("系统","游戏简介",$game_desc);}
if(isset($game_lvl)){$sys->set_system_config("系统","升级公式",$game_lvl);} 
if(isset($game_money)){$sys->set_system_config("系统","货币单位",$game_money);}
echo "操作成功！";
exit();
}

$game_name = $sys->get_system_config("系统","游戏名称");
$game_desc = $sys->get_system_config("系统","游戏简介");
$game_lvl=$sys->get_system_config("系统","升级公式");
$game_money=$sys->get_system_config("系统","货币单位");
$game_Birth = G_trimall($sys->get_system_config("游戏","出生地"));
if($game_Birth!=""){
		$Obj_game_Birth = $map->get_mid_info($game_Birth);
		$game_Birth_name =$map->get_qy_name($Obj_game_Birth->qy)."--". $Obj_game_Birth->name ."(". $game_Birth.")";
	}else{
		$game_Birth_name="未设置出生地";
	}
require_once "html/header.php";
?>

<div class="container">
<h2>游戏基本信息设置</h2>
<span class="con"></span> 
<p>游戏名：<p><input type="text" class="form-control" id="game_name" placeholder="游戏名" value="<?php echo $game_name;?>">
<p>游戏简介：<p><textarea class="form-control" rows="3" id="game_desc"  placeholder="游戏简介"><?php echo $game_desc;?></textarea>
<p>升级公式：<p><textarea class="form-control" id="lvl" rows="3" placeholder="玩家升级公式"><?php echo $game_lvl;?></textarea>
<p>货币名称：<p><input type="text" class="form-control" id="money" placeholder="游戏货币单位" value="<?php echo $game_money;?>">
<p>货币单位：<p><input type="text" class="form-control" id="money" placeholder="游戏货币单位" value="<?php echo $game_money;?>">
<p>出生地设置：</p>
   <div class="row">
	  <div class="col-xs-8">
		<input id="Birth_xs" type="text" class="form-control" value="<?php echo  $game_Birth_name;?>" readonly>
	  </div>
	  <div class="col-xs-4">
		<button type="button" id="btn_Birth" onclick='$("#search").html("")' class="btn btn-primary" data-toggle="modal" data-target="#Birth">
		<?php if($game_Birth!=""){echo "修改出生点";} else{echo "设置出生点";}?></button>
	  </div>
   </div>
<br>
<input type="submit" class="btn btn-block btn-primary" id="subm" value="保存修改" >
<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>

<div class="modal fade" id="Birth"><!--[出生地设置-页面]-->
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">选择出生点</h4>
      </div>
      <div class="modal-body">
	  <div class="input-group">
		<span class="input-group-addon">搜索地图</span>
		<input type="search" id="sear_alldt" class="form-control">
		<span class="input-group-btn"><button class="btn btn-default" id="search_alldt"  type="button">搜索</button></span>
	  </div>
        <p>选择一级区域</p>
	  <select class="form-control" id="chushe" onchange="chusheng()">
		<option value="">请选择一级区域</option>
		<?php
		$Obj_game_qy =$map->get_qy_all();
		foreach($Obj_game_qy->data as $obj){
         echo "<option value='".$obj->qyid ."'>".$obj->qyname ."(". $obj->qyid  .")"."</option>";
		}
		?>
	  </select>
	  <p>选择地图</p>
	  <select class="form-control" id="chusheng_map">
		<option value="">请选择一级区域</option>
	  </select>
	  <div id="search"></div>
      </div> 
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" id="chusheng_bc">保存</button>
      </div>
    </div>
  </div>
</div>
</div>

<script type="text/javascript"> 
function chusheng(){//出生地选择区域加载地图
	 var vs = $("#chushe").val();
	 $("#search").html("");
	 if(vs !=""){
	 $.post('map-gl.php',{basic:"open",type:"Birth",clas:"qydt",chusheng:vs},function(data) {  
	 $("#chusheng_map").html(data);
	 })};
}
function load_add_search(id){//选中搜索结果
	if(!id){return;}
	$.post('map-gl.php',{basic:"open",type:"Birth",clas:"write",set_chusheng:id},function(data) { 
		$('#Birth').modal('hide');
		$("#Birth_xs").val(data);
        $(".con").html("出生地设置-操作成功！"); 
		$("#search").html("");
		$("#btn_Birth").html("修改出生地"); 
    })
}

$(document).ready(function(){ 
	$("#chusheng_bc").click(function(){ //提交出生地设置
		var vs = $("#chusheng_map").val();
	if(!vs){return;}
	$.post('map-gl.php',{basic:"open",type:"Birth",clas:"write",set_chusheng:vs},function(data) { 
		$('#Birth').modal('hide');
		$("#Birth_xs").val(data);
        $(".con").html("出生地设置-操作成功！"); 
		$("#search").html("");
		$("#btn_Birth").html("修改出生地"); 
    })
	})
    $("#subm").click(function(){ 
        var name = $("#game_name").val(); 
        var desc = $("#game_desc").val(); 
		var lvl = $("#lvl").val(); 
		var money = $("#money").val(); 
        $.post('basic.php',{basic:"open",game_name:name,game_desc:desc,game_lvl:lvl,game_money:money},function(data) { 
            (new $.zui.ModalTrigger({title: '提示',custom:data})).show()
            $(".con").html(data); 
        }) 
    }) 
	$("#search_alldt").click(function(){ //搜索所有地图
        var name = $("#sear_alldt").val(); 
		$("#chushe").val(0); 
		$("#chusheng_map").val(0); 
        $.post('map-gl.php',{basic:"open",type:"search",clas:"map",operation:'mid',key:name},function(data) { 
			$("#search").html(data); 
        }) 
	})
	
}) 
</script> 

<?php
require_once "html/footer.php";
?>