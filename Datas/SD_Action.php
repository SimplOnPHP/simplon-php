<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


/**
* Data type used to represent an actionable link to the a parent {@see SC_Element} method.
*
* SD_Action creates a link using {@see SI_Link},that triggers a specific method call on its
* parent {@see SC_Element} instance when activated.
* This is commonly used in administrative interfaces, reports, or lists to provide
* actions such as "View", "Update", or "Delete" for individual Element records.
*
* The constructor defines the method name to be called on the parent Element, the text
* to be displayed for the action link, and an optional icon. The actual URL or action
* is generated using the renderer's `action()` method, which builds the appropriate URL
* to invoke the specified method on the current Element instance.
* It extends {@see SD_ComplexData} as it facilitates interaction derived from the context of the Element.
*
* @version 1b.1.0
* @package SimplOn\Datas
* @author Ruben Schaffer
* @see SD_ComplexData
* @see SC_Data
* @see SC_Element
* @see SI_Link
*/
class SD_Action extends SD_ComplexData {

	protected $methodToCall, $action;

	/**
	 * Constructor for the SD_Action class.
	 *
	 * Initializes an SD_Action instance, setting up the method to be called on the parent
	 * Element and creating the visual link element using SI_Link.
	 *
	 * @param string|null $label An optional label for the data element.
	 * @param string $methodToCall The name of the method to call on the parent SC_Element instance.
	 * @param string $text The text to display for the action link.
	 * @param string|null $icon An optional icon to display alongside the link text.
	 * @param int|null $flags Optional flags for the data element.
	 * @param mixed|null $val Optional initial value for the data element.
	 * @param mixed|null $filterCriteria Optional criteria for filtering the data element.
	 */
	function __construct($label = null, $methodToCall, $text, $icon = null, $flags = null, $val = null, $filterCriteria = null){

		$dataPrepare = null;
		$this->methodToCall = $methodToCall;

		$layout = new SI_Link([$this,'action'], $text,$icon);
		
		parent::__construct($label, $dataPrepare, $layout, $flags, $val, $filterCriteria);

	}

	/**
	 * Handles the action associated with the SD_Action instance.
	 *
	 * If an action is provided, it sets the internal action property. If no action
	 * is provided and no internal action is set, it generates the action URL using
	 * the renderer's `action()` method. Otherwise, it returns the internally set action.
	 *
	 * @param string|null $action An optional action string to set internally.
	 * @return string The generated or internally set action URL.
	 */
	function action($action = null){
		if($action){$this->action = $action;}
		elseif(!$this->action){return SC_Main::$RENDERER->action($this->parent(),$this->methodToCall);}
		else{return $this->action;}
	}
}

