<?php

ini_set('display_errors', 1);	
error_reporting(E_ALL ^ E_NOTICE);

include dirname(dirname(__FILE__))."/init.php";
function mw_exec_cron_job_on_file($file){
	$mancod=basename(dirname($file));
	$base=basename($file);
	$jobcod=substr($base,0,strripos($base,"."));
	if(!$ap=mw_get_main_ap()){
		return false;	
	}
	if(!$man=$ap->get_submanager("jobs")){
		return false;	
	}
	return $man->exec_job_as_cron($mancod,$jobcod);
	
}

?>