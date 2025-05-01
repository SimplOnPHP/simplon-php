<?php 

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SC_ElementContainerIDPlaceHolder extends SC_Element {
	static $permissions;
    function construct() {
        $this->id = new SD_AutoIncrementId();
    }
}