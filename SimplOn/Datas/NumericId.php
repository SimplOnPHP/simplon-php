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

class NumericId extends Id
{
	public $autoIncrement = true;
	
	public function showInput($fill)
	{
		if($this->val())
		{
		} else {
			throw new \SimplOn\Exception('Cannot show this field with empty value!');
			
		}
	}	

	public function label() {}
	
	
	function val($val = null) {
		if(isset($val)) {
			if( is_numeric($val) && is_int($val*1) ) 
				$this->val = intval($val);
			else
				user_error('Non-numeric value received.');
		} else {
			return $this->val;
		}
	}
}
