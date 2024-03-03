<?php
class  mw_autoloadcus_phpexcel extends mw_autoload_prefman_direct{
	function __construct($mainman,$pref,$basepath){
		$this->init($mainman,$pref);
		$this->set_basepath($basepath);
	}
	function do_before_autoload_first(){
		if (!defined('PHPEXCEL_ROOT')) {
			define('PHPEXCEL_ROOT', $this->basepath . '/');
    
		}
		
		
	
	}

}

?>