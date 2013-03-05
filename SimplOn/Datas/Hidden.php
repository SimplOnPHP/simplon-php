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
 * Hidden data type 
 * 
 * Does not print a label and sends a hidden input.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Hidden extends Data
{
    /** 
     * @var boolean $view,$create,$update - these variables are 
     * flags to indicate if this input will be displayed in the different templates.
     * 
     * @var boolean $required - This variable indicates if the input will be required or not. 
     */
	protected
		$view = false,
		$create = false,
		$update = true,
		$required = false;
	/**
         * 
         * function showInput - This function prints the input with the
         * correct format (class, name) to be used in the forms.
         * 
         * @param boolean $fill
         * @return string
         */
	public function showInput($fill)
	{
		if($this->val())
		{
		  return '<input class="SimplOn input '. $this->getClass() .'" name="'. $this->name() .'" '.(($fill)?' value="'.$this->val() .'"':'').' type="hidden" />';
		} 
	}
		
	public function label() {}
}