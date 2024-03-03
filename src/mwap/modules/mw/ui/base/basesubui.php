<?php
abstract class mwmod_mw_ui_base_basesubui extends mwmod_mw_ui_sub_uiabs{
	public $sucods;
	function get_lngmsgsmancod(){
		return $this->mainAp->get_lngmsgsmancod();	
	}
	
	/*Todos los permisos configurados solo para admin*/
	function is_allowed(){
		if($p=$this->permissionInheritEnabled()){
			return $p->is_allowed();
		}
		return $this->allow_admin();	
	}
	function allow_admin(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_admin();
		}
		return $this->allow("admin");	
	}
	function allow_edit(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_edit();
		}
		return $this->allow("admin");	
	}
	function allow_view(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_view();
		}
		if($this->allow("admin")){
			return true;	
		}
		return false;
			
	}
	function permissionInheritEnabled(){
		if($this->parent_subinterface){
			if($this->parent_subinterface->childrenInheritPermissions()){
				return $this->parent_subinterface;
			}
		}
	}
	function childrenInheritPermissions(){
		return true;
	}

	
}
?>