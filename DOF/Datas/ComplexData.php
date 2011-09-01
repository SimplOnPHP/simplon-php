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

class ComplexData extends Data {
		
	protected
		$view = true,
		$create = false,
		$update = false,
		$list = true,
		$fetch = false,
		$required = false,
		$search = false,
		/**
		 * @var \DOF\Elements\Element
		 */
		$parent = null;
	
		
	
	public function __construct($label,$sources,$flags=null,$searchOp=null){
		
		
		$this->sources = $sources;
		
		parent::__construct($label,$flags,null,$searchOp);
	}
	
	public function sourcesToValues($sources = null) {
		if(!isset($sources)) $sources = $this->sources;
		
		$values = array();
		foreach($sources as $source) {
			$values[] = $this->parent->$source();
		}
		return $values;
	}

	
	public function showView($template = null, $sources = null) {return $this->val($sources);}
	public function showInput($fill = false, $sources = null) {return $this->val($sources);}
	

	
	/*@todo: to handle this automatically we'll use 'O' for override in addition to VCRSLF in order to fix at construct time the flags of the source Datas if 'O' is enabled */
	
	public function doRead(){}
	
	public function doCreate(){}
	
	public function doUpdate(){}
	
	public function doSearch(){}
	
	public function doDelete(){}
	
	
	
}