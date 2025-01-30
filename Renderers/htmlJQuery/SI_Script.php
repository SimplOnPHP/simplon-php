<?php
class SI_Script extends SI_Item {
    protected $content;
    
    function __construct($content) {
        $this->content = $content;
    }

    function setTagsVals($renderVals = null) {
        $this->start = '<script>';
        $this->end = '</script>';
    }
}
