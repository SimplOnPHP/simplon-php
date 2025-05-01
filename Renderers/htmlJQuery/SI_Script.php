<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
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
