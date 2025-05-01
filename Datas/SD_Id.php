<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


/**
* ID para las tablas
* --- No imprime un label y manda un input hidden.
*
* @version	1.0
* @author	Ruben Schaffer
* @category Data
*/
abstract class SD_Id extends SD_Data
{
	
	
	protected
		$view = false,
		$embeded = false,
		$create = false,
		$update = true,
		$required = true;

	
	/**
	 *
	 * @param type $flags 
	 */
	function dataFlags($flags = null){
		
		parent::dataFlags($flags);
		$this->required = true;
	}
	
	public function showCreate() {
		return null;
	}
		
	public function showUpdate() {
		return null;
	}

	public function showSearch() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Input($this->name(), $this->val(), null, $this->label(), null, $this->ObjectId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}
	
}
 