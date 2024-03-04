<?php
class mwmod_mw_mail_phpmailer_man extends mw_apsubbaseobj{
	function __construct(){
		//	
	}
	function new_phpmailer(){
		$obj=new PHPMailer();
		return $obj;	
	}
	
}
include_once("class.phpmailer.php");
include_once("class.pop3.php");
include_once("class.smtp.php");
?>