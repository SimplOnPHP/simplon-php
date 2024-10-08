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

/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_Action extends SD_Data {
    /**
     *
     * @var boolean $view,$create,$update and $list - these variables are 
     * flags to indicate if this input will be displayed in the different templates
     */
    protected 
		$view = true,
		$create = false,
		$update = false,
		$read = false,
		$list = true,
		$icon = '',

        $fixedValue  = true;
    /**
     *
     * @var string $validationNaN - 
     */
    public $validationNaN = '';

	public function __construct( $method, $text, $icon=null, $label=null, $flags=null)
	{

		$this->method = $method;
        $this->text = $text;

        if(file_exists($this->renderer()->imgsPath().DIRECTORY_SEPARATOR.$icon)){
            $this->icon = $this->renderer()->imgsWebRoot().DIRECTORY_SEPARATOR.$icon;
        }
  
		parent::__construct($label, $flags);
	}

    	
	public function val($val=null){
		if(isset($val)){
			if(!$this->fixedValue){
				$this->val = $val;
			}
		}else{
			if(empty($this->val)){
                return $this->renderer()->action($this->parent(),$this->method);
			}else{
				return $this->val;
			}
		}
	}

    function getLayout($method)
    {

		if(empty($this->icon)){
            return $this->renderer()->getDataLayoutFromFile($this,$method);
        }else{
            return $this->renderer()->getDataLayoutFromFile($this,$method.'Icon');
        }
    }

}
