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



use SE_JS;

/**
 * HTMLText Data type 
 * 
 * This is a textarea data type which allow you show a rich-text editor, you can change 
 * the library to use another rich-text editor plugin if you want.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 * 
 */

class SD_HTMLText extends SD_Text
{
    /**
     * 
     * function getJS - This is a overwritten function that belong to SE_JS 
     * 
     * @param string $method
     * @return array
     */
	
	public function getJS($method) {
        //Is necessary declarate a varible to save the array returned for the original method
        $rich_text = parent::getJS($method);
        // here you can change or you can add more librarys if you want to change 
        // or modify the plugin
		$local_js = SE_JS::getPath("0-nicEdit.js");
        //add our library(s) to the origial array
		$rich_text[] = $local_js;
		// and finally returned the new array
		return $rich_text;
	}
        
	function showInput($fill=true){
		return
        ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label>' : '') .
        '<textarea id="'.$this->htmlId().'" class="'.$this->htmlClasses().'" name="'.$this->inputName().'">'.(($fill)? $this->val :'').'</textarea>';
	}
}