<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


class SC_ElementValidationException extends \SC_Exception {
	
	protected $datasValidationMessages = array();
	
	public function __construct(){
		$args = func_get_args();
		$this->datasValidationMessages(array_shift($args));
		call_user_func_array(array('parent', '__construct'), $args);
	}
	
	public function datasValidationMessages($array = array()){
		if(empty($array)){
			return $this->datasValidationMessages;
		}else{
			$this->datasValidationMessages = $array;
		}
	}
}