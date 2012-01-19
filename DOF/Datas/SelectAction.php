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

	public function __construct($label, array $sources = array(), $flags=null, $searchOp=null){
		parent::__construct($label,$sources, 'makeSelection', array(), $flags,null,$searchOp);
	}

    
	public function val($sources = null){
		if(!is_array($sources)) $sources = $this->sources;
        
		//$href = $this->parent->encodeURL($id ? array($id) : array(), $this->method,   array( $this->parent->parentClass() )      );
        $params =  array( $this->parent->dataName(), 'makeSelection', array($this->parent->getId()) ) ;
        $href = Main::encodeURL($this->parent->parentClass(), array($this->parent->parentId()), 'callDataMethod', $params);
		$content = vsprintf(array_shift($sources), $this->sourcesToValues($sources));
		
		return Main::$DEFAULT_RENDERER->link($content, $href, array('class'=>$this->htmlClasses()));
	} 
    
    /*

        $this->element->addOnTheFlyAttribute('parentClass' , new Datas\Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $this->element->addOnTheFlyAttribute('dataName' , new Datas\Hidden(null,'CUSf', $this->name(), '' )    );
        $this->element->addOnTheFlyAttribute('parentId' , new Datas\Hidden(null,'CUSf', $this->parent->getId(), '' )    );
        
        
        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"

     */
    
    
    
}
