<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<!-- ZUI 标准版压缩后的 CSS 文件 -->
<link href="https://cdn.bootcss.com/zui/1.9.1/css/zui.min.css" rel="stylesheet">
<!-- ZUI Javascript 依赖 jQuery -->
<script src="https://cdn.bootcss.com/zui/1.9.1/lib/jquery/jquery.js"></script>
<!-- ZUI 标准版压缩后的 JavaScript 文件 -->
<script src="https://cdn.bootcss.com/zui/1.9.1/js/zui.min.js"></script>

<script src="../js/global.js?_=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="../css/global.css?_=<?php echo time(); ?>">
    <title><?php echo $变量_系统->游戏名称."-" ?>设计后台</title>
<style>
h2{
	margin-top: 0px;
}
</style>
</head>

<body>
<div class="modal fade" id="ajax-alert">
  <div class="modal-dialog">
	<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button><h4 class="modal-title" id="alert-title"></h4></div>
      <div class="modal-body" id="alert-body"></div>
      <div class="modal-footer" id="alert-button"></div>
    </div>
  </div>
</div>

<div class="container">
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="admin.php"><?php echo $变量_系统->游戏名称."-" ?>游戏设计后台</a>
    </div>
  </div>
</nav>
