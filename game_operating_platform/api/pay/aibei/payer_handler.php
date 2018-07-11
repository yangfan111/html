<?php

require_once dirname(__FILE__) . '/IappDecrypt.php';

//爱贝
//支付处理
class payer_handler
{
	private static $_transdata = null;
	private static $_sign = null;

	/*
	 * 解析数据并生成用户支付请求
	 * @return {array} 支付成功或失败、详情(若为成功则为成功描述详情，否则为失败描述详情)
	 */
	public static function process_pay() {
		if (!isset($_POST["transdata"]) ||
			!isset($_POST["sign"])) {
			return array(false, "支付数据参数错误！");
		}

		//数据
		$transdata = $_POST["transdata"];

		//签名信息
		$sign = $_POST["sign"];

		self::$_transdata = $transdata;
		self::$_sign = $sign;


		//数据对象
		$trobj = json_decode($transdata);
		if ($trobj == null) {
			return array(false, "支付数据从json字符串转为对象失败！");
		}

		if ($trobj->result != 0) {
			return array(false, "收到的订单状态为支付失败的？？？神马情况");
		}


		//游戏在此支付方的appid
		$appid = $trobj->appid;

		//游戏私有数据
		$private_data = $trobj->cpprivate;

		//游戏订单号
		$order_id = $trobj->exorderno;

		//从分变为元
		$rmb = (int)($trobj->money / 100);

		return pay_process::do_pay($appid, $private_data, $order_id, $rmb);
	}

	/*
	 * 签名验证
	 * @param {string} $app_secret 游戏私钥
	 * @return {boolean}
	 */
	public static function checkSign($app_secret) {
		$checkobj = new IappDecrypt();
		if ($checkobj->validsign(self::$_transdata, self::$_sign, $app_secret) == 0)
			return true;

		return false;
	}

	/*
	 * 获取成功时的回应
	 * @return {string}
	 */
	public static function getSucceedResponse() {
		return 'SUCCESS';
	}

	/*
	 * 获取失败时的回应
	 * @return {string}
	 */
	public static function getFailedResponse() {
		return 'FAILD';
	}
}


