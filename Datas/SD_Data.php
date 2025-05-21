<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/



/**
 * @todo Agregar posibilidad de especificar indices de b�squeda:
 * una posible soluci�n es la de usar el parametro $flags usando el metodo search()
 * de ahi entender si un dato se va a usar para la b�squeda
 * y tambi�n dar la posibilidad de un "override" para especificar
 * de modo explicito, si se quiere o no un indice en dicho dato.
 * Basandose sobre el tipo de dato, elegir el tipo de indice
 * o basarse sobr un atributo del dato.
 */


/**
* __construct($label=null,$campo=null,$inputName=null,$val=null)	Si los parametros estan vacios causará que se use la clase
* __toString()										Regresa el campo que se debe usar para generar consultas a la Fuente de datos - En SQL puede permite (SELECTS) complejos usando la asigancion " AS "
* inputName($prefijo=null)											Regresa la str del name del input para los formularios
* strValorQuery()													Valor como se debe usar en los Querys SQL  (es decir con comillas en el caso de los strings)
* strQueryValueColum()												Da la asiganacion de valor para los Updates de SQL
* HTML()															Regresa el valor como debe imprimirse en HTML
* createInput($prefijo=null,$printval=false)						Input a desplegarse cuando se desea moar un formaulario de captura del dato
* updateInput($prefijo=null,$printval=true)							Input a desplegarse cuando se desea moar un formaulario de edicion del dato
* InputBuscador($prefijo)											 para de los campos de busqueda del buscador
* Where()															 para armar las consultas la seccion Where de las consultas SQL
* Label()															Regresa la  para poner como etiqueta antes de los inputs
* Header()															Regresa los necesariso para inlcuir librerias JA
**/



/**
* Componente para basico definir campos de los elementos
*
* @version	1.0
* @author	Ruben Schaffer
* @todo		-Poner funciones UTF8 en setval
* 			-Agregar excepciones: al asignar valor(si el valor es viable), al llenar desde fuente de datoso y/o REQUEST (validad si el dato esta en el arreglo)
* 			-Funciones set de -labael campo campoAS e imputname- que verifique que tiene estan en l formato correcto
*/




abstract class SD_Data extends SC_BaseObject {

	protected


	$printValInInput = true,

	/**
	 * Data value
	 *
	 * @access protected
	 * @var mixed
	 */
	$val,
	
	
	/**
	 * Data name or label as it must be presented to human readers (in forms, html tables, etc)
	 *
	 * @access protected
	 * @var ing
	 */
	$label,
	
	
	/**
	 * indicates if the SimplOndata must be used when generating the default HTML template
	 * @var boolean
	 */
	$view = true,
	
	/**
	 * indicates if the SimplOndata must be used in the add(capture) from
	 * @var boolean
	 */
	$create = true,
	
	/**
	 * indicates if the SimplOndata must be used in the update from
	 * @var boolean
	 */
	$update = true,
	
	/**
	 * indicates if the SimplOndata must be used in the search from
	 * @var boolean
	 */
	$search = false,
	
	/**
	 * indicates if the SimplOndata must be used when several items are listed on a html table or list
	 * @var boolean
	 */
	$list = false,
	
	/**
	 * indicates if the SimplOndata must have a value in order to allow the storage of it's dataParent SimplOnelement
	 * @var boolean
	 */
	$required = false,
	
	
	/**
	 * indicates if the SimplOndata must have a value in order to allow the storage of it's dataParent SimplOnelement
	 * @var boolean
	 */
	$fetch = true,

	
	/**
	 * RSL 2022 aded "e Emebded"
	 * indicates if the SimplOndata must bre printed when its parrent element i contained within another element
	 * @var boolean
	 */
	$embeded = false,

	/**
	 * RSL 2022 aded "e Emebded"
	 * indicates if the SimplOndata must bre printed when deliting the element
	 * @var boolean
	 */
	$delete = false,
	
	$name,

    
	/** @var SC_Element $parent  */
    $parent,

    $autoIncrement=false,
	

