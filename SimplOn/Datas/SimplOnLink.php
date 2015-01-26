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
namespace SimplOn\Datas;
use \SimplOn\Main;
/**
 * SimplOnLink data type
 * 
 * This is a simplon link data type, this kind of data allows create a direct link to
 * an specific method or to an specific class from another element.   
 * 
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */

class SimplOnLink extends ComplexData {
	 
	public function __construct($label, array $sources, $class, array $construct_params = array(), $method = 'showView', array $method_params = array(), $flags=null, $searchOp=null){
		$this->class = $class;//class name to access
		$this->construct_params = $construct_params;//parameters to pass to the constructor of the class
		$this->method = $method;//method name to access
		$this->method_params = $method_params;//parameters to pass to method
		
		parent::__construct($label,$sources,$flags,$searchOp);
	}
        /**
         * 
         * @param type $sources
         * @return type
         */
	public function val($sources = null){
            // verify if $sources is not an array if it's true then $sources is stored into $this->sources
            if(!is_array($sources)) $sources = $this->sources;
           // @href save the URL to the class indicated with the parameters to initialize it
            $href = Main::encodeURL($this->class, $this->construct_params, $this->method, $this->method_params);
             // $content save $sources with the correct format
            $content = vsprintf(array_shift($sources), $this->sourcesToValues($sources));
            // return the <a> tag with the correct href.
            return Main::$DEFAULT_RENDERER->link($content, $href);
	}

}