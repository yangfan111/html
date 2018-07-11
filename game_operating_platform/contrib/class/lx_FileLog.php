<?php

/*
 * 日志类
 */
class lx_FileLog
{
	//按天划分日志文件
	const EVERY_DAY_FILE = 0;

	//按小时划分日志文件
	const EVERY_HOUR_FILE = 1;

	//不划分子目录
	const SPLIT_SUBDIR_NO = 0;

	//按月划分日志子目录
	const SPLIT_SUBDIR_MONTH = 1;

	//按天划分日志子目录
	const SPLIT_SUBDIR_DAY = 2;

	///////////////////////////////////////////////////////////
	//日志目录
	private $dirpath;

	//日志分文件类型
	private $split_log_type;

	//日志子目录类型
	private $split_dir_type;

	function __construct($dirpath, $split_logtype = self::EVERY_HOUR_FILE, $split_dirtype = self::SPLIT_SUBDIR_DAY) {
		$endchar = $dirpath[strlen($dirpath) - 1];
		if ($endchar != '/' || $endchar != '\\')
			$dirpath .= DIRECTORY_SEPARATOR;

		$this->dirpath = $dirpath;
		$this->split_log_type = $split_logtype;
		$this->split_dir_type = $split_dirtype;
	}

	/*
	 * 写日志文件
	 * @param {string} $logstr 日志内容
	 */
	function writeToFile($logstr) {
		$requestInformation = date("[Y-m-d H:i:s] ") . $logstr . "\n";
		$subdir = "";
		$filename = "";
		switch ($this->split_dir_type) {
		case self::SPLIT_SUBDIR_NO:
			break;
		case self::SPLIT_SUBDIR_DAY:
			$subdir = date("Y-m-d") . "/";
			break;
		case self::SPLIT_SUBDIR_MONTH:
			$subdir = date("Y-m") . "/";
			break;
		}

		switch ($this->split_log_type) {
		case self::EVERY_HOUR_FILE:
			$filename = date("H");
			break;
		case self::EVERY_DAY_FILE:
			$filename = date("Y-m-d");
			break;
		}

		$filepath = $this->dirpath . $subdir . $filename . ".log";

		//创建日志目录
		if ($subdir != "") {
			makeDir($filepath);
		}

		file_put_contents($filepath, $requestInformation, FILE_APPEND);
	}

}
