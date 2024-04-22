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
 * Date data type
 * 
 * This is a date data type which allow you show a datepicker (jQuery UI) to select a
 * correct date to put in the input. 
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_DateTodayinSeach extends SD_Date {

	function __construct($label=null, $flags=null, $val=null, $filterCriteria=null) {
		if(!$val){
			$dateObj = new \DateTime('NOW');
			$val = $dateObj->format($this->dbFormat);
		}
		parent::__construct($label, $flags, $val, $filterCriteria);
	}

	public function showSearch() {
		$dateObj = new \DateTime('NOW');
		$this->val = $dateObj->format($this->dbFormat);
        return '<input id="'.$this->htmlId().'" class="'.$this->htmlClasses('date').'" name="'.$this->inputName().'" value="'.$this->val.'" type="hidden" />';
	}

	// public function doSearch()
	// {
	// 	return ($this->search() && $this->fetch())
	// 		? array(array($this->name(), $this->getClass(), $this->val(), $this->filterCriteria()))
	// 		: null;		
	// }

	// public function doSearch() {
	// 	$dateObj = new \DateTime('NOW');
	// 	$this->val = $dateObj->format($this->dbFormat);
	// 	parent::doSearch();
	// }

}