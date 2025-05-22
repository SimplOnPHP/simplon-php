<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Exception class for element validation errors.
 *
 * @version 1b.1.0
 * @package SimplOn\Core
 * @author Luca Lauretta 
 */
class SC_ElementValidationException extends \SC_Exception {
	
	/**
	 * @var array An array to store validation messages.
	 */
	protected $datasValidationMessages = array();
	
	/**
	 * Constructor.
	 * 
	 * @param array $datasValidationMessages An array of validation messages.
	 * @param string $message The exception message.
	 * @param int $code The exception code.
	 * @param \Throwable $previous The previous throwable used for the exception chaining.
	 */
	public function __construct(){
		$args = func_get_args();
		$this->datasValidationMessages(array_shift($args));
		call_user_func_array(array('parent', '__construct'), $args);
	}
	
	/**
	 * Get or set the data validation messages.
	 * 
	 * @param array $array Optional. An array of validation messages to set. If empty, the current messages are returned.
	 * @return array The data validation messages.
	 */	
	public function datasValidationMessages($array = array()){
		if(empty($array)){
			return $this->datasValidationMessages;
		}else{
			$this->datasValidationMessages = $array;
		}
	}
}