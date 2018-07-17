<?php

/*
 * api应用
 */
class api_app
{
	//暂定为请求处理时间大于300毫秒就为超时
	const PROCESS_ELAPSED_OVER_TIME = 300;

	//处理器
	private static $_process = null;

	//db
	private static $_db = null;

	//db的日志对象
	private static $_db_log = null;

	//日志
	private static $_log = null;

	//错误日志
	private static $_error_log = null;

	//所有请求日志
	private static $_request_log = null;

	//失败的请求的日志
	private static $_failed_request_log = null;

	//请求耗时剖析日志
	private static $_profile_log = null;

	//超时日志
	private static $_over_elapsed_log = null;

	//框架配置对象
	private static $_framework_config = null;

	//结果对象
	private static $_resultobj = null;

	//结果是否要压缩？
	private static $_result_use_zlib = false;


	/*
	 * 初始化
	 * @param {object} $framework_config
	 */
	public static function init($framework_config = null) {
		if (self::$_process != null)
		return;

		self::$_process = new api_process();

		self::$_request_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/api_request/');

		self::$_profile_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/api_request_profile/');

	}

	/*
	 * 获取db对象
	 * @return {dbobj}
	 */
	public static function getDB() {
		$dbobj = self::$_db;
		if ($dbobj) {
			if (!$dbobj->select_db(api_db_config::$db_name)) {
				self::errorLog(sprintf("select platform db error, errno:%s, error:%s", $dbobj->errno(), $dbobj->error()));
				return null;
			}
		} else {
			$dbobj = new lx_Mysql();
			if (!$dbobj->open(api_db_config::$db_ip, api_db_config::$db_name, api_db_config::$db_username, api_db_config::$db_password)) {
				self::errorLog(sprintf("open platform db error, errno:%s, error:%s", $dbobj->errno(), $dbobj->error()));
				return null;
			}

			self::$_db = $dbobj;
		}

		if (!self::$_db_log) {
			self::$_db_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/platform_db/');
		}

		$dbobj->setLogObj(self::$_db_log);
		return $dbobj;
	}

	/*
	 * 获取指定游戏的礼包db对象
	 * @param {string} $game_id 游戏标识
	 * @return {dbobj}
	 */
	public static function getGameGiftDB($game_id) {
		$info = game_list_config::findGameByGameID($game_id);
		if (!$info)
		return null;

		$giftdbinfo = $info->gift_database;
		if (isset($giftdbinfo->__gift_dbobj))
		$dbobj = $giftdbinfo->__gift_dbobj;
		else
		$dbobj = null;

		if ($dbobj) {
			if (!$dbobj->select_db($giftdbinfo->db_name)) {
				self::errorLog(sprintf("select game gift db error, game id:%s, errno:%s, error:%s", $game_id, $dbobj->errno(), $dbobj->error()));
				return null;
			}
			return $dbobj;
		}

		$dbobj = new lx_Mysql();
		if (!$dbobj->open($giftdbinfo->db_ip, $giftdbinfo->db_name, $giftdbinfo->db_username, $giftdbinfo->db_password)) {
			self::errorLog(sprintf("open game gift db error, game id:%s, errno:%s, error:%s", $game_id, $dbobj->errno(), $dbobj->error()));
			return null;
		}

		$giftdbinfo->__gift_dbobj = $dbobj;

		$game_gift_db_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/game_gift_db/' . $game_id . '/');
		$dbobj->setLogObj($game_gift_db_log);
		return $dbobj;
	}

	/*
	 * 获取指定游戏指定服的db对象
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服id
	 * @return {dbobj}
	 */
	public static function getGameServerDB($game_id, $server_id) {
		$server_id = (string)$server_id;
		if (!game_list_config::loadGameServerListByGameID($game_id))
		return null;

		$info = game_server_list_config::getServerInfo($server_id);

		if (!$info)
		return null;

		if (isset($info['__db_obj__'])) {
			if (!$info['__db_obj__']->select_db($info['db_name'])) {
				self::errorLog(sprintf("select game server db error, game id:%s, server id:%s, errno:%s, error:%s", $game_id, $server_id, $dbobj->errno(), $dbobj->error()));
				return null;
			}
			return $info['__db_obj__'];
		}

		$dbobj = new lx_Mysql();
		if (!$dbobj->open($info['db_ip'], $info['db_name'], $info['db_username'], $info['db_password'])) {
			self::errorLog(sprintf("open game server db error, game id:%s, server id:%s, errno:%s, error:%s", $game_id, $server_id, $dbobj->errno(), $dbobj->error()));
			return null;
		}

		$info['__db_obj__'] = $dbobj;

		$game_db_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/game_db/' . $game_id . '/' . $server_id . '/');
		$dbobj->setLogObj($game_db_log);
		return $dbobj;
	}

