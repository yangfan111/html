<?php

/*
 * gm相关操作处理
 */
class gm_process extends user_base
{
	/*
	 * gm操作通用检测
	 */
	private function common_check_is_ok() {
		$user = user_center::getUser();

		//需要先登录
		if ($user == null)
			return false;

		return true;
	}

	/*
	 * 检测是否可操作指定游戏指定服
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服务器id
	 * @return {boolean}
	 */
	private function common_can_handler_game_and_server($game_id, $server_id) {
		$user = user_center::getUser();
		$server_list = null;
		if (!isset($user->gm->game_list->{$game_id}))
			return false;

		$server_list = $user->gm->game_list->{$game_id};
		if ($server_list == "")
			return true;

		if (strstr($server_list, $server_id) != null)
			return true;

		return false;
	}

	/*
	 * 获取gm操作详情
	 * @param {string} $msg 数据检测成功时的描述信息
	 * @return {string}
	 */
	private function get_gm_handler_info($msg) {
		$user = user_center::getUser();
		return sprintf("\ngm信息:\t\n用户名:%s，账号id:%s\n操作详情:\n%s", $user->username, $user->account_id, $msg);
	}

	/*
	 * 获取gm账号id
	 * @return {int64}
	 */
	private function get_gm_account_id() {
		$user = user_center::getUser();
		return $user->account_id;
	}

	/*
	 * 玩家相关操作的通用check
	 * @param {object} $arg 参数
	 * @param {array} $__get_arg 获取游戏信息、玩家账号、dbid信息
	 * @param {string} $handler_ch_name 操作的中文名
	 * @param {boolean} $need_check_char 存在角色检测，默认为true
	 * @return {api_result} 返回结果数据
	 */
	private function as_char_common_check($arg, $__get_arg, $handler_ch_name, $need_check_char = true) {
		if (!$this->common_check_is_ok())
			return new api_result(-1, false);

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		//检测gm是否有权限对此游戏的指定服操作
		if (!$this->common_can_handler_game_and_server($arg->game_id, $arg->server_id)) {
			$err_res->msg = "本gm账号无权限对此游戏或此服进行操作";
			return $err_res;
		}

		$gameinfo = game_list_config::findGameByGameID($arg->game_id);
		if (!$gameinfo) {
			$err_res->msg = "无法识别的游戏";
			return $err_res;
		}

		//加载游戏服务器列表失败
		if (!game_list_config::loadGameServerListByGameID($arg->game_id)) {
			$err_res->msg = sprintf("游戏%s服务器列表加载失败", $gameinfo->note);
			return $err_res;
		}

		//此服务器不存在
		$server_info = game_server_list_config::getServerInfo($arg->server_id);
		if (!$server_info) {
			$err_res->msg = sprintf("游戏%s的服%s不存在", $gameinfo->note, $arg->server_id);
			return $err_res;
		}

		if ($need_check_char) {
			//检测角色dbid指示的角色是否存在
			$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
			if (!$game_db) {
				$err_res->msg = "获取此服游戏数据库失败！";
				return $err_res;
			}

			$db_res = $game_db->query(sprintf("call web_get_char_info_by_char_dbid(%s, @out_accountid, @out_char_name)", $arg->char_dbid));
			if (!$db_res) {
				$err_res->msg = "数据库操作失败！";
				return $err_res;
			}

			$row = $game_db->fetch_array($db_res);
			$has_error = $row['has_error'];
			if ($has_error != 0) {
				$err_res->msg = "数据库事务执行失败!";
				return $err_res;
			}

			$db_res = $game_db->query("select @out_accountid, @out_char_name");
			if (!$db_res) {
				$err_res->msg = "获取存储过程结果失败！";
				return $err_res;
			}

			$row = $game_db->fetch_array($db_res);
			$accountid = $row['@out_accountid'];
			$char_name = $row['@out_char_name'];

			$game_db->free_result();

			if ($accountid <= 0) {
				$err_res->msg = "角色dbid指示的玩家不存在！";
				return $err_res;
			}

			$__get_arg->accountid = $accountid;
			$__get_arg->dbid = $arg->char_dbid;
		}

		$__get_arg->gameinfo = $gameinfo;

		$res = new api_result(0, false);

		if ($need_check_char) {
			$res->msg = sprintf("操作时间:%s\n本次%s细节:\n游戏id:%s\n游戏:%s\n服务器:%s\n玩家账号id:%s\n角色dbid:%s\n玩家名:%s\n", date('Y-m-d H:i:s', time()), $handler_ch_name, $arg->game_id, $gameinfo->note, $arg->server_id, $accountid, $arg->char_dbid, $char_name);
		} else {
			$res->msg = sprintf("操作时间:%s\n本次%s细节:\n游戏id:%s\n游戏:%s\n服务器:%s\n", date('Y-m-d H:i:s', time()), $handler_ch_name, $arg->game_id, $gameinfo->note, $arg->server_id);
		}
		return $res;
	}



