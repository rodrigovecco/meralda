<?php
ini_set('display_errors', 1);	
error_reporting(E_ALL ^ E_NOTICE);
session_start();
ob_start();

include "init.php";

$ap=mw_get_main_ap();
$ui=$ap->get_submanager("uiadmin");
//$ui->exec_login_and_user_validation();
$ui->exec_user_logout();
?>