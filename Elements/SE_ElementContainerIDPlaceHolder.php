<?php 


class SC_ElementContainerIDPlaceHolder extends SC_Element {
	static $permissions;
    function construct() {
        $this->id = new SD_NumericId();
    }
}