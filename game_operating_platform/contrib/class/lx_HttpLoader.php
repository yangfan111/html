<?php

/*
 * http请求类
 */
class lx_HttpLoader
{
	/*
	 * int http请求错误码
	 */
	private $httpcode;

	public function __construct() {
		$this->httpcode = 200;
	}

	/*
	 * 获取http请求错误码
	 * @return {int} 返回http请求错误码
	 */
	public function get_httpcode() {
		return $this->httpcode;
	}

	/*
	 * 获取是否存在错误(若为200则视为无错误)
	 * @return {boolean} 返回true表示存在错误，返回false表示无错误
	 */
	public function has_error() {
		return $this->httpcode != 200;
	}

	/*
	 * 拼接参数
	 * @param {array} $paramarr 参数列表数组
	 * @return {string} 返回拼接的参数字符串
	 */
	public function combineParam($paramarr) {
		$valueArr = array();

		foreach ($paramarr as $key => $val) {
			$valueArr[] = "$key=$val";
		}

		$keyStr = implode("&", $valueArr);
		return $keyStr;
	}

	/*
	 * 拼接url参数
	 * @param {string} $baseURL 基于的url
	 * @param {array} $paramarr 参数列表数组
	 * @return {string} 返回拼接的url
	 */
	public function combineURL($baseURL, $paramarr) {
		$combined = $baseURL . "?";
		$keyStr = $this->combineParam($paramarr);
		$combined .= ($keyStr);

		return $combined;
	}

	/*
	 * 服务器通过get请求获得内容
	 * @param {string} $url 请求的url，拼接后的
	 * @return {string} 请求返回的内容
	 */
	public function get_contents($url) {
		if (ini_get("allow_url_fopen") == "1") {
			$response = file_get_contents($url);
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_URL, $url);
			$response = curl_exec($ch);
			$this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		}

		return $response;
	}

	/*
	 * get方式请求资源
	 * @param {string} $url		基于的baseUrl
	 * @param {array} $keysArr	参数列表数组
	 * @return {string}			返回的资源内容
	 */
	public function get($url, $keysArr=NULL) {
		if ($keysArr != NULL)
			$combined = $this->combineURL($url, $keysArr);
		else
			$combined = $url;

		return $this->get_contents($combined);
	}

	/*
	 * post
	 * post方式请求资源
	 * @param {string} $url		基于的baseUrl
	 * @param {array} $keysArr	请求的参数列表
	 * @return {string}			返回的资源内容
	 */
	public function post($url, $keysArr) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->combineParam($keysArr));
		curl_setopt($ch, CURLOPT_URL, $url);
		$ret = curl_exec($ch);

		$this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $ret;
	}
}
