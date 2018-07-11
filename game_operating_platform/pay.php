<?php

//支付通知入口

if (!isset($payer_name) || !is_string($payer_name)) {
	//若未设置支付类型名或类型名不是字符串，则直接返回，不予理会
	return;
}

//框架配置
$framework_config  = (object)array();

//设置初始化回调函数
$framework_config->init_func = array("pay_mgr", "init_payer");

//设置初始化回调函数的参数
$framework_config->init_arg = $payer_name;

//设置开始处理请求前的回调函数
$framework_config->before_process_check_request_func = array("pay_mgr", "before_process_check_request");


//框架入口
require_once "index.php";


