<?php
class mwmod_mw_db_mysqli_dbman extends mwmod_mw_db_dbman{
	public $lastException;
	function __construct($ap){
		$this->init($ap);	
	}
	//todo paramquery
	function query($sql){
		if(!is_string($sql)){
			return false;
		}
		if(!$l=$this->get_link()){
			return false;	
		}
		
		try {
        	$result = $l->query($sql);

			if ($result === false) {
				return false;
			}
			return $result;
    	} catch (Exception $e) {
    		$this->lastException=$e;
        	
        	return false;
    	}
	}

	function insert($sql){
		if(!is_string($sql)){
			return false;
		}
		if(!$l=$this->get_link()){
			return false;	
		}
		if($l->real_query($sql)){
			return $l->insert_id;
		}
		
	}














	//db methods
	function do_connect($cfg){
		if($cfg["port"]??null){
			@$mysqli=new mysqli($cfg["host"]??null,$cfg["user"]??null,$cfg["pass"]??null,$cfg["db"]??null,$cfg["port"]??null );
		}else{
			@$mysqli=new mysqli($cfg["host"]??null,$cfg["user"]??null,$cfg["pass"]??null,$cfg["db"]??null);
		}
		if ($mysqli->connect_error) {
			return false;	
		}
		if($cfg["charset"]??null){
			$mysqli->set_charset($cfg["charset"]);
		}
		return $mysqli;
		
		
			
	}
	function fetch_array($query){
		if(!$query){
			return false;	
		}
		if(!is_object($query)){
			return false;	
		}
		return $query->fetch_array(MYSQLI_BOTH);
		
	}
	
	function fetch_assoc($query){
		if(!$query){
			return false;	
		}
		if(!is_object($query)){
			return false;	
		}
		return $query->fetch_assoc();
		
		
	}
	function real_escape_string($txt){
		if(!$l=$this->get_link()){
			return false;	
		}
		return $l->real_escape_string($txt);
		
		
	}
	function query_get_affected_rows($sql){
		if(!$l=$this->get_link()){
			return false;	
		}
		$this->query($sql);
		return $l->affected_rows;
		
	}
	function get_error(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		$l->error;
		//return mysql_error($l);
			
	}
	function get_errorno(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		$l->errno;
		//return mysql_errno($l);
			
	}
	function affected_rows(){
		if(!$l=$this->get_link()){
			
			return false;	
		}
		return $l->affected_rows;
		
			
	}
	

	
	
	
}
?>