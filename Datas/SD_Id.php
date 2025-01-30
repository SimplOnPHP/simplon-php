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
* ID para las tablas
* --- No imprime un label y manda un input hidden.
*
* @version	1.0
* @author	Ruben Schaffer
* @category Data
*/
abstract class SD_Id extends SD_Data
{
	
	
	protected
		$view = false,
		$embeded = false,
		$create = false,
		$update = true,
		$required = true;

	
	/**
	 *
	 * @param type $flags 
	 */
	function dataFlags($flags = null){
		
		parent::dataFlags($flags);
		$this->required = true;
	}
	
	public function showCreate() {
		return null;
	}
		
	public function showUpdate() {
		return null;
	}

	public function showSearch() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Input($this->name(), $this->val(), null, $this->label(), null, $this->ObjectId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}
	
}
 