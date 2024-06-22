function mw_datainput_dx(options){
	mw_datainput_item_abs.call(this);
	this.init(options);
	this.get_input_value=function(){
		return this.DXValue;
		
	}
	this.setInputStateProp=function(cod,val,children){
	
		//cod: disabled, readOnly , required 
		if(val){
			val=true;	
		}else{
			val=false;	
		}
		this.options.set_param(val,"state."+cod);
		if(this.input_elem){
			this.update_input_atts(this.input_elem);	
		}
		if(this.DXctr){
			if(cod=="readOnly"){
				this.DXctr.option("readOnly",val);
			}
			if(cod=="disabled"){
				this.DXctr.option("disabled",val);
			}
			
		}


		if(!children){
			return;	
		}
		if(!this.sub_items_list){
			return;	
		}
		var list=this.sub_items_list.getList();
		if(!list){
			return;	
		}
		for(var i =0; i<list.length;i++){
			
			list[i].setInputStatePropFromParent(cod,val,children);
		}
		
	}
	this.set_input_value=function(val){
		this.DXValue=val;

		if(this.DXctr){
			this.DXctr.option("value",val);
		}

		if(this.input_elem){
			this.input_elem.value=this.format_input_value(val)+"";	
		}
	}
	this.get_tooltip_target_elem=function(){
		return this.DXctrElem;	
	}

	this.createDXctr=function(container,ops){
		
		$($(container)).dxTextBox(ops);
		this.DXctr=$($(container)).dxTextBox('instance');
		
		
		
	}
	this.initDX=function(){
		if(!this.DXctrElem){
			return false;	
		}
		var ops=this.getDXOptions();
		this.createDXctr(this.DXctrElem,ops);
		
	}
	this.onDXValueChanged=function(e){
		
		if(e){
			this.DXValue=e.value;	
		}
		this.on_change();
	}
	this.getDXOptionsMore=function(params){
		//
	}
	this.getDXOptions=function(){
		var params=this.options.get_param_if_object("DXOptions",true);
		var p;
		var _this=this;
		if(!this.options.param_exists("DXOptions.onValueChanged")){
			params.onValueChanged=function(e){_this.onDXValueChanged(e)};
		}
		p=this.options.get_param_or_def("placeholder",false);
		if(p){
			if(!this.options.param_exists("DXOptions.placeholder")){
				params.placeholder=p;
			}
		}
		if(!params.inputAttr){
			params.inputAttr={};
		}
		if(!this.options.param_exists("inputAttr.id")){
			params.inputAttr.id=this.get_input_id();
		}
		if(!this.options.param_exists("name")){
			params.name=this.get_input_name();
		}
		if(this.options.get_param_or_def("state.required")){
			params.inputAttr.required="required";
		}
		this.getDXOptionsMore(params);
		return params;

	
	}
	this.append_to_container=function(container){
		if(!container){
			return false;	
		}
		this.beforeAppend();
		var e=this.get_container();
		if(e){
			container.appendChild(e);
			this.initDX();
			this.afterAppend();
			return true;	
		}
	}
	
	
	this.create_container=function(){
		var p;
		var c=document.createElement("div");
		c.className="form-group";
		this.frm_group_elem=c;
		var lbl=this.create_lbl();
		if(lbl){
			c.appendChild(lbl);	
		}
		this.DXctrElemContainer=document.createElement("div");
		this.DXctrElem=document.createElement("div");
		this.DXctrElem.className="dx-field-value";
		this.DXctrElemContainer.className="mw-dx-form-control-placeholder";

		this.DXctrElemContainer.appendChild(this.DXctrElem);
		c.appendChild(this.DXctrElemContainer);	
		
		
		
		this.create_notes_elem_if_req();
		return c;
	}
	this.create_lbl=function(){
		var p;
		p=this.options.get_param_or_def("lbl",false);
		if(p){
			var lbl=document.createElement("label");
			lbl.innerHTML=p;
			p=this.get_input_id();
			if(p){
				lbl.htmlFor =id;	
			}
			return lbl;
			
		}
			
	}
	
	
}
function mw_datainput_dx_textBox(options){
	mw_datainput_dx.call(this,options);	
}

function mw_datainput_dx_selectBox(options){
	mw_datainput_dx.call(this,options);
	this.createDXctr=function(container,ops){
		console.log(ops);
		$($(container)).dxSelectBox(ops);
		this.DXctr=$($(container)).dxSelectBox('instance');
	}
	this.autoCreateItems=function(){
		var list=this.options.get_param_as_list("optionslist");
		if(!list){
			list=[];
		}
		return list;
	}
	this.getDXOptionsMore=function(params){
		if((!params["dataSource"])&&(!params["items"])){
			params["items"]=this.autoCreateItems();
			params["displayExpr"]="name";
			params["valueExpr"]="cod";
		}
	}



}



