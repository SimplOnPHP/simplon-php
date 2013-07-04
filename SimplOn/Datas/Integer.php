<?php
/*
	Copyright © 2011 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP”.
	
	“SimplOn PHP” is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation version 3 of the License.
	
	“SimplOn PHP” is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with “SimplOn PHP”.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace SimplOn\Datas;
/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Integer extends Data {
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
     * @var string $valudationNaN - is a message to display just if the value introduced
     * isn't an integer number.
     */
    public $valudationNaN = 'This field must be an integer number.';
    /**
     * function val - This function verifies if the value introduced is an integer number, 
     * if isn't throw an exception.
     * 
     * @param type $val
     * @return void
     * @throws \SimplOn\DataValidationException
     */
    function val($val = null) {
        // if $val is defined and isn't null, start to verify the value
        if(isset($val)) {
            //verify if it's a numeric and integer number
            if(is_numeric($val) && is_int($val*1))
                //if it's true save the value into $var that belongs to Data
                $this->val = intval($val);
            else
                //if it's false throw an exception
                throw new \SimplOn\DataValidationException($this->valudationNaN);
            
	}
        //if $val is null return the variable
        else{
            return $this->val;
	}
    }
    
    public function showInput($fill) {
        return 
        ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label><br>' : '') .
        '<input id="'.$this->htmlId().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
    }
	
//	public function validationMessages(){
//		
//		$ret = parent::validationMessages();
//		
//		if( !(is_numeric($this->val) && is_int($this->val*1)) ) {
//			$ret[]=$this->valudationNaN;
//		}
//		
//		return $ret;
//	}
}
