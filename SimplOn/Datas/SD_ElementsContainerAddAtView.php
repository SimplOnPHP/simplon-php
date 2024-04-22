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
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class SD_ElementsContainerAddAtView extends SD_ElementsContainer {
	
	function showView($template = null)
	{
		$ret = parent::showView($template);
		
		foreach ($this->allowedClassesInstances as $element){
			$ret .= $element->getClass().' ---- showCreate ---- ';
			//$ret .= $element->showCreate();
		}
		
		
		
		return $ret;
		
	/*	
		
		if($template) {
           
           $dom = SC_Main::loadDom($template);
           $tempTemplate = $dom[$this->cssSelector()];
           
           $elementsViews = '';
           foreach($this->elements as $element){
               $selector = $element->cssSelector();
               $tmp = $tempTemplate.'';
               $elementTemplate=$tempTemplate[$element->cssSelector()];
               $elementsViews.= $element->showView($elementTemplate,true);
           }
           
           $tempTemplate->html($elementsViews);
        
            return $tempTemplate->html();
        } else {
           // creates a dummy template
            
           foreach($this->allowedClassesInstances as $classInstance){
               $template.= $classInstance->nestingLevel(1)->showView(null, true);
           }
           $dom = \phpQuery::newDocument($template);
           $this->nestingLevelFix($dom);
           
		    foreach($this->allowedClassesInstances as $classInstance){
				$nextStep = $this->encodeURL('makeSelection');
				$ret.= $classInstance->showCreate('',$classInstance->encodeURL(array(), 'processCreate', array($nextStep)));
	//array(),'showCreate',array(null, $classInstance->encodeURL(array(), 'processCreate', array($nextStep))
	//			
			
			
			}
		   
		   
           return $dom.''.$ret;
        }
	
	 */
	}
	
}