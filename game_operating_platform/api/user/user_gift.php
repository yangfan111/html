<?php

/*
 * 用户使用礼包处理
 */
class user_gift extends user_base
{


	/////////////////////////////////////////////////////////////////////////////////////
	//注意：
	//	为避免一个礼包同时被多个玩家领取(是否可被同时领，取决于锁定时间以及当可领取时，到游戏服务器数据库处理的耗时)
	//	目前对尝试领取时，会锁定可领取的礼包6秒，若本次由于玩家已领满此数量，那么6秒后此礼包才可被其他玩家领取。
	//
	////////////////////////////////////////////////////////////////////////////////////
	/*
	 * 尝试使用礼包
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function try_use($arg) {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		//渠道非法
		if ($user->channel != $arg->channel) {
			return new api_result(api_error_type::CHANNEL_INVALID);
		}

		//未知的游戏
		if ($user->game_id != $arg->game_id) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}

		//服务器不存在
		if ($user->server_id != $arg->server_id) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//无效的礼包码
		if (strlen($arg->gift_code) > 48) {
			return new api_result(api_error_type::GIFT_CODE_IS_INVALID);
		}

		$info = game_list_config::findGameByGameID($user->game_id);

		//未知的游戏
		if (!$info) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}

		//检测礼包码是否有效
		$game_gift_db = api_app::getGameGiftDB($user->game_id);
		if (!$game_gift_db) {
			return new api_result(api_error_type::SE_CONNECT_GAME_GIFT_DB_ERROR);
		}

		$db_res = $game_gift_db->query(sprintf("call check_gift_code_can_use('%s', '%s', @out_canuse, @out_gift_config_id, @out_max_get_num)", $game_gift_db->escape_string($arg->gift_code), $game_gift_db->escape_string((string)$user->server_id)));

		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_gift_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$db_res = $game_gift_db->query("select @out_canuse, @out_gift_config_id, @out_max_get_num");
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_gift_db->fetch_array($db_res);
		$can_use = (int)$row['@out_canuse'];
		$gift_config_id = $row['@out_gift_config_id'];
		$max_get_num = $row['@out_max_get_num'];

		$game_gift_db->free_result();

		//礼包不存在，告知无效的礼包码
		if ($can_use == -1) {
			return new api_result(api_error_type::GIFT_CODE_IS_INVALID);
		}

		//礼包已被使用过
		if ($can_use == -2) {
			return new api_result(api_error_type::GIFT_CODE_ALREADY_USED);
		}

		//其他情况，照样为无效的礼包码
		if ($can_use != 1) {
			return new api_result(api_error_type::GIFT_CODE_IS_INVALID);
		}

		//加载游戏服务器列表失败
		if (!game_list_config::loadGameServerListByGameID($user->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_SERVER_LIST_ERROR);
		}

		//此服务器不存在
		$info = game_server_list_config::getServerInfo($user->server_id);
		if (!$info) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//检测此玩家是否可领礼包
		$game_db = api_app::getGameServerDB($user->game_id, $user->server_id);
		if (!$game_db) {
			return new api_result(api_error_type::SE_CONNECT_GAME_SERVER_DB_ERROR);
		}

		$db_res = $game_db->query(sprintf("call web_try_get_gift(%s, %s, %s, %s, @out_result)", $user->account_id, $game_db->escape_string((string)$arg->char_dbid), $gift_config_id, $max_get_num));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$db_res = $game_db->query("select @out_result");
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$result = $row['@out_result'];

		$game_db->free_result();

		//不能重复领取该类型礼包
		if ($result != 0) {
			return new api_result(api_error_type::GIFT_CODE_TYPE_CANT_NOT_GET);
		}

		//增加web任务，给玩家发放礼包奖励
		$web_task = (object)array();
		$web_task->opcode = "get_gift";
		$web_task->data = (int)$gift_config_id;
		$web_task->link_dbid = (double)$arg->char_dbid;

		$db_res = $game_db->query(sprintf("call web_add_web_task('%s')", $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$game_db->free_result();

		//把礼包标记为已使用
		$game_gift_db = api_app::getGameGiftDB($user->game_id);
		$db_res = $game_gift_db->query(sprintf("call use_gift_code('%s')", $game_gift_db->escape_string($arg->gift_code)));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_gift_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$game_gift_db->free_result();

		//告知成功，请稍后从邮箱中领取礼包奖励
		return new api_result(api_error_type::SUCCEED);
	}
}
