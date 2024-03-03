<?php
if($cm=$this->mainap->get_submanager("fixcontent")){
	$item=$cm->newContentItem("hello.html");
	echo $item->getContentHTML();
	$item->setPHValue("hola","xxx");

	echo $item->getContentPHMode();
}
?>