	/*
	 * 检测充值数据
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function check_recharge($arg, $__get_arg = null) {
		if ($__get_arg == null)
			$__get_arg = (object)array();

		$err_res = $this->as_char_common_check($arg, $__get_arg, "充值");
		if ($err_res->error_code != 0)
			return $err_res;

		$gameinfo = $__get_arg->gameinfo;

		//先设置为失败
		$err_res->error_code = -1;
		if ($arg->recharge_num < 0 || $arg->give_num < 0) {
			$err_res->msg = sprintf("充值或赠送的%s数不能小于零", $gameinfo->rmb_name);
			return $err_res;
		}

		if ($arg->recharge_num == 0 && $arg->give_num == 0) {
			$err_res->msg = sprintf("充值或赠送的%s数不能同时为零", $gameinfo->rmb_name);
			return $err_res;
		}

		$err_res->error_code = 0;
		$err_res->msg = sprintf("%s充值%s数:%s\n赠送%s数:%s\n", $err_res->msg, $gameinfo->rmb_name, (int)$arg->recharge_num, $gameinfo->rmb_name, (int)$arg->give_num);
		return $err_res;
	}

	/*
	 * 执行充值
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_recharge($arg) {
		$__get_arg = (object)array();
		$check_res = $this->check_recharge($arg, $__get_arg);
		if ($check_res->error_code != 0)
			return $check_res;

		$accountid = $__get_arg->accountid;

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行充值时，获取此服游戏数据库失败！";
			return $err_res;
		}

		//gm操作的充值渠道是特殊的
		$channel = "_gm_channel_";

		//注意，此处要转为整数，防止空字符串。。。(空字符串转整数默认为0)
		$recharge_num = (int)$arg->recharge_num;
		$give_num = (int)$arg->give_num;

		//执行充值
		while (true) {
			$db_res = $game_db->query(sprintf("call web_recharge('%s', '%s', %s, %s, %s, @out_result, @out_error)", $game_db->escape_string(create_guid()), $game_db->escape_string($channel), $accountid, $recharge_num, $give_num));

			if (!$db_res) {
				$err_res->msg = "执行充值时，调用充值存储过程失败，请稍后重试！";
				return $err_res;
			}

			$row = $game_db->fetch_array($db_res);
			$has_error = $row['has_error'];
			if ($has_error != 0) {
				$err_res->msg = "执行充值时，数据库事务执行失败!";
				return $err_res;
			}

			$db_res = $game_db->query("select @out_result, @out_error");
			if (!$db_res) {
				$err_res->msg = "执行充值时，获取充值存储过程结果信息失败！";
				return $err_res;
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
				$err_res->msg = "执行充值时，数据库相关操作，遇到未知的错误！";
				return $err_res;
			}
		}

		$res = new api_result(0, false);
		$res->msg = sprintf("充值成功!\n%s", $check_res->msg);
		$res->info_string = $this->get_gm_handler_info($check_res->msg);
		return $res;
	}

	/*
	 * 检测月卡数据
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function check_month_card($arg, $__get_arg = null) {
		if ($__get_arg == null)
			$__get_arg = (object)array();

		$err_res = $this->as_char_common_check($arg, $__get_arg, "月卡");
		if ($err_res->error_code != 0)
			return $err_res;

		$err_res->error_code = 0;
		$err_res->msg = sprintf("%s本次将增加月卡到此玩家\n", $err_res->msg);
		return $err_res;
	}


	/*
	 * 执行月卡
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_month_card($arg) {
		$__get_arg = (object)array();
		$check_res = $this->check_month_card($arg, $__get_arg);
		if ($check_res->error_code != 0)
			return $check_res;

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行月卡时，获取此服游戏数据库失败！";
			return $err_res;
		}

		//增加web任务
		$web_task = (object)array();
		$web_task->opcode = "month_card";
		$web_task->data = "";
		$web_task->link_dbid = (double)$__get_arg->dbid;

		$db_res = $game_db->query(sprintf("call web_add_web_task_as_gm_task(%s, '%s')", 
						$this->get_gm_account_id(), $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			$err_res->msg = "执行月卡存储过程失败！";
			return $err_res;
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			$err_res->msg = "执行月卡时，数据库事务执行失败!";
			return $err_res;
		}

		$game_db->free_result();

		$res = new api_result(0, false);
		$res->msg = sprintf("月卡成功!\n%s", $check_res->msg);
		$res->info_string = $this->get_gm_handler_info($check_res->msg);
		return $res;
	}

	/*
	 * 检测礼包数据
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function check_get_gift($arg, $__get_arg = null) {
		if ($__get_arg == null)
			$__get_arg = (object)array();

		$err_res = $this->as_char_common_check($arg, $__get_arg, "礼包");
		if ($err_res->error_code != 0)
			return $err_res;

		//先设置为失败
		$err_res->error_code = -1;

		//检测礼包存在性
		if (!game_list_config::loadGameGMDataByGameID((string)$arg->game_id)) {
			$err_res->msg = "加载游戏礼包配置失败！";
			return $err_res;
		}

		$gift_info = game_gm_data_list_config::getGiftInfo((string)$arg->gift_config_id);
		if (!$gift_info) {
			$err_res->msg = "礼包id指示的礼包不存在！";
			return $err_res;
		}

		$gift_info = (object)$gift_info;

		$err_res->error_code = 0;
		$err_res->msg = sprintf("%s礼包id:%s\n礼包名:%s\n", $err_res->msg, (int)$arg->gift_config_id, $gift_info->name);
		return $err_res;
	}

	/*
	 * 执行礼包
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_get_gift($arg) {
		$__get_arg = (object)array();
		$check_res = $this->check_get_gift($arg, $__get_arg);
		if ($check_res->error_code != 0)
			return $check_res;

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行礼包时，获取此服游戏数据库失败！";
			return $err_res;
		}

		//增加web任务
		$web_task = (object)array();
		$web_task->opcode = "get_gift";
		$web_task->data = (int)$arg->gift_config_id;
		$web_task->link_dbid = (double)$__get_arg->dbid;

		$db_res = $game_db->query(sprintf("call web_add_web_task_as_gm_task(%s, '%s')", 
						$this->get_gm_account_id(), $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			$err_res->msg = "执行礼包存储过程失败！";
			return $err_res;
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			$err_res->msg = "执行礼包时，数据库事务执行失败!";
			return $err_res;
		}

		$game_db->free_result();

		$res = new api_result(0, false);
		$res->msg = sprintf("礼包成功!\n%s", $check_res->msg);
		$res->info_string = $this->get_gm_handler_info($check_res->msg);
		return $res;
	}

	/*
	 * 检测附件数据
	 * @param {string} $game_id 游戏标识
	 * @param {object} $attachment 附件对象
	 */
	private function check_mail_attachment_list($game_id, $attachment, $__get_arg) {
		$err_res = new api_result(-1, false);
		if (!game_list_config::loadGameGMDataByGameID((string)$game_id)) {
			$err_res->msg = "加载此游戏的gm配置数据失败！";
			return $err_res;
		}

		if ((!is_string($attachment) && !is_object($attachment)) ||
			(is_string($attachment) && $attachment != "")) {
			$err_res->msg = "附件数据错误！";
			return $err_res;
		}

		//此处判定下是否附件列表为空
		$empty_att = false;
		if ($attachment != "") {
			$num = 0;
			foreach ($attachment as $tp => $value) {
				$num = $num + 1;
			}

			if ($num == 0) {
				$empty_att = true;
			}
		}

		//若不存在附件
		if ($attachment == "" || $empty_att) {
			unset($__get_arg->list);

			$err_res->error_code = 0;
			$err_res->msg = "\t没有附件";
			return $err_res;
		}

		$lt = array();
		$lt_idx = 0;

		$info_str = "";
		foreach ($attachment as $tp => $value) {
			$attinfo = game_gm_data_list_config::getAttachmentTypeSet((string)$tp);
			if (!$attinfo) {
				//附件类型未知
				$err_res->msg = sprintf("未知的附件类型，类型:%s", (string)$tp);
				return $err_res;
			}

			$info_str = sprintf("%s%s:\n", $info_str, $attinfo['name']);
			foreach ($value as $id => $num) {
				if (!isset($attinfo['list'][(string)$id])) {
					$err_res->msg = sprintf("%s中找不到id:%s\n", $attinfo['name'], (string)$id);
					return $err_res;
				}

				$id_info = $attinfo['list'][(string)$id];

				if ($num <= 0) {
					//数量不能小于等于0
					$err_res->msg = sprintf("类型%s，%s的数量不能小于等于0", $attinfo['name'], $id_info['name']);
					return $err_res;
				}

				$lt[$lt_idx] = (object)array(
					'tp' => (int)$tp,
					'id' => (int)$id,
					'num' => (int)$num,
				);

				$lt_idx += 1;

				$info_str = sprintf("%s\t%s:%s个 (%sid:%s)\n", $info_str, $id_info['name'], $num, $attinfo['name'], $id);
			}
		}

		//附件列表
		$__get_arg->list = $lt;

		$err_res->error_code = 0;
		$err_res->msg = $info_str;
		return $err_res;
	}

