<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/




/**
* SD_AutoIncrementId data type
* 
* This is a numeric id data type which defined an id which is auto incremented.
* Intended for relational databases auto increment ids
*  
* @version 1b.1.0
* @package SimplOn\Datas
* @author Ruben Schaffer and Luca Lauretta
*/
class SD_AutoIncrementId extends SD_Id
{
	
	/**
     *
     * @var boolean $autoIncrement - this variable define if this field will be
     * auto incremented or not 
     */
	public $autoIncrement = true;

	/**
	 * function showInput - this function verifies  if $this->val() return an
	 * empty val or not, if return an empty val the throw an exception.
	 *
	 * @param mixed $fill
	 * @throws SC_Exception
	 */
	public function showInput($fill = true)
	{
		if($this->val())
		{
		} else {
			throw new SC_Exception('Cannot show this field with empty value!');
			
		}
	}	

	public function label($label = null) {}
	
	/**
	 *
	 * function val - verifies if $val is an number and if is an integer
	 * then stores $val into $this->val and if isn't a number then display an
	 * error message.
	 *
	 * @param int $val
	 * @return int
	 */
	function val($val = null) {
		if(isset($val)) {
			if(!$this->fixedValue) {
				$val = (int) $val;
				if( is_numeric($val) && is_int($val*1) ) 
					$this->val = intval($val);
				else
					user_error('Non-numeric value received.');
			}
		}
		return $this->val;
	}	

	public function showDelete() {
		return $this->renderer()->render($this,'showView');
	}
}
