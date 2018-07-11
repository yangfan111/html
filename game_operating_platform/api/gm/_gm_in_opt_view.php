<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>GM系统</title>
	<script src="./static/js/lx_http.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="./static/css/gm.css">
</head>

<script type="text/javascript">
function try_gm_exit() {
	var req = {
		opcode:"gm_exit",
		arg:{},
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), function(rt){
		location.reload(true);
	});
}

function tab_change(name) {
	//调整检测以及结果域是否显示
	var dflag = "";
	var gm_dflag = "none";
	if (name == "gm_command") {
		dflag = "none";
		gm_dflag = "";
	}

	document.getElementById("check_data_string").style.display = dflag;
	document.getElementById("check_data_string_title").style.display = dflag;
	document.getElementById("handler_result_string").style.display = dflag;
	document.getElementById("handler_result_string_title").style.display = dflag;
	document.getElementById("gm_handler_result_string").style.display = gm_dflag;
	document.getElementById("gm_handler_result_string_title").style.display = gm_dflag;



	//切换
	var list = ["recharge", "month_card", "get_gift", "send_mail_to_one", "system_mail", "gm_command"];
	var i = 0;
	for (i = 0; i < list.length; ++i) {
		if (document.getElementById(list[i]))
			document.getElementById(list[i]).style.display="none";
	}

	document.getElementById(name).style.display = "block";
}

/*
* 根据操作码获取实际执行的按钮id
* @param {string} opstr 操作码
* @return {string}
*/
function get_do_button_name(opstr) {
	var end_str = "_check";
	return opstr.substr(0, opstr.length - end_str.length) + "_do_button";
}

//数据检测结果处理
function on_check_data_result(rt) {
	try {
		rt = JSON.parse(rt);
	}
	catch (e) {
		//触发刷新下，因为可能超时了
		location.reload(true);
		return;
	}

	document.getElementById("check_data_string").value = rt.msg;
	document.getElementById("handler_result_string").value = "";

	//显示为了避免双击而隐藏的检查数据按钮
	document.getElementById(rt.opcode + '_button').style.display = "block";

	var idname = get_do_button_name(rt.opcode);
	if (rt.error_code == 0) {
		document.getElementById(rt.opcode + '_button').style.display = "none";
		document.getElementById(idname).style.display = "block";
	}
}

//执行结果
function on_do_result(rt) {
	try {
		rt = JSON.parse(rt);
	}
	catch (e) {
		//触发刷新下，因为可能超时了
		location.reload(true);
		return;
	}

	document.getElementById("check_data_string").value = "";
	document.getElementById("handler_result_string").value = rt.msg;

	//显示为了避免双击而隐藏的执行按钮
	document.getElementById(rt.opcode + '_do_button').style.display = "block";

	if (rt.error_code == 0) {
		alert("执行成功！");
	}

	//把执行按钮变为检测数据
	document.getElementById(rt.opcode + '_do_button').style.display = "none";
	document.getElementById(rt.opcode + '_check_button').style.display = "block";
}

//充值相关处理
function check_and_do_recharge(do_type) {
	var req = {
		arg:{
			game_id:document.getElementById("recharge_game_id").value,
			server_id:document.getElementById("recharge_server_id").value,
			char_dbid:document.getElementById("recharge_char_dbid").value,
			recharge_num:document.getElementById("recharge_recharge_num").value,
			give_num:document.getElementById("recharge_give_num").value,
		},
	}

	var func = on_check_data_result;
	if (do_type == "do") {
		req.opcode = "gm_recharge";
		func = on_do_result;

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_do_button').style.display = "none";
	} else {
		req.opcode = "gm_recharge_check";

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_button').style.display = "none";
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), func);
}

//月卡相关处理
function check_and_do_month_card(do_type) {
	var req = {
		arg:{
			game_id:document.getElementById("month_card_game_id").value,
			server_id:document.getElementById("month_card_server_id").value,
			char_dbid:document.getElementById("month_card_char_dbid").value,
		},
	}

	var func = on_check_data_result;
	if (do_type == "do") {
		req.opcode = "gm_month_card";
		func = on_do_result;

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_do_button').style.display = "none";
	} else {
		req.opcode = "gm_month_card_check";

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_button').style.display = "none";
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), func);
}

//礼包相关处理
function check_and_do_get_gift(do_type) {
	var req = {
		arg:{
			game_id:document.getElementById("get_gift_game_id").value,
			server_id:document.getElementById("get_gift_server_id").value,
			char_dbid:document.getElementById("get_gift_char_dbid").value,
			gift_config_id:document.getElementById("get_gift_gift_id").value,
		},
	}

	var func = on_check_data_result;
	if (do_type == "do") {
		req.opcode = "gm_get_gift";
		func = on_do_result;

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_do_button').style.display = "none";
	} else {
		req.opcode = "gm_get_gift_check";

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_button').style.display = "none";
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), func);
}

