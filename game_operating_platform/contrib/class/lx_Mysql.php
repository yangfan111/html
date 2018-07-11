<?php

/*
 * mysql数据库操作包装类
 */
class lx_Mysql
{
	private $dbip;				//数据库ip
	private $dbport;			//数据库端口
	private $dbname;			//数据库名
	private $username;			//用户名
	private $password;			//密码
	private $character;			//字符集
	private $connect_overtime;	//指定连接时最长多少毫秒认为连接超时
	private $overtime;			//指定最长多少秒不执行sql语句就断开连接

	private $test_host_error;	//测试主机错误？(无法连上)
	private $connect_error;		//链接时错误？

	private $dbcon;				//数据库句柄
	private $last_res;			//上次的结果

	private $logobj;			//日志对象


	function __construct() {
		$this->test_host_error = false;
		$this->connect_error = false;
		$this->dbcon = mysqli_init();
		$this->logobj = null;
	}

	/*
	 * 设置日志对象
	 * @param {lx_FileLog} $logobj
	 */
	public function setLogObj($logobj) {
		$this->logobj = $logobj;
	}

	/*
	 * 打开与数据库的连接
	 * @param {string} $dbhost 数据库主机地址，ip:port格式
	 * @param {string} $dbname 数据库名
	 * @param {string} $username 用户名
	 * @param {string} $password 密码
	 * @param {string} $character 与数据库交互时的编码
	 * @param {int} $connect_overtime 指定连接时最长多少毫秒认为连接超时
	 * @param {int} $overtime 指定最长多少秒不执行sql语句就断开连接
	 * @return 若成功，返回true，否则返回false
	 */
	public function open($dbhost, $dbname, $username, $password, $character = 'utf8', 
							$connect_overtime = 500, $overtime = 720000) {

		if (!$this->dbcon)
			return false;

		if (!$this->parse_ip_port($dbhost))
			return false;

		$this->dbname = $dbname;
		$this->username = $username;
		$this->password = $password;
		$this->character = $character;
		$this->connect_overtime = $connect_overtime;
		$this->overtime = $overtime;

		if (!$this->real_open())
			return false;

		return true;
	}

	/*
	 * 对字符串根据mysql连接的字符集做转义处理
	 * @param {string} $str 需要进行转义的字符串
	 * @return 返回转义后的字符串
	 */
	public function escape_string($str) {
		return mysqli_real_escape_string($this->dbcon, $str);
	}

	/*
	 * 执行sql查询
	 * @param {string} $sql sql语句
	 * @return 返回sql语句执行结果
	 */
	public function query($sql) {
		if ($this->logobj) {
			$this->logobj->writeToFile($sql);
		}

		$this->free_result();

		$this->last_res = mysqli_query($this->dbcon, $sql);
		return $this->last_res;
	}

	/*
	 * 释放查询结果
	 */
	public function free_result() {

		/*
		 * 释放所有结果集
		 */
		while (mysqli_more_results($this->dbcon) && mysqli_next_result($this->dbcon)) {
			$result = mysqli_store_result($this->dbcon);
			if ($result) {
				mysqli_free_result($result);
			}

			if ($this->last_res == $result)
				$this->last_res = null;
		}

		if ($this->last_res instanceof mysqli_result) {
			mysqli_free_result($this->last_res);
		}

		$this->last_res = null;
	}

	/*
	 * 选择当前活动的数据库
	 * @param {string} $dbname 数据库名
	 * @return {boolean} 成功返回true，失败返回false
	 */
	public function select_db($dbname) {
		return mysqli_select_db($this->dbcon, $dbname);
	}

	/*
	 * 从结果集中获取一行作为关联数组
	 * @param $queryres 数据集
	 * @return 以关联数组为格式返回一行数据，若无数据，则返回false
	 */
	public function fetch_array($queryres) {
		return mysqli_fetch_array($queryres, MYSQLI_ASSOC);
	}

	/*
	 * 获取结果集中行数量
	 * @param $queryres 数据集
	 * @return 获取结果集中的行数量
	 */
	public function num_rows($queryres) {
		return mysqli_num_rows($queryres);
	}

	/*
	 * 关闭db连接
	 */
	public function close() {
		mysqli_close($this->dbcon);
		$this->dbcon = null;
	}

	/*
	 * 抛出一个异常，并停止
	 */
	public function halt($message = '', $code = 0, $sql = '') {
		throw new DBException($message, $code, $sql);
	}

	/*
	 * 获取上一个mysql操作产生的文本错误
	 */
	public function error() {
		if ($this->test_host_error)
			return "Can't connect to MySQL server on '$this->dbip:$this->dbport' (10060)";

		if ($this->connect_error)
			return mysqli_connect_error();

		return mysqli_error($this->dbcon);
	}

	/*
	 * 获取上一个mysql操作产生的错误信息的数字编码，没有错误则返回0
	 */
	public function errno() {
		if ($this->test_host_error)
			return 2003;

		if ($this->connect_error)
			return mysqli_connect_errno();

		return mysqli_errno($this->dbcon);
	}

	private function parse_ip_port($dbhost) {
		$arr = explode(':', $dbhost);
		if (!isset($arr[0]))
			return false;

		$port = 3306;
		if (isset($arr[1]))
			$port = (int)$arr[1];

		$this->dbip = $arr[0];
		$this->dbport = $port;
		return true;
	}

	private function real_open() {
		/*
		 * 由于win下php连接mysql的超时参数不生效，以及最小单位为秒，
		 * 故采用socket进行检测连接超时问题 - 超时时间高精度
		 *
		if (!mysqli_options($this->dbcon, MYSQLI_OPT_CONNECT_TIMEOUT, $this->connect_overtime))
			return false;
		 */

		if (!test_host_listen_the_port($this->dbip, $this->dbport, $this->connect_overtime)) {
			$this->test_host_error = true;
			return false;
		}

		//mysqli 持久连接为p:ip地址
		//注：此处不要设置数据库
		if (!mysqli_real_connect($this->dbcon, 'p:' . $this->dbip, 
							$this->username, $this->password, '', $this->dbport)) {

			$this->connect_error = true;
			return false;
		}

		//设置数据库，单独设置的原因是可以捕获到相关错误信息
		if (!$this->select_db($this->dbname))
			return false;

		if (!mysqli_set_charset($this->dbcon, $this->character))
			return false;

		if (!$this->query(sprintf("set wait_timeout = %s", $this->overtime)))
			return false;

		return true;
	}
}
