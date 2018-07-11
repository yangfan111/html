<?php
/*
 * api模块初始化入口
 */

//api部分需要用session
session_start();

define('API_ROOT', dirname(__FILE__));

$_class_arr = array(
	'api_app' => API_ROOT . '/base/api_app.php',
	'api_process' => API_ROOT . '/base/api_process.php',
	'api_result' => API_ROOT . '/base/api_result.php',
	'api_opcode' => API_ROOT . '/base/api_opcode.php',
	'api_operating_log' => API_ROOT . '/base/api_operating_log.php',
	'user_center' => API_ROOT . '/user/user_center.php',
	'user_base' => API_ROOT . '/user/user_base.php',
	'user_register' => API_ROOT . '/user/user_register.php',
	'user_login' => API_ROOT . '/user/user_login.php',
	'user_change_info' => API_ROOT . '/user/user_change_info.php',
	'user_game' => API_ROOT . '/user/user_game.php',
	'user_gift' => API_ROOT . '/user/user_gift.php',
	'user_recharge' => API_ROOT . '/user/user_recharge.php',

	'gm_mgr' => API_ROOT . '/gm/gm_mgr.php',
	'gm_base_handler' => API_ROOT . '/gm/gm_base_handler.php',
	'gm_process' => API_ROOT . '/gm/gm_process.php',

	'pay_mgr' => API_ROOT . '/pay/pay_mgr.php',
	'pay_process' => API_ROOT . '/pay/pay_process.php',

	'api_db_config' => API_ROOT . '/config/api_db_config.php',
	'game_list_config' => API_ROOT . '/config/game_list_config.php',
	'game_server_list_config' => API_ROOT . '/config/game_server_list_config.php',
	'game_recharge_list_config' => API_ROOT . '/config/game_recharge_list_config.php',
	'game_gm_data_list_config' => API_ROOT . '/config/game_gm_data_list_config.php',
);


lx_base::register_class($_class_arr);
