<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


/**
* Data type used to concatenate the values of multiple other {@see SC_Data} attributes within an {@see SC_Element}.
*
* This complex data type is designed to combine the string representations of several data fields
* from its parent Element into a single output string. This is useful for displaying combined information
* in lists, views, or embedded contexts, such as displaying a full name by concatenating first and last names,
* or an address from street, city, and postal code fields.
*
* The data fields to be concatenated are specified by their names in the constructor.
* A separator string can also be provided to be inserted between the concatenated values.
* It extends {@see SD_ComplexData} as it derives its value from other data sources within the Element.
*
* @version 1b.1.0
* @package SimplOn\Datas
* @author Ruben Schaffer
* @see SD_ComplexData
* @see SC_Data
* @see SC_Element
*/
class SD_Concat extends SD_ComplexData {

	protected $datasNames, $separator;

	/**
	 * Constructs a new SD_Concat data object.
	 *
	 * Initializes the object, setting the names of the data fields to concatenate
	 * and the separator string. It then sets up the layout to use the content method
	 * to generate the concatenated value and calls the parent constructor.
	 *
	 * @param string|null $label An optional label for the data field.
	 * @param array $datasNames An array of strings, where each string is the name of a data field within the parent element whose value should be concatenated.
	 * @param string $separator The string to use as a separator between the concatenated data values. Defaults to a space.
	 * @param int|null $flags Optional flags for data behavior.
	 * @param mixed|null $val An optional initial value for the data field. This is typically ignored for complex data types like this one.
	 * @param mixed|null $filterCriteria Optional criteria for filtering.
	 */
	function __construct($label = null, $datasNames, $separator=' ', $flags = null, $val = null, $filterCriteria = null){

		$dataPrepare = null;
		$this->datasNames = $datasNames;
		$this->separator = $separator;

		$layout = new SI_Text([$this,'content']);
		
		parent::__construct($label, $dataPrepare, $layout, $flags, $val, $filterCriteria);

	}

	/**
	 * Generates the concatenated string value from the specified data fields.
	 *
	 * Iterates through the names of the data fields provided during construction,
	 * retrieves their embedded string representation using `showEmbeded()` from
	 * the parent element, and concatenates them with the defined separator.
	 *
	 * @return string The concatenated string value of the data fields.
	 */
	function content(){
        $ret = '';
        foreach($this->datasNames as $data) {
            $ret .= $this->parent()->$data->showEmbeded() . $this->separator;
        }
        return $ret;
	}
}
