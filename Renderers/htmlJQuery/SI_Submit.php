<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Submit extends SI_Item {

    function __construct($content = '', $onclick = '', $id = false) {
        $this->content = $content;
        $this->addAttribute('onclick',$onclick);
        $this->addAttribute('type','submit');
        $this->addAttribute('id',$id);
        $this->addClass('SI_Button');
    }

    function setTagsVals($renderVals = null){
        $this->start = "<button {$this->attributesString()}>";
        $this->end = "</button>\n";
    }

}
