<?php
//$dataman=new mwmod_mw_data_man("secret/test");
//mw_array2list_echo($dataman->get_debug_data());
/*
$dm=new mwmod_mw_data_secret("testing/test1");
echo $dm->get_path();

$item=$dm->getItemStr("key");
$item->set_data("hola");
$item->save();
*/



if($cm=$this->mainap->get_submanager("fixcontent")){
	$item=$cm->newContentItem("hello.html");
	echo $item->getContentHTML();
	$item->setPHValue("hola","xxx");

	echo $item->getContentPHMode();
}
?>
