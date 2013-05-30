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
 * Class Report is a copy of Class Search, this class is just to create reports
 * and it can expand its functionality.
 */

namespace SimplOn\Elements;
use 

	\SimplOn\Datas,
	\SimplOn\Datas\DArray,
	\SimplOn\Datas\Link,
	\SimplOn\Main;

class Report extends \SimplOn\Elements\Search{
	
	protected $group;

	public function __construct($id_or_elementsTypes = null, $storage = null, $specialDataStorage = null, $group = array() ) {
		$this->group = $group;
		parent::__construct($id_or_elementsTypes, $storage, $specialDataStorage);
	}
	
	 protected function getFields() {
		$fields = parent::getFields();
		return $fields;
	}
	
	public function processRep($params = null, $columns = null, $position, $limit) {
		if(is_array($params))
			try{
				$this->fillFromArray($params);
			}catch(\SimplOn\ElementValidationException $ev){}
		else
			try{
				$this->fillFromRequest();
			}catch(\SimplOn\ElementValidationException $ev){}			

		// mutilation
		if(is_array($this->dataAttributes)) 
				$this->dataAttributes = array_diff($this->dataAttributes, array('elementsTypes'));
		$elementsTypes = $this->elementsTypes();
		$this->elementsTypes = null;
		//@review the use of $this->datastorage -- such variable must be aasigned to the element DataStorage at some point or it will search on the incorrect DA if the Element's DS is not the default DS
		$return = Main::$DEFAULT_RENDERER->table_from_elements($this->dataStorage->readElements($this, 'Elements', $position, $limit,$this->group),$columns); 
        // restoration
		$this->elementsTypes($elementsTypes);
		$this->dataAttributes[] = 'elementsTypes';
		
		return $return;
	}
}

