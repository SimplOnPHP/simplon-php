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
use \DOF\Main;


/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class ElementContainerDropBox extends ElementContainer {

	
//		if($val === '') {
//			$class = $this->element->getClass();
//			$this->element = new $class;
//		} else	if($val !== null) {
//			$this->element->fillFromDSById($val);
//		} else {
//			return @$this->element->getId();
//		}
//
//        $this->element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
//        $this->element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
//        $this->element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
//
//        $this->element->addOnTheFlyAttribute('selectAction' , new RadioButtonSelfId(null,'CUSf')    );
//
//
//        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
//        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
//    
//	}	
	
	
	
	
	function showInput($fill)
	{
        $element = $this->element->getClass();
        $element = new $element();
		
//		  $element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
//        $element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
//        $element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
		$radioButton = new RadioButtonSelfId(null,'CrUSf',$this->val() );
		$radioButton->name($this->name());
        $element->addOnTheFlyAttribute( 'selectAction' , $radioButton );
		//$element->addOnTheFlyAttribute('selectAction' , new Integer('','CUSf',3 ));
		
		
        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
   
        
		//return 'ggggg  '.$this->element->getClassName();
		//return $element->select->showInput();
		return $element->processSelect();
		//return $element->selectAction->showInput();
	}
}