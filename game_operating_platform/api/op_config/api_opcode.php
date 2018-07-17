<?php

class api_opcode
{
	public static $opcode = array(
		//注册
		'register' => array(
			'note' => '普通注册',					#注释
			'class' => 'user_register',				#类名
			'method' => 'normal_register',			#方法名
			'response_zlib' => 'false',				#回应开启zlib压缩？默认不开启
			'arg_list' => array(					#参数列
				'username', 'password', 'channel', 'game_id'),
		),

		//游客试玩(自动注册)
		'tourists' => array(
			'note' => '游客试玩',
			'class' => 'user_register',
			'method' => 'tourists_register',
			'arg_list' => array(
				'password', 'channel', 'game_id'),
		),

		//登录
		'login' => array(
			'note' => '普通登录',
			'class' => 'user_login',
			'method' => 'normal_login',
			'arg_list' => array(
				'username', 'password', 'channel', 'game_id'),
		),

		'channel_login' => array(
			'note' => '渠道登录',
			'class' => 'user_login',
			'method' => 'channel_login',
			'arg_list' => array(
				'session', 'channel', 'game_id'),
		),

		//修改密码
		'change_pwd' => array(
			'note' => '修改密码',
			'class' => 'user_change_info',
			'method' => 'change_password',
			'arg_list' => array('new_password'),
		),

		//修改用户名
		'change_user' => array(
			'note' => '修改用户名',
			'class' => 'user_change_info',
			'method' => 'change_user',
			'arg_list' => array('new_username'),
		),

		//修改用户名和密码
		'change_user_pwd' => array(
			'note' => '修改用户名和密码',
			'class' => 'user_change_info',
			'method' => 'change_user_pwd',
			'arg_list' => array('new_username', 'new_password'),
		),

		//服务器列表
		'server_list' => array(
			'note' => '服务器列表',
			'class' => 'user_game',
			'method' => 'get_server_list',
			'response_zlib' => 'true',
			'arg_list' => array(),
		),

		//进入指定服进行游戏
		'enter_game' => array(
			'note' => '进入指定服进行游戏',
			'class' => 'user_game',
			'method' => 'enter_game',
			'arg_list' => array('server_id', 'random_a'),
		),

		//使用礼包
		'use_gift' => array(
			'note' => '使用礼包',
			'class' => 'user_gift',
			'method' => 'try_use',
			'arg_list' => array(
				'channel',			#渠道
				'game_id',			#游戏标识
				'server_id',		#服务器id
				'gift_code',		#礼包码
				'char_dbid',		#要使用礼包码的角色dbid
			),
		),

		//用户执行充值(仅作测试流程用，并不会用到实际的运营中)
		'do_recharge' => array(
			'note' => '用户执行充值',
			'class' => 'user_recharge',
			'method' => 'do_recharge',
			'arg_list' => array(
				'rmb',					#充值的金额，单位：元
				'server_id',			#要充值的服务器id
				'char_dbid',			#本次执行充值的角色dbid
			),
		),



		/////////////////////////////////////////////////////////////////////////////////
		//		gm 操作等
		/////////////////////////////////////////////////////////////////////////////////
		//gm登录
		'gm_login' => array(
			'note' => 'gm登录',
			'class' => 'gm_base_handler',
			'method' => 'gm_login',
			'arg_list' => array(
				'username', 'password'
			),
		),

		//gm退出
		'gm_exit' => array(
			'note' => 'gm退出',
			'class' => 'gm_base_handler',
			'method' => 'gm_exit',
			'arg_list' => array(),
		),

		//gm充值检测数据
		'gm_recharge_check' => array(
			'note' => 'gm充值数据检测',
			'class' => 'gm_process',
			'method' => 'check_recharge',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid', 'recharge_num', 'give_num',
			),
		),

