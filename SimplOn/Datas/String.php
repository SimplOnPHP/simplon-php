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
 * String data type 
 * 
 * This is a string data type which allow you show an input to introduce a string.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class String extends Data {
	/**
         *
         * @var boolean $view,$create,$update and $list - these variables are 
         * flags to indicate if this input will be displayed in the different templates
         * 
         * @var string $filterCriteria - this variable indicates the kind of filter to this
         * kind of data.
         */
	protected 
		$view = true,
		$create = true,
		$update = true,
		$list = true,
		$filterCriteria = 'name ~= :name';
        
	public function showInput($fill) {
		return 
            ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label>' : '') .
            '<input id="'.$this->htmlId().'" class="'.$this->htmlClasses().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}