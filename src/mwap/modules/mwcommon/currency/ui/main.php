<?php
class mwcommon_common_currency_ui_main extends mwmod_mw_ui_base_dxtbladmin{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->get_msg("Monedas"));
		$this->set_items_man_cod("currency");
		//$this->sucods="new";
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->editingMode="cell";
		
	}
	function saveItem($item,$nd){
		unset($nd["id"]);
		if(!$nd["name"]){
			unset($nd["name"]);	
		}
		unset($nd["isMain"]);
		if($item->isMain()){
			unset($nd["code"]);
			$nd["rate"]=1;
			$nd["autoupdatemod"]="";
		}
		$item->do_save_data($nd);
		
	}
	function allowDeleteItem($item){
		if($item->isMain()){
			return false;
		}
		return $this->allowDelete();
	}

	function get_item_data($item){
		$r=$item->getDataForDXtbl();
		$r["isMain"]=$item->isMain();
		
		return $r;
	}
	function add_cols($datagrid){
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);
		$col=$datagrid->add_column_string("name",$this->lng_get_msg_txt("currency","Moneda"));
		$col=$datagrid->add_column_string("code",$this->lng_get_msg_txt("code","Código"));
		$col=$datagrid->add_column_string("symbol",$this->lng_get_msg_txt("symbol","Símbolo"));
		$col=$datagrid->add_column_string("plural",$this->lng_get_msg_txt("plural","Plural"));
		$col=$datagrid->add_column_number("rate",$this->lng_get_msg_txt("exchange_rate","Tipo de cambio"));
		$col=$datagrid->add_column_date("update_time",$this->lng_get_msg_txt("updateN","Actualización"));
		$col=$datagrid->add_column_string("autoupdatemod",$this->lng_get_msg_txt("updateMod","Módulo de actualización"));
		$lu=$col->set_lookup("id","name");
		//todo modulos act

		$col=$datagrid->add_column_boolean("isMain",$this->lng_get_msg_txt("main","Principal"));

		
			
	}
	
	function do_exec_page_in_items_list(){
		if(!$items=$this->get_items()){
			return $this->do_exec_page_in_no_items();	
		}
		$tbl=$this->new_tbl_template();
		$tits=array(
			"id"=>$this->get_msg("ID"),
			"name"=>$this->get_msg("Nombre"),
			"code"=>$this->get_msg("Código"),
			//"valueinfo"=>$this->get_msg("Cambio"),
			"_mnu"=>""
		);
		echo $tbl->get_tbl_open_header_and_set_cols_cods($tits);
		
		foreach($items as $id=>$item){
			$data=$item->get_data();
			$url=$this->get_url_subinterface("edit",array("iditem"=>$id));
			//$data["valueinfo"]=$item->currency->get_exchange_rate_formated();
			$data["_mnu"]="<a href='$url'>".$this->get_msg("EDITAR")."</a>";
			echo $tbl->get_row_ordered($data);	
		}
		echo $tbl->get_tbl_close();

	}


}
?>