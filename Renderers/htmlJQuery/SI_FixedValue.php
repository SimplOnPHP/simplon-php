<?php

class SI_FixedValue extends SI_Input {
    
    protected $value;
    
    function __construct($name = '', $value = '', $id = '') {

        $this->required = false;
        if($name)   {$this->addAttribute('name',$name);}
        if($value)  {$this->addAttribute('value',$value);}
        if($id)     {$this->addAttribute('id',$id);}
        $this->addAttribute('type','text');
        $this->addClass('SI_FixedValue');
        $this->addAttribute('disabled','disabled');

        $this->addStylesToAutoCSS('
            .SI_FixedValue {
                opacity: 0.8;
            }
        ');
    }

    function setTagsVals($renderVals = null){
        $this->start = "<input {$this->attributesString()} />";
        $this->end = '';
    }
}
