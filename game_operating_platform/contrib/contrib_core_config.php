<?php
//公共类库配置

//设置时区
date_default_timezone_set("Asia/Shanghai");

define('CONTRIB_ROOT', dirname(__FILE__));


//把contrib的上层目录作为web根目录。
$web_root_path = dirname(__FILE__);
$idx = strrpos($web_root_path, DIRECTORY_SEPARATOR);
if ($idx > 0) {
	$web_root_path = substr($web_root_path, 0, $idx + 1);
}

define('WEB_ROOT_DIR', $web_root_path);
