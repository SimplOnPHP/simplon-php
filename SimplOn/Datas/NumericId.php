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
 * NumericId data type
 * 
 * This is a numeric id data type which defined an id which is auto incremented.
 *  
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class NumericId extends Id
{
    /**
     *
     * @var boolean $autoIncrement - this variable define if this field will be
     * auto incremented or not 
     */
	public $autoIncrement = true;
	/**
         * function showInput - this function verifies  if $this->val() return an
         * empty val or not, if return an empty val the throw an exception.
         * 
         * @param type $fill
         * @throws \SimplOn\Exception
         */
	public function showInput($fill)
	{
		if($this->val())
		{
		} else {
			throw new \SimplOn\Exception('Cannot show this field with empty value!');
			
		}
	}	

	public function label() {}
	
	/**
         * 
         * function val - verifies if $val is an number and if is an integer 
         * then stores $val into $this->val and if isn't a number then display an
         * error message.
         * 
         * @param int $val
         * @return int
         */
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
