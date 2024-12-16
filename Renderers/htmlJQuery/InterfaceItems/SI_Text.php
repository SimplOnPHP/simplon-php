<?php

use voku\helper\HtmlDomParser;

class SI_Text extends SI_Item{

    protected
    $text,
    $size;
    
    function __construct($text = null)
    {
        $this->text = $text;
    }

    function getLayout(){
        return HtmlDomParser::str_get_html('<span>$text</span>');
        
    }
}