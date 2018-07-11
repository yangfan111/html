<?php

/*
 * api请求处理器
 */
class api_process
{
	private $_class_obj = array();

	/*
	 * 解析请求对象
	 * @param {string} $datastr 字符串数据
	 * @return {array} 返回协议对象、请求对象
	 */
	public function parse_request_obj($datastr) {
		$reqobj = json_decode($datastr);
		if ($reqobj == null) {
			api_app::pushResult(new api_result(api_error_type::DATA_ILLEGAL));
			return null;
		}

		//检测必要的数据存在性
		if (!isset($reqobj->opcode) || !isset($reqobj->arg)) {
			api_app::pushResult(new api_result(api_error_type::PARAM_LESS));
			return null;
		}

		//检测请求是否可被识别
		if (!isset(api_opcode::$opcode[$reqobj->opcode])) {
			api_app::pushResult(new api_result(api_error_type::INVALID_REQUEST_TYPE));
			return null;
		}

		//检测参数类型是否正确
		if (!is_object($reqobj->arg)) {
			api_app::pushResult(new api_result(api_error_type::DATA_ILLEGAL));
			return null;
		}


		$pro_obj = api_opcode::$opcode[$reqobj->opcode];
		$arg = $reqobj->arg;

		//检测参数是否合法
		foreach ($pro_obj['arg_list'] as $name) {
			if (!isset($arg->{$name})) {
				api_app::pushResult(new api_result(api_error_type::REQUEST_PARAM_LESS));
				return null;
			}
		}

		return array($pro_obj, $reqobj);
	}

	/*
	 * 处理请求
	 * @param {array} $pro_obj 协议对象
	 * @param {object} $reqobj 请求对象
	 */
	public function process($pro_obj, $reqobj) {
		if ($pro_obj == null || $reqobj == null)
			return;

		//处理回应值是否压缩
		$response_zlib = false;
		if (isset($pro_obj['response_zlib'])) {
			$response_zlib = $pro_obj['response_zlib'];
			if (is_string($response_zlib)) {
				if ($response_zlib == "true")
					$response_zlib = true;
				else
					$response_zlib = false;
			}

			if ($response_zlib != true)
				$response_zlib = false;
		}

		$class_name = $pro_obj['class'];
		$method_name = $pro_obj['method'];
		$cobj = null;
		if (isset($this->_class_obj[$class_name])) {
			$cobj = $this->_class_obj[$class_name];
		} else {
			$cobj = new $class_name;
			$this->_class_obj[$class_name] = $cobj;
		}


		$res = $cobj->{$method_name}($reqobj->arg);

		$res->opcode = $reqobj->opcode;
		api_app::pushResult($res, $response_zlib);
	}
}


