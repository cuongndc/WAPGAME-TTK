<?php
require_once "user_rights.php";
require_once "html/header.php";
?>
<div class="container">
<h2><?php echo $Subheading; ?></h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一条属性</b></td><td style="text-align:right">
<button class="btn btn-primary" type="button" data-position="100" data-toggle="modal" data-target="#ajax-alert" onclick="Establish('<?php echo $page;?>')">添加新属性</button>
</td></tr>
</table>
<table class="table table-bordered table-condensed">
  <thead>
    <tr><th>属性名</th><th>类型</th><th>可视</th><th>备注</th><th>操作</th></tr>
  </thead>
	<tbody id="map_alist">
	<?php echo $attribute->get_attribute_list($page);?>
	</tbody>
</table>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>
</div>
<!-- ZUI Javascript 依赖 jQuery -->
<script type="text/javascript"> 

function map_an(){ 
        var name = $("#aname").val(); 
        var type = $("#atype").val(); 
		var desc = $("#adesc").val(); 
		var sh = $("#ash").val(); 
		var xh = $("#axh").val();  
        $.post('attribute-gl.php',{basic:"open",type:"map_an",name:name,desc:desc,mtype:type,sh:sh,xh:xh},function(data) { 
			$(".con").html(data.title); 
			$('#alert-title').html(data.title); 
			$('#alert-body').html(data.body); 
			$('#alert-button').html(data.button);
			$("#map_alist").html(data.html); 
        }) 
    }
	
function Edit_write(){ 
        var name = $("#aname").val();
        var yname = $("#yname").val(); 		
        var type = $("#atype").val(); 
		var desc = $("#adesc").val(); 
		var sh = $("#ash").val(); 
		var xh = $("#axh").val(); 
		var max = $("#max").val(); 
        $.post('attribute-gl.php',{basic:"open",type:"Edit_write",name:name,desc:desc,mtype:type,sh:sh,xh:xh,yname:yname,max:max},function(data) { 
			$(".con").html(data.title); 
			$('#alert-title').html(data.title); 
			$('#alert-body').html(data.body); 
			$('#alert-button').html(data.button);
			$("#map_alist").html(data.html); 
        }) 
    }
	
function Establish(table){
	$.post('attribute-gl.php',{basic:"open",type:"Establish",table:table},function(data) { 
		$('#alert-title').html(data.title); 
		$('#alert-body').html(data.body); 
		$('#alert-button').html(data.button);
	})
}

function del(name,val){
	$('#alert-title').html('确认删除项目？'); 
	$('#alert-body').html('<h4>('+name +'->'+val+')这个属性？</h4>'); 
	$('#alert-button').html('<button type="button" class="btn btn-default" data-dismiss="modal">取消</button> <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="Edit(\'delete\',\''+val+'\',\'tcc\')">确认删除</button></div>'); 
}

function Edit(type,val){
	$.post('attribute-gl.php',{basic:"open",type:type,name:val},function(data) { 
		if(type == "Edit_read"){
			$('#alert-title').html(data.title); 
			$('#alert-body').html(data.body); 
			$('#alert-button').html(data.button); 
		}else{
			(new $.zui.ModalTrigger({title: '提示',custom:data.title,position:100})).show();
			$(".con").html(data.title); 
			$("#map_alist").html(data.html);
			} 
    })
}
</script> 
<?php
require_once "html/footer.php";
?>