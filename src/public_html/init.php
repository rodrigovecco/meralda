<?php
include dirname(dirname(__FILE__))."/app/init.php";
if($_ap=mw_get_main_ap()){
	$_ap->set_public_path(dirname(__FILE__));	
}

?>