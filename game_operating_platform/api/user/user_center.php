<?php

/*
 * 用户中心
 */
class user_center
{
	//账号id
	public $account_id = null;

	//账号guid
	public $account_guid = null;

	//盐
	public $salt = null;

	//用户名(会自动转换为小写)
	public $username = null;

	//密码(客户端发送过来的，不是数据库中和盐运算完毕的！)
	public $password = null;

	//渠道
	public $channel = null;

	//游戏
	public $game_id = null;

	//服务器id
	public $server_id = null;

	//gm相关
	public $gm = null;


	public static function getUser() {
		if (self::$_user == null) {
			if (isset($_SESSION["_self_user"])) {
				self::$_user = unserialize($_SESSION["_self_user"]);
			}
		}

		return self::$_user;
	}

	public static function setUser($u) {
		self::$_user = $u;

		if ($u == null)
			unset($_SESSION["_self_user"]);
		else
			$_SESSION["_self_user"] = serialize($u);
	}

	public static function updateUser() {
		if (self::$_user == null)
			return;

		$_SESSION["_self_user"] = serialize(self::$_user);
	}

	private static $_user = null;
}
