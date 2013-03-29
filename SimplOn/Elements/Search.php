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
namespace SimplOn\Elements;
use 
	\SimplOn\Datas,
	\SimplOn\Datas\DArray,
	\SimplOn\Datas\Link,
	\SimplOn\Main;

/**
 * Allows searches over multiple Elements.
 * 
 * @todo: allow to specify what fields to display; 
 * in case of auto obtaining the list of fields, then specify
 * if it should be an intersect or union of the Element's Datas.
 * Solve brain-blowing problem of fields with same name and different
 * Search flag :S 
 */
class Search extends Element
{
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
		$this->construct($id_or_elementsTypes, $specialDataStorage);
		
		//Asings the storage element for the SimplOnelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = Main::dataStorage();
		}else{
			$this->dataStorage=&$specialDataStorage;
		}
		
		//On heirs put here the asignation of SimplOndata and attributes
		if(is_array($id_or_elementsTypes)){
			if(array_values($id_or_elementsTypes) === $id_or_elementsTypes) {
				$this->elementsTypes = new DArray('', 'vclsR', $id_or_elementsTypes);
			} else {
				$this->fillFromArray($id_or_elementsTypes);
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
		
		
		$this->viewAction = new Datas\ViewAction('', array('View'));
		// @todo: make it possible to create a new Search using an HTML form
		$this->createAction = new Datas\CreateAction('', array('Create'));
		$this->updateAction = new Datas\UpdateAction('', array('Update'));
		$this->deleteAction = new Datas\DeleteAction('', array('Delete'));

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

	public function showView($template_file = '') {	
		return $this->showSearch($template_file);
	}


	public function select($template_file = '') {
		return $this->showSearch($template_file);
	}


	public function showSearch($template_file = '')
	{
		//$this->fillFromRequest();
            return 
                $this->obtainHtml(__FUNCTION__, $this->templateFilePath('Search', '_'.implode('-', $this->elementsTypes())), null)
                . 
                $this->processSearch()
            ;
	}

        
	public function showSelect($template_file = '')
	{
		//$this->fillFromRequest();
            return 
                $this->obtainHtml(__FUNCTION__, $this->templateFilePath('Search', '_'.implode('-', $this->elementsTypes())), null)
                . 
                $this->processSearch(null,null)
            ;
	}        
        
        
	function processSearch($params = null, $columns = null){
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
		
		$return = Main::$DEFAULT_RENDERER->table_from_elements($this->dataStorage->readElements($this, 'Elements'),$columns); //@review the use of $this->datastorage -- such variable must be aasigned to the element DataStorage at some point or it will search on the incorrect DA if the Element's DS is not the default DS
                
        // restoration
		$this->elementsTypes($elementsTypes);
		$this->dataAttributes[] = 'elementsTypes';

		return $return;
	}
	
	public function index() {
		return $this->showSearch();
	}
}
