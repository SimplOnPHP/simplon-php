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

class SelectAction extends ElementLink {

	public function __construct($label, array $sources, $flags=null, $searchOp=null){
		parent::__construct($label,$sources, 'makeSelection', array(), $flags,null,$searchOp);
	}

    
	public function val($sources = null){
		if(!is_array($sources)) $sources = $this->sources;
		
		$id = $this->parent->hasMethod($this->parent->field_id())
			? $this->parent->{$this->parent->field_id()}()
			: null;
		
		//$href = $this->parent->encodeURL($id ? array($id) : array(), $this->method,   array( $this->parent->parentClass() )      );
        $params = ($this->parent->hasMethod('parentClass') && $this->parent->hasMethod('attributeElementName') ) ? array( $this->parent->parentClass(), $this->parent->attributeElementName() ) : array();
        $href = $this->parent->encodeURL($id ? array($id) : array(), $this->method ,  $params    );
		$content = vsprintf(array_shift($sources), $this->sourcesToValues($sources));
		
		return Main::$DEFAULT_RENDERER->link($content, $href, array('class'=>'DOF '.$this->getClassName()));
	} 

}
