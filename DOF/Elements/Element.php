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
use DOF\Datas\ComplexData;

use DOF\DataStorages\DataStorage;

use \DOF\Datas, \DOF\Datas\Data, \DOF\BaseObject, \DOF\Main, \DOF\Exception, \DOF\Elements\JS, \DOF\Elements\CSS;

/**
 * This is the core element to build the site. Elements are the way to indicate the system all data that conforms it. 
 * Each Element represents a data set.
 *
 * In practical terms Elements are just Objets with extended capabilities to handle some common tasks like:
 * Print their contents, Store their contents, find and retrieve the proper data from a dataStorage, etc.
 *
 * Elements are programmed and used like any other regular object except that, 
 * in order to make their special features work, some of their attributes must be SimplON Data objects.
 *
 * @author RSL
 */
class Element extends BaseObject {

	/**
	 * Name of the Data attribute that represents 
	 * the ID field of the Element
	 * (ie. SQL primary key's column name). 
     * @todo enable handle of multiple id fields, that should be automatically
     * detected, as those should be all instances of \DOF\Data\Id).
	 * @var string
	 */
	protected $field_id;

	/**
	 * What DataStorage to use.
	 * @var DOF\DataStorages\DataStorage
	 */
	protected $dataStorage;
	
	/**
	* Name of the storage associated to this Element
	* (ie. SQL table name, MongoDB collection name).
	* @var string
	*/
	protected $storage;	
	
	/**
	* Criteria to use for searching.
	* @example (.Data1) AND (Data2 == "Hello")
	* @var string
	*/
	protected $filterCriteria;
    
	protected $deleteCriteria;
	
	/**
     * Represents the nesting level of the element (1 is the ancestor element,
     * greater values means deeper nesting).
     * Used in the rendering process.
     * @var integer
     */
    protected $nestingLevel = 1;
    
    protected $parent;

	
	
//------------------------------------------- ???	
	/**
	* Flag to avoid the system to validate
	* DataStorage more than once.
	* @var boolean
	*/
	protected $storageChecked;	
	
	
//------------------------------------------Performance	
	/**
	* Stores a list of Element's attributes
	* of type Data for better performance.
	* @var array containing objects of type DOF\Datas\Data
	*/
	protected $dataAttributes;
	
	
	
	
	
	protected $validationMessages = array();
	
//-----------------------------------------------------------------------------------------	
//------------------------------ METHODS --------------------------------------------------	
//-----------------------------------------------------------------------------------------
	
	
	
	/**
	 * - Calls user defined constructor.
	 * - Adds default Element's actions.
	 * - Validates DataStorages.
	 * - Fills its Datas' values if possible (requires a valid ID or array of values).
	 * - Fills some of its Datas' meta-datas (parent, names).
	 * @param mixed $id_or_array ID of the Element or array of Element's Datas values.  
	 * @param DataStorage $specialDataStorage DataStorage to use in uncommon cases.
	 */
	public function __construct($id_or_array = null, $storage=null, $specialDataStorage=null)
	{
        $this->construct($id_or_array, $storage, $specialDataStorage);
		
		//On heirs put here the asignation of DOFdata and attributes
		
		if($storage) 
            $this->storage($storage);
        else
            $this->storage(strtr($this->getClass(), '\\', '_'));
		
		//Asings the storage element for the DOFelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = Main::dataStorage();
		}else{
			$this->dataStorage=&$specialDataStorage;
		}
		
		if( !isset($this->viewAction) )$this->viewAction = new Datas\ViewAction('', array('View'));
		if( !isset($this->createAction) )$this->createAction = new Datas\CreateAction('', array('Create'));
		if( !isset($this->updateAction) )$this->updateAction = new Datas\UpdateAction('', array('Update'));
		if( !isset($this->deleteAction) )$this->deleteAction = new Datas\DeleteAction('', array('Delete'));
		//if( !isset($this->selectAction) )$this->selectAction = new Datas\SelectAction('', array('Select'));
		//$this->multiSelectAction = new Datas\DeleteAction('', array('Select'));
 
        //Load the attributes on the fly
        $this->addOnTheFlyAttributes();
		
		$this->assignDatasName();
		
		//var_dump($this->{$this->field_id()}());

