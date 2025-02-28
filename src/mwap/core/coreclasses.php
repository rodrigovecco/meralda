<?php
/*
PHPMWLIB by RVH 
*/
/*
Puedan todos los seres lograr la felicidad y sus causas.
Puedan todos los seres estar libres del sufrimiento y de sus causas.
Puedan todos los seres no estar separados del gran gozo, libre de todo sufrimiento.
Puedan todos los seres morar en la gran ecuanimidad, libre de los extremos del apego y la aversión sin considerar a unos cercanos y a otros distantes.

Dedico todos los méritos de la realización de este trabajo al beneficio de todos los seres.

Que la preciosa mente del despertar nazca donde no ha nacido.
Y donde ya existe que se incremente más y más.

OM MANI PADME HUM

TAYATA OM GATE GATE PARAGATE PARASAMGATE BODHI SOHA

TAYATA OM MUNI MUNI MAHA MUNIYE SOHA

OM BENZA SATO SAMAYA MANU PALAYA BENZA SATO TENOPA TIDRA DRIHO MEKHABA SUTO KAYO MEKHABA
SUPO KAYO MEKHABA ANU RAKTO MEKHABA SRWA SIDDHI ME PRA YATSA KARMA ZTU TSAME
SITAM SIDDHI YAM KURU HUN HA HA HA HA HO BAGAVAN SARWA TATAGATA BENZA MAME MUNTZA BENSA BAWA MAHA 
SAMAYA SATO AH

TAYATA OM BEKANZHE BEKANZHE MAHA BEKANZHE RAZA SAMUGATE SOHA

*/
/**
 * Abstract class mw_baseobj
 * 
 * Base class providing utility methods for key validation and dynamic property access.
 */
abstract class  mw_baseobj{
	/**
     * Determines if execution commands are accepted via URL.
     * 
     * This method is intended to be overridden in subclasses if URL execution is allowed.
     * 
     * @return bool Always returns false unless overridden.
     */
	function __accepts_exec_cmd_by_url(){
		return false;	
	}
	/**
     * Validates and returns a string key containing only alphanumeric characters and underscores.
     * 
     * The method first checks if the key is a valid string using `check_str_key()`.
     * Then, it verifies if the string consists solely of alphanumeric characters or underscores.
     * 
     * @param mixed $cod The key to validate.
     * @return false|string The validated key if it passes the check, false otherwise.
     */
	function check_str_key_alnum_underscore($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		if(ctype_alnum($cod)){
			return $cod;	
		}
		$list=explode("_",$cod);
		foreach($list as $c){
			if(!ctype_alnum($c)){
				return false;	
			}
		}
		return implode("_",$list);
	}
	/**
     * Validates and returns a string key.
     * 
     * If the input is numeric, it is converted to a string.
     * If the input is not a string or numeric, it returns false.
     * 
     * @param mixed $cod The key to validate.
     * @return false|string The validated key as a string, or false if invalid.
     */
	function check_str_key($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			if(is_numeric($cod)){
				return $cod."";
			}
			return false;	
		}
		return $cod;
			
	}
	/**
     * Magic method for retrieving private properties via getter methods.
     * 
     * The method constructs a method name using the format `__get_priv_{property}`
     * and calls it if it exists.
     * 
     * @param string $name The name of the property.
     * @return mixed The value returned by the corresponding private getter method, or false if not found.
     */
	function __get($name){
		if(!$name){
			return false;	
		}
		if(!is_string($name)){
			return false;	
		}
		$method="__get_priv_".$name;
		if(method_exists($this,$method)){
			return $this->$method();	
		}
	}
	
	
	
	
}
/**
 * Abstract class mw_apsubbaseobj
 * 
 * Extends `mw_baseobj` to provide functionality related to language message management
 * and application sub-manager integration.
 */
abstract class  mw_apsubbaseobj extends mw_baseobj{
	/**
     * @var object Reference to the main application instance.
     */
	private $mainap;
	/**
     * @var array|null Stores language order mapping.
     */
	private $____lng_order;
	/**
     * @var string|null Stores the application sub-manager code.
     */
	private $__ap_submanager_cod;
	
