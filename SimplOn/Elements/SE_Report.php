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


class SE_Report extends SE_Search{
	
	public $total;
	protected $group;

	public function __construct($id_or_elementsTypes = null, $storage = null, $specialDataStorage = null, $group = array() ) {
		$this->group = $group;
		$this->construct($id_or_elementsTypes, $specialDataStorage);
		//Asings the storage element for the SimplOnelement. (a global one : or a particular one)
		if (!$specialDataStorage) {
			$this->dataStorage = SC_Main::dataStorage();
		} else {
			$this->dataStorage=&$specialDataStorage;
		}
		//On heirs put here the asignation of SimplOndata and attributes
		if (is_array($id_or_elementsTypes)) {
			if (array_values($id_or_elementsTypes) === $id_or_elementsTypes) {
				$this->elementsTypes = new SD_DArray('', 'vclsR', $id_or_elementsTypes);
			} else {
				$this->fillFromArray($id_or_elementsTypes);
			}
		} else if (isset($id_or_elementsTypes)) {
			$id = $id_or_elementsTypes;
			$this->dataStorage->ensureElementStorage($this);
			$this->fillFromDSById($id);
		}
		//checking if there is already a dataStorage and storage for this element
		//if there is a storage and an ID it fills the element with the proper info.
		if (!$this->storage()) {
			$storages = array();
			foreach ($this->elementsTypes() as $elementType) {
				$dummy_class = new $elementType;
				$storages[$elementType] = $dummy_class->storage();
			}
			$this->storage($storages);
		}
		$this->getFields();
		// Tells the SimplOndata whose thier "container" in case any of it has context dependent info or functions.
		$this->assignAsDatasParent();
		$this->assignDatasName();
	}
	
	 protected function getFields() {
		$fields = array();
		$dataObjects = array();
		foreach ($this->elementsTypes() as $class) {
			$new = new $class;
			$new->changeCurrentFlags(null,'search');
			foreach ($new->dataAttributes() as $dataName) {
				$data = $new->{'O'.$dataName}();
				@$fields[$class][$dataName] = $data->getClass();
				if (!isset($dataObjects[$dataName])) {
					$dataObjects[$dataName] = $data;
				}
			}
		}
		if (count($fields) > 1) {
			$rintersect = new \ReflectionFunction('array_intersect_assoc');
			$fields = $rintersect->invokeArgs($fields);
		} else {
			$fields = end($fields);
		}
		foreach ($fields as $dataName => $dataClass) {
			$this->$dataName = $dataObjects[$dataName];
		}
	}
	
	public function processRep($params = null, $columns = null, $position, $limit) {
		if (is_array($params)) {
			try{
				$this->fillFromArray($params);
			}catch(SC_ElementValidationException $ev){}
		} else {
			try{
				$this->fillFromRequest();
			}catch(SC_ElementValidationException $ev){}			
		}
		// mutilation
		if (is_array($this->dataAttributes)) {
			$this->dataAttributes = array_diff($this->dataAttributes, array('elementsTypes'));
		}
		$elementsTypes = $this->elementsTypes();
		$this->elementsTypes = null;
		//@review the use of $this->datastorage -- such variable must be aasigned to the element DataStorage at some point or it will search on the incorrect DA if the Element's DS is not the default DS
		$this->total = $this->dataStorage->countElements($this, $this->group);

		$return = SC_Main::$DEFAULT_RENDERER->table_from_elements($this->dataStorage->readElements($this, 'Elements', $position, $limit,$this->group),$columns); 
        // restoration
		$this->elementsTypes($elementsTypes);
		$this->dataAttributes[] = 'elementsTypes';
		
		return $return;
	}
}