//增加指定类型、指定id、指定数量
function add_to_att_obj(obj, tp, id, num) {
	if (!obj[tp])
		obj[tp] = {};

	obj[tp][id] = num;
}

function mail_common_make_req(name) {
	//把钻石、金币都归到附件列表中
	var gold_num = document.getElementById(name + "_gold_num").value;
	var money_num = document.getElementById(name + "_money_num").value;
	var attlist = document.getElementById(name + "_attachment_list").value;

	var attachment_list = "";

	if (gold_num != "" || money_num != "" || attlist != "") {
		attachment_list = {};

		if (gold_num != "") {
			//钻石归为道具
			add_to_att_obj(attachment_list, "1", "2001", gold_num);
		}

		if (money_num != "") {
			//金币归为道具
			add_to_att_obj(attachment_list, "1", "1001", money_num);
		}

		if (attlist != "") {
			var lt = attlist.split(',');
			if (lt.length > 0) {
				for (var i = 0; i < lt.length; ++i) {
					var line = lt[i];
					var temp = line.split('+');
					if (temp.length == 3) {
						add_to_att_obj(attachment_list, temp[0], temp[1], temp[2]);
					}
				}
			}
		}
	}

	var req = {
		arg:{
			game_id:document.getElementById(name + "_game_id").value,
			server_id:document.getElementById(name + "_server_id").value,
			sender:document.getElementById(name + "_sender").value,
			title:document.getElementById(name + "_title").value,
			content:document.getElementById(name + "_content").value,
			attachment:attachment_list,
		},
	}

	return req
}

//发送邮件到玩家
function check_and_do_player_mail(do_type) {
	var req = mail_common_make_req("send_mail_to_one");
	req.arg.char_dbid = document.getElementById("send_mail_to_one_char_dbid").value;

	var func = on_check_data_result;
	if (do_type == "do") {
		req.opcode = "gm_player_mail";
		func = on_do_result;

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_do_button').style.display = "none";
	} else {
		req.opcode = "gm_player_mail_check";

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_button').style.display = "none";
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), func);
}

//发送系统邮件
function check_and_do_system_mail(do_type) {
	var req = mail_common_make_req("system_mail");
	req.arg.channel = document.getElementById("system_mail_channel").value;
	req.arg.start_time = document.getElementById("system_mail_start_time").value;
	req.arg.end_time = document.getElementById("system_mail_end_time").value;
	req.arg.level = document.getElementById("system_mail_level").value;
	req.arg.vip = document.getElementById("system_mail_vip").value;

	var func = on_check_data_result;
	if (do_type == "do") {
		req.opcode = "gm_system_mail";
		func = on_do_result;

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_do_button').style.display = "none";
	} else {
		req.opcode = "gm_system_mail_check";

		//避免双击，先隐藏
		document.getElementById(req.opcode + '_button').style.display = "none";
	}

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), func);
}

//gm命令执行结果
function on_gm_command_result(rt) {
	try {
		rt = JSON.parse(rt);
	}
	catch (e) {
		//触发刷新下，因为可能超时了
		location.reload(true);
		return;
	}

	document.getElementById("gm_handler_result_string").value = rt.msg;

	//显示按钮
	document.getElementById(rt.opcode + '_do_button').style.display = "block";

	if (rt.alert) {
		alert(rt.alert);
	}

	if (rt.error_code == 0) {
		document.getElementById("gm_command_cmd").value = "";
	}
}

