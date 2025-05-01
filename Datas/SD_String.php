<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * String data type 
 * 
 * This is a string data type which allow you show an input to introduce a string.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_String extends SD_Data {
	/**
         *
         * @var boolean $view,$create,$update and $list - these variables are 
         * flags to indicate if this input will be displayed in the different templates
         * 
         * @var string $filterCriteria - this variable indicates the kind of filter to this
         * kind of data.
         */
	protected 
		$view = true,
		$create = true,
		$update = true,
		$list = true,
		$embeded = true,
		$filterCriteria = 'name ~= :name';

  
}