<?php
class  mwmod_mw_paymentapi_api_culqi_api extends mwmod_mw_paymentapi_abs_api{
	function __construct($man){
		$this->init($man);
	}
	function debugTestApiClassesLoaded(){
		 $COD_COMERCIO = "{Código de comercio}";
 		 $culqi = new Culqi\Culqi(array('api_key' => $COD_COMERCIO));
		 echo get_class($culqi);

	}
	function createCulqi(){
		if(!$key=$this->man->get_key_item("privatekey")->get_data()){
			return false;	
		}
 		 $culqi = new Culqi\Culqi(array('api_key' => $key));
		 return $culqi;
		
	}

}
?>