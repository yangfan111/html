<?php

/*
 * 用户修改信息处理
 */
class user_change_info extends user_base
{

	/*
	 * 修改密码
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function change_password($arg) {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		$new_password = $arg->new_password;

		//密码非法
		if (!is_string($new_password) || $new_password == "" || strlen($new_password) > 48) {
			return new api_result(api_error_type::PASSWORD_IS_INVALID);
		}

		//新密码和老密码相同
		if ($user->password == $new_password) {
			return new api_result(api_error_type::PASSWORD_NEW_AND_OLD_CANNOT_BE_SAME);
		}

		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		//生成盐
		//32字节、包含大小写字母
		$user->salt = make_rand_string(32);

		//计算应该改变的密码
		$pwd_value = md5($new_password . $user->salt);

		$db_res = $db->query(sprintf("update account_base set password = '%s', salt = '%s' where account_id = %s;", $db->escape_string($pwd_value), $db->escape_string($user->salt), $user->account_id));

		//数据库失败
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$db->free_result();

		//保存到用户对象
		$user->password = $new_password;
		user_center::updateUser();

		return new api_result(api_error_type::SUCCEED);
	}

	/*
	 * 修改用户名
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function change_user($arg) {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		$new_username = $arg->new_username;

		//用户名
		if (!is_string($new_username) || $new_username == "" || strlen($new_username) > 48) {
			return new api_result(api_error_type::USERNAME_IS_INVALID);
		}

		//用户名转换为小写
		$new_username = strtolower($new_username);

		//新用户名和老用户名
		if ($user->username == $new_username) {
			return new api_result(api_error_type::USERNAME_NEW_AND_OLD_CANNOT_BE_SAME);
		}

		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		$db_res = $db->query(sprintf("select * from account_base where username = '%s'", $db->escape_string($new_username)));

		//已存在
		if ($db->num_rows($db_res) > 0) {
			return new api_result(api_error_type::USERNAME_ALREADY_EXIST);
		}

		$db_res = $db->query(sprintf("update account_base set username = '%s' where account_id = %s;", $db->escape_string($new_username), $user->account_id));

		//数据库失败
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$db->free_result();

		//保存到用户对象
		$user->username = $new_username;
		user_center::updateUser();

		return new api_result(api_error_type::SUCCEED);
	}

	/*
	 * 修改用户名和密码
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function change_user_pwd($arg) {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		$new_username = $arg->new_username;
		$new_password = $arg->new_password;

		//用户名
		if (!is_string($new_username) || $new_username == "" || strlen($new_username) > 48) {
			return new api_result(api_error_type::USERNAME_IS_INVALID);
		}

		//密码非法
		if (!is_string($new_password) || $new_password == "" || strlen($new_password) > 48) {
			return new api_result(api_error_type::PASSWORD_IS_INVALID);
		}

		//新密码和老密码相同
		if ($user->password == $new_password) {
			return new api_result(api_error_type::PASSWORD_NEW_AND_OLD_CANNOT_BE_SAME);
		}

		//用户名转换为小写
		$new_username = strtolower($new_username);

		//新用户名和老用户名
		if ($user->username == $new_username) {
			return new api_result(api_error_type::USERNAME_NEW_AND_OLD_CANNOT_BE_SAME);
		}

		$db = api_app::getDB();
		if (!$db) {
			return new api_result(api_error_type::SYSTEM_GET_PLATFORM_DB_ERROR);
		}

		$db_res = $db->query(sprintf("select * from account_base where username = '%s'", $db->escape_string($new_username)));

		//已存在
		if ($db->num_rows($db_res) > 0) {
			return new api_result(api_error_type::USERNAME_ALREADY_EXIST);
		}

		//生成盐
		//32字节、包含大小写字母
		$user->salt = make_rand_string(32);

		//计算应该改变的密码
		$pwd_value = md5($new_password . $user->salt);

		$db_res = $db->query(sprintf("update account_base set username = '%s', password = '%s', salt = '%s' where account_id = %s;", $db->escape_string($new_username), $db->escape_string($pwd_value), $db->escape_string($user->salt), $user->account_id));

		//数据库失败
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$db->free_result();

		//保存到用户对象
		$user->username = $new_username;
		$user->password = $new_password;
		user_center::updateUser();

		return new api_result(api_error_type::SUCCEED);
	}
}
