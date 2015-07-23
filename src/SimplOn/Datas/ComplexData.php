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

/**
 * ComplexData is a Data made by combining other Datas 
 * belonging to the same "logic parent" (the same Element).
 * @example /sample_site/Elements/Example_ComplexData.php 
 */
class ComplexData extends Data {
    /**
     *
     * @var boolean $view, $create, $update, $list, $fetch, $required and $search
     * are flags and are defined in  SimplOn\Datas\Data.
     * 
     * @var \SimplOn\Elements\Element $parent - is a reference to parent element of this data.  
     */
		
	protected
		$view = true,
		$create = false,
		$update = false,
		$list = false,
		$fetch = false,
		$required = false,
		$search = false,
		$parent = null;
	
		
	
	public function __construct($label,$sources,$flags=null,$searchOp=null){
		// $this->sources is an array with items to be used for complex data
		$this->sources = $sources;
		
		parent::__construct($label,$flags,null,$searchOp);
	}
	/**
         * function sourcesToValues - this function verifies if $sources isn't defined
         * if is true declare $sources to save $this->sources and define $values as an array
         * and if there is a method with the same name that item $source then svae it into $values
         * and finally return $values.
         * 
         * @param array $sources
         * @return array
         */
	public function sourcesToValues($sources = null) {
            if(!isset($sources)) $sources = $this->sources;
            $values = array();
            foreach($sources as $source) {
                $values[] = $this->parent->$source();
            }
            return $values;
	}

	//showView just return the value from function val()
	public function showView($template = null, $sources = null) {return $this->val($sources);}
	//showInput just return the value from function val()
        public function showInput($fill = false, $sources = null) {return $this->val($sources);}
	

	
	/*@todo: to handle this automatically we'll use 'O' for override in addition to VCRSLF in order to fix at construct time the flags of the source Datas if 'O' is enabled */
	
	public function doRead(){}
	
	public function doCreate(){}
	
	public function doUpdate(){}
	
	public function doSearch(){}
	
	public function doDelete(){}
        
	
}

