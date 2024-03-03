<?php
class mwcommon_common_currency_currencyitem extends mwmod_mw_manager_item{
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);
	}
	function isMain(){
		if($v=$this->mainap->cfg->get_value("main_currency")){
			if($this->get_data("code")==$v){
				return true;
			}
		}
		return false;
	}
	
	

}
?>