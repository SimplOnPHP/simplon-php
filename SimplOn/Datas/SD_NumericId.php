<?php

/**
 * NumericId data type
 * 
 * This is a numeric id data type which defined an id which is auto incremented.
 *  
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_NumericId extends SD_Id
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
