<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Select extends SI_Options {

    protected
        $options,
        $selected;

    function __construct($options = array(), $name = '', $selected = null, $required = False, $id = false, $placeHolder  = '') {
        $this->options = $options;
        $this->selected = $selected;
        $this->required = $required;
        if($id){$this->addAttribute('id',$id);}
        if($name){$this->addAttribute('name',$name);}
        $this->placeHolder  = $placeHolder;
        $this->addClass('SI_Select');
        $this->addStylesToAutoCSS('
        select:not([multiple],[size]){
            padding-left: 0.25rem;
            background-position: center right 0.2rem;
        }');
    }

    function setTagsVals($renderVals = null) {
        
        $this->start = "<select {$this->attributesString()}>";
        $this->end = "</select>";

        $content = "";
        if($this->placeHolder) {
            $content .= "<option value='' disabled selected>{$this->placeHolder}</option>";
        }
        foreach($renderVals['options'] as $value => $text) {
            $selectedAttr = ($value == $renderVals['selected']) ? 'selected' : '';
            $this->addAttribute('value',$value);
            $content .= "<option {$this->attributesString()} $selectedAttr>$text</option>";
        }
        
        $this->content = $content;
    }
}
