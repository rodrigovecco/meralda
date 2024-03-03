<?php
ob_start();
ini_set('display_errors', 1);	
error_reporting(E_ALL ^ E_NOTICE);
include "init.php";
$service = new mwap_nm_service_admin("service/admin");
$service->execServiceByREQUEST_URI();
?>