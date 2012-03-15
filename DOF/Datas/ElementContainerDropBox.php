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

	function showInput($fill)
	{
        $nextStep = $this->parent->encodeURLfragment(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection'));
        $addHref = htmlentities(
			$this->element->encodeURL(
				array(),
				'showCreate',
				array(
					'',
					$this->element->encodeURLfragment(
						array(),
						'processCreate',
						array($nextStep)
					)
				)
			)
		);
        return  '
            <span class="SimplOn label">'.$this->label().'</span>:
			<a class="SimplOn lightbox" href="'.htmlentities($this->parent->encodeURL(array(),'callDataMethod',array($this->name(),'showSelect') )).'">List</a>
            <a class="SimplOn lightbox" href="'.$addHref.'">Add</a>
            <div class="SimplOn preview">
                '.$this->showInputView().'
            </div>
            <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
		';
		
		
		
        $element = $this->element->getClass();
        $element = new $element();
        $element->fillFromRequest();
        
        $element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
        $element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );
        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
   
        return $element->processSelect()
        );		
		
		
		
		
		
		
		
		
		
		
		
		
		
	}
}