		// Tells the DOFdata whose thier "container" in case any of it has context dependent info or functions.
		$this->assignAsDatasParent();
		
		
	
		
		
		
		//checking if there is already a dataStorage and storage for this element
		$this->dataStorage->ensureElementStorage($this);
		
		if(is_array($id_or_array)) {
			$this->fillFromArray($id_or_array);
		} else if($id_or_array) {
			//if there is a storage and an ID it fills the element with the proper info.
			$this->fillFromDSById($id_or_array);
		}
 
        
	}
	
	/**
	 * User defined constructor, called within {@link __constructor()},
	 * useful to declare specific Data attributes.
	 * @param mixed $id_or_array ID of the Element or array of Element's Datas values.  
	 * @param DOF\DataStorages\DataStorage $specialDataStorage DataStorage to use in uncommon cases.
	 */
	public function construct($id_or_array = null, &$specialDataStorage=null) {}
 
	
	
	/**
	 *@todo
	 * 
	 * check why this can't be changed by 
	 * $this->field_id = $this->attributesTypes('\\DOF\\Datas\\Id');
	 * $this->field_id = $this->field_id[0];
	 * at the _construct Method
	 * 
	 * Change the hole field_id concept from string to array
	 * 
	 * @param type $val
	 * @return type 
	 */
	public function field_id($val=null){
		if(!$this->field_id){
			$this->field_id = $this->attributesTypes('\\DOF\\Datas\\Id');
			$this->field_id = $this->field_id[0];		
		}
		
		if($val){
			$this->field_id=$val;
		}else{
			return $this->field_id;
		}
	}



	
	function validateRequiredDatas(){
		$requiredDatas = $this->datasWith('required');
		foreach($requiredDatas as $requiredData){
			if( !$this->$requiredData() && !@$this->$requiredData->autoIncrement()  ){
				$this->validationMessages[$requiredData][] = $this->$requiredData->validationRequired();
			}

		}
	}
	

	function addValidationMessage($dataname,$message){
		$this->validationMessages[$dataname][]=$message;
		
	}

	function clearValidationMessages($dataname){
		$this->validationMessages[$dataname]=array();
	}


	function parent(&$parent = null){
        if(!$parent){
            return $this->parent;
        } else {
            $this->parent = $parent;
            $this->nestingLevel($parent->nestingLevel()+1);
        }
    }
	
	/**
	 * Allows some simplicity for coding and declarations, auto makes getters and setters 
	 * so that any Data’s attribute value data->val() can be transparently accessed as a normal
	 * element attribute by Element->data(); and load all other BasicObject SimplON functionality 
	 * @see DOF.BaseObject::__call()
	 * 
	 */
	public function __call($name, $arguments) {
		if(@$this->$name instanceof Data) {
			if($arguments){
				return $this->$name->val($arguments[0]);
			} else {
				return $this->$name->val();
			}
			 
		} else {
			 
			$letter=substr($name,0,1);
			$Xname=substr($name,1);
			 
			if(($letter == strtoupper($letter)) && (@$this->$Xname instanceof Data)) {
				switch($letter) {
					case 'O':
						if($arguments){
							$this->$Xname->val($arguments[0]);
						} else {
							return $this->$Xname;
						}
						break;
					/*
					case 'F':
						if($arguments){ $this->$Xname->val($arguments[0]); }
						else{ return $this->$Xname->field(); }
						break;*/
					case 'L':
						if($arguments) {
							$this->$Xname->val($arguments[0]);
						} else {
							return $this->$Xname->label();
						}
						break;
					default:
						throw new \Exception('Letter '.$letter.' not recognized!');
				}
			} else {
				return parent::__call($name, $arguments);
			}
		}
	}
	
	function htmlClasses($append = '', $nestingLevel = null) {
        if(!$nestingLevel) $nestingLevel = $this->nestingLevel();
        return 'SimplOn Element '.'SNL-'.$nestingLevel.' '.$this->getClassName().' '.$append;
    }
	
	function cssSelector($append = '', $nestingLevel = null) {
        if(!$nestingLevel) $nestingLevel = $this->nestingLevel();
        return '.SimplOn.Element.SNL-'.$nestingLevel.'.'.$this->getClassName().$append;
    }
	
	// -- SimplON key methods	
	/**
	 * Assigns to each Data attribute it's corresponding value 
	 * from an array of values.
	 * 
	 * @param array $array_of_data
	 */
	public function fillFromArray(array &$array_of_data)
	{
		foreach($array_of_data as $dataName=>$value){
			if(isset($this->$dataName) && ($this->$dataName instanceof Data)){
				$this->$dataName($value);
			}
		}
		
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
		$this->fillFromArray($_REQUEST);
		$this->validateRequiredDatas();
		/**
		 * COMPLETE THE PART TO HANDLE FILES
		 */
	}	
	
	//------------Data Storage
	/**
	* Retrieves the element's Datas values from the DataSotarage,
	* using the recived Id or the element's id if no id is provided.
	*
	* @param mixed $id the id of the element whose data we whant to read from de DS
	* @throws Exception
	*
	* @todo: in  arrays format ????
	*/
	public function fillFromDSById($id = null){
		if(isset($id)) $this->setId($id);
	
		if($this->getId()){
				
			$dataArray = $this->dataStorage->readElement( $this );
				
			$this->fillFromArray($dataArray);
		}else{
			throw new Exception('The object of class: '.$this->getClass()." has no id so it can't be filled using method fillElementById" );
		}
	}	
	
	public function save() {
    	return $this->getId() !== false
    		? $this->update() 
			: $this->create();
	}
	
	public function create() {
		$this->processData('preCreate');
		
		$id = $this->dataStorage->createElement($this);
		$this->setId($id);
		
		$this->processData('postCreate');
		
		return $id !== false;
	}
	
	public function update() {
		$this->processData('preUpdate');
		$return = $this->dataStorage->updateElement($this);
		$this->processData('postUpdate');
		return $return;
	}
	
	public function delete() {
		return 
			$this->processData('preDelete') 
			&& 
			$this->dataStorage->deleteElement($this)
			&& 
			$this->processData('postDelete');
	}
	
	/*@todo determina if this method is neceary or not
	 updateInDS // este debe ser automatico desde el save si se tiene id se genera
	*/
	
	function processCreate($nextStep = null){
		$this->fillFromRequest();
		
		if(empty($this->validationMessages)){
			if($this->create()){
				if(empty($nextStep)) {
					header('Location: '.$this->encodeURL(array($this->getId()), 'showUpdate'));
				} else {
					header('Location: '.$nextStep . Main::encodeUrl(null,null,null, array(array($this->getId(),$this->getClass()))) );
				}

			} else {
				// @todo: error handling
				user_error('Cannot update in DS!', E_USER_ERROR);
			}
		}else{
			var_dump($this->validationMessages);
		}
	}
	
	//function processUpdate($short_template=null, $sid=null){
	function processUpdate($nextStep = null){
		$this->fillFromRequest();
        if($this->update()){

            if(empty($nextStep)) {
                header('Location: '.$this->encodeURL(array($this->getId()), 'showAdmin'));
            } else {
                header('Location: '.$nextStep);
                //if($sid) $this->sid($sid);
                //return $this->makeSelection($short_template, $sid);
            }
        
		} else {
			// @todo: error handling
			user_error('Cannot update in DS!', E_USER_ERROR);
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
		
	function processSearch(){
		$this->fillFromRequest();
		$search = new Search(array($this->getClass()));
		return $search->processSearch($this->toArray());
	}
    
    
   /* 
    function processSelection(){

        $parentClass = $_??????['parentClass'];
        
        
        
        
        //-------------------
        $jsInstructions = array(
            '' => , 
            'b' => , 
            'c' => , 
            'd' => , 
            'e' => ,
        );
        echo json_encode($jsInstructions);
	}
     */   
 	function processSelect(){
		$this->fillFromRequest();
		$search = new Search(array($this->getClass()));
                
       // $colums = array_merge( $this->datasWith("list"), array("selectAction","parentClass") );
        $colums = array_merge( $this->datasWith("list"), array("selectAction") );
        
		return $search->processSearch($this->toArray(),$colums);
	}       

         
 	function processAdmin(){
		$this->fillFromRequest();
		$search = new Search(array($this->getClass()));
  
        $colums = array_merge($this->datasWith("list"), array("deleteAction","viewAction","updateAction") );
                              
		return $search->processSearch($this->toArray(),$colums);
	}        
        
	
	public function defaultFilterCriteria($operator = 'AND') {
		//@todo: make a function that returns the data with a specific VCRSL flag ON or OFF
		$searchables = array();
		foreach ($this->dataAttributes() as $dataName){
			if($this->{'O'.$dataName}()->search() && $this->{'O'.$dataName}()->fetch() && ($this->$dataName() !== null && $this->$dataName() !== '') ){
				$searchables[]=' (.'.$dataName.') ';
			}
		}
		return implode($operator, $searchables);
	}
	
	/**
	 * ????????????????????
	 * 
	 * Possible labels:
	 * 	name to refer to a data name;
	 * 	.name to refer to a data filterCriteria;
	 *  :name to refer to a data value;
	 * 	"values" to specify a hard-coded value.
	 */
	public function filterCriteria($filterCriteria = null) {
		if(isset($filterCriteria)) $this->filterCriteria = $filterCriteria;
		else{
			
            //REMOVED so it adapts on every run if necesary
            if(!isset($this->filterCriteria))
				$this->filterCriteria = $this->defaultFilterCriteria();
			
			//$filterCriteria = $this->filterCriteria;
            
			$patterns = array();
			$subs = array();
			foreach( $this->dataAttributes() as $dataName){
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				$fc = $this->{'O'.$dataName}()->filterCriteria();
                if( !empty($fc) ){ 
                        $patterns[] = '/(\.'.$dataName.')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
                        $subs[] = $fc;
                    }
			}
			
            //$ret = preg_replace($patterns, $subs, $filterCriteria);
			return preg_replace($patterns, $subs, $this->filterCriteria);
		}
	}
	
	
	
    
	public function deleteCriteria($deleteCriteria = null) {
		if(isset($deleteCriteria)) $this->deleteCriteria = $deleteCriteria;
		else{
			
            //REMOVED so it adapts on every run if necesary
            if(!isset($this->deleteCriteria))
				$this->deleteCriteria = $this->defaultDeleteCriteria();
			
			//$filterCriteria = $this->filterCriteria;
            
			$patterns = array();
			$subs = array();
			foreach( $this->dataAttributes() as $dataName){
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				$fc = $this->{'O'.$dataName}()->filterCriteria();
                if( !empty($fc) ){ 
                        $patterns[] = '/(\.'.$dataName.')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
                        $subs[] = $fc;
                    }
			}
			
            //$ret = preg_replace($patterns, $subs, $filterCriteria);
			return preg_replace($patterns, $subs, $this->deleteCriteria);
		}
	}
	public function defaultDeleteCriteria($operator = 'AND') {
		//@todo: make a function that returns the data with a specific VCRSL flag ON or OFF
		$searchables = array();
		foreach ($this->dataAttributes() as $dataName){
			if($this->{'O'.$dataName}()->fetch() && ($this->$dataName() !== null && $this->$dataName() !== '') ){
				$searchables[]=' (.'.$dataName.') ';
			}
		}
		return implode($operator, $searchables);
	}
	


	
	
	/**
	* Sets the current instance the as "logical" parent of the Datas.
	* Thus the datas may access other element's datas and methods if requeired
	* Comments: This is useful in many circumstances for example it enables the existence of ComplexData.
	* @see ComplexData
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
	
	/**
	 * Sets each Data it’s attribute name within the element instance.
	 *
	 * Comment: Usefull to the generate and handle the filtercriteria
	 */
	public function assignDatasName(){
		foreach($this as $name => $data) {
			if(($data instanceof Data) && !$data->name()) {
				$data->name($name);
			}
		}
	}
	
	
	
	


	
	
	
	
	//----- Display
	
	/**
	* Default method that will be shown in case no methods have been specified.
	*/
	public function index() {
		$footer = '<h1>'.$this->getClass().'</h1>'
			.'<div id="DOF-create-new" class="DOF section">'.$this->createAction('', array('Create new %s','getClassName')).'</div>'
			.'<div id="DOF-list" class="DOF section">'.$this->showAdmin(null, array(), 'body').'</div>'
		;
		return $this->obtainHtml(__FUNCTION__, null, null, array('footer' => $footer));
	}
	
	public function showCreate($template_file = null, $action = null, $parentClass = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $action);
	}
	
	/* */
	public function showUpdate($template_file = null, $action = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $action);
	}
	
	public function showView($template_file = null, $partOnly = false)
	{
        return $this->obtainHtml(__FUNCTION__, $template_file, null, null, $partOnly);
	}
	
	public function showDelete($template_file = null, $action = null) {
		return $this->obtainHtml(__FUNCTION__, $template_file, $action);
	}
		
    public function callDataMethod($dataName, $method, array $params = array()){
        return call_user_func_array(array($this->{'O'.$dataName}(), $method), $params);
    }
    
    
	public function showSearch($template_file = null, $action = null)
	{
		return $this->obtainHtml(__FUNCTION__, $template_file, $this->encodeURL(array(),'showSearch'), array('footer' => $this->processAdmin(null, 'multi')));
	}

    public function addOnTheFlyAttribute( $attributeName, $attribute = null)
	{
        Main::addOnTheFlyAttribute($this->getClass(),$attributeName,$attribute);
        $this->$attributeName=$attribute;
        if($attribute instanceof Data ){ 
            if( is_array($this->dataAttributes)){ $this->dataAttributes[]=$attributeName; }else{ $this->dataAttributes = $this->attributesTypes(); }
        }
        $this->$attributeName->parent($this);
        
        return $this;
    }
   
    public function addOnTheFlyAttributes()
	{
        foreach ( Main::getOnTheFlyAttributes($this->getClass()) as  $attributeName=>$attribute ){
            $this->$attributeName = clone $attribute;
            if($attribute instanceof Data ){ 
                if( is_array($this->dataAttributes)){
                    $this->dataAttributes[]=$attributeName; 
                }else{
                    $this->dataAttributes = $this->attributesTypes();
                }
            }           
        }
    }    
    
   
    public function clearValues($clearID = false)
	{
        if(!$clearID){
            $id = $this->getId();
        }
        
        foreach($this->dataAttributes() as $dataName) {
			$this->{'O'.$dataName}()->clearValue();
		}
        
        $this->setId($id);
    } 
    
    
    
 	public function showSelect($template_file = null, $action = null, $previewTemplate = null, $sid = null) //@todo delete??????
	{
        if( $previewTemplate && $sid ){ 
            $this->addOnTheFlyAttribute('previewTemplate' , new Datas\Hidden(null,'CUSf', $previewTemplate, '' )    );
            $this->addOnTheFlyAttribute('sid' , new Datas\Hidden(null,'CUSf', $sid, '' )    );
        }
        
        return $this->obtainHtml("showSearch", $template_file, $this->encodeURL(array(),'showSelect'), array('footer' => $this->processSelect(null, 'multi')));
	}       
           
  	public function showAdmin($template_file = null, $add_html = array(), $partOnly = false)
	{
		return $this->obtainHtml(
				"showSearch", 
				$template_file, 
				$this->encodeURL(array(),'showAdmin'), 
				array_merge($add_html, array('footer' => $this->processAdmin(null, 'multi'))), 
				$partOnly
		);
	}       
        
	
	public function obtainHtml($caller_method, $template = null, $action = null, $add_html = array(), $partOnly = false)
	{
		//$caller_method = end(// explode('::',$caller_method));
		if(strpos($caller_method, 'show') === false) {
			$vcsl = $VCSL = $caller_method;
			$with_form = false;
		} else {
			$VCSL = substr($caller_method, strlen('show'));
			$vcsl = strtolower($VCSL);
			$with_form = in_array($vcsl, array('create', 'update', 'search'));
		}
        
		$overwrite_template = Main::$OVERWRITE_LAYOUT_TEMPLATES;
		if(empty($template)) {
			// get default path
			$template_file = $this->templateFilePath($VCSL);
            
            $template = file_exists($template_file)
                ? \phpQuery::newDocumentFileHTML($template_file)
                : '';
		} else if(file_exists($template)){
            $template_file = $template;
            $template = \phpQuery::newDocumentFileHTML($template);
		} else if(Main::hasNoHtmlTags($template)){
            $template_file = $template;
            $template = '';
        } else {
            // is an html snippet
            $template = \phpQuery::newDocument($template);
            $overwrite_template = false;
        }
	
		if(empty($template) OR $overwrite_template) {
            if(empty($template_file)) $template_file = $this->templateFilePath($VCSL);
            
			$dom = \phpQuery::newDocumentFileHTML(Main::$MASTER_TEMPLATE);
            $dom['head']->append(
					$this->getCSS($caller_method, 'html') .
					$this->getJS($caller_method, 'html')
			);
            
			foreach($this->attributesTypes('\\DOF\\Datas\\File') as $fileData){
				if( $fileData->$vcsl() ){
					$enctype = ' enctype="multipart/form-data" ';
					break;
				}
			}
				
			// create and fill file
			$html = '';
			if($with_form) {
				$html.= '<form class="'.$this->htmlClasses($vcsl).'" '
				. ' action="'. htmlentities(@$action ?: $this->encodeURL(Main::$construct_params, 'process'.$VCSL) ) .'" '
				. ' method="post" '
				. @$enctype
				.'>';
			}
			$html.= '<div class="'.$this->htmlClasses().'">';
			foreach($this as $keydata => $data)
			{
				if( $data instanceof Data && $data->hasMethod($vcsl) && $data->$vcsl() )
				{
					$html.= '<div class="'.$data->htmlClasses().'">';
						
					if($with_form) {
						$data_id = 'SimplOn_'.$data->instanceId();
						$dompart = \phpQuery::newDocumentHTML($data->$caller_method());
						// @todo: Document that class input is MANDATORY
						$dompart['.reference']->attr('ref', $data_id);
						$dompart['.input']->attr('id', $data_id);
						$html.= $dompart;
					} else {
						$html.= $data->$caller_method();
					}
						
					$html.= '</div>';
				}
			}
			if($with_form) {
				$html.= '<button name="commit" type="submit">'.($vcsl == 'search' ? 'Search' : 'Save').'</button>'
				.'<button class="SimplOn cancel-form" name="cancel">Cancel</button>'
				.'</div></form>';
			} else {
				$html.= '</div>';
			}
            $dom['body'] = $html;
            
			// save file
			if(!empty($template_file))
                Main::createFile($template_file, $dom.'');
				
		} else {
            // opens file
            $dom = $template;
        }

        /**
         * 
         * @todo change the way HTL is filled instead of cicle triugh the datas 
         * and filling the template cicle trough the template and run elment's 
         * or data's methods as required.
         * 
         * 
         */

        if($with_form && $action) {
            $dom['form.SimplOn.Element.'.$this->getClassName()]->attr('action', $action);
        }
        foreach($dom['.SimplOn.Data.SNL-'.$this->nestingLevel()] as $node) {
            $domNode = pq($node);
            $data = explode(' ', $domNode->attr('class'));
			if(!isset($data[4])){
				$vladu = $domNode.'';
			}
            if($data[4]) {
                $data = $this->{'O'.$data[4]}();
                if( $data instanceof Data && $data->hasMethod($caller_method) )
                    $domNode->html($data->$caller_method($domNode) ?: '');
            }
        }
        
		$dom['body']
            ->prepend(@$add_html['header']?:'')
            ->append(@$add_html['footer']?:'');
		
		/*
		switch($partOnly) {
			case 0:
			case false:
				return $dom;
				
			case 1:
			case true:
			case 'element':
				return $dom[$this->cssSelector()];
				
			default:
				return $dom[$partOnly];
		}*/
		if(!$partOnly) {
			return $dom;
		} else if($partOnly === true OR $partOnly == 'element') {
			return $dom[$this->cssSelector()];
		} else {
			return $dom[$partOnly];
		}
	}
	
	public function getJS($method, $returnFormat = 'array', $compress = false) {
		$class = end(explode('\\',$this->getClass()));
		
		// gets component's js file
		$local_js = JS::getPath("$class.$method.js");
		$a_js = $local_js ? array($local_js) : array();
		
		// adds
		foreach($this->dataAttributes() as $data)
			foreach($this->{'O'.$data}()->getJS($method) as $local_js)
				if($local_js)
					$a_js[] = $local_js;
				
		$a_js = array_unique($a_js);
		sort($a_js);
		
		// includes libs
		$a_js = array_unique(array_merge($a_js, JS::getLibs()));
	
		if($compress) {
			// @todo: compress in one file and return the file path
		}
	
		// converts to remote paths
		$a_js = array_unique(array_map(array('\\DOF\\Main', 'localToRemotePath'), $a_js));
	
		switch($returnFormat) {
			case 'html':
				$html_js = '';
				foreach($a_js as $js) {
					$html_js.= '<script type="text/javascript" src="'.$js.'"></script>'."\n";
				}
				return $html_js;
			default:
			case 'array':
				return $a_js;
		}
	}
	
	public function getCSS($method, $returnFormat = 'array', $compress = false) {
		$class = end(explode('\\',$this->getClass()));
		
		// gets component's css file
		$local_css = CSS::getPath("$class.$method.css");
		$a_css = $local_css ? array($local_css) : array();
		
		// adds
		foreach($this->dataAttributes() as $data)
			foreach($this->{'O'.$data}()->getCSS($method) as $local_css)
				if($local_css)
					$a_css[] = $local_css;
				
		$a_css = array_unique($a_css);
		sort($a_css);
		
		// includes libs
		$a_css = array_unique(array_merge($a_css, CSS::getLibs()));
	
		if($compress) {
			// @todo: compress in one file and return the file path
		}
	
		// converts to remote paths
		$a_css = array_map(array('\\DOF\\Main', 'localToRemotePath'), $a_css);
	
		switch($returnFormat) {
			case 'html':
				$html_css = '';
				foreach($a_css as $css) {
					$html_css.= '<link type="text/css" rel="stylesheet" href="'.$css.'" />'."\n";
				}
				return $html_css;
			default:
			case 'array':
				return $a_css;
		}
	}
	
	function showMultiPicker(){
		return Main::$DEFAULT_RENDERER->table(array($this->toArray()));
	}
			
	
	
	
	
	function getId(){
        //var_dump($this->field_id());
		return $this->{$this->field_id()}();
    }
	
	function setId($id){
        $this->{$this->field_id()}($id);
        return $this;
    }	
	
	
	
