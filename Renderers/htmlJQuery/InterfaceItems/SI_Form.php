<?php

class SI_Form extends SI_Container {

    protected
        $formMethod = 'POST',
        $type,
        $action;

    function __construct(array $items, $action, $Ajax = true, $defaultItemMethod )
    {
        $this->action = $action;
        $this->type = $Ajax ? 'Ajax' : '';
        parent::__construct($items, $defaultItemMethod);
    }

    function enctype() {
        $ret = ''; // Default form method
        foreach ($this->items as $item) {
            // If there's a file input, return 'multipart/form-data'
            if ($item['item'] instanceof SD_File) {
                return 'multipart/form-data';
            }
        }
        return $ret; // Return 'post' if no file inputs are found
    }
    
}

