<?php

/*
 * 用户充值处理
 */
class user_recharge extends user_base
{
	/*
	 * 执行充值
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_recharge($arg) {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null) {
			return new api_result(api_error_type::NEED_LOGIN_FIRST);
		}

		$gameinfo = game_list_config::findGameByGameID($user->game_id);

		//未知的游戏
		if (!$gameinfo) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}


		//此服务器不存在
		if ($user->server_id != $arg->server_id) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//加载游戏服务器列表失败
		if (!game_list_config::loadGameServerListByGameID($user->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_SERVER_LIST_ERROR);
		}

		//此服务器不存在
		if (!game_server_list_config::getServerInfo($user->server_id)) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//此服务器充值信息不存在
		if (!game_list_config::loadGameRechargeListByGameID($user->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_RECHARGE_LIST_ERROR);
		}

		//无效的金额(此金额对应的充值信息找不到)
		$rmb_info = game_recharge_list_config::getRmbInfo((string)$arg->rmb);
		if (!$rmb_info) {
			return new api_result(api_error_type::USER_RECHARGE_RMB_VALUE_INVALID);
		}

		$rmb_info = (object)$rmb_info;

		//只有当游戏app_key和app_secret为空字符串，
		//并且渠道为default_self
		//并且使用自有账号
		//并且ip白名单为空字符串，才可以直接进行用户充值
		if ($gameinfo->app_key != "" ||
			$gameinfo->app_secret != "" ||
			$gameinfo->channel != "default_self" ||
			$gameinfo->use_channel_account == true ||
			$gameinfo->ip_filter != "") {
			return new api_result(api_error_type::THE_GAME_DO_NOT_USE_USER_RECHARGE);
		}

		//进行充值
		$game_db = api_app::getGameServerDB($user->game_id, $user->server_id);
		if (!$game_db) {
			return new api_result(api_error_type::SE_CONNECT_GAME_SERVER_DB_ERROR);
		}

		//先检测是否为首冲
		$db_res = $game_db->query(sprintf("call web_do_recharge_rmb(%s, %s, @out_already_num)", $user->account_id, $arg->rmb));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$db_res = $game_db->query("select @out_already_num");
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$already_num = $row['@out_already_num'];

		$give_num = $rmb_info->give_num;

		//为首冲，则赠送的用首冲赠送
		if ($already_num == 0) {
			$give_num = $rmb_info->first_recharge_give_num;
		}

		while (true) {
			$db_res = $game_db->query(sprintf("call web_recharge('%s', '%s', %s, %s, %s, @out_result, @out_error)", $game_db->escape_string(create_guid()), $game_db->escape_string($user->channel), $user->account_id, $rmb_info->recharge_num, $give_num));

			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $game_db->fetch_array($db_res);
			$has_error = $row['has_error'];
			if ($has_error != 0) {
				return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
			}

			$db_res = $game_db->query("select @out_result, @out_error");
			if (!$db_res) {
				return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
			}

			$row = $game_db->fetch_array($db_res);

			//订单已存在，继续随机。。。
			if ($row['@out_result'] == 1) {
				continue;
			} else if ($row['@out_result'] == 0) {
				//成功
				break;
			} else {
				//进行用户充值时，未知的错误
				return new api_result(api_error_type::USER_RECHARGE_UNKNOW_ERROR);
			}
		}

		//若是月卡，则增加web任务
		if ($rmb_info->is_month_card) {
			$web_task = (object)array();
			$web_task->opcode = "month_card";
			$web_task->data = "";
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
		}

		$game_db->free_result();

		//充值成功
		return new api_result(api_error_type::SUCCEED);
	}
}
