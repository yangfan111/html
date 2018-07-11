<?php

/*
 * 检测指定的host是否在监听指定端口
 * @param {string} $host 主机ip地址
 * @param {int} $port 端口
 * @param {int} $time_out 超时时间，单位：毫秒。默认500毫秒
 * @return {boolean}
 */
function test_host_listen_the_port($host, $port, $time_out = 500) {
	if ($time_out < 0)
		$time_out = 0;

	//创建套接字
	$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($sock == null)
		return false;

	//若事件数量为1，表示连接成功(当可写，则连接成功)
	$event_num = 0;

	//设置为非阻塞
	if (socket_set_nonblock($sock)) {
		//连接
		@socket_connect($sock, $host, $port);

		//等待指定时间，拆分为秒、微妙。
		$sec = (int)($time_out / 1000);
		$usec = $time_out % 1000 * 1000;

		$read = null;
		$write = array($sock);
		$except = null;
		$event_num = socket_select($read, $write, $except, $sec, $usec);
	}

	//关闭套接字
	socket_close($sock);

	if ($event_num == 1)
		return true;

	return false;
}
