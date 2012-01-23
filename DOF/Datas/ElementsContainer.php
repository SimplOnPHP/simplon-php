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
use DOF\Main;


/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class ElementsContainer extends Data {
	protected 
		/**
		 * Logic parent
		 * @var DOF\Elements\Element
		 */
		$parent, 
		/**
		 * Encapsulated elements
		 * @var DOF\Elements\Element
		 */
		$elements;
	
	public function __construct($elements_or_searchId, $label=null, $flags=null, $element_id=null) {
		
		if(is_array($elements_or_searchId)) {
			$this->elements($elements_or_searchId);
		} else {
			
		}
		
		parent::__construct($label,$flags,$element_id);
	}
	
	public function getJS($method) {
		return array();
	}
	
	//CONTINUAR AQUI
	function showView($template = null)
	{
		return '';
	}
	
	public function val($val = null) {

	}
	
	
	public function padre(&$parent=null)
	{
		//check('Hola tu'.$padre->getId() );
		if($this->element()->hasMethod('parent') ) {
			if($parent) {
				$this->element()->parent($parent);
			} else {
				return $this->element()->parent();
			}
		}		
	}	
	
	function showInput($fill)
	{
		$elementsTypes = array();
		$elementsShow = array();
		$elementsAdd = array();
		foreach($this->elements as $element) {
			$elementsTypes[]= $element->getClass();
			//$elementsShow[]= $element->showView();
			$elementsAdd[]= '<a class="lightbox" href="'.$element->encodeURL(array(),'showCreate').'">Add '.$element->getClass().'</a>';
		}
		$return = '
			<a class="lightbox" href="'.Main::encodeURL('Search',array($elementsTypes),'showSearch', array(null, 'multi')).'">List</a>
			'. implode(' ', $elementsAdd).'
			<div class="preview">'.implode(' ', array_unique($elementsShow)).'</div>
			<input class="input" name="'.$this->name().'" type="hidden" />
		';
		
		return $return;
	}
	
}