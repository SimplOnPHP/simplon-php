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
 * Text data type
 * 
 * This is a textarea data type which allow you show a common textarea.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Text extends Data
{
    /**
     *
     * @var boolean $list - this variable indicates if this data will be listed. 
     */
	protected $list = false;
	
	function showInput($fill)
	{
		$data_id = 'SimplOn_'.$this->instanceId();
		return  ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label><br>' : '') .
				'<textarea id="'.$this->inputName().'" name="'.$this->inputName().'">'.(($fill)? $this->val :'').'</textarea>';
	}
}