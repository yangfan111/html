<?php

/*
 * 用户登录处理
 */
class user_login extends user_base
{

	/*
	 * 执行登录
	 * @param {object} $user
	 * @return 若返回null表示成功，否则表示返回错误结果
	 */
	protected function do_login($user) {
		//用户名非法
		if (!is_string($user->username) || $user->username == "" || strlen($user->username) > 48) {
			return new api_result(api_error_type::USERNAME_IS_INVALID);
		}

		//密码非法
		if (!is_string($user->password) || $user->password == "" || strlen($user->password) > 48) {
			return new api_result(api_error_type::PASSWORD_IS_INVALID);
		}

		//用户名转换为小写
		$user->username = strtolower($user->username);

		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		$db_res = $db->query(sprintf("select * from account_base where username = '%s'", $db->escape_string($user->username)));

		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		//用户不存在
		if ($db->num_rows($db_res) <= 0) {
			return new api_result(api_error_type::NOT_FIND_USERNAME);
		}

		//得到账号信息
		$row = $db->fetch_array($db_res);
		$user->account_id = $row['account_id'];
		$user->account_guid = $row['account_guid'];
		$user->salt = $row['salt'];

		$pwd = $row['password'];

		$db->free_result();

		$res_pwd = md5($user->password . $user->salt);

		//用户名或密码错误
		if ($res_pwd != $pwd) {
			return new api_result(api_error_type::USERNAME_OR_PASSWORD_ERROR);
		}

		return null;
	}

	/*
	 * 普通登录
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function normal_login($arg) {
		$user = user_center::getUser();

		//已登录，不能再登录
		if ($user != null) {
			return new api_result(api_error_type::ALREADY_LOGIN);
		}

		$user = new user_center();
		$user->username = $arg->username;
		$user->password = $arg->password;
		$user->channel = $arg->channel;
		$user->game_id = $arg->game_id;

		$info = game_list_config::findGameByGameID($user->game_id);

		//未知的游戏
		if (!$info) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}

		//渠道非法
		if ($info->channel != $user->channel) {
			return new api_result(api_error_type::CHANNEL_INVALID);
		}

		$res = $this->do_login($user);
		if ($res != null)
			return $res;

		//设置当前用户信息
		user_center::setUser($user);

		return new api_result(api_error_type::SUCCEED);
	}
}
