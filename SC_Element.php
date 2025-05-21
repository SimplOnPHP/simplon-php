<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Base class for all SimplOn Elements.
 * Elements are fundamental building blocks in SimplOn, representing a set of related data and providing a standardized way to interact with that data.
 * They include the information required to allow datastorage and render clases extend traditional object capabilities capabilities allowing 
 * for common tasks such as displaying, storing, searching, and generating user interfaces like forms.
 
 * 
 * Elements work in conjunction with SimplON Data objects. Each attribute of an Element that holds data intended for interaction (display, storage, etc.) 
 * should be an instance of a SimplON Data class (or a class extending SD_Data). These Data objects encapsulate the data itself along with important metadata
 * about how the data should be handled (e.g., whether it should be shown in a view, included in a form, searchable, validated, etc.).
 *
 * By combining the Element's methods and the metadata within its Data attributes, SimplOn's Renderers and DataStorage classes can automatically generate 
 * interfaces and interact with data storage without needing specific knowledge of each Element's internal structure. This allows for seamless integration 
 * and automatic updates of the interface and storage mechanisms as the Element's definition changes.
 *
 * Inherits from {@see SC_BaseObject} for fundamental SimplOn object capabilities.
 *
 * @version 1b.1.0
 * @package SimplOn\Core
 * @author RSL 
 * 
 **/

class SC_Element extends SC_BaseObject {

	/**
	 * Name of the Data attribute that represents the unique identifier field of the Element.
	 * This typically corresponds to the primary key column name in an SQL database.
	 *
	 * @todo enable handle of multiple id fields, that should be automatically
	 * detected, as those should be all instances of \SimplOn\Data\Id).
	 * @var string|null
	 */
	protected $fieldId;

	/**
	 * The DataStorage instance used by this Element to interact with the underlying data source.
	 * By default, it is initialized with the global DataStorage instance configured in {@see SC_Main::$DATA_STORAGE}.
	 *
	 * @var \SimplOn\DataStorages\SDS_DataStorage
	 */
	protected $dataStorage;

	/**
	 * The name of the data storage location associated with this Element.
	 * This could be a table name in a relational database, a collection name in a NoSQL database, or a path in a file system.
	 * By default, this is set to the class name of the Element (without the namespace).
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
	 * Elements can be nested using a special kind of data called ElementContainer.
	 * If an element is contained within another element, the containing element can be accessed via this attribute.
	 *
	 * NOTE: This property is NOT the same as the `parent::` keyword used to call a parent class's methods or access parent properties.
	 *
	 * @var SC_Element|null The parent element, or null if this element is not nested.
	 */
	protected $parent;

	/**
	 * Flag to avoid the system to validate
	 * DataStorage more than once.
	 * @var boolean
	 */
	public static $storageChecked;

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

	protected $defaultAction = null;

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


	protected $Name, $NamePlural;

	protected $nameInParent;

	/**
	 * How to order the elements when listing them
	 * 
	 * 
	 */
	protected $OrderCriteria = 'SimplOn_id desc';

	/** @var SR_htmlJQuery */
	protected $renderer;

	
	/**
	 * Determines the kind of permissions that are given to the attributes.
	 * 
	 * Depending on the $methodsFamilies array each method is assigned a DatasMode
	 * View, Update, Create, Delete, Search or the method it static. This way the permissions don't have to be asigned for each method but for DatasMode and special methods.
	 * 
	 */
	protected $datasMode;

