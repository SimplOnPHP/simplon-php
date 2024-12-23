<?php

use voku\helper\HtmlDomParser;

class SI_Input extends SI_Item{
    protected
        $doe,
        $text,
        $priority;  
    
    function __construct($doe, $text = '', $labelPosition = '') // $labelPosition =  'inline'  'top'  'right'  'left'
    {
        $this->doe = $doe;
        $this->text = $text;
        $this->labelPosition = style;
    }

    function readTemplate(){ 
        $renderer=SC_Main::$RENDERER;
        $dom = HtmlDomParser::file_get_html($this->templatePath());
        $renderer->getStyles($dom);
        $renderer->getJS($dom);
        $itemDom = $dom->findOne(".$labelPosition")->innerHtml();
        $itemDom = HtmlDomParser::str_get_html($itemDom);
        return $itemDom;
    }


}