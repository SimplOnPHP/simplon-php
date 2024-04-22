<?php 


class SE_ElementContainerIDPlaceHolder extends SE_Element {
    function construct() {
        $this->id = new SD_NumericId();
    }
}