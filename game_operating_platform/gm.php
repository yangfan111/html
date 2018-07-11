<?php

//后台管理入口

//框架配置
$framework_config  = (object)array();    

//设置开始处理请求前的回调函数
$framework_config->before_process_check_request_func = array("gm_mgr", "before_process_check_request");

//设置请求处理完毕时的回调函数
$framework_config->process_request_end_func = array("gm_mgr", "process_request_end"); 

//框架入口
require_once "index.php";

