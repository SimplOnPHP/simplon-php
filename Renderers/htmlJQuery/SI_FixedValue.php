<?php

class SI_FixedValue extends SI_Input {
    
    protected $value;
    
    function __construct($name = '', $value = '', $id = '') {
        $this->name = $name;
        $this->content = $value;
        $this->id = $id;
        $this->required = false;
        
        $this->placeHolder = false;
        $this->styles = '
            .SI_FixedValue {
                opacity: 0.8;
            }
        ';
        $this->addStylesToAutoCSS();
    }

    function setTagsVals($renderVals = null){
        $this->start = '<input type="text" class="SI_FixedValue" disabled="disabled" value="';
        $this->end = '" />';
    }
}
