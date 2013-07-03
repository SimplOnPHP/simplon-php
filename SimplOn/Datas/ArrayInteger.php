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
	 * ArrayInteger data type  
	 * 
	 * Displays a selection box with the keys of the array is passed as parameter 
	 * and stores the value associated with the key in the database.
	 * 
	 * @author Castillo García Maximino and López Santiago Daniel
	 * @copyright (c) 2013, Castillo García Maximino and López Santiago Daniel
	 * @category Data
	 */
class ArrayInteger extends Integer {
         /**
         * @var boolean $view,$create,$update and $list - these variables are 
         * flags to indicate if this input will be displayed in the different templates   
         */
    protected 
		$view = true,
		$create = true,
		$update = true,
		$list = true,
		$sources;
	 /**
	  * @var array $sources
	  * @var string $valudationNaN - is a message to display just if the value introduced
	  * isn't an integer number.
	  */
    public $valudationNaN = 'This field must be an integer.';
    	 /**
	  * @param strig $label
	  * @param array $sources
	  * @param string $flags
	  * @param type $searchOp
	  */
    public function __construct($label,$sources,$flags=null,$searchOp=null){
        $this->sources = $sources;
	parent::__construct($label,$flags,null,$searchOp);
    }
	 /**
          * function val - This function verifies if the value introduced is an integer, 
          * if isn't throw an exception.
          * 
          * @param type $val
	  * 
          * @return void
          * @throws \SimplOn\DataValidationException
          */
    public function val($val = null){
        $val = (int) $val;
	if($val){
            if(is_numeric($val) && is_int($val*1)){
                if(in_array($this->sources[$val],$this->sources)){
                    $this->val = $val;
                    return $this->val;
              }
                }else{
		    throw new \SimplOn\DataValidationException($this->valudationNaN);
		}
		}else{
			return $this->val;
	        }
    }
	/**
         * function showInput - This function shows a select with the values ​​in the array $sources.
         *  
	 * @param boolean $fill
	 * @return string
	 */
    public function showInput($fill) {
        $datas = $this->sources;
	$select = '';
	$select = '<label for="'.$this->htmlId().'">'.$this->label().': </label>'.
		  '<select id="'.$this->htmlId().'" class="'.$this->htmlClasses().'" name="'.$this->name().'" >';
	$select .= '<option value="none">None</option>';
	foreach ($datas as $key=>$value){		
	$select .= '<option value="'.$key.'"'.((isset($this->val))
		? ((($key === $this->val)) ? 'selected' : '')
		: '').'>'.$value.'</option>';
	}
		$select .= '</select>';
		return $select;
    }
	/**
	 * function showView - This function shows the option selected in the select. 
	 * 
	 * @return string
	 */
	function showView($template = null){
		if($this->val === null){
			return $this->sources = '';
			}else{
			return $this->sources[$this->val()];
			}
        	}
	}