	$fixedValue = false, // to control if the value is not to be changed
	$renderOverride = false,
	
	 $tip,
	 $tipInline,
	 $validationRequired,
	 $invalidValueMessage,

	/** @var SR_htmlJQuery */
	$renderer = null,
	
	/**
	 * The name of the input field for forms.
	 *
	 * @access protected
	 * @var string
	 */
	$inputName,
	/**
	 * search operands:
	 * >
	 * <
	 * >=
	 * <=
	 * ==
	 * !=
	 * ~=
	 * ^=
	 * $=
	 * 
	 * ***********
	 * 
	 * Complex operations are just simple operations 
	 * jointed with ANDs and ORs, so it'd be possible
	 * to create "Operators" objects that defines those
	 * complex operations.
	 * 
	 * (name > val1) OR (name < val2)
	 * 
	 * a <= x <= b
	 * a > x || x < b
	 * @var string
	 */
	$filterCriteria = 'name == :name';

	
	/**
	 * Constructs a new SD_Data instance.
	 *
	 * Initializes the Data object with a label, optional flags, an initial value, and filter criteria.
	 * Sets default validation messages for required fields and invalid values using the localization function.
	 * Applies the provided flags if available.
	 *
	 * @param string|null $label Human readable description of the attribute, intended to appear in forms and tables. Defaults to null.
	 * @param string|null $flags VCUSRLF flags indicating where the Data will be used within an Element. {@see dataFlags}. Defaults to null.
	 * @param mixed $val If provided, sets the initial value of the data. Defaults to null.
	 * @param string|null $filterCriteria Determines how the data value will be compared/evaluated in queries to the Data Storage. Defaults to null.
	 */
	public function __construct($label=null, $flags=null, $val=null, $filterCriteria=null)
	{
		$this->val = $val;
		$this->label=$label;
		
		$this->validationRequired = SC_Main::L('This field is required');
		$this->invalidValueMessage = SC_Main::L('Invalid value');
		
        $this->filterCriteria($filterCriteria);
		if($flags)
		{
			$this->dataFlags($flags);
		}
		//$this->construct($label, $flags, $val, $filterCriteria);
	}
	

	/**
	 * LLama al reneder del dato para todas las llamadas que inicia con show y directo a la funcion el resto de funciones 
	 * Esto permite llamar a  interfaces del dato sin que sean funciones, solo conque esten declaradas en la platilla de interfaz
	*/
	// public function __call($name, $arguments) {
	// 	if(substr($name, 0, 4) === "show"){

	// 		array_unshift($arguments,$name);
	// 		array_unshift($arguments,$this);
	// 		return call_user_func_array(array($this->renderer(), 'render'), $arguments);
	// 	}else {
	// 		return parent::__call($name, $arguments);
	// 	}
	// }

	/**
	* User defined constructor, called within {@link __constructor()},
	* Useful in child clasess to define any class SD_specific construction code without overwritning the __construct method
	*/
  // public function construct($label=null, $flags=null, $val=null, $filterCriteria=null) {}
 
   
	
    /**
	 * Gets or sets the renderer for this Data object.
	 * If no renderer is set for this Data object, it attempts to get the renderer from its parent Element.
	 * If no parent or renderer is found, it defaults to the global renderer.
	 *
	 * @param SR_htmlJQuery|null $renderer Optional. The renderer instance to set. If null, the current renderer is returned.
	 * @return SR_htmlJQuery The renderer instance.
	 */
   public function renderer($renderer=null){
		if(isset($val)){
			$this->renderer = $renderer;
		}else{
			if($this->renderer){
				return $this->renderer;
			}elseif($this->parent){
				return $this->parent->renderer();
			}else{
				return SC_Main::$RENDERER;
			}
		}
	}

	/**
	 * Sets and fixes the value of the Data object, preventing further changes.
	 * It also sets a render override to 'showFixedValue', thats used on the form interfaces.
	 *
	 * @param mixed $val The value to set and fix.
	 */
	public function fixValue($val){
		$this->val($val);
		$this->fixedValue = true;
		$this->renderOverride = 'showFixedValue';
	}

