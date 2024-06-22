<?php

include dirname(dirname(__FILE__))."/mwap/preinit.php";

$GLOBALS["__mw_autoload_manager"]->create_and_add_sub_pref_man("demo",dirname(dirname(__FILE__))."/mwap/modules/demo","mwap");
$GLOBALS["__mw_autoload_manager"]->output_error=true;


///Meralda X
$GLOBALS["__mw_autoload_manager"]->create_and_add_sub_pref_man("mwx",dirname(dirname(__FILE__))."/mwap/modules/mwx");

$GLOBALS["__mw_main_ap"]=new mwap_demo_ap();
$GLOBALS["__mw_main_ap"]->set_instance_path(dirname(__FILE__));
include dirname(dirname(__FILE__))."/mwap/afterinit.php";;
if($GLOBALS["__mw_main_ap"]->connect_db()){
	$GLOBALS["__mw_main_ap"]->after_connect_db_ok();
}else{
	$GLOBALS["__mw_main_ap"]->after_connect_db_fail();
}
function mw_shutdown(){
	if($ap=mw_get_main_ap()){
		$ap->on_shutdown();
	}

    
}

register_shutdown_function('mw_shutdown');
?>