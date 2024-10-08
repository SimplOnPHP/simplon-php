<?php
/*
	Copyright © 2024 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP” a Data Oriented Aproach free software development framework: you can redistribute it and/or modify it under the terms of the MIT License.
*/

/*
Elements are the means by which to indicate to the system all data that conforms to it and what to do with it. Each Element represents a set of related data.

In practical terms, Elements are just objects with extended capabilities to handle some common tasks such as:
		- Showing their contents,
		- Storing their contents
		- Finding and retrieving the proper data from a data storage
		- Generating forms to create and update them, etc.

Elements are programmed and used like any other regular object except that, in order to make their special features work, some of their attributes must be SimplON's Data objects.

These SimplON Data objects contain metadata about whether or not an attribute must be shown, retrieved, searchable, etc., and combined with some of the element's methods, allow the Renderers and DataStorage Classes to work with any element, generating and updating the interface and storage automatically and seamlessly as the element changes

@author RSL
 */
class SC_Element-bak extends SC_BaseObject {

	/**
	 * Name of the Data attribute that represents
	 * the ID field of the Element
	 * (ie. SQL primary key's column name).
	 * @todo enable handle of multiple id fields, that should be automatically
	 * detected, as those should be all instances of \SimplOn\Data\Id).
	 * @var string
	 */
	protected $fieldId;

	/**
	 * What DataStorage to use.
	 * 
	 * By default uses the SC_Main->$DATA_STORAGE
	 * 
	 * @var SDS_DataStorage
	 */
	protected $dataStorage;

	/**
	 * Name of the storage associated to this Element
	 * (ie. SQL table name, MongoDB collection name).
	 * 
	 * By default uses the class name
	 * 
	 * @var string
	 */
	protected $storage;

	/**
	 * Criteria to use for searching in the dataStorage.
	 * 
	 * By default builds it using the Data attributes' default criteria and the search flags of each
	 * 
	 * @example (.Data1) AND (Data2 == "Hello")
	 * @var string
	 */
	protected $filterCriteria;

	/**
	 * Criteria to use for selection on the time of deletion in the dataStorage.
	 * 
	 * By default builds it using the Data attributes' default criteria and the delete flags of each
	 * 
	 * @example (.Data1) AND (Data2 == "Hello")
	 * @var string
	 */
	protected $deleteCriteria;


	/**
	 * Elements can be nested by using a special jkind of data called elementContainer if an element is within anotoher that element that contains the curren element can be adressed by the parent attribute.
	 * 
	 * !!!!!!!!!!!!!!!!! NOTE: It's not the same as parent:: THAT is a CALL to the parent class
	 * 
	 * @var SC_Element
	 */
	protected $parent;

	/**
	 * Flag to avoid the system to validate
	 * DataStorage more than once.
	 * @var boolean
	 */
	//public static $storageChecked;

	/**
	 * Array with the exception Messages
	 * 
	 * Used for example when validating for Storing Data
	 */
	protected $exceptionMessages = array();

	/**
	 * Flag to allow deletion without confirmation
	 * 
	 * By default it uses the value of SC_MAIN::$QUICK_DELETE
	 * 
	 * @var string
	 */
	public static $quickDelete;

	/**
	 * Stores a list of Element's attributes of type SD_Data.
	 * 
	 * TODO: Evalute if can be made static
	 * 
	 * @var array containing objects of type SD_Data
	 */
	protected $dataAttributes;

	/** 
	 * Used to store Data atributes added on the fly for all the instances of the class usually links to actions like edit delete or view.
	 * 
	 * TODO: Evalute if can be mixed/integrated with $dataAttributes 
	 * 
	 * */
	static $onTheFlyAttributes = array();


	/**
	 * Array containing the acces rules for the datas and methods.
	 * 
	 * 	
	 * Example:
		admin' => array('*'=>'allow'),
			'Asesor' => array(
				'View'=>array(
					'updateAction'=>'viwableWhen_id_=_CurrentUserId',
					'deleteAction'=>'hide',
				),
				'Search'=>'allow',
				'Update'=>array(
					'asesor'=>'fixed_CurrentUserId',
				),
				'Create'=>array(
					'asesor'=>'fixed_CurrentUserId',
				),
				'Delete'=>'deny',
				),
			'*' => array('showView'=>'allow','*'=>'deny')
	 * 
	 */
	static $permissions;

	/**
	 * Determines the kind of permissions that are given to the attributes.
	 * 
	 * Depending on the $methodsFamilies array each method is assigned a DatasMode
	 * View, Update, Create, Delete, Search or the method it self. This way the permissions don't have to be asigned for each method but for DatasMode and special methods.
	 * 
	 */
	protected $datasMode;

	protected $Name;

	protected $nameInParent;

	/**
	 * How to order the elements when listing them
	 * 
	 * 
	 */
	protected $OrderCriteria = 'SimplOn_id desc';

	/**
	 * Keeps the relationship between the methods and the datasMode so that is simplier to write the permissions.
	 */
	static $methodsFamilies = array(
		'showView'=>'View',
		'showSearch'=>'View',
		'showAdmin'=>'View',
		'showList'=>'View',
		'showEmbed'=>'View',
		'showEmbededStrip'=>'View',
		'processReport'=>'View',
		
		'showUpdate'=>'Update',
		'showUpdateSelect'=>'Update',
		'processUpdate'=>'Update',
		'processUpdateJSon'=>'Update',
		'processUpdateSelect'=>'Update',
	
		'showCreate'=>'Create',
		'showContinusCreate'=>'Create',
		'showCreateAppend'=>'Create',
		'showCreateSelect'=>'Create',
		'processContinusCreate'=>'Create',
		'processCreate'=>'Create',
		'processCreateAppend'=>'Create',
		'processCreateJSon'=>'Create',
		'processCreateSelect'=>'Create',
		'processCreprocessUpdateate'=>'Create',
	
		'showDelete'=>'Delete',
		'processDeletePage'=>'Delete',
	
		'showSearch'=>'Search',
		'showSearchSelect'=>'Search',
		'processAdmin'=>'Search',
		'processSearch'=>'Search',	

		'logout'=>'logout',
		'showLogin'=>'showLogin',
		'processLogin'=>'processLogin',
		
		/** 
		 * View Update Create Delete Search
		 * TODO:: Still to be asigned a DataMode methods
		processSelect
		showEmbededAppendInput
		showEmbededSearchInput
		showEmbededUpdateInput
		showMultiPicker
		showReport
		showSelect
		showSelectAppend
		processData
		*/
	);



