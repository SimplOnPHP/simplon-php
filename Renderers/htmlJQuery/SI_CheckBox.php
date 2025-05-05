<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_CheckBox extends SI_Options {
    protected $separator;

    function __construct($options = array(), $name = '', $selected = [], $required = false, $separator = '&nbsp&nbsp&nbsp') {
        $this->options = $options;

        
        $this->selected = is_array($selected) ? $selected : [$selected]; // Ensure selected is always an array
        $this->selected = array_map(function($value) {
            return is_numeric($value) ? intval($value) : $value;
        }, $this->selected);
        $this->required = $required;
        $this->separator = $separator;
        $this->name = $name;
        $this->addAttribute('type', 'checkbox');
    }

    function setTagsVals($renderVals = null) {
        $this->start = "";
        $this->end = "";
   
        if ($this->name) {
            $this->addAttribute('name', $this->name . '[]'); // Use array notation for multiple selections
        }

        $content = "";
        $required = ($this->required) ? 'required' : '';
        foreach ($renderVals['options'] as $value => $text) {
            if ($value === 0 || !empty(trim($value))) {
                $tagId = uniqid();                          
                $selected = (in_array($value, $renderVals['selected'], true)) ? 'checked' : '';
                $this->addAttribute('value', $value);
                $content .= "<input {$this->attributesString()} id='$tagId' $selected ><label for='$tagId'>$text</label>{$this->separator}";
            }
        }
        $content = rtrim($content, $this->separator);
        $this->content = $content;
    }
}
