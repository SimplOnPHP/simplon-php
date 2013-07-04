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
 * Float data type  
 * 
 * This is a float data type which allow you show an input to introduce a float number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Float extends Data {
/**
 *
 * @var string $valudationNaN - is a message to display just if the value introduced
 * isn't a float number.
 */
	public $valudationNaN = 'This field must be a float number.';
        /**
         * 
         * function val - This function verifies if the value introduced is a float number, 
         * if isn't throw an exception.
         * 
         * @param null $val
         * @return void 
         * @throws \SimplOn\DataValidationException
         */
        
	function val($val = null) {
            // if $val is defined and isn't null, start to verify the value
	    if(isset($val)) {
                //verify if it's a numeric and float number
		if( is_numeric($val) && is_float($val*1)){
                    //if it's true save the value into $var that belongs to Data
		    $this->val = floatval($val);
		}else{
                    //if it's false throw an exception
		    throw new \SimplOn\DataValidationException($this->valudationNaN);
		}
            } 
            //if $val is null return the variable
            else {
                return $this->val;
	    }
	}
        
	public function showInput($fill) {
            $data_id = 'SimplOn_'.$this->instanceId();

            return 
            ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label><br>' : '') .
            '<input id="'.$data_id.'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}
