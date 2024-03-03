<?php
ini_set('display_errors', 1);	
error_reporting(E_ALL ^ E_NOTICE);
session_start();
ob_start();
include "init.php";
$ap=mw_get_main_ap();
$ap->exec_submancmd_and_user_validation();
//$ap->exec_user_validation();
//ob_end_clean();
//mw_array2list_echo($ap->debug_exec_submancmd());
//$ap->exec_submancmd();

?>