<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>GM系统</title>
	<script src="./static/js/lx_http.js" type="text/javascript"></script>
	<script src="./static/js/jquery.md5.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="./static/css/gm.css">
</head>

<script type="text/javascript">

function try_gm_login() {
	//浏览器限制为：Chrome(Chrome下样式效果好)
	if (!(navigator.userAgent.indexOf('Chrome') >= 0)) {
		alert("请使用此gm后台专用浏览器(Chrome)！");
		return;
	}

	var req = {
		opcode:"gm_login",
		arg:{
			username:document.getElementById("username").value,
			password:md5(document.getElementById("password").value),
		},
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), function(rt) {
		rt = JSON.parse(rt);
		if (rt.error_code == 0) {
			//成功则触发进入gm操作页面
			location.reload(true);
		} else {
			//失败则进行错误信息提示
			alert(rt.error_msg);
		}

	});
}

function pass_key_down(event, name) {
	if (event.keyCode == 13) {
		if (name == null) {
			try_gm_login();
		} else {
			var info = document.getElementById(name);
			info.focus();
		}
	}
}
</script>
<body>
	<div class="tab_content_block" style="width:240px;height:180px;position:absolute;top:50%;left:50%;margin:-150px 0 0 -100px;display:block;">
		<div class="tab_title_style">GM系统</div><br/><br/>
		<div class="item">
			<label>用户名</label>
			<input class="input_area" id="username" name="username" type="text" maxlength=16 onkeydown="pass_key_down(arguments[0], 'password')">
		</div>
		<div class="item">
			<label>密码</label>
			<input class="input_area" type="password" id="password" name="password" type="text" maxlength=16 onkeydown="pass_key_down(arguments[0])">
		</div>
		<div class="button">
			<br/>
			<a href="#" class="myButton" onclick="try_gm_login()">登录</a>
		</div>
	</div>
</body>
</html>
