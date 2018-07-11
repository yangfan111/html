<?php

/*
 * 用户注册处理
 */
class user_register extends user_base
{
	/*
	 * 普通注册
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function normal_register($arg) {
		$user = user_center::getUser();

		//已登录，不能注册
		if ($user != null) {
			return new api_result(api_error_type::ALREADY_LOGIN_NOT_REGISTER);
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

		//生成盐
		//32字节、包含大小写字母
		$salt = make_rand_string(32);

		//计算应该插入的密码
		$pwd_value = md5($user->password . $salt);


		while (true) {
			//生成账号guid
			$guid = create_guid();

			//创建账号
			$db_res = $db->query(sprintf("call create_new_account('%s', '%s', '%s', '%s', '%s', @out_account_id, @out_result)", $db->escape_string($guid), $db->escape_string($user->username), $db->escape_string($pwd_value), $db->escape_string($salt), $db->escape_string(api_app::getRequesterIP())));
			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $db->fetch_array($db_res);
			$has_error = $row['has_error'];
			if ($has_error != 0) {
				return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
			}

			$db_res = $db->query("select @out_account_id, @out_result");
			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $db->fetch_array($db_res);
			$out_account_id = $row['@out_account_id'];
			$result = $row['@out_result'];
			$db->free_result();

			if ($result == 0) {
				//创建成功
				$user->account_id = $out_account_id;
				$user->account_guid = $guid;
				break;
			} else if ($result == 1) {
				//用户名已存在
				return new api_result(api_error_type::USERNAME_ALREADY_EXIST);
			} else if ($result == 2) {
				//账号guid已存在，继续
				continue;
			}
		}

		//设置用户信息
		$user->salt = $salt;
		user_center::setUser($user);

		return new api_result(api_error_type::SUCCEED);
	}

	/*
	 * 游客试玩
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function tourists_register($arg) {
		$user = user_center::getUser();

		if ($user != null) {
			return new api_result(api_error_type::ALREADY_LOGIN_NOT_REGISTER);
		}

		$user = new user_center();
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

		//密码非法
		if (!is_string($user->password) || $user->password == "" || strlen($user->password) > 48) {
			return new api_result(api_error_type::PASSWORD_IS_INVALID);
		}

		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		//得到最大账号id
		$db_res = $db->query(sprintf("select max(account_id) from account_base"));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $db->fetch_array($db_res);
		$max_account_id = $row["max(account_id)"];
		$max_account_id = $max_account_id + 1;

		//生成盐
		//32字节、包含大小写字母
		$salt = make_rand_string(32);

		//计算应该插入的密码
		$pwd_value = md5($user->password . $salt);

		while (true) {
			//生成用户名
			//随机字符串为8字节，只包含小写字母
			$username = 'yk_' . make_rand_string(8, false) . $max_account_id;
			if (strlen($username) > 48) {
				$username = substr($username, 0, 47);
			}

			//记录用户名
			$user->username = $username;

			//生成账号guid
			$guid = create_guid();

			//创建账号
			$db_res = $db->query(sprintf("call create_new_account('%s', '%s', '%s', '%s', '%s', @out_account_id, @out_result)", $db->escape_string($guid), $db->escape_string($user->username), $db->escape_string($pwd_value), $db->escape_string($salt), $db->escape_string(api_app::getRequesterIP())));
			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $db->fetch_array($db_res);
			$has_error = $row['has_error'];
			if ($has_error != 0) {
				return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
			}

			$db_res = $db->query("select @out_account_id, @out_result");
			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $db->fetch_array($db_res);
			$out_account_id = $row['@out_account_id'];
			$result = $row['@out_result'];
			$db->free_result();

			if ($result == 0) {
				//创建成功
				$user->account_id = $out_account_id;
				$user->account_guid = $guid;
				break;
			} else if ($result == 1) {
				//用户名已存在，继续
				continue;
			} else if ($result == 2) {
				//账号guid已存在，继续
				continue;
			}
		}

		//设置用户信息
		$user->salt = $salt;
		user_center::setUser($user);

		$response_data = new api_result(api_error_type::SUCCEED);
		$response_data->username = $username;

		return $response_data;
	}

}
