<?php

class SI_Button extends SI_Item {

    function __construct($content = '', $onclick = '', $id = false) {
        $this->content = $content;
        $this->addAttribute('onclick',$onclick);
        $this->addAttribute('type','button');
        $this->addAttribute('id',$id);
        $this->addClass('SI_Button');
    }

    function setTagsVals($renderVals = null){
        $this->start = "<button {$this->attributesString()}>";
        $this->end = "</button>\n";
    }
}
