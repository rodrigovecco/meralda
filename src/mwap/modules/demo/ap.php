<?php
class mwap_demo_ap  extends mwmod_mw_ap_def{
	function __construct(){
			
	}
	function create_submanager_uiadmin(){
		$man=new mwap_demo_uiadmin_main($this);
		return $man;	
	}
	function create_submanager_demo(){
		$man=new mwmod_mw_demo_man_man($this);
		return $man;	
	}
}

?>