	/**
	 * Keeps the relationship between the methods and the datasMode so that is simplier to write the permissions.
	 */
	static $methodsFamilies = array(
		'showView'=>'View',
		'showSearch'=>'View',
		'showList'=>'View',
		'showEmbed'=>'View',
		'showEmbededStrip'=>'View',
		'processReport'=>'View',

		'showAdmin'=>'Admin',
		
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

	static
		$AdminMsg,
		$ReturnBtnMsg,
		$CancelBtnMsg,

		$SearchBtnMsg,
		$SearchMsg,

		$ViewBtnMsg,
		$ViewMsg,

		$CreateBtnMsg,
		$CreatedMsg,
		$CreateMsg,
		$CreateError,

		$UpdateBtnMsg,
		$UpdatedMsg,
		$UpdateMsg,
		$UpdateError,

		$DeleteBtnMsg,
		$DeletedMsg,
		$DeleteMsg,
		$DeleteError;

	/**
	 * Constructs a new SimplOn Element instance.
	 *
	 * The constructor performs several key initializations:
	 * - Sets the storage location based on the provided `$storage` parameter or defaults to the class name.
	 * - Assigns the global renderer instance.
	 * - Determines and assigns the appropriate DataStorage instance (either a special one provided or the global one).
	 * - Initializes the Element's singular and plural names if they are not already set.
	 * - Sets various static message properties used for UI elements (e.g., AdminMsg, CreateMsg) using the localization function `SC_MAIN::L()`.
	 * - Calls the user-defined `construct()` method, allowing subclasses to perform their own specific initializations and declare Data attributes.
	 * - Adds any Data attributes defined on the fly.
	 * - Assigns the name of each Data attribute within the Element instance.
	 * - Sets the current Element instance as the logical parent for its Data attributes.
	 * - Ensures the element's storage structure exists in the DataStorage.
	 * - Attempts to fill the Element's Data values from the provided `$id_or_array` (either an array of values or an ID to fetch from storage).
	 * - Sets the `$fullyset` flag to true, indicating that the initialization is complete.
	 *
	 * @param mixed $id_or_array Optional. The unique identifier (ID) of the Element to load from storage, or an array of key-value pairs to pre-fill the Element's Data attributes. Defaults to null.
	 * @param string|null $storage Optional. The specific name of the data storage location for this Element. If not provided, the class name is used.
	 * @param \SimplOn\DataStorages\SDS_DataStorage|null $specialDataStorage Optional. A specific DataStorage instance to use for this Element, overriding the global one. Defaults to null, which means the global DataStorage will be used.
	 */
	public function __construct($id_or_array = null, $storage = null, $specialDataStorage = null) {

		//On heirs put here the asignation of SimplOndata and attributes
		if ($storage)
		$this->storage($storage);
		else
		$this->storage($this->getClass());

		$this->renderer = SC_Main::$RENDERER;

		//Assings the storage element for the SimplonElement. (a global one : or a particular one)
		if (!$specialDataStorage) {
			$this->dataStorage = SC_Main::dataStorage();
		} else {
			$this->dataStorage = &$specialDataStorage;
		}

		if(!$this->Name){$this->Name();}

		static::$AdminMsg = $this->NamePlural().' '.SC_MAIN::L('Manager');

		static::$ReturnBtnMsg = SC_MAIN::L('Return');
		static::$CancelBtnMsg = SC_MAIN::L('Cancel');

		static::$SearchBtnMsg = SC_MAIN::L('Search');
		static::$SearchMsg = SC_MAIN::L('Search').' '.$this->NamePlural();
 
		static::$ViewBtnMsg = SC_MAIN::L('View');
		static::$ViewMsg = SC_MAIN::L('View of '.$this->Name);

		static::$CreateBtnMsg = SC_MAIN::L('Create');
		static::$CreateMsg = SC_MAIN::L('Create a '.$this->Name);
		static::$CreatedMsg = SC_MAIN::L('A '.$this->Name.' has been created');
		static::$CreateError = SC_MAIN::L('A '.$this->Name.' can\'t be created');

		static::$UpdateBtnMsg = SC_MAIN::L('Update');
		static::$UpdateMsg = SC_MAIN::L('Update a '.$this->Name);
		static::$UpdatedMsg = SC_MAIN::L('The '.$this->Name.' has been updated');
		static::$UpdateError = SC_MAIN::L('The '.$this->Name.' can\'t be updated');

		static::$DeleteBtnMsg = SC_MAIN::L('Delete');
		static::$DeleteMsg = SC_MAIN::L('Delete a '.$this->Name);
		static::$DeletedMsg = SC_MAIN::L('A '.$this->Name.' has been deleted');
		static::$DeleteError = SC_MAIN::L('The '.$this->Name.' has been deleted');


		$this->construct($id_or_array, $storage);


		$this->addDatas();
		$this->assignDatasName();
		$this->assignAsDatasParent();

		//checking if there is already a dataStorage and storage for this element
		$this->dataStorage->ensureElementStorage($this);
		
		if (is_array($id_or_array)) {
			try{$this->fillFromArray($id_or_array);}catch(SC_ElementValidationException $ev){}
		} else if ($id_or_array) {
			//if there is a storage and an ID it fills the element with the proper info.
			$this->fillFromDSById($id_or_array);
		} else {
			//$this->fillFromRequest();
		}

		
		// if( !empty(SC_Main::$PERMISSIONS)
		// 	&& !is_string(SC_Main::$PERMISSIONS) 
		// 	&& (SC_Main::$PERMISSIONS !== $this)
		// 	&& ( !in_array($this,SC_Main::$PERMISSIONS::$excentObjects) )
		// 	){ 
		// 		SC_Main::$PERMISSIONS->setValuesByPermissions($this, '' );
		// 	}


		$this->fullyset = true;
	}


	/**
	 * Returns an array of attribute names (properties) of the Element instance
	 * that are instances of the specified class type.
	 *
	 * @param string $type The class type to filter attributes by.
	 * @return array An array of attribute names that are instances of the specified type.
	 */
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
	 * Returns the static permissions array for the Element.
	 *
	 * This array defines the access rules for the element's data and methods.
	 *
	 * @return array The permissions array.
	 * @see SC_Element::$permissions
	 */
	function permissions(){
		return static::$permissions;
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
	 * Gets or sets the name of the Data attribute that represents the unique identifier field of the Element.
	 * If the field ID is not already set and no value is provided, it attempts to automatically detect the first attribute of type `SD_Id`.
	 *
	 * @param string|null $val Optional. The name of the attribute to set as the unique identifier field. If null, the current field ID is returned.
	 * @return string|null The name of the unique identifier field.
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

	/**
	 * Gets or sets the parent element of this instance.
	 * 
	 * This is used when elements are nested within {@see ElementContainer}.
	 * 
	 * @param SC_Element|null $parent Optional. The parent element to set. If null, the current parent element is returned.
	 * @return SC_Element|null The parent element, or null if not set.
	 * @see SC_Element::$parent
	 */
	function parent(&$parent = null) {
		if (!$parent) {
			return $this->parent;
		} else {
			$this->parent = $parent;
		}
	}

	// May be no longer used
	// function elementContainerName(){
	// 	if( $this->nameInParent() || $this->nameInParent() === 0 ){ return $this->nameInParent(); }else{ return 'placeHolderName'; }
		
	// }



	/**
	 * Magic method to handle dynamic method calls, providing simplified access to Data attributes and rendering methods.
	 *
	 * This method intercepts calls to methods that are not explicitly defined in the class. It provides several functionalities:
	 * 1. If the method name matches a Data attribute of the Element, it acts as a getter or setter for the Data attribute's value.
	 *    - If arguments are provided, the first argument is used to set the Data attribute's value.
	 *    - If no arguments are provided, it returns the current value of the Data attribute.
	 * 2. If the method name starts with "show", it delegates the call to the renderer's `render` method to generate a representation of the element.
	 * 3. If the method name starts with 'O', 'F', or 'L' followed by the name of a Data attribute:
	 *    - 'O' followed by a Data attribute name: Provides direct access to the Data object instance (getter/setter).
	 *    - 'F' followed by a Data attribute name: Calls the `fixValue` method on the Data attribute, wich set the value of the attribute and does not allow to be changed later on.
	 *    - 'L' followed by a Data attribute name: Returns the label of the Data attribute.
	 * 4. For any other method calls, it falls back to the parent class's `__call` method.
	 *
	 * @param string $name The name of the method being called.
	 * @param array $arguments An array of arguments passed to the method.
	 * @return mixed The result of the called method or operation.
	 * @throws SC_Exception If an unrecognized letter !('O', 'F' or 'L') is used before a Data attribute name.
	 * @see SC_BaseObject::__call()
	 */
	public function __call($name, $arguments) {

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
			}else {
				return $this->$name->val();
			}
		} else if(substr($name, 0, 4) === "show"){

			// return $this->renderer->render($this,$name);
			array_unshift($arguments,$name);
			array_unshift($arguments,$this);
			return call_user_func_array(array($this->renderer, 'render'), $arguments);
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
					throw new SC_Exception('Letter ' . $letter . ' not recognized!');
				}
			} else {
				return parent::__call($name, $arguments);
			}
		}
	}

	// -- SimplON key methods
	/**
	 * Fills the Element's Data attributes with values from a given array.
	 *
	 * This method iterates through the provided array, and for each key-value pair,
	 * it attempts to assign the value to the corresponding Data attribute within
	 * the Element instance. It also performs validation for each Data attribute
	 * during the assignment process.
	 *
	 * @param array &$array_of_data An associative array where keys are the names of the Data attributes
	 *                              and values are the data to be assigned. The array is passed by reference.
	 * @return int The number of Data attributes successfully filled.
	 * @throws SC_ElementValidationException If any of the Data attributes fail validation during the filling process.
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
		if(!empty($this->excetionsMessages)){
			throw new SC_ElementValidationException($this->excetionsMessages);
		}
		return $filled;
	}


	/**
	 * Fills the Element's Data attributes with values from the $_REQUEST and $_FILES superglobal arrays.
	 *
	 * This method processes data submitted via a request. It first checks if $_REQUEST is populated.
	 * If so, it iterates through $_FILES and, for any entry corresponding to an SD_File attribute
	 * on the element, it incorporates the file data into the $_REQUEST array before
	 * passing the combined data to `fillFromArray` for assignment and validation.
	 *
	 * This is necessary because `fillFromArray` is designed to handle a single array of data,
	 * but file uploads are provided in the separate $_FILES superglobal.
	 *
	 * @return bool True if $_REQUEST was processed, false otherwise.
	 * @throws SC_ElementValidationException If any of the Data attributes fail validation during the filling process via `fillFromArray`.
	 */
	public function fillFromRequest() {
		if ($_REQUEST) {

			if(!empty($_FILES)){
				foreach ($_FILES as $key => $value) {
					if ( isset($this->$key) && $this->$key instanceof SD_File && is_array($value) ) {
						$_REQUEST[$key] = $value;
					}
				}
			}
			try{$this->fillFromArray($_REQUEST);}catch(SC_ElementValidationException $ev){
				//TODO
			}
			return true; // Return true if $_REQUEST was processed
		} else {
			return false;
		}
	}

	/**
	 * Fills the Element's Data attributes with values from the `$_POST` superglobal array.
	 *
	 * This method processes data submitted via a POST request. It checks if `$_POST` is populated.
	 * If so, it passes the `$_POST` array to `fillFromArray` for assignment and validation.
	 * It suppresses potential errors during the call to `fillFromArray`.
	 * Validation exceptions thrown by `fillFromArray` are currently caught but not handled within this method's catch block.
	 *
	 * @return int|false The number of attributes successfully filled if `$_POST` is not empty, or `false` otherwise.
	 *                   Note: Due to error suppression and an empty catch block, the return value might be
	 *                   unpredictable if `fillFromArray` throws an exception.
	 */
	// public function fillFromPost() {
	// 	if ($_POST) {
	// 			try{@$ret = $this->fillFromArray($_POST);}catch(SC_ElementValidationException $ev){
	// 				//TODO
	// 			}
	// 		return @$ret;
	// 	} else {
	// 		return false;
	// 	}
	// }

	//------------Data Storage
	/**
	 * Retrieves the element's Data attribute values from the configured DataStorage.
	 *
	 * It uses the provided `$id` to fetch the record. If no `$id` is provided,
	 * it attempts to use the element's currently set ID (retrieved via {@see getId()}).
	 * The fetched data is then used to fill the Element's Data attributes
	 * by calling {@see fillFromArray()}.
	 *
	 * @param mixed $id Optional. The unique identifier of the element to fetch from the data storage.
	 *                  If null, the element's current ID is used.
	 * @throws SC_Exception If neither a valid `$id` is provided nor the element has a pre-existing ID.
	 * @return void
	 */
	public function fillFromDSById($id = null) {
		if (isset($id)) {
			$this->setId($id);
		}
		if ($this->getId() || $this->getId() === 0) {
			$dataArray = $this->dataStorage->readElement($this);
			try{$this->fillFromArray($dataArray);}catch(SC_ElementValidationException $ev){}
		} else {
			throw new SC_Exception('The object of class: ' . $this->getClass() . " has no id so it can't be filled using method fillFromDSById");
		}
	}


	/**
	 * Saves the element to the configured DataStorage.
	 *
	 * This method checks if the element instance has a unique identifier (ID) set.
	 * If an ID is present, it calls the `update()` method to update the existing record in the data storage.
	 * If no ID is present, it calls the `create()` method to create a new record in the data storage.
	 *
	 * @return mixed The result of the `update()` or `create()` operation, typically the number of affected rows or the new ID.
	 */
	public function save() {
		return $this->getId() ? $this->update() : $this->create();
	}

    /**
     * Updates the element's data in the configured DataStorage.
     *
     * This method first calls `processData('preUpdate')` to execute any pre-update processing
     * defined for the element's Data attributes. It then delegates the actual update operation
     * to the DataStorage instance's `updateElement` method. After the update is performed,
     * it calls `processData('postUpdate')` to execute any post-update processing.
     *
     * @return mixed The result of the DataStorage update operation, which typically
     *               indicates the success or failure of the update (e.g., number of affected rows).
     */
    public function update() {
        $this->processData('preUpdate');
        $return = $this->dataStorage->updateElement($this);
        $this->processData('postUpdate');
        return $return;
    }


	/**
	 * Deletes the element from the configured DataStorage.
	 *
	 * This method first calls `processData('preDelete')` to execute any pre-deletion
	 * processing defined for the element's Data attributes. It then delegates the
	 * actual deletion operation to the DataStorage instance's `deleteElement` method.
	 * After the deletion is performed, it calls `processData('postDelete')` to execute
	 * any post-deletion processing.
	 *
	 * @return mixed The result of the DataStorage deletion operation, which typically
	 *               indicates the success or failure of the deletion (e.g., number of affected rows).
	 */
	public function delete() {
		$this->processData('preDelete');
		$return = $this->dataStorage->deleteElement($this);
		$this->processData('postDelete');
		return $return;
	}

