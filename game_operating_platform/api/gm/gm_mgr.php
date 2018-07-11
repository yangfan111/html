<?php

class gm_mgr
{

	//gm操作成功的行为的日志
	private static $_gm_log = null;

	/*
	 * 设置gm登录后session的有效时间(切记只设置一次)
	 */
	public static function set_gm_session_lifetime() {
		//设置有效期时间
		//对于gm账号，目前失效期为2小时
		$lifeTime = 2 * 3600;
		setcookie(session_name(), session_id(), time() + $lifeTime, "/");
	}

	/*
	 * 准备处理请求时
	 * @param {object} $req_obj 请求对象
	 * @return {array} 是否继续处理、调整后的请求对象。若返回false则不继续处理，返回true则继续进行处理
	 */
	public static function before_process_check_request($req_obj) {
		//先检测是否已登录
		//若未登录，则跳转到登录页面
		$user = user_center::getUser();
		if ($user) {
			if ($req_obj == null) {
				//显示登录成功后，gm操作界面
				self::show_in_gm_opt_view();
				return array(false, null);
			}
		} else {
			if ($req_obj == null || $req_obj->opcode != "gm_login") {
				self::show_login_view();
				return array(false, null);
			}
		}

		return array(true, $req_obj);
	}


	/*
	 * 请求处理完毕时
	 */
	public static function process_request_end($req_obj, $resultobj) {
		api_app::clearResult();

		if ($resultobj->error_code == 0 && isset($resultobj->info_string)) {
			//若成功纪录gm操作日志
			if (!self::$_gm_log) {
				self::$_gm_log = new lx_FileLog(WEB_ROOT_DIR . '/data/log/gm_log/');
			}

			self::$_gm_log->writeToFile($resultobj->info_string);

			unset($resultobj->info_string);
		}

		echo $resultobj->toString();
	}

	//显示gm登录界面
	private static function show_login_view() {
		include dirname(__FILE__) . "/_gm_login_view.php";
	}

	//显示登录成功后，gm操作界面
	private static function show_in_gm_opt_view() {
		include dirname(__FILE__) . "/_gm_in_opt_view.php";
	}

}





