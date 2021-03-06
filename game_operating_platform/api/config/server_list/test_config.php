<?php

class game_server_list_data
{

	public $server_list = array(
		
	);

	//客户端、web端所有信息。
	public $list = array(
		#服务器id(切记为字符串)
		'1' => array(
			#是否新服，为true表示新服
			'new' => true,

			#状态，小于等于0表示维护；大于0小于80表示良好；大于等于80表示爆满。
			'state' => '1',
			'name' => '稳定服',
			'ip' => '172.16.0.95',
			'port' => '16001',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_stable',
		),
		'2' => array(
			'new' => false,
			'state' => '2',
			'name' => '开发1服',
			'ip' => '172.16.50.77',
			'port' => '10009',
			'db_ip' => '172.16.50.77',
			'db_username' => 'root',
			'db_password' => 'root',
			'db_name' => 'gamedb',
		),
		'3' => array(
			'new' => false,
			'state' => '3',
			'name' => '开发2服',
			'ip' => '172.16.51.63',
			'port' => '10009',
			'db_ip' => '172.16.51.63',
			'db_username' => 'root',
			'db_password' => 'root',
			'db_name' => 'gamedb',
		),
		'4' => array(
			'new' => false,
			'state' => '4',
			'name' => '开发3服',
			'ip' => '172.16.50.79',
			'port' => '10009',
			'db_ip' => '172.16.50.79',
			'db_username' => 'root',
			'db_password' => 'root',
			'db_name' => 'gamedb',
		),
		'5' => array(
			'new' => false,
			'state' => '5',
			'name' => '开发4服',
			'ip' => '172.16.50.125',
			'port' => '10009',
			'db_ip' => '172.16.50.125',
			'db_username' => 'root',
			'db_password' => 'root',
			'db_name' => 'gamedb',
		),
		'6' => array(
			'new' => false,
			'state' => '60',
			'name' => '公共测试服',
			'ip' => '172.16.0.95',
			'port' => '16006',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_stable2',
		),
		'7' => array(
			'new' => false,
			'state' => '57',
			'name' => '测试1服',
			'ip' => '172.16.0.95',
			'port' => '16002',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_test1',
		),
		'8' => array(
			'new' => false,
			'state' => '8',
			'name' => '测试2服',
			'ip' => '172.16.0.95',
			'port' => '16003',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_test2',
		),
		'9' => array(
			'new' => false,
			'state' => '9',
			'name' => '测试3服',
			'ip' => '172.16.0.95',
			'port' => '16004',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_test3',
		),
		'10' => array(
			'new' => false,
			'state' => '10',
			'name' => '测试4服',
			'ip' => '172.16.0.95',
			'port' => '16004',
			'db_ip' => '127.0.0.1',
			'db_username' => 'root',
			'db_password' => '',
			'db_name' => 'gamedb_test3',
		),
		'11' => array(
			'new' => false,
			'state' => '11',
			'name' => '开发11服',
			'ip' => '172.16.51.175',
			'port' => '10009',
			'db_ip' => '172.16.51.175',
			'db_username' => 'root',
			'db_password' => 'root',
			'db_name' => 'gamedb',
		),
	);
}


