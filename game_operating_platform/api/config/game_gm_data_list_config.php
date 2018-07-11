<?php

/*
 * 游戏gm数据列表信息(操作类)
 */
class game_gm_data_list_config
{
	private static $_all_list = null;

	/*
	 * 关联数据
	 */
	public static function link($all_list) {
		self::$_all_list = $all_list;
	}

	/*
	 * 通过礼包配置id获取礼包信息
	 * @param {string} $gift_config_id 礼包配置id
	 * @return {array}
	 */
	public static function getGiftInfo($gift_config_id) {
		if (!isset(self::$_all_list['gift_list'][(string)$gift_config_id]))
			return null;

		return self::$_all_list['gift_list'][$gift_config_id];
	}

	/*
	 * 获取附件指定类型的集
	 * @param {string} $type_id 类型id
	 * @return {array}
	 */
	public static function getAttachmentTypeSet($type_id) {
		if (!isset(self::$_all_list['attachment_list'][(string)$type_id]))
			return null;

		return self::$_all_list['attachment_list'][(string)$type_id];
	}


	/*
	格式：
	public $list = array(

		//礼包数据列表
		'gift_list' => array(

			//礼包配置id(切记为字符串)
			'1' => array(
				//礼包名称
				'name' => '新手礼包',
			),
			'2' => array(
				'name' => '至尊礼包',
			),
		),

		//附件数据列
		'attachment_list' => array(

			//附件类型
			'1' => array(

				//名称
				'name' => '物品',

				//道具列表
				'list' => array(

					//id
					'1001' => array(
						'name' => '金币',
					),

					//id
					'2001' => array(
						'name' => '钻石',
					),

					//id
					'3001' => array(
						'name' => '经验药',
					),

					//id
					'4001' => array(
						'name' => '高级锻造石',
					),
				),
			),

			//附件类型
			'2' => array(

				//名称
				'name' => '英雄',

				//英雄碎片列表
				'list' => array(

					//id
					'1' => array(
						'name' => '剑圣',
					),

					//id
					'2' => array(
						'name' => '熊猫',
					),
				),
			),

			//附件类型
			'3' => array(

				//名称
				'name' => '装备',

				//装备碎片列表
				'list' => array(

					//id
					'1' => array(
						'name' => '倚天剑',
					),

					//id
					'3' => array(
						'name' => '屠龙刀',
					),
				),
			),
		),

	);

	*/
}