	/**
	 * Returns a stripped-down, embedded representation of the Data value.
	 * By default, this returns the raw value. Child classes can override this for specific formatting.
	 *
	 * @return mixed The stripped-down embedded value.
	 */
	public function showEmbededStrip(){
		return $this->val();
	}

	/**
	 * Gets or sets the value of the Data object.
	 * If a value is provided, it attempts to set it after validation, unless the value is fixed.
	 * If no value is provided, it returns the current value.
	 *
	 * @param mixed $val Optional. The value to set. Defaults to null.
	 * @return mixed The current value of the Data object if no value is provided for setting.
	 * @throws SC_DataValidationException If a value is provided but is not valid according to the `isValid` method.
	 */
	public function val($val=null){
		if(isset($val)){
			if(!$this->fixedValue && $this->isValid($val)){
				$this->val = $val;
			}elseif(!$this->isValid($val)){
				throw new SC_DataValidationException($this->invalidValueMessage());
			}
		}else{
			return $this->val;
		}
	}

	/**
	 * Validates the given value.
	 * This is a base implementation that always returns true. Child classes should override this method
	 * to provide specific validation logic for their data type.
	 *
	 * @param mixed $val The value to validate.
	 * @return bool True if the value is valid, false otherwise.
	 */
	public function isValid($val){
		return true;
	}

	/**
	 * Returns the value of the Data object for viewing.
	 *
	 * This method provides the raw value suitable for simple display.
	 *
	 * @return mixed The current value of the Data object.
	 */
	public function viewVal(){
		return $this->val();
	}



	/**
	 * Displays the data for a single item view.
	 *
	 * This method is typically used to render the data in a detailed view of an element.
	 * Returns the formatted value appropriate for a detailed view.
	 *
	 * @return mixed The rendered output for viewing.
	 */
	public function showView() {
		return $this->viewVal();
	}

	/**
	 * Displays the data for a list view.
	 *
	 * This method is used to render the data when multiple elements are displayed in a list or table.
	 * Returns the formatted value appropriate for a list view.
	 *
	 * @return mixed The rendered output for listing.
	 */
	public function showList() {
		return $this->viewVal();
	}
	
	/**
	 * Displays the data in an embedded context.
	 *
	 * This method is used when the data is displayed as part of another element.
	 * Returns the formatted value appropriate for embedding.
	 *
	 * @return mixed The rendered output for embedding.
	 */
	public function showEmbeded() {
		return $this->viewVal();
	}

