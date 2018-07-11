<?php
/*
 * 请求总入口
 */


require_once "api_init.php";

if (!isset($framework_config)) {
	$framework_config = null;
}

//初始化
api_app::init($framework_config);

//记录请求
api_app::logRequest();

//获取请求数据
$req_data = getgpc("req_data", "P");

//处理请求
api_app::process($req_data);


