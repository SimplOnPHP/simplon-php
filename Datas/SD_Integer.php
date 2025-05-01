<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_Integer extends SD_Data {
    /**
     *
     * @var boolean $view,$create,$update and $list - these variables are 
     * flags to indicate if this input will be displayed in the different templates
     */
    protected 
		$view = true,
		$create = true,
		$update = true,
		$list = true;
    /**
     *
     * @var string $validationNaN - is a message to display just if the value introduced
     * isn't an integer number.
     */
    public $validationNaN;
    /**
     * function val - This function verifies if the value introduced is an integer number, 
     * if isn't throw an exception.
     * 
     * @param type $val
     * @return void
     * @throws SC_DataValidationException 
     */


    function __construct($label=null,$campo=null,$inputName=null,$val=null) {
        $this->validationNaN = SC_MAIN::L("The value introduced isn't an integer number");
        parent::__construct($label,$campo,$inputName,$val);
    }

    function val($val = null) {
        // if $val is defined and isn't null, start to verify the value
        if(isset($val)) {
            if(!$this->fixedValue) {
                //verify if it's a numeric and integer number
                if(is_numeric($val) AND is_integer((int)$val)){
                
                    //if it's true save the value into $var that belongs to Data
                    $this->val = (int)$val;
                }else{
                    //if it's false throw an exception
                    throw new SC_DataValidationException ($this->validationNaN);
                }
            }
	    }else{ //if $val is null return the variable
            return $this->val;
	    }
    }
    
    public function showInput($fill=true) {
        return 
        ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label>' : '') .
        '<input id="'.$this->htmlId().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="number" />';
    }
	
//	public function validationMessages(){
//		
//		$ret = parent::validationMessages();
//		
//		if( !(is_numeric($this->val) && is_int($this->val*1)) ) {
//			$ret[]=$this->validationNaN;
//		}
//		
//		return $ret;
//	}
}
