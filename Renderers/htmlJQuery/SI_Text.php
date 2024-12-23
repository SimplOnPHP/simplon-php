<?php

use voku\helper\HtmlDomParser;

class SI_Text extends SI_Item{
    protected
        $doe,
        $text,
        $priority;  
    
    function __construct($doe, $text = '', $style = '')
    {
        $this->doe = $doe;
        $this->text = $text;
        $this->style = $style;
    }

    function readTemplate(){ 
        $itemDom = HtmlDomParser::str_get_html('$text');
        return $itemDom;
    }


}