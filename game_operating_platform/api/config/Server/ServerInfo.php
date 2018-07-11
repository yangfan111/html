<?php

/*
 * 账号平台数据库配置
 */
class ServerInfo
{
	//数据库ip
	public  $gameAppId = AppConst::GAME_APP_ID;

	//游戏服务器编号
	public  $gameServerCode;

	//游戏服务器名字
	public  $gameServerName;

	public  $url;
	//游戏服务器状态
	public  $gameServerState = ServerState.NORMAL;
	function __construct($gameServerCode, $gameServerName,$url) {
		$this->gameServerCode = $gameServerCode;
		$this->gameServerName = $gameServerName;
		$this->url = $url;

	}
	function getGMObject(){
	  return array(
		
			'gameServerCode'=>$this->gameServerName,
			'gameAppId'=> $this->gameServerName,
			'gameServerName'=> $this->gameServerName,
			'gameServerState'=> $this->gameServerState,
		);
	}
	

}


