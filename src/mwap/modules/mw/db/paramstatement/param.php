<?php
class mwmod_mw_db_paramstatement_param extends mw_apsubbaseobj{
	public $value=null;
	public $statementMan;
	public $field;//used for inserts and updated.
	function __construct($val,$statementMan){
		$this->setStatementMan($statementMan);
		$this->setValue($val);
	}
	function setStatementMan($statementMan){
		return $this->statementMan=$statementMan;
	}
	function setValue($val){
		return $this->value=$val;
	}
	function getValueRaw(){
		return $this->value;
	}
	function getValue(){
		//depending onf type todo
		return $this->getValueRaw();
	}
	

}