	/*
	 * 邮件通用检测
	 */
	private function mail_common_check($arg, $__get_arg) {
		$err_res = new api_result(-1, false);

		//检测发件人长度
		if (strlen($arg->sender) <= 0 || strlen($arg->sender) > 128) {
			$err_res->msg = "发件人不能为空，并且不能太长！";
			return $err_res;
		}

		//检测标题长度
		if (strlen($arg->title) <= 0 || strlen($arg->title) > 128) {
			$err_res->msg = "标题不能为空，并且不能太长！";
			return $err_res;
		}

		//检测正文长度
		if (strlen($arg->content) <= 0 || strlen($arg->content) > 256) {
			$err_res->msg = "正文不能为空，并且不能太长！";
			return $err_res;
		}

		//附件检测
		$res = $this->check_mail_attachment_list($arg->game_id, $arg->attachment, $__get_arg);
		if ($res->error_code != 0)
			return $res;

		return $res;
	}

	/*
	 * 检测发送邮件到玩家数据
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function check_player_mail($arg, $__get_arg = null) {
		if ($__get_arg == null)
			$__get_arg = (object)array();

		$common_res = $this->as_char_common_check($arg, $__get_arg, "发送邮件到玩家");
		if ($common_res->error_code != 0)
			return $common_res;

		$err_res = $this->mail_common_check($arg, $__get_arg);
		if ($err_res->error_code != 0)
			return $err_res;

		$err_res->error_code = 0;
		$err_res->msg = sprintf("%s发件时间:%s\n发件人:%s\n邮件标题:%s\n邮件正文:%s\n附件内容:\n%s\n", $common_res->msg, date('Y-m-d H:i:s', time()), $arg->sender, $arg->title, $arg->content, $err_res->msg);
		return $err_res;
	}

	/*
	 * 执行发送邮件到玩家
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_player_mail($arg) {
		$__get_arg = (object)array();
		$check_res = $this->check_player_mail($arg, $__get_arg);
		if ($check_res->error_code != 0)
			return $check_res;

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		//增加web任务
		$web_task = (object)array();
		$web_task->opcode = "send_mail_to_player";
		$web_task->link_dbid = (double)$__get_arg->dbid;

		$maildata = (object)array();
		$maildata->sent_time = (double)time();
		$maildata->addresser = (string)$arg->sender;
		$maildata->title = (string)$arg->title;
		$maildata->content = (string)$arg->content;

		if (isset($__get_arg->list)) {
			$maildata->list = $__get_arg->list;
		}

		$web_task->data = $maildata;

		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行发送邮件到玩家时，获取此服游戏数据库失败！";
			return $err_res;
		}

		$db_res = $game_db->query(sprintf("call web_add_web_task_as_gm_task(%s, '%s')", 
						$this->get_gm_account_id(), $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			$err_res->msg = "执行发送邮件到玩家的存储过程失败！";
			return $err_res;
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			$err_res->msg = "执行发送邮件到玩家时，数据库事务执行失败!";
			return $err_res;
		}

		$game_db->free_result();

		$res = new api_result(0, false);
		$res->msg = sprintf("发送邮件到玩家成功!\n%s", $check_res->msg);
		$res->info_string = $this->get_gm_handler_info($check_res->msg);
		return $res;
	}

	/*
	 * 检测发送系统邮件
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function check_system_mail($arg, $__get_arg = null) {
		if ($__get_arg == null)
			$__get_arg = (object)array();

		//切记，系统邮件不要检测角色dbid
		$common_res = $this->as_char_common_check($arg, $__get_arg, "发送系统邮件", false);
		if ($common_res->error_code != 0)
			return $common_res;

		$err_res = $this->mail_common_check($arg, $__get_arg);
		if ($err_res->error_code != 0)
			return $err_res;

		$err_res->error_code = -1;

		$c_info = "";

		//检测渠道是否存在
		//1、空字符串，表示所有渠道
		//2、逗号分割的渠道列表，表示指定的渠道列
		if ($arg->channel != "") {
			$temp_arr = explode(',', $arg->channel);
			foreach ($temp_arr as $channel) {
				if (!game_list_config::checkHasChannel($channel)) {
					$err_res->msg = sprintf("渠道列表[%s]，未知的渠道%s", $arg->channel, $channel);
					return $err_res;
				}
			}
		}

		//检测开始时间和结束时间
		$start_time = strtotime($arg->start_time);
		$end_time = strtotime($arg->end_time);
		if ($start_time == null) {
			$err_res->msg = "开始时间错误，请重新输入，时间格式:\n\t年-月-日 时:分:秒";
			return $err_res;
		}

		if ($end_time == null) {
			$err_res->msg = "结束时间错误，请重新输入，时间格式:\n\t年-月-日 时:分:秒";
			return $err_res;
		}

		if ($start_time > (time() + 31*24*3600)) {
			$err_res->msg = "开始时间为一个月之后？ 开始时间太遥远，请重新输入开始时间！";
			return $err_res;
		}

		if ($end_time > time() + 6*31*24*3600) {
			$err_res->msg = "结束时间为当前时间之后半年？ 这么长的有效期？　请重新输入结束时间！";
			return $err_res;
		}

		if ($end_time <= time()) {
			$err_res->msg = "结束时间小于等于当前时间，此系统邮件永远不会被发送，请重新输入结束时间！";
			return $err_res;
		}

		if ($start_time >= $end_time) {
			$err_res->msg = "开始时间不能大于结束时间，请重新输入！";
			return $err_res;
		}

		//检测等级和vip限制
		$level = (int)$arg->level;
		$vip = (int)$arg->vip;
		if ($level < 0) {
			$err_res->msg = "等级不能小于0！";
			return $err_res;
		}

		if ($vip < 0) {
			$err_res->msg = "vip不能小于0！";
			return $err_res;
		}

		$__get_arg->channel = $arg->channel;
		$__get_arg->start_time = $start_time;
		$__get_arg->end_time = $end_time;

		$c_info = sprintf("开始时间:\n\t%s\n结束时间:\n\t%s\n等级大于等于:\n\t%s\nvip大于等于:\n\t%s\n", date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time), $level, $vip);


		$err_res->error_code = 0;
		$err_res->msg = sprintf("%s发件时间:%s\n条件:\n渠道:\n\t%s\n%s\n发件人:%s\n邮件标题:%s\n邮件正文:%s\n附件内容:\n%s\n", $common_res->msg, date('Y-m-d H:i:s', time()), $arg->channel, $c_info, $arg->sender, $arg->title, $arg->content, $err_res->msg);
		return $err_res;
	}

	/*
	 * 执行发送系统邮件
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_system_mail($arg) {
		$__get_arg = (object)array();
		$check_res = $this->check_system_mail($arg, $__get_arg);
		if ($check_res->error_code != 0)
			return $check_res;

		$err_res = new api_result(-1, false);
		$err_res->msg = "";

		//增加web任务
		$web_task = (object)array();
		$web_task->opcode = "send_mail_to_all_player";
		$web_task->start_time = $__get_arg->start_time;
		$web_task->end_time = $__get_arg->end_time;

		$maildata = (object)array();
		$maildata->sent_time = $__get_arg->start_time;
		$maildata->addresser = (string)$arg->sender;
		$maildata->title = (string)$arg->title;
		$maildata->content = (string)$arg->content;

		$maildata->channel = $arg->channel;
		$maildata->level_limit = (int)$arg->level;
		$maildata->vip_limit = (int)$arg->vip;

		if (isset($__get_arg->list)) {
			$maildata->list = $__get_arg->list;
		}

		$web_task->data = $maildata;

		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行系统邮件时，获取此服游戏数据库失败！";
			return $err_res;
		}

		$db_res = $game_db->query(sprintf("call web_add_web_task_as_gm_task(%s, '%s')", 
						$this->get_gm_account_id(), $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			$err_res->msg = "执行系统邮件存储过程失败！";
			return $err_res;
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			$err_res->msg = "执行系统邮件时，数据库事务执行失败!";
			return $err_res;
		}

		$game_db->free_result();

		$res = new api_result(0, false);
		$res->msg = sprintf("系统邮件发送成功!\n%s", $check_res->msg);
		$res->info_string = $this->get_gm_handler_info($check_res->msg);
		return $res;
	}

	/*
	 * 格式化gm命令
	 * @param {array} $row 一行记录集
	 * @param {boolean} $is_finish 是否为执行完毕的记录
	 */
	private function format_gm_command($row, $is_finish = true) {
		$ffmt = "%s\n";
		$farg = "";
		if ($is_finish) {
			$ffmt = "execute result:[%s]\n\n";
			$farg = $row['execute_result'];
		}


		$state = $row['state'];
		if ($state == 0) {
			$state = "等待执行";
		} else if ($state == 1) {
			$state = "执行中";
		} else if ($state == 2) {
			$state = "执行失败";
		} else if ($state == 3) {
			$state = "执行成功";
		}

		$data = $row['data'];
		$wt = json_decode($data);
		if ($wt && isset($wt->data)) {
			$data = (string)$wt->data;
		}

		return sprintf("state:[%s] create time:[%s] start execute time:[%s]\n\tcommand:[%s]\n" . $ffmt, 
						$state, $row['create_time'], $row['start_execute_time'], $data, $farg);
	}

