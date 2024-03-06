<?php
class mwmod_mw_db_paramstatement_param extends mw_apsubbaseobj{
	public $value=null;
	function __construct($val){
		$this->setValue($val);
	}
	function setValue($val){
		return $this->value=$val;
	}
	function getValue(){
		return $this->value;
	}
	

}

