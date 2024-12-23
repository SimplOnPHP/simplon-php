<?php


use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Siempre deben recibir el Dato o elemento ($doe) del queu forman parte
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 * 
 * En el Renderer htmlJQuery deben tener solo el atributo $doe que guarda a el Dato o Elemento del que forma parte el item de interfaz y atributos sencillos de valor de string para guardar los valores de lo que debe sustiturse en pantalla
 * 
 */
class SI_Item extends SC_BaseObject {

    protected
        $type = null,
        $doe; 

    function __construct($doe) {
        $this->doe = $doe;
    } 

    function templatePath(){
        $renderer=SC_Main::$RENDERER;
        if(file_exists($renderer->Renderer_path().'/'.$this->getClass().'.html')){
            $ret = $renderer->Renderer_path().'/'.$this->getClass().'.html'; 
        }elseif($this->getClassPrefix() == 'AI'){
            $apath = $renderer->Renderer_path().'/'.'A' . substr($this->getClass(), 1).'.html';
            if(file_exists($apath)){$ret = $renderer->Renderer_path().'/'.$this->getClass().'.html';} 
        }else{ $ret = parent::templatePath(); }

        return $ret;
    }

    function readTemplate(){
        $renderer=SC_Main::$RENDERER;
        $dom = HtmlDomParser::file_get_html($this->templatePath());
        $renderer->getStyles($dom);
        $renderer->getJS($dom);
        $itemDom = $dom->findOne("body")->innerHtml();
        $itemDom = HtmlDomParser::str_get_html($itemDom);
        return $itemDom;
    }

    function setFillValues() {
        $stringAttributes = [];
        $attributes = get_object_vars($this);
       
        foreach ($attributes as $key => $value) {           
            if (is_string($value) AND strpos($value, '::') === 0) {
                $this->$key = $this->doe->{substr($value, 2)}();
            }elseif(is_string($value)){
                $this->$key = $value;
            }
        }
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

