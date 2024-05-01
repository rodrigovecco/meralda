<?php
$subpathman=mw_get_main_ap()->get_sub_path_man("modulesext/fpdf","system");
if($fullpath=$subpathman->get_file_path_if_exists("fpdf.php")){
	require_once $fullpath;
}
class mwmod_mw_extmods_fpdf extends FPDF{
	
}

?>