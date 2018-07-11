<?php

class pay_mgr
{
	//支付方名
	private static $_payer_name = null;


	//初始化支付方
	public static function init_payer($payer_name) {
		self::$_payer_name = $payer_name;
	}

	/*
	 * 准备处理请求时
	 * @param {object} $req_obj 请求对象
	 * @return {array} 是否继续处理、调整后的请求对象。若返回false则不继续处理，返回true则继续进行处理
	 */
	public static function before_process_check_request($req_obj) {
		api_app::clearResult();

		//调用指定的支付方模块，生成用户支付请求
		$res = require_once dirname(__FILE__) . "/" . self::$_payer_name . "/payer_handler.php";
		if (!$res) {
			//找不到此支付方
			echo "not find this payer:" . self::$_payer_name;
			self::writeError(sprintf("未知的支付方:%s, 请求详情:\n%s", self::$_payer_name, api_app::getRequestContentString()));

			return array(false, null);
		}

		list($pay_res, $info_string) = payer_handler::process_pay();
		if ($pay_res) {
			//若成功记录支付日志
			self::writeLog(sprintf("%s\n请求详情:\n%s", $info_string, api_app::getRequestContentString()), self::$_payer_name);

			echo payer_handler::getSucceedResponse();
		} else {
			//失败
			self::writeError(sprintf("支付处理失败，错误原因:%s\n请求详情:\n%s", $info_string, api_app::getRequestContentString()), self::$_payer_name);

			echo payer_handler::getFailedResponse();
		}

		return array(false, null);
	}

	private static function writeLog($str, $payer_name) {
		$path = WEB_ROOT_DIR . '/data/pay_log/log/' . $payer_name . '/';

		$logobj = new lx_FileLog($path);
		$logobj->writeToFile($str);
	}

	private static function writeError($str, $payer_name = "") {
		if ($payer_name == "") {
			$path = WEB_ROOT_DIR . '/data/pay_log/unknow_error/';
		} else {
			$path = WEB_ROOT_DIR . '/data/pay_log/payer_error/' . $payer_name . '/';
		}

		$logobj = new lx_FileLog($path);
		$logobj->writeToFile($str);
	}

}



