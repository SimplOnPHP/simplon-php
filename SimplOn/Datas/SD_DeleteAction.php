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
 * DeleteAction data type  
 * 
 * This is a delete action data type which allow you show a direct link to showDelete.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_DeleteAction extends SD_ElementLink {
    /**
     * 
     * function __construct - this construct just especify the method "showDelete"
     * to create the correct link to method 
     * 
     * @param string $label
     * @param array $sources
     * @param string $flags
     * @param null $searchOp
     */
	public function __construct($label, array $sources, $flags=null, $searchOp=null){
		parent::__construct($label, $sources, 'showDelete', array(), $flags,null,$searchOp);
	}
        /**
         * function parent - this function specifies if $parent isn't null then 
         * assign $parent to $this->parent and verifies if the property quickDelete
         * is true then assign precessDelete to $this->method and if it's false then
         * assign showDelete to $this->method. If $parent is null only return $parent.
         * 
         * @param object $parent
         * @return object
         */
	function parent($parent = null) {
            
            if($parent) {
			$this->parent = $parent;
			$this->method = $parent->quickDelete ? 'processDelete' : 'showDelete';
		}
                
		return $parent;
	}
	/**
         * function htmlClasses - overwrite the parent function htmlClasses to modify the class SD_name
         * to "<a></a>" tags
         * 
         * @param string $append
         * @param null $nestingLevel
         * @return string
         */
	function htmlClasses($append = '', $nestingLevel = null) {
            return parent::htmlClasses(($this->parent->quickDelete?' ajax lightbox ':' html ').$append, $nestingLevel);;
        }
	/**
         * function cssSelector - this function specifies the correct css selector for this data
         * 
         * @param string $append
         * @param null $nestingLevel
         * @return string
         */
	function cssSelector($append = '', $nestingLevel = null) {
        return parent::cssSelector(($this->parent->quickDelete?'.ajax':'.lightbox').$append, $nestingLevel);
        }
}