	/*
	 * 获取尚未执行完毕、已执行完毕的gm命令
	 * @param {lx_Mysql} $game_db 数据库对象
	 * @param {object} $err_res 结果信息
	 * @return {object} 返回null表示执行完毕，非null表示存在错误
	 */
	private function get_un_finish_and_histroy_gm_command($game_db, $err_res) {
		//获取上次未执行完毕的命令
		$db_res = $game_db->query(sprintf(
								"select * from web_task where gm_accountid = %s and type = 2 limit 1", 
																		$this->get_gm_account_id()));
		if (!$db_res) {
			$err_res->msg = "查询上次未执行完毕的gm命令失败!";
			return $err_res;
		}

		$un_finish_info = "";
		if ($game_db->num_rows($db_res) > 0) {
			$row = $game_db->fetch_array($db_res);
			$un_finish_info = $this->format_gm_command($row, false);
		}


		//获取若干条之前执行的命令
		$db_res = $game_db->query(sprintf("select * from web_task_log where gm_accountid = %s and " . 
							"type = 2 and (state = 2 or state = 3) order by id desc limit 16", 
																	$this->get_gm_account_id()));
		if (!$db_res) {
			$err_res->msg = "查询之前执行完毕的gm命令失败!";
			return $err_res;
		}

		$history_info = "";
		for ($num = $game_db->num_rows($db_res); $num > 0; $num--) {
			$row = $game_db->fetch_array($db_res);
			$history_info = $history_info . $this->format_gm_command($row, true);
		}

		$err_res->un_finish_info = $un_finish_info;
		$err_res->history_info = $history_info;
		return null;
	}