	static $formMethods = ['showSearch','showCreate','showUpdate'];

	/**
	 * Flag to indicate that the whole constructor has finished. Used to avoid cicles with SC_ElementBased PERMISSIONS like SE_User but can be used to prevent other kind of infite loops and problems.
	 * 
	*/
	protected $fullyset = false;


	/**
	 * - Calls user defined constructor.
	 * - Adds default Element's actions.
	 * - Validates DataStorages.
	 * - Fills its Datas' values if possible (requires a valid ID or array of values).
	 * - Fills some of its Datas' meta-datas (parent, names).
	 * @param mixed $id_or_array ID of the Element or array of Element's Datas values.
	 * @param DataStorage $specialDataStorage DataStorage to use in uncommon cases.
	 */
	public function __construct($id_or_array = null, $storage = null, $specialDataStorage = null) {

		//On heirs put here the asignation of SimplOndata and attributes
		if ($storage)
		$this->storage($storage);
		else
		$this->storage($this->getClass());

		//Assings the storage element for the SimplOnelement. (a global one : or a particular one)
		if (!$specialDataStorage) {
			$this->dataStorage = SC_Main::dataStorage();
		} else {
			$this->dataStorage = &$specialDataStorage;
		}

		//Call to "construct" function
		$this->construct($id_or_array, $storage);

		if (!self::$quickDelete)
			self::$quickDelete = SC_Main::$QUICK_DELETE;

		if (!isset($this->viewAction))
			$this->viewAction = new SD_viewAction('', array('<img     src="/Imgs/viewIcon.webp" atl="Edit">'));
		else 
			$this->viewAction = new SD_emptyAction('', array(''));

		if (!isset($this->createAction))
		$this->createAction = new SD_CreateAction('', array('<img     src="/Imgs/addIcon.webp" atl="Create">'));
	
		if (!isset($this->updateAction))
			$this->updateAction = new SD_UpdateAction('', array('<img     src="/Imgs/editIcon.webp" atl="Edit">'));
		else 
			$this->updateAction = new SD_emptyAction('', array(''));

		if (!isset($this->deleteAction))
		$this->deleteAction = new SD_DeleteAction('', array('<img     src="/Imgs/deleteIcon.webp" atl="Delete">'));
		if (!isset($this->adminAction))
		$this->adminAction = new SD_AdminAction('', array('Admin'));

		if( !isset($this->selectAction) )
		$this->selectAction = new SD_SelectAction('', array('Select'));

		if( !isset($this->selectSearchAction) )
		$this->selectSearchAction = new SD_SelectSearchAction('', array('Select'));

		if( !isset($this->selectAppendAction) )
		$this->selectAppendAction = new SD_SelectAppendAction('', array('Select'));

		$this->multiSelectAction = new SD_DeleteAction('', array('Select'));
		//Load the attributes on the fly
		$this->addDatas();

		$this->assignDatasName();

		//user_error($this->{$this->fieldId()}());
		// Tells the SimplOndata whose thier "container" in case any of it has context dependent info or functions.
		$this->assignAsDatasParent();

		//checking if there is already a dataStorage and storage for this element
		if(!($this instanceof SE_Interface)){
			$this->dataStorage->ensureElementStorage($this);
		}

		if (is_array($id_or_array)) {
			$this->fillFromArray($id_or_array);
		} else if ($id_or_array) {
			//if there is a storage and an ID it fills the element with the proper info.
			$this->fillFromDSById($id_or_array);
		} else {
			//$this->fillFromRequest();
		}

		$this->fullyset = true;
	}

	function attributesOfClass($type){
		$ret = array();
		foreach ($this as $property => $value) {
			if($value instanceof $type){
				$ret[] = $property;
			}
		}

		return $ret;
	}



	/**
	 * User defined constructor, called within {@link __constructor()},
	 * useful to declare specific Data attributes.
	 * @param mixed $id_or_array ID of the Element or array of Element's Datas values.
	 * @param \SimplOn\DataStorages\DataStorage $specialDataStorage DataStorage to use in uncommon cases.
	 */
	public function construct() {
	}

	/**
	 *
	 * @param type $val
	 * @return type
	 */
	public function fieldId($val = null) {
		if (!$this->fieldId) {
			$this->fieldId = $this->attributesTypes('SD_Id');
			$this->fieldId = $this->fieldId[0];
		}

		if ($val) {
			$this->fieldId = $val;
		} else {
			return $this->fieldId;
		}
	}

	function parent(&$parent = null) {
		if (!$parent) {
			return $this->parent;
		} else {
			$this->parent = $parent;
		}
	}

	function elementContainerName(){
		if( $this->nameInParent() || $this->nameInParent() === 0 ){ return $this->nameInParent(); }else{ return 'placeHolderName'; }
		
	}



	/**
	 * Allows some simplicity for coding and declarations, auto makes getters and setters so that any Data’s attribute value data->val() can be transparently accessed as a normal element attribute by Element->data(); and load all other BasicObject SimplON functionality.
	 * 
	 * @see SimplOn.BaseObject::__call()
	 */
	public function __call($name, $arguments) {
		//if( !is_string(SC_Main::$PERMISSIONS) && (SC_Main::$PERMISSIONS !== $this)){SC_Main::$PERMISSIONS->setValuesByPermissions($this, $name );}
		//ensure there is a response to ->id() even if ID it's not defined as ->id ex: ->name in user
		if ($name == 'id' && !$this->id){
			if ($arguments[0]!==null){
				$this->{$this->fieldId()}($arguments[0]);
			}else{
				return $this->{$this->fieldId()}();
			}
		}

		if (@$this->$name instanceof SD_Data) {
			if ($arguments) {
				return $this->$name->val($arguments[0]);
			} else {
				return $this->$name->val();
			}
		} else if(substr($name, 0, 4) === "show"){
			$redender = $GLOBALS['redender'];
			// return $redender->render($this,$name);
			array_unshift($arguments,$name);
			array_unshift($arguments,$this);
			return call_user_func_array(array($redender, 'render'), $arguments);
		} else {

			$letter = substr($name, 0, 1);
			$Xname = substr($name, 1);

			if (($letter == strtoupper($letter)) && (@$this->$Xname instanceof SD_Data)) {
				switch ($letter) {
				case 'O': //Get the object / Change the object
					if ($arguments) {
						//$this->$Xname->val($arguments[0]);
						$this->$Xname = $arguments[0];
					} else {
						return $this->$Xname;
					}
					break;
				case 'F': //Fix value
					if ($arguments) {
						$this->$Xname->fixValue($arguments[0]);
					} else {
						return $this->$Xname;
					}
					break;
				case 'L':
					if ($arguments) {
						$this->$Xname->val($arguments[0]);
					} else {
						return $this->$Xname->label();
					}
					break;
				default:
					throw new \Exception('Letter ' . $letter . ' not recognized!');
				}
			} else {
				return parent::__call($name, $arguments);
			}
		}
	}

