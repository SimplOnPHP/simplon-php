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
use \DOF\Datas\Data, \DOF\BaseObject, \DOF\Main, \DOF\Exception;

/**
 * This is the core element to build the site. DOF (Data Oriented FrameWork) is based on data representation, stoarge and manipulation.
 * Elements are the way to indicate the system all data that conforms it. Each Element represents a data set.
 *
 * In practical terms Elements are just Objets with extended capabilities to handdle some comon tasks like:
 * Print their contets, Store their contents, find and retrive the proper data froma a dataStorage, etc.
 *
 * Elemnts are programed and used like any other regular object except
 * that in orther to make their special features work some of their attributes must be DOF data objects.
 *
 * @author RSL
 */
class Element extends BaseObject {
	protected 
		$field_id = 'id',
		$dir,
		$storage,
		$tempFormPrefix,
		/*@var dataStorage \DOF\DataStorages\DataStorage */
		$dataStorage,
		$dataAttributes,
		$storageChecked,
	
		$filterCriteria;
	
	/**
	* Costructor.
	*
	* Meant to be added at the end of all heir's constructors width:
	*
	* parent::__construct($id=null);
	*
	*
	* beacuse it perfroms DOFdata dependant methods but that are common to all DOFelements
	*
	* @param int $id
	*/
	public function __construct($id=null,&$specialDataStorage=null, array $dataConstructParams = array())
	{
		$this->construct($id, $specialDataStorage);
		
		//On heirs put here the asignation of DOFdata and attributes
		
		if(!$this->storage()) $this->storage(end(explode('::',$this->getClass())));
		
		//Asings the storage element for the DOFelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = Main::$DATA_STORAGE;
		}else{
			$this->dataStorage=&$specialDataStorage;
		}

		// Tells the DOFdata whose thier "container" in case any of it has context dependent info or functions.
		$this->assignAsDatasParent();
		
		$this->assignDatasName();
		
		//checking if there is already a dataStorage and storage for this element
		$this->dataStorage->ensureElementStorage($this);
		
		//if there is a storage and an ID it fills the element with the proper info.
		if($id) {
			$this->fillFromDSById($id);
		}
		 
	}
	
	public function construct($id=null, &$specialDataStorage=null) {}
	
	public function index() {
		return '
			<h1>'.$this->getClass().' works!</h1>
			<p><a href="'.$this->encodeURL(array(), 'showCreate').'">Create a new '.$this->getClass().'</a></p>
		';
	}

	//@todo: in  arrays format
	public function fillFromDSById($id = null){
		if(isset($id)) $this->id($id);
		
		if($this->id()){
			/*@var $this->dataStorage DataStorage*/
			$dataArray = $this->dataStorage->readElement( $this );
			
			$this->fillFromArray($dataArray);
		}else{
			throw new Exception('The object of class: '.$this->getClass()." has no id so it can't be filled using method fillElementById" );
		}
	}
	
	public function fillFromArray(array &$array_of_data)
	{
		foreach($array_of_data as $dataName=>$value){
			if(isset($this->$dataName) && ($this->$dataName instanceof Data)){
				$this->$dataName($value);
			}
		}
	}
	
	public function processData($method)
	{
		foreach($this->dataAttributes() as $dataName) {
			$r = $this->$dataName->$method();
			if($r) @$ret[]= $r;
		}
		
		// @todo: verify if it can stay this way
		return @$ret ?: true;
	}

	
	/**
	 *
	 * NOTE: This method is not a simple redirection to $this->fillFromArray($_REQUEST) because the file upload requeires the $_FILES array
	 * Thus the redirection from fillFromRequest to fillFromArray is made at the DOFData and there for any DOFData that needs to
	 * distinguish between both can do it.
	 *
	 */
	public function fillFromRequest()
	{
		return $this->fillFromArray($_REQUEST);
	}
	
	
	// public function save()
	// {
		// /*@var $this->dataStorage DataStorage*/
		// $pre = $this->processData('pre'.ucwords(__FUNCTION__));
		// $exec = $this->processData(__FUNCTION__);
		// $post = $this->processData('post'.ucwords(__FUNCTION__));
