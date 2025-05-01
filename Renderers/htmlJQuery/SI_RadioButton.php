<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_RadioButton extends SI_Options {
    protected $separator;

    function __construct($options = array(), $name = '', $selected = null, $required = False, $separator = '&nbsp&nbsp&nbsp') {
        $this->options = $options;
        $this->selected = $selected;
        $this->required = $required;
        $this->separator = $separator;
        $this->name = $name;
        $this->addAttribute('type','radio');
    }

    function setTagsVals($renderVals = null) {
        $this->start = "";
        $this->end = "";
        if($this->name){$this->addAttribute('name',$this->name);}

        $content = "";
        $required = ($this->required) ? 'required' : '';
        foreach($renderVals['options'] as $value => $text) {
            if($value == 0 || !empty(trim($value))){
                $tagId = uniqid();
                $selected = ($value == $renderVals['selected']) ? 'checked' : '';
                $this->addAttribute('value',$value);
                $content .=  "<input {$this->attributesString()} id='$tagId' value='$value' $selected $required><label for='$tagId'>$text</label>{$this->separator}";
            }
        }
        $content = rtrim($content, $this->separator);
        $this->content = $content;
    }
}


