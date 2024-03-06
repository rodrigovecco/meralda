<?php
class mwmod_mw_db_paramstatement_paramquery extends mw_apsubbaseobj{
	public $sql="";
	private $_params=array();
	function __construct(){

	}
	function addParam($value){
		$item=new mwmod_mw_db_paramstatement_param($value,$this);
		return $this->addParamItem($item);

	}
	final function addParamItem($item){
		$this->_params[]=$item;
		return $item;
	}
	final function getParamsItems(){
		return $this->_params;
	}
	function getParams(){
		$r=array();
		if($items $this->getParamsItems()){
			foreach ($items as $item) {
				$r[]=$item->getValue();
			}
		}
		return $r;
	}


}