/**
 * Validates the Element's Data attributes before interacting with the DataStorage (e.g., before creating or updating a record).
 *
 * This method primarily checks for required Data attributes by calling {@see requiredCheck()}.
 * If any required attributes are missing or validation fails during the `requiredCheck`,
 * it collects the validation messages and throws an {@see SC_ElementValidationException}.
 *
 * This method is typically called internally by methods like {@see create()} and {@see update()}.
 *
 * @throws SC_ElementValidationException If required fields are not filled or fail validation checks.
 */
function validateForDB() {
	@$exceptionMessages = $this->requiredCheck($this->excetionsMessages);
	if (!empty($exceptionMessages)) {
		throw new SC_ElementValidationException($exceptionMessages);
	}
}

	/**
	 * Checks if all required Data attributes of the Element have values.
	 *
	 * This method iterates through all Data attributes marked as 'required'.
	 * If a required Data attribute is empty and is not an auto-incrementing field,
	 * it adds a validation message to the provided array.
	 *
	 * @param array $array An associative array to which validation messages will be added.
	 *                     The keys are the names of the Data attributes that failed validation.
	 * @return array The updated array containing validation messages for any required fields that are empty.
	 */
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
	 * Creates a new record for the element in the configured DataStorage.
	 *
	 * This method delegates the creation process to the DataStorage instance's `createElement` method.
	 * Upon successful creation, it sets the ID of the current Element instance with the newly generated ID
	 * returned by the DataStorage. Finally, it calls `processData('postCreate')` to execute any post-creation
	 * processing defined for the element's Data attributes.
	 *
	 * @return mixed The result of the DataStorage creation operation, typically the new unique identifier (ID)
	 *               for the created record on success, or `false` on failure.
	 */
	/**
	 * Creates a new record for the element in the configured DataStorage.
	 *
	 * This method first calls `processData('preCreate')` to execute any pre-creation
	 * processing defined for the element's Data attributes. It then delegates the
	 * creation process to the DataStorage instance's `createElement` method.
	 * Upon successful creation, it sets the ID of the current Element instance with
	 * the newly generated ID returned by the DataStorage. Finally, it calls
	 * `processData('postCreate')` to execute any post-creation processing defined
	 * for the element's Data attributes.
	 *
	 * @return mixed The result of the DataStorage creation operation, typically the new unique identifier (ID)
	 *               for the created record on success, or `false` on failure.
	 */
	public function create() {
		$this->processData('preCreate');
		$id = $this->dataStorage->createElement($this);
		$this->setId($id);
		$this->processData('postCreate');
		return $id !== false;
	}

	/**
	 * Processes the creation of an Element via a JSON request.
	 *
	 * This method handles incoming request data, validates it, attempts to create
	 * a new record for the element in the data storage, and returns a JSON response
	 * indicating the result or validation errors.
	 *
	 * On successful creation, it returns a JSON object containing commands for the
	 * renderer, typically a command to redirect to the next step.
	 * If validation fails, it returns a JSON object containing commands for the
	 * renderer to display validation messages for specific data attributes.
	 * If a database error occurs during creation, it triggers a user error.
	 *
	 * @param string|null $nextStep Optional. The URL or action to redirect to after successful creation. Defaults to null.
	 * @return string A JSON string containing commands for the renderer or error information.
	 * @throws SC_ElementValidationException If data validation fails before attempting to create the element. (This is caught internally and returned as JSON)
	 * @throws PDOException If a database error occurs during the creation process. (This is caught internally and triggers a user error)
	 */
	function processCreateJSon($nextStep = null) {
					
		try {
			$this->fillFromRequest();
			$this->validateForDB();
			$shouldContinue = true; // Control variable		
		} catch (SC_Exception $ev) {

			$data = array();
	
			
			if($ev->datasValidationMessages()){
				foreach ($ev->datasValidationMessages() as $key => $value) {
					$data[] = array(
					'func' => 'showValidationMessages',
					'args' => array($key, $value[0])
					);
				}
			}else{
				//var_dump($ev);
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
				user_error(static::$CreateError, E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}
	 
	/**
	 * Renders a form for creating a new instance of the Element.
	 *
	 * This method generates a user interface for creating a new Element record.
	 * It includes a title, a form containing input fields corresponding to the
	 * Element's Data attributes that are marked for 'create' and 'show',
	 * and submit/cancel buttons. The form is configured to submit data to the
	 * `processCreate` method.
	 *
	 * @return \SimplOn\Interface\SI_systemScreen A system screen object containing the creation form.
	 */
	function showCreate(){

		$content = new SI_VContainer();
		$content->addItem(new SI_Title(static::$CreateMsg,4));
		
		$form = new SI_Form($this->datasWith('create','show'), SC_Main::$RENDERER->action($this,'processCreate'));


		//$form->addItem( new SI_HContainer([  new SI_Submit(SC_Main::L('Create')), new SI_CancelButton()  ]) );
		$form->addItem( new SI_Submit(SC_Main::L('Create')) );
		$form->addItem( new SI_CancelButton() );
		
		$content->addItem($form);

		$page = new SI_systemScreen($content,SC_Main::L(static::$CreateBtnMsg) );
		return $page;

	}

	/**
	 * Processes the creation of a new Element record and returns a JSON response.
	 *
	 * This method prepares the necessary data and delegates the actual creation
	 * and JSON response generation to {@see processCreateJSon()}.
	 * It sets a success message and determines the next step (typically redirecting to the admin view)
	 * before calling the JSON processing method.
	 *
	 * @return string A JSON string containing commands for the renderer, typically a redirect command.
	 */
	function processCreate(){
		$this->renderer->setMessage(static::$CreatedMsg);
		$nextStep = $this->renderer->action($this,'showAdmin','id',static::$CreatedMsg );	
		return $this->processCreateJSon($nextStep);
	}

	/**
	 * Renders a form for continuous creation of new Element instances.
	 *
	 * This method is similar to {@see showCreate()} but is intended for scenarios where
	 * the user needs to create multiple instances consecutively. After a successful
	 * creation, the form is typically re-displayed with a success message, allowing
	 * the user to immediately create another element without navigating away.
	 *
	 * It sets a success message and defines the form action to point to
	 * {@see processContinusCreate()} which handles the continuous flow.
	 * The actual rendering of the form is delegated to the renderer's `render` method,
	 * using the 'showCreate' method as the basis for the form structure.
	 *
	 * @param string|null $template Optional. The template to use for rendering. Defaults to the default template for 'showCreate'.
	 * @param bool $partOnly Optional. If true, renders only the content part, not the full page. Defaults to false.
	 * @param string|null $action Optional. The URL or action for the form submission. If not provided, it defaults to the result of {@see processContinusCreate()}.
	 * @param string|null $nextStep Optional. The URL or action to redirect to after a sequence of continuous creations is finished. Not typically used in the continuous flow itself, but can be passed.
	 * @return mixed The rendered output, typically an HTML string representing the continuous creation form.
	 */
	function showContinusCreate($template = null, $partOnly = false,$action=null,$nextStep=null){
		$this->renderer->setMessage(static::$CreatedMsg );
		$action = $this->renderer->action($this,'processContinusCreate');
		return $this->renderer->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
		// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	}	

	/**
	 * Processes the continuous creation of a new Element record via a JSON request.
	 *
	 * This method is the target of the form submission from {@see showContinusCreate()}.
	 * It handles incoming request data, validates it, attempts to create a new record,
	 * and returns a JSON response.
	 *
	 * Unlike {@see processCreateJSon()}, upon successful creation, this method generates
	 * a `$nextStep` that points back to {@see showContinusCreate()} with the newly created
	 * element's ID (though the ID might not be strictly necessary for displaying an empty form,
	 * it's included in the action URL). This allows the renderer to stay on the creation form
	 * and display a success message, facilitating continuous creation.
	 *
	 * If validation fails, it returns a JSON object containing commands for the renderer
	 * to display validation messages, similar to {@see processCreateJSon()}.
	 * If a database error occurs during creation, it triggers a user error.
	 *
	 * @param string|null $nextStep Optional. The URL or action to redirect to after successful creation. If null, it defaults to calling `showContinusCreate` again with the newly created element's ID.
	 * @return string A JSON string containing commands for the renderer or error information.
	 * @throws SC_ElementValidationException If data validation fails before attempting to create the element. (Caught internally and returned as JSON)
	 * @throws PDOException If a database error occurs during the creation process. (Caught internally and triggers a user error)
	 */
	function processContinusCreate($nextStep = null) {
		$nextStep = $this->renderer->action($this,'showContinusCreate','id');
		return $this->processCreateJSon($nextStep);
	}

	/**
	 * Renders a form for creating a new instance of the Element, intended for scenarios
	 * where the newly created element should be selected before the creation of another element usually using SD_ElementContainer.
	 *
	 * This method delegates the rendering of the creation form to the renderer, typically
	 * reusing the structure defined for `showCreate`. The form's action is set to
	 * `processCreateSelect`, which handles the creation and subsequent selection logic.
	 *
	 * @param string|null $template Optional. The template to use for rendering. Defaults to the default template for 'showCreate'.
	 * @param bool $partOnly Optional. If true, renders only the content part, not the full page. Defaults to false.
	 * @param string|null $action Optional. The URL or action for the form submission. If not provided, it defaults to the result of {@see processCreateSelect()}.
	 * @param string|null $nextStep Optional. This parameter is typically not directly used in this method but is included for consistency with other show methods.
	 * @return mixed The rendered output, typically an HTML string representing the creation form for selection.
	 */
	function showCreateSelect($template = null, $partOnly = false,$action=null,$nextStep=null){
		$action = $this->renderer->action($this,'processCreateSelect');
		return $this->renderer->render($this,'showCreate', 'AE_basicPage',$template, $action);
		//return $this->renderer->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
		// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	}
	
	/**
	 * Renders a form for creating a new instance of the Element, specifically designed
	 * for appending the newly created element to a container or list in the parent element's interface, usually using SD_ElementsContainer.
	 *
	 * This method delegates the rendering of the creation form to the renderer, typically
	 * reusing the structure defined for `showCreate`. The form's action is set to
	 * `processCreateAppend`, which handles the creation and subsequent appending logic.
	 *
	 * @param string|null $template Optional. The template to use for rendering. Defaults to the default template for 'showCreate'.
	 * @param bool $partOnly Optional. If true, renders only the content part, not the full page. Defaults to false.
	 * @param string|null $action Optional. The URL or action for the form submission. If not provided, it defaults to the result of {@see processCreateAppend()}.
	 * @param string|null $nextStep Optional. This parameter is typically not directly used in this method but is included for consistency with other show methods.
	 * @return mixed The rendered output, typically an HTML string representing the creation form for appending.
	 */
	// function showCreateAppend($template = null, $partOnly = false,$action=null,$nextStep=null){

	// 	//$action = $this->renderer->action($this,'processCreateSelect');
	// 	$action = $this->renderer->action($this,'processCreateAppend');
	// 	return $this->renderer->render($this,'showCreate', 'AE_basicPage',$template, $action);
	// 	//return $this->renderer->render($this,'showCreate',$template, $partOnly ,$action ,$nextStep);
	// 	// (SC_BaseObject $object, string $method, $template = null, $partOnly = false,$action=null,$nextStep=null)
	// }

	/**
	 * Displays a message indicating that the user does not have sufficient permissions
	 * to access the requested resource or perform the requested action.
	 *
	 * This method creates a simple interface with a title showing the "cannot access" message
	 * and a link to a default action or home page, as configured in the SC_Main::$PERMISSIONS.
	 * It returns a system screen object containing this content.
	 *
	 * @return \SimplOn\Interface\SI_systemScreen A system screen object displaying the no access message.
	 */
	function showNoAccess() {

		$content = new SI_VContainer();
		$content->addItem(new SI_Title( SC_Main::$PERMISSIONS::$CantAccessMsg,4));
		$content->addItem(new SI_Link( SC_Main::$PERMISSIONS->defaultAction(),SC_Main::L(SC_Main::$PERMISSIONS::$CantAccessHomeLinkMsg )));
		
		$page = new SI_systemScreen($content,SC_Main::L(SC_Main::$PERMISSIONS::$CantAccessMsg) );
		return $page;
	}

	/**
	 * Gets or sets the default action for the Element.
	 *
	 * If a default action is provided, it sets the internal `defaultAction` property.
	 * If no default action is provided, it returns the currently set default action.
	 * If no default action is set, it returns the default action configured in `SC_Main::$DEFAULT_ELEMENT` and `SC_Main::$DEFAULT_METHOD` rendered by the renderer.
	 *
	 * @param string|null $defaultAction Optional. The default action to set. Defaults to null.
	 * @return string The default action URL or identifier.
	 */
	function defaultAction($defaultAction = null) { 

		if(!$defaultAction) {
			if(isset($this->defaultAction)) {
				return $this->defaultAction;
			} else {
				return $this->renderer->action(SC_Main::$DEFAULT_ELEMENT,SC_Main::$DEFAULT_METHOD);
			}
		} else {
			$this->defaultAction = $defaultAction;
		}
	}

	/**
	 * Processes the creation of a new Element record and appends it to a container in the parent's interface via a JSON response.
	 *  usually used with SD_ElementsContainer.
	 *
	 * This method handles incoming request data, validates it, attempts to create
	 * a new record for the element in the data storage. Upon successful creation,
	 * it calls `makeChangeSelection()` to generate a JSON response containing commands
	 * for the renderer to append the newly created element's representation to a container
	 * and close any open lightboxes.
	 *
	 * If validation fails, it returns a JSON object containing commands for the renderer
	 * to display validation messages.
	 * If a database error occurs during creation, it triggers a user error.
	 *
	 * @param string|null $nextStep Optional. This parameter is typically not directly used in this method but is included for consistency.
	 * @return string A JSON string containing commands for the renderer or error information.
	 * @throws SC_ElementValidationException If data validation fails before attempting to create the element. (Caught internally and returned as JSON)
	 * @throws PDOException If a database error occurs during the creation process. (Caught internally and triggers a user error)
	 */
	// function processCreateAppend($nextStep = null) {
	// 	try {
	// 		$this->fillFromRequest();
	// 		$this->validateForDB();
	// 	} catch (SC_ElementValidationException $ev) {
	// 		$data = array();
	// 		foreach ($ev->datasValidationMessages() as $key => $value) {
	// 			$data[] = array(
	// 			'func' => 'showValidationMessages',
	// 			'args' => array($key, $value[0])
	// 			);
	// 		}
	// 		$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => $data
	// 		);
	// 		$return = json_encode($return);
	// 		return $return;
	// 	}
	// 	try {
	// 		if ($this->create()) {
	// 				return $this->makeChangeSelection();
	// 		} else {
	// 			// @todo: error handling
	// 			user_error(static::$CreateError, E_USER_ERROR);
	// 		}
	// 	} catch (\PDOException $ev) {
	// 		//user_error($ev->errorInfo[1]);
	// 		//@todo handdle the exising ID (stirngID) in the DS
	// 		user_error($ev);
	// 	}
	// }

	// function processCreateSelect($nextStep = null) {
	// 	try {
	// 		$this->fillFromRequest();
	// 		$this->validateForDB();
	// 	} catch (SC_ElementValidationException $ev) {
	// 		$data = array();
	// 		foreach ($ev->datasValidationMessages() as $key => $value) {
	// 			$data[] = array(
	// 			'func' => 'showValidationMessages',
	// 			'args' => array($key, $value[0])
	// 			);
	// 		}
	// 		$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => $data
	// 		);
	// 		$return = json_encode($return);
	// 		return $return;
	// 	}
	// 	try {
	// 		if ($this->create()) {
	// 				return $this->makeSelection();
	// 		} else {
	// 			// @todo: error handling
	// 			user_error(static::$CreateError, E_USER_ERROR);
	// 		}
	// 	} catch (\PDOException $ev) {
	// 		//user_error($ev->errorInfo[1]);
	// 		//@todo handdle the exising ID (stirngID) in the DS
	// 		user_error($ev);
	// 	}
	// }

	function showSearch(){
		$body = new SI_VContainer();
		$title = new SI_Title(static::$SearchMsg,'3');

		$action = $this->renderer->action($this,'processSearch');
        $form = new SI_Form($this->datasWith('search','objects'), $action);
		$form->addItem(new SI_SubmitButton(static::$SearchBtnMsg));
		$form->addItem(new SI_CancelButton(static::$CancelBtnMsg));

		$body->addItem($title);
		$body->addItem($form);	

		
        return $this->renderer->renderFullPage($body, 'showCreate', $this);
	}

	// function processCreprocessUpdateate(){
		
	// 	$this->renderer->setMessage(static::$UpdatedMsg);
	// 	$nextStep = $this->renderer->action($this,'showAdmin','id');

	// 	return $this->processUpdateJSon($nextStep);
	// }

	// function showEmbededUpdateInput($template = null, $partOnly = false,$action=null,$nextStep=null){

	// 	//$ret = $this->renderer->render($this,'showEmbededUpdateInput',$template,$partOnly,$action);
	// 	$ret = $this->renderer->render($this,'showEmbededUpdateInput', null,$template, $action);
	// 	$canEdit = true;
	// 	if($canEdit == false){
	// 		//$ret = \phpQuery::newDocumentHTML($ret);
	// 		//$ret['.SimplOn.actions .UpdateSelect']->remove();
	// 		//$ret = $ret->htmlOuter();
	// 	}
	// 	return $ret;
	// }	

	// function showEmbededSearchInput($template = null, $partOnly = false,$action=null,$nextStep=null){

	// 	//$ret = $this->renderer->render($this,'showEmbededUpdateInput',$template,$partOnly,$action);
	// 	$ret = $this->renderer->render($this,'showEmbededSearchInput', null,$template, $action);
	// 	$canEdit = false;
	// 	if($canEdit == false){
	// 		//$ret = \phpQuery::newDocumentHTML($ret);
	// 		//$ret['.SimplOn.actions .UpdateSelect']->remove();
	// 		//$ret = $ret->htmlOuter();
	// 	}
	// 	return $ret;
	// }
	

	function showView(){
		$this->fillFromDSById();


		$content = new SI_VContainer();
		$content->addItem(new SI_Divider(static::$ViewMsg,5));

		foreach($this->datasWith('view','objects') as $data){
			$content->addItem(new SI_HContainer([$data->label().':',$data->showView()],'r l','minmax(7rem, 1fr) 10fr'));
		}

		$page = new SI_systemScreen($content,static::$ViewMsg );
		SC_Main::render($page);

	}

	function showEmbeded(){
		$this->fillFromDSById();

		foreach($this->datasWith('embeded','objects') as $data){
			@$ret .= $data->showEmbeded().' ';
		}

		return $ret;
	}

	function showUpdate(){

		$content = new SI_VContainer();
		$content->addItem(new SI_Title(static::$UpdateMsg,4));
		
		$form = new SI_Form($this->datasWith('update','show'), SC_Main::$RENDERER->action($this,'processupdate'));


		//$form->addItem( new SI_HContainer([  new SI_Submit(SC_Main::L('Create')), new SI_CancelButton()  ]) );
		$form->addItem( new SI_Submit(SC_Main::L('Update')) );
		$form->addItem( new SI_CancelButton() );
		
		$content->addItem($form);

		//SC_Main::$PERMISSIONS->menu();

		$page = new SI_systemScreen($content,SC_Main::L(static::$UpdateBtnMsg) );
		
		return $page;
	}

	function showUpdateSelect($template = null, $output='AE_basicPage', $messages=null){
		$action = $this->renderer->action($this,'processUpdateSelect');
		return $this->renderer->render($this,'showUpdate', $output,$template, $action);
	}


	// function processUpdateSelect($nextStep = null) {
	// 	try {
	// 		$this->fillFromRequest();
	// 		$this->validateForDB();
	// 	} catch (SC_ElementValidationException $ev) {
	// 		$data = array();
	// 		foreach ($ev->datasValidationMessages() as $key => $value) {
	// 			$data[] = array(
	// 			'func' => 'showValidationMessages',
	// 			'args' => array($key, $value[0])
	// 			);
	// 		}
	// 		$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => $data
	// 		);
	// 		$return = json_encode($return);
	// 		return $return;
	// 	}
	// 	try {
	// 		if ($this->update()) {		
	// 				return $this->makeSelection();
	// 		} else {
	// 			// @todo: error handling
	// 			user_error('Cannot create in DS!', E_USER_ERROR);
	// 		}
	// 	} catch (\PDOException $ev) {
	// 		//user_error($ev->errorInfo[1]);
	// 		//@todo handdle the exising ID (stirngID) in the DS
	// 		user_error($ev);
	// 	}
	// }

	/**
	 * Processes the update of an Element record and returns a JSON response.
	 *
	 * This method prepares the necessary data and delegates the actual update
	 * and JSON response generation to {@see processUpdateJSon()}.
	 * It sets a success message and determines the next step (typically redirecting to the admin view)
	 * before calling the JSON processing method.
	 *
	 * @return string A JSON string containing commands for the renderer, typically a redirect command.
	 */
	function processUpdate(){
		$this->renderer->setMessage(static::$UpdatedMsg);
		$nextStep = $this->renderer->action($this,'showAdmin','id',static::$UpdatedMsg);
		return $this->processUpdateJSon($nextStep);
	}

	/**
	 * Generates the proper response JSON for the update of an Element .
	 *
	 * This method handles incoming request data, validates it, attempts to update
	 * an existing record for the element in the data storage, and returns a JSON response
	 * indicating the result or validation errors.
	 *
	 * On successful update, it returns a JSON object containing commands for the
	 * renderer, typically a command to redirect to the next step.
	 * If validation fails, it returns a JSON object containing commands for the
	 * renderer to display validation messages for specific data attributes.
	 * If a database error occurs during update, it triggers a user error.
	 *
	 * @param string|null $nextStep Optional. The URL or action to redirect to after successful update. Defaults to null.
	 * @return string A JSON string containing commands for the renderer or error information.
	 * @throws SC_ElementValidationException If data validation fails before attempting to update the element. (This is caught internally and returned as JSON)
	 * @throws PDOException If a database error occurs during the update process. (This is caught internally and triggers a user error)
	 */
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
				user_error(static::$UpdateError);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}
	}

	/**
	 * Renders a confirmation form for deleting an Element.
	 *
	 * This method displays the details of the element to be deleted and provides
	 * a form with "Delete" and "Cancel" buttons to confirm or abort the deletion.
	 * The element's data is first fetched from the data storage using its ID.
	 *
	 * @return \SimplOn\Interface\SI_systemScreen A system screen object containing the deletion confirmation form.
	 */
	function showDelete(){
		$this->fillFromDSById();
		$content = new SI_VContainer();
		$content->addItem(new SI_Divider(static::$DeleteMsg));

		foreach($this->datasWith('view','objects') as $data){
			$content->addItem(new SI_HContainer([$data->label().':',$data->showView()],'r l','minmax(7rem, 1fr) 10fr'));
		}

			$form = new SI_Form([$this->id], SC_Main::$RENDERER->action($this,'processDelete'));
			$form->addItem( new SI_Submit(SC_Main::L('Borrar')) );
			$form->addItem( new SI_CancelButton() );
		$content->addItem($form);

		$page = new SI_systemScreen($content,SC_Main::L(static::$ViewMsg) );
		SC_Main::render($page);
		
	}

	/**
	 * Processes the deletion of an Element record.
	 *
	 * This method handles the request to delete an element. It performs the deletion
	 * operation in the data storage and returns a JSON response indicating the result.
	 * Upon successful deletion, the JSON response typically contains commands for
	 * the renderer to redirect to another page (e.g., the admin view) and potentially
	 * display a success message. If the deletion fails, it triggers a user error.
	 *
	 * @param string|null $nextStep Optional. The URL or action to redirect to after successful deletion. Defaults to the admin view with a deleted message.
	 * @param string $format Optional. The desired response format. Defaults to 'json'. (Note: Currently only JSON output is implemented).
	 * @return string A JSON string containing commands for the renderer or error information.
	 * @throws PDOException If a database error occurs during the deletion process. (Caught internally and triggers a user error)
	 */
	function processDelete($nextStep = null, $format = 'json') {
		//esta pendienmte el tema de nexstep o el json y las modalidades de delete

		$this->renderer->setMessage($this->Name().' borrado correctamente');
		if(!$nextStep){ $nextStep = $this->renderer->action($this,'showAdmin','id',static::$DeletedMsg ); }
		$data = array(
			// array(
			// 'func' => 'removeHtml',
			// ),
			// array(
			// 'func' => 'closeLightbox',
			// ),
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
			user_error(static::$DeleteError);
		}
	}

	/**
	 * Processes a search request for the Element.
	 *
	 * This method handles incoming search criteria, performs the search using the
	 * configured DataStorage, formats the results into a table, and renders the
	 * table for display.
	 *
	 * @return mixed The rendered search results, typically an HTML string representing a table.
	 * @throws SC_ElementValidationException If there are validation errors with the search criteria.
	 */
	function processSearch() {
		try {
			//$search = new SE_Search(array($this->getClass()));

			$results = $this->dataStorage->readElements($this, 'Elements', $position, $limit);
			
			$params = [];
			// $params = ['simplonCols'=>'show'];
			$results = new SD_Table('Results',$params,$results);

			return $this->renderer->renderData($results,'showView',null,1);
			// $results = $search->getResults($this->toArray());
			// foreach($results as $row) 
			//  	$ret .= var_export($row, true);
			// return $ret;
		} catch (SC_ElementValidationException $ev) {
			user_error($ev->datasValidationMessages());
		}
	}

	/**
	 * Returns the main value of the Element in a format adecute for human reading.
	 *
	 * @return mixed The value of the Element.
	 */
	function viewVal(){
		return $this->val();
	}
	

	/**
	 * Obtain an array with all results from Element's table to be used in SD_ElementContainer.
	 * 
	 * This method uses the search functionality to retrieve all records for the Element
	 * @return array An array containing all results from the Element's data storage.
	 */
	function Elements(){
		$search = new SE_Search(array($this->getClass()));
		$colums = array_merge($this->datasWith("embeded"));

		return $search->getResults($this->toArray());
	} 
	
	/**
	 * Processes a selection request for the Element.
	 *
	 * This method typically handles selecting an element from a list, often used
	 * in conjunction with ElementContainer or similar components. It fills the
	 * element from the request, performs a search, and returns the processed search results.
	 *
	 * @return mixed The processed search results, typically in a format suitable for selection interfaces.
	 */
	// function processSelect() {
	// 	$this->fillFromRequest();
	// 	$search = new SE_Search(array($this->getClass()));
	// 	// $colums = array_merge( $this->datasWith("list"), array("selectAction","parentClass") );
	// 	//@todo do not add selectAction here but just include it in the listing using VCRSL when adding it on the fly
	// 	$colums = array_merge($this->datasWith("list"), array("selectAction"));
	// 	return $search->processSearch($this->toArray(), $colums);
	// }

	/**
	 * Processes a report request for the Element.
	 *
	 * This method prepares and executes a report based on the Element's data.
	 * It typically involves setting search flags, processing request data,
	 * retrieving results from the data storage using a reporting mechanism,
	 * and generating formatted output with paging.
	 *
	 * @param int $start The starting record number for the report.
	 * @param int $limit Optional. The maximum number of records to include in the report page. Defaults to null (no limit).
	 * @return string The rendered report output, typically an HTML string containing the report table and paging links.
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

	/**
	 * Renders the administration view for the Element.
	 *
	 * This method generates the main administrative interface for managing Element records.
	 * It includes a search form, a list or table displaying existing records,
	 * and links or buttons for creating, viewing, updating, and deleting elements.
	 *
	 * @return \SimplOn\Interface\SI_systemScreen A system screen object containing the administration interface.
	 */
	function showAdmin(){

		$this->fillFromRequest();

		$content = new SI_VContainer();
		$content->addItem(new SI_Link(SC_Main::$RENDERER->action($this,'showCreate'), static::$CreateMsg,'addIcon.svg'));

		$content->addItem(new SI_Divider(new SI_Title(static::$SearchMsg,5)));

			$form = new SI_Form($this->datasWith('search','show'), SC_Main::$RENDERER->action($this,'showAdmin'));
			$form->ajax = false;
			$form->addItem( new SI_Submit(SC_Main::L('Search')) );
			$form->addItem( new SI_CancelButton() );
		$content->addItem($form);

			$elements = $this->dataStorage->readElements($this, 'Elements');

				$dataPrepare = function(){
					$viewAction = new SD_Action('view action','showView',SC_Main::L('View'),'viewIcon.svg');
					$updateAction = new SD_Action('update action','showUpdate',SC_Main::L('Update'),'editIcon.svg');
					$deleteAction = new SD_Action('delete action','showDelete',SC_Main::L('Delete'),'deleteIcon.svg');
						
					
					$viewAction->parent($this->parent());
					$updateAction->parent($this->parent());
					$deleteAction->parent($this->parent());
					$layout = new SI_HContainer([$viewAction->showView(),$updateAction->showView(),$deleteAction->showView()],'c c c');
					
					$this->layouts(['showView'=>$layout]);
				};
				$actions = new SD_ComplexData('Acciones',$dataPrepare,null);
			$table = new SI_Table($elements, ['acciones' => $actions], 'list' );
		$content->addItem($table);

		$content->addItem(new SI_Divider());

		//$menu = new SI_SystemMenu();
		//$menu->addItem(new SI_Link(SC_Main::$RENDERER->action($this,'showSearch'), static::$SearchMsg));
		$page = new SI_systemScreen( $content,static::$AdminMsg );
		return $page;
	}

	/**
	 * Processes the administration view's search request and returns the results.
	 *
	 * This method is typically called when the search form in the administration view
	 * is submitted. It handles the search criteria, retrieves the relevant data from
	 * the data storage, and formats it for display in the administration view,
	 * including handling paging.
	 *
	 * @param int $start Optional. The starting record number for the results. Defaults to 1.
	 * @param int $limit Optional. The maximum number of records to include per page. Defaults to null (no limit).
	 * @return string The rendered search results for the administration view, typically an HTML string containing a table and paging links.
	 */
	function processAdmin($start = 1, $limit = null) {

		if ($start < 1) {
			$start = 1;
		}			
		$position = ($start - 1) * $limit;
		$this->fillFromRequest();
		$search = new SE_Search(array($this->getClass()));
		//$admin = $this->renderer->encodeURL(array(), 'showAdmin');

		$colums = array_merge($this->datasWith("list"), array( "viewAction", "updateAction","deleteAction"));

		$tableAdmin = $search->processSearch($this->toArray(), $colums, $position, $limit);

		$totalRecords = $search->total;
		$links = $this->makePaging($start, $limit, $totalRecords);
		return $tableAdmin.$links;
	}

	/**
	 * Generates pagination links for navigating through a list of results.
	 *
	 * This method creates HTML anchor tags for navigating between pages of results,
	 * commonly used in administrative views or reports where results are
	 * displayed in limited chunks. It calculates the total number of pages
	 * based on the total number of records and the limit per page, and generates
	 * links for each page, as well as "Prev" and "Next" links.
	 *
	 * @param int $start The current page number (starting from 1).
	 * @param int $limit The maximum number of records to display per page.
	 * @param int $totalRecords The total number of records available across all pages.
	 * @return string An HTML string containing the pagination links.
	 */
	// function makePaging($start, $limit, $totalRecords){
	// 	$links = "";
	// 	$totalElements = $totalRecords;
	// 	$division = $limit ? ceil($totalElements / $limit) : 0;
	// 	if ($division > 1) {
	// 		for ($i = 1; $i <= $division; $i++) {
	// 			$links.= "<a class = 'SimplOn_pag' href=\"/$i/$limit\">$i<\a> ";
	// 		}
	// 		$next = $start + 1;
	// 		$prev = $start - 1;
	// 		if ($start > '1') {
	// 			$links = "<a class = 'SimplOn_pag' href=\"/$prev/$limit\">Prev<\a> " . $links;
	// 		}
	// 		if ($next < $i) {
	// 			$links.= "<a class = 'SimplOn_pag' href=\"/$next/$limit\">Next<\a> ";
	// 		}
	// 	}
	// 	return $links;
	// }

	/**
	 * Generates an associative array mapping Data attribute labels to their names.
	 *
	 * This method iterates through the Element's Data attributes. For each attribute
	 * that is an instance of SD_Data, is configured to be fetched (fetch() is true),
	 * has a non-empty label, and is not an instance of SD_AutoIncrementId,
	 * it adds an entry to the result array where the key is the attribute's label
	 * and the value is the attribute's name.
	 *
	 * @return array An associative array where keys are Data attribute labels and values are Data attribute names.
	 */
	function getLabelsAndNames(){
		$data = 'SD_ata';
		$numId = 'SD_AutoIncrementId';
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
	 * Stores the 'search' flag status for all relevant Data attributes.
	 *
	 * This method iterates through the Element's Data attributes that are instances
	 * of SD_Data, are configured to be fetched (fetch() is true), and are not
	 * instances of SD_AutoIncrementId. It records the current boolean value of
	 * the 'search' flag for each such attribute in an associative array. This is
	 * useful for temporarily changing search flags and later restoring them.
	 *
	 * @return array An associative array where keys are Data attribute names and values are their current 'search' flag status (boolean).
	 */
	// function storeAllFlags() {
	// 	$type = 'SD_AutoIncrementId';
	// 	$flagsName = array();
	// 	$flagsStatus = array();
	// 	foreach ($this->dataAttributes() as $dataName) {
	// 		$valFetch = $this->{'O' . $dataName}()->fetch();
	// 		if($valFetch === true){
	// 			if (!($this->{'O' . $dataName}() instanceof $type)) {
	// 				$status = $this->{'O' . $dataName}()->search();
	// 				$flagsName[] = $dataName;
	// 				$flagsStatus[] = $status;
	// 			}
	// 		}    
	// 	}
	// 	$flagStock = array_combine($flagsName, $flagsStatus);
	// 	return $flagStock;
	// }

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

	// function changeCurrentFlags($chosenDatas = array(), $flag, $status = true) {
	// 	$type = 'SD_AutoIncrementId';
	// 	if (isset($chosenDatas)) {
	// 		foreach ($chosenDatas as $data){
	// 			if(method_exists($this,$flag))
	// 			$this->{'O' . $data}()->$flag($status);
	// 		}
	// 	} else {
	// 		foreach ($this->dataAttributes() as $dataName) {
	// 			$valFetch = $this->{'O' . $dataName}()->fetch();
	// 			if( $valFetch === true){
	// 				if (!($this->{'O' . $dataName}() instanceof $type)) {
	// 					if(method_exists($this,$flag))
	// 						$this->{'O' . $dataName}()->$flag($status);
	// 				}
	// 			}    
	// 		}
	// 	}
	// }

	/**
	 * restoreAllFlags
	 *
	 * Restore the flags of all the data with their original value in the Search view.
	 * 
	 * @param array $flagStock
	 */
	// function restoreAllFlags($flagStock = array()) {
	// 	foreach ($flagStock as $dataName => $value) {
	// 		$this->{'O' . $dataName}()->search($value);
	// 	}
	//}

	/**
	 * Generates the default filter criteria string based on searchable and fetched Data attributes.
	 *
	 * This method iterates through the Element's Data attributes and builds a filter criteria string
	 * including attributes that are marked as searchable (`search()`) and fetchable (`fetch()`)
	 * and have a non-empty value. The criteria are joined by the specified operator.
	 *
	 * @param string $operator Optional. The logical operator to use between criteria (e.g., 'AND', 'OR'). Defaults to 'AND'.
	 * @return string The default filter criteria string.
	 */
	function defaultFilterCriteria($operator = 'AND') {
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
	 * Gets or sets the filter criteria string for searching in the dataStorage.
	 *
	 * If a criteria string is provided, it sets the internal `filterCriteria` property.
	 * If no criteria string is provided, it returns the currently set filter criteria.
	 * If no filter criteria has been set, it generates the default filter criteria using `defaultFilterCriteria()`.
	 *
	 * The criteria string can contain placeholders that will be replaced:
	 * - `.dataName`: Replaced by the `filterCriteria` defined in the corresponding Data attribute.
	 * - `:dataName`: Replaced by the current value of the corresponding Data attribute.
	 *
	 * @param string|null $filterCriteria Optional. The filter criteria string to set. If null, the current criteria is returned.
	 * @return string The filter criteria string.
	 * @see SC_Element::$filterCriteria
	 * @see defaultFilterCriteria()
	 */
	function filterCriteria($filterCriteria = null) {
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

	/**
	 * Gets or sets the criteria string to use for selecting elements to delete from the dataStorage.
	 *
	 * If a criteria string is provided, it sets the internal `deleteCriteria` property.
	 * If no criteria string is provided, it returns the currently set delete criteria.
	 * If no delete criteria has been set, it generates the default delete criteria using `defaultDeleteCriteria()`.
	 *
	 * The criteria string can contain placeholders that will be replaced, similar to `filterCriteria()`.
	 *
	 * @param string|null $deleteCriteria Optional. The delete criteria string to set. If null, the current criteria is returned.
	 * @return string The delete criteria string.
	 * @see SC_Element::$deleteCriteria
	 * @see defaultDeleteCriteria()
	 */
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

	/**
	 * Generates the default delete criteria string based on fetched Data attributes that have values.
	 *
	 * This method iterates through the Element's Data attributes and builds a delete criteria string
	 * including attributes that are marked as fetchable (`fetch()`) and have a non-empty value.
	 * The criteria are joined by the specified operator.
	 *
	 * @param string $operator Optional. The logical operator to use between criteria (e.g., 'AND', 'OR'). Defaults to 'AND'.
	 * @return string The default delete criteria string.
	 */
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
				if ($data->hasAttribute('parent') && empty($data->parent()) ) {
					$data->parent($parent);
				}
			}
		}
	}

	/**
	 * Assigns the name attribute of each Data object within the Element instance.
	 *
	 * This method iterates through the properties of the Element. If a property
	 * is an instance of `SD_Data` and its `name` property is not already set,
	 * this method sets the Data object's `name` property to the name of the
	 * attribute in the Element instance.
	 *
	 * This is useful for Data objects to know their corresponding attribute name
	 * within the parent Element, which can be used for generating and handling
	 * filter criteria and other operations.
	 */
	public function assignDatasName() {
		foreach ($this as $name => $data) {
			if (($data instanceof SD_Data) && empty($data->name())) {
				$data->name($name);
			}
		}
	}

	/**
	 * Displays a stripped-down, embedded representation of the Element.
	 *
	 * This method retrieves Data attributes marked with the "embeded" flag.
	 * If the Element has an ID, it first fills the Element's data from the
	 * data storage. It then iterates through the embedded Data attributes
	 * and concatenates their stripped-down embedded representations,
	 * typically suitable for displaying within a compact space.
	 *
	 * @return string A string containing the stripped-down embedded representation of the Element.
	 */
	public function showEmbededStrip(){
		/** @var string[] $embeadedDatas */
		$embeadedDatas = $this->datasWith("embeded");
		$ret = '';

		if ($this->getId() || $this->getId() === 0) {$this->fillFromDSById();}
		foreach($embeadedDatas as $embeadedData){
			
			$ret .= $this->{$embeadedData}->showEmbededStrip().' ';
		}		
		return trim($ret);
	} 

	//RSL 2022 #todo 
	/**
	 * Adds a Data attribute to the Element instance dynamically.
	 *
	 * This method allows adding new Data objects to an Element instance at runtime.
	 * It assigns the provided `$attribute` (an instance of `SD_Data`) to the
	 * Element instance as a property with the name specified by `$attributeName`.
	 * It also sets the name of the Data attribute itself and adds it to the
	 * list of data attributes for the Element.
	 *
	 * @param string $attributeName The name of the attribute to add to the Element.
	 * @param SD_Data $attribute The SD_Data object to add as an attribute.
	 * @return SC_Element The current Element instance, allowing for method chaining.
	 */
	public function addData($attributeName, SD_Data $attribute) {
		///RSL 2022 fix de error que tronaba en ciertas lecturas por falta name en el atributo
		$attribute->name($attributeName);
		static::$onTheFlyAttributes[$attributeName]=$attribute;
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

	/**
	 * Removes a dynamically added Data attribute from the Element instance.
	 *
	 * This method removes a Data attribute that was previously added dynamically
	 * using `addData()`. It unsets the property from the Element instance,
	 * removes it from the static list of on-the-fly attributes, and updates
	 * the list of data attributes for the current instance.
	 *
	 * @param string $attributeName The name of the attribute to remove from the Element.
	 * @return SC_Element The current Element instance, allowing for method chaining.
	 */
	public function removeData($attributeName) {
		if ($attribute instanceof SD_Data) {
			unset($obj->$attributeName);
			unset(static::$onTheFlyAttributes[$attributeName]);
			SC_Main::removeData($this->getClass(), $attributeName);
			$this->dataAttributes = $this->attributesTypes();
		}
		return $this;
	}

	/**
	 * Adds dynamically defined Data attributes to the current Element instance.
	 *
	 * This method iterates through the static list of on-the-fly attributes
	 * (`static::$onTheFlyAttributes`) and clones each attribute, assigning it
	 * as a property to the current Element instance. It also ensures that the
	 * cloned attribute is added to the instance's list of data attributes.
	 * This is typically called during the Element's construction to include
	 * attributes defined dynamically.
	 */
	public function addDatas() {
		foreach (static::$onTheFlyAttributes as $attributeName => $attribute) {
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
		$this->clear( $this->fieldId() );
	}

	/**
	 * function makeSelection - this function pass the arguments to javascript file to 
	 * display the light box.
	 * @param type $id
	 */
	// function makeSelection(){

	// 	$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => array(
	// 			// array(
	// 			// 	'func' => 'changeValue',
	// 			// 	'args' => array($this->getId())
	// 			// ),
	// 			array(
	// 				'func' => 'changePreview',
	// 				'args' => array($this->showEmbededUpdateInput(null,true))
	// 			),
	// 			array(
	// 				'func' => 'closeLightbox'
	// 			),
	// 		)
	// 	);
	// 	header('Content-type: application/json');
	// 	echo json_encode($return);
	// }

	// function makeChangeSelection(){
	// 	$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => array(
	// 			array(
	// 				'func' => 'appendContainedElement',
	// 				'args' => array($this->showEmbededAppendInput(null,true))
	// 			),
	// 			array(
	// 				'func' => 'closeLightbox'
	// 			),
	// 		)
	// 	);
	// 	header('Content-type: application/json');
	// 	echo json_encode($return);
	// }

	// function makeSearchAdition(){
	// 	$return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => array(
	// 			array(
	// 				'func' => 'changeValue',
	// 				'args' => array($this->getId())
	// 			),
	// 			array(
	// 				'func' => 'changePreview',
	// 				'args' => array($this->showEmbededSearchInput(null,true))
	// 			),
	// 			array(
	// 				'func' => 'closeLightbox'
	// 			),
	// 		)
	// 	);
	// 	header('Content-type: application/json');
	// 	echo json_encode($return);
	// }

	/**
	 * Gets or sets the singular name of the Element.
	 * If no name is provided, it attempts to derive the name from the class name.
	 * The name is then passed through the localization function.
	 *
	 * @param string|null $name Optional. The singular name to set. If null, the current name is returned or derived.
	 * @param bool $captalize Optional. Whether to capitalize the first letter of the returned name. Defaults to false.
	 * @return string The singular name of the Element.
	 */
	public function Name($name = null, $captalize = false){
		if(!$name){
			if(!$this->Name){
				$this->Name = explode('_',get_class($this));
				array_shift( $this->Name  );
				$this->Name = implode(' ', $this->Name );
				$this->Name = strtolower(trim(preg_replace('/[A-Z]/', ' $0', $this->Name)));
			}
			if($captalize){
				return SC_MAIN::L(ucfirst($this->Name));
			}else{
				return SC_MAIN::L($this->Name);
			}
		}else{
			$this->Name = $name;
		}
	}

	/**
	 * Gets or sets the plural name of the Element.
	 * If no plural name is provided, it attempts to derive it by appending 's' to the singular name.
	 * The plural name is then passed through the localization function.
	 *
	 * @param string|null $namePlural Optional. The plural name to set. If null, the current plural name is returned or derived.
	 * @param bool $captalize Optional. Whether to capitalize the first letter of the returned plural name. Defaults to false.
	 * @return string The plural name of the Element.
	 */
	public function NamePlural($namePlural = null, $captalize = false){
		if(!$namePlural){
			if(!$this->NamePlural){
				$this->NamePlural = strtolower(trim($this->Name.'s'));
			}
			if($captalize){
				return SC_MAIN::L(ucfirst($this->NamePlural));
			}else{
				return SC_MAIN::L($this->NamePlural);
			}
		}else{
			$this->NamePlural = $namePlural;
		}
	}

	/**
	 * Returns the value of the Element's unique identifier field.
	 * The field used as the ID is determined by the `fieldId` property.
	 *
	 * @return mixed The value of the unique identifier field, or null if not set.
	 * @see SC_Element::$fieldId
	 */
	function getId() {
		//user_error($this->fieldId());
		return $this->{$this->fieldId()}();
	}

	/**
	 * Sets the value of the Element's unique identifier field.
	 * The field used as the ID is determined by the `fieldId` property.
	 *
	 * @param mixed $id The value to set as the unique identifier.
	 * @return SC_Element The current Element instance, allowing for method chaining.
	 * @see SC_Element::$fieldId
	 */
	function setId($id) {
		$this->{$this->fieldId()}($id);
		return $this;
	}

	/**
	 * Returns an array representation of the Element, mapping each Data attribute's name
	 * as the key and the data attribute's current value as the value.
	 *
	 * @return array An associative array where keys are Data attribute names and values are their current values.
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
	 * This method iterates through all the Data attributes of the Element and calls the specified method on each Data object.
	 * It collects and returns an array of all non-null responses from these method calls.
	 *
	 * @param string $method The name of the method to call on each Data object. This method must be common to all relevant Data objects.
	 * @return array An array containing the non-null results of calling the specified method on each Data attribute.
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
	/**
	 * Returns an array containing the names of all Data attributes belonging to this Element instance.
	 *
	 * If the list of data attributes has not been initialized, it calls `attributesTypes()` to generate it.
	 *
	 * @return array An array of strings, where each string is the name of a Data attribute.
	 */
	function dataAttributes() {
		if (!$this->dataAttributes) {
			$this->dataAttributes = $this->attributesTypes();
		}
		return $this->dataAttributes;
	}

	/**
	 * Returns an array of attribute names (properties) of the Element instance
	 * that are instances of the specified class type.
	 *
	 * @param string $type The class type to filter attributes by. Defaults to 'SD_Data'.
	 * @return array An array of attribute names that are instances of the specified type.
	 */
	function attributesTypes($type = 'SD_Data') {
		$a = array();
		foreach ($this as $name => $data) {
			if ($data instanceof $type) {
				$a[] = $name;
			}
		}
		return @$a ? : array();
	}

	/**
	 * Returns an array of attribute names (properties) of the Element instance
	 * that are instances of the specified class type and have a specific flag or method returning true.
	 *
	 * @param string $type The class type to filter attributes by. Defaults to 'SD_Data'.
	 * @param string $what The name of the method or attribute to check for a true value. Defaults to 'fetch'.
	 * @return array An array of attribute names that match the criteria.
	 */
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
	 * Returns an array of Element Data objects or their representations based on a specified flag and return type.
	 *
	 * This method iterates through the Element's Data attributes and filters them based on whether they have the flag or method specified by `$what` returning true.
	 * The return type of the array can be controlled by the `$retType` parameter.
	 *
	 * @param string $what The name of the VCRSL flag or method to check on each Data object.
	 * @param string $retType Optional. The desired return type. 'strings' returns an array of Data attribute names, 'show' returns an array of the results of calling the 'show' method with the flag name (e.g., showView), and 'objects' returns an array of the Data objects themselves. Defaults to 'strings'.
	 * @return array|string An array of Data attributes (names, show output, or objects) that have the specified flag set, or the string 'NotVCRSL' if the flag is not a recognized VCRSL method.
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
					}elseif($retType == 'show'){																
						$output[] = $this->$data->{'show'.ucfirst($what)}();
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

	/**
	 * Returns a string representation of the Element.
	 *
	 * This method is automatically called when an Element object is treated as a string.
	 * By default, it returns the result of calling the `showView()` method, which provides a human-readable representation of the Element.
	 *
	 * @return string A string representation of the Element, typically the output of `showView()`.
	 */
	public function __toString(){
		return $this->showView();
	}

	/**
	 * Returns a string containing debugging information about the Element.
	 *
	 * This method provides a simple way to inspect the current state of the Element, including its class name and the names and values of its Data attributes.
	 *
	 * @return string A string containing the class name of the Element and a list of its Data attributes with their current values.
	 */
	public function debug(){
		$datas = $this->dataAttributes();
		$ret = $this->getClass();
		foreach($datas as $data){
			$ret .= "\n".$data.' :: '.$this->$data()."";
		}
		return $ret;
	}

}