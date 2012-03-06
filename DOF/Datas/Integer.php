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

class Integer extends Data {
	public $valudationNaN = 'This field must be a number.';
	
	function val($val = null) {
		if(isset($val)) {
			$this->val = intval($val);
		} else {
			return $this->val;
		}
	}
    
	public function showInput($fill) {
        $data_id = 'DOF_'.$this->instanceId();
		
		if($this->validationMessage){$fill=false;}
		
		return 
            ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label>' : '') .
            '<input id="'.$data_id.'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
	
	public function validationMessages(){
		
		$ret = parent::validationMessages();
		
		if( !(is_numeric($this->val) && is_int($this->val*1)) ) {
			$ret[]=$this->valudationNaN;
		}
		
		return $ret;
	}
}
