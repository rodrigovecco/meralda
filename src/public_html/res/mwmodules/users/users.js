function mw_users_list(){
	this.users=new mw_objcol();
	this.create_users_from_data_optim=function(doptim){
		if(!mw_is_object(doptim,"get_all_data")){
			return false;	
		}
		return this.create_users_from_list(doptim.get_all_data());
	}
	
	this.create_users_from_list=function(list){
		var p =new mw_objcol_array_processor();
		var _this=this;
		p.process_elem=function(elem,index){return _this.add_user_from_data(elem)};
		p.process_list(list);
	}
	this.add_user_from_data=function(data){
		var user=this.create_user_from_data(data);
		if(!user){
			return false;	
		}
		this.users.add_item(user.id,user);
		return user;
	}
	this.create_user_from_data=function(data){
		if(!mw_is_object(data)){
			return false;	
		}
		if(!data.id){
			return false;	
		}
		return this.create_user_from_data_validated(data);
	}
	this.create_user_from_data_validated=function(data){
		var user=new mw_user();
		user.init(data,this);
		return user;
			
	}

	
	
}
function mw_user(){
	this.init=function(data,man){
		this.man=man;
		this.id=data.id;
		this.data=new mw_obj();
		this.data.set_params(data);
	}
}
