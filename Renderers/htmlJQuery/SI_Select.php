<?php

class SI_Select extends SI_Input {
    //private $placeHolder;

    function __construct($options = array(), $name = '', $selected = null, $required = False, $id = false, $placeHolder  = '') {
        $this->options = $options;
        $this->selected = $selected;
        $this->required = $required;
        $this->id = $id;
        $this->name = $name;
        $this->placeHolder  = $placeHolder;
        $this->styles = '
        select:not([multiple],[size]){
            padding-left: 0.25rem;
            background-position: center right 0.2rem;
        }';
        $this->addStylesToAutoCSS();
    }

    function setTagsVals($renderVals = null) {
        $id = $renderVals['id'] ? "id='".$renderVals['id']."'" : "";
        $name = $renderVals['name'] ? "name='".$renderVals['name']."'" : "";
        
        $this->start = "<select $id $name class='SI_Select'>";
        $this->end = "</select>";

        $content = "";
        if($this->placeHolder) {
            $content .= "<option value='' disabled selected>{$this->placeHolder}</option>";
        }
        foreach($renderVals['options'] as $value => $text) {
            $selectedAttr = ($value == $renderVals['selected']) ? 'selected' : '';
            $content .= "<option value='$value' $selectedAttr>$text</option>";
        }
        
        $this->content = $content;
    }
}
