<?php 


class SC_ElementContainerIDPlaceHolder extends SC_Element {
    function construct() {
        $this->id = new SD_NumericId();
    }
}