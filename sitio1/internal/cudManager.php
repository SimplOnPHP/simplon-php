<?php

require_once "includer.php";


$class = $_REQUEST['class'];



$element = new $class();
$browser = new Browser($element);
/*@var $element Element  */

$template = $element->getTemplatePath($element->getClass().'CUD');

if( file_exists($template) ){
	
	
}else{

	$browser->Pform();
	
	
}