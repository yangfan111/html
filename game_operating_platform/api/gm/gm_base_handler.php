<?php

/*
 * gm登录、退出处理
 */
class gm_base_handler extends user_login
{

	/*
	 * gm登录
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function gm_login($arg) {
		$user = user_center::getUser();

		//已登录，不能再登录
		if ($user != null) {
			return new api_result(api_error_type::ALREADY_LOGIN);
		}

		$user = new user_center();
		$user->username = $arg->username;
		$user->password = $arg->password;

		$res = $this->do_login($user);
		if ($res != null)
			return $res;

		//检测下账号是否拥有gm权限
		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		$db_res = $db->query(sprintf("select * from gm_account where account_id = '%s'", $user->account_id));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		//不是gm
		if ($db->num_rows($db_res) <= 0) {
			return new api_result(api_error_type::USER_NOT_IS_GM);
		}

		//得到gm拥有哪些游戏的权限
		$row = $db->fetch_array($db_res);
		$game_id_server_list = $row['game_id_server_list'];
		$ip_filter = $row['ip_filter'];
		$o = json_decode($game_id_server_list);

		$db->free_result();

		if ($o == null) {
			return new api_result(api_error_type::USER_NOT_IS_GM);
		}

		//ip不在白名单，不允许登录
		if (strstr($ip_filter, api_app::getRequesterIP()) == null) {
			return new api_result(api_error_type::GM_USER_LOGIN_IP_NOT_ALLOW);
		}

		$user->gm = (object)array();
		$user->gm->game_list = $o;

		//设置当前用户信息
		user_center::setUser($user);

		//设置gm登录后的session有效期
		gm_mgr::set_gm_session_lifetime();

		return new api_result(api_error_type::SUCCEED);
	}

	/*
	 * gm退出
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function gm_exit($arg) {
		$user = user_center::getUser();

		user_center::setUser(null);

		return new api_result(api_error_type::SUCCEED);
	}

}


