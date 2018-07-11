<?php

class game_recharge_list_data
{
	//rmb对应的充值信息
	public $list = array(
		//rmb值(切记为字符串)
		'25' => array(
			//充值元宝数量
			'recharge_num' => 300,

			//赠送元宝数量
			'give_num' => 0,

			//首冲赠送元宝数量
			'first_recharge_give_num' => 0,

			//是否是月卡
			'is_month_card' => true,
		),
		'50' => array(
			'recharge_num' => 500,

			'give_num' => 60,

			'first_recharge_give_num' => 500,

			'is_month_card' => false,
		),
		'100' => array(
			'recharge_num' => 1000,

			'give_num' => 180,

			'first_recharge_give_num' => 1000,

			'is_month_card' => false,
		),

	);
}


