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
 * Sum data type
 * 
 * This data receives a string which is used in SUM() to be used in database statements.
 * 
 * @author López Santiago Daniel <http://www.behance.net/zesk8>
 * @copyright (c) 2013, López Santiago Daniel
 * @category Data
 */
class SD_SumDB extends SD_ComplexData {
	
	protected
		$list = true,
		$fetch = true;
    
	/**
	 * val
	 * 
	 * Store the value recived in $this->val.
	 * 
	 * @param string $val
	 * @return string
	 * @throws SC_DataValidationException 
	 */
	function val($val = null){
		if (isset($val) ) {
			if (is_numeric($val) && is_int($val*1)) {
				$this->val = intval($val);
			} else {
				throw new SC_DataValidationException ($this->validationNaN);
			}
		} else {
			return $this->val;
		}
    }
	
	/**
	 * 
	 * doRead
	 * 
	 * Create the syntax of a "sum" like this: "SUM(item) as dataName", which 
	 * will be used in the database statements to sum results.
	 * 
	 * @return array
	 */
	public function doRead(){
		$sumItem = $this->sources;
		return ($this->fetch())
			? array(array('SUM('.$sumItem.') as '.$this->name(), $this->getClass()))
			: null;
	}
}