<?php

/*
 * 服务器列表信息(操作类)
 */
class game_server_list_config
{
	//数据源
	//客户端所需
	private static $_server_list = null;

	//客户端、web端所有
	private static $_all_list = null;

	/*
	 * 关联数据
	 */
	public static function link($all_list) {
		self::$_all_list = $all_list;
		self::$_server_list = null;
	}

	/*
	 * 获取指定服务器信息
	 * @param {string} server_id
	 * @return {array}
	 */
	public static function getServerInfo($server_id) {
		if (!isset(self::$_all_list[$server_id]))
			return null;

		return self::$_all_list[$server_id];
	}


	/*
	 * 获取客户端所需的服务器列表
	 * @return {string}
	 */
	public static function getServerListForClient() {
		if (self::$_server_list != null)
			return self::$_server_list;

		foreach (self::$_all_list as $id => $info) {
			//客户端只需要这些值。
			$temp = array();
			$temp['new'] = $info['new'];
			$temp['state'] = $info['state'];
			$temp['name'] = $info['name'];
			$temp['ip'] = $info['ip'];
			$temp['port'] = $info['port'];

			self::$_server_list[$id] = $temp;
		}

		return self::$_server_list;
	}


	/*
	格式：
	public $list = array(
		#服务器id(切记为字符串)
		'1' => array(
			#是否新服，为true表示新服
			'new' => true,

			#状态，小于等于0表示维护；大于0小于80表示良好；大于等于80表示爆满。
			'state' => '0',
			'name' => '%s区 区名 %-8s',
			'ip' => '127.0.0.1',
			'port' => '10001',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb',
		),
		'2' => array(
			'new' => false,
			'state' => '0',
			'name' => '%s区 区名 %-8s',
			'ip' => '127.0.0.1',
			'port' => '10001',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb',
		),
		'3' => array(
			'new' => true,
			'state' => '0',
			'name' => '%s区 区名 %-8s',
			'ip' => '127.0.0.1',
			'port' => '10001',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb',
		),

	);

	 */
}