	function htmlClasses($append = '') {
		return 'SimplOn Element ' . $this->getClass() . ' ' . $append;
	}

	function cssSelector($append = '') {
		return '.SimplOn.Element.' . $this->getClass() . $append;
	}

	// -- SimplON key methods
	/**
	 * Assigns to each Data attribute it's corresponding value
	 * from an array of values.
	 *
	 * @param array $array_of_data
	 */
	public function fillFromArray(&$array_of_data) {
		$filled = 0;
		if (is_array($array_of_data)) {
			foreach ($array_of_data as $dataName => $value) {
				if (isset($this->$dataName) && ($this->$dataName instanceof SD_Data)) {	
				try {
						$this->$dataName($value);
						$filled++;
					} catch (SC_DataValidationException  $ve) {
						$this->excetionsMessages[$dataName] = array($ve->getMessage());
					}
				}
			}
		}
		return $filled;
	}

	public function requiredCheck($array = array()) {

		$requiredDatas = $this->datasWith('required');

		foreach ($requiredDatas as $requiredData) {
			if (!$this->$requiredData->val() && ($this->$requiredData->required() && !@$this->$requiredData->autoIncrement())) {
				$array[$requiredData][] = $this->$requiredData->validationRequired();
			}
		}

		return $array;
	}

	/**
	 *
	 * NOTE: This method is not a simple redirection to $this->fillFromArray($_REQUEST) because the file upload requeires the $_FILES array
	 * Thus the redirection from fillFromRequest to fillFromArray is made at the SimplOnData and there for any SimplOnData that needs to
	 * distinguish between both can do it.
	 *
	 */
	public function fillFromRequest() {
		if ($_REQUEST) {	
			$this->fillFromArray($_REQUEST);
			return;
		} else {
			return false;
		}
	}

	public function fillFromPost() {
		if ($_POST) {
			return $this->fillFromArray($_POST);
		} else {
			return false;
		}
	}

	//------------Data Storage
	/**
	 * Retrieves the element's Datas values from the DataSotarage,
	 * using the recived Id or the element's id if no id is provided.
	 *
	 * @param mixed $id the id of the element whose data we whant to read from de DS
	 * @throws SC_Exception
	 *
	 * @todo: in  arrays format ????
	 */
	public function fillFromDSById($id = null) {
		if (isset($id)) {
			$this->setId($id);
		}
		if ($this->getId() || $this->getId() === 0) {
			$dataArray = $this->dataStorage->readElement($this);
			$this->fillFromArray($dataArray);
		} else {
			throw new SC_Exception('The object of class: ' . $this->getClass() . " has no id so it can't be filled using method fillElementById");
		}
	}

	public function save() {
		return $this->getId() ? $this->update() : $this->create();
	}


	public function update() {
		$this->processData('preUpdate');
		$return = $this->dataStorage->updateElement($this);
		$this->processData('postUpdate');
		return $return;
	}

	public function delete() {
		$this->processData('preDelete');
		$return = $this->dataStorage->deleteElement($this);
		$this->processData('postDelete');
		return $return;
	}

	/* @todo determine if this method is neceary or not
	 * updateInDS // este debe ser automatico desde el save si se tiene id se genera
	 */
	function validateForDB() {
		@$exceptionMessages = $this->requiredCheck($this->excetionsMessages);
		if (!empty($excetionsMessages)) {
			throw new SC_ElementValidationException($excetionsMessages);
		}
	}



