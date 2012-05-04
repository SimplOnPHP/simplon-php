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

class Date extends String {
	var 
		$dateFormat = 'm/d/Y',
		$validationDate = 'Invalid date received!',
		$dateObj;
	
	function val($val = null) {
		if(isset($val)) {
			$val = trim($val);
			if(is_numeric($val)) {
				$this->dateObj = new \DateTime();
				$this->dateObj->setTimestamp($val);
			} else {
				$this->dateObj = new \DateTime($val);
			}
			$this->val = $this->dateObj->format($this->dateFormat);
		} else {
			return $this->val;
		}
	}
	
	//@todo: declare doRead and similar methods
	
}