//------------------------------- ????	

	function encodeURL(array $construct_params = array(), $method = null, array $method_params = array()) {
        if( empty($construct_params) && $this->getId() ) {
            $construct_params = array( $this->getId() );
        }
		return Main::encodeURL($this->getClass(), $construct_params, $method, $method_params);
	}
	
	public function templateFilePath($show_type, $alternative = '', $short = false, $template_type = 'html') {
        return ($this->parent)
            ? $this->parent->templateFilePath($show_type, $alternative, $short, $template_type)
            : ($short?'':Main::$GENERIC_TEMPLATES_PATH) . '/' . $show_type . '/' .$this->getClassName() . $alternative . '.' .$template_type;;
    }	
	
	/**
	* Returns an array representation of the Element assigning each Data's name
	* as the key and the data's value as the value.
	*
	* @return array
	*/
	public function toArray() {
		foreach($this->dataAttributes() as $dataName ) {
			$ret[$dataName] = $this->$dataName();
		}
		return $ret;
	}	

	/**
	* Applies a method to all the Datas and returns an array containing all the responses.
	*
	* @param string $method must be a method common to all datas
	*/
	public function processData($method)
	{
		$return = array();
		foreach($this->dataAttributes() as $dataName) {
			if(isset($this->$dataName)) {
				$r = $this->$dataName->$method();
				if(isset($r))
					$return[]= $r;
			}
		}
	
		// @todo: verify if it can stay this way
		return $return;
	}
    
    public function nestingLevel($nestingLevel = null) {
        if(isset($nestingLevel)) {
            $this->nestingLevel = $nestingLevel;
            foreach($this->datasWith('parent') as $container) {
                $this->{'O'.$container}()->parent($this);
            }
            return $this;
        } else {
            return $this->nestingLevel;
        }
    }
	
	
	
//------------------------------- Performance	
	
	function dataAttributes() {
		if(!$this->dataAttributes) {
			$this->dataAttributes = $this->attributesTypes();
		}
		return $this->dataAttributes;
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
	

	//vcsrl
	public function datasWith($what){
		foreach($this->dataAttributes() as $data) {
			if( $this->$data->$what() ) {
				$output[] = $data;
			}
		}
        return $output;
	}
}
