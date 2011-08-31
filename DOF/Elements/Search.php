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
namespace DOF\Elements;
use \DOF\Datas\DArray,
	\DOF\Datas\Link,
	\DOF\Main;

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



	public function __construct($id_or_elementsTypes, &$specialDataStorage=null)
	{
		//On heirs put here the asignation of DOFdata and attributes
		if(is_array($id_or_elementsTypes)){
			$this->elementsTypes = new DArray('','vclsR',$id_or_elementsTypes);
		} else {
			$id = $id_or_elementsTypes;
		}
		
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
		
		
		if(!$this->storage()) {
			$storages = array();
			foreach($this->elementsTypes() as $elementType) {
				$dummy_class = new $elementType;
				$storages[] = $dummy_class->storage();
			}
			$this->storage($storages);
		}

		$this->getFields();

		//------------------------
		
		//$this->selectLink = new Link('$link' , '$label', '$flags' );
		

		
		//-------------------------

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
	/* ejemplo de comentario */
	/* yo lo veo bastante fluido, se tarda como medio segundo en mostrar la palabra que escribí,
	 * lo cual se me hace aceptable ok pues podemos probar sin audio y por telefono solo tengo que comprar un buen telefono
	 * que igual me hace falta
	 * como ve slo que yo pongo?
	 *  como? oooo si ya veo jaja solo que escribo muy mal
	 *  sas
	 *  pues con el audio aprte si puede jalar
	 *  sale podemos tratar de trabajar asi a ver que pasa
	 *  
	 *  
	 *  solo que sigo ocn la duda de si la coleccion es dato o elemento
	 *  creo que habra uan y una
	 *  mmm no se a mi me desqueicia mucho
	 *  
	 */

	public function showView($template_file = '') {	
		return $this->showSearch($template_file);
	}


	public function select($template_file = '') {
		return $this->showSearch($template_file);
	}


	public function showSearch($template_file = '')
	{
		//var_dump($this);	
		return $this->obtainHtml(__FUNCTION__, $this->templateFilePath('Search', '_'.implode('-', $this->elementsTypes())), null);;
	}

	function processSearch($params = null){
		if(isset($params))
			$this->fillFromArray($params);
		else
			$this->fillFromRequest();
		
		$elementsTypes = $this->elementsTypes;
		$this->elementsTypes = null;
		var_dump( $this->dataStorage->readElements($this) );
		$return = Main::$DEFAULT_RENDERER->table($this->dataStorage->readElements($this));
		$this->elementsTypes = $elementsTypes;
		return $return;
	}
}