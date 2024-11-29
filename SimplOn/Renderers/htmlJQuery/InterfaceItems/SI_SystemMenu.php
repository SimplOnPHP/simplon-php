<?php

use voku\helper\HtmlDomParser;

class SI_SystemMenu extends SI_Container{

    protected
        $menuIcon = '',
        $greatting = '',
        $logo,
        $message = '',
        $href = '';

    public function __construct($items = []){
        parent::__construct($items);
        
        $this->logo = $this->renderer()->imgsWebRoot().DIRECTORY_SEPARATOR.'Logo.webp';
        $this->menuIcon = $this->renderer()->imgsWebRoot().DIRECTORY_SEPARATOR.'menu.svg';
    } 
    

}