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

class ElementContainer extends Data {
		
	protected $parent, $element;
	
	public function __construct( \DOF\Elements\Element $element, $label=null, $vcuslr=null, $element_id=null) {
		
		$this->element($element);			
		
		parent::__construct($label,$vcuslr,$element_id);
	}
	
	
	public function getJS($method) {
		return array_map(
			function($fp) {
				return str_replace(\DOF\Main::$REMOTE_ROOT, \DOF\Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}
	
	
	function showView($template = null)
	{
		//return $this->parent()->getClass();
		$dom = \phpQuery::newDocumentHTML($this->element()->showView());
		
		return $dom['.DOF.'.$this->element()->getClass()].'';
	}
	
	public function val($val = null) {
		if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			return @$this->element->{$this->element->field_id()}();
		}
	}
	
	
	public function padre(&$parent=null)
	{
		//check('Hola tu'.$padre->id() );
		if($this->element()->hasMethod('parent') )
		{
			if($parent)
			{
				$this->element()->parent($parent);
			}else{
				return $this->element()->parent();
			}
		}		
	}	
	
	function showInput($fill)
	{
		return  $this->parent()->getClass().'<input class="input '.str_replace('\\', ' ', $this->getClass()).'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}	
	
}