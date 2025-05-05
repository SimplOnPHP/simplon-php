<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Input extends SI_Item {

    protected $required = false,
        $placeHolder = false,
        $id = false,
        $name = '',
        $attributes = array();


    function __construct( $name, $value='', $type=null, $placeHolder=False, $required=False, $id = False) {
        $this->required = $required;
        $this->addAttribute('value',$value);

        if($type)           {$this->addAttribute('type',$type);}
        if($placeHolder)    {$this->addAttribute('placeHolder',$placeHolder);}
        if($id)             {$this->addAttribute('id',$id);}
        if($name)           {$this->addAttribute('name',$name);}
    }

    function setTagsVals($renderVals = null) {
        $this->start = "<input {$this->attributesString()} />";
    }
}