	/**
     * @var string|null Stores the language messages manager code.
     */
	private $lngmsgsmancod;
	/**
     * Sets the language message manager code.
     * 
     * @param string $cod The language manager code.
     * @return bool Returns true if successfully set.
     */
	final function set_lngmsgsmancod($cod){
		if($cod){
			$this->lngmsgsmancod=$cod;
			return true;
		}
	}
	 /**
     * Gets the default language message manager code.
     * 
     * @return string The default language manager code ("def").
     */
	function get_lngmsgsmancod(){
		return "def";	
	}
	 /**
     * Private getter for the language message manager code.
     * 
     * Initializes the value if not already set.
     * 
     * @return string The language message manager code.
     */
	final function __get_priv_lngmsgsmancod(){
		if(!isset($this->lngmsgsmancod)){
			
			$this->lngmsgsmancod=$this->get_lngmsgsmancod();	
		}
		
		return $this->lngmsgsmancod;
	}
	/**
     * Retrieves a localized message from the specific language manager.
     * 
     * @param string $cod The message code.
     * @param string|bool $def Default message if not found.
     * @param array|bool $params Optional parameters for message formatting.
     * @return string|bool The message text or default value.
     */
	function lng_get_msg_txt($cod,$def=false,$params=false){
		if($man=$this->get_msgs_man_specific()){
			return $man->get_msg_txt($cod,$def,$params);
		}
		return $this->lng_common_get_msg_txt($cod,$def,$params);
	}
	 /**
     * Retrieves a localized message from the common language manager.
     * 
     * @param string $cod The message code.
     * @param string|bool $def Default message if not found.
     * @param array|bool $params Optional parameters for message formatting.
     * @return string|bool The message text or default value.
     */
	function lng_common_get_msg_txt($cod,$def=false,$params=false){
		if($man=$this->get_msgs_man_common()){
			return $man->get_msg_txt($cod,$def,$params);
		}
		return $def;
	}
	 /**
     * Retrieves the specific language messages manager.
     * 
     * @return object|null The specific language manager instance or null.
     */
	function get_msgs_man_specific(){
		
		if($ap=$this->__get_priv_mainap()){
			//return $ap->get_msgs_man($this->lngmsgsmancod);
			return $ap->get_msgs_man($this->__get_priv_lngmsgsmancod());
		}
		
	}
	 /**
     * Retrieves the common language messages manager.
     * 
     * @return object|null The common language manager instance or null.
     */
	function get_msgs_man_common(){
		if($ap=$this->__get_priv_mainap()){
			return $ap->get_msgs_man_common();
		}
	}
	/**
     * Sets the language message manager code from another object.
     * 
     * @param object $obj The object containing `lngmsgsmancod`.
     * @return bool Returns true if successfully set.
     */
	final function set_lngmsgsmancod_by_obj($obj){
		return $this->set_lngmsgsmancod($obj->lngmsgsmancod);
	}
	
	final function __get_lngmsgsmancod(){
		return $this->__lngmsgsmancod;	
	}
	/**
     * Retrieves the application sub-manager code.
     * 
     * @return string|null The sub-manager code.
     */
	final function __get_ap_submanager_cod(){
		return $this->__ap_submanager_cod;	
	}
	/**
     * Generates an execution command URL for the sub-manager.
     * 
     * @param string $cmd The command name.
     * @param array $params Optional parameters as key-value pairs.
     * @param string|bool $filename Optional filename to append.
     * @return string|bool The generated URL or false if unavailable.
     */
	function get_exec_cmd_url($cmd,$params=array(),$filename=false){
		$this->__get_priv_mainap();
		if(!$url=$this->mainap->get_submanagerexeccmdurl()){
			return false;	
		}
		if(!$cod=$this->__get_ap_submanager_cod()){
			return false;
		}
		
		$url.="/".$cod."/".$cmd;
		if(is_array($params)){
			foreach($params as $c=>$v){
				$url.="/$c/$v";	
			}
		}
		if($filename){
			$url.="/$filename";		
		}
		return $url;
	}
 	 /**
     * Sets the application sub-manager code.
     * 
     * @param string $cod The sub-manager code.
     * @return string The assigned code.
     */
	final function __set_ap_submanager_cod($cod){
		return $this->__ap_submanager_cod=$cod;	
	}
	 /**
     * Initializes the main application instance.
     */
	private function ___init_main_ap(){
		if(!isset($this->mainap)){
			$this->mainap=mw_get_main_ap();	
		}
	}
	/**
     * Sets the main application instance.
     * 
     * @param object|false $ap The application instance or false to retrieve the default.
     */
	final function set_mainap($ap=false){
		if(!$ap){
			$ap=mw_get_main_ap();
		}
		$this->mainap=$ap;	
	}
	/**
     * Retrieves the main application instance.
     * 
     * @return object The main application instance.
     */
	final function __get_priv_mainap(){
		$this->___init_main_ap();
		return $this->mainap; 	
	}
	/**
     * Initializes language indexes for codes.
     */
	private function ___set_lng_indexes_for_codes(){
		if(isset($this->____lng_order)){
			return;	
		}
		$this->____lng_order=false;
		if($a=$this->get_lng_order()){
			$this->____lng_order=array();
			foreach($a as $index=>$code){
				$this->____lng_order[$code]=$index;	
			}
		}
		return;
	}
	/**
     * Retrieves the language index mapping.
     * 
     * @return array|null The language index mapping.
     */
	final function _get_lng_index_for_codes(){
		$this->___set_lng_indexes_for_codes();
		return $this->____lng_order;
	}
	 /**
     * Retrieves the index for a specific language code.
     * 
     * @param string $code The language code.
     * @return int|false The index if found, false otherwise.
     */
	final function _get_lng_index_for_code($code){
		$this->___set_lng_indexes_for_codes();
		if($this->____lng_order){
			return $this->____lng_order[$code];	
		}
		return false;
	}
	 /**
     * Retrieves the language order.
     * 
     * This method should be overridden in subclasses.
     * 
     * @return false|array Returns false by default, should return an array in subclasses.
     */
	function get_lng_order(){
		return false;	
	}
	/**
     * Retrieves a message using the main application's language sub-manager.
     * 
     * @return string|false The retrieved message or false if unavailable.
     */
	function get_msg(){
		$this->__get_priv_mainap();
		if(!$man=$this->mainap->get_submanager("lng")){
			return false;
		}
		$msgslist=func_get_args();
		return $man->get_msg_by_list($msgslist,$this);
	}
	/**
     * Retrieves a message by code using the language sub-manager.
     * 
     * @return string|false The retrieved message or false if unavailable.
     */
	function get_msg_by_code(){
		$this->__get_priv_mainap();
		if(!$man=$this->mainap->get_submanager("lng")){
			return false;
		}
		$msgslist=func_get_args();
		return $man->get_msg_by_list_and_code($msgslist,$this);
	}


	
	
}
?>