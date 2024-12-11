<?php
class mwap_demo_uiadmin_main extends mwmod_mw_ui_def_main_admin{
	
	function __construct($ap){
		$this->set_mainap($ap);	
		$this->subinterface_def_code="welcome";
		$this->url_base_path="/admin/";
		$this->enable_session_check();
		$this->logout_script_file="logout.php";
		$this->su_cods_for_side="demo,mwx,uidebug,users,cfg";
		
	}
	function createUISessionDataMan(){
		return new mwmod_mw_data_session_man("demomainui");	
		
	}
	function create_subinterface_demo(){
		$si= new mwmod_mw_demo_ui("demo",$this);
		return $si;
	}
	function create_subinterface_mwx(){
		$m=mw_get_autoload_manager();
		if($m->class_exists("mwmod_mwx_demo_ui")){
			$si= new mwmod_mwx_demo_ui("mwx",$this);
			return $si;
		}
	}
	
	
	
	
}
?>