	/*
	 * 获取请求方的ip
	 * @return {string}
	 */
	public static function getRequesterIP() {
		return $_SERVER['REMOTE_ADDR'];
	}

	/*
	 * 获取请求信息
	 */
	public static function getRequestContentString() {
		$get_data = $_SERVER['QUERY_STRING'];
		$post_data = file_get_contents("php://input");

		$http_host = "";
		if (isset($_SERVER['HTTP_HOST'])) {
			$http_host = $_SERVER['HTTP_HOST'];
		}

		if ($get_data != null) {
			$info_str = $_SERVER['REMOTE_ADDR'] . '  request  ' . 'http://' . $http_host . htmlentities($_SERVER['PHP_SELF']) . '?' . $get_data;
		} else if ($post_data != null) {
			$info_str = $_SERVER['REMOTE_ADDR'] . '  request  ' . 'http://' . $http_host . htmlentities($_SERVER['PHP_SELF']) . '  post data:' . $post_data;
		} else {
			$info_str = $_SERVER['REMOTE_ADDR'] . '  request  ' . 'http://' . $http_host . htmlentities($_SERVER['PHP_SELF']);
		}

		return $info_str;
	}

	/*
	 * 记录请求
	 */
	public static function logRequest($fileobj = null, $usetime = null) {
		$info_str = self::getRequestContentString();

		if ($fileobj == null) {
			self::$_request_log->writeToFile($info_str);
		} else {
			$fileobj->writeToFile($info_str . '    elapsed time:' . (int)$usetime . ' ms');
		}
	}

	/*
	 * 写日志
	 * @param {string} str 日志内容
	 */
	public static function writeLog($str) {
		if (!self::$_log) {
			self::$_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/api/');
		}

		self::$_log->writeToFile($str);
	}

	/*
	 * 写错误日志
	 * @param {string} error_str 错误内容
	 */
	public static function errorLog($error_str) {
		if (!self::$_error_log) {
			self::$_error_log = new lx_FileLog(WEB_ROOT_DIR . '/data/error/api/', lx_FileLog::EVERY_DAY_FILE, lx_FileLog::SPLIT_SUBDIR_MONTH);
		}

		self::$_error_log->writeToFile($error_str);
	}

	/*
	 * 清空本次结果
	 */
	public static function clearResult() {
		self::$_resultobj = null;
	}

	/*
	 * 装入结果
	 * @param {api_result} result 结果对象
	 * @param {boolean} use_zlib 启用zlib压缩？默认不启用
	 */
	public static function pushResult($result, $use_zlib = false) {
		self::$_resultobj = $result;
		self::$_result_use_zlib = $use_zlib;
	}


	//---------------------MARK---------------------
	private static function real_process($req_data) {
		//解析,验证请求
		list($pro_obj, $req_obj) = self::$_process->parse_request_obj($req_data);
		//TODO:判断是否做初始化
		call_user_func(api_frame_opcode::$init_func,array(api_frame_opcode::$init_arg));
		//1.执行开始操作
		list($continue_do, $new_req) = call_user_func(api_frame_opcode::$gm_before_process_req_func, array($req_obj));
		if (!$continue_do) {
			//此时直接返回
			return;
		}
		$req_obj = $new_req;
		//2.执行操作
		self::$_process->process($pro_obj, $req_obj);
		//3.执行结束操作
		call_user_func_array(api_frame_opcode::$gm_end_process_req_func, array($req_obj, self::$_resultobj));
		//4.执行处理结果
		self::process_request_result(self::$_resultobj, self::$_result_use_zlib);
	}


	/*
	 * 处理请求
	 * @param {string} $req_data 请求数据
	 */
	public static function process($req_data) {
		$begin = microtime(true);
		//MARK REAL PROCESS
		self::real_process($req_data);
		$end = microtime(true);

		//性能剖析
		//变为毫秒值
		$usetime = ($end - $begin)*1000;
		if ($usetime > self::PROCESS_ELAPSED_OVER_TIME) {
			if (!self::$_over_elapsed_log) {
				self::$_over_elapsed_log = new lx_FileLog(WEB_ROOT_DIR . '/data/elapsed/api_request/');
			}

			self::logRequest(self::$_over_elapsed_log, $usetime);
		} else {
			self::logRequest(self::$_profile_log, $usetime);
		}
	}
	/*
	 * 对请求的结果进行处理
	 */
	private static function process_request_result($result, $use_zlib) {
		if ($result == null)
		return;

		//若请求不成功，则记录失败的请求的日志
		if ($result->error_code != api_error_type::SUCCEED) {
			if (!self::$_failed_request_log) {
				self::$_failed_request_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/api_failed_request/');
			}

			self::$_failed_request_log->writeToFile(self::getRequestContentString() . "   do failed. failed info:" . $result->toString());
		}

		$str = $result->toString();

		if ($use_zlib) {
			$str = gzcompress($str);
		}

		echo $str;
	}
}


