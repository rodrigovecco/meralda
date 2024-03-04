function MWGlobalizeMessageFormater(msg){
	this.msg=msg;
	//this.data=data;
	this.format=function(data){
		if(!this.msg){
			return "";	
		}
		if(!mw_is_array(this.msg)){
			if(typeof(this.msg)=="string"){
				return this.msg;	
			}
			return "";
		}
		var r=new Array();
		if(!mw_is_array(data)){
			data=new Array();
		}
		var e;
		for(var i=0;i<this.msg.length;i++){
			e=this.msg[i];
			if(e){
				if(mw_is_object(e)){
					if(mw_isNumber(e["argindex"])){
						if(data[e["argindex"]]!=undefined){
							r.push(data[e["argindex"]]+"");	
						}
					}
				}else{
					r.push(e+"");		
				}
			}
		}
		return r.join("");
	}
}

jQuery.extend(Globalize, {

    mwMessageFormater:function(key,cultureSelector){
		var msg= Globalize.localize(key,cultureSelector);
		var o=new MWGlobalizeMessageFormater(msg);
		return o;
	}
		

});
