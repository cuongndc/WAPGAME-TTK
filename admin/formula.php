<?php
require_once "user_rights.php";

$token_sys = $_SESSION['token'] ;
$sid_sys = $_SESSION['sid'] ;
$uid_sys = $_SESSION['uid'] ;
$power_sys = $_SESSION['power'] ;
$_SESSION = array();
$_SESSION['power'] = $power_sys;
$_SESSION['sid'] = $sid_sys;
$_SESSION['token'] = $token_sys;
$_SESSION['uid'] = $uid_sys;

$test=file_get_contents("php://input"); 
parse_str($test,$value);
//print_r($value);

if(isset($value['basic'])){
	switch ($value["type"]){
	case "new_add" :
		$arry = add_new($value);
	break;
	case "new" :
		$arry = new_window();
	break;
	case "edit":
		$arry = edit_formula($value);
	break;
	case "del_open":
		$arry = del_formula($value);
	break;
	case "del":
		$arry = del_math($value);
	break;
	case "reload":
		$arry = loa_data($value['page'],$value['recPerPage']);
	break;
	}
	ajax_alert($arry);
exit;
}

function loa_data($page,$recPerPage=20){//加载表达式列表
	global $formula;
	$alert_open = alert_open;
	$obj = $formula->get_math_all($page,$recPerPage);
	    foreach($obj->data as $odj){
			$html .= "<tr>
			<td>$odj->math_name</td><td>$odj->math_notes</td><td>";
			switch ($odj->math_type){
				case "number" :
				$html .= "数值型";
				break;
				case "condition" :
				$html .= "逻辑型";
				break;
				case "text" :
				$html .= "文本型";
				break;
			}
			$html .="</td>
			<td><button class='btn btn-primary' type='button' {$alert_open} onclick='edit(\"$odj->id\")'>修改</button>
			<button class='btn btn-danger '{$alert_open} type='button' onclick='del_open(\"$odj->id\")'>删除</button></td>
			</tr>";
		}
		return array('list'=>$html,'recTotal'=>$obj->num,'recPerPage'=>20);
	}

function add_new($val){//保存或修改表达式数据
	global $formula;
	if(!G_isPermit($val["name"])){
	  return array('title'=>'表达式标识符只能是"字母"+"数字"+"_"组成的3-16位字符串' ,'error'=>"true");
		
	}
	if(!G_isPermit($val["old_name"]) && $val["old_name"]!=""){
	  return array('title'=>'表达式标识符只能是"字母"+"数字"+"_"组成的3-16位字符串'  ,'error'=>"true");
	}
	$temp = $formula->set_math($val["old_name"],$val["name"],$val["typel"],$val["string"],$val["notes"]);
	if(!$temp){$title = "对表达式的修改操作失败了！"; }
	if($temp=='new'){$title = "表达式修改成功！"; $reload = true;}
	if($temp=='edit'){$title = "表达式新建成功！"; $reload = true;}

	return array('title'=>$title,'body'=>$title,'reload'=>$reload);
}

function del_math($value){//删除一个表达式
	global $formula;
	if($formula->del_math_id($value["id"])){
		$arry = array('title'=>"删除表达式成功" ,'body'=>'表达式删除成功！', 'reload' => true);
	}else{
		$arry = array('title'=>"删除表达式失败" ,'error'=>'true');
	}
	return $arry;
}

function new_window(){//加载新建表达式界面
	$body ='
	  	<input id="old_name" type="hidden" value="">
		表达式标识：<input type="text" id="name" class="form-control" placeholder="表达式标识">
		表达式备注：<input type="text" id="notes" class="form-control" placeholder="表达式备注">
		表达式类型：
		<select class="form-control" id="type">
			<option value="number">数值型</option>
			<option value="condition">条件型</option>
			<option value="text">文本型</option>
		</select>
		表达式内容：
		<textarea class="form-control" rows="3" id="string" placeholder="表达式内容"></textarea>';
		
	return array('title'=>'新建表达式',
				'body'=>$body,
				'exbtn'=>true,
				'btn'=>'<button type="button" class="btn btn-primary" onclick="new_add()">保存</button>'
			);
}

function edit_formula($value){//加载表达式编辑界面
	global $formula;
	$math = $formula->get_math_info($value["id"]);
	$body = <<<html
	<input id="old_name" type="hidden" value="{$math->math_name}">
	表达式标识：<input type="text" id="name" class="form-control" placeholder="表达式标识" value="{$math->math_name}">
	表达式备注：<input type="text" id="notes" class="form-control" placeholder="表达式备注" value="{$math->math_notes}">
	表达式类型：
	<select class="form-control" id="type">
		<option value="number"
html;
	if($math->math_type=="number"){$body .= "selected";} $body .=  '>数值型</option>
	<option value="condition"';
	if($math->math_type=="condition"){$body .=  "selected";} $body .=  '>条件型</option>
	<option value="text"';
	if($math->math_type=="text"){$body .=  "selected";} 
	$body .=  <<<html
	>文本型</option>
	</select>
	表达式内容：
	<textarea class="form-control" rows="3" id="string"  placeholder="表达式内容" >{$math->math_string}</textarea>
html;
	return array('title'=> '修改表达式','body'=>$body ,'btn'=>'<button type="button" class="btn btn-primary" onclick="new_add()">保存修改</button>','exbtn'=>true);
}

