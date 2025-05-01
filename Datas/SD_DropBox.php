<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SD_DropBox extends SD_String {

    protected $options;

	function __construct($label = null, $options = [], $flags = null, $val = null, $filterCriteria = null)
    {
        $this->options = $options;
        parent::__construct($label, $flags, $val, $filterCriteria);
    }

	public function val($val = null) {
		if($val === '' OR $val === ' '){
			$this->val = null;
		}elseif($val===null){
			return $this->val;
		}elseif(isset($val)){
			if(!$this->fixedValue) {
				$this->val = $val;
			}
		}
	}

	public function selected($test = null) {
		return ($test == $this->val);
	}
	
	public function viewVal() {
		if(isset($this->options[$this->val]))
		{return $this->options[$this->val];}
	}



}

