<?php
require_once(dirname(__FILE__) . "/guid.php");
require_once(dirname(__FILE__) . "/network.php");



/*
 * 生成指定长度的随机字符串
 * @param {number} $length 长度，默认16字节
 * @param {boolean} $all_char 若为true则包含大小写字母，若为false只包含小写字母。默认为true
 * @return {string}
 */
function make_rand_string($length = 16, $all_char = true) {
	$str = "";
	if ($all_char) {
		$c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	} else {
		$c = "abcdefghijklmnopqrstuvwxyz0123456789";
	}

	srand((double)microtime()*1000000);
	$c_len = strlen($c);
	for ($i = 0; $i < $length; $i++) {
		$str .= $c[rand() % $c_len];
	}

	return $str;
}

/*
 * 获取输入参数
 * @param $k 参数名
 * @param $type 从哪里获取，G - Get，P - Post，C - cookie
 * @return 返回对应的值，若找不到，返回NULL
 */
function getgpc($k, $type = 'GP') {
	$type = strtoupper($type);
	switch($type) {
	case 'G': $var = &$_GET; break;
	case 'P': $var = &$_POST; break;
	case 'C': $var = &$_COOKIE; break;
	default:
		if(isset($_GET[$k])){
			$var = &$_GET;
		} else {
			$var = &$_POST;
		}
		break;
	}

	return isset($var[$k]) ? $var[$k] : NULL;
}

/*
 * 用32位数生成64位整数
 * @param {int} $high 高32位
 * @param {int} $low 低32位
 */
function makeint64by32($high, $low) {
	$value = doubleval($high) * doubleval(0x100000000);
	$value += $low;
	return $value;
}

/*
 * 把64位整数解析为高低32位
 * @param {int64} $value
 * @return {int}、{int} 高32位、低32位
 */
function parseint64($value) {
	$high = floor($value / doubleval(0x100000000));
	$low = $value - $high * doubleval(0x100000000);
	return array($high, $low);
}

/*
 * 输出一则错误信息
 * @param $errorstr 错误描述信息
 * @param $filename 发生错误的文件名
 * @param $linenum 发生错误的行号
 */
function showError($errorstr, $filename) {
}

/*
 * 递归创建指定目录
 * @param $dirpath 要创建的子目录
 * @param $mode 权限
 * @return 若创建成功，返回true，否则返回创建失败的目录路径
 */
function makeDir($dirpath, $mode = 0755) {
	$dirpath = str_replace(array('/', '\\', '//', '\\\\'), DIRECTORY_SEPARATOR, $dirpath);
	$dirpath = str_replace(strrchr($dirpath, DIRECTORY_SEPARATOR), "", $dirpath) . DIRECTORY_SEPARATOR;
	if (file_exists($dirpath))
		return true;

	$res = mkdir($dirpath, $mode, true);
	if (!$res)
		return $dirpath;

	return true;
}



/*
 * 定制的错误处理函数
 */
function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	//忽略警告(貌似执行error_reporting(E_ALL ^ E_WARNING)不起作用。。。)
	if ($errno == E_WARNING)
		return;

	$errorinfo = date("Y-m-d H:i:s ") . "[errorHandler] " .
		print_r($errfile, true) .
		"(line:" . print_r($errline, true) . ")" .
		" errno:" . print_r($errno, true) .
		" errstr:" . print_r($errstr, true) .
		"\n---Backtrace---\n" .
		print_r(debug_backtrace(), true);


	$filename = WEB_ROOT_DIR . "/data/error/phperror/errorhandler_" . date("Y_m_d") . ".log";
	makeDir($filename);
	file_put_contents($filename, $errorinfo, FILE_APPEND);
}

//忽略警告
error_reporting(E_ALL ^ E_WARNING);
set_error_handler("errorHandler");
