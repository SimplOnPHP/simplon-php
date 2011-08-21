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

/**
* ID para las tablas
* --- No imprime un label y manda un input hidden.
*
* @version	1.0
* @author	Ruben Schaffer
* @todo
*/
class Id extends Integer
{
	protected
		$view = false,
		$create = false,
		$update = true,
		$required = true;
	
	public function showInput($fill)
	{
		if($this->val())
		{
			return '<input class="DOF input '. $this->getClass() .'" name="'. $this->name() .'" '.(($fill)?' value="'.$this->val() .'"':'').' type="hidden" />';
		} else {
			throw new \DOF\Exception('Cannot show this field with empty value!');
		}
	}
	
	function dataFlags($flags)
	{
		parent::dataFlags($flags);
		$this->required = true;
	}
	
	public function label() {}
}
