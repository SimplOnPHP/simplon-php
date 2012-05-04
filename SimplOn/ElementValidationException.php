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
namespace SimplOn;

class ElementValidationException extends \Exception {
	
	protected $datasValidationMessages = array();
	
	public function __construct(){
		$args = func_get_args();
		$this->datasValidationMessages(array_shift($args));
		call_user_func_array(array('parent', '__construct'), $args);
	}
	
	public function datasValidationMessages($array = array()){
		if(empty($array)){
			return $this->datasValidationMessages;
		}else{
			$this->datasValidationMessages = $array;
		}
	}
}