	/** Stores in the DataStorage the Element Values */
	public function create() {
		$this->processData('preCreate');

		$id = $this->dataStorage->createElement($this);
		$this->setId($id);

		$this->processData('postCreate');

		return $id !== false;
	}
	/** Calls Create and makes th JSon for the renderer to do the next Step */
	function processCreateJSon($nextStep = null) {
					
		try {
			$this->fillFromRequest();
			$this->validateForDB();
			$shouldContinue = true; // Control variable		
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}


		try {
			if ($this->create()) {
					$data = array(array(
					'func' => 'redirectNextStep',
					'args' => array($nextStep)
					));
					$return = array(
					'status' => true,
					'type' => 'commands',
					'data' => $data
					);
					$return = json_encode($return);
					return $return;
			} else {
				// @todo: error handling
				user_error('Cannot create in DS!', E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}

	function showCreate($template = null, $output='AE_fullPage', $messages=null){
		$redender = $GLOBALS['redender'];
		
		$action = $redender->action($this,'processCreate');
		return $redender->render($this,'showCreate', 'AE_fullPage',$template, $action);
	}

	function processCreate(){
		$redender = $GLOBALS['redender'];
		$redender->setMessage($this->Name().' guardado correctamente');
		$nextStep = $redender->action($this,'showAdmin','id');
		return $this->processCreateJSon($nextStep);
	}


	function showContinusCreate($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		$redender->setMessage($this->Name().' guardado correctamente');
		$action = $redender->action($this,'processContinusCreate');
		return $redender->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
		// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	}	
	function processContinusCreate($nextStep = null) {
		$redender = $GLOBALS['redender'];
		$nextStep = $redender->action($this,'showContinusCreate','id');
		return $this->processCreateJSon($nextStep);
	}

	function showCreateSelect($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		$action = $redender->action($this,'processCreateSelect');
		return $redender->render($this,'showCreate', 'AE_basicPage',$template, $action);
		//return $redender->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
		// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	}
	
	function showCreateAppend($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		//$action = $redender->action($this,'processCreateSelect');
		$action = $redender->action($this,'processCreateAppend');
		return $redender->render($this,'showCreate', 'AE_basicPage',$template, $action);
		//return $redender->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
		// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	}

	function processCreateAppend($nextStep = null) {
		try {
			$this->fillFromRequest();
			$this->validateForDB();
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}
		try {
			if ($this->create()) {
					return $this->makeAppendSelection();
			} else {
				// @todo: error handling
				user_error('Cannot create in DS!', E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}



	function processCreateSelect($nextStep = null) {
		try {
			$this->fillFromRequest();
			$this->validateForDB();
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}
		try {
			if ($this->create()) {
					return $this->makeSelection();
			} else {
				// @todo: error handling
				user_error('Cannot create in DS!', E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}

	function showSearch($template = NULL, $output = false, $action = NULL, $nextStep = NULL){
		$redender = $GLOBALS['redender'];
		$action = $redender->action($this,$action);
		return $redender->render($this,'showSearch',$template, $output, $action,$nextStep,true);
	}

	// function showUpdate($template = null, $partOnly = false,$action=null,$nextStep=null){
	// 	$redender = $GLOBALS['redender'];
	// 	$action = $redender->action($this,'processUpdate');
	// 	return $redender->render($this,'showUpdate',$template, $partOnly ,$action ,$nextStep);
	// }
	function processCreprocessUpdateate(){
		$redender = $GLOBALS['redender'];
		$redender->setMessage($this->Name().' actualizado correctamente');
		$nextStep = $redender->action($this,'showAdmin','id');

		return $this->processUpdateJSon($nextStep);
	}


	
	function showEmbededUpdateInput($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		//$ret = $redender->render($this,'showEmbededUpdateInput',$template,$partOnly,$action);
		$ret = $redender->render($this,'showEmbededUpdateInput', null,$template, $action);
		$canEdit = true;
		if($canEdit == false){
			//$ret = \phpQuery::newDocumentHTML($ret);
			//$ret['.SimplOn.actions .UpdateSelect']->remove();
			//$ret = $ret->htmlOuter();
		}
		return $ret;
	}	

	function showEmbededAppendInput($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		//$ret = $redender->render($this,'showEmbededUpdateInput',$template,$partOnly,$action);

		$ret = $redender->render($this,'showEmbededAppendInput', null,$template, $action);
		$canEdit = true;
		
		if($canEdit == false){
			//$ret = \phpQuery::newDocumentHTML($ret);
			//$ret['.SimplOn.actions .UpdateSelect']->remove();
			//$ret = $ret->htmlOuter();
		}
		return $ret;
	}

	function showEmbededSearchInput($template = null, $partOnly = false,$action=null,$nextStep=null){
		$redender = $GLOBALS['redender'];
		//$ret = $redender->render($this,'showEmbededUpdateInput',$template,$partOnly,$action);
		$ret = $redender->render($this,'showEmbededSearchInput', null,$template, $action);
		$canEdit = false;
		if($canEdit == false){
			//$ret = \phpQuery::newDocumentHTML($ret);
			//$ret['.SimplOn.actions .UpdateSelect']->remove();
			//$ret = $ret->htmlOuter();
		}
		return $ret;
	}
	

	function showUpdate($template = null, $output='AE_fullPage', $messages=null){
		$redender = $GLOBALS['redender'];
		
		$action = $redender->action($this,'processUpdate');
		return $redender->render($this,'showUpdate', $output,$template, $action);
	}

	function showUpdateSelect($template = null, $output='AE_basicPage', $messages=null){
		$redender = $GLOBALS['redender'];
		
		$action = $redender->action($this,'processUpdateSelect');
		return $redender->render($this,'showUpdate', $output,$template, $action);
	}


	function processUpdateSelect($nextStep = null) {
		try {
			$this->fillFromRequest();
			$this->validateForDB();
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}
		try {
			if ($this->update()) {		
					return $this->makeSelection();
			} else {
				// @todo: error handling
				user_error('Cannot create in DS!', E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}




	function processUpdate(){
		$redender = $GLOBALS['redender'];
		$redender->setMessage($this->Name().' actualizado correctamente');
		$nextStep = $redender->action($this,'showAdmin','id');
		return $this->processUpdateJSon($nextStep);
	}

	//function processUpdate($short_template=null, $sid=null){
	function processUpdateJSon($nextStep = null) {
		try {
			$this->fillFromRequest();
			$this->validateForDB();	
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}

		try {
			if ($this->update()) {
					$data = array(array(
					'func' => 'redirectNextStep',
					'args' => array($nextStep)
					));
					$return = array(
					'status' => true,
					'type' => 'commands',
					'data' => $data
					);
					$return = json_encode($return);
					return $return;
			} else {
				// @todo: error handling
				user_error('Cannot create in DS!', E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}

	function showDelete($template = null, $output='AE_fullPage', $messages=null){
		/** @var SR_main2 $redender */
		$redender = $GLOBALS['redender'];
		$action = $redender->action($this,'processDeletePage');
		return $redender->render($this,'showDelete', 'AE_fullPage',$template, $action);
	}

	function processDeletePage($nextStep = null, $format = 'json') {
		//esta pendienmte el tema de nexstep o el json y las modalidades de delete
		/** @var SR_main2 $redender */
		$redender = $GLOBALS['redender'];
		$redender->setMessage($this->Name().' borrado correctamente');
		if(!$nextStep){ $nextStep = $redender->action($this,'showAdmin','id'); }
		$data = array(
			array(
			'func' => 'removeHtml',
			),
			array(
			'func' => 'closeLightbox',
			),
			array(
				'func' => 'redirectNextStep',
				'args' => array($nextStep)
			)
		);

		if ($this->delete()) {
			$return = array(
				'status' => true,
				'type' => 'commands',
				'data' => $data
			);	
			header('Content-type: application/json');
			echo json_encode($return);
		} else {
			// @todo: error handling
			user_error('Cannot delete in DS!', E_USER_ERROR);
		}
	}

	function processSearch() {
		try {
			//$search = new SE_Search(array($this->getClass()));

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);
			
			$params = [];
			// $params = ['simplonCols'=>'show'];
			$results = new SD_Table('Results',$params,$results);

			/** @var SR_main2 $redender */
			$redender = $GLOBALS['redender'];
			return $redender->renderData($results,'showView',null,1);
			// $results = $search->getResults($this->toArray());
			// foreach($results as $row) 
			//  	$ret .= var_export($row, true);
			// return $ret;
		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}

	function viewVal(){
		return $this->val();
	}

	function adminTable( $position = null, $limit = null) {
		try {
			//$search = new SE_Search(array($this->getClass()));
			if(is_object(SC_Main::$PERMISSIONS)){SC_Main::$PERMISSIONS->setValuesByPermissions($this, 'View');}

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);

			$columnsToAdd = [
				//'Suma'=>new SD_Sum('Suma',array('num1','num2')),
				'&nbsp;'=>new SD_Concat('&nbsp;',array('&nbsp;','viewAction','updateAction','deleteAction'))
			];

			// $params = [];
			// $rowsToAdd=[
			// 	['title'=>'Total','function'=>'array_sum','rows'=>[1,2,3] ]
			// ];
			$params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'keys','columnsToAdd'=>$columnsToAdd,];
			// $params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'none','columnsToAdd'=>$columnsToAdd];
			// $params = ['simplonCols'=>'show'];


			if( !empty($results) ){
				$results = new SD_Table('Results',$params,$results);
			}else{
				$results = new SD_Text('Results','VL','No hay datos que mostrar');
			}


			/** @var SR_main2 $redender */
			$redender = $GLOBALS['redender'];
			return $redender->renderData($results,'showView',null,1);

		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}

	function selectTable() {
		try {
			$search = new SE_Search(array($this->getClass()));

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);

			$params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'keys','columnsToAdd'=>array('selectAction')];

			if( !empty($results) ){
				$results = new SD_Table('Results',$params,$results);
			}else{
				$results = new SD_Text('Results','VL','No hay datos que mostrar');
			}

			/** @var SR_main2 $redender */
			$redender = $GLOBALS['redender'];
			return $redender->renderData($results,'showView',null,1);

		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}
	
	function selectAppendTable() {
		try {
			$search = new SE_Search(array($this->getClass()));

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);

			$params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'keys','columnsToAdd'=>array('selectAppendAction')];
			if( !empty($results) ){
				$results = new SD_Table('Results',$params,$results);
			}else{
				$results = new SD_Text('Results','VL','No hay datos que mostrar');
			}

			/** @var SR_main2 $redender */
			$redender = $GLOBALS['redender'];
			return $redender->renderData($results,'showView',null,1);

		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}

	function selectSearchTable() {
		try {
			$search = new SE_Search(array($this->getClass()));

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);

			$params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'keys','columnsToAdd'=>array('selectSearchAction')];

			if( !empty($results) ){
				$results = new SD_Table('Results',$params,$results);
			}else{
				$results = new SD_Text('Results','VL','No hay datos que mostrar');
			}

			/** @var SR_main2 $redender */
			$redender = $GLOBALS['redender'];
			return $redender->renderData($results,'showView',null,1);

		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}	

	/**
	 * 
	 *  Obtain an array with all results from Element's table to be used in SD_ElementContainer. 
	 */
	function Elements(){
		$search = new SE_Search(array($this->getClass()));
		$colums = array_merge($this->datasWith("embeded"));

		return $search->getResults($this->toArray());
	} 
	
	function processSelect() {
		$this->fillFromRequest();
		$search = new SE_Search(array($this->getClass()));
		// $colums = array_merge( $this->datasWith("list"), array("selectAction","parentClass") );
		//@todo do not add selectAction here but just include it in the listing using VCRSL when adding it on the fly
		$colums = array_merge($this->datasWith("list"), array("selectAction"));
		return $search->processSearch($this->toArray(), $colums);
	}

	/**
	 * 
	 * processReport
	 * 
	 * Turn on the search flag from all datas to display them in search form and 
	 * add 4 controlSelect one to list the results, one to count the results, one 
	 * to group results and one to sum the results. 
	 * When the form is sent, processReport analyzes data sent and verifies that 
	 * data will be listed and finally send data to "Report" class which will 
	 * return results from the database to display them in showReport.
	 * 
	 * @param int $start
	 * @param int $limit
	 * @return string
	 */
	function processReport($start, $limit = null){
		$this->changeCurrentFlags(null,'search');
		$labelsNames = $this->getLabelsAndNames();
		if ($start < 1) {
			$start = 1;
		}
		$position = ($start - 1) * $limit;
		///RSL 2022 comentado sin saber como funcionaba para tener el reporte sencillo funcionando
		// $this->addData('SimplOn_list_datas', new SD_ControlSelect('List', $labelsNames));
		// $this->addData('SimplOn_count', new SD_ControlSelect('Count', $labelsNames));
		// $this->addData('SimplOn_group', new SD_ControlSelect('Group', $labelsNames));
		// $this->addData('SimplOn_sum', new SD_ControlSelect('Sum', $labelsNames));
		$this->assignDatasName(); 
		$this->fillFromRequest();
		///RSL 2022 comentado sin saber como funcionaba para tener el reporte sencillo funcionando
		// $listDatas = $this->SimplOn_list_datas->val();
		// $count = $this->SimplOn_count->val();
		// $group = $this->SimplOn_group->val();
		// $sum = $this->SimplOn_sum->val();
		// if ($listDatas !== null) {
		// 	$this->changeCurrentFlags(null,'list',false);
		// 	$this->changeCurrentFlags($listDatas,'list');
		// }
		// if(isset($count)) $this->SimplOn_count->addCount($count);
		// if(isset($sum)) $this->SimplOn_sum->addSum($sum);
		$columns = array_merge($this->datasWith('list'));
		
		///RSL 2022 comentado sin saber como funcionaba para tener el reporte sencillo funcionando
		//$process = new Report(array($this->getClass()), null, null, $group);
		$process = new SE_Report(array($this->getClass()), null, null, null);
		
		$tableReport = $process->processRep($this->toArray(), $columns, $position, $limit); 
		$totalRecords = $process->total;
		$links = $this->makePaging($start, $limit, $totalRecords);
		return $tableReport.$links;
	}

	function showAdmin($template = null, $output='AE_fullPage', $messages=null){
		$redender = $GLOBALS['redender'];
		$this->fillFromRequest();
		$mode = self::$methodsFamilies[__FUNCTION__];

		if($mode && is_object(SC_Main::$PERMISSIONS) ){
			SC_Main::$PERMISSIONS->setValuesByPermissions($this, $mode);
		}

		$action = $redender->action($this,'processAdmin');
		return $redender->render($this,'showAdmin', 'AE_fullPage',$template, $action);
	}

	/**
	 * 
	 * processAdmin
	 * 
	 * When the form is sent, processAdmin analyzes data sent and verifies that 
	 * data will be listed and finally send data to "Search" class which will 
	 * return results from the database to display them in showAdmin.
	 * 
	 * @param int $start
	 * @param int $limit
	 * @return string
	 */
	function processAdmin($start = 1, $limit = null) {
		$redender = $GLOBALS['redender'];	
		if ($start < 1) {
			$start = 1;
		}			
		$position = ($start - 1) * $limit;
		$this->fillFromRequest();
		$search = new SE_Search(array($this->getClass()));
		//$admin = $redender->encodeURL(array(), 'showAdmin');

		$colums = array_merge($this->datasWith("list"), array( "viewAction", "updateAction","deleteAction"));

		$tableAdmin = $search->processSearch($this->toArray(), $colums, $position, $limit);

		$totalRecords = $search->total;
		$links = $this->makePaging($start, $limit, $totalRecords);
		return $tableAdmin.$links;
	}

	/**
	 * 
	 * makePaging
	 * 
	 * Create links which will be used in showAdmin and showReport when the results
	 * exceed the limit established and as needed show results in more than one
	 * page. 
	 *   
	 * 
	 * @param int $start
	 * @param int $limit
	 * @param int $totalRecords
	 * @return string
	 */
	function makePaging($start, $limit, $totalRecords){
		$links = "";
		$totalElements = $totalRecords;
		$division = $limit ? ceil($totalElements / $limit) : 0;
		if ($division > 1) {
			for ($i = 1; $i <= $division; $i++) {
				$links.= "<a class = 'SimplOn_pag' href=\"/$i/$limit\">$i<\a> ";
			}
			$next = $start + 1;
			$prev = $start - 1;
			if ($start > '1') {
				$links = "<a class = 'SimplOn_pag' href=\"/$prev/$limit\">Prev<\a> " . $links;
			}
			if ($next < $i) {
				$links.= "<a class = 'SimplOn_pag' href=\"/$next/$limit\">Next<\a> ";
			}
		}
		return $links;
	}

	/**
	 * 
	 * getLabelsAndNames
	 * 
	 * Generate an array where keys are the label from each data and  values are
	 * the name of each data.
	 * 
	 * @return array
	 */
	function getLabelsAndNames(){
		$data = 'SD_ata';
		$numId = 'SD_NumericId';
		$labels = array();
		$dataNames = array();
		foreach ($this as $name => $dataObj) {
			if ($dataObj instanceof $data) {
				$valFetch = $this->{'O' . $name}()->fetch();
				if($valFetch === true) {
					if ($dataObj->label() !== '' && $dataObj->label() !== null) {
						$labels[] = $dataObj->label();
					}
					if (!($dataObj instanceof $numId)) {
						$dataNames[] = $name;
					}
				}
			}
		}
		$labelsAndNames = array_combine($labels, $dataNames);
		return $labelsAndNames;
	}

	/**
	 * storeAllFlags 
	 * 
	 * Stores all flags default data in an array
	 * 
	 * @return array
	 */
	function storeAllFlags() {
		$type = 'SD_NumericId';
		$flagsName = array();
		$flagsStatus = array();
		foreach ($this->dataAttributes() as $dataName) {
			$valFetch = $this->{'O' . $dataName}()->fetch();
			if($valFetch === true){
				if (!($this->{'O' . $dataName}() instanceof $type)) {
					$status = $this->{'O' . $dataName}()->search();
					$flagsName[] = $dataName;
					$flagsStatus[] = $status;
				}
			}    
		}
		$flagStock = array_combine($flagsName, $flagsStatus);
		return $flagStock;
	}

	/**
	 * changeCurrentFlags
	 * 
	 * Change the flag indicated by the parameter 'flag' of all data 
	 * in the array 'chosenDatas' actually providing the parameter 'status' 
	 * flag change.
	 * 
	 * @param array $chosenDatas
	 * @param string $flag
	 * @param string $status
	 */

	function changeCurrentFlags($chosenDatas = array(), $flag, $status = true) {
		$type = 'SD_NumericId';
		if (isset($chosenDatas)) {
			foreach ($chosenDatas as $data){
				if(method_exists($this,$flag))
				$this->{'O' . $data}()->$flag($status);
			}
		} else {
			foreach ($this->dataAttributes() as $dataName) {
				$valFetch = $this->{'O' . $dataName}()->fetch();
				if( $valFetch === true){
					if (!($this->{'O' . $dataName}() instanceof $type)) {
						if(method_exists($this,$flag))
							$this->{'O' . $dataName}()->$flag($status);
					}
				}    
			}
		}
	}

	/**
	 * restoreAllFlags
	 *
	 * Restore the flags of all the data with their original value in the Search view.
	 * 
	 * @param array $flagStock
	 */
	function restoreAllFlags($flagStock = array()) {
		foreach ($flagStock as $dataName => $value) {
			$this->{'O' . $dataName}()->search($value);
		}
	}

	public function defaultFilterCriteria($operator = 'AND') {
		//@todo: make a function that returns the data with a specific VCRSL flag ON or OFF
		$searchables = array();
		foreach ($this->dataAttributes() as $dataName) {
			///RSL 2022 agrego && $this->{'O' . $dataName}()->filterCriteria()!=='none' para poder tener datos indexados que no generen filter criteria directo al ponerles filterCriteria = 'none'
			if ($this->{'O' . $dataName}()->search() && $this->{'O' . $dataName}()->fetch() && ($this->$dataName() !== null && $this->$dataName() !== '' && $this->{'O' . $dataName}()->filterCriteria()!=='' )) {
				$searchables[] = ' (.' . $dataName . ') ';
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
		if (isset($filterCriteria))
			$this->filterCriteria = $filterCriteria;
		else {
			//REMOVED so it adapts on every run if necesary
			if (!isset($this->filterCriteria))
				$this->filterCriteria = $this->defaultFilterCriteria();
			//$filterCriteria = $this->filterCriteria;

			$patterns = array();
			$subs = array();
			foreach ($this->dataAttributes() as $dataName) {
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				$fc = $this->{'O' . $dataName}()->filterCriteria();
	
		
				if (!empty($fc)) {
					$patterns[] = '/(\.' . $dataName . ')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
					$subs[] = $fc;
				}
			}
/**
	TODO :: FIX REGEXS SO THAT THERE CAN BE TWO DATAS WITH A SHARED SUB STRING in the name in the same element EX: cuenta and cuentaHabiente
*/
			//$ret = preg_replace($patterns, $subs, $filterCriteria);
			return preg_replace($patterns, $subs, $this->filterCriteria);
		}
	}

	public function deleteCriteria($deleteCriteria = null) {
		if (isset($deleteCriteria))
		$this->deleteCriteria = $deleteCriteria;
		else {

			//REMOVED so it adapts on every run if necesary
			if (!isset($this->deleteCriteria))
			$this->deleteCriteria = $this->defaultDeleteCriteria();

			//$filterCriteria = $this->filterCriteria;

			$patterns = array();
			$subs = array();
			foreach ($this->dataAttributes() as $dataName) {
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				$fc = $this->{'O' . $dataName}()->filterCriteria();
				if (!empty($fc)) {
					$patterns[] = '/(\.' . $dataName . ')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
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
		foreach ($this->dataAttributes() as $dataName) {
			if ($this->{'O' . $dataName}()->fetch() && ($this->$dataName() !== null && $this->$dataName() !== '')) {
				$searchables[] = ' (.' . $dataName . ') ';
			}
		}
		return implode($operator, $searchables);
	}

	/**
	 * Sets the current instance the as "logical" parent of the Datas.
	 * Thus the datas may access other element's datas and methods if required
	 * Comments: This is useful in many circumstances for example it enables the existence of ComplexData.
	 * @see ComplexData
	 */
	public function assignAsDatasParent(&$parent = null) {
		if (!isset($parent))
		$parent = $this;

		foreach ($this as $data) {
			if ($data instanceof SD_Data) {
				if ($data->hasMethod('parent') && empty($data->parent()) ) {
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
	public function assignDatasName() {
		foreach ($this as $name => $data) {
			if (($data instanceof SD_Data) && !$data->name()) {
				$data->name($name);
			}
		}
	}

	public function showEmbededStrip(){
		/** @var string[] $embeadedDatas */
		$embeadedDatas = $this->datasWith("embeded");
		$ret = '';
		$this->fillFromDSById();
		foreach($embeadedDatas as $embeadedData){
			$ret .= $this->{$embeadedData}->showEmbededStrip().' ';
		}
		return trim($ret);
	} 

	//RSL 2022 #todo 
	public function addData($attributeName, SD_Data $attribute = null) {
		///RSL 2022 fix de error que tronaba en ciertas lecturas por falta name en el atributo
		$attribute->name($attributeName);
		self::$onTheFlyAttributes[$attributeName]=$attribute;
		$this->$attributeName = $attribute;
		if ($attribute instanceof SD_Data) {
			if (is_array($this->dataAttributes)) {
				if(!in_array($attributeName, $this->dataAttributes)){
					$this->dataAttributes[] = $attributeName;
				}
			} else {
				$this->dataAttributes = $this->attributesTypes();
			}
		}
		$this->$attributeName->parent($this);

		return $this;
	}

	public function removeData($attributeName) {
		if ($attribute instanceof SD_Data) {
			unset($obj->$attributeName);
			unset(self::$onTheFlyAttributes[$attributeName]);
			SC_Main::removeData($this->getClass(), $attributeName);
			$this->dataAttributes = $this->attributesTypes();
		}
		return $this;
	}

	public function addDatas() {
		foreach (self::$onTheFlyAttributes as $attributeName => $attribute) {
			$this->$attributeName = clone $attribute;
			if ($attribute instanceof SD_Data) {
				if (is_array($this->dataAttributes)) {
					$this->dataAttributes[] = $attributeName;
				} else {
					$this->dataAttributes = $this->attributesTypes();
				}
			}
		}
	}

	public function clearValues($clearID = false) {
		if (!$clearID) {
			$id = $this->getId();
		}
		foreach ($this->dataAttributes() as $dataName) {
			$this->{'O' . $dataName}()->clearValue();
		}
		$this->setId($id);
	}

	public function clearId() {
		$this->{$this->fieldId()}->clearValue();
	}

	/**
	 * function makeSelection - this function pass the arguments to javascript file to 
	 * display the light box.
	 * @param type $id
	 */
	function makeSelection(){

		$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				// array(
				// 	'func' => 'changeValue',
				// 	'args' => array($this->getId())
				// ),
				array(
					'func' => 'changePreview',
					'args' => array($this->showEmbededUpdateInput(null,true))
				),
				array(
					'func' => 'closeLightbox'
				),
			)
		);
		header('Content-type: application/json');
		echo json_encode($return);

	}

	function makeAppendSelection(){
		$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				array(
					'func' => 'appendContainedElement',
					'args' => array($this->showEmbededAppendInput(null,true))
				),
				array(
					'func' => 'closeLightbox'
				),
			)
		);
		header('Content-type: application/json');
		echo json_encode($return);

	}



	function makeSearchAdition(){

		$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				array(
					'func' => 'changeValue',
					'args' => array($this->getId())
				),
				array(
					'func' => 'changePreview',
					'args' => array($this->showEmbededSearchInput(null,true))
				),
				array(
					'func' => 'closeLightbox'
				),
			)
		);
		header('Content-type: application/json');
		echo json_encode($return);
	}

	public function Name($name = null){
		if(!$name){
			if(!$this->Name){
				$this->Name = explode('_',get_class($this));
				array_shift( $this->Name  );
				$this->Name = implode(' ', $this->Name );
				$this->Name  = preg_replace('/[A-Z]/', ' $0', $this->Name);
			}
			return $this->Name;
		}
	}

	function showMultiPicker() {
		return SC_Main::$RENDERER->table(array($this->toArray()));
	}

	function getId() {
		//user_error($this->fieldId());
		return $this->{$this->fieldId()}();
	}

	function setId($id) {
		$this->{$this->fieldId()}($id);
		return $this;
	}

	/**
	 * Returns an array representation of the Element assigning each Data's name
	 * as the key and the data's value as the value.
	 *
	 * @return array
	 */
	public function toArray() {
		foreach ($this->dataAttributes() as $dataName) {
			$ret[$dataName] = $this->$dataName();
		}
		return $ret;
	}

	/**
	 * Applies a method to all the Datas and returns an array containing all the responses.
	 *
	 * @param string $method must be a method common to all datas
	 */
	public function processData($method) {
		$return = array();
		foreach ($this->dataAttributes() as $dataName) {
			if (isset($this->$dataName)) {
				$r = $this->$dataName->$method();
				if (isset($r))
				$return[] = $r;
			}
		}

		// @todo: verify if it can stay this way
		return $return;
	}

	//------------------------------- Performance
	function dataAttributes() {
		if (!$this->dataAttributes) {
			$this->dataAttributes = $this->attributesTypes();
		}
		return $this->dataAttributes;
	}

	// @todo: change name to attributesOfClass
	function attributesTypes($type = 'SD_Data') {
		foreach ($this as $name => $data) {
			if ($data instanceof $type) {
				$a[] = $name;
			}
		}
		return @$a ? : array();
	}

	// @todo: change name to attributesOfClass
	function attributesTypesWith($type = 'SD_Data', $what = 'fetch') {
		$a = null;
		foreach ($this as $name => $data) {

			if ($data instanceof $type && $this->$name->$what()  ) {

				$a[] = $name;
			}
		}
		return @$a ? : array();
	}

	//vcsrl
	/**
	 * @param string $what
	 * Returs all the Element Datas that have the method/attribute specified by $what
	 * 
	 * @return SD_Data[] 
	 */
	public function datasWith(string $what,$retType = 'strings') {
		$output = array();
		$tempArray = SC_Main::$VCRSLMethods;
		$tempArray[] = 'parent';
		$tempArray[] = 'embed';  // may be useless because embeded in VCRSL
		if(in_array($what,$tempArray)){
			foreach ($this->dataAttributes() as $data) {
				if ($this->$data->$what()) {
					if($retType == 'strings'){
						$output[] = $data;
					}elseif($retType == 'objects'){					
						$output[] = $this->$data;
					}
				}
			}
		}else{
			$output = 'NotVCRSL';
		}
		return $output;
	}
        
	function allow($validator, $method) {

		$validatorClass = '\\' . SC_Main::$PERMISSIONS;
		$validatorObject = new $validatorClass($validator);
                $rolesArray = $validatorObject->role();
                $roleNameHierarchy = array();
                foreach ($rolesArray as $key) {
                    $roleNameHierarchy[$key->level()] = $key->name();
                }
                asort($roleNameHierarchy);
                if (isset($_SESSION['simplonUser'])) {
                        foreach ($roleNameHierarchy as $value) {
                            if (isset(self::$permissions[$value])) {
                                    return $this->isAllowed(self::$permissions[$value], $method); 
                            } elseif (isset(self::$permissions['default'])){
                                    return $this->isAllowed(self::$permissions['default'], $method);
                            } elseif (isset(SC_Main::$DEFAULT_PERMISSIONS[$value])) {
                                    return $this->isAllowed(SC_Main::$DEFAULT_PERMISSIONS[$value], $method);
                            } else {
                                    return false;
                            }
                    }
                } else {
                            return true;
                    }
	}

	function isAllowed($methods, $method) {
		$values = array(
		'a' => array('processAdmin', 'showAdmin'),
		'c' => array('processCreate', 'showCreate'),
		'd' => array('processDelete', 'showDelete'),
		's' => array('processSearch', 'showSearch'),
		'u' => array('processUpdate', 'showUpdate'),
        'v' => array('showView'),
		'i' => array('index'),
                '*' => array('*')
		);
		$allowedMethods = array();
		$deniedMethods = array();
                $allowedElementMethods = array();
		$deniedElementMethods = array();
		if (isset($methods['Allow'])) {
			$flagsMethods = str_split(strtolower(array_shift($methods['Allow'])));
			foreach ($flagsMethods as $key) {
				foreach ($values[$key] as $value) {
					$allowedMethods[] = $value;
				}
			}
			if (isset($methods['Allow'])) {
				foreach ($methods['Allow'] as $value) {
					$allowedElementMethods[] = $value;
				}
			}
		}
		if (isset($methods['Deny'])) {
			$flagsMethods = str_split(strtolower(array_shift($methods['Deny'])));
			foreach ($flagsMethods as $key) {
				foreach ($values[$key] as $value) {
					$deniedMethods[] = $value;
				}
			}
			if (isset($methods['Deny'][1])) {
				foreach ($methods['Deny'] as $value) {
					$deniedElementMethods[] = $value;
				}
			}
		}
                if ( ( in_array('*', $deniedElementMethods) || in_array('*', $deniedMethods)) || (in_array($method, $deniedElementMethods) || in_array($method, $deniedMethods)) ) {
			return false;
		} elseif ( (in_array('*', $allowedElementMethods) || in_array('*', $allowedMethods)) || ( in_array($method, $allowedElementMethods) || in_array($method, $allowedMethods)) ) {
			return true;
		} else {
			return true;
		}
	}


	public function __toString()
	{
		return $this->showView();
	}

	public function debug(){
		$datas = $this->dataAttributes();
		$ret = $this->getClass();
		foreach($datas as $data){
			$ret .= "\n".$data.' :: '.$this->$data()."";
		}
		return $ret;
	}

}