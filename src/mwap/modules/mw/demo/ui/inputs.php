<?php
class mwmod_mw_demo_ui_inputs extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("forms","Formularios"));
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$container=$this->get_ui_dom_elem_container_empty();
		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$container->do_output();
		$mainInputsGroup=new mwmod_mw_jsobj_inputs_gr("gr");
		$input=$mainInputsGroup->addNewChild("normal");
		$input->set_prop("lbl","Ingresa un dato");

		$input=$mainInputsGroup->addNewChild("multiline","textarea");
		$input->set_prop("lbl","Ingresa un dato varias líneas");

		

		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js->add_cont("var igr=".$mainInputsGroup->get_as_js_val().";\n");
		$js->add_cont("igr.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		
		echo $js->get_js_script_html();
		
	}
	
	function prepare_before_exec_no_sub_interface(){
		
		$jsman=$this->maininterface->jsmanager;
		
		$jsman->add_item_by_cod("/res/js/util.js");
		$jsman->add_item_by_cod("/res/js/ajax.js");
		$jsman->add_item_by_cod("/res/js/url.js");
		$jsman->add_item_by_cod("/res/js/mw_date.js");
		$jsman->add_item_by_cod("/res/js/inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/inputs/container.js");
		$jsman->add_item_by_cod("/res/js/inputs/other.js");
		$jsman->add_item_by_cod("/res/js/inputs/date.js");
		$jsman->add_item_by_cod("/res/js/inputs/dx.js");
		$jsman->add_item_by_cod("/res/js/arraylist.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui.js");
		$jsman->add_item_by_cod("/res/js/mw_bootstrap_helper.js");
		$jsman->add_item_by_cod("/res/js/validator.js");

		
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}
}
?>