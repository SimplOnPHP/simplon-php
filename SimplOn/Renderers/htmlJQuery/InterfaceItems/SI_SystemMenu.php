<?php

use voku\helper\HtmlDomParser;

class SI_SystemMenu extends SI_Container{

    protected
        $menuIcon = '',
        $greatting = '',
        $message = '',
        $href = '';

    public function __construct($items = []){
        parent::__construct($items);
        $this->menuIcon = $this->renderer()->imgsWebRoot().DIRECTORY_SEPARATOR.'menu.svg';
    } 
    

}