		//gm执行充值
		'gm_recharge' => array(
			'note' => 'gm执行充值',
			'class' => 'gm_process',
			'method' => 'do_recharge',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid', 'recharge_num', 'give_num',
			),
		),

		//gm月卡检测数据
		'gm_month_card_check' => array(
			'note' => 'gm月卡数据检测',
			'class' => 'gm_process',
			'method' => 'check_month_card',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid',
			),
		),

		//gm执行月卡
		'gm_month_card' => array(
			'note' => 'gm执行月卡',
			'class' => 'gm_process',
			'method' => 'do_month_card',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid',
			),
		),

		//gm礼包检测数据
		'gm_get_gift_check' => array(
			'note' => 'gm礼包数据检测',
			'class' => 'gm_process',
			'method' => 'check_get_gift',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid', 'gift_config_id',
			),
		),

		//gm执行礼包
		'gm_get_gift' => array(
			'note' => 'gm执行礼包',
			'class' => 'gm_process',
			'method' => 'do_get_gift',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid', 'gift_config_id',
			),
		),

		//gm发邮件到玩家检测数据
		'gm_player_mail_check' => array(
			'note' => 'gm发邮件到玩家数据检测',
			'class' => 'gm_process',
			'method' => 'check_player_mail',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid',
				'sender', 'title', 'content', 'attachment',
			),
		),

		//gm执行发邮件到玩家
		'gm_player_mail' => array(
			'note' => 'gm执行发邮件到玩家',
			'class' => 'gm_process',
			'method' => 'do_player_mail',
			'arg_list' => array(
				'game_id', 'server_id', 'char_dbid',
				'sender', 'title', 'content', 'attachment',
			),
		),

		//gm发系统邮件检测数据
		'gm_system_mail_check' => array(
			'note' => 'gm发系统邮件',
			'class' => 'gm_process',
			'method' => 'check_system_mail',
			'arg_list' => array(
				'game_id', 'server_id',
				'start_time', 'end_time',
				'level', 'vip',
				'sender', 'title', 'content', 'attachment',
			),
		),

		//gm执行发系统邮件
		'gm_system_mail' => array(
			'note' => 'gm执行发系统邮件',
			'class' => 'gm_process',
			'method' => 'do_system_mail',
			'arg_list' => array(
				'channel', 'game_id', 'server_id',
				'start_time', 'end_time',
				'level', 'vip',
				'sender', 'title', 'content', 'attachment',
			),
		),

		//gm执行命令
		'gm_command' => array(
			'note' => 'gm执行命令',
			'class' => 'gm_process',
			'method' => 'do_gm_command',
			'arg_list' => array(
				'game_id', 'server_id', 'cmd',
			),
		),
	);
}


/*
请求 注册：
	普通注册：
		用户名
		密码
		渠道
		游戏id

	游客试玩(web端自动生成用户名，成功时，会附带用户名返回)：
		密码
		渠道
		游戏id


回应 普通注册/游客试玩：
	消息号
	错误码
	错误信息
		若是游客试玩，则回应附带用户名


请求 登录：
	普通登录：
		用户名
		密码
		渠道
		游戏id


	渠道登录：
		session
		渠道
		游戏id


回应 普通登录/渠道登录：
	消息号
	错误码
	错误信息


请求 修改密码：
	新密码

回应 修改密码：
	消息号
	错误码
	错误信息

请求 修改用户名：
	新用户名

回应 修改用户名：
	消息号
	错误码
	错误信息


请求 服务器列表


回应 服务器列表(zlib压缩)：
	消息号
	错误码
	错误信息
	服务器列表信息


请求 进入指定服进行游戏
	服务器id
	客户端随机数a

回应 进入指定服进行游戏：
	消息号
	错误码
	错误信息

请求 使用礼包
	渠道
	游戏id
	服务器id
	礼包码
	要使用礼包的角色dbid

回应 使用礼包
	消息号
	错误码
	错误信息

请求 用户执行充值
	rmb金额
	要充值的服务器id
	对哪个角色充值

回应 用户执行充值
	消息号
	错误码
	错误信息

请求 gm登录
	用户名
	密码

回应 gm登录
	消息号
	错误码
	错误信息

请求 gm退出

回应 gm退出
	消息号
	错误码
	错误信息

*/


