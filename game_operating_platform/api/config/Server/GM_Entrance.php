<?php
//gm入口
    class GM_Entrance{


        public static function init(){
            GM_CMDMgr::register();
            
        }
        public function process($gmJson)
        {
           $gmObject = json_decode($gmJson);
           $check_res = GM_Checker::checkGMArgs($gmObject);
           if($check_res->code !=1){
               return;
           }
           GM_CMDMgr::handleGMCmd();

        }
     

    }

