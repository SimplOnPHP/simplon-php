<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


class SD_DArray extends SD_Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	//@todo determine the proper input for the array
	function showInput($fill=true)
	{
		return '<input name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}


	function val($val=null){	
		if(isset($val)){	
            if(!$this->fixedValue){
				if(is_array($val))
					$this->val = $val;
				else
					throw new SC_Exception('Recived value should be an array!');
			}
		}else{
			return $this->val;
		}
	}
	
	
}