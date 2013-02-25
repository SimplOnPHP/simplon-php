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
 * ElementLink data type  
 * 
 * This is an element link data type which allow you show a link to an specific method.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class ElementLink extends ComplexData {
    /**
     *
     * @var boolean $view, $list, $required - these variables represent flags to
     * indicate how ElementLink will be display 
     */
	protected
		$view = false,
		$list = false,
		$required = false;
        /**
         * function __construct get the parameters to overwrite them in the parent construct
         * 
         * @param string $label
         * @param array $sources
         * @param string $method - indicates the method which is going to direct for example showCreate, showUpdate, etc.
         * @param array $method_params - is an array with specials parameters for $method
         * @param string $flags
         * @param null $searchOp
         */
	
	public function __construct($label, array $sources, $method, array $method_params = array(), $flags=null, $searchOp=null){
            $this->method = $method;
            $this->method_params = $method_params;
            parent::__construct($label,$sources,$flags,null,$searchOp);
	}
        /**
         * 
         * @param array $sources
         * @return string
         */
	public function val($sources = null){
                // verify if $sources is not an array if it's true then $sources stores $this->sources
		if(!is_array($sources)) $sources = $this->sources;
		// @href save the URL to the method indicated
		$href = $this->parent->encodeURL(array(), $this->method, $this->method_params);
                // $content save $sources with the correct format
		$content = vsprintf(array_shift($sources), $this->sourcesToValues($sources));
		// return the a tag with the correct href.
                return  Main::$DEFAULT_RENDERER->link($content, $href, array('class'=>$this->htmlClasses()));
	}
	/**
         * 
         * function htmlClasses - overwrite the parent function htmlClasses to modify the class name
         * to "<a></a>" tags
         * 
         * @param string $append
         * @param undefined $nestingLevel
         * @return string
         */
	function htmlClasses($append = '', $nestingLevel = null) {
        return parent::htmlClasses('Action '.$append, $nestingLevel);
    }
	/**
         * 
         * function cssSelector - this function specifies the correct css selector for this data
         * 
         * @param string $append
         * @param undefined $nestingLevel
         * @return string
         */
	function cssSelector($append = '', $nestingLevel = null) {
        return parent::cssSelector('.Action'.$append, $nestingLevel);
    }
}
