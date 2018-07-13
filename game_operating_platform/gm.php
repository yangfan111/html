<?php

//鍚庡彴绠＄悊鍏ュ彛

//妗嗘灦閰嶇疆
$framework_config  = (object)array();    

//璁剧疆寮�濮嬪鐞嗚姹傚墠鐨勫洖璋冨嚱鏁�
$framework_config->before_process_check_request_func = array("gm_mgr", "before_process_check_request");

//璁剧疆璇锋眰澶勭悊瀹屾瘯鏃剁殑鍥炶皟鍑芥暟
$framework_config->process_request_end_func = array("gm_mgr", "process_request_end"); 

//妗嗘灦鍏ュ彛
require_once "index.php";