	/**
	 * Generates the input field for creating a new data entry.
	 *
	 * This method provides the interface item element required for capturing the data when creating a new element.
	 * Returns the appropriate input field for creation.
	 *
	 * @return mixed The input element for creation.
	 */
	public function showCreate() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Input($this->name(), '', null, $this->label(), $this->required(), $this->ObjectId());	
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}
		
	/**
	 * Generates the input field for updating an existing data entry.
	 *
	 * This method provides the interface item element required for modifying the data of an existing element.
	 * Returns the appropriate input field for updating, potentially pre-filled with the current value.
	 *
	 * @return mixed The input element for updating.
	 */
	public function showUpdate() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{		
			$input = new SI_Input($this->name(), $this->val(), null, $this->label(), $this->required(), $this->ObjectId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	/**
	 * Generates the output for displaying the data in a deletion context.
	 *
	 * This method is used to render the data when an element is being prepared for deletion.
	 * Child classes should override this method to provide a specific representation for deletion.
	 *
	 * @return mixed The rendered output for deletion.
	 */
	public function showDelete() {}

	/**
	 * Generates the input field or display for searching based on this data.
	 *
	 * This method is used to create the form element or display representation for searching.
	 * Returns the appropriate input field or display for searching.
	 *
	 * @return mixed The input element or display for searching.
	 */
	public function showSearch() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{	
			$input = new SI_Input($this->name(), $this->val(), null, $this->label(), null, $this->ObjectId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	/**
	 * Performs actions before data is read from the data source.
	 *
	 * This method is a hook for executing custom logic before the data is fetched or read.
	 */
	public function preRead() {}
	
	/**
	 * Performs actions before data is created in the data source.
	 *
	 * This method is a hook for executing custom logic before the data is stored for a new element.
	 */
	public function preCreate() {}
		
	/**
	 * Performs actions before data is updated in the data source.
	 *
	 * This method is a hook for executing custom logic before the data is updated for an existing element.
	 */
	public function preUpdate() {}

	/**
	 * Performs actions before data is deleted from the data source.
	 *
	 * This method is a hook for executing custom logic before the data is removed for an element.
	 */
	public function preDelete() {}

	/**
	 * Performs actions before a search is executed on the data source.
	 *
	 * This method is a hook for executing custom logic before search criteria are applied.
	 */
	public function preSearch() {}
	
	//----

	/**
	 * Prepares data for a read operation from the data source.
	 *
	 * This method is called by the parent Element's `processData('doRead')` method.
	 * It should return an array of data relevant for reading if the data is fetchable.
	 *
	 * @return array|null An array containing data for the read operation, or null if the data is not fetchable.
	 * @see SC_Element::processData()
	 */
	public function doRead(){
		return ($this->fetch())
			? array(array($this->name(), $this->getClass()))
			: null;
	}

	/**
	 * Prepares data for a create operation in the data source.
	 *
	 * This method is called by the parent Element's `processData('doCreate')` method.
	 * It should return an array of data relevant for creation if the data is creatable.
	 *
	 * @return array|null An array containing data for the create operation, or null if the data is not creatable.
	 * @see SC_Element::processData()
	 */
	public function doCreate(){
		return ($this->create())
			? array(array($this->name(), $this->getClass(), $this->val()))
			: null;
	}

	/**
	 * Prepares data for an update operation in the data source.
	 *
	 * This method is called by the parent Element's `processData('doUpdate')` method.
	 * It should return an array of data relevant for updating if the data is updatable.
	 *
	 * @return array|null An array containing data for the update operation, or null if the data is not updatable.
	 * @see SC_Element::processData()
	 */
	public function doUpdate(){
		return ($this->update())
			? array(array($this->name(),$this->getClass(),$this->val()))
			: null;
	}

	/**
	 * Prepares data for a search operation on the data source.
	 *
	 * This method is called by the parent Element's `processData('doSearch')` method.
	 * It should return an array of data relevant for searching if the data is searchable and fetchable.
	 *
	 * @return array|null An array containing data for the search operation, or null if the data is not searchable or fetchable.
	 * @see SC_Element::processData()
	 */
	public function doSearch(){
		return ($this->search() && $this->fetch())
			? array(array($this->name(), $this->getClass(), $this->val(), $this->filterCriteria()))
			: null;		
	}

	/**
	 * Performs actions during a delete operation.
	 *
	 * This method is called by the parent Element's `processData('doDelete')` method.
	 * Child classes can override this to perform specific actions during deletion.
	 * @see SC_Element::processData()
	 */
	public function doDelete(){}


	/**
	 * Performs actions after data is read from the data source.
	 *
	 * This method is a hook for executing custom logic after the data has been fetched or read.
	 * It is called by the parent Element's `processData('postRead')` method.
	 * @see SC_Element::processData()
	 */
	public function postRead()
	{}

	/**
	 * Performs actions after data is created in the data source.
	 *
	 * This method is a hook for executing custom logic after the data has been stored for a new element.
	 * It is called by the parent Element's `processData('postCreate')` method.
	 * @see SC_Element::processData()
	 */
	public function postCreate()
	{}

	/**
	 * Performs actions after data is updated in the data source.
	 *
	 * This method is a hook for executing custom logic after the data has been updated for an existing element.
	 * It is called by the parent Element's `processData('postUpdate')` method.
	 * @see SC_Element::processData()
	 */
	public function postUpdate()
	{}

	/**
	 * Performs actions after data is deleted from the data source.
	 *
	 * This method is a hook for executing custom logic after the data has been removed for an element.
	 * It is called by the parent Element's `processData('postDelete')` method.
	 * @see SC_Element::processData()
	 */
	public function postDelete()
	{}

	/**
	 * Performs actions after a search is executed on the data source.
	 *
	 * This method is a hook for executing custom logic after search criteria have been applied.
	 * It is called by the parent Element's `processData('postSearch')` method.
	 * @see SC_Element::processData()
	 */
	public function postSearch()
	{}
	
		
	public function filterCriteria($filterCriteria = null) {
		if(isset($filterCriteria)) $this->filterCriteria = $filterCriteria;
		
		return strtr($this->filterCriteria, array(
			'name' => $this->name(),
		));
	}
    
	/**
	 * Clears the value of the Data object.
	 */
	public function clearValue() {
        $this->clear('val');
	}

	
	/**
	 * Sets create, update, search, list, required and fetch flags according to the letters in $flags
	 *
	 * The flags are represented by a string where each character indicates a specific use case:
	 * 'v' (view), 'c' (create), 'u' (update), 's' (search), 'l' (list), 'e' (embeded), 'r' (required), 'f' (fetch).
	 * Uppercase letters set the flag to true, lowercase letters set it to false.
	 *
	 * @param string|null $flags Optional. A string containing the flags to set. If null, the current flags string is returned.
	 * @return string|void Returns the current flags string if $flags is null, otherwise sets the flags and returns nothing.
	 */
	function dataFlags($flags = null) {
		// @todo: Optimizar esta parte
		// @todo: check conflict with required and create/update
		// Ej: $this->view( strpos($flags,'v')!==false );
		///RSL 2022 added "e Emebeded"
		$a_flags = array(
			'v' => 'view',
			'c' => 'create',
			'u' => 'update',
			's' => 'search',
			'l' => 'list',
			'e' => 'embeded',
			'r' => 'required',
			'f' => 'fetch',
		);
	
		if(isset($flags)) {
			foreach(str_split($flags) as $flag)
				$this->{$a_flags[strtolower($flag)]} = ($flag != strtolower($flag));
		} else {
			$return = '';
			foreach($a_flags as $letter => $flag)
				$return.= $this->$flag ? strtoupper($letter) : strtolower($letter);
			return $return;
		}
	}

	/**
	 * Tells PHP how to render this object as a string
	 *
	 * @return string
	 */
	public function __toString() {
		return (string)$this->val();
	}

	/**
	 * Gives the name of the field for the create or update forms (usualy an HTML form)
	 *
	 * @param $prefijoNombre =null valor que se puede usar para distinguir los diversos de dos elementos
	 * Gives the name of the field for the create or update forms (usually an HTML form).
	 *
	 * @param string|null $inputName Optional. The name to set for the input field. If null, the current input name is returned or derived.
	 * @return string The name of the input field.
	 */
	public function inputName($inputName=null){
		if($inputName){
			$this->inputName = $inputName;
		}else{
			return @$this->inputName ?: $this->name();
		}
		
		
	}

	/**
	 * Returns a unique Interface ID for the Data object instance.
	 *
	 * This ID can be used for generating unique Interface element IDs in forms or other interfaces.
	 *
	 * @return string A unique Interface ID for the instance.
	 */
	function InterfaceId() {
        return $this->instanceId();
    }

	/**
	 * Returns the value to be used in input fields, based on the `printValInInput` flag.
	 *
	 * @return mixed|string The data value or an empty string.
	 */
	function inputVal(){
        if($this->printValInInput){ return $this->val(); }
		else{ return '';  }
	}

	/**
	 * Returns or sets the label for the data object.
	 * If setting, it updates the internal label.
	 * If getting and no label is set, it generates one from the data name and applies localization.
	 *
	 * @access public
	 * @param string|null $label Optional. The label to set. If null, the current label is returned.
	 * @return string The localized label for the data object.
	 */
	public function label($label=null){
		if($label){
			$this->label = $label;
		}else{
			return isset($this->label) ? SC_MAIN::L($this->label) : SC_MAIN::L(ucfirst($this->name()));
		}
	}
		
}
