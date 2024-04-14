<?php
ob_start();
ini_set('display_errors', 1);	
error_reporting(E_ALL ^ E_NOTICE);
include "init.php";
$service = new mwmod_mw_service_hello_hello("service/hello");
$service->execServiceByREQUEST_URI();
?>