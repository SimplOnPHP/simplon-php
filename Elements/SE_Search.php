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
 * Allows searches over multiple Elements.
 * 
 * @todo: allow to specify what fields to display; 
 * in case of auto obtaining the list of fields, then specify
 * if it should be an intersect or union of the Element's Datas.
 * Solve brain-blowing problem of fields with same name and different
 * Search flag :S 
 */
class SE_Search extends SC_Element
{
	static $permissions;
	public $total;

	///RSL 2022
	public $searchResults;

	protected
		$parent, 
		/**
		 * @var array
		 */
		$elementsTypes, 
		/**
		 * @var array
		 */
		$fields;



	public function __construct($id_or_elementsTypes = null, $storage=null, &$specialDataStorage=null)
	{
		//$this->construct($id_or_elementsTypes, $specialDataStorage);
		
		//Asings the storage element for the SimplOnelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = SC_Main::dataStorage();
		}else{
			$this->dataStorage=&$specialDataStorage;
		}
		
		//On heirs put here the asignation of SimplOndata and attributes
		if(is_array($id_or_elementsTypes)){
			if(array_values($id_or_elementsTypes) === $id_or_elementsTypes) {
				$this->elementsTypes = new SD_DArray('', 'vclsR', $id_or_elementsTypes);
			} else {
				try{$this->fillFromArray($id_or_elementsTypes);}catch(SC_ElementValidationException $ev){}
			}
		} else if(isset($id_or_elementsTypes)) {
			$id = $id_or_elementsTypes;
			$this->dataStorage->ensureElementStorage($this);
			$this->fillFromDSById($id);
		}
		
		//checking if there is already a dataStorage and storage for this element
		
		//if there is a storage and an ID it fills the element with the proper info.
		
		
		if(!$this->storage()) {
			$storages = array();
			foreach($this->elementsTypes() as $elementType) {
				$dummy_class = new $elementType;
				$storages[$elementType] = $dummy_class->storage();
			}
			$this->storage($storages);
		}

		$this->getFields();
		
		
		///$this->viewAction = new SD_ViewAction('', array('View'));
		// @todo: make it possible to create a new SE_Search using an HTML form
		///$this->createAction = new SD_CreateAction('', array('Create'));
		///$this->updateAction = new SD_UpdateAction('', array('Update'));
		///$this->deleteAction = new SD_DeleteAction('', array('Delete'));

		// Tells the SimplOndata whose thier "container" in case any of it has context dependent info or functions.
		$this->assignAsDatasParent();
		
		$this->assignDatasName();
	}


	private function getFields(){
		$fields = array();
		$dataObjects = array();

		foreach($this->elementsTypes() as $class){
			$new = new $class;
			foreach($new->dataAttributes() as $dataName) {

				$data = $new->{'O'.$dataName}();

				//if($data->search()) {
					@$fields[$class][$dataName] = $data->getClass();
					if(!isset($dataObjects[$dataName]))
						$dataObjects[$dataName] = $data;
				//}
			}

		}
		
		if(count($fields) > 1) {
			$rintersect = new \ReflectionFunction('array_intersect_assoc');
			$fields = $rintersect->invokeArgs($fields);
		} else {
			$fields = end($fields);
		}

		foreach($fields as $dataName => $dataClass) {
			//$fields[$dataName] = $dataObjects[$dataName];
			$this->$dataName = $dataObjects[$dataName];
		}
	
	}

	public function showView($template_file = null, $partOnly = false) {	
		return $this->showSearch($template_file);
	}


	public function select($template_file = '') {
		return $this->showSearch($template_file);
	}
   

	///RSL 2022 --- Fix para poder leer los datos sin sacar la tabla
	public function getResults($params = null, $columns = null, $position = 1, $limit = null){


		if(is_array($params))
			try{$this->fillFromArray($params);
			}catch(SC_ElementValidationException $ev){}
		else
			try{
				$this->fillFromRequest();
			}catch(SC_ElementValidationException $ev){}	
		// mutilation

		if(is_array($this->dataAttributes)) 
				$this->dataAttributes = array_diff($this->dataAttributes, array('elementsTypes'));
		$elementsTypes = $this->elementsTypes();
		$this->elementsTypes = null;
		
		$this->total = $this->dataStorage->countElements($this);

		$this->searchResults = $this->dataStorage->readElements($this, 'Elements', $position, $limit);

        // restoration
		$this->elementsTypes($elementsTypes);
		$this->dataAttributes[] = 'elementsTypes';

		return $this->searchResults;
	}
        
	function processSearch($params = null, $columns = null, $position = 1, $limit = null){
		
		///RSL 2022 agregue esto --- Fix para poder leer los datos sin sacar la tabla

		$this->getResults($params, $columns, $position, $limit);
		//$return = SC_Main::$RENDERER->table_from_elements($this->searchResults,$columns); //@review the use of $this->datastorage -- such variable must be aasigned to the element DataStorage at some point or it will search on the incorrect DA if the Element's DS is not the default DS
		/// RSL 2022 quite esto 
		//$return = SC_Main::$RENDERER->table_from_elements($this->dataStorage->readElements($this, 'Elements', $position, $limit),$columns); //@review the use of $this->datastorage -- such variable must be aasigned to the element DataStorage at some point or it will search on the incorrect DA if the Element's DS is not the default DS

		$results = new SD_Table('Results',$params,$this->searchResults);

		return $this->renderer()->renderData($results,'showView',null,1);
	}
	
	public function index() {
		return $this->showSearch();
	}
}
