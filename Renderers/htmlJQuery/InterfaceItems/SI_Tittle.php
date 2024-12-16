<?php

use voku\helper\HtmlDomParser;

class SI_Tittle extends SI_Item{

    protected
    $text,
    $size;
    
    function __construct($text = null, $size = '1')
    {
        $this->text = $text;
        $this->size = $size;
    }

    function getLayout(){
        return HtmlDomParser::str_get_html('<h'.$this->size.'>$text</h'.$this->size.'>');
        
    }
}