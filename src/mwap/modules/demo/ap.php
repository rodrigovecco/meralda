<?php
class mwap_demo_ap  extends mwmod_mw_ap_def{
	function __construct(){
			
	}
	function create_submanager_uiadmin(){
		$man=new mwap_demo_uiadmin_main($this);
		return $man;	
	}
}

?>