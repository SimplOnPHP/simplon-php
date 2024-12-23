<?php
use voku\helper\HtmlDomParser;


class SI_Form extends SI_StaticContainer {

    protected
        $items,
        $action,
        $method;
    
    function __construct($doe, array $items, $action) 
    {
        $this->doe = $doe;
        $this->action = $action;
        $this->items = $items;
        
        $this->method = "post";
        $this->enctype = $this->hasFileInput($items) ? "multipart/form-data" : null;
    }
    
    private function hasFileInput(array $items): bool 
    {
        foreach ($items as $item) {
            if ($item instanceof SI_FileInput) return true;
        }
        return false;
    }

    function readTemplates(){
        $renderer=SC_Main::$RENDERER;
        $dom = HtmlDomParser::file_get_html($renderer->Renderer_path().'/'.$this->getClass().'.html');
        $renderer->getStyles($dom);
        $renderer->getJS($dom);

        $conteinerDoms['containerDom'] = $dom->findOne("body")->innerHtml();
        $conteinerDoms['containerDom'] = HtmlDomParser::str_get_html($conteinerDoms['containerDom']);

        return $conteinerDoms;
    }

    function readTemplate(){
        throw new Exception("This is a".$this->getClass()." container, not a item, please use readTemplates()");
    }

    function assingItemsTypes(){
    }


    /**
     * Sets the values to generate a Data or Element template that can later be filled independently of the SI_items defined in that method to have the chance to create elements view independent of the limitations of the Simplon Interface items.
     */
    function setDOETemplateValues(){
        $stringAttributes = [];
        $attributes = get_object_vars($this);
        
        foreach ($attributes as $key => $value) {
            
            if (is_string($value) AND strpos($value, '::') === 0) {
                $this->$key = substr($value, 2);
            }elseif(is_string($value)){
                $this->$key = '$'.$value;
            }
        }
    }
}