<?php
//优先引用类库核心配置
require_once(dirname(__FILE__) . "/contrib_core_config.php");
require_once(CONTRIB_ROOT . "/function/function_core.php");


//类自动加载
class lx_base
{
	//注册类
	public static function register_class($class_arr) {
		foreach ($class_arr as $name => $path) {
			if (isset(self::$classMap[$name])) {
				throw Exception($name . 'class already in.');
				return;
			}

			self::$classMap[$name] = $path;
		}
	}

	//类自动加载器
	public static function autoload($className) {
		if (isset(self::$classMap[$className])) {
			include(self::$classMap[$className]);
			return true;
		} else if (isset(self::$_coreClasses[$className])) {
			include(CONTRIB_ROOT . self::$_coreClasses[$className]);
			return true;
		}

		return false;
	}

	//外部类映射
	//		类名 => 路径
	private static $classMap = array();

	//内部类映射
	//		类名 => 路径
	private static $_coreClasses = array(
		'lx_Mysql' => '/class/lx_Mysql.php',
		'lx_FileLog' => '/class/lx_FileLog.php',
		'lx_HttpLoader' => '/class/lx_HttpLoader.php',
		);
}

spl_autoload_register(array('lx_base', 'autoload'));

