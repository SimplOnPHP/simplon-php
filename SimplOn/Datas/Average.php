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
 * Average data type
 * 
 * This is an average data type which allow calculate the average of a group of 
 * numeric datas to return the average.
 * 
 * @author López Santiago Daniel <http://www.behance.net/zesk8>
 * @copyright (c) 2013, López Santiago Daniel
 * @category Data
 */
class Average extends ComplexData {
    
    public function val($sources = null){
        // $count stores the sum of all values ​​to average
        $count = 0;
        // verify if $sources isn't an array, if it's true create $sources and stores $this->sources
        if(!is_array($sources)) $sources = $this->sources;
        // $values stores the values from $sources
        $values = $this->sourcesToValues($sources);
        // $size stores the total number of values
        $size = sizeof($values);
        // tot every value
        foreach($values as $item){
            $count += $item;
        }
        // finally the average is calculated
        $result = $count/$size;
        // and return it
        return sprintf("%01.2f", $result);
        
    }
}