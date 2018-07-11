<?php

/*
 * 此平台游戏列表
 */
class game_list_config
{

	/*
	 * 获取游戏标识所指示游戏配置
	 * @param {string} $game_id 游戏标识
	 * @return {object}
	 */
	public static function findGameByGameID($game_id) {
		self::__init();

		if (!isset(self::$_game_list[$game_id])) 
			return null;

		return self::$_game_list[$game_id];
	}

	/*
	 * 检测渠道是否存在
	 * @param {string} $channel 渠道
	 * @return {boolean}
	 */
	public static function checkHasChannel($channel) {
		if (!isset(self::$_channel_set[$channel]))
			return false;
	
		return true;
	}

	/*
	 * 通过游戏标识加载游戏gm数据
	 * @param {string} $game_id 游戏标识
	 * @return {boolean}
	 */
	public static function loadGameGMDataByGameID($game_id) {
		self::__init();

		$info = self::findGameByGameID($game_id);

		if (!$info)
			return false;

		$path = API_ROOT. "/config/gm_data_list/" . $info->gm_data_list_filename . '.php';

		$res = require_once($path);
		if ($res) {
			$data = new game_gm_data_list();
			//关联数据
			game_gm_data_list_config::link($data->list);
		}

		return $res;
	}

	/*
	 * 通过游戏标识加载游戏可用充值信息列表
	 * @param {string} $game_id 游戏标识
	 * @return {boolean}
	 */
	public static function loadGameRechargeListByGameID($game_id) {
		self::__init();

		$info = self::findGameByGameID($game_id);
		if (!$info)
			return false;

		$path = API_ROOT. "/config/recharge_list/" . $info->recharge_list_filename . '.php';

		$res = require_once($path);
		if ($res) {
			$data = new game_recharge_list_data();
			//关联数据
			game_recharge_list_config::link($data->list);
		}

		return $res;
	}

	/*
	 * 通过游戏标识加载游戏服务器列表
	 * @param {string} $game_id 游戏标识
	 * @return {boolean}
	 */
	public static function loadGameServerListByGameID($game_id) {
		self::__init();

		$info = self::findGameByGameID($game_id);
		if (!$info)
			return false;

		$path = API_ROOT. "/config/server_list/" . $info->server_list_filename . '.php';

		$res = require_once($path);
		if ($res) {
			$data = new game_server_list_data();
			//关联数据
			game_server_list_config::link($data->list);
		}

		return $res;
	}


	/*
	 * 初始化，自动生成数据
	 */
	private static function __init() {
		if (self::$__init)
			return;

		$list = array();

		foreach (self::$_channel_set as $ch => $ch_info) {
			foreach ($ch_info["game_list"] as $app_id => $app_info) {
				$info = (object)array();
				$info->note = $app_info["note"];
				$info->rmb_name = $app_info["rmb_name"];
				$info->game_id = $app_info["game_id"];
				$info->use_channel_account = $ch_info["use_channel_account"];
				$info->ip_filter = $ch_info["ip_filter"];
				$info->channel = $ch;
				$info->app_id = $app_id;
				$info->app_key = $app_info["app_key"];
				$info->app_secret = $app_info["app_secret"];
				$info->server_list_filename = $app_info["server_list_filename"];
				$info->recharge_list_filename = $app_info["recharge_list_filename"];
				$info->gm_data_list_filename = $app_info["gm_data_list_filename"];

				$info->gift_database = (object)$app_info["gift_database"];

				$list[$info->game_id] = $info;
			}
		}

		self::$_game_list = $list;

		self::$__init = true;
	}

	//初始化标记
	private static $__init = false;

	/*
	 * 游戏列表，此数据为自动生成！

	 game_id(游戏唯一标识) =>
			{
				note(注释),
				game_id(游戏唯一标识),
				rmb_name(游戏中rmb对应的货币的名字),
				use_channel_account(若为false，则认为账号系统完全使用此运营平台),
				ip_filter(ip白名单),
				channel(所属渠道),
				app_id(此渠道中，此游戏的app_id - 唯一识别),
				app_key(此渠道中，此游戏的app_key - 相关签名等处理),
				app_secret(此渠道中，此游戏的app_secret - 支付等严格要求安全时用的),

				server_list_filename(server_list目录，服务器列表的文件名，不带.php),
				recharge_list_filename(recharge_list目录，充值列表信息的文件名，不带.php),
				gm_data_list_filename(gm_data_list目录，游戏gm所需数据的文件名，不带.php),

				#礼包数据库
				gift_database =>
				{
					db_ip 数据库ip,
					db_name 数据库名,
					db_username 数据库用户名,
					db_password 数据库密码，
				}
			},

	 */
	private static $_game_list = null;


	//渠道集
	private static $_channel_set = array(
		#渠道名
		'default_self' => array(
			#使用渠道账号？
			'use_channel_account' => false,

			#ip白名单
			'ip_filter' => '',

			#游戏列
			'game_list' => array(
				#游戏app_id
				'65456456465456w4r5' => array(
					#note
					'note' => '<无限幻想> 开发测试版',

					#rmb_name
					'rmb_name' => '钻石',

					#游戏标识
					'game_id' => '_self_game',

					#app_key
					'app_key' => '',

					#app_secret
					'app_secret' => '',

					#server_list_filename
					'server_list_filename' => 'test_config',

					#recharge_list_filename
					'recharge_list_filename' => 'test_config',

					#gm_data_list_filename
					'gm_data_list_filename' => 'test_config',

					#gift_database
					'gift_database' => array(
						#db_ip
						'db_ip' => '127.0.0.1',

						#db_name
						'db_name' => 'game_gift_db',

						#db_username
						'db_username' => 'root',

						#db_password
						'db_password' => 'root',
					),
				),

				#游戏app_id
				'654654we65r4564' => array(
					#note
					'note' => '',

					#rmb_name
					'rmb_name' => '钻石',

					#游戏标识
					'game_id' => 'test_game_id',

					#app_key
					'app_key' => 'wer65465w4re',

					#app_secret
					'app_secret' => 'wer546sdf',

					#server_list_filename
					'server_list_filename' => '',

					#recharge_list_filename
					'recharge_list_filename' => '',

					#gm_data_list_filename
					'gm_data_list_filename' => '',

					#gift_database
					'gift_database' => array(
						#db_ip
						'db_ip' => '127.0.0.1',

						#db_name
						'db_name' => 'game_gift_db_2',

						#db_username
						'db_username' => 'root',

						#db_password
						'db_password' => 'root',
					),

				),
			),
		),
	);

}

