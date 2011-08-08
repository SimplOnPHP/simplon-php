<?php
namespace DOF\Elements;
use \DOF\Elements\Element,
	\DOF\Datas\DArray,
	\DOF\Main;

/**
 *
 * @var array $columns	Collection of objects of type Data that represents the columns to print out.
 * @var DataBase $db	Database handler.
 *
 * @author Luca
 *
 */
class Search extends Element
{
	protected
		$parent, 
		$elementsTypes,
		$fields;



	public function __construct($id_or_elementsTypes, &$specialDataStorage=null)
	{
		//On heirs put here the asignation of DOFdata and attributes
		if(is_array($id_or_elementsTypes)){
			$this->elementsTypes = new DArray('','vclsR',$id_or_elementsTypes);
		} else {
			$id = $id_or_elementsTypes;
		}
		
		
		if(!$this->storage()) $this->storage(end(explode('::',$this->getClass())));
		
		//Asings the storage element for the DOFelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = Main::$DATA_STORAGE;
		}else{
			$this->dataStorage=&$specialDataStorage;
		}
		
		//checking if there is already a dataStorage and storage for this element
		
		//if there is a storage and an ID it fills the element with the proper info.

		if( isset($id) ) {
			$this->dataStorage->ensureElementStorage($this);
			$this->fillFromDSById($id);
		}

		$this->getFields();



		// Tells the DOFdata whose thier "container" in case any of it has context dependent info or functions.
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
				if($data->search()) {
					@$fields[$class][$dataName] = $data->getClass();
					if(!isset($dataObjects[$dataName]))
						$dataObjects[$dataName] = $data;
				}
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


	public function showSearch($template_file = '')
	{
		return $this->obtainHtml(__FUNCTION__, $this->templateFilePath('Search', '_'.implode('-', $this->elementsTypes())), null);
	}


	public function processSearch() {
		var_dump($_POST);
	}
}