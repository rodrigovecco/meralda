function mw_modules_mailqueue_ui_manualproccess(info){
	this.info=new mw_obj();
	this.info.set_params(info);
	this.msgToSend=0;
	this.msgsSent=0;
	this.sending=false;
	this.waitingForNextSend=false;
	this.setMsgToSend=function(num){
		this.msgToSend=mw_getInt(num);
		var e=this.get_ui_elem("msgstosend");
		if(e){
			e.innerHTML=this.msgToSend+"";	
		}
		this.updateOnStatusChanged();
	}
	this.clearTimeoutDoNext=function(){
		if(this.timeoutDoNext){
			clearTimeout(this.timeoutDoNext);
			this.timeoutDoNext=false;
		}
		this.waitingForNextSend=false;	
	}
	this.onTimeoutDoNext=function(){
		this.clearTimeoutDoNext();
		var input;
		if(this.controls){
			input=	this.controls.getChildByDotCod("auto");
			if(input){
				if(input.get_input_value()){
					this.procQueue();
					return;	
				}
			}
		}
		this.updateOnStatusChanged();
		
			
	}
	
	this.setTimeoutDoNext=function(){
		if(this.timeoutDoNext){
			clearTimeout(this.timeoutDoNext);
			
		}
		this.waitingForNextSend=true;
		var _this=this;
		this.timeoutDoNext=setTimeout(function(){_this.onTimeoutDoNext()},500);
			
	}
	this.canSend=function(){
		if(!this.sending){
			if(this.msgToSend>0){
				return true;
			}
		}
		return false;
			
	}
	this.updateOnStatusChanged=function(){
		var disabled=true;
		if(this.canSend()){
			if(!this.waitingForNextSend){
				disabled=false;	
			}
		}
		var input;
		if(this.controls){
			input=	this.controls.getChildByDotCod("btns.do");
			if(input){
				input.setDisabled(disabled);
			}
				
		}
		var e=this.get_ui_elem("msgssent");
		if(e){
			e.innerHTML=this.msgsSent+"";	
		}
		
			
	}
	this.init_ctrs=function(){
		var e=this.get_ui_elem("controls");
		var _this=this;
		if(!e){
			return false;	
		}
		
		this.controls=this.params.get_param_if_object("controls");
		if(!this.controls){
			return false;	
		}
		this.controls.append_to_container(e);
		var input;
		input=	this.controls.getChildByDotCod("btns.do");
		if(input){
			input.setOnClick(function(){_this.procQueue()});
		}
		
		
			
	}
	this.procQueue=function(){
		if(!this.canSend()){
			return false;
		}
		this.sending=true;
		this.clearTimeoutDoNext();
		this.updateOnStatusChanged();
		var _this=this;
		
		var l=this.getAjaxLoader();
		var p={};
		var input;
		if(this.controls){
			
			input=	this.controls.getChildByDotCod("debugmode");
			if(input){
				if(input.get_input_value()){
					p.debugmode=1;	
				}
			}
		}
		
		
		var url=this.get_xmlcmd_url("procqueue",p);
		this.debug_url(url);
		l.set_url(url);
		l.addOnLoadAcctionUnique(function(){_this.onProcDone()});
		l.run();
	}
	this.onProcDone=function(){
		var num=0;
		var sentnum;
		var data=this.getAjaxDataResponse(true);
		if(!data){
			this.sending=false;
			this.setMsgToSend(num);
			return false;	
		}
		//console.log(data.params);
		if(!data.get_param_or_def("ok",false)){
			this.sending=false;
			this.setMsgToSend(num);
			return false;	
		}
		
		num=data.get_param_or_def("jsresponse.msgsleft",0);
		this.appendToResult(data.get_param_as_list("jsresponse.msgssentinfo"));
		this.msgsSent=this.msgsSent+mw_getInt(data.get_param_or_def("jsresponse.msgssent",0));
		this.sending=false;
		this.setMsgToSend(num);
		this.setTimeoutDoNext();
		this.updateOnStatusChanged();
		/*
		var input;
		if(this.controls){
			input=	this.controls.getChildByDotCod("auto");
			if(input){
				if(input.get_input_value()){
					this.setTimeoutDoNext();
					this.updateOnStatusChanged();
					return;	
				}
			}
		}
		*/
		
		
		
	}
	this.appendToResult=function(list){
		if(!list){
			return false;	
		}
		var container=this.get_ui_elem("msgdonelist");
		var e;
		if(container){
			for(var i=0;i<list.length;i++){
				e=document.createElement("div");
				e.innerHTML=list[i].htmldebugdata+"<hr>";
				container.appendChild(e);
			}
		}
		
	}
	
	this.after_init=function(){
		
		
		var e;
		var _this=this;
		if(e=this.get_ui_elem("container")){
			this.set_container(e);
		}
		
		this.init_ctrs();
		this.setMsgToSend(this.params.get_param_or_def("msgtosend",0));
		return;
		
	}
}

mw_modules_mailqueue_ui_manualproccess.prototype=new mw_ui();

