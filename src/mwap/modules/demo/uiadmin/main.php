<?php
class mwap_demo_uiadmin_main extends mwmod_mw_ui_def_main_admin{
	
	function __construct($ap){
		$this->set_mainap($ap);	
		$this->subinterface_def_code="welcome";
		$this->url_base_path="/admin/";
		$this->enable_session_check();
		$this->logout_script_file="logout.php";
		$this->su_cods_for_side="demo,cfg,uidebug,users";
		
	}
	function create_subinterface_demo(){
		$si= new mwmod_mw_demo_ui("demo",$this);
		return $si;
	}
	
	
	
	
}
?>