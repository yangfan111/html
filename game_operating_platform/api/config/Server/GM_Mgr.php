<?php

    //消息注册
class GM_CMDMgr{
    
    public  static $handlerMap = array();
 
    public static function register(){

        $self::$handlerMap[ GMMsgCMD::GAME_SERVER_LIST] = $GM_CMDHandler::$handleGameServerListReq;
        
        $self::$handlerMap[ GMMsgCMD::CHANGE_SERVER_STATE]                  =$GM_CMDHandlerself::$handleGameServerStateReq;
    }
    public static function handleGMCmd()
    {

    }
}
class GM_OpObject{

  //  public $cmdName;//命令
    public $processFunc;//处理函数
    public $argNames;//参数列表
    function __construct($processFunc, $argNames) {
        $this->processFunc = $processFunc;
        $this->argNames = $argNames;
    }

}
//执行函数
class GM_CMDHandler
{
   
    public static function handleGameServerListReq($gmObject){

    }
    public static function handleGameServerStateReq($gmObject){

    }
}

// <?php

// //后台管理入口

// //框架配置
// $framework_config  = (object)array();    

// //设置开始处理请求前的回调函数
// $framework_config->before_process_check_request_func = array("gm_mgr", "before_process_check_request");

// //设置请求处理完毕时的回调函数
// $framework_config->process_request_end_func = array("gm_mgr", "process_request_end"); 

// //框架入口
// require_once "index.php";


