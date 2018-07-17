<?php

/*
 * api模块的运营日志
 */
class api_operating_log
{
	//新增
	const GAMELOG_TYPE_NEW_USER = "new_user";

	//活跃
	const GAMELOG_TYPE_ACTIVE_USER = "active_user";

	//支付
	const GAMELOG_TYPE_PAY = "pay";



	////////////////////////////////////////////////////////////////////////////////////////////////////

	//日志对象缓存
	private static $_cache_log = array();

	/*
	 * 获取指定游戏指定服的用户日志对象
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服id
	 * @param {string} $logtype 日志类型(新增、活跃等)
	 * @return {lx_FileLog}
	 */
	private static function getLogObjByGameIDServerID($game_id, $server_id, $logtype) {
		$name = $game_id . $server_id . $logtype;
		if (isset(self::$_cache_log[$name]))
			return self::$_cache_log[$name];

		$logobj = new lx_FileLog(WEB_ROOT_DIR . '/data/log/game_log/' . $game_id . '/' . $server_id . '/' . $logtype . '/');

		self::$_cache_log[$name] = $logobj;
		return $logobj;
	}

	/*
	 * 指定游戏新增用户
	 * @param {string} $account_id 账号唯一id
	 * @param {string} $channel 渠道标识
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服id
	 */
	public static function onNewUser($account_id, $channel, $game_id, $server_id) {
		$logobj = self::getLogObjByGameIDServerID($game_id, $server_id, self::GAMELOG_TYPE_NEW_USER);
		$logobj->writeToFile(sprintf("account_id:%s, channel:%s, game_id:%s, server_id:%s", $account_id, $channel, $game_id, $server_id));
	}

	/*
	 * 当用户进游戏时
	 * @param {string} $account_id 账号唯一id
	 * @param {string} $channel 渠道标识
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服id
	 */
	public static function onUserEnterGame($account_id, $channel, $game_id, $server_id) {
		$logobj = self::getLogObjByGameIDServerID($game_id, $server_id, self::GAMELOG_TYPE_ACTIVE_USER);
		$logobj->writeToFile(sprintf("account_id:%s, channel:%s, game_id:%s, server_id:%s", $account_id, $channel, $game_id, $server_id));
	}


	/*
	 * 当用户充值时
	 * @param {string} $account_id 账号唯一id
	 * @param {string} $channel 渠道标识
	 * @param {string} $game_id 游戏标识
	 * @param {string} $server_id 服id
	 * @param {string} $dbid 角色dbid
	 * @param {string} $rmb 充值的rmb金额
	 */
	public static function onUserPay($account_id, $channel, $game_id, $server_id, $dbid, $rmb) {
		$logobj = self::getLogObjByGameIDServerID($game_id, $server_id, self::GAMELOG_TYPE_PAY);
		$logobj->writeToFile(sprintf("account_id:%s, channel:%s, game_id:%s, server_id:%s, dbid:%s, rmb:%s", $account_id, $channel, $game_id, $server_id, $dbid, $rmb));
	}
}

