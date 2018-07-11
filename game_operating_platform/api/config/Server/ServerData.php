<?php

class ServerData{
	public  $serverList =  array();
	public  function init()
	{
		$this->$serverList[] = new ServerInfo(1,'Wildest Dreams','https://wd-cb.shinezone.com');
		$this->$serverList[] = new ServerInfo(2,'public','http://172.16.0.242:8360');
		$this->$serverList[] = new ServerInfo(3,'public test','http://172.16.0.242:8361');
	}
	public  function getGMServerList()
	{
		$ret = array();
		foreach ($this->$serverList as  $value) {
			$ret[] =$value::getGMObject();
		}
		echo $ret;
		return $ret;
	}
}




