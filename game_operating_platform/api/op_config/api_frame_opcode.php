<?php


class api_frame_opcode{
	public static $gm_before_process_req_func =  array("gm_mgr", "before_process_check_request");
	public static $gm_end_process_req_func =  array("gm_mgr", "process_request_end"); 
	
	public static $init_func = array("pay_mgr", "init_payer");
	public static $init_arg = "";//TODO
	public static $pay_end_process_req_func =  array("gm_mgr", "process_request_end"); 
}

require_once "index.php";

