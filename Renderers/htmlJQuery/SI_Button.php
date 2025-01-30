<?php

class SI_Button extends SI_Item {

    function __construct($content = '', $onclick = '', $id = false) {
        $this->content = $content;
        $this->onclick = $onclick;
        $this->id = $id;
    }

    function setTagsVals($renderVals = null){
        $id = $renderVals['id'] ? "id='{$renderVals['id']}'" : "";
        $onclick = $renderVals['onclick'] ? "onclick='{$renderVals['onclick']}'" : "";
        $this->start = "<button $id $onclick class='SI_Button' type='button'>";
        $this->end = "</button>\n";
    }
}
