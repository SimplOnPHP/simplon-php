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
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_Action extends SD_ComplexData {

	protected $methodToCall;

	function __construct($label = null, $methodToCall, $text, $icon = null, $flags = null, $val = null, $filterCriteria = null){

		$dataPrepare = null;
		$this->methodToCall = $methodToCall;

		$layout = new SI_Link([$this,'action'], $text,$icon);
		
		parent::__construct($label, $dataPrepare, $layout, $flags, $val, $filterCriteria);

	}

	function action(){
		return SC_Main::$RENDERER->action($this->parent(),$this->methodToCall);
	}

    // function getLayout($method)
    // {
	// 	if(SC_Main::$PERMISSIONS instanceof SE_User){

	// 		$permissions = SC_Main::$PERMISSIONS->getPermissions($this->parent());

	// 		if($permissions == 'allow'){
	// 			//keep the same method for bellow;
	// 		}elseif($permissions == 'deny'){
	// 			$method = 'showEmpty';
	// 		}elseif(is_array($permissions)){
	// 			$actionMethod = strtolower(str_replace("show", "", $this->method)).'Action';
	// 			if(isset($permissions[$actionMethod])){
	// 				SC_Main::$PERMISSIONS->setCheckDataRule($this->parent(), $this, $permissions[$actionMethod]);
	// 			}
	// 		}
	// 	}
	// 	if(!empty($this->renderOverride) ){$method = $this->renderOverride;}
	// 	if(empty($this->icon)){
	// 		return $this->renderer()->getDataLayoutFromFile($this,$method);
	// 	}else{
	// 		return $this->renderer()->getDataLayoutFromFile($this,$method.'Icon');
	// 	}
    // }
}