	/*
	 * 执行gm命令
	 * @param {object} $arg 参数
	 * @return {api_result} 返回结果数据
	 */
	public function do_gm_command($arg) {
		$__get_arg = (object)array();

		//切记，系统邮件不要检测角色dbid
		$err_res = $this->as_char_common_check($arg, $__get_arg, "执行gm命令", false);
		if ($err_res->error_code != 0)
			return $err_res;

		//先设置为失败
		$err_res->error_code = -1;


		$game_db = api_app::getGameServerDB($arg->game_id, $arg->server_id);
		if (!$game_db) {
			$err_res->msg = "执行gm命令时，获取此服游戏数据库失败！";
			return $err_res;
		}

		//获取尚未执行完毕、已执行完毕的gm命令
		if ($this->get_un_finish_and_histroy_gm_command($game_db, $err_res)) {
			return $err_res;
		}

		$un_finish_info = $err_res->un_finish_info;
		$history_info = $err_res->history_info;
		unset($err_res->un_finish_info);
		unset($err_res->history_info);


		$header_info = sprintf("[%s]\n", date('Y-m-d H:i:s', time()));
		$err_res->msg = $header_info . $un_finish_info . $history_info;


		//若传的命令为空字符串，则表示查询之前执行的命令
		if ($arg->cmd == "") {
			if ($err_res->msg == $header_info) {
				$err_res->msg = $err_res->msg . "从未执行过gm命令\n";
			}
			return $err_res;
		}

		//每个gm对一个服同时只能执行一个gm命令
		if ($un_finish_info != "") {
			$err_res->alert = "每个gm对一个服同时只能执行一个gm命令!";
			return $err_res;
		}

		//增加gm命令
		$web_task = (object)array();
		$web_task->opcode = "gm_command";
		$web_task->data = (string)$arg->cmd;

		$db_res = $game_db->query(sprintf("call web_add_web_task_as_gm_command(%s, '%s')", 
						$this->get_gm_account_id(), $game_db->escape_string(json_encode($web_task))));
		if (!$db_res) {
			$err_res->alert = "增加gm命令失败！";
			return $err_res;
		}

		$row = $game_db->fetch_array($db_res);
		$has_error = $row['has_error'];
		if ($has_error != 0) {
			$err_res->alert = "增加gm命令失败(事务失败)！";
			return $err_res;
		}


		//增加成功，获取新的信息
		//获取尚未执行完毕、已执行完毕的gm命令
		if ($this->get_un_finish_and_histroy_gm_command($game_db, $err_res) == null) {
			$un_finish_info = $err_res->un_finish_info;
			$history_info = $err_res->history_info;
			unset($err_res->un_finish_info);
			unset($err_res->history_info);

			$header_info = sprintf("[%s]\n", date('Y-m-d H:i:s', time()));
			$err_res->msg = $header_info . $un_finish_info . $history_info;
		}

		$game_db->free_result();

		$err_res->error_code = 0;
		$err_res->alert = "gm命令增加成功，等待服务器执行!\n";
		$err_res->info_string = $this->get_gm_handler_info(sprintf("gm command:[%s]\n", (string)$arg->cmd));
		return $err_res;
	}

}


