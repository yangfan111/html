<?php

//支付处理
class pay_process
{

	/*
	 * 执行充值
	 * @param {string} $appid 支付方传递的appid
	 * @param {string} $private_data 支付方传递的游戏私有数据
	 * @param {string} $order_id 游戏方订单号
	 * @param {int} $rmb 支付的金额，单位：元
	 * @return {api_result} 返回结果数据
	 */
	public static function do_pay($appid, $private_data, $order_id, $rmb) {
		$res = null;
		if ($private_data == null || $private_data == "") {
			//游戏私有信息错误
			$res = new api_result(api_error_type::PAY_PRIVATE_DATA_ERROR);
			return array(false, $res->error_msg);
		}

		$private_data = json_decode(base64_decode($private_data));
		if ($private_data == null) {
			//游戏私有信息转为对象失败
			$res = new api_result(api_error_type::PAY_PRIVATE_DATA_DECODE_FROM_JSON_ERROR);
			return array(false, $res->error_msg);
		}

		//查游戏标识指示的游戏是否存在
		$gameinfo = game_list_config::findGameByGameID($private_data->game_id);
		if ($gameinfo == null) {
			$res = new api_result(api_error_type::UNKNOW_GAME_ID);
			return array(false, $res->error_msg);
		}

		//比较此游戏的appid是否和此支付的appid相同
		if ($gameinfo->app_id != $appid) {
			$res = new api_result(api_error_type::PAY_APPID_NOT_EQ_GAME_ID_FOR_APPID);
			return array(false, $res->error_msg);
		}

		if (!payer_handler::checkSign($gameinfo->app_secret)) {
			//签名验证失败
			$res = new api_result(api_error_type::PAY_SIGN_CHECK_FAILED);
			return array(false, $res->error_msg);
		}

		//ip检测
		if (strstr($gameinfo->ip_filter, api_app::getRequesterIP()) == null) {
			//ip不在白名单
			$res = new api_result(api_error_type::PAY_IP_NOT_IN_WHITE_LIST);
			return array(false, $res->error_msg);
		}

		$req = (object)array();
		$req->game_id = $private_data->game_id;
		$req->server_id = $private_data->server_id;
		$req->dbid = $private_data->dbid;
		$req->rmb = (int)$rmb;
		$req->order_id = (string)$order_id;

		$info_string = sprintf('游戏[%s]，服[%s]的角色dbid为[%s]的玩家充值[%s]元时:', $req->game_id, $req->server_id, $req->dbid, $req->rmb);

		//执行充值
		$dres = self::real_do_pay($req);
		if ($dres->error_code == api_error_type::SUCCEED)
			return array(true, $info_string . $dres->error_msg);

		return array(false, $info_string . "失败，" . sprintf("错误码:%s, 错误信息:%s", $dres->error_code, $dres->error_msg));
	}


	/*
	 * 执行支付
	 * @param {object} $req 请求对象
	 * @return {api_result} 返回结果数据
	 */
	private static function real_do_pay($req) {
		/*
			req对象包含
				game_id		游戏标识
				server_id	服务器id
				dbid		充值的角色dbid
				rmb			充值金额，单位：元
				order_id	订单号
		 */

		$gameinfo = game_list_config::findGameByGameID($req->game_id);

		//未知的游戏
		if (!$gameinfo) {
			return new api_result(api_error_type::UNKNOW_GAME_ID);
		}

		//加载游戏服务器列表失败
		if (!game_list_config::loadGameServerListByGameID($req->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_SERVER_LIST_ERROR);
		}

		//此服务器不存在
		if (!game_server_list_config::getServerInfo((string)$req->server_id)) {
			return new api_result(api_error_type::SERVER_ID_IS_INVALID);
		}

		//此服务器充值信息不存在
		if (!game_list_config::loadGameRechargeListByGameID($req->game_id)) {
			return new api_result(api_error_type::SE_LOAD_GAME_RECHARGE_LIST_ERROR);
		}

		//无效的金额(此金额对应的充值信息找不到)
		$rmb_info = game_recharge_list_config::getRmbInfo((string)$req->rmb);
		if (!$rmb_info) {
			return new api_result(api_error_type::PAY_RMB_VALUE_INVALID);
		}

		$rmb_info = (object)$rmb_info;

		//进行充值
		$game_db = api_app::getGameServerDB($req->game_id, $req->server_id);
		if (!$game_db) {
			return new api_result(api_error_type::SE_CONNECT_GAME_SERVER_DB_ERROR);
		}

		//检测订单号是否已存在
		$db_res = $game_db->query(sprintf("select id from web_recharge_order where order_id = '%s'", $req->order_id));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$num = $game_db->num_rows($db_res);
		$game_db->free_result();
		if ($num > 0) {
			//订单已存在
			return new api_result(api_error_type::PAY_ORDERID_ALREADY_EXIST);
		}

		//根据dbid得到accountid
		$db_res = $game_db->query(sprintf("call web_get_char_info_by_char_dbid(%s, @out_accountid, @out_char_name)", $req->dbid));
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			return new api_result(api_error_type::SE_DB_TRANSACTION_ERROR);
		}

		$db_res = $game_db->query("select @out_accountid, @out_char_name");
		if (!$db_res) {
			return new api_result(api_error_type::SE_DB_HANDLER_ERROR);
		}

		$row = $game_db->fetch_array($db_res);
		$accountid = $row['@out_accountid'];
		if ($accountid <= 0) {
			//找不到角色dbid对应的账号
			return new api_result(api_error_type::NOT_FIND_ACCOUNTID_BY_CHAR_DBID);
		}

		//先检测是否为首冲
		$db_res = $game_db->query(sprintf("call web_do_recharge_rmb(%s, %s, @out_already_num)", $accountid, $req->rmb));
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

		$db_res = $game_db->query(sprintf("call web_recharge('%s', '%s', %s, %s, %s, @out_result, @out_error)", $game_db->escape_string($req->order_id), $game_db->escape_string($gameinfo->channel), $accountid, $rmb_info->recharge_num, $give_num));

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
		$out_result = $row['@out_result'];
		$game_db->free_result();

		//订单已存在
		if ($out_result == 1) {
			return new api_result(api_error_type::PAY_ORDERID_ALREADY_EXIST);
		}

		//充值时，未知的错误
		if ($out_result != 0) {
			return new api_result(api_error_type::PAY_UNKNOW_ERROR);
		}

		//若是月卡，则增加web任务
		if ($rmb_info->is_month_card) {
			$web_task = (object)array();
			$web_task->opcode = "month_card";
			$web_task->data = "";
			$web_task->link_dbid = (double)$req->dbid;

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

		//平台支付日志
		api_operating_log::onUserPay($accountid, $gameinfo->channel, $req->game_id, $req->server_id, $req->dbid, $req->rmb);

		//充值成功
		return new api_result(api_error_type::SUCCEED);
	}
}

