<?php
class mwcommon_common_currency_currencyman extends mwmod_mw_manager_man{
	private $currencyItems;
	function __construct($code,$ap,$tblname=false){
		$this->init($code,$ap,$tblname);
		$this->enable_jsondata();
		
	}
	function getCurrencyByID($id){
		$this->__get_priv_currencyItems();
		if($item=$this->currencyItems($id)){
			return $item;
		}
		return $this->getMain();
	}
	function getMain(){
		$this->__get_priv_currencyItems();
		return $this->currencyItems->getDefaultItem();
	}
	final function __get_priv_currencyItems(){
		if(!isset($this->currencyItems)){
			$this->currencyItems= new mwmod_mw_util_itemsbycod();
			if($items= $this->get_all_items()){
				foreach($items as $id=>$item){
					$this->currencyItems->add_item($item);
					if($item->isMain()){
						$this->currencyItems->setDefaultItem($item);
					}
				}


			}
		}
		return $this->currencyItems; 
		
	}
	function create_item($tblitem){
		
		$item=new mwcommon_common_currency_currencyitem($tblitem,$this);
		return $item;
	}
	

	function get_new_item_datafield_creator(){
		$cr=new mwmod_mw_datafield_creator();
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("symbol",$this->get_msg("Símbolo")));
		$input->set_required(true);
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->get_msg("Nombre")));
		$input->set_required(true);
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("code",$this->get_msg("Código")));
		$input->set_required(true);
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("plural",$this->get_msg("Plural")));
		

		return $cr;
	}

	
	

}
?>