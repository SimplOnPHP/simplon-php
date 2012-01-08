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
namespace DOF\Datas;

class DArray extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	//@todo determine the proper input for the array
	function showInput($fill)
	{
		return '<input class="'.$this->htmlClasses('input').'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}


	function val($val=null){
		if(isset($val)){
			if(is_array($val))
				$this->val = $val;
			else
				throw new \DOF\Exception('Reveived value should be an array!');
		}else{
			return $this->val;
		}
	}
	
	
}