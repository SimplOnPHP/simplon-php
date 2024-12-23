<?php
use voku\helper\HtmlDomParser;

/**
 *NOTTE for now a  clone of dynamiContainer but should be diferent
 dynami ccontainers are are allways rendered on the fly because their content may vary during exectution
 static containers templates will be generated once and the number of ittems and their kind is  fixed and thus a template can be generated
 */
class SI_StaticContainer extends SI_Container {

    protected
        $items,
        $warp;
    
    function __construct($doe, $items, $warp = true)
    {
        $this->doe = $doe;
        $this->items = $items;
        $this->warp = $warp;
    }  
        


    function readTemplates(){
        $renderer=SC_Main::$RENDERER;
        $dom = HtmlDomParser::file_get_html($renderer->Renderer_path().'/'.$this->getClass().'.html');
        $renderer->getStyles($dom);
        $renderer->getJS($dom);


        $conteinerDoms['containerDom'] = $dom->findOne("body")->innerHtml();
        $conteinerDoms['containerDom'] = HtmlDomParser::str_get_html($conteinerDoms['containerDom']);

        $conteinerDoms['itemWarp'] = $dom->findOne(".itemWarp");
        
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