<?php
/*
 * 请求总入口
 */

class AppConst
{
	//数据库ip
	
	public  const GAME_APP_ID = "";
}

class ServerState
{
	public const NORMAL = 1;
	public const RECOMMAND =2;//推荐
	public const HOT =3;//火爆
	public const MAINTAIN = 4;//维护中
	public const EXPECTATION =5; //期待
}
class GMMsgCMD
{
	public const GAME_SERVER_LIST = 'server_getGameServerList';
	public const CHANGE_SERVER_STATE = 'server_changeGameServerState';
	
}