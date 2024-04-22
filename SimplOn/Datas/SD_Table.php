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
*
* 
* @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
* @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
* @category Data
*/

// class SD_Table extends SD_DorpdownBox
// {
// 	public function __construct($label=null, $rows=array(), $flags=null, $val=null, $filterCriteria=null)
// 	{
// 		$this->rows = $rows;	
// 		parent::__construct($label, $flags, $val, $filterCriteria);
// 	}	
// }

class SD_Table extends SD_Data
{
	protected 
		$simplonCols = 'ignore', // show/ignore
		$colsTitles = true, // true/false
		$rowsTitles = false, // true/false
		$colsTitlesIn = 'keys', // keys/firstRow
		$rowsTitlesIn = 'keys', // keys/firstCol
		$columnsToAdd = array(),
		$columnsToList = array(),
		$rowsToAdd = array(),
		$colsToShow = array(); // array with the specif colums to show
	
	public function __construct($label=null, $args=array(), $val=null, $filterCriteria=null)
	{
		if(is_array($args))
			foreach($args as $arg=>$key){$this->$arg = $key;}	
		$flags='rs';
		parent::__construct($label, $flags, $val, $filterCriteria);
	}	
}