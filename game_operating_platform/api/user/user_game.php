<?php

/*
 * 用户游戏相关处理
 */
class user_game extends user_base
{
	/*
	 * 通用检测
	 * 返回null表示成功
	 */
	private function common_check($user) {
		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		$info = game_list_config::findGameByGameID($user->game_id);

		//未知的游戏
		if (!$info) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}

		//渠道非法
		if ($info->channel != $user->channel) {
			return new api_result(api_error_type::CHANNEL_INVALID);
		}

		//加载游戏服务器列表失败
		if (!game_list_config::loadGameServerListByGameID($user->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_SERVER_LIST_ERROR);
		}

		return null;
	}

	/*
	 * 获取服务器列表
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function get_server_list($arg) {
		$user = user_center::getUser();

		$res = $this->common_check($user);
		if ($res != null)
			return $res;

		//附加服务器列表
		$response_data = new api_result(api_error_type::SUCCEED);
		$response_data->server_list = game_server_list_config::getServerListForClient();

		return $response_data;
	}

	/*
	 * 请求进入游戏
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function enter_game($arg) {
		$user = user_center::getUser();

		$res = $this->common_check($user);
		if ($res != null)
			return $res;

		$server_id = $arg->server_id;
		$random_a = $arg->random_a;

		//此服务器不存在
		$info = game_server_list_config::getServerInfo($server_id);
		if (!$info) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//随机因子非法
		if (!is_string($random_a) || strlen($random_a) != 16) {
			return new api_result(api_error_type::DATA_ILLEGAL);
		}

		//随机32字节的字符串
		$str_B = make_rand_string(32);
		$str_M = md5($random_a . $user->account_guid . $str_B);

		//插入认证信息到服务器
		$game_db = api_app::getGameServerDB($user->game_id, $server_id);
		if (!$game_db) {
			return new api_result(api_error_type::SE_CONNECT_GAME_SERVER_DB_ERROR);
		}

		$db_res = $game_db->query(sprintf("call web_login(%s, '%s', '%s', '%s', @out_new_user)", $user->account_id, $game_db->escape_string($user->channel), $game_db->escape_string($str_M), $game_db->escape_string(api_app::getRequesterIP())));

		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$db_res = $game_db->query("select @out_new_user");
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$new_user = $row['@out_new_user'];

		$game_db->free_result();

		if ($new_user != 1 && $new_user != 0) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		//保存到用户对象
		$user->server_id = $server_id;
		user_center::updateUser();


		//新用户，之前没在此服登录过
		if ($new_user == 1) {
			api_operating_log::onNewUser($user->account_id, $user->channel, $user->game_id, $user->server_id);
		}

		api_operating_log::onUserEnterGame($user->account_id, $user->channel, $user->game_id, $user->server_id);


		//回馈账号id、认证信息M到客户端
		$response_data = new api_result(api_error_type::SUCCEED);
		$response_data->account_id = $user->account_id;
		$response_data->M = $str_M;
		return $response_data;
	}
}
