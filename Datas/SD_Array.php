<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
* 
* 
*
* @version 1b.1.0
* @package SimplOn\Datas
* @author Ruben Schaffer 
*/
class SD_Array extends SC_Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	protected $options;


	public function __construct($options, $label=null, $flags=null, $val=null, $filterCriteria=null){
		$this->options = $options;
		parent::__construct($label, $flags, $val, $filterCriteria);
	}

	
	//@todo determine the proper input for the array
	function showInput($fill=true){
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			if($fill){
				$input = new SI_CheckBox($this->options, $this->inputName(), $this->val(), $this->required);
			}else{
				$input = new SI_CheckBox($this->options, $this->inputName(), null, $this->required);
			}	
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	public function showCreate() {
		return $this->showInput(false);
	}

	public function showUpdate() {
		return $this->showInput(true);
	}

	function viewVal($separator = ', '){
		$selectedOptions = [];
		foreach ($this->val as $key) {
			if (isset($this->options[$key])) {
				$selectedOptions[] = $this->options[$key];
			}
		}
		return implode($separator, $selectedOptions);
	}

	function val($val=null){	
		if(isset($val)){	
            if(!$this->fixedValue){
				if(is_array($val)){
					$this->val = $val;
				}else{
                    $unserialized = @unserialize($val);
                    if ($unserialized !== false && is_array($unserialized)) {
                        $this->val = $unserialized;
                    } else {
						throw new SC_DataValidationException('The value must be an array');
                    }
				}	
			}
		}else{
			return $this->val;
		}
	}

	public function doCreate(){
		$encVal = serialize($this->val);
		return ($this->create())
			? array(array($this->name(), $this->getClass(), $encVal))
			: null;
	}
		
	public function doUpdate(){
		$encVal = serialize($this->val);
		return ($this->update())
			? array(array($this->name(),$this->getClass(),$encVal))
			: null;
	}

}