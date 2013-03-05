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
 * PrintF data type
 * 
 * This is a PrintF data type which allow you print a text with format.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */

class PrintF extends ComplexData {
	/**
         * 
         * Function val - The function checks if the value is null,if it's returns showView.
         *  
         */	
    public function val($val=null){
        if(!isset($val)){
            return $this->showView();
        }
        
    }
        /**
         * 
         * Function showView - This function overwrite the original showView 
         * function to show an output with format.
         * 
         */    
	public function showView($fill = null){
            return vsprintf(array_shift($this->sources), $this->sources);
	}
}