// 	
// 		
		// return $this->dataStorage->__FUNCTION__($pre,$this->storage()) && $this->dataStorage->__FUNCTION__($exec,$this->storage()) && $this->dataStorage->__FUNCTION__($post,$this->storage());
	// }
// 	
	// public function create()
	// {
		// /*@var $this->dataStorage DataStorage*/
		// return $this->processData('pre'.ucwords(__FUNCTION__)) && $this->dataStorage->createElement($this) && $this->processData('post'.ucwords(__FUNCTION__));
	// }
// 		
	// public function update()
	// {
		// /*@var $this->dataStorage DataStorage*/
		// return $this->processData('pre'.ucwords(__FUNCTION__)) && $this->dataStorage->updateElement($this) && $this->processData('post'.ucwords(__FUNCTION__));
	// }
// 
	// public function delete()
	// {
		// /*@var $this->dataStorage DataStorage*/
		// return $this->processData('pre'.ucwords(__FUNCTION__)) && $this->dataStorage->deleteElement($this) && $this->processData('post'.ucwords(__FUNCTION__));
	// }
	
	public function templateFilePath($show_type, $alternative = '', $template_type = 'html') {
		return Main::$GENERIC_TEMPLATES_PATH . '/' . $show_type . '/' .$this->getClass() . $alternative . '.' .$template_type;
	}
	
	public function showCreate($template_file = null, $action = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $action);
	}
	
	/* */
	public function showUpdate($template_file = null, $action = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $action);
	}

	public function showView($template_file = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, null);
	}
	
	public function showSearch($template_file = null, $action = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $action).$this->processSearch();
	}
		
	// @todo: allow to obtain only the dom part inherent to the element (and not the whole web page)
	public function obtainHtml($caller_method, $template_file = null, $action = null)
	{	
		//$caller_method = end(// explode('::',$caller_method));
		$VCSL = substr($caller_method, strlen('show'));
		$vcsl = strtolower($VCSL);
		$with_form = in_array($vcsl, array('create', 'update', 'search'));
		 
		if(!isset($template_file)) {
			// get default path
			$template_file = $this->templateFilePath($VCSL);
		}
		
		if($template_file === false || !file_exists($template_file) || Main::$OVERWRITE_LAYOUT_TEMPLATES) {
			$dom = \phpQuery::newDocumentFileHTML(Main::$MASTER_TEMPLATE);
			$dom['head']->append($this->getJS($caller_method, 'html'));
			
			foreach($this->attributesTypes('\\DOF\\Datas\\File') as $fileData)
			{
				if( $fileData->$vcsl() ){
					$enctype = ' enctype="multipart/form-data" ';
					break;
				}
			}
			
			// create and fill file
			$html = '';
			if($with_form) {
				$html.= '<form class="DOF '.$this->getClass().'" '
					. ' action="'. (@$action ?: $this->encodeURL(Main::$construct_params, 'process'.$VCSL) ) .'" ' 
					. ' method="post" ' 
					. @$enctype 
					.'>';
			}
			$html.= '<div class="DOF '.$this->getClass().'">';
			foreach($this as $keydata => $data)
			{
				if( $data instanceof Data && $data->$vcsl() )
				{					
					$html.= '<div class="DOF '.$keydata.'">';
					
					if($with_form) {
						$data_id = 'DOF_'.$data->instanceId();
						$dompart = \phpQuery::newDocumentHTML($data->$caller_method());
						// @todo: Document that class input is MANDATORY
						$dompart['.reference']->attr('ref', $data_id);
						$dompart['.input']->attr('id', $data_id);
						
						if($data->label())
							$html.='<label for="'.$data_id.'">'.$data->label().': </label>';
						
						$html.= $dompart;
					} else {
						$html.= $data->$caller_method();
					}
					
					$html.= '</div>';
				}
			}
			if($with_form) {
				$html.= '<button name="commit" type="submit">'.($vcsl == 'search' ? 'Search' : 'Save').'</button>'
					.'<button name="cancel" onclick="javascript:history.back()">Cancel</button>'
					.'</div></form>';
			} else {
				$html.= '</div>';
			}
			$dom['body'] = $html;
			
			// save file
			if($template_file !== false)
				Main::createFile($template_file, $dom.'');
		} else {
			// opens file
			$dom = \phpQuery::newDocumentFileHTML($template_file);
			
			// fill file with data 
			if($vcsl != 'create') {
				foreach($this as $keydata=>$data) {
					if($data instanceof Data && $data->$vcsl())
					{
						$dom['.DOF.'.$this->getClass().' .DOF.'.$keydata] = $data->$caller_method($dom['.DOF.'.$this->getClass().' .DOF.'.$keydata]);
					}
				}
			}
		}
		
		return $dom;
	}

	public function getJS($method, $returnFormat = 'array', $compress = false) {
		$class = end(explode('\\',$this->getClass()));
		
		$local_js = Main::$JS_FLAVOUR_BASE . "/Inits/$class.$method.js";
		$a_js = file_exists($local_js) ? array($local_js) : array();
		
		foreach($this->dataAttributes() as $data) {
			/*
			$class = end(explode('\\',$this->{'O'.$data}()->getClass()));
			$local_js = \DOF\Main::$JS_FLAVOUR_BASE . "/Inits/$class.$method.js";
			
			if(file_exists($local_js))
				$a_js[] = $local_js;
			 * */
			foreach($this->{'O'.$data}()->getJS($method) as $local_js)
				if(file_exists($local_js))
					$a_js[] = $local_js;
			}
		$a_js = array_unique($a_js);
		$libs = glob(Main::$JS_FLAVOUR_BASE . '/Libs/*');
		sort($a_js);
		sort($libs);
		
		if($compress) {
			// @todo: compress in one file and return the file path
		}
		
		// converts to remote paths
		$a_js = array_map(
			function($fp) {
				return str_replace(Main::$LOCAL_ROOT, Main::$REMOTE_ROOT, $fp);
			},
			array_unique(array_merge(
				$libs,
				$a_js
			))
		);
		
		switch($returnFormat) {
			case 'html':
				return ($a_js)
					? '<script type="text/javascript" src="'. implode('"></script>'."\n".'<script type="text/javascript" src="', $a_js) . '"></script>'
					: '';
			case 'array':
				return $a_js;
		}
	}
	
	/**
	* Tells the DOFdata whose their "container" in case any of it has context dependent info or functions.
	*
	* @param &$dataParent Reference to the logical data parent.
	*/
	public function assignAsDatasParent(&$parent=null){
		if(!isset($parent)) $parent = $this;
		
		foreach($this as $data)
		{
			if($data instanceof Data)
			{
				if( $data->hasMethod('parent')  )
				{
					$data->parent($parent);
				}
			}
		}
	}
	
	public function assignDatasName(){
		foreach($this as $name => $data) {
			if(($data instanceof Data) && !$data->name()) {
				$data->name($name);
			}
		}
	}
	
	
	public function save() {
    	return ($this->{$this->field_id()}())
    		? $this->update() 
			: $this->create();
	}
	
	public function create() {
		$pre = $this->processData('preCreate');
		$id = $this->dataStorage->createElement($this);
		$this->{$this->field_id()}($id);
		
		return $pre && ($id !== false) && $this->processData('postCreate');
	}
	
	public function update() {
		return 
			$this->processData('preUpdate') 
			&& 
			$this->dataStorage->updateElement($this)
			&& 
			$this->processData('postUpdate');
	}
	
	public function delete() {
		return 
			$this->processData('preDelete') 
			&& 
			$this->dataStorage->deleteElement($this)
			&& 
			$this->processData('postDelete');
	}
	
	
    public function __call($name, $arguments)
    {
        
    	if(@$this->$name instanceof Data)
        {
        	if($arguments){ $this->$name->val($arguments[0]); return; }
        	else{ return $this->$name->val(); }
        	
        } else {	
        	
        	$letter=substr($name,0,1);
        	$Xname=substr($name,1);
        	
			if(($letter == strtoupper($letter)) && (@$this->$Xname instanceof Data)) {
				switch($letter) {
					case 'O': 
		   				if($arguments){ $this->$Xname->val($arguments[0]); }
			        	else{ return $this->$Xname; }
						break;
						/*
					case 'F':
						if($arguments){ $this->$Xname->val($arguments[0]); }
        				else{ return $this->$Xname->field(); }
						break;*/
					case 'L':
						if($arguments){ $this->$Xname->val($arguments[0]); }
	        			else{ return $this->$Xname->label(); }
						break;
					default:
						throw new \Exception('Letter '.$letter.' not recognized!');
				}
			} else {
        		return parent::__call($name, $arguments);
        	}
        }
    }
	
	/*@todo determina if this method is neceary or not
	 updateInDS // este debe ser automatico desde el save si se tiene id se genera
	*/
	
	function encodeURL(array $construct_params, $method, array $method_params = array()) {
		return Main::encodeURL($this->getClass(), $construct_params, $method, $method_params);
	}
	
	function processCreate($result = null){
		$this->fillFromRequest();
		//var_dump($result); exit();
		//var_dump($this); exit;
		if($this->create()) {
			if(@$result == 'json') {
				return json_encode(array('id' => $this->id(), 'preview' => $this->showView()->find('body')->html()));
			} else {
				header('Location: '.$this->encodeURL(array($this->id()), 'showUpdate'));
			}
		} else {
			// @todo: error handling
			user_error('Cannot create in DS!', E_USER_ERROR);
		}
	}
	
	function processUpdate(){
		$this->fillFromRequest();
		if($this->update()) {
			header('Location: '.$this->encodeURL(array($this->id()), 'showUpdate'));
		} else {
			// @todo: error handling
			user_error('Cannot update in DS!', E_USER_ERROR);
		}
	}
	
	
	function processSearch(){
		$this->fillFromRequest();
		return \DOF\Renderers\Html4::table($this->dataStorage->readElements($this));
	}
	
	public function defaultFilterCriteria($operator = 'AND') {
		//@todo: make a function that returns the data with a specific VCRSL flag ON or OFF
		$searchables = array();
		foreach ($this->dataAttributes() as $dataName){
			if($this->{'O'.$dataName}()->search() && ($this->$dataName() !== null && $this->$dataName() !== '') ){
				$searchables[]=' (.'.$dataName.') ';
			}
		}
		return implode($operator, $searchables);
	}
	
	/**
	 * Possible labels:
	 * 	name to refer to a data name;
	 * 	.name to refer to a data filterCriteria;
	 *  :name to refer to a data value;
	 * 	"values" to specify a hard-coded value.
	 */
	public function filterCriteria($filterCriteria = null) {
		if(isset($filterCriteria)) $this->filterCriteria = $filterCriteria;
		else{
			if(!isset($this->filterCriteria))
				$this->filterCriteria = $this->defaultFilterCriteria();
			
			$filterCriteria = $this->filterCriteria;
			$patterns = array();
			$subs = array();
			foreach( $this->dataAttributes() as $dataName){
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				$patterns[] = '/(\.'.$dataName.')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
				$subs[] = $this->{'O'.$dataName}()->filterCriteria();
			}
			
			return preg_replace($patterns, $subs, $this->filterCriteria);
		}
	}
	
	function processDelete() {
		if($this->delete()) {
			header('Location: '.$this->encodeURL(array(), 'showCreate'));
		} else {
			// @todo: error handling
			user_error('Cannot delete in DS!', E_USER_ERROR);
		}
	}
	
	// @todo: change name to attributesOfType
	function attributesTypes($type = '\\DOF\\Datas\\Data') {
		foreach($this as $name => $data) {
			if($data instanceof $type) {
				$a[] = $name;
			}
		}
		
		return @$a ?: array();
	}
	
	function dataAttributes() {
		if(!$this->dataAttributes) {
			$this->dataAttributes = $this->attributesTypes();
		}
		
		return $this->dataAttributes;
	}
	
	//vcsrl
	public function datasForView(){
		foreach($this as $data) {
			if($data instanceof Data && $data->$what()) {
				$output.= $data->{'show'.ucfirst($what)}();
			}
		}		
	}
	
	public function datasForCreate(){
		
	}
	
	public function datasForUpdate(){
		
	}	
	
	public function datasForSearch(){
		
	}	
	
	public function datasForList(){
		
	}	
		
	public function datasRequired(){
		
	}
}