function del_formula($value){//加载表达式删除界面
	global $formula;
	$formula = $formula->get_math_info($value["id"]);
	$body = <<<html
		<input id="old_name" type="hidden" value="{$formula->math_name}" readonly>
		表达式标识：<input type="text" id="name" class="form-control" placeholder="表达式标识" value="{$formula->math_name}" readonly>
		表达式备注：<input type="text" id="notes" class="form-control" placeholder="表达式备注" value="{$formula->math_notes}" readonly>
		表达式类型：
		<select class="form-control" id="type" disabled>
			<option value="number"
html;
			if($formula->math_type=="number"){$body .= "selected";} $body .=  '>数值型</option>
			<option value="condition"';
			if($formula->math_type=="condition"){$body .=  "selected";} $body .=  '>条件型</option>
			<option value="text"';
			if($formula->math_type=="text"){$body .=  "selected";} 
		$body .=  <<<html
		>文本型</option>
		</select>
		表达式内容：
		<textarea class="form-control" rows="3" id="string"  placeholder="表达式内容"  readonly="readonly">{$formula->math_string}</textarea>
html;
		return array(
				'title'=>'删除表达式',
				'body'=>$body,
				'btn'=>'<button type="button" class="btn btn-primary" onclick="del(\''.$formula->id .'\')">确认删除</button>',
				'exbtn'=>true
			);
}

require_once "html/header.php";
?>

<h2>表达式定义</h2>
<span class="con"></span> 
<table  class="table"> 
<tr><td><b>新建一条表达式</b></td><td style="text-align:right">
<button class="btn btn-primary" type="button" <?php echo alert_open;?> onclick="new_formula()">增加表达式</button>
</td></tr>
</table>

<table class="table table-condensed">
  <thead>
    <tr>
      <th>引用名</th>
      <th>说明</th>
	  <th>类型</th>
	  <th>操作</th>
    </tr>
  </thead>
  <tbody id="math_list"></tbody>
</table>

<ul class="pager" id="pager"></ul>

<a href="#"  class="btn btn-block " onClick="javaScript:history.go(-1)">返回上级</a>
<br>
<br>


<script>
//显示列表翻页控制-开始

$('#pager').pager({// 手动进行初始化
    page: 1,
	recPerPage:20,
	elements:['first', 'prev', 'pages', 'next', 'last', 'page_of_total_text', 'items_range_text', 'total_text','goto','size_menu'],
});

$(document).ready(function () { 
$('#pager').on('onPageChange', function(e, state, oldState) {
	if (state.page !== oldState.page || state.recPerPage !== oldState.recPerPage) {reload_math();}
});
	reload_math();
}); 

function reload_math(){//重新加载子类型数据
	var myPager = $('#pager').data('zui.pager');// 获取分页器实例对象
	var pager = myPager.state;
	$.post('formula.php',{basic:"open",type:"reload",page:pager.page,recPerPage:pager.recPerPage},function(data) { 
		$("#math_list").html(data.list);
		if(myPager){
		myPager.set({
			recTotal: data.recTotal,
			recPerPage: data.recPerPage
		});
		};
    }) 
 }

function new_formula(){//新建公式界面请求
	$.post('formula.php',{basic:"open",type:"new"},function(data) {
		ajax_alert(data);
	})
}

function new_add(){//新建或编辑公式操作
	var name=$("#name").val();
	var old_name=$("#old_name").val();
	var typel=$("#type").val();
	var notes=$("#notes").val();
	var string=$("#string").val();
	var idata = {basic:"open",type:"new_add",name:name,typel:typel,notes:notes,string:string,old_name:old_name};
	$.post('formula.php',idata,function(data) {
		$(".con").html(data.title); 
		if(data.body){ajax_alert(data);};
		if(data.error){ajax_alert(data);};
		if(data.reload){reload_math();};
	})
}

function edit(id){//编辑公式请求
	$.post('formula.php',{basic:"open",type:"edit",id:id},function(data) {ajax_alert(data);if(data.reload){reload_math();};})
}

function del_open(id){//删除公式请求
	$.post('formula.php',{basic:"open",type:"del_open",id:id},function(data){ajax_alert(data);if(data.reload){reload_math();};})
}

function del(id){//删除公式操作
	$.post('formula.php',{basic:"open",type:"del",id:id},function(data) {
		$(".con").html(data.title); ajax_alert(data);if(data.reload){reload_math();};
	})
}

</script>

<?php
require_once "html/footer.php";
?>