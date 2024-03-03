function ui_mwaddon_schev_edit(info){
	this.info=new mw_obj();
	this.info.set_params(info);
	
	this.init_frm=function(){
		var e=this.get_ui_elem("container");
		var _this=this;
		if(!e){
			return false;	
		}
		this.frm=this.params.get_param_if_object("frm");
		if(!this.frm){
			return false;	
		}
		this.frm.append_to_container(e);
			
	}
	
	this.after_init=function(){
		
		
		var e;
		var _this=this;
		
		if(e=this.get_ui_elem("container")){
			this.set_container(e);
		}
		this.init_frm();
		
		if(this.params.get_param("popup_notify.enabled")){
			this.show_popup_notify(this.params.get_param("popup_notify"));	
		}
		
		
		
		return;
		
	}
	
	
}

ui_mwaddon_schev_edit.prototype=new mw_ui();

