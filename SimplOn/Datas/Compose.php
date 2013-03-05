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
* Used for inserting data and methods according to a specified format at the 
* beginning of the received values​​.
* 
* Compose data type
* 
* @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
* @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
* @category Data
*/

class Compose extends ComplexData {

       /**
        * function val - This function checks if there is an existing array, if 
        * true stores the values ​​in the array $ sources.
        * 
        * @param array $sources
        * @return string
        */
 	public function val($sources = null){
		if(!is_array($sources)) $sources = $this->sources;
        $content = vsprintf(array_shift($sources),$this->sourcesToValues($sources));	
		return $content;
	}  
}
