<?php

class SI_Form extends SI_Container {

    protected
        $type,
        $action;

    function __construct(array $items, $action, $type, $defaultItemMethod )
    {
        $this->action = $action;
        $this->type = $type;
        parent::__construct($items, $defaultItemMethod);
    }

    
       
    
}