//执行gm命令
function do_gm_command() {
	var req = {
		arg:{
			game_id:document.getElementById("gm_command_game_id").value,
			server_id:document.getElementById("gm_command_server_id").value,
			cmd:document.getElementById("gm_command_cmd").value,
		},
	}

	if (req.arg.game_id == "") {
		alert("游戏id不能为空");
		return;
	}

	if (req.arg.server_id == "") {
		alert("服务器组id不能为空");
		return;
	}


	req.opcode = "gm_command";

	//隐藏按钮，等收到回馈后再显示按钮
	document.getElementById(req.opcode + '_do_button').style.display = "none";

	lx.http_post("./gm.php", 'req_data=' + JSON.stringify(req), on_gm_command_result);
}
</script>
<body >
	<div id="top_title">
		<div style="text-align:right;float:left;position:relative;left:100px;">
			<a class="href_style" href="#recharge" onclick="tab_change('recharge')">充值</a>
			<a class="href_style" href="#month_card" onclick="tab_change('month_card')">月卡</a>
			<a class="href_style" href="#get_gift" onclick="tab_change('get_gift')">礼包</a>
			<a class="href_style" href="#send_mail_to_one" onclick="tab_change('send_mail_to_one')">玩家邮件</a>
			<a class="href_style" href="#system_mail" onclick="tab_change('system_mail')">系统邮件</a>
			<a class="href_style" href="#gm_command" onclick="tab_change('gm_command')">gm命令</a>
		</div>
		<div style="float:right;position:relative;right:10px;">
			<a href="#" class="myButton" onclick="try_gm_exit()">退出系统</a>
		</div>
		<div style="clear:both;margin:0px;"></div>
		<div id="xxb" style="height:2px;"></div>
	</div>

	<br/>
	<div id="recharge" class="tab_content_block" style="display:block;">
		<div class="tab_title_style">此处充值不参与充值配置处理<br/>不会触发月卡等</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="recharge_game_id" name="recharge_game_id" value="_self_game" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="recharge_server_id" name="recharge_server_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>角色dbid</label>
			<input class="input_area" id="recharge_char_dbid" name="recharge_char_dbid" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>充值的rmb货币数</label>
			<input class="input_area" id="recharge_recharge_num" name="recharge_recharge_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>赠送的rmb货币数</label>
			<input class="input_area" id="recharge_give_num" name="recharge_give_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="button">
			<br/>
			<a id="gm_recharge_check_button" href="#" class="myButton" onclick="check_and_do_recharge('check')">检查数据</a>
			<a id="gm_recharge_do_button" href="#" class="myButton" style="display:none" onclick="check_and_do_recharge('do')">充值</a>
		</div>
	</div>
	<div id="month_card" class="tab_content_block">
		<div class="tab_title_style">发月卡</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="month_card_game_id" name="month_card_game_id" value="_self_game" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="month_card_server_id" name="month_card_server_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>角色dbid</label>
			<input class="input_area" id="month_card_char_dbid" name="month_card_char_dbid" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="button">
			<br/>
			<a id="gm_month_card_check_button" href="#" class="myButton" onclick="check_and_do_month_card('check')">检查数据</a>
			<a id="gm_month_card_do_button" href="#" class="myButton" style="display:none" onclick="check_and_do_month_card('do')">发月卡</a>
		</div>
	</div>
	<div id="get_gift" class="tab_content_block">
		<div class="tab_title_style">送礼包</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="get_gift_game_id" name="get_gift_game_id" value="_self_game" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="get_gift_server_id" name="get_gift_server_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>角色dbid</label>
			<input class="input_area" id="get_gift_char_dbid" name="get_gift_char_dbid" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>礼包配置id</label>
			<input class="input_area" id="get_gift_gift_id" name="get_gift_gift_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="button">
			<br/>
			<a id="gm_get_gift_check_button" href="#" class="myButton" onclick="check_and_do_get_gift('check')">检查数据</a>
			<a id="gm_get_gift_do_button" href="#" class="myButton" style="display:none" onclick="check_and_do_get_gift('do')">送礼包</a>
		</div>
	</div>
	<div id="send_mail_to_one" class="tab_content_block">
		<div class="tab_title_style">发邮件到指定玩家</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="send_mail_to_one_game_id" name="send_mail_to_one_game_id" value="_self_game" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="send_mail_to_one_server_id" name="send_mail_to_one_server_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>角色dbid</label>
			<input class="input_area" id="send_mail_to_one_char_dbid" name="send_mail_to_one_char_dbid" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>发件人</label>
			<input class="input_area" id="send_mail_to_one_sender" name="send_mail_to_one_sender" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>邮件标题</label>
			<input class="input_area" id="send_mail_to_one_title" name="send_mail_to_one_title" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>邮件正文</label>
			<textarea class="input_area" id="send_mail_to_one_content" style="resize: none;" rows="2" cols="21" maxlength=128></textarea>
		</div>
		<br/>
		<div style="border:1px dashed #000;padding:5px;background-color:#EECFA1;clear:both">
			<div>附件列表</div>
			<div class="item">
				<label>rmb货币(换算后)</label>
				<input class="input_area" id="send_mail_to_one_gold_num" name="send_mail_to_one_gold_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
			</div>
			<div class="item">
				<label>游戏币</label>
				<input class="input_area" id="send_mail_to_one_money_num" name="send_mail_to_one_money_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
			</div>
			<div>
				<label><br/>附件列表(内容格式(英文加号、逗号)：<br/>类型+id+数量,类型+id+数量)<br/>注：格式错误的数据会被过滤掉</label>
				<br/>
				<label></label>
				<textarea class="input_area" id="send_mail_to_one_attachment_list" style="resize: none;" rows="2" cols="21" maxlength=512></textarea>
				<br/>
				<br/>
			</div>
		</div>
		<div class="button">
			<br/>
			<a href="#" id="gm_player_mail_check_button" class="myButton" onclick="check_and_do_player_mail('check')">检查数据</a>
			<a href="#" id="gm_player_mail_do_button" class="myButton" style="display:none" onclick="check_and_do_player_mail('do')">发邮件</a>
		</div>
	</div>
	<div id="system_mail" class="tab_content_block">
		<div class="tab_title_style">发送系统邮件<br/>
			<div style="color:#CD2626;">
				(时间格式&nbsp;2017-01-01 00:00:00)
			</div>
		</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="system_mail_game_id" name="system_mail_game_id" value="_self_game" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="system_mail_server_id" name="system_mail_server_id" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>渠道</label>
			<input class="input_area" id="system_mail_channel" name="system_mail_channel" type="text" maxlength=512>
		</div>
		<div class="item">
		<label>开始时间</label>
			<input class="input_area" id="system_mail_start_time" name="system_mail_start_time" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>结束时间</label>
			<input class="input_area" id="system_mail_end_time" name="system_mail_end_time" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>等级大于等于</label>
			<input class="input_area" id="system_mail_level" name="system_mail_level" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>vip大于等于</label>
			<input class="input_area" id="system_mail_vip" name="system_mail_vip" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="item">
			<label>发件人</label>
			<input class="input_area" id="system_mail_sender" name="system_mail_sender" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>邮件标题</label>
			<input class="input_area" id="system_mail_title" name="system_mail_title" type="text" maxlength=64>
		</div>
		<div class="item">
			<label>邮件正文</label>
			<textarea class="input_area" id="system_mail_content" style="resize: none;" rows="2" cols="21" maxlength=128></textarea>
		</div>
		<br/>
		<div style="border:1px dashed #000;padding:5px;background-color:#EECFA1;clear:both;">
			<div>附件列表</div>
			<div class="item">
				<label>rmb货币(换算后)</label>
				<input class="input_area" id="system_mail_gold_num" name="system_mail_gold_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
			</div>
			<div class="item">
				<label>游戏币</label>
				<input class="input_area" id="system_mail_money_num" name="system_mail_money_num" type="text" maxlength=64 onkeyup="this.value=this.value.replace(/\D/g,'')">
			</div>
			<div>
				<label><br/>附件列表(内容格式(英文加号、逗号)：<br/>类型+id+数量,类型+id+数量)<br/>注：格式错误的数据会被过滤掉</label>
				<br/>
				<label></label>
				<textarea class="input_area" id="system_mail_attachment_list" style="resize: none;" rows="2" cols="21" maxlength=512></textarea>
				<br/>
				<br/>
			</div>
		</div>
		<div class="button">
			<br/>
			<a href="#" id="gm_system_mail_check_button" class="myButton" onclick="check_and_do_system_mail('check')">检查数据</a>
			<a href="#" id="gm_system_mail_do_button" class="myButton" style="display:none" onclick="check_and_do_system_mail('do')">发邮件</a>
		</div>
	</div>
	<div id="gm_command" class="gm_command_tab_content_block">
		<div class="tab_title_style">执行gm命令</div>
		<div class="item">
			<label>游戏id</label>
			<input class="input_area" id="gm_command_game_id" name="gm_command_game_id" value="_self_game" type="text" maxlength=15>
		</div>
		<div class="item">
			<label>服务器组id</label>
			<input class="input_area" id="gm_command_server_id" name="gm_command_server_id" type="text" maxlength=15 onkeyup="this.value=this.value.replace(/\D/g,'')">
		</div>
		<div class="button">
			<br/>
			<a href="#" id="gm_command_do_button" class="myButton" onclick="do_gm_command()">执行命令</a>
		</div>
		<div style="display:inline-block;">
			<div class="tab_title_style">gm命令</div>
			<div class="tab_title_style">（为空表示查询尚未执行完毕、已执行完毕的gm命令）</div>
			<textarea id="gm_command_cmd" style="resize: none;" rows="4" cols="52"></textarea>
		</div>
	</div>

	<div id="check_data_and_result" class="check_data_and_result">
		<div style="display:inline-block;">
			<div id="check_data_string_title" class="tab_title_style">检查后的操作信息<br/></div>
			<textarea id="check_data_string" readonly="readonly" style="resize: none;" rows="18" cols="52"></textarea>
		</div>

		<div style="display:inline-block;">
			<div id="handler_result_string_title" class="tab_title_style">操作结果描述<br/></div>
			<textarea id="handler_result_string" readonly="readonly" style="resize: none;" rows="18" cols="52"></textarea>
		</div>
		<div style="display:inline-block;">
			<div id="gm_handler_result_string_title" class="tab_title_style" style="display:none;">gm操作结果描述<br/></div>
			<textarea id="gm_handler_result_string" readonly="readonly" style="resize:none;display:none;" rows="18" cols="104"></textarea>
		</div>
	</div>

</body>
</html>
